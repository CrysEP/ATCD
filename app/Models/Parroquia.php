<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parroquia extends Model
{
    protected $table = 'parroquia';
    protected $primaryKey = 'CodParroquia';
    public $timestamps = false;

    /**
     * Relación: Una parroquia pertenece a un municipio.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'Municipio_FK', 'CodMunicipio');
    }
}
