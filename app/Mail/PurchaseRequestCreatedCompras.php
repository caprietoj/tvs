<?php

namespace App\Mail;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseRequestCreatedCompras extends Mailable
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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Determinar el tipo de solicitud para el asunto
        $requestType = '';
        if ($this->purchaseRequest->type == 'purchase') {
            $requestType = 'Compra';
        } elseif ($this->purchaseRequest->isCopiesRequest()) {
            $requestType = 'Fotocopias';
        } else {
            $requestType = 'Materiales';
        }
        
        return new Envelope(
            subject: 'Nueva Solicitud de ' . $requestType . ' - Acción Requerida #' . $this->purchaseRequest->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-request-created-compras',
            with: [
                'purchaseRequest' => $this->purchaseRequest,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
