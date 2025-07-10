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

            // Enviar email a todos los destinatarios directamente
            // Ya no necesitamos EmailTestModeService porque usamos configuración dinámica
            Mail::to($recipients)->send(new ProveedorCreated($proveedor));
            
            Log::info('Notificación de proveedor creado enviada exitosamente', [
                'proveedor_id' => $proveedor->id,
                'proveedor_nombre' => $proveedor->nombre,
                'recipients' => $recipients,
                'config_source' => \App\Services\DynamicSectionEmailsService::getCurrentConfigSource()
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
     * Obtiene la lista de destinatarios para notificaciones de proveedores
     * Usa la configuración dinámica de secciones según el ambiente
     *
     * @return array
     */
    private function getNotificationRecipients()
    {
        // Usar la configuración dinámica de secciones
        // En desarrollo/pruebas usará section-mail-test.php
        // En producción usará section_emails.php
        $dynamicService = new \App\Services\DynamicSectionEmailsService();
        $sections = $dynamicService->getSections();
        
        $recipients = [];
        
        // Obtener correos de las secciones relacionadas con proveedores
        if (isset($sections['Contabilidad'])) {
            $recipients[] = $sections['Contabilidad'];
        }
        
        if (isset($sections['Tesorería'])) {
            $recipients[] = $sections['Tesorería'];
        }
        
        if (isset($sections['Asistente Contabilidad'])) {
            $recipients[] = $sections['Asistente Contabilidad'];
        }
        
        // Fallback a correos hardcodeados si no se encuentran en la configuración
        if (empty($recipients)) {
            Log::warning('No se encontraron correos en la configuración de secciones, usando fallback');
            
            // Determinar si estamos en modo de prueba
            if (\App\Services\DynamicSectionEmailsService::isTestingMode()) {
                return [
                    'contabilidad@test.com',
                    'tesoreria@test.com',
                    'asistentecontabilidad@test.com'
                ];
            } else {
                return [
                    'contabilidad@tvs.edu.co',
                    'tesoreria@tvs.edu.co',
                    'asistentecontabilidad@tvs.edu.co'
                ];
            }
        }
        
        // Aplanar el array en caso de que algunos elementos sean arrays
        $flattenedRecipients = [];
        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                $flattenedRecipients = array_merge($flattenedRecipients, $recipient);
            } else {
                $flattenedRecipients[] = $recipient;
            }
        }
        
        return array_unique($flattenedRecipients);
    }
}
