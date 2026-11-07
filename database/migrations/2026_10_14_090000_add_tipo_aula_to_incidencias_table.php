<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->string('tipo_aula', 10)->nullable();
        });

        DB::statement(
            "ALTER TABLE incidencias
            ADD CONSTRAINT incidencias_tipo_aula_valid_check
            CHECK (tipo_aula IS NULL OR tipo_aula IN ('teoria', 'taller'))"
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE incidencias DROP CONSTRAINT incidencias_tipo_aula_valid_check'
        );

        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('tipo_aula');
        });
    }
};
