<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Borra los datos de PRUEBA: secciones cuyo grado NO tenga el formato real del colegio
 * ("1 sec" ... "5 sec"), los alumnos de esas secciones, las incidencias de esos alumnos
 * (con sus actas, agresores, tipos y fotografías) y las asignaciones docentes de esas secciones.
 *
 * No toca usuarios, roles, cursos, gravedades, estados ni las secciones "N sec" reales.
 * Por defecto SOLO SIMULA. Para borrar de verdad hay que usar --confirmar.
 *
 *   php artisan datos:limpiar-pruebas
 *   php artisan datos:limpiar-pruebas --confirmar
 */
class LimpiarDatosPrueba extends Command
{
    protected $signature = 'datos:limpiar-pruebas
        {--confirmar : Borra de verdad (sin esta opción solo se simula)}';

    protected $description = 'Borra secciones, alumnos e incidencias de prueba (simulación por defecto)';

    public function handle(): int
    {
        // Secciones de prueba = las que no siguen el formato real "1 sec".."5 sec"
        $secciones = DB::table('secciones')->orderBy('id')->get()
            ->filter(fn ($s) => ! preg_match('/^[1-5] sec$/', (string) $s->grado))
            ->values();
        $seccionIds = $secciones->pluck('id')->all();

        if ($seccionIds === []) {
            $this->info('No hay secciones de prueba. No hay nada que borrar.');

            return self::SUCCESS;
        }

        $estudianteIds = DB::table('estudiantes')->whereIn('seccion_id', $seccionIds)->pluck('id')->all();
        // Incluye las incidencias ya "eliminadas" (SoftDeletes), porque siguen ocupando la FK
        $incidenciaIds = $estudianteIds === [] ? [] : DB::table('incidencias')->whereIn('estudiante_id', $estudianteIds)->pluck('id')->all();
        $actaIds = $incidenciaIds === [] ? [] : DB::table('actas_incidencia')->whereIn('incidencia_id', $incidenciaIds)->pluck('id')->all();
        $fotos = $incidenciaIds === [] ? collect() : DB::table('incidencia_fotografias')->whereIn('incidencia_id', $incidenciaIds)->get(['id', 'ruta_archivo']);
        $asignaciones = DB::table('asignaciones_docentes')->whereIn('seccion_id', $seccionIds)->count();

        $this->line('');
        $this->info($this->option('confirmar') ? 'BORRADO DE DATOS DE PRUEBA' : 'SIMULACIÓN (no se borra nada)');
        $this->line('Secciones de prueba: ' . $secciones->map(fn ($s) => "[{$s->id}] {$s->grado} {$s->seccion}")->implode(', '));
        $this->table(['Se borraría', 'Cantidad'], [
            ['Secciones', count($seccionIds)],
            ['Alumnos', count($estudianteIds)],
            ['Incidencias (incluye eliminadas)', count($incidenciaIds)],
            ['Actas', count($actaIds)],
            ['Fotografías (registros)', $fotos->count()],
            ['Asignaciones docentes a esas secciones', $asignaciones],
        ]);

        // Alumnos que se conservarán (en secciones reales), para que veas que no se tocan
        $this->line('Alumnos que NO se tocan (secciones reales): ' . DB::table('estudiantes')->whereNotIn('seccion_id', $seccionIds)->count());

        if (! $this->option('confirmar')) {
            $this->comment('Simulación terminada. Para borrar de verdad, repite el comando con --confirmar.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($actaIds, $incidenciaIds, $estudianteIds, $seccionIds): void {
            // Se borra de lo más "hijo" a lo más "padre" (así no depende del ON DELETE CASCADE)
            if ($actaIds !== []) {
                DB::table('acta_agresores')->whereIn('acta_incidencia_id', $actaIds)->delete();
                DB::table('actas_incidencia')->whereIn('id', $actaIds)->delete();
            }
            if ($incidenciaIds !== []) {
                DB::table('incidencia_fotografias')->whereIn('incidencia_id', $incidenciaIds)->delete();
                DB::table('incidencia_tipo')->whereIn('incidencia_id', $incidenciaIds)->delete();
                DB::table('incidencias')->whereIn('id', $incidenciaIds)->delete();
            }
            if ($estudianteIds !== []) {
                DB::table('estudiantes')->whereIn('id', $estudianteIds)->delete();
            }
            DB::table('asignaciones_docentes')->whereIn('seccion_id', $seccionIds)->delete();
            DB::table('secciones')->whereIn('id', $seccionIds)->delete();
        });

        // Archivos de las fotografías (después de confirmar la transacción)
        $noBorrados = 0;
        foreach ($fotos as $foto) {
            try {
                Storage::disk('evidencias')->delete($foto->ruta_archivo);
            } catch (\Throwable $e) {
                $noBorrados++;
            }
        }

        $this->info('Listo: datos de prueba eliminados.');
        if ($noBorrados > 0) {
            $this->warn("{$noBorrados} archivo(s) de fotografía no se pudieron borrar del disco (los registros sí).");
        }

        return self::SUCCESS;
    }
}