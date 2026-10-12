<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->date('fecha');
            $table->time('hora');
            $table->string('tipo');
            $table->foreignId('gravedad_id')->constrained('gravedades');
            $table->text('descripcion');
            $table->foreignId('estado_id')->constrained('estados');
            $table->text('justificacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};