<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}