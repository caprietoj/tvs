<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;
    protected $rejectionReason;
    protected $rejectedBy;

    public function __construct(PurchaseRequest $purchaseRequest, $rejectionReason, $rejectedBy)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->rejectionReason = $rejectionReason;
        $this->rejectedBy = $rejectedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Solicitud Rechazada desde Pre-aprobación - #' . $this->purchaseRequest->request_number)
                    ->markdown('emails.purchases.request-rejected-preapproval', [
                        'request' => $this->purchaseRequest,
                        'reason' => $this->rejectionReason,
                        'rejectedBy' => $this->rejectedBy,
                        'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                    ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->title,
            'message' => 'La solicitud #' . $this->purchaseRequest->request_number . ' ha sido rechazada desde pre-aprobación por ' . $this->rejectedBy,
            'url' => route('purchase-requests.show', $this->purchaseRequest->id),
            'rejection_reason' => $this->rejectionReason
        ];
    }
}