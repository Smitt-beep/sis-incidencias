<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Incidencia;
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

        if ($usuario->rol?->nombre_rol === 'Profesor') {
            $graficos = $this->datosDashboardProfesor($usuario);
        } elseif (in_array($usuario->rol?->nombre_rol, ['Administrador', 'Directora', 'Subdirector'], true)) {
            $graficos = $this->datosDashboardAdmin();
        }

        return view('dashboard', compact('graficos'));
    }

    /**
     * Arma los datos de los gráficos de incidencias del profesor:
     * una vista semanal (por día) y una mensual (por semana del mes),
     * cada una con su desglose de gravedad (Leve/Intermedia/Grave).
     */
    private function datosDashboardProfesor(User $usuario): array
    {
        $hoy = Carbon::now();

        // --- Semana actual: lunes a domingo ---
        $inicioSemana = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $finSemana = $hoy->copy()->endOfWeek(Carbon::SUNDAY);

        $incidenciasSemana = Incidencia::with('gravedad:id,nombre')
            ->where('usuario_id', $usuario->getKey())
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id']);

        $diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $valoresSemana = array_fill(0, 7, 0);

        foreach ($incidenciasSemana as $incidencia) {
            $indiceDia = Carbon::parse($incidencia->fecha)->dayOfWeekIso - 1; // 0 = Lunes
            $valoresSemana[$indiceDia]++;
        }

        // --- Mes actual, dividido en semanas de 7 días (Semana 1, 2, 3...) ---
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $incidenciasMes = Incidencia::with('gravedad:id,nombre')
            ->where('usuario_id', $usuario->getKey())
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id']);

        $totalSemanasMes = (int) ceil($finMes->day / 7);
        $labelsMes = [];
        for ($i = 1; $i <= $totalSemanasMes; $i++) {
            $labelsMes[] = "Semana {$i}";
        }
        $valoresMes = array_fill(0, $totalSemanasMes, 0);

        foreach ($incidenciasMes as $incidencia) {
            $diaDelMes = Carbon::parse($incidencia->fecha)->day;
            $indiceSemana = (int) ceil($diaDelMes / 7) - 1;
            $valoresMes[$indiceSemana]++;
        }

        return [
            'semana' => [
                'labels' => $diasSemana,
                'valores' => $valoresSemana,
                'gravedad' => $this->conteoPorGravedad($incidenciasSemana),
            ],
            'mes' => [
                'labels' => $labelsMes,
                'valores' => $valoresMes,
                'gravedad' => $this->conteoPorGravedad($incidenciasMes),
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

        $inicioSemana = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $finSemana = $hoy->copy()->endOfWeek(Carbon::SUNDAY);
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $incSemana = Incidencia::with([
            'gravedad:id,nombre',
            'curso:id,nombre',
            'estudiante.seccion:id,grado',
        ])
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'curso_id', 'estudiante_id', 'tipo_aula']);

        $incMes = Incidencia::with([
            'gravedad:id,nombre',
            'curso:id,nombre',
            'estudiante.seccion:id,grado',
        ])
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get(['id', 'fecha', 'gravedad_id', 'curso_id', 'estudiante_id', 'tipo_aula']);

        return [
            'tipo' => 'admin',
            'semana' => [
                'porGrado' => $this->conteosPorGradoAdmin($incSemana),
                'porGradoApilado' => $this->seriesPorGravedadAdmin(
                    $incSemana,
                    fn ($incidencia) => $incidencia->estudiante?->seccion?->grado
                ),
                'porCursoTeoria' => $this->conteosPorCursoAdmin($incSemana, 'teoria'),
                'porCursoTeoriaApilado' => $this->seriesPorGravedadAdmin(
                    $incSemana,
                    fn ($incidencia) => $incidencia->tipo_aula === 'teoria'
                        ? $incidencia->curso?->nombre
                        : null
                ),
                'porCursoTaller' => $this->conteosPorCursoAdmin($incSemana, 'taller'),
                'porCursoTallerApilado' => $this->seriesPorGravedadAdmin(
                    $incSemana,
                    fn ($incidencia) => $incidencia->tipo_aula === 'taller'
                        ? $incidencia->curso?->nombre
                        : null
                ),
                'gravedad' => $this->conteoPorGravedad($incSemana),
            ],
            'mes' => [
                'porGrado' => $this->conteosPorGradoAdmin($incMes),
                'porGradoApilado' => $this->seriesPorGravedadAdmin(
                    $incMes,
                    fn ($incidencia) => $incidencia->estudiante?->seccion?->grado
                ),
                'porCursoTeoria' => $this->conteosPorCursoAdmin($incMes, 'teoria'),
                'porCursoTeoriaApilado' => $this->seriesPorGravedadAdmin(
                    $incMes,
                    fn ($incidencia) => $incidencia->tipo_aula === 'teoria'
                        ? $incidencia->curso?->nombre
                        : null
                ),
                'porCursoTaller' => $this->conteosPorCursoAdmin($incMes, 'taller'),
                'porCursoTallerApilado' => $this->seriesPorGravedadAdmin(
                    $incMes,
                    fn ($incidencia) => $incidencia->tipo_aula === 'taller'
                        ? $incidencia->curso?->nombre
                        : null
                ),
                'gravedad' => $this->conteoPorGravedad($incMes),
            ],
        ];
    }

    private function seriesPorGravedadAdmin(Collection $incidencias, callable $categoria): array
    {
        $conteos = $incidencias
            ->filter(fn ($incidencia) => $categoria($incidencia) !== null)
            ->groupBy(fn ($incidencia) => $categoria($incidencia))
            ->sortKeys(SORT_NATURAL);

        $series = [];
        foreach (['Leve', 'Intermedia', 'Grave'] as $nombreGravedad) {
            $series[$nombreGravedad] = $conteos
                ->map(fn (Collection $grupo) => $grupo
                    ->filter(fn ($incidencia) => $incidencia->gravedad?->nombre === $nombreGravedad)
                    ->count())
                ->values()
                ->all();
        }

        return [
            'labels' => $conteos->keys()->values()->all(),
            'series' => $series,
        ];
    }

    private function conteosPorGradoAdmin(Collection $incidencias): array
    {
        $conteos = $incidencias
            ->filter(fn ($incidencia) => $incidencia->estudiante?->seccion?->grado !== null)
            ->groupBy(fn ($incidencia) => $incidencia->estudiante->seccion->grado)
            ->map(fn (Collection $grupo) => $grupo->count())
            ->sortKeys(SORT_NATURAL);

        return [
            'labels' => $conteos->keys()->values()->all(),
            'valores' => $conteos->values()->all(),
        ];
    }

    private function conteosPorCursoAdmin(Collection $incidencias, string $tipoAula): array
    {
        $conteos = $incidencias
            ->filter(fn ($incidencia) =>
                $incidencia->tipo_aula === $tipoAula
                && $incidencia->curso?->nombre !== null
            )
            ->groupBy(fn ($incidencia) => $incidencia->curso->nombre)
            ->map(fn (Collection $grupo) => $grupo->count())
            ->sortKeys();

        return [
            'labels' => $conteos->keys()->values()->all(),
            'valores' => $conteos->values()->all(),
        ];
    }
}