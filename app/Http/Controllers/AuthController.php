<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Incidencia;
use App\Models\Seccion;
use App\Models\Curso;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuthController extends Controller
{
    // Muestra el formulario de login
    public function showLogin()
    {
        return view('login');
    }

    // Procesa el intento de inicio de sesión
    public function login(Request $request)
    {
        // 1. Validar datos recibidos del formulario
        $request->validate([
            'dni' => ['required', 'digits:8'],
            'password' => ['required', 'string'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 números.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // 2. Buscar al usuario únicamente por DNI en la tabla users
        $user = User::where('dni', $request->dni)->first();

        // 3. Verificar si el usuario existe y si la contraseña coincide
        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->estado_aprobacion === 'Pendiente') {
                return back()->withErrors([
                    'login_error' => 'Tu cuenta está pendiente de aprobación por el administrador.',
                ])->withInput($request->only('dni'));
            }

            if ($user->estado_aprobacion === 'Rechazado') {
                return back()->withErrors([
                    'login_error' => 'Tu solicitud de cuenta ha sido rechazada.',
                ])->withInput($request->only('dni'));
            }

            if ($user->activo === false) {
                return back()->withErrors([
                    'login_error' => 'Tu cuenta se encuentra desactivada.',
                ])->withInput($request->only('dni'));
            }

            if ($user->estado_aprobacion !== 'Aprobado') {
                return back()->withErrors([
                    'login_error' => 'Las credenciales ingresadas son incorrectas.',
                ])->withInput($request->only('dni'));
            }

            $user->load('rol');

            // 4. Guardar datos clave en la sesión
            $request->session()->regenerate();

            $request->session()->put([
                'usuario_id' => $user->id,
                'usuario_nombre' => $user->name,
                'usuario_correo' => $user->email,
                'usuario_dni' => $user->dni,
                'usuario_rol_id' => $user->rol?->id,
                'usuario_rol' => $user->rol?->nombre_rol,
            ]);

            // Redirigir al dashboard
            return redirect()->route('dashboard');
        }

        // 5. Si falla la autenticación, volver atrás con un mensaje de error
        return back()->withErrors([
            'login_error' => 'Las credenciales ingresadas son incorrectas.',
        ])->withInput($request->only('dni'));
    }

    // Procesa el cierre de sesión
    public function logout(Request $request)
    {
        // Invalidar la sesión actual.
        $request->session()->invalidate();

        // Crear un token CSRF nuevo para la siguiente sesión.
        $request->session()->regenerateToken();

        // Redirigir al login
        return redirect()->route('login');
    }

    // Muestra la vista del Dashboard
    public function dashboard(Request $request)
    {
        $usuario = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        $graficos = null;
        $seleccionProfesor = null;
        $grados = collect();
        $secciones = collect();
        $cursos = collect();

        if ($usuario->rol?->nombre_rol === 'Profesor') {
            $claveContexto = 'contexto_dashboard_profesor_'.$usuario->getKey();
            if ($request->boolean('limpiar_contexto')) {
                $request->session()->forget($claveContexto);

                return redirect()->route('dashboard');
            }

            $seleccionProfesor = $this->contextoDashboardProfesor($request->query());
            if ($seleccionProfesor !== null) {
                $request->session()->put($claveContexto, $seleccionProfesor);
            } else {
                $seleccionProfesor = $this->contextoDashboardProfesor(
                    $request->session()->get($claveContexto, [])
                );
            }

            $grados = Seccion::query()->whereNotNull('grado')->distinct()->orderBy('grado')->pluck('grado');
            $secciones = Seccion::query()->orderBy('grado')->orderBy('seccion')->get(['id', 'grado', 'seccion']);
            $cursos = Curso::query()->orderBy('nombre')->get(['id', 'nombre']);
            $graficos = $this->datosDashboardProfesor($usuario, $seleccionProfesor);
        } elseif (in_array($usuario->rol?->nombre_rol, ['Administrador', 'Directora', 'Subdirector'], true)) {
            $graficos = $this->datosDashboardAdmin();
        }

             // Tarjetas de resumen (números): las ven todos los roles con dashboard
        $tarjetas = in_array($usuario->rol?->nombre_rol, ['Profesor', 'Administrador', 'Directora', 'Subdirector'], true)
            ? $this->tarjetasResumen($usuario, $seleccionProfesor)
            : null;

        return view('dashboard', compact(
            'graficos',
            'tarjetas',
            'seleccionProfesor',
            'grados',
            'secciones',
            'cursos'
        ));
    }

    private function contextoDashboardProfesor(array $seleccion): ?array
    {
        $grado = trim((string) ($seleccion['grado'] ?? ''));
        $seccionId = $seleccion['seccion_id'] ?? null;
        $cursoId = $seleccion['curso_id'] ?? null;

        if ($grado === '' || !is_numeric($seccionId) || !is_numeric($cursoId)) {
            return null;
        }

        $seccion = Seccion::query()->find((int) $seccionId);
        $curso = Curso::query()->find((int) $cursoId);

        if (
            !$seccion
            || !$curso
            || $seccion->grado !== $grado
        ) {
            return null;
        }

        return [
            'grado' => $grado,
            'seccion_id' => $seccion->id,
            'curso_id' => $curso->id,
        ];
    }

    /**
     * Números de las 4 tarjetas de resumen del dashboard.
     * - Profesor: solo cuenta las incidencias que él registró.
     * - Administrador, Directora y Subdirector: cuentan todas las incidencias
     *   (las actas reservadas suman como una más, solo como número).
     * Las incidencias eliminadas no se cuentan (SoftDeletes).
     */
    private function tarjetasResumen(User $usuario, ?array $contextoProfesor = null): array
    {
        $esProfesor = $usuario->rol?->nombre_rol === 'Profesor';

        // Consulta base según el rol
        $base = fn () => Incidencia::query()
            ->when($esProfesor, function ($consulta) use ($usuario, $contextoProfesor) {
                if ($contextoProfesor === null) {
                    return $consulta->whereRaw('1 = 0');
                }

                return $consulta
                    ->where('usuario_id', $usuario->getKey())
                    ->where('curso_id', $contextoProfesor['curso_id'])
                    ->whereHas('estudiante', fn ($q) => $q->where('seccion_id', $contextoProfesor['seccion_id']));
            });

        // Filtra por nombre de estado (sin importar mayúsculas)
        $conEstado = fn ($consulta, array $estados) => $consulta->whereHas(
            'estado',
            fn ($q) => $q->whereRaw('LOWER(nombre) IN (' . implode(',', array_fill(0, count($estados), '?')) . ')', $estados)
        );

        $total = $base()->count();
        $finalizadas = $conEstado($base(), ['finalizado'])->count();
        $pendientes = $conEstado($base(), ['pendiente'])->count();
        $enProceso = $conEstado($base(), ['en proceso'])->count();

        // Graves activas = las mismas que se consideran urgencias (Grave + Pendiente/En Proceso)
        $gravesActivas = $conEstado($base(), ['pendiente', 'en proceso'])
            ->whereHas('gravedad', fn ($q) => $q->whereRaw('LOWER(nombre) = ?', ['grave']))
            ->count();

        // Mes actual contra mes anterior (por la fecha de la incidencia)
        $hoy = Carbon::now();
        $mesAnterior = $hoy->copy()->subMonthNoOverflow();

        $mesActualCantidad = $base()
            ->whereBetween('fecha', [$hoy->copy()->startOfMonth()->toDateString(), $hoy->copy()->endOfMonth()->toDateString()])
            ->count();
        $mesAnteriorCantidad = $base()
            ->whereBetween('fecha', [$mesAnterior->copy()->startOfMonth()->toDateString(), $mesAnterior->copy()->endOfMonth()->toDateString()])
            ->count();

        return [
            'alcance' => $esProfesor ? 'propias' : 'todas',
            'total' => $total,
            'mes_actual' => $mesActualCantidad,
            // null cuando el mes anterior no tiene incidencias (no se puede calcular el %)
            'variacion_mes' => $mesAnteriorCantidad > 0
                ? (int) round(($mesActualCantidad - $mesAnteriorCantidad) / $mesAnteriorCantidad * 100)
                : null,
            'finalizadas' => $finalizadas,
            'porcentaje_finalizadas' => $total > 0 ? round($finalizadas / $total * 100, 1) : 0,
            'abiertas' => $pendientes + $enProceso,
            'pendientes' => $pendientes,
            'en_proceso' => $enProceso,
            'graves_activas' => $gravesActivas,
        ];
    }

    /**
     * Arma los datos de los gráficos de incidencias del profesor:
     * una vista semanal (por día) y una mensual (por semana del mes),
     * cada una con su desglose de gravedad (Leve/Intermedia/Grave).
     */
    private function datosDashboardProfesor(User $usuario, ?array $contexto): array
    {
        $hoy = Carbon::now();
        $labelsSemana = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        $labelsMes = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        if ($contexto === null) {
            $incidenciasVacias = collect();

            return [
                'tipo' => 'profesor',
                'seleccionado' => false,
                'mensaje' => 'Selecciona grado, sección y curso para visualizar tus estadísticas',
                'semana' => [
                    'labels' => $labelsSemana,
                    'valores' => array_fill(0, count($labelsSemana), 0),
                    'gravedad' => $this->conteoPorGravedad($incidenciasVacias),
                    'gravedadPorGrado' => $this->conteoGravedadPorGrado($incidenciasVacias),
                ],
                'mes' => [
                    'labels' => $labelsMes,
                    'valores' => array_fill(0, count($labelsMes), 0),
                    'gravedad' => $this->conteoPorGravedad($incidenciasVacias),
                    'gravedadPorGrado' => $this->conteoGravedadPorGrado($incidenciasVacias),
                ],
            ];
        }

        $consultaBase = fn () => Incidencia::query()
            ->with([
                'gravedad:id,nombre',
                'estudiante.seccion:id,grado',
            ])
            ->where('usuario_id', $usuario->getKey())
            ->where('curso_id', $contexto['curso_id'])
            ->whereHas('estudiante', fn ($q) => $q->where('seccion_id', $contexto['seccion_id']));

        // --- Mes actual, dividido en cuatro semanas ---
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $incidenciasSemana = $consultaBase()
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'estudiante_id']);

        $labelsSemana = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        $valoresSemana = array_fill(0, count($labelsSemana), 0);

        foreach ($incidenciasSemana as $incidencia) {
            $indiceSemana = min(intdiv(Carbon::parse($incidencia->fecha)->day - 1, 7), 3);
            $valoresSemana[$indiceSemana]++;
        }

        // --- Año actual, agrupado por mes ---
        $inicioAnio = $hoy->copy()->startOfYear();
        $finAnio = $hoy->copy()->endOfYear();

        $incidenciasAnio = $consultaBase()
            ->whereBetween('fecha', [$inicioAnio->toDateString(), $finAnio->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'estudiante_id']);

        $valoresMes = array_fill(0, count($labelsMes), 0);

        foreach ($incidenciasAnio as $incidencia) {
            $indiceMes = Carbon::parse($incidencia->fecha)->month - 1;
            $valoresMes[$indiceMes]++;
        }

        return [
            'tipo' => 'profesor',
            'seleccionado' => true,
            'semana' => [
                'labels' => $labelsSemana,
                'valores' => $valoresSemana,
                'gravedad' => $this->conteoPorGravedad($incidenciasSemana),
                'gravedadPorGrado' => $this->conteoGravedadPorGrado($incidenciasSemana),
            ],
            'mes' => [
                'labels' => $labelsMes,
                'valores' => $valoresMes,
                'gravedad' => $this->conteoPorGravedad($incidenciasAnio),
                'gravedadPorGrado' => $this->conteoGravedadPorGrado($incidenciasAnio),
            ],
        ];
    }

    /**
     * Cuenta y calcula el porcentaje de incidencias por gravedad (Leve/Intermedia/Grave)
     * dentro de una colección de incidencias ya cargada con su relación "gravedad".
     *
     * @param Collection<int, Incidencia> $incidencias
     */
    private function conteoPorGravedad(Collection $incidencias): array
    {
        $total = $incidencias->count();
        $cantidades = ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0];

        foreach ($incidencias as $incidencia) {
            $nombreGravedad = $incidencia->gravedad?->nombre;

            if (isset($cantidades[$nombreGravedad])) {
                $cantidades[$nombreGravedad]++;
            }
        }

        $porcentajes = [];
        foreach ($cantidades as $nombre => $cantidad) {
            $porcentajes[$nombre] = $total > 0 ? round($cantidad / $total * 100, 1) : 0;
        }

        return [
            'cantidades' => $cantidades,
            'porcentajes' => $porcentajes,
            'total' => $total,
        ];
    }

    private function datosDashboardAdmin(): array
    {
        $hoy = Carbon::now();

        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();
        $inicioAnio = $hoy->copy()->startOfYear();
        $finAnio = $hoy->copy()->endOfYear();

        $incSemana = Incidencia::with([
            'gravedad:id,nombre',
            'curso:id,nombre',
            'estudiante.seccion:id,grado',
        ])
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'curso_id', 'estudiante_id']);

        $incAnio = Incidencia::with([
            'gravedad:id,nombre',
            'curso:id,nombre',
            'estudiante.seccion:id,grado',
        ])
            ->whereBetween('fecha', [$inicioAnio->toDateString(), $finAnio->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'curso_id', 'estudiante_id']);

        $labelsSemana = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        $labelsMes = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        $resumenSemana = $this->resumenPeriodoDashboardAdmin(
            $incSemana,
            $labelsSemana,
            fn ($incidencia) => min(intdiv(Carbon::parse($incidencia->fecha)->day - 1, 7), 3)
        );
        $resumenMes = $this->resumenPeriodoDashboardAdmin(
            $incAnio,
            $labelsMes,
            fn ($incidencia) => Carbon::parse($incidencia->fecha)->month - 1
        );

        $resumenSemana['general'] = [
            'labels' => $labelsSemana,
            'valores' => $resumenSemana['valores'],
        ];
        $resumenMes['general'] = [
            'labels' => $labelsMes,
            'valores' => $resumenMes['valores'],
        ];

        return [
            'tipo' => 'admin',
            'semana' => $resumenSemana,
            'mes' => $resumenMes,
        ];
    }

    private function resumenPeriodoDashboardAdmin(
        Collection $incidencias,
        array $labels,
        callable $indicePeriodo
    ): array
    {
        $valores = array_fill(0, count($labels), 0);
        $porGrado = [];
        $porCurso = [];
        $porCursoPorGrado = array_fill(0, 5, []);

        foreach ($incidencias as $incidencia) {
            $indice = $indicePeriodo($incidencia);
            if (!array_key_exists($indice, $valores)) {
                continue;
            }

            $valores[$indice]++;
            $grado = $incidencia->estudiante?->seccion?->grado;
            $curso = $incidencia->curso?->nombre;

            if ($grado !== null) {
                $porGrado[$grado] ??= array_fill(0, count($labels), 0);
                $porGrado[$grado][$indice]++;
            }

            if ($curso !== null) {
                $porCurso[$curso] ??= array_fill(0, count($labels), 0);
                $porCurso[$curso][$indice]++;
            }

            $indiceGrado = $this->indiceGradoDashboard($grado);
            if ($indiceGrado !== null && $curso !== null) {
                $porCursoPorGrado[$indiceGrado][$curso] ??= array_fill(0, count($labels), 0);
                $porCursoPorGrado[$indiceGrado][$curso][$indice]++;
            }
        }

        ksort($porGrado, SORT_NATURAL);
        ksort($porCurso, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($porCursoPorGrado as &$cursos) {
            ksort($cursos, SORT_NATURAL | SORT_FLAG_CASE);
        }
        unset($cursos);

        return [
            'labels' => $labels,
            'valores' => $valores,
            'porGrado' => array_map(
                fn ($grado, $conteos) => ['grado' => $grado, 'valores' => $conteos],
                array_keys($porGrado),
                array_values($porGrado)
            ),
            'porCurso' => array_map(
                fn ($curso, $conteos) => ['curso' => $curso, 'valores' => $conteos],
                array_keys($porCurso),
                array_values($porCurso)
            ),
            'porCursoPorGrado' => array_map(
                fn ($cursos) => array_map(
                    fn ($curso, $conteos) => ['curso' => $curso, 'valores' => $conteos],
                    array_keys($cursos),
                    array_values($cursos)
                ),
                $porCursoPorGrado
            ),
            'gravedad' => $this->conteoPorGravedad($incidencias),
            'gravedadPorGrado' => $this->conteoGravedadPorGrado($incidencias),
        ];
    }

    private function conteoGravedadPorGrado(Collection $incidencias): array
    {
        $nombresGrados = [
            'Primer grado',
            'Segundo grado',
            'Tercer grado',
            'Cuarto grado',
            'Quinto grado',
        ];
        $gravedades = ['Leve', 'Intermedia', 'Grave'];
        $conteos = array_fill(
            0,
            count($nombresGrados),
            array_fill_keys($gravedades, 0)
        );

        foreach ($incidencias as $incidencia) {
            $indiceGrado = $this->indiceGradoDashboard(
                $incidencia->estudiante?->seccion?->grado
            );
            $nombreGravedad = $incidencia->gravedad?->nombre;

            if ($indiceGrado !== null && isset($conteos[$indiceGrado][$nombreGravedad])) {
                $conteos[$indiceGrado][$nombreGravedad]++;
            }
        }

        $resumen = [];
        foreach ($nombresGrados as $indice => $nombreGrado) {
            $total = array_sum($conteos[$indice]);
            $porcentajes = [];

            foreach ($gravedades as $nombreGravedad) {
                $cantidad = $conteos[$indice][$nombreGravedad];
                $porcentajes[$nombreGravedad] = $total > 0
                    ? round($cantidad / $total * 100, 1)
                    : 0;
            }

            $resumen[] = [
                'grado' => $nombreGrado,
                'cantidades' => $conteos[$indice],
                'porcentajes' => $porcentajes,
                'total' => $total,
            ];
        }

        return $resumen;
    }

    private function indiceGradoDashboard(?string $grado): ?int
    {
        if ($grado === null) {
            return null;
        }

        if (preg_match('/(?<!\d)([1-5])(?!\d)/u', $grado, $coincidencia) === 1) {
            return (int) $coincidencia[1] - 1;
        }

        $gradoNormalizado = mb_strtolower($grado);
        $ordinales = [
            ['primer', 'primero', 'primera'],
            ['segundo', 'segunda'],
            ['tercer', 'tercero', 'tercera'],
            ['cuarto', 'cuarta'],
            ['quinto', 'quinta'],
        ];

        foreach ($ordinales as $indice => $variantes) {
            foreach ($variantes as $variante) {
                if (str_contains($gradoNormalizado, $variante)) {
                    return $indice;
                }
            }
        }

        return null;
    }

}