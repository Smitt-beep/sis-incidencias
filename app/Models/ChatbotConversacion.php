<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversacion extends Model
{
    protected $table = 'chatbot_conversaciones';

    protected $fillable = [
        'usuario_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatbotMensaje::class, 'conversacion_id');
    }
}
