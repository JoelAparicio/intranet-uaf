<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'usuarios';

    protected $guard_name = 'api';

    protected $fillable = [
        'nombre',
        'correo_electronico',
        'password',
        'cargo',
        'posicion',
        'cedula',
        'extension',
        'estado',
        'departamento',
        'tiempo_extra',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dates = ['deleted_at'];

    public $timestamps = false;

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s');
    }

    public function department()
    {
        return $this->belongsTo(departamentos::class, 'departamento', 'id_departamento');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'id_usuario', 'id');
    }
    

}
