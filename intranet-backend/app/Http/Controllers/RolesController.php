<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RRHHCacheService;

class RolesController extends Controller
{
    public function Roles_Usuario(Request $request)
    {
        $user = $request->user();
        $roles = $user->roles->pluck('name');

        return response()->json(['roles' => $roles, 'id_usuario' => $user->id, 'firma_path' => $user->firma_path]);
    }

    public function listar_roles()
    {
        $roles = Role::select('roles.name', DB::raw('COUNT(usuarios.id) as usuarios_count'))
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('usuarios', 'model_has_roles.model_id', '=', 'usuarios.id')
            ->where('roles.deleted', 0)
            ->whereNull('roles.deleted_at')
            ->groupBy('roles.name')
            ->get();

        foreach ($roles as $role) {
            $usuarios = DB::table('usuarios')
                ->select('usuarios.correo_electronico', 'usuarios.id')
                ->leftJoin('model_has_roles', 'usuarios.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', $role->name)
                ->get();

            $role->usuarios = $usuarios;
        }

        return response()->json($roles, 200);
    }

    public function agregar_rol(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);

        return response()->json([
            'status' => true,
            'message' => 'Rol creado correctamente',
            'data' => $role
        ], 201);
    }

    public function asignar_rol(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:usuarios,id',
            'role' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Role::where('name', $value)->where('guard_name', 'api')->whereNull('deleted_at')->exists()) {
                        $fail('El rol no existe o ha sido eliminado.');
                    }
                }
            ]
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::where('name', $request->role)->where('guard_name', 'api')->first();

        $user->syncRoles([$role->name]);

        // ===== NUEVA IMPLEMENTACIÓN: INVALIDAR CACHE SI SE ASIGNA ROL RRHH =====
        $rolesRRHH = ['Recursos Humanos', 'Jefe de Recursos Humanos'];
        if (in_array($role->name, $rolesRRHH)) {
            RRHHCacheService::invalidarCache('asignacion_rol_rrhh');
            Log::info('Cache RRHH invalidado por asignación de rol', [
                'usuario_id' => $user->id,
                'usuario_nombre' => $user->nombre,
                'rol_asignado' => $role->name
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Rol asignado correctamente'
        ], 200);
    }

    public function eliminarRol($name, Request $request)
    {
        $role = Role::where('name', $name)->first();
        if ($role) {
            $role->deleted = 1;
            $role->delete();
            $role->save();

            return response()->json(['success' => true, 'message' => 'Rol eliminado exitosamente']);
        }

        return response()->json(['success' => false, 'message' => 'Rol no encontrado'], 404);
    }

    public function desasignar_rol(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:usuarios,id',
            'role' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Role::where('name', $value)->where('guard_name', 'api')->whereNull('deleted_at')->exists()) {
                        $fail('El rol no existe o ha sido eliminado.');
                    }
                }
            ]
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::where('name', $request->role)->where('guard_name', 'api')->first();

        $user->removeRole($role->name);

        // ===== NUEVA IMPLEMENTACIÓN: INVALIDAR CACHE SI SE QUITA ROL RRHH =====
        $rolesRRHH = ['Recursos Humanos', 'Jefe de Recursos Humanos'];
        if (in_array($role->name, $rolesRRHH)) {
            RRHHCacheService::invalidarCache('desasignacion_rol_rrhh');
            Log::info('Cache RRHH invalidado por desasignación de rol', [
                'usuario_id' => $user->id,
                'usuario_nombre' => $user->nombre,
                'rol_removido' => $role->name
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Rol desasignado correctamente'
        ], 200);
    }

    public function roles_de_usuario($id)
    {
        $user = User::findOrFail($id);
        $roles = $user->roles->pluck('name');

        return response()->json(['roles' => $roles]);
    }
}
