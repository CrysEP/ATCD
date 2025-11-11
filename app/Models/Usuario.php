<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'CodUsuario';
    public $timestamps = false; // La tabla no tiene created_at/updated_at

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
     * Apunta a 'ContraseniaUsuario' en lugar de 'password'.
     */
    public function getAuthPassword()
    {
        return $this->ContraseniaUsuario;
        return $this->belongsTo(Persona::class, 'CedulaPersonaUsuario_FK', 'CedulaPersona');
    }

    /**
     * Relación: Un usuario (que es un funcionario/persona) tiene datos de persona.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'CedulaPersonaUsuario_FK', 'CedulaPersona');
    }

    public function funcionarioData()
    {
        // 'CedulaPersona_FK' -> Columna en la tabla 'funcionario'
        // 'CedulaPersonaUsuario_FK' -> Columna en la tabla 'usuario' (este modelo)
        return $this->hasOne(Funcionario::class, 'CedulaPersona_FK', 'CedulaPersonaUsuario_FK');
    }
}
