<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Configura al administrador inicial del sistema.
     */
    public function run(): void
    {
        // Buscamos el rol Administrador por su nombre.
        // No asumimos que siempre tendrá ID = 1.
        $rolAdministrador = DB::table('roles')
            ->where('nombre_rol', 'Administrador')
            ->first();

        // Si encontramos el rol, actualizamos al administrador inicial.
        if ($rolAdministrador) {

            DB::table('users')
                ->where('dni', '12345678')
                ->update([
                    'rol_id' => $rolAdministrador->id,
                    'estado_aprobacion' => 'Aprobado',
                    'activo' => true,
                ]);
        }
    }
}