<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoSolicitud extends Model
{
    protected $table = 'archivos_solicitudes';
    
    protected $fillable = [
        'solicitud_id',
        'nombre_original',
        'ruta_archivo',
        'tipo_archivo',
        'tamano_archivo',
    ];

    /**
     * Relación: Un archivo pertenece a una solicitud.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id', 'CodSolicitud');
    }
}