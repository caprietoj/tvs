<?php

namespace App\Mail;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceNoQuotationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseRequest;
    public $emailType;

    /**
     * Create a new message instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, $emailType = 'general')
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->emailType = $emailType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->emailType) {
            'user' => 'Confirmación: Se ha creado tu nueva solicitud de servicio #' . $this->purchaseRequest->id,
            'compras' => 'Se ha creado una nueva solicitud de servicio sin cotización #' . $this->purchaseRequest->id,
            'pre_approval' => 'Se ha creado una nueva solicitud de servicio - Pre-aprobación requerida #' . $this->purchaseRequest->id,
            default => 'Se ha creado una nueva solicitud de servicio #' . $this->purchaseRequest->id,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-no-quotation-created',
            with: [
                'purchaseRequest' => $this->purchaseRequest,
                'emailType' => $this->emailType,
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
        $attachments = [];
        
        // No adjuntar archivos cuando se envía a compras
        if ($this->emailType === 'compras') {
            return $attachments;
        }
        
        // Adjuntar archivo de cotización si existe (para otros destinatarios)
        if ($this->purchaseRequest->quotation_file_path) {
            $filePath = storage_path('app/public/' . $this->purchaseRequest->quotation_file_path);
            
            if (file_exists($filePath)) {
                $attachments[] = Attachment::fromPath($filePath)
                    ->as('cotizacion_servicio_' . $this->purchaseRequest->id . '.' . pathinfo($filePath, PATHINFO_EXTENSION))
                    ->withMime(mime_content_type($filePath));
            }
        }
        
        return $attachments;
    }
}
