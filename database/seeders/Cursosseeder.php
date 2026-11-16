<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de cursos para I.E. Tungasuca.
 * Incluye cursos de aula de teoría y aulas de taller.
 * Usar solo hasta que llegue la API académica oficial.
 */
class CursosSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            // Aulas de teoría
            ['nombre' => 'Matemática',               'tipo' => 'Teoría'],
            ['nombre' => 'Comunicación',              'tipo' => 'Teoría'],
            ['nombre' => 'Ciencias Sociales',         'tipo' => 'Teoría'],
            ['nombre' => 'Ciencia y Tecnología',      'tipo' => 'Teoría'],
            ['nombre' => 'Educación Física',          'tipo' => 'Teoría'],
            ['nombre' => 'Educación Religiosa',       'tipo' => 'Teoría'],
            ['nombre' => 'Inglés',                    'tipo' => 'Teoría'],
            ['nombre' => 'Educación para el Trabajo', 'tipo' => 'Teoría'],

            // Aulas de taller
            ['nombre' => 'Comunicación (Taller)',              'tipo' => 'Taller'],
            ['nombre' => 'Matemática (Taller)',                'tipo' => 'Taller'],
            ['nombre' => 'Inglés (Taller)',                    'tipo' => 'Taller'],
            ['nombre' => 'Educación para el Trabajo (Taller)', 'tipo' => 'Taller'],
            ['nombre' => 'AIP',                                'tipo' => 'Taller'],
        ];

        foreach ($cursos as $curso) {
            $existe = DB::table('cursos')->where('nombre', $curso['nombre'])->exists();
            if (!$existe) {
                DB::table('cursos')->insert(['nombre' => $curso['nombre']]);
                $this->command->info("Insertado: {$curso['nombre']} ({$curso['tipo']})");
            } else {
                $this->command->info("Ya existe: {$curso['nombre']}");
            }
        }

        $this->command->info('');
        $this->command->info('Cursos listos. Total en tabla: ' . DB::table('cursos')->count());
    }
}