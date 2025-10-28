<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persona';
    protected $primaryKey = 'CedulaPersona';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // La tabla no tiene created_at/updated_at

    protected $fillable = [
        'CedulaPersona', 
        'ApellidosPersona', 
        'NombresPersona', 
        'FechaNacPersona',
        'SexoPersona', 
        'TelefonoPersona', 
        'ParroquiaPersona_FK', 
        'CorreoElectronicoPersona'
    ];

    /**
     * Relación: Una persona puede tener muchas solicitudes.
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'CedulaPersona_FK', 'CedulaPersona');
    }

    /**
     * Relación: Una persona pertenece a una parroquia.
     */
    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'ParroquiaPersona_FK', 'CodParroquia');
    }

    /**
     * Accesor para obtener el nombre completo.
     */
    public function getNombreCompletoAttribute()
    {
        return $this->NombresPersona . ' ' . $this->ApellidosPersona;
    }
}
