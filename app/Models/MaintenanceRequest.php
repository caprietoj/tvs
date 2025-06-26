<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaintenanceRequestCreated;
use App\Models\Configuration;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_type',
        'location',
        'description',
        'priority',
        'status',
        'technician_id',
        'completion_date'
    ];

    protected static function booted()
    {
        static::created(function($maintenanceRequest) {
            // Obtener correos configurados
            $config = Configuration::where('key', 'maintenance_emails')->first();
            $notificationEmails = $config ? explode(',', $config->value) : [];
            
            // Enviar a todos los correos configurados (con interceptor)
            foreach ($notificationEmails as $email) {
                $interceptedEmail = \App\Services\EmailTestModeService::interceptEmail(trim($email));
                Mail::to($interceptedEmail)->send(new MaintenanceRequestCreated($maintenanceRequest));
            }

            // Enviar al usuario que realizó la solicitud (con interceptor)
            if ($maintenanceRequest->user && $maintenanceRequest->user->email) {
                $userEmail = \App\Services\EmailTestModeService::interceptEmail($maintenanceRequest->user->email);
                Mail::to($userEmail)->send(new MaintenanceRequestCreated($maintenanceRequest));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
