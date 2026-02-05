<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoSolicitud extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'archivos_solicitudes';
    
    // Campos que permitimos llenar masivamente
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