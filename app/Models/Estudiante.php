<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    public $timestamps = false;

    protected $fillable = [
        'dni',
        'nombre',
        'seccion_id',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id', 'id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'estudiante_id', 'id');
    }
}