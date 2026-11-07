<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencia_fotografias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidencia_id')
                ->constrained('incidencias')
                ->cascadeOnDelete();
            $table->string('ruta_archivo');
            $table->string('nombre_original');
            $table->string('tipo_mime', 100);
            $table->unsignedBigInteger('tamano');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencia_fotografias');
    }
};
