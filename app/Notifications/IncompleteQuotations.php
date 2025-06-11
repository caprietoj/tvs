<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompleteQuotations extends Notification
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
        $quotationCount = $this->purchaseRequest->quotations()->count();
        $requiredQuotations = $this->purchaseRequest->getRequiredQuotationsCount();
        $progressText = $this->purchaseRequest->getQuotationProgress();
        
        return (new MailMessage)
                    ->subject('Cotizaciones incompletas para revisión - Solicitud #' . $this->purchaseRequest->request_number)
                    ->greeting('Estimado/a usuario/a')
                    ->line('Una solicitud de compra tiene cotizaciones incompletas que requieren su revisión.')
                    ->line('Solicitud: ' . $this->purchaseRequest->request_number)
                    ->line('Título: ' . $this->purchaseRequest->title ?? 'Sin título')
                    ->line('Sección: ' . $this->purchaseRequest->section_area)
                    ->line('Cotizaciones subidas: ' . $progressText)
                    ->line('El solicitante ha indicado que no adjuntará más cotizaciones y requiere su revisión.')
                    ->action('Ver solicitud', url(route('purchase-requests.show', $this->purchaseRequest->id)))
                    ->line('Gracias por usar nuestro sistema.')
                    ->salutation('Atentamente, El equipo de sistemas');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $quotationCount = $this->purchaseRequest->quotations()->count();
        $progressText = $this->purchaseRequest->getQuotationProgress();

        return [
            'id' => $this->purchaseRequest->id,
            'title' => 'Cotizaciones incompletas',
            'message' => 'Solicitud ' . $this->purchaseRequest->request_number . ' tiene ' . $progressText . ' cotizaciones',
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
