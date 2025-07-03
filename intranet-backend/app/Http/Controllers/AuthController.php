<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Registro de nuevo usuario
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'correo_electronico' => 'required|string|email|max:255|unique:usuarios,correo_electronico',
            'password' => 'required|string|min:8|confirmed',
            'cargo' => 'required|string|max:100',
            'posicion' => 'required|string|max:40',
            'cedula' => 'required|string|max:25|unique:usuarios,cedula',
            'extension' => 'required|string|max:10|unique:usuarios,extension',
            'departamento' => 'required|integer|exists:departamentos,id_departamento'
        ]);

        // Crear el nuevo usuario
        $usuario = User::create([
            'nombre' => $request->nombre,
            'correo_electronico' => $request->correo_electronico,
            'password' => Hash::make($request->password),
            'cargo' => $request->cargo,
            'posicion' => $request->posicion,
            'cedula' => $request->cedula,
            'extension' => $request->extension,
            'departamento' => $request->departamento,
            'estado' => 'activo',
            'tiempo_extra' => 0,
        ]);

        // ✅ CORREGIDO: Mapa de departamentos a roles sin duplicados
        $departamentoRoles = [
            1 => 'Director',                              // Despacho superior
            2 => 'Secretaria',                           // Secretaría
            3 => 'Relaciones Públicas',                  // Relaciones públicas
            4 => 'Administración',                       // Administración
            5 => 'Recursos Humanos',                     // Recursos humanos
            6 => 'Análisis Operativo',                   // Análisis operativo
            7 => 'Análisis Estratégico',                 // Análisis estratégico
            8 => 'Asesoría Legal',                       // Asesoría legal
            9 => 'Contact Center',                       // Contact Center
            10 => 'Cooperación Internacional',           // Cooperación nacional e internacional
            11 => 'Tecnología',                          // Tecnología
        ];

        // Asignar el rol basado en el departamento seleccionado
        $departamentoId = $request->departamento;
        if (array_key_exists($departamentoId, $departamentoRoles)) {
            $rol = $departamentoRoles[$departamentoId];
            $usuario->assignRole($rol);
        }

        return response()->json([
            'status' => true,
            'message' => 'Usuario registrado correctamente',
        ], 200);
    }

    // Login de usuario
    public function login(Request $request)
    {
        try {
            $request->validate([
                'correo_electronico' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Log para debugging
            Log::info('Intento de login para: ' . $request->correo_electronico);

            // Buscar el usuario por correo electrónico
            $user = User::where('correo_electronico', $request->correo_electronico)->first();

            // ✅ CORREGIDO: Verificar si el usuario existe y la contraseña es correcta
            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Login fallido para: ' . $request->correo_electronico);

                // ✅ SINTAXIS CORREGIDA: Sin ->status()
                return response()->json([
                    'message' => 'Las credenciales proporcionadas son incorrectas',
                    'errors' => [
                        'correo_electronico' => ['Las credenciales proporcionadas son incorrectas']
                    ]
                ], 401);
            }

            // Verificar estado del usuario
            if ($user->estado !== 'activo') {
                Log::warning('Usuario inactivo intentando login: ' . $request->correo_electronico);
                return response()->json([
                    'message' => 'Usuario inactivo. Contacte al administrador.',
                ], 403);
            }

            // Eliminar tokens existentes antes de crear uno nuevo
            $user->tokens()->delete();

            // Crear token con nombre específico
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Login exitoso para: ' . $request->correo_electronico);

            return response()->json([
                'message' => 'Has entrado correctamente',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'correo_electronico' => $user->correo_electronico,
                    'cargo' => $user->cargo,
                    'departamento' => $user->departamento,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    // Logout de usuario
    public function logout(Request $request)
    {
        try {
            // Verificar que el usuario esté autenticado
            if (!$request->user()) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            // Eliminar todos los tokens del usuario
            $request->user()->tokens()->delete();

            Log::info('Logout exitoso para usuario ID: ' . $request->user()->id);

            return response()->json(['message' => 'Has salido correctamente'], 200);

        } catch (\Exception $e) {
            Log::error('Error en logout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }
}
