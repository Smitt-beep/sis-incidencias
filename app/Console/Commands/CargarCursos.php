<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CargarCursos extends Command
{
    protected $signature = 'cursos:cargar';

    protected $description = 'Carga los 19 cursos del colegio en la base de datos de sis_incidencias';

    public function handle(): int
    {
        // Cambia 'cursos' y 'nombre' si tu tabla o columna se llama distinto (ej. 'titulo')
        $tabla = 'cursos';
        $columnaNombre = 'nombre';

        if (! Schema::hasTable($tabla)) {
            $this->error("No existe la tabla '{$tabla}'. Revisa el nombre real en tu migracion o modelo.");
            return self::FAILURE;
        }

        $cursos = [
            'Arte y Cultura',
            'Asistencia',
            'Ciencia y Tecnología',
            'Ciencias Sociales',
            'Competencias Transversales',
            'Comunicación',
            'Desarrollo Personal, Ciudadanía y Cívica',
            'Descubrimiento del Mundo',
            'Educación Física',
            'Educación para el Trabajo',
            'Educación Religiosa',
            'Inglés',
            'Matemática',
            'Personal Social',
            'Plan Lector',
            'Psicomotricidad',
            'Razonamiento Matemático',
            'Razonamiento Verbal',
            'Tutoría',
        ];

        try {
            DB::transaction(function () use ($tabla, $columnaNombre, $cursos) {
                $ahora = now();

                foreach ($cursos as $curso) {
                    $existe = DB::table($tabla)->where($columnaNombre, $curso)->exists();

                    if (! $existe) {
                        $datos = [$columnaNombre => $curso];

                        if (Schema::hasColumn($tabla, 'created_at')) {
                            $datos['created_at'] = $ahora;
                        }
                        if (Schema::hasColumn($tabla, 'updated_at')) {
                            $datos['updated_at'] = $ahora;
                        }

                        DB::table($tabla)->insert($datos);
                    }
                }
            });

            $this->info('Listo: se verificaron/insertaron los 19 cursos sin duplicados.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al cargar los cursos: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}