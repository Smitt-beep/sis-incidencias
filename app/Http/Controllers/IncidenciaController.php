<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Incidencia;
use App\Models\Seccion;
use App\Models\Gravedad;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function index()
    {
        $incidencias = Incidencia::with([
            'estudiante.seccion',
            'curso',
            'usuario',
            'gravedad',
            'estado'
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('incidencias.index', compact('incidencias'));
    }

    public function create()
    {   //obtiene los cursos para mostrarlos en el formulario.
        $cursos = Curso::orderBy('nombre')->get();
        //obtiene las secciones ordenadas por grado y seccion.
        $secciones = Seccion::orderBy('grado')->orderBy('seccion')->get();
        //obtiene los estudiantes junto con su seccion
        $estudiantes = Estudiante::with('seccion')->orderBy('nombre')->get();
        //obtiene sus gravedades registradas en la base de datos
        $gravedades = Gravedad::orderByRaw("
        CASE
            WHEN nombre = 'Leve' THEN 1
            WHEN nombre = 'Intermedia' THEN 2
            WHEN nombre = 'Grave' THEN 3
             ELSE 4
              END
              ")->get();
        //obtiene sus estados registradas en la base de datos
        $estados = Estado::orderByRaw("
        CASE
                WHEN nombre = 'Pendiente' THEN 1
                WHEN nombre = 'En Proceso' THEN 2
                WHEN nombre = 'Finalizado' THEN 3
                ELSE 4
            END
        ")->get();
        return view('incidencias.create', compact(
            'cursos',
            'secciones',
            'estudiantes',
            'gravedades',
            'estados',
        ));
    }

    public function store(Request $request)
    {
        $datos = $this->validatedData($request);
        $datos['usuario_id'] = $request->session()->get('usuario_id');

        Incidencia::create($datos);

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia registrada correctamente.');
    }

    public function show(Incidencia $incidencia)
    {
        $incidencia->load(['estudiante.seccion', 'curso', 'usuario', 'gravedad', 'estado']);

        return view('incidencias.show', compact('incidencia'));
    }

    public function edit(Incidencia $incidencia)
    {
        $incidencia->load(['estudiante.seccion', 'curso', 'usuario', 'gravedad', 'estado']);
        $cursos = Curso::orderBy('nombre')->get();
        $secciones = Seccion::orderBy('grado')->orderBy('seccion')->get();
        $estudiantes = Estudiante::with('seccion')->orderBy('nombre')->get();
        //obtiene las opciones disponibles en gravedad
        $gravedades = Gravedad::orderByRaw("
        CASE
            WHEN nombre = 'Leve' THEN 1
            WHEN nombre = 'Intermedia' THEN 2
            WHEN nombre = 'Grave' THEN 3
            ELSE 4
            END
            ")->get();

        $estados = Estado::orderByRaw("
        CASE
            WHEN nombre = 'Pendiente' THEN 1
            WHEN nombre = 'En Proceso' THEN 2
            WHEN nombre = 'Finalizado' THEN 3
            ELSE 4
        END
          ")->get();
        return view('incidencias.edit', compact('incidencia', 'cursos', 'secciones', 'estudiantes', 'gravedades', 'estados'));
    }

    public function update(Request $request, Incidencia $incidencia)
    {
        $incidencia->update($this->validatedData($request));

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia actualizada correctamente.');
    }

    public function destroy(Incidencia $incidencia)
    {
        $incidencia->delete();

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia eliminada correctamente.');
    }

    private function validatedData(Request $request): array
    {
        $sectionId = $request->input('seccion_id');

        $datos = $request->validate([
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'estudiante_id' => [
                'required',
                'integer',
                Rule::exists('estudiantes', 'id')->where(function ($query) use ($sectionId) {
                    if (is_numeric($sectionId)) {
                        $query->where('seccion_id', (int) $sectionId);
                    }
                }),
            ],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i,H:i:s'],
            'gravedad_id' => ['required', 'integer', 'exists:gravedades,id'],
            'descripcion' => ['required', 'string'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
        ], [
            'curso_id.required' => 'Seleccione un curso.',
            'curso_id.exists' => 'El curso seleccionado no existe.',
            'seccion_id.required' => 'Seleccione una sección.',
            'seccion_id.exists' => 'La sección seleccionada no existe.',
            'estudiante_id.required' => 'Seleccione un estudiante.',
            'estudiante_id.exists' => 'El estudiante no existe o no pertenece a la sección seleccionada.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'Ingrese una fecha válida.',
            'hora.required' => 'La hora es obligatoria.',
            'hora.date_format' => 'Ingrese una hora válida.',
            'gravedad_id.required' => 'La gravedad es obligatoria.',
            'gravedad_id.exists' => 'La gravedad seleccionada no existe.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'estado_id.required' => 'El estado es obligatorio.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
        ]);

        unset($datos['seccion_id']);

        return $datos;
    }
}
