<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

class Aprobaciones extends Model
{
    use HasFactory, SoftDeletes, HasRoles;
    // public function fromDateTime($value)
    // {
    //     return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s'); // No changes needed here
    // }

    protected $table = 'aprobaciones';
    protected $primaryKey = 'id_aprobacion';

    protected $fillable = [
        'id_solicitud',
        'id_usuario_solicitud',
        'id_jefe_departamento',
        'id_rrhh',
        'aprobado_jefe',
        'aprobado_rrhh',
        'comentarios_jefe',
        'comentarios_rrhh',
        'fecha_finalizacion',
        'deleted'
    ];

    protected $dates = ['deleted_at', 'fecha_finalizacion'];

    public function solicitud()
    {
        return $this->belongsTo(Solicitudes::class, 'id_solicitud', 'id_solicitud');
    }

    public function jefeDepartamento()
    {
        return $this->belongsTo(User::class, 'id_usuario_solicitud', 'id');
    }
    public $timestamps = false;
}
