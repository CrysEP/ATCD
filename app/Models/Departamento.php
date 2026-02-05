<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'CodDepartamento';
    protected $fillable = ['NombreDepartamento']; // Sin siglas

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class, 'Departamento_FK', 'CodDepartamento');
    }
}


?>