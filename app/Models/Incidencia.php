<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidencia extends Model
{
    protected $table = 'incidencias';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'usuario_id',
        'curso_id',
        'fecha',
        'hora',
        'descripcion',
        'gravedad_id',
        'estado_id',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }
    //una incidencia pertenece a una gravedad
    public function gravedad(): BelongsTo
    {
        return $this->belongsTo(Gravedad::class, 'gravedad_id', 'id');
    }
    //una incidencia pertenece a un estado
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }
}