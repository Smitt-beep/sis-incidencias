<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Datos propios del formato "Acta de Incidencia" (solo Directora / Subdirector).
 * Casi todo es texto libre que la directora escribe a mano.
 */
class ActaIncidencia extends Model
{
    protected $table = 'actas_incidencia';

    protected $guarded = ['id', 'incidencia_id'];

    protected $casts = [
        'sisve_fecha' => 'date',
        'agredido_discapacidad' => 'boolean',
        'tipos_violencia' => 'array',
    ];

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class, 'incidencia_id');
    }

    public function agresores(): HasMany
    {
        return $this->hasMany(ActaAgresor::class, 'acta_incidencia_id');
    }
}
