<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    

    
    public function run(): void
    {
        // ejecuta el seeder que insertará:
        //leve/intermedio/grave

        $this->call(GravedadSeeders::class);

        // ejecuta el seeder que insertará:
        //pendiente/en proceso/finalizado
        $this->call(EstadoSeeders::class);

        //ejecuta el seeder de los roles principales del sistema
        $this->call(RolSeeder::class);

        //configura el administrador inicial
        $this->call(AdminSeeder::class);
    }
}
