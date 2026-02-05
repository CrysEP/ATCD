<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'CodDepartamento';
    
    protected $fillable = [
        'NombreDepartamento', 
        'DepartamentoPadre_FK' // Nuevo campo
    ];

    // Relación: Un departamento tiene funcionarios
    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class, 'Departamento_FK', 'CodDepartamento');
    }

    // Relación: Un departamento puede tener sub-departamentos (Hijos)
    public function hijos()
    {
        return $this->hasMany(Departamento::class, 'DepartamentoPadre_FK', 'CodDepartamento');
    }

    // Relación: Un departamento pertenece a una Gerencia superior (Padre)
    public function padre()
    {
        return $this->belongsTo(Departamento::class, 'DepartamentoPadre_FK', 'CodDepartamento');
    }
}