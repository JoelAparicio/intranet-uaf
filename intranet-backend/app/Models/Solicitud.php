<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'observacion',
        'motivo',
        'id_tipo_solicitud',
        'id_usuario',
        'estado',
        'deleted',
        'tiempo_utilizado',
        'fecha_creacion',
        'trabajo_realizado',
        'justificacion',
        'tiempo_laborado'
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
    public function tipo_solicitud()
    {
        return $this->belongsTo(tipo_solicitud::class, 'id_tipo_solicitud', 'id_tipo_solicitud');
    }
    public function documentos()
    {
        return $this->hasMany(Documentos::class, 'id_solicitud', 'id_solicitud');
    }

    
}

