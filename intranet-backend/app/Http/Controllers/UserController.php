<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use App\Services\RRHHCacheService;

class UserController extends Controller
{
    public function information_user(Request $request)
    {
        try {
            $user = $request->user()->load('department');

            return response()->json([
                'status' => true,
                'message' => 'Información del usuario',
                'data' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'correo_electronico' => $user->correo_electronico,
                    'cargo' => $user->cargo,
                    'posicion' => $user->posicion,
                    'cedula' => $user->cedula,
                    'extension' => $user->extension,
                    'estado' => $user->estado,
                    'departamento' => $user->department->nombre ?? 'No asignado',
                    'tiempo_extra' => $user->tiempo_extra,
                    'firma_path' => $user->firma_path,
                    'firma_url' => $user->firma_path ? asset('storage/' . $user->firma_path) : null
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en information_user: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener información del usuario'
            ], 500);
        }
    }

    public function edit_information(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'cargo' => 'required|string|max:100',
                'posicion' => 'required|string|max:40',
                'extension' => 'required|string|max:10',
            ]);

            $user = $request->user();

            $user->nombre = $request->nombre;
            $user->cargo = $request->cargo;
            $user->posicion = $request->posicion;
            $user->extension = $request->extension;

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Información actualizada correctamente',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en edit_information: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar información'
            ], 500);
        }
    }

    public function uploadFirma(Request $request)
    {
        try {
            $request->validate([
                'firma' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            $user = $request->user();
            $file = $request->file('firma');

            // Verificar que GD está disponible
            if (!extension_loaded('gd')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Extensión GD no disponible en el servidor'
                ], 500);
            }

            $path = $file->getRealPath();

            // Crear imagen desde archivo
            $image = imagecreatefromstring(file_get_contents($path));

            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo procesar la imagen',
                ], 500);
            }

            // Obtener dimensiones
            $width = imagesx($image);
            $height = imagesy($image);

            // Crear nueva imagen con canal alpha
            $output = imagecreatetruecolor($width, $height);
            imagesavealpha($output, true);
            $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
            imagefill($output, 0, 0, $transparent);

            // Reemplazar fondo blanco por transparente
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Si es casi blanco, hacerlo transparente
                    if ($r > 240 && $g > 240 && $b > 240) {
                        imagesetpixel($output, $x, $y, $transparent);
                    } else {
                        imagesetpixel($output, $x, $y, $rgb);
                    }
                }
            }

            // Crear directorio si no existe
            $firmasDir = storage_path('app/public/firmas');
            if (!file_exists($firmasDir)) {
                mkdir($firmasDir, 0755, true);
            }

            // Eliminar firma anterior si existe
            if ($user->firma_path) {
                $oldFirmaPath = storage_path('app/public/' . $user->firma_path);
                if (file_exists($oldFirmaPath)) {
                    unlink($oldFirmaPath);
                }
            }

            // Guardar como PNG transparente
            $filename = 'firma_' . $user->id . '_' . time() . '.png';
            $fullPath = storage_path("app/public/firmas/{$filename}");

            if (!imagepng($output, $fullPath)) {
                imagedestroy($image);
                imagedestroy($output);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la imagen'
                ], 500);
            }

            imagedestroy($image);
            imagedestroy($output);

            // Actualizar en base de datos
            $user->firma_path = "firmas/{$filename}";
            $user->save();

            // ===== NUEVA IMPLEMENTACIÓN: INVALIDAR CACHE SI ES USUARIO RRHH =====
            // Verificar si el usuario tiene roles RRHH y invalidar cache
            $rolesRRHH = ['Recursos Humanos', 'Jefe de Recursos Humanos'];
            $esUsuarioRRHH = $user->roles()->whereIn('name', $rolesRRHH)->exists();

            if ($esUsuarioRRHH) {
                RRHHCacheService::invalidarCache('actualizacion_firma_rrhh');
                Log::info('Cache RRHH invalidado por actualización de firma', [
                    'usuario_id' => $user->id,
                    'usuario_nombre' => $user->nombre,
                    'nueva_firma' => $user->firma_path
                ]);
            }

            // Recargar el usuario con la relación department
            $user = $user->fresh(['department']);

            return response()->json([
                'success' => true,
                'message' => 'Firma guardada correctamente',
                'data' => [
                    'firma_path' => $user->firma_path,
                    'firma_url' => asset('storage/' . $user->firma_path),
                    'cache_invalidado' => $esUsuarioRRHH,
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'correo_electronico' => $user->correo_electronico,
                        'cargo' => $user->cargo,
                        'posicion' => $user->posicion,
                        'cedula' => $user->cedula,
                        'extension' => $user->extension,
                        'estado' => $user->estado,
                        'departamento' => $user->department->nombre ?? 'No asignado',
                        'tiempo_extra' => $user->tiempo_extra,
                        'firma_path' => $user->firma_path
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo inválido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en uploadFirma: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function directorioUsers()
    {
        try {
            $adminRoleId = Role::where('name', 'Administrador')->first()->id;
            $users = User::whereDoesntHave('roles', function ($query) use ($adminRoleId) {
                $query->where('role_id', $adminRoleId);
            })
                ->with('department')
                ->select('id', 'nombre', 'correo_electronico', 'departamento', 'extension')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'correo_electronico' => $user->correo_electronico,
                        'departamento' => $user->department->nombre ?? 'No asignado',
                        'extension' => $user->extension,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Lista de usuarios',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en directorioUsers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener directorio de usuarios'
            ], 500);
        }
    }

    public function listarUsuarios()
    {
        try {
            $users = User::with('department')
                ->select('id','nombre', 'correo_electronico', 'departamento', 'extension')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'correo_electronico' => $user->correo_electronico,
                        'departamento' => $user->department->nombre ?? 'No asignado',
                        'extension' => $user->extension,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Lista de usuarios',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en listarUsuarios: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al listar usuarios'
            ], 500);
        }
    }

    public function administrar_usuarios()
    {
        try {
            $users = User::with('roles', 'department')
                ->select('id', 'nombre', 'correo_electronico', 'estado', 'cargo', 'posicion', 'cedula', 'extension', 'departamento', 'tiempo_extra')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'correo_electronico' => $user->correo_electronico,
                        'roles' => $user->roles->pluck('name'),
                        'estado' => $user->estado == 'activo' ? 'Activo' : 'Inactivo',
                        'cargo' => $user->cargo,
                        'posicion' => $user->posicion,
                        'cedula' => $user->cedula,
                        'extension' => $user->extension,
                        'departamento' => $user->department->id_departamento ?? null,
                        'departamento_nombre' => $user->department->nombre ?? 'No asignado',
                        'tiempo_extra' => $user->tiempo_extra,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Lista de usuarios',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en administrar_usuarios: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener usuarios para administración'
            ], 500);
        }
    }

    public function borrar_usuario($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                $user->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Usuario eliminado correctamente'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error en borrar_usuario: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar usuario'
            ], 500);
        }
    }

    public function actualizar_usuario(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'correo_electronico' => 'required|string|email|max:255|unique:usuarios,correo_electronico,' . $id,
                'cargo' => 'required|string|max:100',
                'posicion' => 'required|string|max:40',
                'cedula' => 'required|string|max:25|unique:usuarios,cedula,' . $id,
                'extension' => 'required|string|max:10|unique:usuarios,extension,' . $id,
                'departamento' => 'nullable|exists:departamentos,id_departamento',
                'tiempo_extra' => 'required|integer',
            ]);

            $user = User::findOrFail($id);

            $user->nombre = $request->input('nombre');
            $user->correo_electronico = $request->input('correo_electronico');
            $user->cargo = $request->input('cargo');
            $user->posicion = $request->input('posicion');
            $user->cedula = $request->input('cedula');
            $user->extension = $request->input('extension');
            $user->departamento = $request->input('departamento');
            $user->tiempo_extra = $request->input('tiempo_extra');

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en actualizar_usuario: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar usuario'
            ], 500);
        }
    }

    public function actualizar_estado(Request $request, $id)
    {
        try {
            $request->validate([
                'estado' => 'required|string|in:activo,inactivo',
            ]);

            $user = User::findOrFail($id);
            $user->estado = $request->input('estado');
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Estado del usuario actualizado correctamente',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en actualizar_estado: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar estado del usuario'
            ], 500);
        }
    }
}
