<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidenciaFotografia extends Model
{
    use SoftDeletes;

    protected $table = 'incidencia_fotografias';

    protected $fillable = [
        'incidencia_id',
        'ruta_archivo',
        'nombre_original',
        'tipo_mime',
        'tamano',
    ];

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class, 'incidencia_id');
    }

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por');
    }
}
