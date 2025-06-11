<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestStatus extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $message;

    public function __construct(PurchaseRequest $purchaseRequest, $message)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->message = $message;
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
            'message' => $this->message,
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
