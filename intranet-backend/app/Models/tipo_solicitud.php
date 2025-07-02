<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipo_solicitud extends Model
{
    use HasFactory;
    protected $table = 'tipos_de_solicitud';
    protected $primaryKey = 'id_tipo_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'tipo_solicitud',
        'descripcion',
        'deleted',
        'deleted_at'
    ];

    public function solicitudes()
    {
        return $this->hasMany(solicitudes::class, 'id_tipo_solicitud', 'id_tipo_solicitud');
    }
}
