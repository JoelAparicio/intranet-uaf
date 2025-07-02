<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Documentos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'nombre_documento',
        'path',
        'id_solicitud',
        'deleted',
        'fecha_creacion'
    ];

    public $timestamps = false;

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud', 'id_solicitud');
    }
    
}

