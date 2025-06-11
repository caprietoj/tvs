<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->purchaseRequest->type === 'purchase' ? 
            'Solicitud de compra #' . $this->purchaseRequest->id : 
            'Solicitud de materiales #' . $this->purchaseRequest->id;
            
        return (new MailMessage)
                    ->subject('Aprobación de ' . $title)
                    ->greeting('Hola,')
                    ->line('La solicitud "' . $title . '" ha sido aprobada definitivamente.')
                    ->line('Detalles de la aprobación:')
                    ->line('- Solicitante: ' . $this->purchaseRequest->requester)
                    ->line('- Área/Sección: ' . $this->purchaseRequest->section_area)
                    ->line('- Fecha de solicitud: ' . $this->purchaseRequest->request_date->format('d/m/Y'))
                    ->line('- Fecha de aprobación: ' . now()->format('d/m/Y'))
                    ->line('- Cotización aprobada: ' . $this->purchaseRequest->preApprovedQuotation->provider_name)
                    ->line('- Monto total: $' . number_format($this->purchaseRequest->preApprovedQuotation->total_amount, 2, ',', '.'))
                    ->action('Ver detalles', url('/purchase-requests/' . $this->purchaseRequest->id))
                    ->line('Gracias por utilizar nuestra aplicación.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->purchaseRequest->id,
            'title' => $this->purchaseRequest->type === 'purchase' ? 
                'Solicitud de compra #' . $this->purchaseRequest->id : 
                'Solicitud de materiales #' . $this->purchaseRequest->id,
            'message' => 'La solicitud ha sido aprobada definitivamente.',
            'url' => url('/purchase-requests/' . $this->purchaseRequest->id)
        ];
    }
}
