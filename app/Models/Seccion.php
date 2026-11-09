<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    protected $table = 'secciones';

    public $timestamps = false;

    protected $fillable = [
        'grado',
        'seccion',
    ];

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'seccion_id', 'id');
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'asignaciones_docentes',
            'seccion_id',
            'usuario_id'
        )->withPivot('curso_id')->distinct();
    }
}