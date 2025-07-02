<?php

namespace App\Http\Controllers;

use App\Models\Solicitudes;
use App\Models\User;
use App\Models\Aprobaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\DocumentController;
use App\Jobs\GenerarPdfSolicitudJob;

class SolicitudesController extends Controller
{
    public function insertar_solicitud(Request $request)
    {
        try {
            Log::info('Iniciando la solicitud de inserción...');
            Log::info('Datos de la solicitud: ', $request->all());

            // Validación base
            $rules = [
                'fecha_inicio' => 'required|date_format:Y-m-d H:i',
                'fecha_fin' => 'sometimes|nullable|date_format:Y-m-d H:i',
                'tipo_solicitud' => 'required|integer',
            ];

            // Validaciones específicas por tipo de solicitud
            if ($request->tipo_solicitud == 1) {
                // Permiso
                $rules['motivo'] = 'required|string';
                $rules['observacion'] = 'sometimes|string|max:255';
            } elseif ($request->tipo_solicitud == 2) {
                // Reincorporación
                $rules['motivo'] = 'required|string';
                $rules['observacion'] = 'sometimes|string|max:255';
            } elseif ($request->tipo_solicitud == 3) {
                // Tiempo compensatorio
                $rules['observacion'] = 'sometimes|string|max:255';
                $rules['tiempo_utilizado'] = 'sometimes|nullable|string|max:255';
            } elseif ($request->tipo_solicitud == 4) {
                // Vacaciones
                $rules['salario'] = 'required|numeric|min:0';
                $rules['dias'] = 'required|integer|min:1';
            } elseif ($request->tipo_solicitud == 5) {
                // Horas extraordinarias
                $rules['trabajo_realizado'] = 'required|string';
                $rules['justificacion'] = 'required|string';
                $rules['observacion'] = 'sometimes|string|max:255';
            }

            $request->validate($rules);

            $user = Auth::user();

            $fecha_inicio = Carbon::createFromFormat('Y-m-d H:i', $request->fecha_inicio, 'America/Panama');
            $fecha_fin = $request->fecha_fin ? Carbon::createFromFormat('Y-m-d H:i', $request->fecha_fin, 'America/Panama') : null;

            $fecha_creacion = Carbon::now('America/Panama');

            // Variables para almacenar en la BD
            $tiempo_utilizado = null;
            $tiempo_laborado = null;
            $salario = null;
            $dias = null;

            if ($request->tipo_solicitud == 1) {
                // Permiso
                if ($fecha_fin) {
                    $diffDays = $fecha_fin->diffInDays($fecha_inicio);
                    $diffHrs = $fecha_fin->diffInHours($fecha_inicio) % 24;
                    $diffMins = $fecha_fin->diffInMinutes($fecha_inicio) % 60;
                    $tiempo_utilizado = "días: {$diffDays}, horas: {$diffHrs}, minutos: {$diffMins}";
                }
            } elseif ($request->tipo_solicitud == 3) {
                // Tiempo compensatorio
                $tiempo_utilizado = $request->tiempo_utilizado ?? null;

                $diffHrs = $fecha_fin->diffInHours($fecha_inicio);
                $tiempo_extra_actual = is_numeric($user->tiempo_extra) ? $user->tiempo_extra : 0;

                if ($diffHrs > $tiempo_extra_actual) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No se puede solicitar más tiempo del que se tiene disponible',
                    ], 400);
                } else {
                    $user->tiempo_extra = $tiempo_extra_actual - $diffHrs;
                    $user->save();
                }
            } elseif ($request->tipo_solicitud == 4) {
                // Vacaciones - Almacenar salario en observación como JSON
                $salario = $request->salario;
                $dias = $request->dias;
                $tiempo_utilizado = "días: {$dias}";

                // Crear un objeto con los datos de vacaciones
                $datosVacaciones = [
                    'salario' => $salario,
                    'dias' => $dias,
                    'observacion_usuario' => $request->observacion ?? ''
                ];

                // Almacenar como JSON en el campo observación
                $request->merge(['observacion' => json_encode($datosVacaciones)]);

                Log::info('Procesando vacaciones - Salario: ' . $salario . ', Días: ' . $dias);
            } elseif ($request->tipo_solicitud === 5) {
                // Horas extraordinarias
                $diffHrs = $fecha_fin->diffInHours($fecha_inicio) % 24;
                $diffMins = $fecha_fin->diffInMinutes($fecha_inicio) % 60;
                $tiempo_utilizado = "horas: {$diffHrs}, minutos: {$diffMins}";
                $tiempo_laborado = $tiempo_utilizado;

                $tiempo_extra_actual = is_numeric($user->tiempo_extra) ? $user->tiempo_extra : 0;
                $user->tiempo_extra = $tiempo_extra_actual + $diffHrs;
                $user->save();
            } else {
                $tiempo_utilizado = $request->tiempo_utilizado ?? null;
            }

            // Crear la solicitud
            $dataSolicitud = [
                'fecha_inicio' => $fecha_inicio->format('Y-m-d H:i'),
                'fecha_fin' => $fecha_fin ? $fecha_fin->format('Y-m-d H:i') : null,
                'observacion' => $request->observacion ?? null,
                'motivo' => $request->motivo ?? null,
                'id_tipo_solicitud' => $request->tipo_solicitud,
                'id_usuario' => $user->id,
                'estado' => 'pendiente',
                'deleted' => false,
                'tiempo_utilizado' => $tiempo_utilizado,
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'trabajo_realizado' => $request->trabajo_realizado ?? null,
                'justificacion' => $request->justificacion ?? null,
                'tiempo_laborado' => $tiempo_laborado ?? null,
            ];

            // Agregar campos específicos de vacaciones
            if ($request->tipo_solicitud == 4) {
                // NO agregar salario y dias como campos separados
                // Ya están almacenados en el campo observacion como JSON
            }

            $solicitud = Solicitudes::create($dataSolicitud);

            // Crear aprobación
            Aprobaciones::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_usuario_solicitud' => $user->id,
                'aprobado_jefe' => false,
                'aprobado_rrhh' => false,
                'comentarios_jefe' => null,
                'comentarios_rrhh' => null,
                'fecha_finalizacion' => null,
                'deleted' => false,
            ]);

            // ===== NUEVA IMPLEMENTACIÓN CON JOBS =====
            // Encolar generación de PDF inicial en lugar de hacerlo síncronamente
            try {
                // Dispatch del job con prioridad normal para creación inicial
                GenerarPdfSolicitudJob::dispatch($solicitud->id_solicitud, 'creacion_inicial');

                Log::info("PDF inicial encolado para solicitud {$solicitud->id_solicitud}", [
                    'tipo_solicitud' => $request->tipo_solicitud,
                    'usuario' => $user->id
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Solicitud registrada correctamente. El documento se está generando.',
                    'id_solicitud' => $solicitud->id_solicitud,
                    'pdf_en_proceso' => true
                ], 200);

            } catch (\Exception $e) {
                // Si falla el encolado, intentar generación síncrona como fallback
                Log::warning("Error al encolar PDF inicial para solicitud {$solicitud->id_solicitud}, intentando generación síncrona", [
                    'error' => $e->getMessage()
                ]);

                try {
                    $documentController = new DocumentController();
                    $documentRequest = new Request($request->all());
                    $documentRequest->merge(['id_solicitud' => $solicitud->id_solicitud]);
                    $documentResponse = $documentController->crear_permiso($documentRequest);

                    return response()->json([
                        'status' => true,
                        'message' => 'Solicitud registrada correctamente',
                        'id_solicitud' => $solicitud->id_solicitud,
                        'document_response' => json_decode($documentResponse->getContent()),
                        'pdf_en_proceso' => false
                    ], 200);

                } catch (\Exception $syncError) {
                    Log::error("Error en generación síncrona de PDF para solicitud {$solicitud->id_solicitud}", [
                        'error' => $syncError->getMessage()
                    ]);

                    // La solicitud se creó exitosamente, solo falló el PDF
                    return response()->json([
                        'status' => true,
                        'message' => 'Solicitud registrada correctamente. El documento se generará más tarde.',
                        'id_solicitud' => $solicitud->id_solicitud,
                        'pdf_error' => true
                    ], 200);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error al registrar la solicitud: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al registrar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function historial_solicitud(Request $request)
    {
        $userId = $request->user()->id;
        $solicitudes = Solicitudes::with(['tipo_solicitud', 'usuario'])
            ->where('id_usuario', $userId)
            ->where('deleted', false)
            ->get();

        return response()->json($solicitudes);
    }

    /**
     * Obtener una solicitud específica
     */
    public function obtener_solicitud(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;

            $solicitud = Solicitudes::with(['tipo_solicitud', 'usuario'])
                ->where('id_solicitud', $id)
                ->where('id_usuario', $userId) // Solo puede ver sus propias solicitudes
                ->where('deleted', false)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'status' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $solicitud
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener solicitud: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la solicitud'
            ], 500);
        }
    }

    /**
     * Actualizar una solicitud existente
     */
    public function actualizar_solicitud(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Buscar la solicitud
            $solicitud = Solicitudes::where('id_solicitud', $id)
                ->where('id_usuario', $user->id) // Solo puede editar sus propias solicitudes
                ->where('deleted', false)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'status' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // Verificar que la solicitud esté en estado pendiente
            if ($solicitud->estado !== 'pendiente') {
                return response()->json([
                    'status' => false,
                    'message' => 'Solo se pueden editar solicitudes en estado pendiente'
                ], 400);
            }

            // Validar los datos de entrada
            $motivosValidos = [
                'Enfermedad', 'Duelo', 'Matrimonio', 'Nacimiento de hijos',
                'Enfermedades de parientes cercanos', 'Eventos académicos puntuales',
                'Permisos personales', 'Misión oficial', 'Seminarios', 'Otros, especifique'
            ];

            $validationRules = [
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'observacion' => 'nullable|string|max:255',
                'justificacion' => 'nullable|string|max:500',
                'trabajo_realizado' => 'nullable|string|max:500'
            ];

            // Validaciones específicas por tipo
            if ($solicitud->id_tipo_solicitud == 1) {
                $validationRules['motivo'] = 'required|string|in:' . implode(',', $motivosValidos);
            } elseif ($solicitud->id_tipo_solicitud == 4) {
                // Vacaciones
                $validationRules['salario'] = 'required|numeric|min:0';
                $validationRules['dias'] = 'required|integer|min:1';
            } else {
                $validationRules['motivo'] = 'nullable|string|max:500';
            }

            $request->validate($validationRules);

            // Convertir fechas
            $fecha_inicio = Carbon::createFromFormat('Y-m-d', $request->fecha_inicio)->startOfDay();
            $fecha_fin = $request->fecha_fin ? Carbon::createFromFormat('Y-m-d', $request->fecha_fin)->endOfDay() : null;

            // Recalcular tiempo utilizado según el tipo de solicitud
            $tiempo_utilizado = null;
            $tiempo_laborado = null;

            if ($solicitud->id_tipo_solicitud == 1) { // Permisos
                if ($fecha_fin) {
                    $diffDays = $fecha_fin->diffInDays($fecha_inicio);
                    $diffHrs = $fecha_fin->diffInHours($fecha_inicio) % 24;
                    $diffMins = $fecha_fin->diffInMinutes($fecha_inicio) % 60;
                    $tiempo_utilizado = "días: {$diffDays}, horas: {$diffHrs}, minutos: {$diffMins}";
                }
            } elseif ($solicitud->id_tipo_solicitud == 3) { // Tiempo compensatorio
                if ($fecha_fin) {
                    $diffHrs = $fecha_fin->diffInHours($fecha_inicio);
                    $tiempo_extra_actual = is_numeric($user->tiempo_extra) ? $user->tiempo_extra : 0;

                    // Revisar si hay cambios en las horas y ajustar tiempo extra
                    $hora_anterior = $solicitud->fecha_fin ?
                        Carbon::parse($solicitud->fecha_fin)->diffInHours(Carbon::parse($solicitud->fecha_inicio)) : 0;

                    // Restaurar las horas de la solicitud anterior
                    $user->tiempo_extra = $tiempo_extra_actual + $hora_anterior;

                    // Verificar si las nuevas horas son válidas
                    if ($diffHrs > $user->tiempo_extra) {
                        return response()->json([
                            'status' => false,
                            'message' => 'No se puede solicitar más tiempo del que se tiene disponible'
                        ], 400);
                    }

                    // Aplicar las nuevas horas
                    $user->tiempo_extra = $user->tiempo_extra - $diffHrs;
                    $user->save();

                    $tiempo_utilizado = "horas: {$diffHrs}";
                }
            } elseif ($solicitud->id_tipo_solicitud == 5) { // Horas extraordinarias
                if ($fecha_fin) {
                    $diffHrs = $fecha_fin->diffInHours($fecha_inicio) % 24;
                    $diffMins = $fecha_fin->diffInMinutes($fecha_inicio) % 60;
                    $tiempo_utilizado = "horas: {$diffHrs}, minutos: {$diffMins}";
                    $tiempo_laborado = $tiempo_utilizado;

                    // Ajustar tiempo extra
                    $hora_anterior = $solicitud->fecha_fin ?
                        Carbon::parse($solicitud->fecha_fin)->diffInHours(Carbon::parse($solicitud->fecha_inicio)) % 24 : 0;

                    $tiempo_extra_actual = is_numeric($user->tiempo_extra) ? $user->tiempo_extra : 0;
                    $user->tiempo_extra = ($tiempo_extra_actual - $hora_anterior) + $diffHrs;
                    $user->save();
                }
            }

            // Actualizar la solicitud según su tipo
            $updateData = [
                'fecha_inicio' => $fecha_inicio->format('Y-m-d H:i'),
                'observacion' => $request->observacion,
            ];

            // Campos específicos por tipo
            if ($solicitud->id_tipo_solicitud == 1) { // Permiso
                $updateData['motivo'] = $request->motivo;
                $updateData['fecha_fin'] = $fecha_fin ? $fecha_fin->format('Y-m-d H:i') : null;
                $updateData['tiempo_utilizado'] = $tiempo_utilizado;

            } elseif ($solicitud->id_tipo_solicitud == 2) { // Reincorporación
                $updateData['motivo'] = $request->motivo;

            } elseif ($solicitud->id_tipo_solicitud == 3) { // Tiempo compensatorio
                $updateData['fecha_fin'] = $fecha_fin ? $fecha_fin->format('Y-m-d H:i') : null;
                $updateData['tiempo_utilizado'] = $tiempo_utilizado;

            } elseif ($solicitud->id_tipo_solicitud == 4) { // Vacaciones
                $updateData['fecha_fin'] = $fecha_fin ? $fecha_fin->format('Y-m-d H:i') : null;

                // Para vacaciones, almacenar datos como JSON en observación
                $datosVacaciones = [
                    'salario' => $request->salario,
                    'dias' => $request->dias,
                    'observacion_usuario' => $request->observacion ?? ''
                ];
                $updateData['observacion'] = json_encode($datosVacaciones);

                if ($fecha_fin) {
                    $updateData['tiempo_utilizado'] = "días: {$request->dias}";
                }

            } elseif ($solicitud->id_tipo_solicitud == 5) { // Horas extraordinarias
                $updateData['fecha_fin'] = $fecha_fin ? $fecha_fin->format('Y-m-d H:i') : null;
                $updateData['trabajo_realizado'] = $request->trabajo_realizado;
                $updateData['justificacion'] = $request->justificacion;
                $updateData['tiempo_utilizado'] = $tiempo_utilizado;
                $updateData['tiempo_laborado'] = $tiempo_laborado;
            }

            $solicitud->update($updateData);

            // ===== REGENERAR PDF CON JOBS =====
            // Regenerar el PDF con los nuevos datos usando Jobs
            try {
                GenerarPdfSolicitudJob::dispatch($solicitud->id_solicitud, 'actualizacion');

                Log::info("PDF de actualización encolado para solicitud {$solicitud->id_solicitud}", [
                    'tipo_solicitud' => $solicitud->id_tipo_solicitud,
                    'usuario' => $user->id
                ]);

            } catch (\Exception $e) {
                // Si falla el encolado, intentar generación síncrona como fallback
                Log::warning('Error al encolar PDF de actualización: ' . $e->getMessage());

                try {
                    $documentController = new DocumentController();
                    $documentRequest = new Request();
                    $documentRequest->merge(['id_solicitud' => $solicitud->id_solicitud]);
                    $documentController->crear_permiso($documentRequest);
                } catch (\Exception $syncError) {
                    Log::warning('Error en regeneración síncrona de PDF: ' . $syncError->getMessage());
                    // No fallar la actualización por un error en el PDF
                }
            }

            // Recargar la solicitud con relaciones
            $solicitud = $solicitud->fresh(['tipo_solicitud', 'usuario']);

            return response()->json([
                'status' => true,
                'message' => 'Solicitud actualizada correctamente',
                'data' => $solicitud
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar solicitud: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar la solicitud'
            ], 500);
        }
    }
}
