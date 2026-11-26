<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Seccion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $usuario = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        $rol = $usuario->rol?->nombre_rol;
        abort_unless(in_array($rol, ['Profesor', 'Directora', 'Subdirector'], true), 403);
        $contextoProfesor = $rol === 'Profesor'
            ? $this->contextoDashboardProfesor($usuario)
            : null;

        $secciones = $rol === 'Profesor'
            ? $usuario->seccionesDocentes()->orderBy('grado')->orderBy('seccion')->get()
            : Seccion::query()->orderBy('grado')->orderBy('seccion')->get();
        if ($rol === 'Profesor' && $secciones->isEmpty()) {
            $secciones = Seccion::query()->orderBy('grado')->orderBy('seccion')->get();
        }
        $query = Estudiante::with('seccion');

        if ($rol === 'Profesor') {
            $query->whereIn('seccion_id', $secciones->modelKeys());
            if ($contextoProfesor === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('seccion_id', $contextoProfesor['seccion_id']);
            }
        }

        $buscar = trim((string) $request->input('buscar', ''));
        $seccionId = $request->input('seccion_id');

        if ($seccionId !== null && $seccionId !== '') {
            $request->validate([
                'seccion_id' => [
                    'integer',
                    Rule::in($secciones->modelKeys()),
                ],
            ]);
            $query->where('seccion_id', (int) $seccionId);
        }

        if ($buscar !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($estudiantesQuery) use ($buscar, $operator) {
                $estudiantesQuery
                    ->where('nombre', $operator, '%'.$buscar.'%')
                    ->orWhere('dni', 'like', '%'.$buscar.'%');
            });
        }

        $estudiantes = $query->orderBy('nombre')->paginate(25)->withQueryString();
        $contextoProfesorActivo = $contextoProfesor !== null;

        return view('estudiantes.index', compact(
            'secciones',
            'estudiantes',
            'buscar',
            'seccionId',
            'rol',
            'contextoProfesorActivo'
        ));
    }

    private function contextoDashboardProfesor(User $usuario): ?array
    {
        $contexto = session()->get('contexto_dashboard_profesor_'.$usuario->getKey());
        if (
            !is_array($contexto)
            || trim((string) ($contexto['grado'] ?? '')) === ''
            || !is_numeric($contexto['seccion_id'] ?? null)
            || !is_numeric($contexto['curso_id'] ?? null)
        ) {
            return null;
        }

        $seccion = Seccion::query()->find((int) $contexto['seccion_id']);
        if (
            !$seccion
            || $seccion->grado !== trim((string) $contexto['grado'])
            || !Curso::query()->whereKey((int) $contexto['curso_id'])->exists()
        ) {
            return null;
        }

        return [
            'seccion_id' => (int) $contexto['seccion_id'],
            'curso_id' => (int) $contexto['curso_id'],
        ];
    }

    public function reporteSeleccionar(Request $request, Estudiante $estudiante)
    {
        $this->authorizeReporteEstudiante($request, $estudiante);
        $incidencias = $this->incidenciasDeEstudiante($estudiante);

        return view('estudiantes.reporte-seleccionar', compact('estudiante', 'incidencias'));
    }

    public function reportePdf(Request $request, Estudiante $estudiante)
    {
        $this->authorizeReporteEstudiante($request, $estudiante);
        $datos = $request->validate([
            'incidencia_ids' => ['required', 'array', 'min:1'],
            'incidencia_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $incidencias = $this->incidenciasDeEstudiante($estudiante, $datos['incidencia_ids']);
        if ($incidencias->count() !== count($datos['incidencia_ids'])) {
            throw ValidationException::withMessages([
                'incidencia_ids' => 'Seleccione incidencias válidas para este alumno.',
            ]);
        }

        return $this->descargarReportePdf($estudiante, $incidencias);
    }

    public function reportePdfTodas(Request $request, Estudiante $estudiante)
    {
        $this->authorizeReporteEstudiante($request, $estudiante);

        return $this->descargarReportePdf($estudiante, $this->incidenciasDeEstudiante($estudiante));
    }

    private function authorizeReporteEstudiante(Request $request, Estudiante $estudiante): void
    {
        $usuario = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        $rol = $usuario->rol?->nombre_rol;

        if ($rol === 'Profesor') {
            abort_unless(
                $usuario->seccionesDocentes()->whereKey($estudiante->seccion_id)->exists(),
                404
            );
            return;
        }

        abort_unless(in_array($rol, ['Directora', 'Subdirector'], true), 403);
    }

    private function incidenciasDeEstudiante(Estudiante $estudiante, ?array $ids = null): EloquentCollection
    {
        return $estudiante->incidencias()
            ->where('es_reservada', false) // las actas reservadas no salen en el reporte al apoderado
            ->with(['gravedad', 'estado', 'curso'])
            ->when($ids !== null, fn ($query) => $query->whereIn('id', $ids))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();
    }

    private function descargarReportePdf(Estudiante $estudiante, EloquentCollection $incidencias)
    {
        $estudiante->loadMissing('seccion');

        return Pdf::loadView('estudiantes.reporte-pdf', compact('estudiante', 'incidencias'))
            ->setPaper('a4')
            ->download('reporte-'.$estudiante->dni.'.pdf');
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        $profesor = User::findOrFail($request->session()->get('usuario_id'));
        $seccionesAsignadas = $profesor->seccionesDocentes()->get();
        $seccionIds = $seccionesAsignadas->isEmpty()
            ? Seccion::query()->pluck('id')->all()
            : $seccionesAsignadas->modelKeys();

        $datos = $request->validate([
            'dni' => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
                'unique:estudiantes,dni',
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'seccion_id' => [
                'required',
                'integer',
                Rule::in($seccionIds),
            ],
        ], [
            'dni.required' => 'El DNI del alumno es obligatorio.',
            'dni.regex' => 'El documento solo puede contener letras, números y guiones.',
            'dni.min' => 'El documento debe tener al menos 6 caracteres.',
            'dni.max' => 'El documento no puede tener más de 20 caracteres.',
            'dni.unique' => 'Ya existe un alumno registrado con ese DNI.',
            'nombre.required' => 'El nombre del alumno es obligatorio.',
            'seccion_id.required' => 'Seleccione una sección.',
            'seccion_id.exists' => 'La sección seleccionada no está asignada a su cuenta.',
        ]);

        Estudiante::create($datos);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Alumno registrado correctamente.');
    }

    public function show(Estudiante $estudiante)
    {
    }

    public function edit(Estudiante $estudiante)
    {
        $usuario = User::with('rol')->findOrFail(request()->session()->get('usuario_id'));
        abort_unless($usuario->rol?->nombre_rol === 'Profesor', 403);

        $seccionesAsignadas = $usuario->seccionesDocentes()->get();
        $secciones = $seccionesAsignadas->isEmpty()
            ? Seccion::query()->orderBy('grado')->orderBy('seccion')->get()
            : Seccion::query()
                ->whereIn('id', $seccionesAsignadas->modelKeys())
                ->orderBy('grado')
                ->orderBy('seccion')
                ->get();

        abort_unless($secciones->contains('id', $estudiante->seccion_id), 404);

        return view('estudiantes.edit', compact('estudiante', 'secciones'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $profesor = User::findOrFail($request->session()->get('usuario_id'));
        $seccionesAsignadas = $profesor->seccionesDocentes()->get();
        $seccionIds = $seccionesAsignadas->isEmpty()
            ? Seccion::query()->pluck('id')->all()
            : $seccionesAsignadas->modelKeys();

        abort_unless(in_array((int) $estudiante->seccion_id, array_map('intval', $seccionIds), true), 404);

        $datos = $request->validate([
            'dni' => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('estudiantes', 'dni')->ignore($estudiante->id),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'seccion_id' => [
                'required',
                'integer',
                Rule::in($seccionIds),
            ],
        ], [
            'dni.required' => 'El documento del alumno es obligatorio.',
            'dni.regex' => 'El documento solo puede contener letras, números y guiones.',
            'dni.min' => 'El documento debe tener al menos 6 caracteres.',
            'dni.max' => 'El documento no puede tener más de 20 caracteres.',
            'dni.unique' => 'Ya existe un alumno registrado con ese documento.',
            'nombre.required' => 'El nombre del alumno es obligatorio.',
            'seccion_id.required' => 'Seleccione una sección.',
            'seccion_id.in' => 'La sección seleccionada no está asignada a su cuenta.',
        ]);

        $estudiante->update($datos);

        return redirect()->route('estudiantes.index')
            ->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
    }
}