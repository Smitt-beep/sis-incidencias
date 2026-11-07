<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index('usuario_id', 'chatbot_conv_usuario_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversaciones');
    }
};
