<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Seeder de PRUEBA para el dashboard del profesor.
 * Identificador: PRUEBA_DASHBOARD_
 *
 * Inserta:
 *  - 1 profesor de prueba (DNI: 99999001)
 *  - 1 curso de prueba
 *  - 2 secciones de prueba
 *  - 3 estudiantes de prueba
 *  - Incidencias de esta semana y este mes
 *
 * NO eliminar estos datos automáticamente.
 * El desarrollador los inspecciona manualmente y los borra cuando quiera.
 */
class PruebasDashboardSeeder extends Seeder
{
    public function run(): void
    {
        // ── Rol Profesor ──────────────────────────────────────────────────────
        $rolProfesor = DB::table('roles')->where('nombre_rol', 'Profesor')->first();
        if (!$rolProfesor) {
            $this->command->error('No existe el rol Profesor. Ejecuta primero RolSeeder.');
            return;
        }

        // ── Gravedades ────────────────────────────────────────────────────────
        $gravedadLeve       = DB::table('gravedades')->where('nombre', 'Leve')->first();
        $gravedadIntermedia = DB::table('gravedades')->where('nombre', 'Intermedia')->first();
        $gravedadGrave      = DB::table('gravedades')->where('nombre', 'Grave')->first();

        if (!$gravedadLeve || !$gravedadIntermedia || !$gravedadGrave) {
            $this->command->error('Faltan registros en gravedades. Ejecuta primero GravedadSeeders.');
            return;
        }

        // ── Estado ────────────────────────────────────────────────────────────
        $estadoPendiente = DB::table('estados')->where('nombre', 'Pendiente')->first();
        if (!$estadoPendiente) {
            $this->command->error('No existe el estado Pendiente. Ejecuta primero EstadoSeeders.');
            return;
        }

        // ── Curso de prueba ───────────────────────────────────────────────────
        $cursoId = DB::table('cursos')
            ->where('nombre', 'PRUEBA_DASHBOARD_Matemática')
            ->value('id');

        if (!$cursoId) {
            $cursoId = DB::table('cursos')->insertGetId([
                'nombre' => 'PRUEBA_DASHBOARD_Matemática',
            ]);
            $this->command->info("Curso de prueba creado (id={$cursoId}).");
        } else {
            $this->command->info("Curso de prueba ya existe (id={$cursoId}).");
        }

        // ── Secciones de prueba ───────────────────────────────────────────────
        $seccion1Id = DB::table('secciones')
            ->where('grado', 'PRUEBA_1ro')
            ->where('seccion', 'A')
            ->value('id');

        if (!$seccion1Id) {
            $seccion1Id = DB::table('secciones')->insertGetId([
                'grado'   => 'PRUEBA_1ro',
                'seccion' => 'A',
            ]);
            $this->command->info("Sección 1ro-A creada (id={$seccion1Id}).");
        }

        $seccion2Id = DB::table('secciones')
            ->where('grado', 'PRUEBA_1ro')
            ->where('seccion', 'B')
            ->value('id');

        if (!$seccion2Id) {
            $seccion2Id = DB::table('secciones')->insertGetId([
                'grado'   => 'PRUEBA_1ro',
                'seccion' => 'B',
            ]);
            $this->command->info("Sección 1ro-B creada (id={$seccion2Id}).");
        }

        // ── Profesor de prueba ────────────────────────────────────────────────
        $profesorId = DB::table('users')->where('dni', '99999001')->value('id');

        if (!$profesorId) {
            $profesorId = DB::table('users')->insertGetId([
                'name'              => 'PRUEBA_DASHBOARD_Profesor Test',
                'email'             => 'prueba.dashboard@test.local',
                'dni'               => '99999001',
                'password'          => Hash::make('password123'),
                'rol_id'            => $rolProfesor->id,
                'estado_aprobacion' => 'Aprobado',
                'activo'            => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $this->command->info("Profesor de prueba creado (id={$profesorId}, dni=99999001, pass=password123).");
        } else {
            $this->command->info("Profesor de prueba ya existe (id={$profesorId}).");
        }

        // ── Asignaciones docentes ─────────────────────────────────────────────
        foreach ([$seccion1Id, $seccion2Id] as $secId) {
            $existe = DB::table('asignaciones_docentes')
                ->where('usuario_id', $profesorId)
                ->where('curso_id',   $cursoId)
                ->where('seccion_id', $secId)
                ->exists();

            if (!$existe) {
                DB::table('asignaciones_docentes')->insert([
                    'usuario_id' => $profesorId,
                    'curso_id'   => $cursoId,
                    'seccion_id' => $secId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('Asignaciones docentes listas.');

        // ── Estudiantes de prueba ─────────────────────────────────────────────
        $estudiante1Id = DB::table('estudiantes')->where('dni', '88880001')->value('id');
        if (!$estudiante1Id) {
            $estudiante1Id = DB::table('estudiantes')->insertGetId([
                'nombre'     => 'PRUEBA_Alumno Uno',
                'dni'        => '88880001',
                'seccion_id' => $seccion1Id,
            ]);
        }

        $estudiante2Id = DB::table('estudiantes')->where('dni', '88880002')->value('id');
        if (!$estudiante2Id) {
            $estudiante2Id = DB::table('estudiantes')->insertGetId([
                'nombre'     => 'PRUEBA_Alumno Dos',
                'dni'        => '88880002',
                'seccion_id' => $seccion1Id,
            ]);
        }

        $estudiante3Id = DB::table('estudiantes')->where('dni', '88880003')->value('id');
        if (!$estudiante3Id) {
            $estudiante3Id = DB::table('estudiantes')->insertGetId([
                'nombre'     => 'PRUEBA_Alumno Tres',
                'dni'        => '88880003',
                'seccion_id' => $seccion2Id,
            ]);
        }
        $this->command->info('Estudiantes de prueba listos.');

        // ── Incidencias de prueba ─────────────────────────────────────────────
        // Se generan fechas en la SEMANA ACTUAL y en semanas anteriores del MES ACTUAL.
        // Así el dashboard mostrará datos tanto en la vista Semana como en la vista Mes.

        $hoy          = Carbon::now();
        $lunes        = $hoy->copy()->startOfWeek(Carbon::MONDAY);

        $fechas = [
            // Esta semana (días lunes, martes, miércoles)
            $lunes->copy()->toDateString(),
            $lunes->copy()->addDay()->toDateString(),
            $lunes->copy()->addDays(2)->toDateString(),
            // Semana anterior del mismo mes (si el mes lo permite)
            $lunes->copy()->subWeek()->toDateString(),
            $lunes->copy()->subWeek()->addDay()->toDateString(),
            // Dos semanas atrás
            $lunes->copy()->subWeeks(2)->toDateString(),
        ];

        $registros = [
            // Semana actual
            ['fecha' => $fechas[0], 'estudiante_id' => $estudiante1Id, 'gravedad_id' => $gravedadLeve->id,       'descripcion' => 'PRUEBA_DASHBOARD_ Conducta inadecuada en clase'],
            ['fecha' => $fechas[1], 'estudiante_id' => $estudiante2Id, 'gravedad_id' => $gravedadIntermedia->id, 'descripcion' => 'PRUEBA_DASHBOARD_ Falta de respeto al docente'],
            ['fecha' => $fechas[2], 'estudiante_id' => $estudiante3Id, 'gravedad_id' => $gravedadGrave->id,      'descripcion' => 'PRUEBA_DASHBOARD_ Agresión física a compañero'],
            ['fecha' => $fechas[1], 'estudiante_id' => $estudiante1Id, 'gravedad_id' => $gravedadLeve->id,       'descripcion' => 'PRUEBA_DASHBOARD_ Llegó tarde a clases'],
            // Semana anterior
            ['fecha' => $fechas[3], 'estudiante_id' => $estudiante2Id, 'gravedad_id' => $gravedadIntermedia->id, 'descripcion' => 'PRUEBA_DASHBOARD_ No trajo materiales'],
            ['fecha' => $fechas[4], 'estudiante_id' => $estudiante3Id, 'gravedad_id' => $gravedadLeve->id,       'descripcion' => 'PRUEBA_DASHBOARD_ Uso de celular en clase'],
            // Dos semanas atrás
            ['fecha' => $fechas[5], 'estudiante_id' => $estudiante1Id, 'gravedad_id' => $gravedadGrave->id,      'descripcion' => 'PRUEBA_DASHBOARD_ Amenazas a un compañero'],
        ];

        $insertadas = 0;
        foreach ($registros as $r) {
            // Solo insertar si la fecha sigue dentro del mes actual
            if (Carbon::parse($r['fecha'])->month !== $hoy->month) {
                continue;
            }

            // Evitar duplicados exactos (mismo profesor, estudiante, fecha, descripción)
            $existe = DB::table('incidencias')
                ->where('usuario_id',    $profesorId)
                ->where('estudiante_id', $r['estudiante_id'])
                ->where('fecha',         $r['fecha'])
                ->where('descripcion',   $r['descripcion'])
                ->exists();

            if (!$existe) {
                DB::table('incidencias')->insert([
                    'estudiante_id' => $r['estudiante_id'],
                    'usuario_id'    => $profesorId,
                    'curso_id'      => $cursoId,
                    'fecha'         => $r['fecha'],
                    'hora'          => '08:00:00',
                    'descripcion'   => $r['descripcion'],
                    'gravedad_id'   => $r['gravedad_id'],
                    'estado_id'     => $estadoPendiente->id,
                ]);
                $insertadas++;
            }
        }

        $this->command->info("Incidencias de prueba insertadas: {$insertadas}.");
        $this->command->info('');
        $this->command->info('=== DATOS DE PRUEBA ===');
        $this->command->info("Profesor  → DNI: 99999001 | Contraseña: password123");
        $this->command->info("Busca los registros con el prefijo PRUEBA_DASHBOARD_ para identificarlos.");
        $this->command->info('Para ejecutar solo este seeder: php artisan db:seed --class=PruebasDashboardSeeder');
    }
}