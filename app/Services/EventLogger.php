<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class EventLogger
{
    protected $logger;
    
    public function __construct()
    {
        // Crear un canal de log específico para eventos
        $this->logger = new Logger('eventos');
        $this->logger->pushHandler(
            new StreamHandler(storage_path('logs/eventos.log'), Logger::DEBUG)
        );
    }
    
    /**
     * Registrar datos del evento antes de crearlo
     */
    public function logEventData($eventData)
    {
        $userId = auth()->id() ?? 'no-autenticado';
        $this->logger->info('Datos de evento recibidos', [
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $this->sanitizeData($eventData)
        ]);
    }
    
    /**
     * Registrar los datos después de la limpieza
     */
    public function logCleanedData($eventData)
    {
        $this->logger->info('Datos de evento limpiados', [
            'data' => $this->sanitizeData($eventData)
        ]);
    }
    
    /**
     * Registrar un evento creado exitosamente
     */
    public function logEventCreated($event)
    {
        $this->logger->info('Evento creado exitosamente', [
            'event_id' => $event->id,
            'consecutive' => $event->consecutive,
            'event_name' => $event->event_name
        ]);
    }
    
    /**
     * Registrar problemas o advertencias
     */
    public function logWarning($message, $data = [])
    {
        $this->logger->warning($message, $data);
    }
    
    /**
     * Registrar errores
     */
    public function logError($message, $exception = null, $data = [])
    {
        $errorData = $data;
        if ($exception) {
            $errorData['exception'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        $this->logger->error($message, $errorData);
    }
    
    /**
     * Sanitizar datos potencialmente sensibles para el registro
     */
    protected function sanitizeData($data)
    {
        // Copia para no modificar el original
        $sanitized = is_array($data) ? $data : [];
        
        // Quitar potenciales campos sensibles
        if (isset($sanitized['password'])) {
            $sanitized['password'] = '[REDACTADO]';
        }
        
        return $sanitized;
    }
}
