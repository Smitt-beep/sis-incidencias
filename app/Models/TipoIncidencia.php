<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoIncidencia extends Model
{
    protected $table = 'tipos_incidencia';

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function incidencias(): BelongsToMany
    {
        return $this->belongsToMany(
            Incidencia::class,
            'incidencia_tipo',
            'tipo_incidencia_id',
            'incidencia_id'
        );
    }
}
