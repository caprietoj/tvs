<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\MaintenanceRequest;

class MaintenanceRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $maintenanceRequest;

    public function __construct(MaintenanceRequest $maintenanceRequest)
    {
        $this->maintenanceRequest = $maintenanceRequest;
    }

    public function build()
    {
        return $this->markdown('emails.maintenance-request-created')
                    ->subject('Nueva Solicitud de Mantenimiento');
    }
}
