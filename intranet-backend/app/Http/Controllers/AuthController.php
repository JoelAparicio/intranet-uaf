<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;


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
            'estado' => 'activo', // Valor por defecto
            'deleted' => false, // Valor por defecto
        ]);

        // Mapa de departamentos a roles
        $departamentoRoles = [
            1 => 'Director',
            1 => 'Subdirector',
            2 => 'Secretaria',
            3 => 'Relaciones Públicas',
            4 => 'Administración',
            5 => 'Recursos Humanos',
            6 => 'Análisis Operativo',
            7 => 'Análisis Estratégico',
            8 => 'Asesoría Legal',
            9 => 'Contact Center',
            10 => 'Cooperación Nacional e Internacional',
            11 => 'Tecnología',
            // Añade más departamentos y roles según sea necesario
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
        $request->validate([
            'correo_electronico' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Buscar el usuario por correo electrónico
        $user = User::where('correo_electronico', $request->correo_electronico)->first();

        // Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['Las credenciales proporcionadas son incorrectas'],
            ])->status(400);
        }

        return response()->json([
            'message' => 'Has entrado correctamente',
            'token' => $user->createToken('Auth Token')->plainTextToken,
        ], 200);
    }

    // Logout de usuario
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Has salido correctamente'], 200);
    }

}


