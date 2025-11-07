<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEnte extends Model
{
    // Le decimos a Laravel que use el nombre singular
    protected $table = 'tipo_ente'; 
    
    protected $primaryKey = 'CodTipoEnte';
    public $timestamps = false;

    protected $fillable = ['NombreEnte', 'PrefijoCodigo', 'ContadorActual'];
}