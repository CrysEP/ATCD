<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';
    protected $primaryKey = 'CodFuncionario';
    public $timestamps = false; // La tabla no tiene timestamps sdicjsdicefjei, por ahora o.o

    public $incrementing = false; // Para códigos alfanuméricos
    protected $keyType = 'string';

    protected $fillable = [
        'CargoFuncionario',
        'FirmaDigital',
        'AdscripciónFuncionario',
        'CedulaPersona_FK'
    ];

    /**
     * Relación: Un funcionario es una persona.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'CedulaPersona_FK', 'CedulaPersona');
    }

    /**
     * ACCESOR:
     * Convierte el 'FirmaDigital' (que es un blob) en una
     * cadena de texto base64 que se puede usar en una etiqueta <img>, de locos
     */
    public function getFirmaDigitalBase64Attribute()
    {
        if ($this->FirmaDigital) {
            // Se asume que es PNG o JPG, pero se puede ajustar
            return 'data:image/png;base64,' . base64_encode($this->FirmaDigital);
        }
        return null; // Devuelve nulo si no hay firma
    }
}