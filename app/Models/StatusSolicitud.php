<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusSolicitud extends Model
{
    protected $table = 'status_solicitud';
    protected $primaryKey = 'CodStatusSolicitud';
    public $timestamps = false;
    
    protected $fillable = ['CodStatusSolicitud', 'NombreStatusSolicitud'];
}
