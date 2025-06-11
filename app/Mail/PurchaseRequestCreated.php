<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseRequest;

class PurchaseRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Determinar el tipo de solicitud para el asunto del correo
        if ($this->purchaseRequest->type == 'purchase') {
            $requestType = 'compra';
        } elseif ($this->purchaseRequest->isCopiesRequest()) {
            $requestType = 'fotocopias';
        } else {
            $requestType = 'materiales';
        }

        return $this->subject("Nueva solicitud de {$requestType} #{$this->purchaseRequest->id}")
                    ->view('emails.purchase-request-created');
    }
}
