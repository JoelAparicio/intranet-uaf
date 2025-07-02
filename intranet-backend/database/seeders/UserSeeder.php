<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Usuario Administrador (igual que antes)
        $admin = User::create([
            'nombre'             => 'Usuario Administrador',
            'correo_electronico' => 'joelaparicio454@gmail.com',
            'cargo'              => 'Administrador',
            'posicion'           => 'Administrador',
            'cedula'             => 'Admin-8-977-47',
            'extension'          => 'Admin',
            'estado'             => 'activo',
            'departamento'       => 11,                 // Tecnología
            'tiempo_extra'       => 0,
            'password'           => Hash::make('Andres0125@'),
        ]);
        $admin->assignRole('Administrador');

        // 2) Mapeo de roles → departamento (id_departamento) con nombres cortos
        $userConfigs = [
            'Jefe de Tecnología' => [
                'dept' => 11,
                'cargo_corto' => 'Jefe de Tecnología',
                'posicion_corta' => 'Jefe Tecnología'
            ],
            'Tecnología' => [
                'dept' => 11,
                'cargo_corto' => 'Tecnología',
                'posicion_corta' => 'Analista TI'
            ],
            'Jefe de Relaciones Públicas' => [
                'dept' => 3,
                'cargo_corto' => 'Jefe RR.PP.',
                'posicion_corta' => 'Jefe RR.PP.'
            ],
            'Relaciones Públicas' => [
                'dept' => 3,
                'cargo_corto' => 'Relaciones Públicas',
                'posicion_corta' => 'Analista RR.PP.'
            ],
            'Jefe de Administración' => [
                'dept' => 4,
                'cargo_corto' => 'Jefe Administración',
                'posicion_corta' => 'Jefe Admin.'
            ],
            'Administración' => [
                'dept' => 4,
                'cargo_corto' => 'Administración',
                'posicion_corta' => 'Analista Admin.'
            ],
            'Jefe de Análisis Operativo' => [
                'dept' => 6,
                'cargo_corto' => 'Jefe Análisis Op.',
                'posicion_corta' => 'Jefe Análisis Op.'
            ],
            'Análisis Operativo' => [
                'dept' => 6,
                'cargo_corto' => 'Análisis Operativo',
                'posicion_corta' => 'Analista Op.'
            ],
            'Jefe de Análisis Estratégico' => [
                'dept' => 7,
                'cargo_corto' => 'Jefe Análisis Est.',
                'posicion_corta' => 'Jefe Análisis Est.'
            ],
            'Análisis Estratégico' => [
                'dept' => 7,
                'cargo_corto' => 'Análisis Estratégico',
                'posicion_corta' => 'Analista Est.'
            ],
            'Jefe de Asesoría Legal' => [
                'dept' => 8,
                'cargo_corto' => 'Jefe Legal',
                'posicion_corta' => 'Jefe Legal'
            ],
            'Asesoría Legal' => [
                'dept' => 8,
                'cargo_corto' => 'Asesoría Legal',
                'posicion_corta' => 'Abogado'
            ],
            'Jefe de Contact Center' => [
                'dept' => 9,
                'cargo_corto' => 'Jefe Contact Center',
                'posicion_corta' => 'Jefe Contact'
            ],
            'Contact Center' => [
                'dept' => 9,
                'cargo_corto' => 'Contact Center',
                'posicion_corta' => 'Agente Contact'
            ],
            'Jefe de Cooperación Nacional e Internacional' => [
                'dept' => 10,
                'cargo_corto' => 'Jefe Cooperación',
                'posicion_corta' => 'Jefe Cooperación'
            ],
            'Cooperación Internacional' => [
                'dept' => 10,
                'cargo_corto' => 'Cooperación Int.',
                'posicion_corta' => 'Analista Coop.'
            ],
            'Jefe de Recursos Humanos' => [
                'dept' => 5,
                'cargo_corto' => 'Jefe RRHH',
                'posicion_corta' => 'Jefe RRHH'
            ],
            'Recursos Humanos' => [
                'dept' => 5,
                'cargo_corto' => 'Recursos Humanos',
                'posicion_corta' => 'Analista RRHH'
            ],
            'Director' => [
                'dept' => 1,
                'cargo_corto' => 'Director',
                'posicion_corta' => 'Director'
            ],
            'Subdirector' => [
                'dept' => 1,
                'cargo_corto' => 'Subdirector',
                'posicion_corta' => 'Subdirector'
            ],
            'Secretaria' => [
                'dept' => 2,
                'cargo_corto' => 'Secretaria',
                'posicion_corta' => 'Secretaria'
            ],
        ];

        // 3) Crear un usuario para cada rol, excepto "Administrador"
        $roles = Role::all();
        foreach ($roles as $role) {
            if ($role->name === 'Administrador') {
                continue;
            }

            // Obtener configuración del usuario o usar defaults
            $config = $userConfigs[$role->name] ?? [
                'dept' => 1,
                'cargo_corto' => Str::limit($role->name, 30),
                'posicion_corta' => Str::limit($role->name, 25)
            ];

            // Generar email único y corto
            $emailSlug = Str::limit(Str::slug($role->name, '_'), 25);

            // Generar nombre más corto para el campo nombre
            $nombreCorto = Str::limit($role->name, 45);

            $user = User::create([
                'nombre'             => $nombreCorto,
                'correo_electronico' => $emailSlug . '@uaf.gob.pa',
                'cargo'              => $config['cargo_corto'],
                'posicion'           => $config['posicion_corta'],
                'cedula'             => strtoupper(Str::substr(Str::slug($role->name, ''), 0, 3))
                    . '-' . rand(1, 9) . '-' . rand(100, 999) . '-' . rand(10, 99),
                'extension'          => (string) rand(1000, 9999),
                'estado'             => 'activo',
                'departamento'       => $config['dept'],
                'tiempo_extra'       => 0,
                'password'           => Hash::make('12345678'),
            ]);

            // Asignar el rol recién creado
            $user->assignRole($role->name);
        }
    }
}
