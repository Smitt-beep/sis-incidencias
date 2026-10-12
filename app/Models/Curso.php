<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'curso_id', 'id');
    }
}