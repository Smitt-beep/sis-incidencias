<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Curso;
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
            $table->foreignId('curso_id');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('tipo_aula')->nullable();
            $table->text('descripcion');
            $table->foreignId('gravedad_id');
            $table->foreignId('estado_id');
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

    public function test_public_registration_selects_base_course_and_assigns_it_to_all_sections_on_approval(): void
    {
        $cursoBase = Curso::create(['nombre' => 'Comunicación']);
        $cursoTaller = Curso::create(['nombre' => 'Comunicación (Taller)']);
        $seccionA = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);
        $seccionB = Seccion::create(['grado' => '1.º', 'seccion' => 'B']);
        Seccion::create(['grado' => '2.º', 'seccion' => 'A']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee($cursoBase->nombre)
            ->assertDontSee($cursoTaller->nombre);

        $registrationData = [
            'name' => 'Profesor Registrado',
            'email' => 'registro@example.test',
            'dni' => '23456789',
            'grado' => '1.º',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
        ];

        $this->from(route('register'))
            ->post(route('register.store'), $registrationData + [
                'curso_registro_id' => $cursoTaller->id,
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('curso_registro_id');

        $this->post(route('register.store'), $registrationData + [
            'curso_registro_id' => $cursoBase->id,
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $profesor = User::where('dni', '23456789')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'id' => $profesor->id,
            'grado_registro' => '1.º',
            'curso_registro_id' => $cursoBase->id,
        ]);

        $this->post(route('usuarios.aprobar', $profesor))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('asignaciones_docentes', 2);
        foreach ([$seccionA, $seccionB] as $seccion) {
            $this->assertDatabaseHas('asignaciones_docentes', [
                'usuario_id' => $profesor->id,
                'curso_id' => $cursoBase->id,
                'seccion_id' => $seccion->id,
            ]);
            $this->assertDatabaseMissing('asignaciones_docentes', [
                'usuario_id' => $profesor->id,
                'curso_id' => $cursoTaller->id,
                'seccion_id' => $seccion->id,
            ]);
        }
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

    public function test_admin_dashboard_shows_stacked_grade_and_course_counts_for_both_periods(): void
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

        $indiceDia = $fecha->dayOfWeekIso - 1;
        $valoresDia = array_fill(0, 7, 0);
        $valoresDia[$indiceDia] = 3;
        $valoresGradoPrimeroSemana = array_fill(0, 7, 0);
        $valoresGradoPrimeroSemana[$indiceDia] = 2;
        $valoresGradoSegundoSemana = array_fill(0, 7, 0);
        $valoresGradoSegundoSemana[$indiceDia] = 1;
        $valoresSemanaCursoTeoria = $valoresGradoPrimeroSemana;
        $valoresSemanaCursoTaller = $valoresGradoSegundoSemana;

        $cantidadSemanasMes = (int) ceil($fecha->copy()->endOfMonth()->day / 7);
        $labelsMes = array_map(
            fn ($numero) => "Semana {$numero}",
            range(1, $cantidadSemanasMes)
        );
        $indiceSemanaMes = intdiv($fecha->day - 1, 7);
        $valoresMes = array_fill(0, $cantidadSemanasMes, 0);
        $valoresMes[$indiceSemanaMes] = 3;
        $valoresGradoPrimeroMes = array_fill(0, $cantidadSemanasMes, 0);
        $valoresGradoPrimeroMes[$indiceSemanaMes] = 2;
        $valoresGradoSegundoMes = array_fill(0, $cantidadSemanasMes, 0);
        $valoresGradoSegundoMes[$indiceSemanaMes] = 1;

        $datosGravedad = [
            'cantidades' => ['Leve' => 1, 'Intermedia' => 1, 'Grave' => 1],
            'porcentajes' => ['Leve' => 33.3, 'Intermedia' => 33.3, 'Grave' => 33.3],
            'total' => 3,
        ];
        $datosSemana = [
            'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'valores' => $valoresDia,
            'porGrado' => [
                ['grado' => '1ro', 'valores' => $valoresGradoPrimeroSemana],
                ['grado' => '2do', 'valores' => $valoresGradoSegundoSemana],
            ],
            'porCurso' => [
                ['curso' => 'Theory Course', 'valores' => $valoresSemanaCursoTeoria],
                ['curso' => 'Workshop Course', 'valores' => $valoresSemanaCursoTaller],
            ],
            'gravedad' => $datosGravedad,
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
            'gravedad' => $datosGravedad,
        ];

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('grafico-admin-grado')
            ->assertSee('grafico-admin-curso')
            ->assertSee('grafico-admin-gravedad')
            ->assertSee("type: 'pie'", false)
            ->assertSee('stacked: true')
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
                ->assertSee('grafico-admin-grado')
                ->assertSee('grafico-admin-curso')
                ->assertSee('grafico-admin-gravedad');
        }
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
}
