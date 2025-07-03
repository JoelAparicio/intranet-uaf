<?php

namespace App\Http\Controllers;

use App\Models\Aprobaciones;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use App\Http\Controllers\DocumentController;
use App\Jobs\GenerarPdfSolicitudJob;
use Illuminate\Support\Facades\Log;

class AprobacionesController extends Controller
{
    public function listar_aprobaciones(Request $request)
    {
        $usuario = $request->user();
        $rolesUsuario = $usuario->roles->pluck('name')->toArray();
        $usuarioId = $usuario->id;

        $RolesJefes = [
            'Jefe de Tecnología' => ['Tecnología'],
            'Jefe de Relaciones Públicas' => ['Relaciones Públicas'],
            'Jefe de Administración' => ['Administración'],
            'Jefe de Análisis Estratégico' => ['Analista Estratégico'],
            'Jefe de Análisis Operativo' => ['Analista Operativo'],
            'Jefe de Asesoría Legal' => ['Legal'],
            'Jefe de Contact Center' => ['Call Center'],
            'Jefe de Cooperación Nacional e Internacional' => ['Cooperación']
        ];

        $RolesJefeRRHH = ['Jefe de Recursos Humanos'];
        $RolesRRHH = ['Recursos Humanos'];
        $RolesDirector = ['Director', 'Subdirector'];

        $query = Aprobaciones::query();

        foreach ($RolesJefes as $jefeRol => $rolesSubalternos) {
            if (in_array($jefeRol, $rolesUsuario)) {
                $query->where('aprobado_jefe', false)
                    ->where('deleted', 0)
                    ->whereHas('solicitud.usuario.roles', function ($query) use ($rolesSubalternos) {
                        $query->whereIn('name', $rolesSubalternos);
                    })
                    ->whereHas('solicitud.usuario', function ($query) use ($usuarioId) {
                        $query->where('id', '!=', $usuarioId);
                    });
                break;
            }
        }

        if (array_intersect($rolesUsuario, $RolesJefeRRHH)) {
            $query->orWhere(function ($query) {
                $query->where('aprobado_jefe', true)
                    ->where('aprobado_rrhh', false)
                    ->where('deleted', 0);
            });
        }

        if (array_intersect($rolesUsuario, $RolesRRHH)) {
            $query->orWhere(function ($query) use ($usuarioId) {
                $query->where('aprobado_jefe', true)
                    ->where('aprobado_rrhh', false)
                    ->where('deleted', 0)
                    ->whereHas('solicitud.usuario', function ($q) use ($usuarioId) {
                        $q->where('id', '!=', $usuarioId);
                    });
            });
        }

        if (array_intersect($rolesUsuario, $RolesDirector)) {
            $query->orWhere(function ($query) {
                $query->where('aprobado_jefe', false)
                    ->where('aprobado_rrhh', false)
                    ->where('deleted', 0)
                    ->whereHas('solicitud.usuario.roles', function ($q) {
                        $q->whereIn('name', [
                            'Jefe de Tecnología',
                            'Jefe de Relaciones Públicas',
                            'Jefe de Administración',
                            'Jefe de Análisis Estratégico',
                            'Jefe de Análisis Operativo',
                            'Jefe de Asesoría Legal',
                            'Jefe de Contact Center',
                            'Jefe de Cooperación Nacional e Internacional',
                            'Jefe de Recursos Humanos'
                        ]);
                    });
            });
        }

        if ($query->getQuery()->wheres === []) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver estas aprobaciones'
            ], 403);
        }

        $aprobaciones = $query->with(['jefeDepartamento', 'solicitud.usuario', 'solicitud.tipo_solicitud'])
            ->get()
            ->map(function ($aprobacion) {
                return [
                    'id_solicitud' => $aprobacion->id_solicitud,
                    'colaborador' => $aprobacion->solicitud->usuario->nombre,
                    'tipo_solicitud' => $aprobacion->solicitud->tipo_solicitud->tipo_solicitud,
                    'fecha_creacion' => $aprobacion->solicitud->fecha_creacion,
                ];
            });

        return response()->json(['success' => true, 'data' => $aprobaciones], 200);
    }

    public function listar_solicitudes_rechazadas(Request $request)
    {
        $usuario = $request->user();
        $rolesUsuario = $usuario->roles->pluck('name')->toArray();
        $usuarioId = $usuario->id;

        $query = Aprobaciones::onlyTrashed();

        if (in_array('Jefe de Tecnología', $rolesUsuario)) {
            $query->whereHas('solicitud.usuario.roles', function ($query) {
                $query->where('name', 'Tecnología');
            })->where('id_usuario_solicitud', '!=', $usuarioId);
        } elseif (in_array('Jefe de Recursos Humanos', $rolesUsuario) || in_array('Recursos Humanos', $rolesUsuario)) {
            $query->where('id_rrhh', $usuarioId)->where('aprobado_jefe', true);
        } elseif (in_array('Director', $rolesUsuario) || in_array('Subdirector', $rolesUsuario)) {
            $query->where('id_jefe_departamento', $usuarioId);
        }

        $aprobaciones = $query->with(['jefeDepartamento', 'solicitud.usuario', 'solicitud.tipo_solicitud'])
            ->get()
            ->map(function ($aprobacion) {
                return [
                    'id_solicitud' => $aprobacion->id_solicitud,
                    'colaborador' => $aprobacion->solicitud->usuario->nombre,
                    'tipo_solicitud' => $aprobacion->solicitud->tipo_solicitud->tipo_solicitud,
                    'fecha_creacion' => $aprobacion->solicitud->fecha_creacion,
                ];
            });

        return response()->json(['success' => true, 'data' => $aprobaciones], 200);
    }

    public function listar_solicitudes_aceptadas(Request $request)
    {
        $usuario = $request->user();
        $rolesUsuario = $usuario->roles->pluck('name')->toArray();
        $usuarioId = $usuario->id;

        $query = Aprobaciones::where('deleted', 0);

        if (in_array('Jefe de Tecnología', $rolesUsuario)) {
            $query->where('aprobado_jefe', true)
                ->whereHas('solicitud.usuario.roles', function ($query) {
                    $query->where('name', 'Tecnología');
                })
                ->where('id_usuario_solicitud', '!=', $usuarioId);
        } elseif (in_array('Jefe de Recursos Humanos', $rolesUsuario) || in_array('Recursos Humanos', $rolesUsuario)) {
            $query->where('aprobado_rrhh', true)
                ->where('id_rrhh', $usuarioId);
        } elseif (in_array('Director', $rolesUsuario) || in_array('Subdirector', $rolesUsuario)) {
            $query->where('id_jefe_departamento', $usuarioId);
        }

        $aprobaciones = $query->with(['jefeDepartamento', 'solicitud.usuario', 'solicitud.tipo_solicitud'])
            ->get()
            ->map(function ($aprobacion) {
                return [
                    'id_solicitud' => $aprobacion->id_solicitud,
                    'colaborador' => $aprobacion->solicitud->usuario->nombre,
                    'tipo_solicitud' => $aprobacion->solicitud->tipo_solicitud->tipo_solicitud,
                    'fecha_creacion' => $aprobacion->solicitud->fecha_creacion,
                ];
            });

        return response()->json(['success' => true, 'data' => $aprobaciones], 200);
    }

    public function aprobar_solicitud($id, Request $request)
    {
        $usuario = $request->user();
        $aprobacion = Aprobaciones::where('id_solicitud', $id)->first();

        if (!$aprobacion) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        // Obtener la solicitud relacionada
        $solicitud = Solicitud::where('id_solicitud', $id)->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada en la tabla de solicitudes'
            ], 404);
        }

        $generarPDF = false;
        $nuevoEstado = null;

        // Lógica de aprobación y actualización de estado
        if (!$aprobacion->aprobado_jefe) {
            $aprobacion->aprobado_jefe = true;
            $aprobacion->id_jefe_departamento = $usuario->id;
            $nuevoEstado = 'revision'; // Aprobado por jefe, pasa a revisión de RRHH
            $generarPDF = true;
        } elseif (!$aprobacion->aprobado_rrhh) {
            $aprobacion->aprobado_rrhh = true;
            $aprobacion->id_rrhh = $usuario->id;
            $nuevoEstado = 'aprobada'; // Aprobado por RRHH, completamente aprobada
            $generarPDF = true;
        }

        // Guardar cambios en la tabla aprobaciones
        $aprobacion->save();

        // Actualizar el estado en la tabla solicitudes
        if ($nuevoEstado) {
            $solicitud->estado = $nuevoEstado;
            $solicitud->save();
        }

        // ===== NUEVA IMPLEMENTACIÓN CON JOBS =====
        // Encolar generación de PDF en lugar de hacerlo síncronamente
        if ($generarPDF) {
            try {
                // Dispatch del job con alta prioridad para aprobaciones
                GenerarPdfSolicitudJob::dispatch($solicitud->id_solicitud, 'aprobacion');

                Log::info("PDF encolado para solicitud {$solicitud->id_solicitud}", [
                    'nuevo_estado' => $nuevoEstado,
                    'aprobado_por' => $usuario->id,
                    'tipo_aprobacion' => $aprobacion->aprobado_jefe && !$aprobacion->aprobado_rrhh ? 'rrhh' : 'jefe'
                ]);

            } catch (\Exception $e) {
                // Si falla el encolado, log el error pero no fallar la aprobación
                Log::error("Error al encolar PDF para solicitud {$solicitud->id_solicitud}", [
                    'error' => $e->getMessage(),
                    'nuevo_estado' => $nuevoEstado
                ]);

                // Opcional: Intentar generación síncrona como fallback
                // app(DocumentController::class)->crear_permiso(new Request(['id_solicitud' => $solicitud->id_solicitud]));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada correctamente. El documento se está generando en segundo plano.',
            'estado' => $nuevoEstado,
            'pdf_en_proceso' => $generarPDF
        ], 200);
    }

    public function rechazar_solicitud($id, Request $request)
    {
        $request->validate([
            'comentarios' => 'required|string|max:500'
        ]);

        $usuario = $request->user();
        $aprobacion = Aprobaciones::where('id_solicitud', $id)->first();

        if (!$aprobacion) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        // Obtener la solicitud relacionada
        $solicitud = Solicitud::where('id_solicitud', $id)->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada en la tabla de solicitudes'
            ], 404);
        }

        // Actualizar la aprobación
        $aprobacion->deleted = 1;
        $aprobacion->comentario_rechazo = $request->comentarios;

        if (!$aprobacion->aprobado_jefe) {
            $aprobacion->id_jefe_departamento = $usuario->id;
        } elseif (!$aprobacion->aprobado_rrhh) {
            $aprobacion->id_rrhh = $usuario->id;
        }

        $aprobacion->save();
        $aprobacion->delete(); // Soft delete

        // Actualizar el estado en la tabla solicitudes
        $solicitud->estado = 'rechazada';
        $solicitud->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada correctamente',
            'estado' => 'rechazada'
        ], 200);
    }
}
