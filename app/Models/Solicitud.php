<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitud';
    protected $primaryKey = 'CodSolucitud';
    public $timestamps = false; // La tabla no tiene created_at/updated_at

    protected $fillable = [
        'TipoSolicitudPlanilla', 'DescripcionSolicitud', 'FechaSolicitud',
        'TipoSolicitante', 'NivelUrgencia', 'FechaAtención', 'AnexaDocumentos',
        'CantidadDocumentosOriginal', 'CantidadDocumentoCopia', 'CantidadPaginasAnexo',
        'CedulaPersona_FK', 'Nro.UAC', 'CodigoInterno_FK', 'Funcionario_FK', 'TipoSolicitud_FK'
    ];

    protected $casts = [
        'FechaSolicitud' => 'datetime',
        'FechaAtención' => 'datetime',
        'AnexaDocumentos' => 'boolean'
    ];

    /**
     * Relación: Una solicitud pertenece a una persona (ciudadano).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'CedulaPersona_FK', 'CedulaPersona');
    }

    /**
     * Relación: Una solicitud es registrada por un funcionario.
     * (Asegúrate de que el modelo Funcionario exista si lo necesitas)
     */
    public function funcionario()
    {
        // return $this->belongsTo(Funcionario::class, 'Funcionario_FK', 'CodFuncionario');
        // De momento lo comentamos hasta que se cree el modelo Funcionario
        return $this
            ->belongsTo(Usuario::class, 'Funcionario_FK', 'CodFuncionario'); // Temporal
    }
    
    /**
     * Relación: Una solicitud está vinculada a un registro de correspondencia.
     * Este es el vínculo clave para obtener el estado.
     */
    // public function correspondencia()
    // {
    //     return $this->belongsTo(RelacionCorrespondencia::class, 'CodigoInterno_FK', 'CodigoInterno');
    // }
    
public function correspondencia()
    {
        // El 'Solicitud_FK' de la tabla 'relacion_correspondencia'
        // apunta al 'CodSolucitud' (PK) de esta tabla.
        return $this->hasOne(RelacionCorrespondencia::class, 'Solicitud_FK', 'CodSolucitud');
    }

    /**
     * Relación: Una solicitud tiene muchos archivos adjuntos.
     * (Usando la nueva tabla 'archivos_solicitud')
     */
    public function archivos()
    {
        return $this->hasMany(ArchivoSolicitud::class, 'solicitud_id', 'CodSolucitud');
    }

    /**
     * Relación: Una solicitud pertenece a un tipo.
     * (Asegúrate de que el modelo TipoSolicitud exista)
     */
    // public function tipoSolicitud()
    // {
    //     return $this->belongsTo(TipoSolicitud::class, 'TipoSolicitud_FK', 'CodTipoSolicitud');
    // }


    /**
     * ATRIBUTO DE ACCESO (Helper)
     * Para obtener el estado (Status) directamente a través de la correspondencia.
     * Uso: $solicitud->status
     */
    public function getStatusAttribute()
    {
        // Accedemos a la relación 'correspondencia' y luego a la relación 'status'
        // de ese modelo de correspondencia.
        return $this->correspondencia->status;
    }
}