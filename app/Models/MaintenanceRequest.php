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
            
            // Enviar a todos los correos configurados
            foreach ($notificationEmails as $email) {
                Mail::to(trim($email))->send(new MaintenanceRequestCreated($maintenanceRequest));
            }

            // Enviar al usuario que realizó la solicitud
            if ($maintenanceRequest->user && $maintenanceRequest->user->email) {
                Mail::to($maintenanceRequest->user->email)->send(new MaintenanceRequestCreated($maintenanceRequest));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
