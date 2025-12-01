<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEnte extends Model
{
    // Le decimos a Laravel que use el nombre singular
    protected $table = 'tipos_entes'; 
    
    protected $primaryKey = 'CodTipoEnte';
    public $timestamps = false;

    protected $fillable = ['NombreEnte', 'PrefijoCodigo', 'ContadorActual'];
}