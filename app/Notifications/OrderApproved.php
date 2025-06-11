<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderApproved extends Notification implements ShouldQueue
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
        $mailMessage = (new MailMessage)
                    ->subject('Orden de Compra Aprobada')
                    ->markdown('emails.purchases.order-approved', [
                        'request' => $this->purchaseRequest,
                        'order' => $this->order,
                        'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                    ]);
                    
        // Adjuntar PDF de la orden
        if ($this->order->pdf_path) {
            $mailMessage->attach(storage_path('app/' . $this->order->pdf_path), [
                'as' => $this->order->order_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        
        // Adjuntar cotizaciones
        foreach ($this->purchaseRequest->quotations as $quotation) {
            $mailMessage->attach(storage_path('app/' . $quotation->file_path), [
                'as' => 'Cotizacion_' . $quotation->provider_name . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->title,
            'message' => 'La orden de compra ha sido aprobada',
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
