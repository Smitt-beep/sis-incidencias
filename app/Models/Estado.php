<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    public $timestamps = false;

    //campos que pueden ser asignados de forma masiva
    protected $fillable = ['nombre'];
    //un estado puede estar asociados muchas incidencias
    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
