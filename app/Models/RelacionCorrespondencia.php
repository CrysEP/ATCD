<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelacionCorrespondencia extends Model
{
    protected $table = 'relacion_correspondencia';
    protected $primaryKey = 'CodigoInterno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // La tabla no tiene created_at/updated_at

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
        'StatusSolicitud_FK'
    ];

    protected $casts = [
        'FechaOficioEntrega' => 'datetime',
        'FechaRecibido' => 'datetime',
    ];

    /**
     * Relación: La correspondencia tiene un estado (status).
     */
    public function status()
    {
        return $this->belongsTo(StatusSolicitud::class, 'StatusSolicitud_FK', 'CodStatusSolicitud');
    }

    /**
     * Relación: La correspondencia tiene una solicitud asociada.
     */
    public function solicitud()
    {
        return $this->hasOne(Solicitud::class, 'CodigoInterno_FK', 'CodigoInterno');
    }
    
    /**
     * Relación: La correspondencia pertenece a un municipio.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'Municipio_FK', 'CodMunicipio');
    }
}