<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Proveedor;

class ProveedorCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $proveedor;

    /**
     * Create a new message instance.
     */
    public function __construct(Proveedor $proveedor)
    {
        $this->proveedor = $proveedor;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Nuevo proveedor registrado: {$this->proveedor->nombre}")
                    ->view('emails.proveedor-created');
    }
}
