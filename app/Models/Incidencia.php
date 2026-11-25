<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incidencia extends Model
{
    use SoftDeletes;

    protected $table = 'incidencias';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'usuario_id',
        'curso_id',
        'fecha',
        'hora',
        'tipo_aula',
        'descripcion',
        'gravedad_id',
        'estado_id',
        'es_reservada',
    ];

    protected $casts = [
        'es_reservada' => 'boolean',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /** Datos del formato de acta, que puede ser público o reservado. */
    public function acta(): HasOne
    {
        return $this->hasOne(ActaIncidencia::class, 'incidencia_id');
    }

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por');
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

    public function tiposIncidencia(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoIncidencia::class,
            'incidencia_tipo',
            'incidencia_id',
            'tipo_incidencia_id'
        );
    }

    /**
     * El límite de cinco corresponde solo a fotografías activas, para permitir reemplazos.
     */
    public function fotografias(): HasMany
    {
        return $this->hasMany(IncidenciaFotografia::class, 'incidencia_id');
    }
}