<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
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

        $secciones = $rol === 'Profesor'
            ? $usuario->seccionesDocentes()->orderBy('grado')->orderBy('seccion')->get()
            : Seccion::query()->orderBy('grado')->orderBy('seccion')->get();
        $query = Estudiante::with('seccion');

        if ($rol === 'Profesor') {
            $query->whereIn('seccion_id', $secciones->modelKeys());
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

        return view('estudiantes.index', compact('secciones', 'estudiantes', 'buscar', 'seccionId', 'rol'));
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

        $datos = $request->validate([
            'dni' => ['required', 'digits:8', 'unique:estudiantes,dni'],
            'nombre' => ['required', 'string', 'max:255'],
            'seccion_id' => [
                'required',
                'integer',
                Rule::exists('asignaciones_docentes', 'seccion_id')
                    ->where(fn ($query) => $query->where('usuario_id', $profesor->getKey())),
            ],
        ], [
            'dni.required' => 'El DNI del alumno es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 números.',
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
    }

    public function update(Request $request, Estudiante $estudiante)
    {
    }

    public function destroy(Estudiante $estudiante)
    {
    }
}