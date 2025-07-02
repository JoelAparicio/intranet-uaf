<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Birthday extends Model
{
    use HasFactory, SoftDeletes;
    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s'); // No changes needed here
    }

    protected $table = 'birthdays';
    protected $primaryKey = 'id_birthday';

    protected $fillable = [
        'usuario', // temporal, luego cambiar a id_usuario
        'id_departamento',
        'fecha_birthday',
        'deleted',
        'deleted_at',
    ];

    public $timestamps = false;
    

    public function departmentos()
    {
        return $this->belongsTo(departamentos::class, 'id_departamento', 'id_departamento');
    }

    protected $dates = ['deleted_at'];
}
