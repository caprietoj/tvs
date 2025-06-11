<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SalidaPedagogica;

class SalidaPedagogicaNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $salida;
    public $tipoDestinatario;

    public function __construct(SalidaPedagogica $salida, $tipoDestinatario)
    {
        $this->salida = $salida;
        $this->tipoDestinatario = $tipoDestinatario;
    }

    public function build()
    {
        return $this->subject('Nueva Salida Pedagógica - ' . $this->salida->grados)
                    ->view('emails.salida-pedagogica');
    }
}
