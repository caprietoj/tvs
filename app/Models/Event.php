<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use Notifiable;

    protected $guarded = [];

    protected $dates = [
        'request_date',
        'service_date',
        'maintenance_setup_date',
        'general_services_setup_date',
        'systems_setup_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'service_date' => 'datetime',
        'service_dates' => 'array', // Para múltiples fechas
        'locations' => 'array',     // Para múltiples lugares
        'maintenance_setup_date' => 'datetime',
        'general_services_setup_date' => 'datetime',
        'systems_setup_date' => 'datetime',
        'cafam_parking' => 'boolean',
        'metro_junior_required' => 'boolean',
        'aldimark_required' => 'boolean',
        'maintenance_required' => 'boolean',
        'general_services_required' => 'boolean',
        'systems_required' => 'boolean',
        'purchases_required' => 'boolean',
        'communications_required' => 'boolean',
        'metro_junior_confirmed' => 'boolean',
        'aldimark_confirmed' => 'boolean',
        'maintenance_confirmed' => 'boolean',
        'general_services_confirmed' => 'boolean',
        'systems_confirmed' => 'boolean',
        'purchases_confirmed' => 'boolean',
        'communications_confirmed' => 'boolean'
    ];

    public function getStatusColor()
    {
        $confirmedServices = 0;
        $totalServices = 0;
        
        $services = [
            'metro_junior',
            'aldimark',
            'maintenance',
            'general_services',
            'systems',
            'purchases',
            'communications'
        ];

        foreach ($services as $service) {
            $requiredField = $service . '_required';
            $confirmedField = $service . '_confirmed';
            
            if ($this->$requiredField) {
                $totalServices++;
                if ($this->$confirmedField) {
                    $confirmedServices++;
                }
            }
        }

        if ($totalServices === 0) return '#3788d8'; // Azul por defecto
        
        $percentage = ($confirmedServices / $totalServices) * 100;
        
        if ($percentage === 100) return '#28a745'; // Verde - Todo confirmado
        if ($percentage === 0) return '#dc3545';   // Rojo - Nada confirmado
        return '#ffc107';                          // Amarillo - Parcialmente confirmado
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Obtener los correos generales configurados
        $emails = config('notifications.events.emails', []);
        
        // Agregar correos específicos según los servicios requeridos
        $services = [
            'systems',
            'purchases',
            'maintenance',
            'general_services',
            'communications',
            'aldimark',
            'metro_junior'
        ];
        
        foreach ($services as $service) {
            $requiredField = $service . '_required';
            
            if ($this->$requiredField) {
                $serviceEmails = config("notifications.events.{$service}_emails", []);
                $emails = array_merge($emails, $serviceEmails);
            }
        }
        
        return array_unique($emails);
    }
    
    /**
     * Obtiene las novedades asociadas al evento.
     */
    public function novelties()
    {
        return $this->hasMany(EventNovelty::class);
    }
    
    /**
     * Obtiene el usuario que creó el evento.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
