<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\IngresoEstudiante;

class AtencionEnfermeriaNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ingreso;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(IngresoEstudiante $ingreso)
    {
        $this->ingreso = $ingreso;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reporte de Atención en Enfermería - ' . $this->ingreso->estudiante)
                    ->view('emails.atencion-enfermeria');
    }
}
