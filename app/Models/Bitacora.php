<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'CodBitacora';
    public $timestamps = false;

    protected $fillable = [
        'Usuario_FK', 'Accion', 'Tabla', 'Registro_ID', 
        'Nro_UAC', // <--- Agregado
        'Descripcion', 'FechaHora'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Usuario_FK', 'CodUsuario'); 
    }

    /**
     * Helper actualizado para recibir Nro_UAC
     */
    public static function registrar($accion, $tabla, $idRegistro, $nroUAC = null, $descripcion = null)
    {
        $usuarioId = Auth::check() ? Auth::id() : 0; 

        self::create([
            'Usuario_FK'  => $usuarioId,
            'Accion'      => $accion,
            'Tabla'       => $tabla,
            'Registro_ID' => $idRegistro,
            'Nro_UAC'     => $nroUAC,
            'Descripcion' => $descripcion,
            'FechaHora'   => now(),
        ]);
    }
}