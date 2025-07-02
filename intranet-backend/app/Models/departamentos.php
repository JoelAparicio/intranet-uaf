<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departamentos extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class, 'departamento');
    }

    public function birthdays()
    {
        return $this->hasMany(birthdays::class, 'id_departamento', 'id_departamento');
    }
}
