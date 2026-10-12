<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * insertar los roles principales del sistema
     */
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['nombre_rol' => 'Administrador'],
            ['nombre_rol' => 'Profesor'],
            ['nombre_rol' => 'Directora'],
            ['nombre_rol' => 'Subdirector'],
           
        ]);
    }
}
