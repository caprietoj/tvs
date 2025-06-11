<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;
    protected $order;

    public function __construct(PurchaseRequest $purchaseRequest, PurchaseOrder $order)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Pago Registrado - Solicitud Completada')
                    ->markdown('emails.purchases.payment-registered', [
                        'request' => $this->purchaseRequest,
                        'order' => $this->order,
                        'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                    ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->title,
            'message' => 'El pago ha sido registrado y la solicitud ha sido cerrada',
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
