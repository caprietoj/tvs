<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class QuotationSelected extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $quotation;

    public function __construct(PurchaseRequest $purchaseRequest, Quotation $quotation)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->quotation = $quotation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Cotización Pre-Aprobada')
                    ->markdown('emails.purchases.quotation-selected', [
                        'request' => $this->purchaseRequest,
                        'quotation' => $this->quotation,
                        'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                    ]);
    }

    public function toArray($notifiable)
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'quotation_id' => $this->quotation->id,
            'message' => 'La cotización de ' . $this->quotation->provider_name . ' ha sido pre-aprobada para la solicitud #' . $this->purchaseRequest->request_number,
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
