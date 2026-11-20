<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Cambios mínimos en la tabla base. Las incidencias actuales no cambian:
        //    es_reservada queda en false para todas.
        Schema::table('incidencias', function (Blueprint $table) {
            $table->boolean('es_reservada')->default(false);

            // La directora no registra el acta desde un curso ni siempre redacta
            // una descripción breve, por eso estos dos campos pasan a aceptar NULL.
            $table->unsignedBigInteger('curso_id')->nullable()->change();
            $table->text('descripcion')->nullable()->change();
        });

        // 2) Datos propios del acta (uno por incidencia reservada).
        //    Todo es texto libre y nullable: la directora lo escribe a mano.
        Schema::create('actas_incidencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidencia_id')
                ->unique()
                ->constrained('incidencias')
                ->cascadeOnDelete();

            // Encabezado
            $table->string('ie')->nullable();
            $table->string('dre')->nullable();
            $table->string('ugel')->nullable();
            $table->date('sisve_fecha')->nullable();
            $table->string('sisve_nro_reporte', 50)->nullable();
            $table->string('tipo_reporte', 1)->nullable(); // A, B, C o D

            // 2. Persona informante
            $table->string('informante_nombres')->nullable();
            $table->string('informante_parentesco', 30)->nullable();
            $table->string('informante_parentesco_otro')->nullable();
            $table->string('informante_dni', 20)->nullable();
            $table->string('informante_domicilio')->nullable();
            $table->string('informante_telefono', 30)->nullable();
            $table->string('informante_correo')->nullable();

            // 3. Supuesta persona agredida (el alumno va enlazado en incidencias.estudiante_id)
            $table->string('agredido_nombres_iniciales')->nullable();
            $table->unsignedSmallInteger('agredido_edad')->nullable();
            $table->string('agredido_sexo', 1)->nullable();
            $table->string('agredido_turno', 1)->nullable();
            $table->string('agredido_telefono', 30)->nullable();
            $table->boolean('agredido_discapacidad')->nullable();
            $table->string('agredido_discapacidad_detalle')->nullable();

            // 5. Tipo de violencia (lista de claves) y "otro"
            $table->jsonb('tipos_violencia')->nullable();
            $table->string('violencia_otro')->nullable();

            $table->timestamps();
        });

        // 3) Presuntos agresores: varios por acta (estudiantes y adultos).
        Schema::create('acta_agresores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_incidencia_id')
                ->constrained('actas_incidencia')
                ->cascadeOnDelete();

            $table->string('tipo', 10); // estudiante | adulto
            $table->string('nombres')->nullable();
            $table->unsignedSmallInteger('edad')->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('dni', 20)->nullable();

            // Solo estudiantes
            $table->string('relacion_grado', 20)->nullable(); // mismo_grado | mayor | menor
            $table->string('grado_seccion', 30)->nullable();
            $table->string('turno', 1)->nullable();

            // Solo adultos
            $table->string('cargo', 30)->nullable(); // director, docente, administrativo, personal_apoyo, otro
            $table->string('cargo_otro')->nullable();

            $table->timestamps();
        });

        DB::statement(
            "ALTER TABLE acta_agresores
            ADD CONSTRAINT acta_agresores_tipo_valid_check
            CHECK (tipo IN ('estudiante', 'adulto'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_agresores');
        Schema::dropIfExists('actas_incidencia');

        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('es_reservada');
            // curso_id y descripcion se dejan nullable a propósito: volver a NOT NULL
            // fallaría si ya existen actas guardadas sin curso.
        });
    }
};
