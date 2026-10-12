<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('grado');
            $table->string('seccion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};