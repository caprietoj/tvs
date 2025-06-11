<?php

namespace App\Traits;

use App\Models\Configuration;

trait NotificationHelpers
{
    protected function getNotificationEmails($type)
    {
        $config = Configuration::where('key', $type . '_emails')->first();
        return $config ? explode(',', $config->value) : [];
    }

    protected function sendNotificationToAll($mailable, $type, $user = null)
    {
        $emails = $this->getNotificationEmails($type);
        
        // Agregar el correo del usuario que realiza la solicitud
        if ($user && $user->email) {
            $emails[] = $user->email;
        }

        // Eliminar duplicados y valores vacíos
        $emails = array_unique(array_filter($emails));

        foreach ($emails as $email) {
            \Mail::to($email)->send($mailable);
        }
    }
}
