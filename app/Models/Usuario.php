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
    }

    /**
     * Relación: Un usuario (que es un funcionario/persona) tiene datos de persona.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'CedulaPersonaUsuario_FK', 'CedulaPersona');
    }
}
