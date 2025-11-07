<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipio';
    protected $primaryKey = 'CodMunicipio';
    public $timestamps = false;

    /**
     * Relación: Un municipio pertenece a un estado.
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'Estado_FK', 'CodEstado');
    }


public function parroquias()
    {
        return $this->hasMany(Parroquia::class, 'Municipio_FK', 'CodMunicipio');
    }

}
