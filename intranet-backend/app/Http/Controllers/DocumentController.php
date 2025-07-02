<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Documentos;
use App\Models\Aprobaciones;
use App\Models\User;
use App\Models\Solicitud;
use App\Services\RRHHCacheService;

class DocumentController extends Controller
{
    public function crear_permiso(Request $request)
    {
        try {
            $request->validate([
                'id_solicitud' => 'required|integer',
            ]);

            $solicitud = Solicitud::with('usuario')->where('id_solicitud', $request->id_solicitud)->firstOrFail();

            // Determinar qué tipo de documento generar según el tipo de solicitud
            switch ($solicitud->id_tipo_solicitud) {
                case 1: // Permiso
                    return $this->generarDocumentoPermiso($solicitud);
                case 2: // Reincorporación
                    return $this->generarDocumentoReincorporacion($solicitud);
                case 3: // Tiempo compensatorio
                    return $this->generarDocumentoTiempoCompensatorio($solicitud);
                case 4: // Vacaciones
                    return $this->generarDocumentoVacaciones($solicitud);
                case 5: // Horas extraordinarias
                    return $this->generarDocumentoHorasExtras($solicitud);
                default:
                    throw new \Exception("Tipo de solicitud no implementado: {$solicitud->id_tipo_solicitud}");
            }

        } catch (\Exception $e) {
            Log::error('Error al generar el documento PDF: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al generar el documento PDF',
            ], 500);
        }
    }

    private function generarDocumentoPermiso($solicitud)
    {
        $user = $solicitud->usuario;

        $fecha_inicio = Carbon::parse($solicitud->fecha_inicio);
        $fecha_fin = $solicitud->fecha_fin ? Carbon::parse($solicitud->fecha_fin) : null;
        $fecha_creacion = Carbon::now();

        $diffDays = $fecha_fin ? $fecha_fin->diffInDays($fecha_inicio) : 0;
        $diffHrs = $fecha_fin ? $fecha_fin->diffInHours($fecha_inicio) % 24 : 0;
        $diffMins = $fecha_fin ? $fecha_fin->diffInMinutes($fecha_inicio) % 60 : 0;

        // Firma del solicitante (siempre presente)
        $firma_path = null;
        if ($user->firma_path && file_exists(public_path('storage/' . $user->firma_path))) {
            $firma_path = $user->firma_path;
        }

        // Inicializar variables de firmas
        $firma_jefe_path = null;
        $fecha_firma2 = null;
        $firma_rrhh = null;
        $fecha_firma3 = null;

        $aprobacion = Aprobaciones::where('id_solicitud', $solicitud->id_solicitud)->first();

        if ($aprobacion) {
            // Lógica de firmas basada en el estado de la solicitud
            switch ($solicitud->estado) {
                case 'pendiente':
                    // Solo firma del solicitante (ya configurada arriba)
                    break;

                case 'revision':
                    // Firma del solicitante + jefe (si ya aprobó)
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }
                    break;

                case 'aprobada':
                    // Firma del solicitante + jefe + RRHH

                    // Firma del jefe
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }

                    // ===== NUEVA IMPLEMENTACIÓN CON CACHE =====
                    // Firma de RRHH (solo si RRHH ya aprobó)
                    if ($aprobacion->aprobado_rrhh) {
                        // Intentar con el RRHH específico que aprobó
                        if ($aprobacion->id_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarFirmaRRHHEspecifico($aprobacion->id_rrhh);
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }

                        // Si no se encontró firma del RRHH específico, buscar cualquier RRHH con firma
                        if (!$firma_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarPrimeraFirmaRRHH();
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }
                    }
                    break;

                case 'rechazada':
                    // Solo firma del solicitante (ya configurada arriba)
                    break;

                default:
                    // Estado desconocido, solo firma del solicitante
                    Log::warning("Estado de solicitud desconocido: {$solicitud->estado} para solicitud ID: {$solicitud->id_solicitud}");
                    break;
            }
        }

        $datos_pdf = [
            'id' => $solicitud->id_solicitud,
            'nombre' => $user->nombre,
            'cedula' => $user->cedula,
            'motivo' => $solicitud->motivo ?? 'N/A',
            'puesto' => $user->cargo ?? 'N/A',
            'posicion' => $user->posicion ?? 'N/A',
            'unidad' => 'Unidad de Análisis Financiero',
            'otro_motivo' => $solicitud->otro_motivo ?? '',
            'observacion' => $solicitud->observacion ?? '',
            'hora_inicio' => $fecha_inicio->format('H:i'),
            'dia_inicio' => $fecha_inicio->format('d'),
            'mes_inicio' => $fecha_inicio->translatedFormat('F'),
            'anio_inicio' => $fecha_inicio->format('Y'),
            'hora_fin' => $fecha_fin ? $fecha_fin->format('H:i') : '',
            'dia_fin' => $fecha_fin ? $fecha_fin->format('d') : '',
            'mes_fin' => $fecha_fin ? $fecha_fin->translatedFormat('F') : '',
            'anio_fin' => $fecha_fin ? $fecha_fin->format('Y') : '',
            'dias' => $diffDays,
            'horas' => $diffHrs,
            'minutos' => $diffMins,
            'fecha_firma1' => $fecha_creacion->format('d/m/Y'),
            'fecha_firma2' => $fecha_firma2,
            'fecha_firma3' => $fecha_firma3,
            'fecha_firma4' => '',
            'firma_path' => $firma_path,
            'firma_jefe_path' => $firma_jefe_path,
            'firma_rrhh' => $firma_rrhh,
            'estado_solicitud' => $solicitud->estado,
        ];

        $folder = "formularios/permisos";
        $fileName = 'solicitud_' . $solicitud->id_solicitud . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('pdf.solicitud', $datos_pdf)->render();
        $pdf = Pdf::loadHTML($html)->output();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put("{$folder}/{$fileName}", $pdf);

        chmod(storage_path("app/public/{$folder}/{$fileName}"), 0755);

        Documentos::updateOrCreate(
            ['id_solicitud' => $solicitud->id_solicitud],
            [
                'nombre_documento' => $fileName,
                'path' => "{$folder}/{$fileName}",
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'deleted' => false
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Documento generado correctamente',
            'pdf_file_path' => "/storage/{$folder}/{$fileName}",
            'estado' => $solicitud->estado,
            'firmas_incluidas' => [
                'solicitante' => $firma_path ? true : false,
                'jefe' => $firma_jefe_path ? true : false,
                'rrhh' => $firma_rrhh ? true : false,
            ]
        ], 200);
    }

    private function generarDocumentoReincorporacion($solicitud)
    {
        $user = $solicitud->usuario;
        $fecha_inicio = Carbon::parse($solicitud->fecha_inicio);
        $fecha_creacion = Carbon::now();

        // Firma del solicitante (siempre presente)
        $firma_path = null;
        if ($user->firma_path && file_exists(public_path('storage/' . $user->firma_path))) {
            $firma_path = $user->firma_path;
        }

        // Inicializar variables de firmas
        $firma_jefe_path = null;
        $fecha_firma2 = null;
        $firma_rrhh = null;
        $fecha_firma3 = null;

        $aprobacion = Aprobaciones::where('id_solicitud', $solicitud->id_solicitud)->first();

        // Aplicar la misma lógica de firmas según el estado
        if ($aprobacion) {
            switch ($solicitud->estado) {
                case 'revision':
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }
                    break;

                case 'aprobada':
                    // Firma del jefe
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }

                    // ===== NUEVA IMPLEMENTACIÓN CON CACHE =====
                    // Firma de RRHH
                    if ($aprobacion->aprobado_rrhh) {
                        if ($aprobacion->id_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarFirmaRRHHEspecifico($aprobacion->id_rrhh);
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }

                        if (!$firma_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarPrimeraFirmaRRHH();
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }
                    }
                    break;
            }
        }

        $datos_pdf = [
            'id' => $solicitud->id_solicitud,
            'nombre' => $user->nombre,
            'cedula' => $user->cedula,
            'motivo' => $solicitud->motivo ?? 'N/A',
            'puesto' => $user->cargo ?? 'N/A',
            'posicion' => $user->posicion ?? 'N/A',
            'unidad' => 'Unidad de Análisis Financiero',
            'observacion' => $solicitud->observacion ?? '',
            'dia' => $fecha_inicio->format('d'),
            'mes' => $fecha_inicio->translatedFormat('F'),
            'anio' => $fecha_inicio->format('Y'),
            'fecha_firma1' => $fecha_creacion->format('d/m/Y'),
            'fecha_firma2' => $fecha_firma2,
            'fecha_firma3' => $fecha_firma3,
            'firma_path' => $firma_path,
            'firma_jefe_path' => $firma_jefe_path,
            'firma_rrhh' => $firma_rrhh,
            'estado_solicitud' => $solicitud->estado,
        ];

        $folder = "formularios/reincorporaciones";
        $fileName = 'reincorporacion_' . $solicitud->id_solicitud . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('pdf.reincorporacion', $datos_pdf)->render();
        $pdf = Pdf::loadHTML($html)->output();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put("{$folder}/{$fileName}", $pdf);

        chmod(storage_path("app/public/{$folder}/{$fileName}"), 0755);

        Documentos::updateOrCreate(
            ['id_solicitud' => $solicitud->id_solicitud],
            [
                'nombre_documento' => $fileName,
                'path' => "{$folder}/{$fileName}",
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'deleted' => false
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Documento de reincorporación generado correctamente',
            'pdf_file_path' => "/storage/{$folder}/{$fileName}",
            'estado' => $solicitud->estado,
            'firmas_incluidas' => [
                'solicitante' => $firma_path ? true : false,
                'jefe' => $firma_jefe_path ? true : false,
                'rrhh' => $firma_rrhh ? true : false,
            ]
        ], 200);
    }

    private function generarDocumentoTiempoCompensatorio($solicitud)
    {
        $user = $solicitud->usuario;
        $fecha_inicio = Carbon::parse($solicitud->fecha_inicio);
        $fecha_fin = $solicitud->fecha_fin ? Carbon::parse($solicitud->fecha_fin) : null;
        $fecha_creacion = Carbon::now();

        // Extraer cantidad de tiempo del campo tiempo_utilizado
        // Formato esperado: "X horas" o "X días"
        $cantidad_tiempo = '';
        if ($solicitud->tiempo_utilizado) {
            preg_match('/(\d+)\s*(horas?|días?)/', $solicitud->tiempo_utilizado, $matches);
            if (!empty($matches[1])) {
                $cantidad_tiempo = $matches[1];
            }
        }

        // Firma del solicitante
        $firma_path = null;
        if ($user->firma_path && file_exists(public_path('storage/' . $user->firma_path))) {
            $firma_path = $user->firma_path;
        }

        // Inicializar variables de firmas
        $firma_jefe_path = null;
        $fecha_firma2 = null;
        $firma_rrhh = null;
        $fecha_firma3 = null;

        $aprobacion = Aprobaciones::where('id_solicitud', $solicitud->id_solicitud)->first();

        // Aplicar la misma lógica de firmas según el estado
        if ($aprobacion) {
            switch ($solicitud->estado) {
                case 'revision':
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }
                    break;

                case 'aprobada':
                    // Firma del jefe
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }

                    // ===== NUEVA IMPLEMENTACIÓN CON CACHE =====
                    // Firma de RRHH
                    if ($aprobacion->aprobado_rrhh) {
                        if ($aprobacion->id_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarFirmaRRHHEspecifico($aprobacion->id_rrhh);
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }

                        if (!$firma_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarPrimeraFirmaRRHH();
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }
                    }
                    break;
            }
        }

        $datos_pdf = [
            'id' => $solicitud->id_solicitud,
            'nombre' => $user->nombre,
            'cedula' => $user->cedula,
            'puesto' => $user->cargo ?? 'N/A',
            'posicion' => $user->posicion ?? 'N/A',
            'unidad' => 'Unidad de Análisis Financiero',
            'cantidad_tiempo' => $cantidad_tiempo,
            'observacion' => $solicitud->observacion ?? '',
            'dia_inicio' => $fecha_inicio->format('d'),
            'mes_inicio' => $fecha_inicio->translatedFormat('F'),
            'anio_inicio' => $fecha_inicio->format('Y'),
            'hora_inicio' => $fecha_inicio->format('H:i'),
            'dia_fin' => $fecha_fin ? $fecha_fin->format('d') : '',
            'mes_fin' => $fecha_fin ? $fecha_fin->translatedFormat('F') : '',
            'anio_fin' => $fecha_fin ? $fecha_fin->format('Y') : '',
            'hora_fin' => $fecha_fin ? $fecha_fin->format('H:i') : '',
            'fecha_firma1' => $fecha_creacion->format('d/m/Y'),
            'fecha_firma2' => $fecha_firma2,
            'fecha_firma3' => $fecha_firma3,
            'firma_path' => $firma_path,
            'firma_jefe_path' => $firma_jefe_path,
            'firma_rrhh' => $firma_rrhh,
            'estado_solicitud' => $solicitud->estado,
        ];

        $folder = "formularios/tiempo_compensatorio";
        $fileName = 'compensatorio_' . $solicitud->id_solicitud . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('pdf.compensatorio', $datos_pdf)->render();
        $pdf = Pdf::loadHTML($html)->output();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put("{$folder}/{$fileName}", $pdf);

        chmod(storage_path("app/public/{$folder}/{$fileName}"), 0755);

        Documentos::updateOrCreate(
            ['id_solicitud' => $solicitud->id_solicitud],
            [
                'nombre_documento' => $fileName,
                'path' => "{$folder}/{$fileName}",
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'deleted' => false
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Documento de tiempo compensatorio generado correctamente',
            'pdf_file_path' => "/storage/{$folder}/{$fileName}",
            'estado' => $solicitud->estado,
            'firmas_incluidas' => [
                'solicitante' => $firma_path ? true : false,
                'jefe' => $firma_jefe_path ? true : false,
                'rrhh' => $firma_rrhh ? true : false,
            ]
        ], 200);
    }

    private function generarDocumentoVacaciones($solicitud)
    {
        $user = $solicitud->usuario;
        $fecha_inicio = Carbon::parse($solicitud->fecha_inicio);
        $fecha_fin = $solicitud->fecha_fin ? Carbon::parse($solicitud->fecha_fin) : null;
        $fecha_creacion = Carbon::now();

        // Calcular fecha de reincorporación (día siguiente al fin de vacaciones)
        $fecha_reincorporacion = $fecha_fin ? $fecha_fin->copy()->addDay() : null;

        // Log para debugging
        Log::info('Datos de vacaciones en solicitud:', [
            'id_solicitud' => $solicitud->id_solicitud,
            'observacion' => $solicitud->observacion,
            'tiempo_utilizado' => $solicitud->tiempo_utilizado,
            'todos_los_campos' => $solicitud->toArray()
        ]);

        // Firma del solicitante
        $firma_path = null;
        if ($user->firma_path && file_exists(public_path('storage/' . $user->firma_path))) {
            $firma_path = $user->firma_path;
        }

        // Inicializar variables de firmas
        $firma_jefe_path = null;
        $firma_rrhh = null;

        $aprobacion = Aprobaciones::where('id_solicitud', $solicitud->id_solicitud)->first();

        // Aplicar la misma lógica de firmas según el estado
        if ($aprobacion) {
            switch ($solicitud->estado) {
                case 'revision':
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                        }
                    }
                    break;

                case 'aprobada':
                    // Firma del jefe
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                        }
                    }

                    // ===== NUEVA IMPLEMENTACIÓN CON CACHE =====
                    // Firma de RRHH
                    if ($aprobacion->aprobado_rrhh) {
                        if ($aprobacion->id_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarFirmaRRHHEspecifico($aprobacion->id_rrhh);
                        }

                        if (!$firma_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarPrimeraFirmaRRHH();
                        }
                    }
                    break;
            }
        }

        // Obtener salario y días de vacaciones
        $salario_formateado = 'N/A';
        $dias_vacaciones = 0;
        $observacion_texto = '';

        // Para el salario, intentar decodificar la observación como JSON
        if ($solicitud->observacion) {
            $datosVacaciones = json_decode($solicitud->observacion, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($datosVacaciones['salario'])) {
                // Es un JSON válido con datos de vacaciones
                $salario_formateado = number_format($datosVacaciones['salario'], 2);
                $observacion_texto = $datosVacaciones['observacion_texto'] ?? '';

                Log::info('Salario recuperado del JSON:', [
                    'salario' => $salario_formateado,
                    'observacion' => $observacion_texto
                ]);
            } else {
                // No es JSON, es solo la observación normal
                $observacion_texto = $solicitud->observacion;
            }
        }

        // Para los días, extraer del campo tiempo_utilizado
        if ($solicitud->tiempo_utilizado) {
            // Formato esperado: "días: X"
            preg_match('/días:\s*(\d+)/', $solicitud->tiempo_utilizado, $matches);
            if (!empty($matches[1])) {
                $dias_vacaciones = intval($matches[1]);
                Log::info('Días extraídos de tiempo_utilizado: ' . $dias_vacaciones);
            }
        }

        // Si no se encontraron días en tiempo_utilizado, calcular desde las fechas
        if ($dias_vacaciones == 0 && $fecha_fin) {
            $dias_vacaciones = $fecha_inicio->diffInDays($fecha_fin) + 1; // +1 para incluir el día de inicio
            Log::info('Días calculados desde fechas: ' . $dias_vacaciones);
        }

        $datos_pdf = [
            'id' => $solicitud->id_solicitud,
            'nombre' => $user->nombre,
            'cedula' => $user->cedula,
            'posicion' => $user->posicion ?? 'N/A',
            'salario' => $salario_formateado,
            'dias' => $dias_vacaciones,
            // Fecha de la carta (fecha actual)
            'dia_carta' => $fecha_creacion->format('d'),
            'mes_carta' => $fecha_creacion->translatedFormat('F'),
            'anio_carta' => $fecha_creacion->format('Y'),
            // Fecha de inicio de vacaciones
            'dia_inicio' => $fecha_inicio->format('d'),
            'mes_inicio' => $fecha_inicio->translatedFormat('F'),
            'anio_inicio' => $fecha_inicio->format('Y'),
            // Fecha de reincorporación
            'dia_fin' => $fecha_reincorporacion ? $fecha_reincorporacion->format('d') : '',
            'mes_fin' => $fecha_reincorporacion ? $fecha_reincorporacion->translatedFormat('F') : '',
            'anio_fin' => $fecha_reincorporacion ? $fecha_reincorporacion->format('Y') : '',
            // Datos del resuelto (estos vendrían de la base de datos o se dejarían vacíos)
            'resuelto_no' => '',
            'resuelto_fecha' => '',
            // Firmas
            'firma_path' => $firma_path,
            'firma_jefe_path' => $firma_jefe_path,
            'firma_rrhh' => $firma_rrhh,
            'estado_solicitud' => $solicitud->estado,
        ];

        // Log los datos que se enviarán al PDF
        Log::info('Datos enviados al PDF de vacaciones:', [
            'salario' => $datos_pdf['salario'],
            'dias' => $datos_pdf['dias']
        ]);

        $folder = "formularios/vacaciones";
        $fileName = 'vacaciones_' . $solicitud->id_solicitud . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('pdf.vacaciones', $datos_pdf)->render();
        $pdf = Pdf::loadHTML($html)->output();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put("{$folder}/{$fileName}", $pdf);

        chmod(storage_path("app/public/{$folder}/{$fileName}"), 0755);

        Documentos::updateOrCreate(
            ['id_solicitud' => $solicitud->id_solicitud],
            [
                'nombre_documento' => $fileName,
                'path' => "{$folder}/{$fileName}",
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'deleted' => false
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Documento de vacaciones generado correctamente',
            'pdf_file_path' => "/storage/{$folder}/{$fileName}",
            'estado' => $solicitud->estado,
            'firmas_incluidas' => [
                'solicitante' => $firma_path ? true : false,
                'jefe' => $firma_jefe_path ? true : false,
                'rrhh' => $firma_rrhh ? true : false,
            ],
            'datos_vacaciones' => [
                'salario' => $salario_formateado,
                'dias' => $dias_vacaciones
            ]
        ], 200);
    }

    private function generarDocumentoHorasExtras($solicitud)
    {
        $user = $solicitud->usuario;
        $fecha_inicio = Carbon::parse($solicitud->fecha_inicio);
        $fecha_fin = $solicitud->fecha_fin ? Carbon::parse($solicitud->fecha_fin) : null;
        $fecha_creacion = Carbon::now();

        // Ya no necesitamos dividir en líneas, enviamos el texto completo
        $trabajo_realizado = $solicitud->trabajo_realizado ?? '';
        $justificacion = $solicitud->justificacion ?? '';
        $observacion = $solicitud->observacion ?? '';

        // Firma del solicitante
        $firma_path = null;
        if ($user->firma_path && file_exists(public_path('storage/' . $user->firma_path))) {
            $firma_path = $user->firma_path;
        }

        // Inicializar variables de firmas
        $firma_jefe_path = null;
        $fecha_firma2 = null;
        $firma_rrhh = null;
        $fecha_firma3 = null;

        $aprobacion = Aprobaciones::where('id_solicitud', $solicitud->id_solicitud)->first();

        // Aplicar la misma lógica de firmas según el estado
        if ($aprobacion) {
            switch ($solicitud->estado) {
                case 'revision':
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }
                    break;

                case 'aprobada':
                    // Firma del jefe
                    if ($aprobacion->aprobado_jefe && $aprobacion->id_jefe_departamento) {
                        $jefe = User::find($aprobacion->id_jefe_departamento);
                        if ($jefe && $jefe->firma_path && file_exists(public_path('storage/' . $jefe->firma_path))) {
                            $firma_jefe_path = $jefe->firma_path;
                            $fecha_firma2 = $fecha_creacion->format('d/m/Y');
                        }
                    }

                    // ===== NUEVA IMPLEMENTACIÓN CON CACHE =====
                    // Firma de RRHH
                    if ($aprobacion->aprobado_rrhh) {
                        if ($aprobacion->id_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarFirmaRRHHEspecifico($aprobacion->id_rrhh);
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }

                        if (!$firma_rrhh) {
                            $firma_rrhh = RRHHCacheService::buscarPrimeraFirmaRRHH();
                            if ($firma_rrhh) {
                                $fecha_firma3 = $fecha_creacion->format('d/m/Y');
                            }
                        }
                    }
                    break;
            }
        }

        $datos_pdf = [
            'id' => $solicitud->id_solicitud,
            'nombre' => $user->nombre,
            'cedula' => $user->cedula,
            'puesto' => $user->cargo ?? 'N/A',
            'posicion' => $user->posicion ?? 'N/A',
            'unidad' => 'Unidad de Análisis Financiero',
            'fecha_trabajo' => $fecha_inicio->format('d/m/Y'),
            'hora_inicio' => $fecha_inicio->format('H:i'),
            'hora_fin' => $fecha_fin ? $fecha_fin->format('H:i') : '',
            'tiempo_laborado' => $solicitud->tiempo_laborado ?? '',

            // Enviar texto completo sin dividir en líneas
            'trabajo_realizado' => $trabajo_realizado,
            'justificacion' => $justificacion,
            'observacion' => $observacion,

            // Firmas
            'fecha_firma1' => $fecha_creacion->format('d/m/Y'),
            'fecha_firma2' => $fecha_firma2,
            'fecha_firma3' => $fecha_firma3,
            'firma_path' => $firma_path,
            'firma_jefe_path' => $firma_jefe_path,
            'firma_rrhh' => $firma_rrhh,
            'estado_solicitud' => $solicitud->estado,
        ];

        $folder = "formularios/horas_extraordinarias";
        $fileName = 'horas_extras_' . $solicitud->id_solicitud . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('pdf.horasextraordinarias', $datos_pdf)->render();
        $pdf = Pdf::loadHTML($html)->output();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put("{$folder}/{$fileName}", $pdf);

        chmod(storage_path("app/public/{$folder}/{$fileName}"), 0755);

        Documentos::updateOrCreate(
            ['id_solicitud' => $solicitud->id_solicitud],
            [
                'nombre_documento' => $fileName,
                'path' => "{$folder}/{$fileName}",
                'fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                'deleted' => false
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Documento de horas extraordinarias generado correctamente',
            'pdf_file_path' => "/storage/{$folder}/{$fileName}",
            'estado' => $solicitud->estado,
            'firmas_incluidas' => [
                'solicitante' => $firma_path ? true : false,
                'jefe' => $firma_jefe_path ? true : false,
                'rrhh' => $firma_rrhh ? true : false,
            ]
        ], 200);
    }

    private function dividirTextoEnLineas($texto, $caracteresPorLinea, $numeroLineas)
    {
        // Si el texto está vacío, devolver array con líneas vacías
        if (empty(trim($texto))) {
            return array_fill(0, $numeroLineas, '');
        }

        // Limpiar el texto
        $texto = trim($texto);

        // Si el texto es corto y cabe en las líneas disponibles, simplemente devolverlo
        $longitudTotal = strlen($texto);
        $capacidadTotal = $caracteresPorLinea * $numeroLineas;

        if ($longitudTotal <= $capacidadTotal) {
            // Dividir el texto equitativamente entre las líneas disponibles
            $lineas = [];
            $palabras = explode(' ', $texto);
            $lineaActual = '';

            foreach ($palabras as $palabra) {
                $pruebaLinea = $lineaActual . ($lineaActual ? ' ' : '') . $palabra;

                if (strlen($pruebaLinea) <= $caracteresPorLinea) {
                    $lineaActual = $pruebaLinea;
                } else {
                    if ($lineaActual) {
                        $lineas[] = $lineaActual;
                        if (count($lineas) >= $numeroLineas) {
                            // Si ya llenamos todas las líneas, concatenar el resto
                            $palabrasRestantes = array_slice($palabras, array_search($palabra, $palabras));
                            $textoRestante = implode(' ', $palabrasRestantes);

                            // Agregar el texto restante a la última línea si cabe
                            if (count($lineas) > 0) {
                                $ultimaLinea = $lineas[count($lineas) - 1];
                                if (strlen($ultimaLinea . ' ' . $textoRestante) <= $caracteresPorLinea * 1.5) {
                                    $lineas[count($lineas) - 1] = $ultimaLinea . ' ' . $textoRestante;
                                }
                            }
                            break;
                        }
                    }
                    $lineaActual = $palabra;
                }
            }

            // Agregar la última línea si existe
            if ($lineaActual && count($lineas) < $numeroLineas) {
                $lineas[] = $lineaActual;
            }

            // Rellenar con líneas vacías si es necesario
            while (count($lineas) < $numeroLineas) {
                $lineas[] = '';
            }

            return array_slice($lineas, 0, $numeroLineas);
        } else {
            // Si el texto es muy largo, devolver todo concatenado en las líneas disponibles
            // Esto será manejado por la plantilla Blade con ajuste de fuente
            $lineas = [];
            $lineas[0] = $texto; // Todo el texto en la primera línea

            // Rellenar el resto con vacío
            for ($i = 1; $i < $numeroLineas; $i++) {
                $lineas[] = '';
            }

            return $lineas;
        }
    }

    public function obtenerRutaPdf(Request $request)
    {
        try {
            $request->validate([
                'id_solicitud' => 'required|integer',
            ]);

            $documento = Documentos::where('id_solicitud', $request->id_solicitud)->first();

            if (!$documento) {
                return response()->json([
                    'status' => false,
                    'message' => 'Documento no encontrado',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'PDF obtenido correctamente',
                'pdf_file_path' => "/storage/{$documento->path}",
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener la ruta del documento: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la ruta del documento',
            ], 500);
        }
    }
}
