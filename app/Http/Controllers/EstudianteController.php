<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
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
        $profesor = User::findOrFail($request->session()->get('usuario_id'));
        $secciones = $profesor->seccionesDocentes()
            ->orderBy('grado')
            ->orderBy('seccion')
            ->get();
        $query = Estudiante::with('seccion')
            ->whereIn('seccion_id', $secciones->modelKeys());
        $buscar = trim((string) $request->input('buscar', ''));

        if ($buscar !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($estudiantesQuery) use ($buscar, $operator) {
                $estudiantesQuery
                    ->where('nombre', $operator, '%'.$buscar.'%')
                    ->orWhere('dni', 'like', '%'.$buscar.'%');
            });
        }

        $estudiantes = $query->orderBy('nombre')->get();

        return view('estudiantes.index', compact('secciones', 'estudiantes', 'buscar'));
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
        $profesor = User::findOrFail($request->session()->get('usuario_id'));
        abort_unless(
            $profesor->seccionesDocentes()->whereKey($estudiante->seccion_id)->exists(),
            404
        );
    }

    private function incidenciasDeEstudiante(Estudiante $estudiante, ?array $ids = null): EloquentCollection
    {
        return $estudiante->incidencias()
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