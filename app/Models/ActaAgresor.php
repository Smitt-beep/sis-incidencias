<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Presunto agresor de un acta (tipo: estudiante | adulto).
 */
class ActaAgresor extends Model
{
    protected $table = 'acta_agresores';

    protected $guarded = ['id', 'acta_incidencia_id'];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(ActaIncidencia::class, 'acta_incidencia_id');
    }
}
