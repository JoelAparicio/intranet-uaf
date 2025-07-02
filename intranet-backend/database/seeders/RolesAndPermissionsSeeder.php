<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Creación de roles de jefes de departamento
        Role::create(['name' => 'Jefe de Tecnología', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Relaciones Públicas', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Administración', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Análisis Estratégico', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Análisis Operativo', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Asesoría Legal', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Contact Center', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Cooperación Nacional e Internacional', 'guard_name' => 'api']);
        Role::create(['name' => 'Jefe de Recursos Humanos', 'guard_name' => 'api']);

        // Creación de roles de directores
        Role::create(['name' => 'Administrador', 'guard_name' => 'api']);
        Role::create(['name' => 'Director', 'guard_name' => 'api']);
        Role::create(['name' => 'Subdirector', 'guard_name' => 'api']);

        // Creación de roles de colaboradores
        Role::create(['name' => 'Secretaria', 'guard_name' => 'api']);
        Role::create(['name' => 'Relaciones Públicas', 'guard_name' => 'api']);
        Role::create(['name' => 'Tecnología', 'guard_name' => 'api']);
        Role::create(['name' => 'Contact Center', 'guard_name' => 'api']);
        Role::create(['name' => 'Cooperación Nacional e Internacional', 'guard_name' => 'api']);
        Role::create(['name' => 'Análisis Operativo', 'guard_name' => 'api']);
        Role::create(['name' => 'Análisis Estratégico', 'guard_name' => 'api']);
        Role::create(['name' => 'Asesoría Legal', 'guard_name' => 'api']);
        Role::create(['name' => 'Administración', 'guard_name' => 'api']);
        Role::create(['name' => 'Recursos Humanos', 'guard_name' => 'api']);
    }
}
