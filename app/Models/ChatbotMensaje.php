<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMensaje extends Model
{
    public const EMISOR_USUARIO = 'usuario';

    public const EMISOR_ASISTENTE = 'asistente';

    public const ESTADO_COMPLETADO = 'completado';

    public const ESTADO_BLOQUEADO = 'bloqueado';

    public const ESTADO_ERROR = 'error';

    protected $table = 'chatbot_mensajes';

    protected $fillable = [
        'conversacion_id',
        'emisor',
        'contenido',
        'estado',
    ];

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversacion::class, 'conversacion_id');
    }
}