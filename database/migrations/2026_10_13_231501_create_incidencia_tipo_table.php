<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencia_tipo', function (Blueprint $table) {
            $table->foreignId('incidencia_id')
                ->constrained('incidencias')
                ->cascadeOnDelete();
            $table->foreignId('tipo_incidencia_id')
                ->constrained('tipos_incidencia');

            $table->unique(
                ['incidencia_id', 'tipo_incidencia_id'],
                'incidencia_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencia_tipo');
    }
};
