<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asignaciones_docentes', function (Blueprint $table) {
            $table->id();

            // Profesor al que pertenece la asignación
            $table->foreignId('usuario_id')
                  ->constrained('users');

            // Curso que enseña el profesor
            $table->foreignId('curso_id')
                  ->constrained('cursos');

            // Sección donde enseña ese curso
            $table->foreignId('seccion_id')
                  ->constrained('secciones');

            $table->timestamps();

            //evita registrar 2 veces la misma asigancion
            $table->unique(
                ['usuario_id', 'curso_id', 'seccion_id'], 
                'asignacion_docente_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docentes');
    }
};