<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class QuotationsUploaded extends Notification
{
    use Queueable;

    protected $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        Log::info('Enviando correo de cotizaciones completas', [
            'purchase_request' => $this->purchaseRequest->request_number,
            'to_email' => $notifiable->routes['mail'] ?? 'No email specified'
        ]);

        $url = route('quotation-approvals.show', $this->purchaseRequest->id);

        // Usar la plantilla Blade de emails/purchases/quotations-uploaded.blade.php
        $mailMessage = (new MailMessage)
            ->subject('Cotizaciones Listas para Revisión - Solicitud ' . $this->purchaseRequest->request_number)
            ->markdown('emails.purchases.quotations-uploaded', [
                'request' => $this->purchaseRequest,
                'url' => $url
            ]);

        // Adjuntar PDFs de las cotizaciones
        foreach ($this->purchaseRequest->quotations as $quotation) {
            if (file_exists(storage_path('app/' . $quotation->file_path))) {
                $mailMessage->attach(storage_path('app/' . $quotation->file_path), [
                    'as' => 'Cotización_' . $quotation->provider_name . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }
        
        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->request_number,
            'message' => 'Las cotizaciones están listas para su pre-aprobación',
            'url' => route('quotation-approvals.show', $this->purchaseRequest->id)
        ];
    }
    
    // Si falla el envío, registrar el error
    public function failed(\Exception $exception)
    {
        Log::error('Error al enviar notificación de cotizaciones', [
            'purchase_request' => $this->purchaseRequest->request_number,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
