<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GravedadSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inserta los estados.
        // Si alguno ya existe, no vuelve a insertarlo.
        DB::table('gravedades')->insertOrIgnore([
            //cada arreglo repressenta un registro(unafila) de la tabla
            ['nombre' => 'Leve'],
            ['nombre' => 'Intermedia'],
            ['nombre' => 'Grave'],
        ]);
    }
}
