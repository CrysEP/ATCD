<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    // Le decimos a Laravel el nombre exacto de la tabla
    protected $table = 'estado';

    // Le decimos cuál es la clave primaria
    protected $primaryKey = 'CodEstado';

    // Le decimos que esta tabla NO usa 'created_at' ni 'updated_at'
    public $timestamps = false;

    /**
     * Relación: Un estado TIENE MUCHOS municipios.
     */
    public function municipios()
    {
        // Un Estado se relaciona con muchos Municipios
        return $this->hasMany(Municipio::class, 'Estado_FK', 'CodEstado');
    }
}