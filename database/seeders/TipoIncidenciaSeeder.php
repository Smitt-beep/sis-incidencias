<?php

namespace Database\Seeders;

use App\Models\TipoIncidencia;
use Illuminate\Database\Seeder;

class TipoIncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Agresión física',
            'Agresión verbal',
            'Acoso escolar (bullying)',
            'Ciberacoso',
            'Discriminación',
            'Daños a la infraestructura',
            'Hurto o robo',
            'Conducta disruptiva',
            'Incumplimiento de normas',
            'Otros',
        ];

        foreach ($tipos as $nombre) {
            TipoIncidencia::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
