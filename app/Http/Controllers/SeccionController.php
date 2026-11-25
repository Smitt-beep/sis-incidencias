<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SeccionController extends Controller
{
    public function index(Request $request): View
    {
        $grados = Seccion::query()
            ->select('grado')
            ->distinct()
            ->orderBy('grado')
            ->pluck('grado');
        $cursosPorGrado = [];

        foreach ($grados as $grado) {
            $cursosPorGrado[$grado] = [];
        }

        $cursosRegistrados = DB::table('users')
            ->join('cursos', 'users.curso_registro_id', '=', 'cursos.id')
            ->whereIn('users.grado_registro', $grados)
            ->whereIn('users.estado_aprobacion', ['Pendiente', 'Aprobado'])
            ->select(
                'users.grado_registro as grado',
                'cursos.id as curso_id',
                'cursos.nombre'
            )
            ->distinct()
            ->get();

        foreach ($cursosRegistrados as $curso) {
            $cursosPorGrado[$curso->grado][$curso->curso_id] = [
                'id' => $curso->curso_id,
                'nombre' => $curso->nombre,
                'secciones' => [],
            ];
        }

        $asignaciones = AsignacionDocente::query()
            ->with(['curso:id,nombre', 'seccion:id,grado,seccion'])
            ->whereHas('seccion', fn ($query) => $query->whereIn('grado', $grados))
            ->get(['curso_id', 'seccion_id']);

        foreach ($asignaciones as $asignacion) {
            $grado = $asignacion->seccion->grado;
            $cursoId = $asignacion->curso_id;

            if (!isset($cursosPorGrado[$grado][$cursoId])) {
                $cursosPorGrado[$grado][$cursoId] = [
                    'id' => $cursoId,
                    'nombre' => $asignacion->curso->nombre,
                    'secciones' => [],
                ];
            }

            $cursosPorGrado[$grado][$cursoId]['secciones'][$asignacion->seccion_id] = [
                'id' => $asignacion->seccion_id,
                'nombre' => $asignacion->seccion->seccion,
            ];
        }

        $seccionesPorGrado = Seccion::query()
            ->orderBy('seccion')
            ->get(['id', 'grado', 'seccion'])
            ->groupBy('grado');

        $grados = $grados->map(function (string $grado) use ($cursosPorGrado, $seccionesPorGrado): array {
            $cursos = collect($cursosPorGrado[$grado] ?? [])
                ->map(function (array $curso): array {
                    $curso['secciones'] = collect($curso['secciones'])
                        ->sortBy('nombre')
                        ->values();

                    return $curso;
                })
                ->sortBy('nombre')
                ->values();

            return [
                'nombre' => $grado,
                'cursos' => $cursos,
                'secciones' => $seccionesPorGrado->get($grado, collect()),
            ];
        });

        return view('secciones.index', compact('grados'));
    }

    public function reporteProfesional(Request $request): View
    {
        $usuario = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        abort_unless(
            in_array($usuario->rol?->nombre_rol, ['Administrador', 'Directora', 'Subdirector'], true),
            403
        );

        $gradoIngresado = trim((string) $request->input('grado', ''));
        $cursoIngresado = $request->input('curso_id');
        $anioIngresado = $request->input('anio');
        $request->merge([
            'grado' => $gradoIngresado === '' || strtolower($gradoIngresado) === 'todos'
                ? null
                : $gradoIngresado,
            'curso_id' => is_string($cursoIngresado) && trim($cursoIngresado) === ''
                ? null
                : $cursoIngresado,
            'anio' => is_string($anioIngresado) && trim($anioIngresado) === ''
                ? null
                : $anioIngresado,
        ]);

        $datos = $request->validate([
            'grado' => ['nullable', 'string'],
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
            'secciones' => ['nullable', 'array'],
            'secciones.*' => ['integer', 'distinct', 'exists:secciones,id'],
            'anio' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $gradoFiltro = $datos['grado'] ?? null;
        $cursoId = isset($datos['curso_id']) ? (int) $datos['curso_id'] : null;
        $anio = isset($datos['anio']) ? (int) $datos['anio'] : (int) now()->year;
        $seccionesIds = collect($datos['secciones'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($gradoFiltro !== null && $seccionesIds->isNotEmpty()) {
            $seccionesValidas = Seccion::query()
                ->where('grado', $gradoFiltro)
                ->whereIn('id', $seccionesIds)
                ->count();
            if ($seccionesValidas !== $seccionesIds->count()) {
                throw ValidationException::withMessages([
                    'secciones' => 'Las secciones deben pertenecer al grado seleccionado.',
                ]);
            }
        }

        $incidencias = Incidencia::query()
            ->with([
                'gravedad:id,nombre',
                'estado:id,nombre',
                'curso:id,nombre',
                'estudiante:id,seccion_id',
                'estudiante.seccion:id,grado,seccion',
            ])
            ->select(['id', 'fecha', 'curso_id', 'gravedad_id', 'estado_id', 'estudiante_id'])
            ->whereYear('fecha', $anio)
            ->when($cursoId !== null, fn ($query) => $query->where('curso_id', $cursoId))
            ->when(
                $seccionesIds->isNotEmpty(),
                fn ($query) => $query->whereHas(
                    'estudiante',
                    fn ($queryEstudiante) => $queryEstudiante->whereIn('seccion_id', $seccionesIds)
                )
            )
            ->when(
                $gradoFiltro !== null,
                fn ($query) => $query->whereHas(
                    'estudiante.seccion',
                    fn ($querySeccion) => $querySeccion->where('grado', $gradoFiltro)
                )
            )
            ->get();

        $gravedades = ['Leve', 'Intermedia', 'Grave'];
        $estados = ['Pendiente', 'En Proceso', 'Finalizado'];
        $cantidadesGravedad = array_fill_keys($gravedades, 0);
        $cantidadesEstado = array_fill_keys($estados, 0);
        $porCurso = [];
        $mensual = array_fill(0, 12, 0);
        $comparativa = [];
        $pendientesEnProceso = 0;
        $gravesActivas = 0;

        foreach ($incidencias as $incidencia) {
            $nombreGravedad = trim((string) ($incidencia->gravedad?->nombre));
            $gravedadNormalizada = mb_strtolower($nombreGravedad);
            if (isset($cantidadesGravedad[$nombreGravedad])) {
                $cantidadesGravedad[$nombreGravedad]++;
            }

            $nombreEstado = trim((string) ($incidencia->estado?->nombre));
            $estadoNormalizado = mb_strtolower($nombreEstado);
            if (isset($cantidadesEstado[$nombreEstado])) {
                $cantidadesEstado[$nombreEstado]++;
            }
            if (in_array($estadoNormalizado, ['pendiente', 'en proceso'], true)) {
                $pendientesEnProceso++;
                if ($gravedadNormalizada === 'grave') {
                    $gravesActivas++;
                }
            }

            $nombreCurso = trim((string) ($incidencia->curso?->nombre ?: 'Sin curso'));
            $porCurso[$nombreCurso] ??= [
                'curso' => $nombreCurso,
                'total' => 0,
                'Leve' => 0,
                'Intermedia' => 0,
                'Grave' => 0,
            ];
            $porCurso[$nombreCurso]['total']++;
            if (isset($porCurso[$nombreCurso][$nombreGravedad])) {
                $porCurso[$nombreCurso][$nombreGravedad]++;
            }

            if ($incidencia->fecha) {
                $indiceMes = (int) Carbon::parse($incidencia->fecha)->month - 1;
                if (array_key_exists($indiceMes, $mensual)) {
                    $mensual[$indiceMes]++;
                }
            }
        }

        $total = $incidencias->count();
        $porcentajesGravedad = [];
        foreach ($gravedades as $nombre) {
            $porcentajesGravedad[$nombre] = $total > 0
                ? round($cantidadesGravedad[$nombre] / $total * 100, 1)
                : 0;
        }
        $porcentajesEstado = [];
        foreach ($estados as $nombre) {
            $porcentajesEstado[$nombre] = $total > 0
                ? round($cantidadesEstado[$nombre] / $total * 100, 1)
                : 0;
        }

        if ($gradoFiltro === null) {
            $gradosComparativos = Seccion::query()
                ->whereNotNull('grado')
                ->distinct()
                ->orderBy('grado')
                ->pluck('grado');
            foreach ($gradosComparativos as $nombreGrado) {
                $incidenciasGrado = $incidencias->filter(fn ($incidencia) =>
                    $incidencia->estudiante?->seccion?->grado === $nombreGrado
                );
                $comparativa[] = $this->filaComparativaInstitucional(
                    $nombreGrado,
                    $incidenciasGrado,
                    $gravedades
                );
            }
        } else {
            $seccionesComparativas = $seccionesIds->isNotEmpty()
                ? Seccion::query()->whereIn('id', $seccionesIds)->orderBy('seccion')->get()
                : Seccion::query()->where('grado', $gradoFiltro)->orderBy('seccion')->get();

            foreach ($seccionesComparativas as $seccion) {
                $incidenciasSeccion = $incidencias->filter(fn ($incidencia) =>
                    (int) $incidencia->estudiante?->seccion_id === (int) $seccion->id
                );
                $comparativa[] = $this->filaComparativaInstitucional(
                    'Sección '.$seccion->seccion,
                    $incidenciasSeccion,
                    $gravedades
                );
            }
        }

        usort($porCurso, fn (array $a, array $b): int =>
            ($b['total'] <=> $a['total']) ?: strnatcasecmp($a['curso'], $b['curso'])
        );

        $reporte = [
            'autoridad' => $request->session()->get('usuario_rol'),
            'docente' => $request->session()->get('usuario_nombre'),
            'grado' => $gradoFiltro,
            'curso_id' => $cursoId,
            'anio' => $anio,
            'alcance' => $gradoFiltro === null ? 'Institucional / Todos los grados' : $gradoFiltro,
            'secciones' => $seccionesIds->all(),
            'seccionesNombres' => Seccion::query()
                ->whereIn('id', $seccionesIds)
                ->orderBy('grado')
                ->orderBy('seccion')
                ->get(['grado', 'seccion'])
                ->map(fn (Seccion $seccion): string => $seccion->grado.' '.$seccion->seccion)
                ->all(),
            'total' => $total,
            'gestionActiva' => [
                'pendientesEnProceso' => $pendientesEnProceso,
                'gravesActivas' => $gravesActivas,
            ],
            'gravedades' => [
                'cantidades' => $cantidadesGravedad,
                'porcentajes' => $porcentajesGravedad,
            ],
            'estados' => [
                'cantidades' => $cantidadesEstado,
                'porcentajes' => $porcentajesEstado,
            ],
            'comparativa' => $comparativa,
            'porCurso' => array_values($porCurso),
            'mensual' => [
                'labels' => [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
                ],
                'valores' => $mensual,
            ],
        ];

        $grados = Seccion::query()
            ->select('grado')
            ->distinct()
            ->orderBy('grado')
            ->pluck('grado');
        $cursos = Curso::query()->orderBy('nombre')->get(['id', 'nombre']);
        $seccionesDisponibles = Seccion::query()
            ->orderBy('grado')
            ->orderBy('seccion')
            ->get(['id', 'grado', 'seccion']);

        return view('reporte-profesional.institucional', [
            'grados' => $grados,
            'cursos' => $cursos,
            'seccionesDisponibles' => $seccionesDisponibles,
            'reporteDirectivo' => $reporte,
        ]);
    }

    private function filaComparativaInstitucional(
        string $nombre,
        Collection $incidencias,
        array $gravedades
    ): array {
        $cantidades = array_fill_keys($gravedades, 0);
        foreach ($incidencias as $incidencia) {
            $nombreGravedad = trim((string) ($incidencia->gravedad?->nombre));
            if (isset($cantidades[$nombreGravedad])) {
                $cantidades[$nombreGravedad]++;
            }
        }

        return [
            'nombre' => $nombre,
            'total' => $incidencias->count(),
            'Leve' => $cantidades['Leve'],
            'Intermedia' => $cantidades['Intermedia'],
            'Grave' => $cantidades['Grave'],
        ];
    }

    public function create(): View
    {
        return view('secciones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('secciones', 'seccion')
                    ->where(fn ($query) => $query->where('grado', $request->input('grado'))),
            ],
        ], [
            'grado.required' => 'El grado es obligatorio.',
            'seccion.required' => 'La sección es obligatoria.',
            'seccion.unique' => 'Ya existe una sección con ese grado y nombre.',
        ]);

        Seccion::create($validated);

        return redirect()->route('secciones.index')->with('success', 'Sección registrada correctamente.');
    }

    public function edit(Seccion $seccion): View
    {
        return view('secciones.edit', compact('seccion'));
    }

    public function update(Request $request, Seccion $seccion)
    {
        $validated = $request->validate([
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('secciones', 'seccion')
                    ->where(fn ($query) => $query->where('grado', $request->input('grado')))
                    ->ignore($seccion->id),
            ],
        ], [
            'grado.required' => 'El grado es obligatorio.',
            'seccion.required' => 'La sección es obligatoria.',
            'seccion.unique' => 'Ya existe una sección con ese grado y nombre.',
        ]);

        $seccion->update($validated);

        return redirect()->route('secciones.index')->with('success', 'Sección actualizada correctamente.');
    }
}