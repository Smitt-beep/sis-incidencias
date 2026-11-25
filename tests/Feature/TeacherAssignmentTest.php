<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre_rol');
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('dni', 8)->unique();
            $table->string('password');
            $table->foreignId('rol_id')->nullable();
            $table->string('grado_registro')->nullable();
            $table->unsignedBigInteger('curso_registro_id')->nullable();
            $table->string('estado_aprobacion')->default('Pendiente');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('secciones', function (Blueprint $table): void {
            $table->id();
            $table->string('grado');
            $table->string('seccion');
        });

        Schema::create('asignaciones_docentes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id');
            $table->foreignId('curso_id');
            $table->foreignId('seccion_id');
            $table->timestamps();
            $table->unique(['usuario_id', 'curso_id', 'seccion_id']);
        });

        Schema::create('estudiantes', function (Blueprint $table): void {
            $table->id();
            $table->string('dni');
            $table->string('nombre');
            $table->foreignId('seccion_id');
        });

        Schema::create('gravedades', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('estados', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('incidencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('usuario_id');
            $table->foreignId('curso_id')->nullable();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('tipo_aula')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('gravedad_id');
            $table->foreignId('estado_id');
            $table->boolean('es_reservada')->default(false);
            $table->softDeletes();
        });

        Schema::create('tipos_incidencia', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('incidencia_tipo', function (Blueprint $table): void {
            $table->foreignId('incidencia_id');
            $table->foreignId('tipo_incidencia_id');
        });

        Schema::create('incidencia_fotografias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incidencia_id');
            $table->string('ruta_archivo');
            $table->string('nombre_original');
            $table->string('tipo_mime');
            $table->unsignedBigInteger('tamano');
            $table->timestamps();
            $table->softDeletes();
        });

        Rol::create(['nombre_rol' => 'Administrador']);
        Rol::create(['nombre_rol' => 'Profesor']);
        Rol::create(['nombre_rol' => 'Directora']);
        Rol::create(['nombre_rol' => 'Subdirector']);

        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.test',
            'dni' => '12345678',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Administrador')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        $this->withSession([
            'usuario_id' => $admin->id,
            'usuario_rol' => 'Administrador',
        ]);
    }

    public function test_admin_can_create_teacher_with_sections_from_multiple_grades(): void
    {
        $curso = Curso::create(['nombre' => 'Matemática']);
        $seccionPrimero = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionSegundo = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);

        $this->post(route('usuarios.store'), [
            'name' => 'Profesor Nuevo',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'curso_id' => $curso->id,
            'grados' => ['1.º', '2.º'],
            'secciones' => [$seccionPrimero->id, $seccionSegundo->id],
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $profesor = User::where('dni', '87654321')->firstOrFail();

        $this->assertDatabaseCount('asignaciones_docentes', 2);
        $this->assertEqualsCanonicalizing(
            [$seccionPrimero->id, $seccionSegundo->id],
            $profesor->seccionesDocentes()->pluck('secciones.id')->all()
        );
        $this->assertDatabaseHas('asignaciones_docentes', [
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionSegundo->id,
        ]);
    }

    public function test_public_registration_does_not_request_academic_context_and_approval_does_not_assign_sections(): void
    {
        Curso::create(['nombre' => 'Comunicación']);
        Seccion::create(['grado' => '1.º', 'seccion' => 'A']);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="grado"', false)
            ->assertDontSee('name="curso_registro_id"', false);

        $registrationData = [
            'name' => 'Profesor Registrado',
            'email' => 'registro@example.test',
            'dni' => '23456789',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
        ];

        $this->post(route('register.store'), $registrationData)
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $profesor = User::where('dni', '23456789')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'id' => $profesor->id,
            'estado_aprobacion' => 'Pendiente',
        ]);

        $this->post(route('usuarios.aprobar', $profesor))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('asignaciones_docentes', 0);
    }

    public function test_assignment_rejects_sections_outside_the_selected_grades(): void
    {
        $curso = Curso::create(['nombre' => 'Ciencia']);
        Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccion = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);

        $this->from(route('usuarios.create'))
            ->post(route('usuarios.store'), [
                'name' => 'Profesor Inválido',
                'email' => 'invalido@example.test',
                'dni' => '87654321',
                'password' => 'clave-segura',
                'password_confirmation' => 'clave-segura',
                'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
                'curso_id' => $curso->id,
                'grados' => ['1.º', '2.º'],
                'secciones' => [$seccion->id],
            ])->assertRedirect(route('usuarios.create'))
            ->assertSessionHasErrors('secciones');

        $this->assertDatabaseMissing('users', ['dni' => '87654321']);
        $this->assertDatabaseCount('asignaciones_docentes', 0);
    }

    public function test_edit_replaces_only_the_selected_course_assignments(): void
    {
        $cursoSeleccionado = Curso::create(['nombre' => 'Historia']);
        $otroCurso = Curso::create(['nombre' => 'Arte']);
        $seccionAnterior = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionNueva = Seccion::create(['grado' => '1.º', 'seccion' => 'B']);
        $seccionOtroCurso = Seccion::create(['grado' => '2.º', 'seccion' => 'A']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoSeleccionado->id,
            'seccion_id' => $seccionAnterior->id,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $otroCurso->id,
            'seccion_id' => $seccionOtroCurso->id,
        ]);

        $this->put(route('usuarios.update', $profesor), [
            'name' => 'Profesor Actualizado',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => '',
            'password_confirmation' => '',
            'curso_id' => $cursoSeleccionado->id,
            'grados' => ['1.º'],
            'secciones' => [$seccionNueva->id],
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('asignaciones_docentes', [
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoSeleccionado->id,
            'seccion_id' => $seccionAnterior->id,
        ]);
        $this->assertDatabaseHas('asignaciones_docentes', [
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoSeleccionado->id,
            'seccion_id' => $seccionNueva->id,
        ]);
        $this->assertDatabaseHas('asignaciones_docentes', [
            'usuario_id' => $profesor->id,
            'curso_id' => $otroCurso->id,
            'seccion_id' => $seccionOtroCurso->id,
        ]);
    }

    public function test_professor_sees_only_assigned_courses_sections_and_students(): void
    {
        $cursoAsignado = Curso::create(['nombre' => 'Curso asignado']);
        $cursoNoAsignado = Curso::create(['nombre' => 'Curso no asignado']);
        $seccionAsignada = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionNoAsignada = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoAsignado->id,
            'seccion_id' => $seccionAsignada->id,
        ]);
        \DB::table('estudiantes')->insert([
            ['dni' => '11111111', 'nombre' => 'Estudiante asignado', 'seccion_id' => $seccionAsignada->id],
            ['dni' => '22222222', 'nombre' => 'Estudiante no asignado', 'seccion_id' => $seccionNoAsignada->id],
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->get(route('incidencias.create'))
            ->assertOk()
            ->assertSee($cursoAsignado->nombre)
            ->assertDontSee($cursoNoAsignado->nombre)
            ->assertSee($seccionAsignada->seccion)
            ->assertDontSee('2.º - B')
            ->assertSee('Estudiante asignado')
            ->assertDontSee('Estudiante no asignado');
    }

    public function test_professor_cannot_submit_a_section_assigned_to_a_different_course(): void
    {
        $cursoA = Curso::create(['nombre' => 'Curso A']);
        $cursoB = Curso::create(['nombre' => 'Curso B']);
        $seccionA = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionB = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoA->id,
            'seccion_id' => $seccionA->id,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $cursoB->id,
            'seccion_id' => $seccionB->id,
        ]);
        $estudiante = \DB::table('estudiantes')->insertGetId([
            'dni' => '11111111',
            'nombre' => 'Estudiante',
            'seccion_id' => $seccionB->id,
        ]);
        \DB::table('gravedades')->insert(['nombre' => 'Leve']);
        \DB::table('estados')->insert(['nombre' => 'Pendiente']);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->from(route('incidencias.create'))
            ->post(route('incidencias.store'), [
                'curso_id' => $cursoA->id,
                'seccion_id' => $seccionB->id,
                'estudiante_id' => $estudiante,
                'gravedad_id' => 1,
                'tipo_aula' => 'teoria',
                'descripcion' => 'Prueba de asignación',
                'estado_id' => 1,
            ])->assertRedirect(route('incidencias.create'))
            ->assertSessionHasErrors('seccion_id');
    }

    public function test_professor_can_add_students_to_assigned_sections_and_only_sees_their_roster(): void
    {
        $curso = Curso::create(['nombre' => 'Matemática']);
        $seccionAsignada = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionNoAsignada = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionAsignada->id,
        ]);
        \DB::table('estudiantes')->insert([
            'dni' => '11111111',
            'nombre' => 'Alumno de otra sección',
            'seccion_id' => $seccionNoAsignada->id,
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->get(route('estudiantes.index'))
            ->assertOk()
            ->assertSee('Añadir alumno')
            ->assertSee('1.º - A')
            ->assertDontSee('2.º - B')
            ->assertDontSee('Alumno de otra sección');

        $this->post(route('estudiantes.store'), [
            'dni' => '22222222',
            'nombre' => 'Alumno Nuevo',
            'seccion_id' => $seccionAsignada->id,
        ])
            ->assertRedirect(route('estudiantes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('estudiantes', [
            'dni' => '22222222',
            'nombre' => 'Alumno Nuevo',
            'seccion_id' => $seccionAsignada->id,
        ]);

        $this->get(route('estudiantes.index'))
            ->assertSee('Alumno Nuevo')
            ->assertDontSee('Alumno de otra sección');
    }

    public function test_professor_can_register_and_update_foreign_student_document(): void
    {
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor-ce@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->post(route('estudiantes.store'), [
            'dni' => 'CE-123456',
            'nombre' => 'Estudiante Extranjero',
            'seccion_id' => $seccion->id,
        ])
            ->assertRedirect(route('estudiantes.index'))
            ->assertSessionHas('success');

        $estudiante = Estudiante::where('dni', 'CE-123456')->firstOrFail();

        $this->put(route('estudiantes.update', $estudiante), [
            'dni' => 'X12345678',
            'nombre' => 'Estudiante Extranjero Actualizado',
            'seccion_id' => $seccion->id,
        ])
            ->assertRedirect(route('estudiantes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'dni' => 'X12345678',
            'nombre' => 'Estudiante Extranjero Actualizado',
        ]);
    }

    public function test_professor_cannot_add_student_to_unassigned_section(): void
    {
        $curso = Curso::create(['nombre' => 'Ciencia']);
        $seccionAsignada = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionNoAsignada = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionAsignada->id,
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->from(route('estudiantes.index'))
            ->post(route('estudiantes.store'), [
                'dni' => '22222222',
                'nombre' => 'Alumno No Autorizado',
                'seccion_id' => $seccionNoAsignada->id,
            ])
            ->assertRedirect(route('estudiantes.index'))
            ->assertSessionHasErrors('seccion_id');

        $this->assertDatabaseMissing('estudiantes', [
            'dni' => '22222222',
        ]);
    }

    public function test_only_professors_can_open_student_registration(): void
    {
        $this->get(route('estudiantes.index'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_grade_course_and_gravity_counts_for_both_periods(): void
    {
        $cursoTeoria = Curso::create(['nombre' => 'Theory Course']);
        $cursoTaller = Curso::create(['nombre' => 'Workshop Course']);
        $seccionPrimero = Seccion::create(['grado' => '1ro', 'seccion' => 'A']);
        $seccionSegundo = Seccion::create(['grado' => '2do', 'seccion' => 'A']);
        $estudiantePrimero = \DB::table('estudiantes')->insertGetId([
            'dni' => '11111111',
            'nombre' => 'Alumno Primero',
            'seccion_id' => $seccionPrimero->id,
        ]);
        $estudianteSegundo = \DB::table('estudiantes')->insertGetId([
            'dni' => '22222222',
            'nombre' => 'Alumno Segundo',
            'seccion_id' => $seccionSegundo->id,
        ]);
        $gravedades = [
            'Leve' => \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']),
            'Intermedia' => \DB::table('gravedades')->insertGetId(['nombre' => 'Intermedia']),
            'Grave' => \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']),
        ];
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $fecha = now();

        foreach ([
            [$estudiantePrimero, $cursoTeoria->id, $gravedades['Leve']],
            [$estudiantePrimero, $cursoTeoria->id, $gravedades['Grave']],
            [$estudianteSegundo, $cursoTaller->id, $gravedades['Intermedia']],
        ] as [$estudianteId, $cursoId, $gravedadId]) {
            \DB::table('incidencias')->insert([
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $cursoId,
                'fecha' => $fecha->toDateString(),
                'hora' => '08:00:00',
                'descripcion' => 'Dashboard test',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
            ]);
        }

        $indiceSemana = min(intdiv($fecha->day - 1, 7), 3);
        $labelsSemana = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        $valoresSemana = array_fill(0, count($labelsSemana), 0);
        $valoresSemana[$indiceSemana] = 3;
        $valoresGradoPrimeroSemana = array_fill(0, count($labelsSemana), 0);
        $valoresGradoPrimeroSemana[$indiceSemana] = 2;
        $valoresGradoSegundoSemana = array_fill(0, count($labelsSemana), 0);
        $valoresGradoSegundoSemana[$indiceSemana] = 1;
        $valoresSemanaCursoTeoria = $valoresGradoPrimeroSemana;
        $valoresSemanaCursoTaller = $valoresGradoSegundoSemana;
        $cursosPorGradoSemana = [
            [['curso' => 'Theory Course', 'valores' => $valoresSemanaCursoTeoria]],
            [['curso' => 'Workshop Course', 'valores' => $valoresSemanaCursoTaller]],
            [],
            [],
            [],
        ];

        $labelsMes = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];
        $indiceMes = $fecha->month - 1;
        $valoresMes = array_fill(0, count($labelsMes), 0);
        $valoresMes[$indiceMes] = 3;
        $valoresGradoPrimeroMes = array_fill(0, count($labelsMes), 0);
        $valoresGradoPrimeroMes[$indiceMes] = 2;
        $valoresGradoSegundoMes = array_fill(0, count($labelsMes), 0);
        $valoresGradoSegundoMes[$indiceMes] = 1;
        $cursosPorGradoMes = [
            [['curso' => 'Theory Course', 'valores' => $valoresGradoPrimeroMes]],
            [['curso' => 'Workshop Course', 'valores' => $valoresGradoSegundoMes]],
            [],
            [],
            [],
        ];

        $datosGravedad = [
            'cantidades' => ['Leve' => 1, 'Intermedia' => 1, 'Grave' => 1],
            'porcentajes' => ['Leve' => 33.3, 'Intermedia' => 33.3, 'Grave' => 33.3],
            'total' => 3,
        ];
        $datosGravedadPorGrado = [
            [
                'grado' => 'Primer grado',
                'cantidades' => ['Leve' => 1, 'Intermedia' => 0, 'Grave' => 1],
                'porcentajes' => ['Leve' => 50, 'Intermedia' => 0, 'Grave' => 50],
                'total' => 2,
            ],
            [
                'grado' => 'Segundo grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 1, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 100, 'Grave' => 0],
                'total' => 1,
            ],
            [
                'grado' => 'Tercer grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
            [
                'grado' => 'Cuarto grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
            [
                'grado' => 'Quinto grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
        ];
        $datosSemana = [
            'labels' => $labelsSemana,
            'valores' => $valoresSemana,
            'porGrado' => [
                ['grado' => '1ro', 'valores' => $valoresGradoPrimeroSemana],
                ['grado' => '2do', 'valores' => $valoresGradoSegundoSemana],
            ],
            'porCurso' => [
                ['curso' => 'Theory Course', 'valores' => $valoresSemanaCursoTeoria],
                ['curso' => 'Workshop Course', 'valores' => $valoresSemanaCursoTaller],
            ],
            'porCursoPorGrado' => $cursosPorGradoSemana,
            'gravedad' => $datosGravedad,
            'gravedadPorGrado' => $datosGravedadPorGrado,
            'general' => [
                'labels' => $labelsSemana,
                'valores' => $valoresSemana,
            ],
        ];
        $datosMes = [
            'labels' => $labelsMes,
            'valores' => $valoresMes,
            'porGrado' => [
                ['grado' => '1ro', 'valores' => $valoresGradoPrimeroMes],
                ['grado' => '2do', 'valores' => $valoresGradoSegundoMes],
            ],
            'porCurso' => [
                ['curso' => 'Theory Course', 'valores' => $valoresGradoPrimeroMes],
                ['curso' => 'Workshop Course', 'valores' => $valoresGradoSegundoMes],
            ],
            'porCursoPorGrado' => $cursosPorGradoMes,
            'gravedad' => $datosGravedad,
            'gravedadPorGrado' => $datosGravedadPorGrado,
            'general' => [
                'labels' => $labelsMes,
                'valores' => $valoresMes,
            ],
        ];

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('grafico-admin-general')
            ->assertSee('grafico-gravedad-grado-1')
            ->assertSee('grafico-gravedad-grado-2')
            ->assertSee('grafico-gravedad-grado-3')
            ->assertSee('grafico-gravedad-grado-4')
            ->assertSee('grafico-gravedad-grado-5')
            ->assertSee('grafico-admin-curso')
            ->assertSee('grado-admin-curso')
            ->assertSee('grafico-admin-gravedad')
            ->assertSee("type: 'line'", false)
            ->assertSee('#7C3AED')
            ->assertSee("type: 'doughnut'", false)
            ->assertSee('#0756A3')
            ->assertSee('#F5C542')
            ->assertSee('#dc3545')
            ->assertSee("type: 'pie'", false)
            ->assertSee("indexAxis: 'y'", false)
            ->assertSee('Theory Course')
            ->assertSee('Workshop Course')
            ->assertSee('"semana":' . json_encode($datosSemana), false)
            ->assertSee('"mes":' . json_encode($datosMes), false);

        $usuarioId = \DB::table('users')->where('dni', '12345678')->value('id');
        foreach (['Directora', 'Subdirector'] as $nombreRol) {
            $rolId = \DB::table('roles')->where('nombre_rol', $nombreRol)->value('id');
            \DB::table('users')->where('id', $usuarioId)->update(['rol_id' => $rolId]);

            $this->get(route('dashboard'))
                ->assertOk()
                ->assertSee('grafico-gravedad-grado-1')
                ->assertSee('grafico-gravedad-grado-5')
                ->assertSee('grafico-admin-curso')
                ->assertSee('grafico-admin-gravedad');
        }
    }

    public function test_professor_dashboard_uses_four_weeks_and_twelve_months_for_own_incidences(): void
    {
        $curso = Curso::create(['nombre' => 'Curso del profesor']);
        $seccion = Seccion::create(['grado' => '1ro', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '33333333',
            'nombre' => 'Alumno Dashboard',
            'seccion_id' => $seccion->id,
        ]);
        $profesor = User::create([
            'name' => 'Profesor Dashboard',
            'email' => 'profesor.dashboard@example.test',
            'dni' => '33334444',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $fecha = now();

        foreach ([$profesor->id, 1] as $usuarioId) {
            \DB::table('incidencias')->insert([
                'estudiante_id' => $estudianteId,
                'usuario_id' => $usuarioId,
                'curso_id' => $curso->id,
                'fecha' => $fecha->toDateString(),
                'hora' => '08:00:00',
                'descripcion' => 'Dashboard profesor test',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
            ]);
        }

        $labelsSemana = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        $valoresSemana = array_fill(0, 4, 0);
        $valoresSemana[min(intdiv($fecha->day - 1, 7), 3)] = 1;
        $labelsMes = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];
        $valoresMes = array_fill(0, 12, 0);
        $valoresMes[$fecha->month - 1] = 1;
        $gravedadPorGradoProfesor = [
            [
                'grado' => 'Primer grado',
                'cantidades' => ['Leve' => 1, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 100, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 1,
            ],
            [
                'grado' => 'Segundo grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
            [
                'grado' => 'Tercer grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
            [
                'grado' => 'Cuarto grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
            [
                'grado' => 'Quinto grado',
                'cantidades' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'porcentajes' => ['Leve' => 0, 'Intermedia' => 0, 'Grave' => 0],
                'total' => 0,
            ],
        ];

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])->get(route('dashboard').'?grado='.urlencode($seccion->grado).'&seccion_id='.$seccion->id.'&curso_id='.$curso->id)
            ->assertOk()
            ->assertSee('Selecciona tu contexto')
            ->assertDontSee('grafico-gravedad-grado-1')
            ->assertSee('"gravedadPorGrado":' . json_encode($gravedadPorGradoProfesor), false)
            ->assertSee('"semana":{"labels":' . json_encode($labelsSemana) . ',"valores":' . json_encode($valoresSemana), false)
            ->assertSee('"mes":{"labels":' . json_encode($labelsMes) . ',"valores":' . json_encode($valoresMes), false)
            ->assertDontSee('"labels":["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"]', false);
    }

    public function test_professor_dashboard_starts_at_zero_without_complete_selection(): void
    {
        $profesor = User::create([
            'name' => 'Profesor Sin Contexto',
            'email' => 'profesor.cero@example.test',
            'dni' => '44445555',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Selecciona grado, sección y curso para visualizar tus estadísticas')
            ->assertSee('Selecciona tu contexto')
            ->assertSee('name="grado"', false)
            ->assertSee('name="seccion_id"', false)
            ->assertSee('name="curso_id"', false)
            ->assertDontSee('id="grafico-incidencias"', false)
            ->assertSee('Las tarjetas estarán en cero', false)
            ->assertSee('>0</div>', false);
    }

    public function test_professor_professional_report_supports_multiple_sections_and_excludes_reserved_actas(): void
    {
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccionA = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionB = Seccion::create(['grado' => '1.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor Reporte',
            'email' => 'profesor.reporte@example.test',
            'dni' => '77778888',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionA->id,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionB->id,
        ]);

        $estudianteA = \DB::table('estudiantes')->insertGetId([
            'dni' => '88889999',
            'nombre' => 'Alumno A',
            'seccion_id' => $seccionA->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);

        \DB::table('incidencias')->insert([
            [
                'estudiante_id' => $estudianteA,
                'usuario_id' => $profesor->id,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '09:00:00',
                'descripcion' => 'Incidencia visible',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
                'es_reservada' => false,
            ],
            [
                'estudiante_id' => $estudianteA,
                'usuario_id' => $profesor->id,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '10:00:00',
                'descripcion' => 'Acta reservada',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
                'es_reservada' => true,
            ],
        ]);

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])
            ->post(route('reporte-profesional.generar'), [
                'grado' => '1.º',
                'curso_id' => $curso->id,
                'secciones' => [$seccionA->id, $seccionB->id],
            ])
            ->assertOk()
            ->assertSee('Reporte Profesional')
            ->assertSee('data-row-submit', false)
            ->assertSee('data-panel-submit', false)
            ->assertSee('reporte-profesional-resultados', false)
            ->assertSee('scrollIntoView', false)
            ->assertSee('Sección A')
            ->assertSee('Sección B')
            ->assertSee('INSTITUCIÓN EDUCATIVA TUNGASUCA')
            ->assertSee('Imprimir / Guardar PDF')
            ->assertSee('Comparativa por sección')
            ->assertSee('Estado de incidencias')
            ->assertSee('Evolución mensual')
            ->assertDontSee('Acta reservada');
    }

    public function test_directiva_can_generate_institutional_report_from_grades(): void
    {
        $directora = User::create([
            'name' => 'Directora Institucional',
            'email' => 'directora.reporte@example.test',
            'dni' => '10101010',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Directora')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $curso = Curso::create(['nombre' => 'Matemática']);
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '20202020',
            'nombre' => 'Alumno Directivo',
            'seccion_id' => $seccion->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        \DB::table('incidencias')->insert([
            'estudiante_id' => $estudianteId,
            'usuario_id' => $directora->id,
            'curso_id' => $curso->id,
            'fecha' => now()->toDateString(),
            'hora' => '08:30:00',
            'descripcion' => 'Descripción privada reservada',
            'gravedad_id' => $gravedadId,
            'estado_id' => $estadoId,
            'es_reservada' => true,
        ]);

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])
            ->post(route('reporte-profesional.institucional.generar'), [
                'grado' => 'todos',
                'curso_id' => $curso->id,
                'secciones' => [$seccion->id],
                'anio' => now()->year,
            ])
            ->assertOk()
            ->assertSee('Generar reporte')
            ->assertSee('REPORTE PROFESIONAL INSTITUCIONAL')
            ->assertSee('INCIDENCIAS POR GRADO')
            ->assertSee('Desglose por curso')
            ->assertSee('Curso')
            ->assertSee('Total')
            ->assertSee('Estado de incidencias')
            ->assertSee('Imprimir / Guardar PDF')
            ->assertDontSee('Nueva sección')
            ->assertDontSee('Editar secciones')
            ->assertDontSee('Descripción privada reservada');
    }

    public function test_professor_professional_report_rejects_sections_from_another_grade(): void
    {
        $curso = Curso::create(['nombre' => 'Ciencia']);
        $seccionPrimero = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionSegundo = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor Validación',
            'email' => 'profesor.validacion@example.test',
            'dni' => '99990000',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionPrimero->id,
        ]);

        $this->from(route('reporte-profesional.index'))
            ->withSession([
                'usuario_id' => $profesor->id,
                'usuario_rol' => 'Profesor',
            ])
            ->post(route('reporte-profesional.generar'), [
                'grado' => '1.º',
                'curso_id' => $curso->id,
                'secciones' => [$seccionSegundo->id],
            ])
            ->assertRedirect(route('reporte-profesional.index'))
            ->assertSessionHasErrors('secciones');
    }

    public function test_professor_dashboard_excludes_reserved_actas_and_requires_matching_context(): void
    {
        $curso = Curso::create(['nombre' => 'Matemática']);
        $seccion = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $otraSeccion = Seccion::create(['grado' => '2.º', 'seccion' => 'C']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '55556666',
            'nombre' => 'Alumno Filtro',
            'seccion_id' => $seccion->id,
        ]);
        $profesor = User::create([
            'name' => 'Profesor Filtrado',
            'email' => 'profesor.filtro@example.test',
            'dni' => '66667777',
            'password' => 'password-seguro',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);

        foreach ([false, true] as $reservada) {
            \DB::table('incidencias')->insert([
                'estudiante_id' => $estudianteId,
                'usuario_id' => $profesor->id,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '09:00:00',
                'descripcion' => 'Filtro dashboard',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
                'es_reservada' => $reservada,
            ]);
        }

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])->get(route('dashboard').'?grado='.urlencode($seccion->grado).'&seccion_id='.$seccion->id.'&curso_id='.$curso->id)
            ->assertOk()
            ->assertSee('id="grafico-incidencias"', false)
            ->assertSee('"seleccionado":true', false)
            ->assertSee('"total":1', false);

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])->get(route('dashboard').'?grado='.urlencode($otraSeccion->grado).'&seccion_id='.$otraSeccion->id.'&curso_id='.$curso->id)
            ->assertOk()
            ->assertSee('"seleccionado":true', false)
            ->assertSee('"total":0', false);
    }

    public function test_incidence_search_filters_by_student_name(): void
    {
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $estudianteCoincidente = \DB::table('estudiantes')->insertGetId([
            'dni' => '11111111',
            'nombre' => 'Ana Torres',
            'seccion_id' => $seccion->id,
        ]);
        $estudianteNoCoincidente = \DB::table('estudiantes')->insertGetId([
            'dni' => '22222222',
            'nombre' => 'Luis Pérez',
            'seccion_id' => $seccion->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);

        foreach ([$estudianteCoincidente, $estudianteNoCoincidente] as $estudianteId) {
            \DB::table('incidencias')->insert([
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '08:00:00',
                'descripcion' => 'Prueba de búsqueda',
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
            ]);
        }

        $this->get(route('incidencias.index', ['buscar' => 'ana']))
            ->assertOk()
            ->assertSee('Ana Torres')
            ->assertDontSee('Luis Pérez')
            ->assertSee('name="buscar"', false)
            ->assertSee('Limpiar');
    }

    public function test_director_and_subdirector_can_browse_students_and_report_only_non_reserved_incidents(): void
    {
        $seccion = Seccion::create(['grado' => '3.º', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '11112222',
            'nombre' => 'Estudiante Dirección',
            'seccion_id' => $seccion->id,
        ]);
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $idsNormales = [];

        foreach ([
            ['descripcion' => 'Reporte visible uno', 'es_reservada' => false],
            ['descripcion' => 'Reporte visible dos', 'es_reservada' => false],
            ['descripcion' => 'Contenido reservado', 'es_reservada' => true],
        ] as $reporte) {
            $id = \DB::table('incidencias')->insertGetId([
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '08:00:00',
                'descripcion' => $reporte['descripcion'],
                'gravedad_id' => $gravedadId,
                'estado_id' => $estadoId,
                'es_reservada' => $reporte['es_reservada'],
            ]);
            if (!$reporte['es_reservada']) {
                $idsNormales[] = $id;
            }
        }

        $directora = User::create([
            'name' => 'Directora',
            'email' => 'directora@example.test',
            'dni' => '87654320',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Directora')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $subdirector = User::create([
            'name' => 'Subdirector',
            'email' => 'subdirector@example.test',
            'dni' => '87654319',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Subdirector')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ]);
        $this->get(route('estudiantes.index', [
            'buscar' => 'Estudiante Dirección',
            'seccion_id' => $seccion->id,
        ]))->assertOk()
            ->assertSee('Estudiante Dirección')
            ->assertSee('Reportes')
            ->assertDontSee('Añadir alumno');

        $this->get(route('estudiantes.reporte.seleccionar', $estudianteId))
            ->assertOk()
            ->assertSee('Reporte visible uno')
            ->assertSee('Reporte visible dos')
            ->assertDontSee('Contenido reservado');

        $this->post(route('estudiantes.reporte.pdf.post', $estudianteId), [
            'incidencia_ids' => [$idsNormales[0]],
        ])->assertDownload('reporte-11112222.pdf');
        $this->post(route('estudiantes.reporte.pdf.post', $estudianteId), [
            'incidencia_ids' => $idsNormales,
        ])->assertDownload('reporte-11112222.pdf');
        $this->get(route('estudiantes.reporte.pdf', $estudianteId))
            ->assertDownload('reporte-11112222.pdf');

        for ($i = 0; $i < 26; $i++) {
            \DB::table('estudiantes')->insert([
                'dni' => str_pad((string) (30000000 + $i), 8, '0', STR_PAD_LEFT),
                'nombre' => 'Alumno adicional '.$i,
                'seccion_id' => $seccion->id,
            ]);
        }

        $this->withSession([
            'usuario_id' => $subdirector->id,
            'usuario_rol' => 'Subdirector',
        ]);
        $this->get(route('estudiantes.index'))
            ->assertOk()
            ->assertSee('page=2', false);
        $this->get(route('estudiantes.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Estudiante Dirección');
        $this->withSession([
            'usuario_id' => 1,
            'usuario_rol' => 'Administrador',
        ])->get(route('estudiantes.index'))
            ->assertForbidden();
    }

    public function test_reserved_acta_pdf_is_downloadable_only_by_school_leadership(): void
    {
        Schema::create('actas_incidencia', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('incidencia_id')->unique();
            $table->string('ie')->nullable();
            $table->string('dre')->nullable();
            $table->string('ugel')->nullable();
            $table->date('sisve_fecha')->nullable();
            $table->string('sisve_nro_reporte', 50)->nullable();
            $table->string('tipo_reporte', 1)->nullable();
            $table->string('informante_nombres')->nullable();
            $table->string('informante_parentesco', 30)->nullable();
            $table->string('informante_parentesco_otro')->nullable();
            $table->string('informante_dni', 20)->nullable();
            $table->string('informante_domicilio')->nullable();
            $table->string('informante_telefono', 30)->nullable();
            $table->string('informante_correo')->nullable();
            $table->string('agredido_nombres_iniciales')->nullable();
            $table->unsignedSmallInteger('agredido_edad')->nullable();
            $table->string('agredido_sexo', 1)->nullable();
            $table->string('agredido_turno', 1)->nullable();
            $table->string('agredido_telefono', 30)->nullable();
            $table->boolean('agredido_discapacidad')->nullable();
            $table->string('agredido_discapacidad_detalle')->nullable();
            $table->json('tipos_violencia')->nullable();
            $table->string('violencia_otro')->nullable();
            $table->timestamps();
        });
        Schema::create('acta_agresores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('acta_incidencia_id');
            $table->string('tipo', 10);
            $table->string('nombres')->nullable();
            $table->unsignedSmallInteger('edad')->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('relacion_grado', 20)->nullable();
            $table->string('grado_seccion', 30)->nullable();
            $table->string('turno', 1)->nullable();
            $table->string('cargo', 30)->nullable();
            $table->string('cargo_otro')->nullable();
            $table->timestamps();
        });

        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '11112222',
            'nombre' => 'Estudiante Reservado',
            'seccion_id' => $seccion->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $incidenciaId = \DB::table('incidencias')->insertGetId([
            'estudiante_id' => $estudianteId,
            'usuario_id' => 1,
            'curso_id' => $curso->id,
            'fecha' => now()->toDateString(),
            'hora' => '08:00:00',
            'descripcion' => 'Registro reservado',
            'gravedad_id' => $gravedadId,
            'estado_id' => $estadoId,
            'es_reservada' => true,
        ]);
        $actaId = \DB::table('actas_incidencia')->insertGetId([
            'incidencia_id' => $incidenciaId,
            'ie' => 'I.E. Tungasuca',
            'tipo_reporte' => 'B',
            'informante_nombres' => 'Persona informante',
            'agredido_nombres_iniciales' => 'E.R.',
            'tipos_violencia' => json_encode(['fisico']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('acta_agresores')->insert([
            'acta_incidencia_id' => $actaId,
            'tipo' => 'estudiante',
            'nombres' => 'Presunto agresor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $directora = User::create([
            'name' => 'Directora',
            'email' => 'directora-pdf@example.test',
            'dni' => '87654320',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Directora')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->get(route('incidencias.acta.create'))
            ->assertOk()
            ->assertSee('Registrar Acta de Incidencia');
        $this->get(route('incidencias.acta.estudiantes.buscar', [
            'buscar' => 'Estudiante Reservado',
        ]))->assertOk()
            ->assertJsonPath('estudiantes.0.id', $estudianteId);

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->post(route('incidencias.acta.store'), [
            'estudiante_id' => $estudianteId,
            'agredido_nombres_iniciales' => 'Estudiante Reservado',
            'tipos_violencia' => ['sexual'],
        ])->assertSessionHasErrors('agredido_nombres_iniciales');

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->post(route('incidencias.acta.store'), [
            'estudiante_id' => $estudianteId,
            'fecha' => '2020-01-01',
        ])->assertRedirect();

        $actaVacia = \DB::table('incidencias')
            ->where('es_reservada', true)
            ->whereNull('curso_id')
            ->whereNull('descripcion')
            ->first();
        $this->assertNotNull($actaVacia);
        $this->assertSame(now()->toDateString(), $actaVacia->fecha);
        $actaVaciaId = \DB::table('actas_incidencia')
            ->where('incidencia_id', $actaVacia->id)
            ->value('id');
        $this->assertNotNull($actaVaciaId);
        $this->assertDatabaseMissing('acta_agresores', [
            'acta_incidencia_id' => $actaVaciaId,
        ]);

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->get(route('incidencias.acta.edit', $incidenciaId))
            ->assertOk()
            ->assertSee('I.E. Tungasuca')
            ->assertSee('Presunto agresor');

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->put(route('incidencias.acta.update', $incidenciaId), [
            'estudiante_id' => $estudianteId,
            'ie' => 'I.E. Actualizada',
        ])->assertRedirect(route('incidencias.show', $incidenciaId));
        $this->assertDatabaseHas('actas_incidencia', [
            'incidencia_id' => $incidenciaId,
            'ie' => 'I.E. Actualizada',
        ]);
        $this->assertDatabaseMissing('acta_agresores', [
            'acta_incidencia_id' => $actaId,
        ]);

        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->get(route('incidencias.acta.pdf', $incidenciaId))
            ->assertDownload('acta-incidencia-'.$incidenciaId.'.pdf');

        $this->withSession([
            'usuario_id' => 1,
            'usuario_rol' => 'Administrador',
        ])->get(route('incidencias.acta.pdf', $incidenciaId))
            ->assertForbidden();

        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'profesor-acta@example.test',
            'dni' => '87654318',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccion->id,
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ])->get(route('incidencias.index'))
            ->assertOk()
            ->assertSee('Reservado')
            ->assertDontSee('Registro reservado');
        $this->get(route('incidencias.show', $incidenciaId))
            ->assertForbidden();
        $this->get(route('incidencias.acta.pdf', $incidenciaId))
            ->assertForbidden();
    }

    public function test_teacher_can_search_students_and_download_scoped_incidence_reports(): void
    {
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccionAsignada = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionNoAsignada = Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        $profesor = User::create([
            'name' => 'Profesor',
            'email' => 'reportes@example.test',
            'dni' => '87654321',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Profesor')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        AsignacionDocente::create([
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'seccion_id' => $seccionAsignada->id,
        ]);
        $estudiante = \DB::table('estudiantes')->insertGetId([
            'dni' => '11111111',
            'nombre' => 'Ana Torres',
            'seccion_id' => $seccionAsignada->id,
        ]);
        $otroEstudiante = \DB::table('estudiantes')->insertGetId([
            'dni' => '22222222',
            'nombre' => 'Alumno de otra sección',
            'seccion_id' => $seccionNoAsignada->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $incidenciaId = \DB::table('incidencias')->insertGetId([
            'estudiante_id' => $estudiante,
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'fecha' => now()->toDateString(),
            'hora' => '08:00:00',
            'descripcion' => 'Incidencia seleccionada',
            'gravedad_id' => $gravedadId,
            'estado_id' => $estadoId,
        ]);
        $incidenciaDeOtroAlumnoId = \DB::table('incidencias')->insertGetId([
            'estudiante_id' => $otroEstudiante,
            'usuario_id' => $profesor->id,
            'curso_id' => $curso->id,
            'fecha' => now()->toDateString(),
            'hora' => '09:00:00',
            'descripcion' => 'Incidencia ajena',
            'gravedad_id' => $gravedadId,
            'estado_id' => $estadoId,
        ]);
        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_rol' => 'Profesor',
        ]);

        $this->get(route('estudiantes.index', ['buscar' => '1111']))
            ->assertOk()
            ->assertSee('Ana Torres')
            ->assertDontSee('Alumno de otra sección')
            ->assertSee(route('estudiantes.reporte.seleccionar', $estudiante), false)
            ->assertSee(route('estudiantes.reporte.pdf', $estudiante), false);

        $this->get(route('estudiantes.reporte.seleccionar', $estudiante))
            ->assertOk()
            ->assertSee('Incidencia seleccionada');

        $this->post(route('estudiantes.reporte.pdf.post', $estudiante), [
            'incidencia_ids' => [$incidenciaId],
        ])->assertDownload('reporte-11111111.pdf');

        $this->get(route('estudiantes.reporte.pdf', $estudiante))
            ->assertDownload('reporte-11111111.pdf');

        $this->from(route('estudiantes.reporte.seleccionar', $estudiante))
            ->post(route('estudiantes.reporte.pdf.post', $estudiante), [
                'incidencia_ids' => [$incidenciaDeOtroAlumnoId],
            ])
            ->assertRedirect(route('estudiantes.reporte.seleccionar', $estudiante))
            ->assertSessionHasErrors('incidencia_ids');

        $this->get(route('estudiantes.reporte.seleccionar', $otroEstudiante))
            ->assertNotFound();
    }

    public function test_urgencies_show_the_active_count_spanish_pagination_and_privacy_safe_rows(): void
    {
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '11112222',
            'nombre' => 'Estudiante de urgencia',
            'seccion_id' => $seccion->id,
        ]);
        $gravedadGrave = \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']);
        $gravedadLeve = \DB::table('gravedades')->insertGetId(['nombre' => 'Leve']);
        $estadoPendiente = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        $estadoFinalizado = \DB::table('estados')->insertGetId(['nombre' => 'Finalizado']);

        $incidenciaNormalId = null;
        for ($indice = 0; $indice < 16; $indice++) {
            $incidenciaId = \DB::table('incidencias')->insertGetId([
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => sprintf('08:%02d:00', $indice),
                'descripcion' => $indice === 0 ? 'DETALLE CONFIDENCIAL DEL ACTA' : 'Incidencia activa',
                'gravedad_id' => $gravedadGrave,
                'estado_id' => $estadoPendiente,
                'es_reservada' => $indice === 0,
            ]);

            if ($indice === 1) {
                $incidenciaNormalId = $incidenciaId;
            }
        }

        \DB::table('incidencias')->insert([
            [
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '09:00:00',
                'descripcion' => 'Incidencia grave finalizada',
                'gravedad_id' => $gravedadGrave,
                'estado_id' => $estadoFinalizado,
            ],
            [
                'estudiante_id' => $estudianteId,
                'usuario_id' => 1,
                'curso_id' => $curso->id,
                'fecha' => now()->toDateString(),
                'hora' => '10:00:00',
                'descripcion' => 'Incidencia leve pendiente',
                'gravedad_id' => $gravedadLeve,
                'estado_id' => $estadoPendiente,
            ],
        ]);

        $response = $this->get(route('incidencias.urgencias'));
        $response
            ->assertOk()
            ->assertSee('16 urgencias activas')
            ->assertSee('Mostrando 1 a 15 de 16 registros')
            ->assertSee('Anterior')
            ->assertSee('Siguiente')
            ->assertSee('Grado / Sección')
            ->assertSee('Fecha y hora')
            ->assertSee('Acciones')
            ->assertSee('Ver')
            ->assertSee(route('incidencias.show', $incidenciaNormalId), false)
            ->assertDontSee('DETALLE CONFIDENCIAL DEL ACTA')
            ->assertDontSee('Incidencia grave finalizada')
            ->assertDontSee('Incidencia leve pendiente')
            ->assertDontSee('Motivo')
            ->assertDontSee('Showing');

        $this->assertMatchesRegularExpression(
            '/<td class="urgencias-datetime">\s*<span class="urgencias-date">[\d\/]+<\/span>\s*<span class="urgencias-time">[^<]+<\/span>\s*<\/td>/',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<td class="urgencias-actions-cell">\s*<div[^>]*class="[^"]*urgencias-actions[^"]*"[^>]*>.*?<\/div>\s*<\/td>/s',
            $response->getContent()
        );
    }

    public function test_empty_urgencies_show_zero_and_keep_the_acta_registration_action(): void
    {
        $directora = User::create([
            'name' => 'Directora',
            'email' => 'directora-urgencias@example.test',
            'dni' => '87654320',
            'password' => 'clave-segura',
            'rol_id' => Rol::where('nombre_rol', 'Directora')->value('id'),
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
        $this->withSession([
            'usuario_id' => $directora->id,
            'usuario_rol' => 'Directora',
        ])->get(route('incidencias.urgencias'))
            ->assertOk()
            ->assertSee('0 urgencias activas')
            ->assertSee('No hay urgencias activas.')
            ->assertSee('Mostrando 0 a 0 de 0 registros')
            ->assertSee('+ Registrar acta')
            ->assertSee(route('incidencias.acta.create'), false)
            ->assertDontSee('Showing');

        $this->get(route('incidencias.acta.create'))
            ->assertOk()
            ->assertSee('Registrar Acta de Incidencia');
    }

    public function test_directive_sidebar_has_only_urgencies_active_and_no_duplicate_all_incidences_link(): void
    {
        $curso = Curso::create(['nombre' => 'Comunicación']);
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $estudianteId = \DB::table('estudiantes')->insertGetId([
            'dni' => '33334444',
            'nombre' => 'Estudiante directivo',
            'seccion_id' => $seccion->id,
        ]);
        $gravedadId = \DB::table('gravedades')->insertGetId(['nombre' => 'Grave']);
        $estadoId = \DB::table('estados')->insertGetId(['nombre' => 'Pendiente']);
        \DB::table('incidencias')->insert([
            'estudiante_id' => $estudianteId,
            'usuario_id' => 1,
            'curso_id' => $curso->id,
            'fecha' => now()->toDateString(),
            'hora' => '08:15:00',
            'descripcion' => 'Incidencia visible',
            'gravedad_id' => $gravedadId,
            'estado_id' => $estadoId,
        ]);

        foreach (['Directora', 'Subdirector'] as $indice => $rol) {
            $usuario = User::create([
                'name' => $rol,
                'email' => strtolower($rol).'-sidebar@example.test',
                'dni' => '8765432'.($indice + 1),
                'password' => 'clave-segura',
                'rol_id' => Rol::where('nombre_rol', $rol)->value('id'),
                'estado_aprobacion' => 'Aprobado',
                'activo' => true,
            ]);

            $response = $this->withSession([
                'usuario_id' => $usuario->id,
                'usuario_rol' => $rol,
            ])->get(route('incidencias.urgencias'));
            $response->assertOk()
                ->assertSee('class="nav-link active" href="'.route('incidencias.urgencias').'"', false)
                ->assertSee('class="nav-link text-dark" href="'.route('incidencias.index').'"', false)
                ->assertSee('urgencias-table--directivo', false)
                ->assertSee('Estudiante directivo')
                ->assertSee('08:15')
                ->assertDontSee('Todas las incidencias');

            $this->assertMatchesRegularExpression(
                '/<td class="urgencias-datetime">\s*<span class="urgencias-date">[\d\/]+<\/span>\s*<span class="urgencias-time">08:15<\/span>\s*<\/td>/',
                $response->getContent()
            );
        }
    }
}
