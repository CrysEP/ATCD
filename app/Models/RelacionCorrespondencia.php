<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelacionCorrespondencia extends Model
{
    protected $table = 'relacion_correspondencia';
    protected $primaryKey = 'CodigoInterno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'CodigoInterno',
        'Nro.Oficio',
        'FechaOficioEntrega',
        'FechaRecibido',
        'Municipio_FK',
        'Ente',
        'Sector',
        'Descripcion',
        'InstruccionPresidencia',
        'Observacion',
        'Gerencia_Jefatura',
        'StatusSolicitud_FK',
        'Solicitud_FK' // <-- ¡AÑADIR ESTA LÍNEA!
    ];

    protected $casts = [
        'FechaOficioEntrega' => 'datetime',
        'FechaRecibido' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(StatusSolicitud::class, 'StatusSolicitud_FK', 'CodStatusSolicitud');
    }

    /**
     * RELACIÓN INVERTIDA (NUEVA):
     * Una correspondencia pertenece a una solicitud.
     */
    public function solicitud()
    {
        // El FK 'Solicitud_FK' de esta tabla apunta al 'CodSolucitud' de la Solicitud
        return $this->belongsTo(Solicitud::class, 'Solicitud_FK', 'CodSolucitud');
    }
    
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'Municipio_FK', 'CodMunicipio');
    }
}