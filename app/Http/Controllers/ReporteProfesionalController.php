<?php

namespace App\Http\Controllers;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Incidencia;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReporteProfesionalController extends Controller
{
    public function index(Request $request)
    {
        $usuario = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        $rol = $usuario->rol?->nombre_rol;

        if ($rol === 'Profesor') {
            return view('reporte-profesional.index', [
                'grados' => $this->gradosDisponibles($usuario),
                'reporte' => null,
            ]);
        }

        abort_unless(in_array($rol, ['Administrador', 'Directora', 'Subdirector'], true), 403);

        return view('reporte-profesional.institucional', [
            'grados' => Seccion::query()
                ->select('grado')
                ->distinct()
                ->orderBy('grado')
                ->pluck('grado'),
            'cursos' => Curso::query()->orderBy('nombre')->get(['id', 'nombre']),
            'seccionesDisponibles' => Seccion::query()
                ->orderBy('grado')
                ->orderBy('seccion')
                ->get(['id', 'grado', 'seccion']),
            'reporteDirectivo' => null,
        ]);
    }

    public function generar(Request $request)
    {
        $profesor = $this->profesor($request);
        $asignaciones = AsignacionDocente::query()
            ->with(['curso:id,nombre', 'seccion:id,grado,seccion'])
            ->where('usuario_id', $profesor->id)
            ->get();

        $request->merge([
            'grado' => trim((string) $request->input('grado', '')),
        ]);

        $datos = Validator::make($request->all(), [
            'grado' => ['required', 'string'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
            'secciones' => ['required', 'array', 'min:1'],
            'secciones.*' => ['required', 'integer', 'distinct', 'exists:secciones,id'],
        ], [
            'curso_id.required' => 'Seleccione un curso.',
            'curso_id.exists' => 'El curso seleccionado no existe.',
            'secciones.required' => 'Seleccione al menos una sección.',
            'secciones.min' => 'Seleccione al menos una sección.',
            'secciones.*.exists' => 'Una sección seleccionada no existe.',
        ])->validate();

        $secciones = Seccion::query()
            ->whereIn('id', $datos['secciones'])
            ->get(['id', 'grado', 'seccion']);

        if ($secciones->count() !== count($datos['secciones'])) {
            throw ValidationException::withMessages([
                'secciones' => 'Una de las secciones seleccionadas no es válida.',
            ]);
        }

        if ($secciones->contains(fn (Seccion $seccion) => $seccion->grado !== $datos['grado'])) {
            throw ValidationException::withMessages([
                'secciones' => 'Las secciones deben pertenecer al grado seleccionado.',
            ]);
        }

        if ($asignaciones->isNotEmpty()) {
            $cursoAsignado = $asignaciones->contains(
                fn (AsignacionDocente $asignacion) => (int) $asignacion->curso_id === (int) $datos['curso_id']
            );
            $seccionesAsignadas = $asignaciones
                ->where('curso_id', (int) $datos['curso_id'])
                ->pluck('seccion_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (!$cursoAsignado) {
                throw ValidationException::withMessages([
                    'curso_id' => 'El curso no está asignado a su cuenta.',
                ]);
            }

            if (count(array_diff(array_map('intval', $datos['secciones']), $seccionesAsignadas)) > 0) {
                throw ValidationException::withMessages([
                    'secciones' => 'Una sección no está asignada a su cuenta para este curso.',
                ]);
            }
        }

        $curso = Curso::findOrFail($datos['curso_id']);
        $seccionesIds = $secciones->pluck('id')->map(fn ($id) => (int) $id)->all();

        $incidencias = Incidencia::query()
            ->with(['gravedad:id,nombre', 'estado:id,nombre', 'estudiante.seccion:id,grado,seccion', 'curso:id,nombre'])
            ->where('usuario_id', $profesor->id)
            ->where('curso_id', $curso->id)
            ->where('es_reservada', false)
            ->whereHas('estudiante', fn ($query) => $query->whereIn('seccion_id', $seccionesIds))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        $reporte = $this->armarReporte(
            $datos['grado'],
            $curso,
            $secciones,
            $incidencias
        );

        return view('reporte-profesional.index', [
            'grados' => $this->gradosDisponibles($profesor),
            'reporte' => $reporte,
        ]);
    }

    private function profesor(Request $request): User
    {
        $profesor = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        abort_unless($profesor->rol?->nombre_rol === 'Profesor', 403);

        return $profesor;
    }

    private function gradosDisponibles(User $profesor)
    {
        $asignaciones = AsignacionDocente::query()
            ->with(['curso:id,nombre', 'seccion:id,grado,seccion'])
            ->where('usuario_id', $profesor->id)
            ->get();

        if ($asignaciones->isNotEmpty()) {
            $grados = $asignaciones
                ->map(fn (AsignacionDocente $asignacion) => $asignacion->seccion?->grado)
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $datosPorGrado = [];
            foreach ($grados as $grado) {
                $cursos = $asignaciones
                    ->filter(fn (AsignacionDocente $asignacion) => $asignacion->seccion?->grado === $grado)
                    ->map(fn (AsignacionDocente $asignacion) => $asignacion->curso)
                    ->filter()
                    ->unique('id')
                    ->sortBy('nombre')
                    ->values();

                $cursosData = [];
                foreach ($cursos as $curso) {
                    $secciones = $asignaciones
                        ->filter(fn (AsignacionDocente $asignacion) =>
                            $asignacion->seccion?->grado === $grado
                            && (int) $asignacion->curso_id === (int) $curso->id
                        )
                        ->map(fn (AsignacionDocente $asignacion) => $asignacion->seccion)
                        ->filter()
                        ->unique('id')
                        ->sortBy('seccion')
                        ->values();

                    $cursosData[] = [
                        'id' => $curso->id,
                        'nombre' => $curso->nombre,
                        'secciones' => $secciones,
                    ];
                }

                $datosPorGrado[] = [
                    'nombre' => $grado,
                    'cursos' => collect($cursosData),
                ];
            }

            return collect($datosPorGrado);
        }

        $grados = Seccion::query()
            ->whereNotNull('grado')
            ->distinct()
            ->orderBy('grado')
            ->pluck('grado');
        $cursos = Curso::query()->orderBy('nombre')->get(['id', 'nombre']);

        return $grados->map(function (string $grado) use ($cursos): array {
            $secciones = Seccion::query()
                ->where('grado', $grado)
                ->orderBy('seccion')
                ->get(['id', 'grado', 'seccion']);

            $cursosData = $cursos->map(fn (Curso $curso): array => [
                'id' => $curso->id,
                'nombre' => $curso->nombre,
                'secciones' => $secciones,
            ]);

            return [
                'nombre' => $grado,
                'cursos' => $cursosData,
            ];
        });
    }

    private function armarReporte(
        string $grado,
        Curso $curso,
        $secciones,
        $incidencias
    ): array
    {
        $gravedades = ['Leve', 'Intermedia', 'Grave'];
        $estados = ['Pendiente', 'En Proceso', 'Finalizado'];

        $cantidadesGravedad = array_fill_keys($gravedades, 0);
        $cantidadesEstado = array_fill_keys($estados, 0);
        $comparativa = $secciones->mapWithKeys(fn (Seccion $seccion) => [
            $seccion->id => [
                'seccion' => $seccion->seccion,
                'total' => 0,
                'gravedades' => array_fill_keys($gravedades, 0),
                'estados' => array_fill_keys($estados, 0),
            ],
        ]);

        $hoy = Carbon::now();
        $valoresMes = array_fill(0, 12, 0);
        $labelsMes = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        foreach ($incidencias as $incidencia) {
            $nombreGravedad = trim((string) ($incidencia->gravedad?->nombre));
            if (isset($cantidadesGravedad[$nombreGravedad])) {
                $cantidadesGravedad[$nombreGravedad]++;
            }

            $nombreEstado = trim((string) ($incidencia->estado?->nombre));
            if (isset($cantidadesEstado[$nombreEstado])) {
                $cantidadesEstado[$nombreEstado]++;
            }

            $seccionId = $incidencia->estudiante?->seccion_id;
            if ($seccionId !== null && $comparativa->has($seccionId)) {
                $fila = $comparativa->get($seccionId);
                $fila['total']++;
                if (isset($fila['gravedades'][$nombreGravedad])) {
                    $fila['gravedades'][$nombreGravedad]++;
                }
                if (isset($fila['estados'][$nombreEstado])) {
                    $fila['estados'][$nombreEstado]++;
                }
                $comparativa->put($seccionId, $fila);
            }

            if ($incidencia->fecha) {
                $fecha = Carbon::parse($incidencia->fecha);
                if ($fecha->year === $hoy->year) {
                    $valoresMes[$fecha->month - 1]++;
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

        return [
            'grado' => $grado,
            'curso' => $curso->nombre,
            'secciones' => $secciones->pluck('seccion')->all(),
            'total' => $total,
            'gravedades' => [
                'cantidades' => $cantidadesGravedad,
                'porcentajes' => $porcentajesGravedad,
            ],
            'estados' => [
                'cantidades' => $cantidadesEstado,
                'porcentajes' => $porcentajesEstado,
            ],
            'comparativa' => $comparativa->values()->all(),
            'mensual' => [
                'labels' => $labelsMes,
                'valores' => $valoresMes,
            ],
        ];
    }
}
