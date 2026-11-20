<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->index(['seccion_id', 'nombre'], 'estudiantes_seccion_nombre_idx');
        });

        Schema::table('incidencias', function (Blueprint $table): void {
            $table->index(
                ['estudiante_id', 'es_reservada', 'fecha', 'hora'],
                'incidencias_estudiante_reservada_fecha_hora_idx'
            );
            $table->index(['usuario_id'], 'incidencias_usuario_id_idx');
            $table->index(['fecha', 'hora'], 'incidencias_fecha_hora_idx');
        });

        Schema::table('acta_agresores', function (Blueprint $table): void {
            $table->index('acta_incidencia_id', 'acta_agresores_acta_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('acta_agresores', function (Blueprint $table): void {
            $table->dropIndex('acta_agresores_acta_id_idx');
        });

        Schema::table('incidencias', function (Blueprint $table): void {
            $table->dropIndex('incidencias_estudiante_reservada_fecha_hora_idx');
            $table->dropIndex('incidencias_usuario_id_idx');
            $table->dropIndex('incidencias_fecha_hora_idx');
        });

        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->dropIndex('estudiantes_seccion_nombre_idx');
        });
    }
};
