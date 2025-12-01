<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    // Apunta correctamente a la tabla en plural
    protected $table = 'usuarios';
    protected $primaryKey = 'CodUsuario';
    public $timestamps = false; 

    protected $fillable = [
        'NombreUsuario', 
        'ContraseniaUsuario', 
        'RolUsuario', 
        'EstadoUsuario',
        'CedulaPersonaUsuario_FK'
    ];

    protected $hidden = [
        'ContraseniaUsuario', 'remember_token'
    ];
    
    /**
     * Sobrescribe el método para obtener la contraseña de autenticación.
     */
    public function getAuthPassword()
    {
        // CORREGIDO: Se eliminó la línea "muerta" que sobraba aquí.
        return $this->ContraseniaUsuario;
    }

    /**
     * Relación: Un usuario pertenece a una Persona.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'CedulaPersonaUsuario_FK', 'CedulaPersona');
    }

    /**
     * Relación: Un usuario puede tener datos de Funcionario (vinculados por la Cédula).
     */
    public function funcionarioData()
    {
        // Vinculamos la tabla 'funcionarios' con la tabla 'usuarios' a través de la Cédula
        return $this->hasOne(Funcionario::class, 'CedulaPersona_FK', 'CedulaPersonaUsuario_FK');
    }
}