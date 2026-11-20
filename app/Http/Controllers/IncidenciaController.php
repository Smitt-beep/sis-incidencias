<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Incidencia;
use App\Models\User;
use App\Models\Seccion;
use App\Models\Gravedad;
use App\Models\Estado;
use App\Models\TipoIncidencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;
use Closure;

class IncidenciaController extends Controller
{
    public function createActa(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('createActa', Incidencia::class);

        $secciones = Seccion::query()->orderBy('grado')->orderBy('seccion')->get();
        $estudiantes = Estudiante::with('seccion')->orderBy('nombre')->limit(50)->get();

        return view('incidencias.acta-create', compact('secciones', 'estudiantes'));
    }

    public function editActa(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'update');
        abort_unless($incidencia->es_reservada, 404);
        $incidencia->load(['estudiante.seccion', 'acta.agresores']);
        abort_unless($incidencia->acta, 404);

        $secciones = Seccion::query()->orderBy('grado')->orderBy('seccion')->get();
        $estudiantes = Estudiante::with('seccion')->orderBy('nombre')->limit(50)->get();
        if (!$estudiantes->contains('id', $incidencia->estudiante_id)) {
            $estudiantes->prepend($incidencia->estudiante);
        }

        return view('incidencias.acta-create', compact('incidencia', 'secciones', 'estudiantes'));
    }

    public function buscarEstudiantesActa(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('createActa', Incidencia::class);

        $buscar = trim((string) $request->input('buscar', ''));
        $seccionId = $request->input('seccion_id');
        $query = Estudiante::with('seccion')->orderBy('nombre');

        if (is_numeric($seccionId)) {
            $query->where('seccion_id', (int) $seccionId);
        }
        if ($buscar !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function (Builder $q) use ($buscar, $operator) {
                $q->where('nombre', $operator, '%'.$buscar.'%')
                    ->orWhere('dni', $operator, '%'.$buscar.'%');
            });
        }

        return response()->json([
            'estudiantes' => $query->limit(30)->get()->map(fn (Estudiante $estudiante) => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->nombre,
                'dni' => $estudiante->dni,
                'seccion_id' => $estudiante->seccion_id,
                'grado' => $estudiante->seccion?->grado,
                'seccion' => $estudiante->seccion?->seccion,
            ]),
        ]);
    }

    public function storeActa(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('createActa', Incidencia::class);

        $datos = $this->validatedActaData($request);
        $fechaHora = now();

        $incidencia = DB::transaction(function () use ($datos, $fechaHora, $user) {
            $gravedad = Gravedad::whereRaw('LOWER(nombre) = ?', ['grave'])->first();
            $estado = Estado::whereRaw('LOWER(nombre) = ?', ['pendiente'])->first();
            if (!$gravedad || !$estado) {
                throw ValidationException::withMessages([
                    'base' => 'Debe existir gravedad Grave y estado Pendiente para registrar el acta.',
                ]);
            }

            $incidencia = Incidencia::create([
                'estudiante_id' => $datos['estudiante_id'],
                'usuario_id' => $user->getKey(),
                'curso_id' => null,
                'fecha' => $fechaHora->toDateString(),
                'hora' => $fechaHora->format('H:i:s'),
                'tipo_aula' => null,
                'descripcion' => null,
                'gravedad_id' => $gravedad->id,
                'estado_id' => $estado->id,
                'es_reservada' => true,
            ]);

            $acta = $incidencia->acta()->create($this->actaAttributes($datos));

            foreach ($datos['agresores'] ?? [] as $agresor) {
                $atributosAgresor = $this->agresorAttributes($agresor);
                if ($atributosAgresor !== null) {
                    $acta->agresores()->create($atributosAgresor);
                }
            }

            return $incidencia;
        });

        return redirect()->route('incidencias.show', $incidencia)
            ->with('success', 'Acta de incidencia registrada correctamente.');
    }

    public function updateActa(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'update');
        abort_unless($incidencia->es_reservada, 404);
        $datos = $this->validatedActaData($request);

        DB::transaction(function () use ($incidencia, $datos): void {
            $incidenciaBloqueada = Incidencia::query()
                ->whereKey($incidencia->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($incidenciaBloqueada->es_reservada, 404);
            $acta = $incidenciaBloqueada->acta()->lockForUpdate()->firstOrFail();

            $incidenciaBloqueada->update(['estudiante_id' => $datos['estudiante_id']]);
            $acta->update($this->actaAttributes($datos));
            $acta->agresores()->delete();

            foreach ($datos['agresores'] ?? [] as $agresor) {
                $atributosAgresor = $this->agresorAttributes($agresor);
                if ($atributosAgresor !== null) {
                    $acta->agresores()->create($atributosAgresor);
                }
            }
        });

        return redirect()->route('incidencias.show', $incidencia)
            ->with('success', 'Acta de incidencia actualizada correctamente.');
    }

    private function validatedActaData(Request $request): array
    {
        $reglas = [
            'estudiante_id' => ['required', 'integer', 'exists:estudiantes,id'],
            'ie' => ['nullable', 'string', 'max:255'],
            'dre' => ['nullable', 'string', 'max:255'],
            'ugel' => ['nullable', 'string', 'max:255'],
            'sisve_fecha' => ['nullable', 'date'],
            'sisve_nro_reporte' => ['nullable', 'string', 'max:50'],
            'tipo_reporte' => ['nullable', 'in:A,B,C,D'],
            'informante_nombres' => ['nullable', 'string', 'max:255'],
            'informante_parentesco' => ['nullable', 'string', 'max:30'],
            'informante_parentesco_otro' => ['nullable', 'string', 'max:255'],
            'informante_dni' => ['nullable', 'string', 'max:20'],
            'informante_domicilio' => ['nullable', 'string', 'max:255'],
            'informante_telefono' => ['nullable', 'string', 'max:30'],
            'informante_correo' => ['nullable', 'email', 'max:255'],
            'agredido_nombres_iniciales' => ['nullable', 'string', 'max:255'],
            'agredido_edad' => ['nullable', 'integer', 'between:1,99'],
            'agredido_sexo' => ['nullable', 'in:M,F'],
            'agredido_turno' => ['nullable', 'in:M,T,N'],
            'agredido_telefono' => ['nullable', 'string', 'max:30'],
            'agredido_discapacidad' => ['nullable', 'boolean'],
            'agredido_discapacidad_detalle' => ['nullable', 'string', 'max:255'],
            'tipos_violencia' => ['nullable', 'array'],
            'tipos_violencia.*' => ['in:fisico,sexual,verbal,bullying,ciberbullying,otro'],
            'violencia_otro' => ['nullable', 'string', 'max:255'],
            'agresores' => ['nullable', 'array', 'max:4'],
            'agresores.*.tipo' => ['required', 'in:estudiante,adulto'],
            'agresores.*.nombres' => ['nullable', 'string', 'max:255'],
            'agresores.*.edad' => ['nullable', 'integer', 'between:1,99'],
            'agresores.*.sexo' => ['nullable', 'in:M,F'],
            'agresores.*.dni' => ['nullable', 'string', 'max:20'],
            'agresores.*.relacion_grado' => ['nullable', 'in:mismo_grado,mayor,menor'],
            'agresores.*.grado_seccion' => ['nullable', 'string', 'max:30'],
            'agresores.*.turno' => ['nullable', 'in:M,T,N'],
            'agresores.*.cargo' => ['nullable', 'in:director,docente,administrativo,personal_apoyo,otro'],
            'agresores.*.cargo_otro' => ['nullable', 'string', 'max:255'],
        ];
        if (in_array('sexual', (array) $request->input('tipos_violencia', []), true)) {
            $reglas['agredido_nombres_iniciales'] = [
                'required',
                'string',
                'max:255',
                'regex:/^\\p{L}\\.?(?:[\\s.]+\\p{L}\\.?)*$/u',
            ];
        }

        $datos = $request->validate($reglas, [
            'agredido_nombres_iniciales.required' => 'Indique las iniciales de la persona agredida en casos de violencia sexual.',
            'agredido_nombres_iniciales.regex' => 'En casos de violencia sexual, registre solo iniciales (por ejemplo: A. B.).',
        ]);
        array_walk_recursive($datos, static function (&$value): void {
            if ($value === '') {
                $value = null;
            }
        });

        return $datos;
    }

    private function actaAttributes(array $datos): array
    {
        return Arr::only($datos, [
            'ie', 'dre', 'ugel', 'sisve_fecha', 'sisve_nro_reporte', 'tipo_reporte',
            'informante_nombres', 'informante_parentesco', 'informante_parentesco_otro',
            'informante_dni', 'informante_domicilio', 'informante_telefono', 'informante_correo',
            'agredido_nombres_iniciales', 'agredido_edad', 'agredido_sexo', 'agredido_turno',
            'agredido_telefono', 'agredido_discapacidad', 'agredido_discapacidad_detalle',
            'tipos_violencia', 'violencia_otro',
        ]);
    }

    private function agresorAttributes(array $agresor): ?array
    {
        $hasData = collect($agresor)
            ->except(['tipo'])
            ->contains(fn ($value) => $value !== null && $value !== '');

        return $hasData ? $agresor : null;
    }

    public function pdfActa(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'imprimirActa');
        $incidencia->load(['estudiante.seccion', 'usuario', 'acta.agresores']);

        return Pdf::loadView('incidencias.acta-pdf', compact('incidencia'))
            ->setPaper('a4', 'portrait')
            ->download('acta-incidencia-'.$incidencia->id.'.pdf');
    }

    public function index(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('viewAny', Incidencia::class);

        $query = $this->incidenciasQuery();
        if ($user->rol?->nombre_rol === 'Profesor') {
            // Sus propias incidencias + las actas reservadas de alumnos de sus secciones
            // (estas últimas se muestran solo con la etiqueta "Reservado").
            $seccionesAsignadas = $user->asignacionesDocentes()->pluck('seccion_id');
            $query->where(function (Builder $q) use ($user, $seccionesAsignadas) {
                $q->where('usuario_id', $user->getKey())
                    ->orWhere(function (Builder $reservadas) use ($seccionesAsignadas) {
                        $reservadas->where('es_reservada', true)
                            ->whereHas('estudiante', fn (Builder $e) => $e->whereIn('seccion_id', $seccionesAsignadas));
                    });
            });
        }

        $buscar = trim((string) $request->input('buscar', ''));
        if ($buscar !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('estudiante', fn (Builder $estudianteQuery) => $estudianteQuery
                ->where('nombre', $operator, '%'.$buscar.'%'));
        }

        $incidencias = $query->paginate(15)->withQueryString();
        $esUrgencias = false;

        return view('incidencias.index', compact('incidencias', 'esUrgencias', 'buscar'));
    }

    public function urgencias(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('viewAny', Incidencia::class);

        $incidencias = $this->incidenciasQuery()
            ->whereHas('gravedad', function (Builder $query) {
                $query->whereRaw('LOWER(nombre) = ?', ['grave']);
            })
            ->whereHas('estado', function (Builder $query) {
                $query->whereRaw('LOWER(nombre) IN (?, ?)', ['pendiente', 'en proceso']);
            })
            ->paginate(15);
        $esUrgencias = true;

        return view('incidencias.index', compact('incidencias', 'esUrgencias'));
    }

    private function incidenciasQuery(): Builder
    {
        return Incidencia::with([
            'estudiante.seccion',
            'curso',
            'usuario',
            'gravedad',
            'estado'
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('hora');
    }

    public function create(Request $request)
    {
        $user = $this->sessionUser($request);
        $formOptions = $this->incidenceFormOptions($user);
        $tiposIncidencia = TipoIncidencia::orderBy('nombre')->get();
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
        return view('incidencias.create', array_merge($formOptions, compact(
            'tiposIncidencia',
            'gravedades',
            'estados',
        )));
    }

    public function store(Request $request)
    {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize('create', Incidencia::class);

        $datos = $this->validatedData($request, $user);
        $fotografias = $this->validatedPhotographs($request);
        $tipos = $request->validate([
            'tipos' => ['required', 'array', 'min:1'],
            'tipos.*' => ['required', 'integer', 'distinct', 'exists:tipos_incidencia,id'],
        ], [
            'tipos.required' => 'Seleccione al menos un tipo de incidencia.',
            'tipos.array' => 'La selección de tipos no es válida.',
            'tipos.min' => 'Seleccione al menos un tipo de incidencia.',
            'tipos.*.required' => 'Cada tipo seleccionado debe ser válido.',
            'tipos.*.integer' => 'Cada tipo seleccionado debe ser válido.',
            'tipos.*.distinct' => 'No puede seleccionar el mismo tipo más de una vez.',
            'tipos.*.exists' => 'Uno de los tipos seleccionados ya no está disponible.',
        ]);

        $this->withPhotographCompensation(function (array &$storedPaths) use ($datos, $tipos, $fotografias, $user) {
            DB::transaction(function () use ($datos, $tipos, $fotografias, $user, &$storedPaths) {
                $fechaHora = now();
                $datos['fecha'] = $fechaHora->toDateString();
                $datos['hora'] = $fechaHora->format('H:i:s');
                $datos['usuario_id'] = $user->getKey();

                $incidencia = Incidencia::create($datos);
                $incidencia->tiposIncidencia()->attach($tipos['tipos']);
                $this->assertPhotographLimit($incidencia, $fotografias);
                $this->storePhotographs($incidencia, $fotografias, $storedPaths);
            });
        });

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia registrada correctamente.');
    }

    public function show(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'view');

        $incidencia->load([
            'estudiante.seccion',
            'curso',
            'usuario',
            'gravedad',
            'estado',
            'tiposIncidencia',
            'fotografias',
        ]);

        return view('incidencias.show', compact('incidencia'));
    }

    public function edit(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'update');

        $incidencia->load([
            'estudiante.seccion',
            'curso',
            'usuario',
            'gravedad',
            'estado',
            'tiposIncidencia',
            'fotografias',
        ]);
        $user = $this->sessionUser($request);
        $formOptions = $this->incidenceFormOptions($user);
        $tiposIncidencia = TipoIncidencia::orderBy('nombre')->get();
        $tiposIncidenciaSeleccionados = $incidencia->tiposIncidencia->pluck('id')->all();
        $fotografiasActuales = $incidencia->fotografias->count();
        $fotografiasDisponibles = max(0, 5 - $fotografiasActuales);
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
        return view('incidencias.edit', array_merge($formOptions, compact(
            'incidencia',
            'tiposIncidencia',
            'tiposIncidenciaSeleccionados',
            'fotografiasActuales',
            'fotografiasDisponibles',
            'gravedades',
            'estados'
        )));
    }

    public function update(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'update');

        $user = $this->sessionUser($request);
        $datos = $this->validatedData($request, $user);
        $fotografias = $this->validatedPhotographs($request);
        $tipos = $request->validate([
            'tipos' => ['required', 'array', 'min:1'],
            'tipos.*' => ['required', 'integer', 'distinct', 'exists:tipos_incidencia,id'],
        ], [
            'tipos.required' => 'Seleccione al menos un tipo de incidencia.',
            'tipos.array' => 'La selección de tipos no es válida.',
            'tipos.min' => 'Seleccione al menos un tipo de incidencia.',
            'tipos.*.required' => 'Cada tipo seleccionado debe ser válido.',
            'tipos.*.integer' => 'Cada tipo seleccionado debe ser válido.',
            'tipos.*.distinct' => 'No puede seleccionar el mismo tipo más de una vez.',
            'tipos.*.exists' => 'Uno de los tipos seleccionados ya no está disponible.',
        ]);

        $this->withPhotographCompensation(function (array &$storedPaths) use ($incidencia, $datos, $tipos, $fotografias) {
            DB::transaction(function () use ($incidencia, $datos, $tipos, $fotografias, &$storedPaths) {
                $incidenciaBloqueada = Incidencia::query()
                    ->whereKey($incidencia->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertPhotographLimit($incidenciaBloqueada, $fotografias);
                $incidenciaBloqueada->update($datos);
                $incidenciaBloqueada->tiposIncidencia()->sync($tipos['tipos']);
                $this->storePhotographs($incidenciaBloqueada, $fotografias, $storedPaths);
            });
        });

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia actualizada correctamente.');
    }

    public function destroy(Request $request, Incidencia $incidencia)
    {
        $this->authorizeIncidencia($request, $incidencia, 'delete');

        $user = $this->sessionUser($request);

        DB::transaction(function () use ($incidencia, $user): void {
            $incidencia->eliminado_por = $user->getKey();
            $incidencia->save();
            $incidencia->delete();
        });

        return redirect()->route('incidencias.index')
            ->with('success', 'Incidencia eliminada correctamente.');
    }

    private function authorizeIncidencia(
        Request $request,
        Incidencia $incidencia,
        string $ability
    ): void {
        $user = $this->sessionUser($request);
        Gate::forUser($user)->authorize($ability, $incidencia);
    }

    private function sessionUser(Request $request): User
    {
        return User::with('rol')->findOrFail($request->session()->get('usuario_id'));
    }

    private function incidenceFormOptions(User $user): array
    {
        $esProfesor = $user->rol?->nombre_rol === 'Profesor';
        $asignaciones = $esProfesor
            ? $user->asignacionesDocentes()->get(['curso_id', 'seccion_id'])
            : collect();

        $cursosQuery = Curso::query()->orderBy('nombre');
        $seccionesQuery = Seccion::query()->orderBy('grado')->orderBy('seccion');
        $estudiantesQuery = Estudiante::with('seccion')->orderBy('nombre');
        $sectionCourseIds = [];

        if ($esProfesor) {
            $courseIds = $asignaciones->pluck('curso_id')->unique()->all();
            $sectionIds = $asignaciones->pluck('seccion_id')->unique()->all();
            $cursosQuery->whereIn('id', $courseIds);
            $seccionesQuery->whereIn('id', $sectionIds);
            $estudiantesQuery->whereIn('seccion_id', $sectionIds);
            $sectionCourseIds = $asignaciones
                ->groupBy('seccion_id')
                ->map(fn ($rows) => $rows->pluck('curso_id')->map(fn ($id) => (string) $id)->all())
                ->all();
        }

        return [
            'cursos' => $cursosQuery->get(),
            'secciones' => $seccionesQuery->get(),
            'estudiantes' => $estudiantesQuery->get(),
            'sectionCourseIds' => $sectionCourseIds,
            'esProfesor' => $esProfesor,
        ];
    }

    private function validatedData(Request $request, User $user): array
    {
        $sectionId = $request->input('seccion_id');
        $courseId = $request->input('curso_id');
        $courseIdForRule = is_numeric($courseId) ? (int) $courseId : 0;
        $esProfesor = $user->rol?->nombre_rol === 'Profesor';
        $courseRule = $esProfesor
            ? Rule::exists('asignaciones_docentes', 'curso_id')
                ->where(fn ($query) => $query->where('usuario_id', $user->getKey()))
            : Rule::exists('cursos', 'id');
        $sectionRule = $esProfesor
            ? Rule::exists('asignaciones_docentes', 'seccion_id')
                ->where(fn ($query) => $query
                    ->where('usuario_id', $user->getKey())
                    ->where('curso_id', $courseIdForRule))
            : Rule::exists('secciones', 'id');

        $datos = $request->validate([
            'curso_id' => ['required', 'integer', $courseRule],
            'seccion_id' => ['required', 'integer', $sectionRule],
            'estudiante_id' => [
                'required',
                'integer',
                Rule::exists('estudiantes', 'id')->where(function ($query) use ($sectionId) {
                    if (is_numeric($sectionId)) {
                        $query->where('seccion_id', (int) $sectionId);
                    }
                }),
            ],
            'gravedad_id' => ['required', 'integer', 'exists:gravedades,id'],
            'tipo_aula' => ['required', Rule::in(['teoria', 'taller'])],
            'descripcion' => ['required', 'string'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
        ], [
            'curso_id.required' => 'Seleccione un curso.',
            'curso_id.exists' => 'El curso seleccionado no existe o no está asignado a su cuenta.',
            'seccion_id.required' => 'Seleccione una sección.',
            'seccion_id.exists' => 'La sección no existe o no está asignada a su cuenta para el curso elegido.',
            'estudiante_id.required' => 'Seleccione un estudiante.',
            'estudiante_id.exists' => 'El estudiante no existe o no pertenece a la sección seleccionada.',
            'gravedad_id.required' => 'La gravedad es obligatoria.',
            'gravedad_id.exists' => 'La gravedad seleccionada no existe.',
            'tipo_aula.required' => 'Seleccione el tipo de aula.',
            'tipo_aula.in' => 'Seleccione un tipo de aula válido.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'estado_id.required' => 'El estado es obligatorio.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
        ]);

        unset($datos['seccion_id']);

        return $datos;
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function validatedPhotographs(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'fotografias' => ['nullable', 'array', 'max:5'],
            'fotografias.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'fotografias.array' => 'Seleccione fotografías válidas.',
            'fotografias.max' => 'Puede seleccionar como máximo 5 fotografías.',
            'fotografias.*.image' => 'Cada archivo debe ser una imagen válida.',
            'fotografias.*.mimes' => 'Las fotografías deben estar en formato JPG, JPEG, PNG o WebP.',
            'fotografias.*.max' => 'Cada fotografía no debe superar los 5 MB.',
        ])->validate();

        return array_values(array_filter($validated['fotografias'] ?? []));
    }

    /**
     * @param array<int, UploadedFile> $fotografias
     */
    private function assertPhotographLimit(Incidencia $incidencia, array $fotografias): void
    {
        $actuales = $incidencia->fotografias()->count();

        if ($actuales + count($fotografias) > 5) {
            throw ValidationException::withMessages([
                'fotografias' => "La incidencia ya tiene {$actuales} fotografías. El máximo permitido es 5.",
            ]);
        }
    }

    /**
     * @param array<int, UploadedFile> $fotografias
     * @param array<int, string> $storedPaths
     */
    private function storePhotographs(Incidencia $incidencia, array $fotografias, array &$storedPaths): void
    {
        $disk = Storage::disk('evidencias');

        foreach ($fotografias as $fotografia) {
            $tipoMime = $fotografia->getMimeType();
            $extension = match ($tipoMime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => throw new RuntimeException('El tipo MIME de la fotografía no es compatible.'),
            };
            $tamano = $fotografia->getSize();

            if (!is_int($tamano)) {
                throw new RuntimeException('No se pudo determinar el tamaño de una fotografía.');
            }

            $nombreArchivo = Str::uuid().'.'.$extension;
            $ruta = 'incidencias/'.$nombreArchivo;

            $storedPaths[] = $ruta;
            $rutaGuardada = $fotografia->storeAs('incidencias', $nombreArchivo, 'evidencias');

            if ($rutaGuardada !== $ruta || !$disk->exists($ruta)) {
                throw new RuntimeException('No se pudo guardar una fotografía en el disco privado de evidencias.');
            }

            $incidencia->fotografias()->create([
                'ruta_archivo' => $ruta,
                'nombre_original' => $this->normalizeOriginalPhotographName(
                    $fotografia->getClientOriginalName()
                ),
                'tipo_mime' => $tipoMime,
                'tamano' => $tamano,
            ]);
        }
    }

    private function normalizeOriginalPhotographName(string $nombreOriginal): string
    {
        $nombreOriginal = basename(str_replace('\\', '/', $nombreOriginal));

        if ($nombreOriginal === '') {
            $nombreOriginal = 'fotografia';
        }

        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $sufijoExtension = $extension !== ''
            && in_array(strtolower($extension), $extensionesPermitidas, true)
                ? '.'.$extension
                : '';
        $nombreBase = $sufijoExtension === ''
            ? $nombreOriginal
            : substr($nombreOriginal, 0, -strlen($sufijoExtension));
        $longitudMaximaBase = 255 - Str::length($sufijoExtension);

        return Str::substr($nombreBase, 0, $longitudMaximaBase).$sufijoExtension;
    }

    private function withPhotographCompensation(Closure $operation): void
    {
        $storedPaths = [];

        try {
            $operation($storedPaths);
        } catch (Throwable $exception) {
            $disk = Storage::disk('evidencias');

            foreach ($storedPaths as $path) {
                try {
                    if ($disk->exists($path) && !$disk->delete($path)) {
                        Log::error('No se pudo compensar el almacenamiento de una fotografía.', [
                            'path' => $path,
                        ]);
                    }
                } catch (Throwable $cleanupException) {
                    Log::error('Error al compensar el almacenamiento de una fotografía.', [
                        'path' => $path,
                        'exception' => $cleanupException,
                    ]);
                }
            }

            throw $exception;
        }
    }
}
