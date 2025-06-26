<?php

namespace App\Services;

use App\Models\Proveedor;
use App\Mail\ProveedorCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProveedorNotificationService
{
    /**
     * Envía notificación por email cuando se crea un nuevo proveedor
     *
     * @param Proveedor $proveedor
     * @return void
     */
    public function sendProveedorCreatedNotification(Proveedor $proveedor)
    {
        try {
            $recipients = $this->getNotificationRecipients();
            
            if (empty($recipients)) {
                Log::warning('No se encontraron destinatarios para la notificación de proveedor creado');
                return;
            }

            // Usar el interceptor de correos para redirigir en modo de prueba
            $emailTestService = new \App\Services\EmailTestModeService();
            $interceptedRecipients = $emailTestService->interceptEmails($recipients, 'Contabilidad');

            // Enviar email a todos los destinatarios
            Mail::to($interceptedRecipients)->send(new ProveedorCreated($proveedor));
            
            Log::info('Notificación de proveedor creado enviada exitosamente', [
                'proveedor_id' => $proveedor->id,
                'proveedor_nombre' => $proveedor->nombre,
                'recipients_original' => $recipients,
                'recipients_intercepted' => $interceptedRecipients
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de proveedor creado', [
                'proveedor_id' => $proveedor->id,
                'proveedor_nombre' => $proveedor->nombre,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene la lista de destinatarios según el entorno
     *
     * @return array
     */
    private function getNotificationRecipients()
    {
        $environment = config('app.env');
        
        if ($environment === 'production') {
            return [
                'contabilidad@tvs.edu.co',
                'tesoreria@tvs.edu.co',
                'auxiliarcontable@tvs.edu.co'
            ];
        } else {
            // Entorno de desarrollo/testing
            return [
                'contabilidad@test.com',
                'tesoreria@test.com',
                'auxiliarcontable@test.com'
            ];
        }
    }
}
