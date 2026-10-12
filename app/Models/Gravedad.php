<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gravedad extends Model
{
    //indidcamos exactamente que tabla usa este modelo
    protected $table = 'gravedades';
    //Esta tabla no utiliza las columnas created:at y updated_at.
    public $timestamps = false;
    //indica que campos puede recibirse datos de forma masiva
    protected $fillable = ['nombre'];
    //una gravedad puede pertenecer a muchas incidencias.
    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
