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
        
        Schema::table('users', function (Blueprint $table) {
            //relaciona al usuario con su rol
            $table->foreignId('rol_id')
            ->nullable()
            ->constrained('roles');

            //Indica si el administrador ya aprobó el registro
            $table->string('estado_aprobacion')
            ->default('Pendiente');

            //permite desactivar una cuenta sin eliminar a un usuario
            $table->boolean('activo')
            ->default(true);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //primero eliminamos la relacion con roles
            $table->dropColumn([
                'rol_id',
                'estado_aprobacion',
                'activo'
            ]);
        });
    }
};
