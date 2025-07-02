<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;

class GenerarPdfSolicitudJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutos máximo por PDF
    public $tries = 3; // Reintentar hasta 3 veces si falla
    public $backoff = [30, 60, 120]; // Esperar 30s, 60s, 120s entre reintentos

    protected $idSolicitud;
    protected $contextoGeneracion;

    /**
     * Create a new job instance.
     */
    public function __construct($idSolicitud, $contextoGeneracion = 'aprobacion')
    {
        $this->idSolicitud = $idSolicitud;
        $this->contextoGeneracion = $contextoGeneracion;

        // Configurar cola con prioridad según contexto
        if ($contextoGeneracion === 'aprobacion') {
            $this->onQueue('high'); // Alta prioridad para aprobaciones
        } else {
            $this->onQueue('default'); // Prioridad normal para creación inicial
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Iniciando generación de PDF para solicitud {$this->idSolicitud}", [
                'contexto' => $this->contextoGeneracion,
                'job_id' => $this->job->getJobId() ?? 'N/A'
            ]);

            // Crear el request para el DocumentController
            $request = new Request([
                'id_solicitud' => $this->idSolicitud
            ]);

            // Instanciar el controlador y generar el PDF
            $documentController = new DocumentController();
            $response = $documentController->crear_permiso($request);

            // Verificar si la generación fue exitosa
            $responseData = json_decode($response->getContent(), true);

            if (!$responseData || !$responseData['status']) {
                throw new \Exception('Error en la generación del PDF: ' . ($responseData['message'] ?? 'Respuesta inválida'));
            }

            Log::info("PDF generado exitosamente para solicitud {$this->idSolicitud}", [
                'contexto' => $this->contextoGeneracion,
                'pdf_path' => $responseData['pdf_file_path'] ?? 'N/A',
                'job_id' => $this->job->getJobId() ?? 'N/A'
            ]);

            // Opcional: Aquí podrías enviar notificaciones por email
            // $this->enviarNotificacionPdfGenerado($responseData);

        } catch (\Exception $e) {
            Log::error("Error al generar PDF para solicitud {$this->idSolicitud}", [
                'error' => $e->getMessage(),
                'contexto' => $this->contextoGeneracion,
                'intento' => $this->attempts(),
                'job_id' => $this->job->getJobId() ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            // Si es el último intento, registrar el fallo definitivo
            if ($this->attempts() >= $this->tries) {
                Log::critical("Fallo definitivo al generar PDF para solicitud {$this->idSolicitud} después de {$this->tries} intentos");

                // Opcional: Notificar a administradores sobre el fallo
                // $this->notificarFalloDefinitivo();
            }

            // Re-lanzar la excepción para que Laravel maneje el reintento
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("Job de PDF completamente fallido para solicitud {$this->idSolicitud}", [
            'error' => $exception->getMessage(),
            'contexto' => $this->contextoGeneracion,
            'job_id' => $this->job->getJobId() ?? 'N/A'
        ]);

        // Aquí podrías implementar notificaciones de fallo crítico
        // Por ejemplo, enviar email a administradores
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['pdf', 'solicitud:' . $this->idSolicitud, $this->contextoGeneracion];
    }

    // Método opcional para notificaciones futuras
    // private function enviarNotificacionPdfGenerado($responseData)
    // {
    //     // Implementar cuando tengas sistema de notificaciones
    // }
}
