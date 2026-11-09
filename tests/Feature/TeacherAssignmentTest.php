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

        Schema::create('tipos_incidencia', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
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
}
