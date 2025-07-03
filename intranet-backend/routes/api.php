<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipoSolicitudController;
use App\Http\Controllers\SolicitudesController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\AprobacionesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ CAMBIO PRINCIPAL: De 'auth:sanctum' a 'auth:api'
Route::middleware('auth:api')->group(function () {

    // ==================== AUTENTICACIÓN ====================
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==================== USUARIO ====================
    // Información del usuario autenticado
    Route::get('/user', [UserController::class, 'information_user']);
    Route::get('/information_user', [UserController::class, 'information_user']);

    // Editar información personal
    Route::put('/edit_user', [UserController::class, 'edit_information']);
    Route::put('/edit_information', [UserController::class, 'edit_information']);

    // Subir firma digital
    Route::post('/upload-firma', [UserController::class, 'uploadFirma']);

    // Directorio de usuarios
    Route::get('/directorio_usuarios', [UserController::class, 'directorioUsers']);
    Route::get('/listar_usuarios', [UserController::class, 'listarUsuarios']);

    // Roles del usuario
    Route::get('/roles_usuario', [RolesController::class, 'Roles_Usuario']);

    // ==================== CUMPLEAÑOS ====================
    Route::get('/birthdays', [BirthdayController::class, 'getBirthdays']);
    Route::post('/addBirthday', [BirthdayController::class, 'addBirthday']);
    Route::put('/birthdays/{id}', [BirthdayController::class, 'deleteBirthday']);

    // ==================== SOLICITUDES ====================
    // Tipos de solicitudes disponibles
    Route::get('/listar_solicitud', [TipoSolicitudController::class, 'listar_solicitudes']);

    // Crear nueva solicitud
    Route::post('/insertar_solicitud', [SolicitudesController::class, 'insertar_solicitud']);

    // Historial de solicitudes del usuario
    Route::get('/historial_solicitud', [SolicitudesController::class, 'historial_solicitud']);

    // Obtener solicitud específica
    Route::get('/solicitud/{id}', [SolicitudesController::class, 'obtener_solicitud']);

    // Actualizar solicitud
    Route::put('/actualizar_solicitud/{id}', [SolicitudesController::class, 'actualizar_solicitud']);

    // Generar/Obtener PDF de solicitud
    Route::post('/obtener_ruta_pdf', [DocumentController::class, 'obtenerRutaPdf']);

    // ==================== APROBACIONES ====================
    // (Filtrado por rol dentro del controlador)
    Route::get('/listar_aprobaciones', [AprobacionesController::class, 'listar_aprobaciones']);
    Route::put('/aprobar-solicitud/{id}', [AprobacionesController::class, 'aprobar_solicitud']);
    Route::put('/rechazar-solicitud/{id}', [AprobacionesController::class, 'rechazar_solicitud']);
    Route::get('/solicitudes-rechazadas', [AprobacionesController::class, 'listar_solicitudes_rechazadas']);
    Route::get('/solicitudes-aceptadas', [AprobacionesController::class, 'listar_solicitudes_aceptadas']);

    // ==================== ADMINISTRACIÓN ====================
    // Rutas exclusivas para administradores
    Route::middleware('role:Administrador')->group(function () {

        // ===== GESTIÓN DE ROLES =====
        Route::get('/roles', [RolesController::class, 'listar_roles']);
        Route::post('/agregar_roles', [RolesController::class, 'agregar_rol']);
        Route::post('/asignar_rol', [RolesController::class, 'asignar_rol']);
        Route::put('/eliminar_rol/{name}', [RolesController::class, 'eliminarRol']);
        Route::put('/desasignar_rol', [RolesController::class, 'desasignar_rol']);

        // ===== ADMINISTRACIÓN DE USUARIOS =====
        Route::get('/administrar_usuarios', [UserController::class, 'administrar_usuarios']);
        Route::put('/borrar_usuario/{id}', [UserController::class, 'borrar_usuario']);
        Route::put('/actualizar_usuario/{id}', [UserController::class, 'actualizar_usuario']);
        Route::put('/status_usuario/{id}', [UserController::class, 'actualizar_estado']);
    });
});

// Ruta de fallback para rutas no encontradas
Route::fallback(function(){
    return response()->json([
        'status' => false,
        'message' => 'Ruta no encontrada'
    ], 404);
});
