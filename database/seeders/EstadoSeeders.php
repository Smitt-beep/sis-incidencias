<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inserta las gravedades.
        // Si alguna ya existe, PostgreSQL la ignora gracias
        // a la restricción UNIQUE de la columna "nombre".
        DB::table('estados')->insertOrIgnore([
            //cada arreglo representa un registro (una fila) de la tabla
            ['nombre' => 'Pendiente'],
            ['nombre' => 'En proceso'],
            ['nombre' => 'Finalizado'],
        ]);
    }
}
