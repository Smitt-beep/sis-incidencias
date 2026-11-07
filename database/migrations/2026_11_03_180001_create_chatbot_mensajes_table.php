<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')
                ->constrained('chatbot_conversaciones')
                ->restrictOnDelete();
            $table->enum('emisor', ['usuario', 'asistente']);
            $table->text('contenido');
            $table->enum('estado', ['completado', 'bloqueado', 'error']);
            $table->timestamps();

            $table->index(
                ['conversacion_id', 'created_at', 'id'],
                'chatbot_msg_conv_fecha_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_mensajes');
    }
};
