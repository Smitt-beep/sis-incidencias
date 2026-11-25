<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReporteProfesionalDirectivoTest extends TestCase
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
            $table->string('email');
            $table->string('dni', 8);
            $table->string('password');
            $table->foreignId('rol_id')->nullable();
            $table->string('estado_aprobacion');
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
            $table->text('descripcion')->nullable();
            $table->foreignId('gravedad_id');
            $table->foreignId('estado_id');
            $table->boolean('es_reservada')->default(false);
            $table->softDeletes();
        });
    }

    public function test_institutional_report_counts_reserved_records_without_rendering_private_descriptions_for_authorized_roles(): void
    {
        $cursoId = DB::table('cursos')->insertGetId(['nombre' => 'Matemática']);
        $cursoMenosIncidenciasId = DB::table('cursos')->insertGetId(['nombre' => 'Álgebra']);
        $seccionId = DB::table('secciones')->insertGetId(['grado' => 'Primer grado', 'seccion' => 'A']);
        $estudianteId = DB::table('estudiantes')->insertGetId([
            'dni' => '12345678',
            'nombre' => 'Estudiante de prueba',
            'seccion_id' => $seccionId,
        ]);
        DB::table('gravedades')->insert([
            ['id' => 1, 'nombre' => 'Leve'],
            ['id' => 2, 'nombre' => 'Intermedia'],
            ['id' => 3, 'nombre' => 'Grave'],
        ]);
        DB::table('estados')->insert([
            ['id' => 1, 'nombre' => 'Pendiente'],
            ['id' => 2, 'nombre' => 'En Proceso'],
            ['id' => 3, 'nombre' => 'Finalizado'],
        ]);

        $primerUsuario = $this->createApprovedUser('Administrador');
        $fecha = now()->toDateString();
        foreach ([
            ['gravedad_id' => 3, 'estado_id' => 1, 'es_reservada' => true, 'descripcion' => 'DETALLE CONFIDENCIAL DEL ACTA'],
            ['gravedad_id' => 2, 'estado_id' => 2, 'es_reservada' => false, 'descripcion' => 'Descripción ordinaria'],
            ['gravedad_id' => 3, 'estado_id' => 3, 'es_reservada' => false, 'descripcion' => 'Incidencia finalizada'],
        ] as $incidencia) {
            DB::table('incidencias')->insert($incidencia + [
                'estudiante_id' => $estudianteId,
                'usuario_id' => $primerUsuario->id,
                'curso_id' => $cursoId,
                'fecha' => $fecha,
            ]);
        }
        DB::table('incidencias')->insert([
            'estudiante_id' => $estudianteId,
            'usuario_id' => $primerUsuario->id,
            'curso_id' => $cursoMenosIncidenciasId,
            'fecha' => $fecha,
            'gravedad_id' => 1,
            'estado_id' => 3,
            'es_reservada' => false,
            'descripcion' => 'Otra incidencia',
        ]);

        foreach (['Directora', 'Subdirector', 'Administrador'] as $rol) {
            $usuario = $rol === 'Administrador' ? $primerUsuario : $this->createApprovedUser($rol);
            $response = $this->withSession([
                'usuario_id' => $usuario->id,
                'usuario_nombre' => $usuario->name,
                'usuario_rol' => $rol,
            ])->post(route('secciones.reporte-profesional'), [
                'grado' => 'todos',
                'curso_id' => '',
                'anio' => now()->year,
            ]);

            $response->assertOk()
                ->assertSee('TOTAL GENERAL')
                ->assertSee('TOTAL REGISTRADAS')
                ->assertSee('4')
                ->assertSee('Curso')
                ->assertSee('Total')
                ->assertSee('Pendientes y en proceso')
                ->assertSee('2 casos')
                ->assertSee('Graves activas')
                ->assertSee('1 casos')
                ->assertSee($rol)
                ->assertDontSee('DETALLE CONFIDENCIAL DEL ACTA')
                ->assertSee('responsive: true', false)
                ->assertSee('maintainAspectRatio: false', false);

            $contenidoReporte = $response->getContent();
            $inicioDesglose = strpos($contenidoReporte, 'class="directivo-course-breakdown"');
            $this->assertNotFalse($inicioDesglose);
            $contenidoDesglose = substr($contenidoReporte, $inicioDesglose);
            $posicionMatematica = strpos($contenidoDesglose, 'Matemática');
            $posicionAlgebra = strpos($contenidoDesglose, 'Álgebra');
            $this->assertNotFalse($posicionMatematica);
            $this->assertNotFalse($posicionAlgebra);
            $this->assertLessThan($posicionAlgebra, $posicionMatematica);
        }
    }

    public function test_authorized_roles_have_a_dedicated_institutional_report_page(): void
    {
        foreach (['Administrador', 'Directora', 'Subdirector'] as $rol) {
            $usuario = $this->createApprovedUser($rol);

            $response = $this->withSession([
                'usuario_id' => $usuario->id,
                'usuario_nombre' => $usuario->name,
                'usuario_rol' => $rol,
            ])->get(route('reporte-profesional.index'))
                ->assertOk();

            $response->assertSeeInOrder($rol === 'Administrador'
                    ? ['Inicio', 'Usuarios', 'Incidencias', 'Grado', 'Reporte Profesional']
                    : ['Inicio', 'Incidencias', 'Grado', 'Reporte Profesional'])
                ->assertSee('Reporte Profesional Institucional')
                ->assertSee('Todos los grados')
                ->assertSee('Generar reporte')
                ->assertDontSee('Nueva sección')
                ->assertDontSee('Editar secciones')
                ->assertDontSee('Gestión de Grados');
        }
    }

    public function test_institutional_report_handles_empty_filters_and_zero_incidents(): void
    {
        $usuario = $this->createApprovedUser('Subdirector');

        $response = $this->withSession([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->name,
            'usuario_rol' => 'Subdirector',
        ])->post(route('secciones.reporte-profesional'), [
            'grado' => 'todos',
            'curso_id' => '',
            'anio' => now()->year,
        ]);

        $response->assertOk()
            ->assertSee('TOTAL REGISTRADAS')
            ->assertSee('0% del total')
            ->assertSee('Sin incidencias')
            ->assertSee('0 casos');
    }

    private function createApprovedUser(string $role): User
    {
        $rol = Rol::firstOrCreate(['nombre_rol' => $role]);

        return User::create([
            'name' => 'Autoridad de prueba',
            'email' => strtolower($role).'-'.uniqid().'@example.test',
            'dni' => (string) random_int(10000000, 99999999),
            'password' => 'clave-segura',
            'rol_id' => $rol->id,
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
    }
}
