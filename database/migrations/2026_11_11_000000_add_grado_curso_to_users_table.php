<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('grado_registro')->nullable();
            $table->foreignId('curso_registro_id')->nullable()->constrained('cursos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curso_registro_id');
            $table->dropColumn('grado_registro');
        });
    }
};
