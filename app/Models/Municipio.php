<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipios';
    protected $primaryKey = 'CodMunicipio';
    public $timestamps = false;



public function parroquias()
    {
        return $this->hasMany(Parroquia::class, 'Municipio_FK', 'CodMunicipio');
    }

}
