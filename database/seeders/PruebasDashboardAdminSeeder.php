<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Datos sintéticos para probar los gráficos administrativos.
 * Ejecutar manualmente solo en una base de desarrollo.
 */
class PruebasDashboardAdminSeeder extends Seeder
{
    public function run(): void
    {
        $rolProfesor = DB::table('roles')->where('nombre_rol', 'Profesor')->first();
        $gravedades = DB::table('gravedades')->pluck('id', 'nombre');
        $estadoId = DB::table('estados')->where('nombre', 'Pendiente')->value('id');

        if (!$rolProfesor || !$estadoId || !$gravedades->has('Leve')) {
            $this->command->error('Faltan roles, gravedades o estados base. Ejecuta primero sus seeders.');
            return;
        }

        foreach (['Intermedia', 'Grave'] as $gravedad) {
            if (!$gravedades->has($gravedad)) {
                $this->command->error("No existe la gravedad {$gravedad}.");
                return;
            }
        }

        $profesorId = DB::table('users')->where('dni', '99999002')->value('id');
        if (!$profesorId) {
            $profesorId = DB::table('users')->insertGetId([
                'name' => 'PRUEBA_DASHBOARD_ADMIN_Profesor',
                'email' => 'prueba.dashboard.admin@test.local',
                'dni' => '99999002',
                'password' => Hash::make('password123'),
                'rol_id' => $rolProfesor->id,
                'estado_aprobacion' => 'Aprobado',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $grados = ['1 grado', '2 grado', '3 grado', '4 grado', '5 grado'];
        $conteos = [10, 30, 18, 15, 4];
        $nombresCursos = [
            'Matemática',
            'Comunicación',
            'Ciencias Sociales',
            'Religión',
            'Educación Física',
        ];
        $idsEstudiantes = [];

        foreach ($grados as $indice => $grado) {
            $gradoPrueba = "PRUEBA_DASHBOARD_ADMIN_{$grado}";
            $seccionId = DB::table('secciones')
                ->where('grado', $gradoPrueba)
                ->where('seccion', 'A')
                ->value('id');

            if (!$seccionId) {
                $seccionId = DB::table('secciones')->insertGetId([
                    'grado' => $gradoPrueba,
                    'seccion' => 'A',
                ]);
            }

            $dniEstudiante = (string) (88991001 + $indice);
            $estudianteId = DB::table('estudiantes')->where('dni', $dniEstudiante)->value('id');

            if (!$estudianteId) {
                $estudianteId = DB::table('estudiantes')->insertGetId([
                    'dni' => $dniEstudiante,
                    'nombre' => "PRUEBA_DASHBOARD_ADMIN_Alumno_{$indice}",
                    'seccion_id' => $seccionId,
                ]);
            }

            $idsEstudiantes[] = $estudianteId;
        }

        $inicioMes = Carbon::now()->startOfMonth();
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $diasPrevios = $inicioMes->lessThan($inicioSemana)
            ? $inicioMes->diffInDays($inicioSemana)
            : 0;
        $diasSemanaTranscurridos = $inicioSemana->diffInDays(Carbon::now());
        $insertadas = 0;

        foreach (['teoria', 'taller'] as $tipoAula) {
            foreach ($grados as $indiceGrado => $grado) {
                $cursoNombre = "PRUEBA_DASHBOARD_ADMIN_{$tipoAula}_{$nombresCursos[$indiceGrado]}";
                $cursoId = DB::table('cursos')->where('nombre', $cursoNombre)->value('id');

                if (!$cursoId) {
                    $cursoId = DB::table('cursos')->insertGetId(['nombre' => $cursoNombre]);
                }

                for ($indiceIncidencia = 0; $indiceIncidencia < $conteos[$indiceGrado]; $indiceIncidencia++) {
                    if ($indiceIncidencia % 2 === 1 && $diasPrevios > 0) {
                        $fecha = $inicioMes->copy()
                            ->addDays($indiceIncidencia % $diasPrevios)
                            ->toDateString();
                    } else {
                        $fecha = $inicioSemana->copy()
                            ->addDays($indiceIncidencia % ($diasSemanaTranscurridos + 1))
                            ->toDateString();
                    }

                    $descripcion = "PRUEBA_DASHBOARD_ADMIN_{$tipoAula}_{$indiceGrado}_{$indiceIncidencia}";
                    $gravedad = ['Leve', 'Intermedia', 'Grave'][$indiceIncidencia % 3];
                    DB::table('incidencias')->updateOrInsert(
                        [
                            'usuario_id' => $profesorId,
                            'descripcion' => $descripcion,
                        ],
                        [
                            'estudiante_id' => $idsEstudiantes[$indiceGrado],
                            'curso_id' => $cursoId,
                            'fecha' => $fecha,
                            'hora' => '08:00:00',
                            'tipo_aula' => $tipoAula,
                            'gravedad_id' => $gravedades[$gravedad],
                            'estado_id' => $estadoId,
                        ]
                    );
                    $insertadas++;
                }
            }
        }

        $this->command->info("Incidencias sintéticas del dashboard creadas o actualizadas: {$insertadas}.");
        $this->command->info('Busca los registros con el prefijo PRUEBA_DASHBOARD_ADMIN_.');
    }
}
