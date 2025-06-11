<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPurchaseRequest extends Notification
{
    use Queueable;

    protected $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->title,
            'user' => $this->purchaseRequest->user->name,
            'message' => 'Nueva solicitud de compra creada',
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
