<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestRejected extends Notification implements ShouldQueue
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
        return (new MailMessage)
                    ->subject('Solicitud de Compra Rechazada')
                    ->markdown('emails.purchases.request-rejected', [
                        'request' => $this->purchaseRequest,
                        'reason' => $this->purchaseRequest->rejection_reason,
                        'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                    ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->title,
            'message' => 'La solicitud ha sido rechazada: ' . substr($this->purchaseRequest->rejection_reason, 0, 30) . '...',
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
