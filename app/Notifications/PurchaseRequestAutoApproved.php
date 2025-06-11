<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestAutoApproved extends Notification implements ShouldQueue
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
        $requestType = $this->purchaseRequest->isCopiesRequest() ? 'fotocopias' : 'materiales';
        $totalQuantity = $this->purchaseRequest->isCopiesRequest() 
            ? $this->purchaseRequest->getTotalCopiesQuantity() 
            : $this->purchaseRequest->getTotalMaterialsQuantity();

        return (new MailMessage)
            ->subject("Solicitud de {$requestType} aprobada automáticamente - #{$this->purchaseRequest->request_number}")
            ->greeting('Estimado ' . $this->purchaseRequest->requester)
            ->line("Su solicitud de {$requestType} ha sido **aprobada automáticamente** por el sistema.")
            ->line("**Detalles de la solicitud:**")
            ->line("• **Número de solicitud:** {$this->purchaseRequest->request_number}")
            ->line("• **Fecha de solicitud:** " . $this->purchaseRequest->created_at->format('d/m/Y H:i'))
            ->line("• **Cantidad total:** {$totalQuantity} " . ($this->purchaseRequest->isCopiesRequest() ? 'copias' : 'materiales'))
            ->line("• **Fecha de aprobación automática:** " . $this->purchaseRequest->approval_date->format('d/m/Y H:i'))
            ->line("**Motivo de aprobación automática:** La cantidad solicitada es igual o menor a 15 unidades.")
            ->line("Su solicitud será procesada inmediatamente por el área correspondiente.")
            ->action('Ver Solicitud', route('purchase-requests.show', $this->purchaseRequest->id))
            ->line('¡Gracias por utilizar nuestro sistema!')
            ->salutation('Saludos cordiales,<br>Sistema Automático de Aprobaciones<br>The Victoria School');
    }

    public function toDatabase($notifiable)
    {
        $requestType = $this->purchaseRequest->isCopiesRequest() ? 'fotocopias' : 'materiales';
        $totalQuantity = $this->purchaseRequest->isCopiesRequest() 
            ? $this->purchaseRequest->getTotalCopiesQuantity() 
            : $this->purchaseRequest->getTotalMaterialsQuantity();

        return [
            'id' => $this->purchaseRequest->id,
            'title' => "Solicitud de {$requestType} aprobada automáticamente",
            'message' => "Solicitud #{$this->purchaseRequest->request_number} aprobada automáticamente ({$totalQuantity} unidades ≤ 15)",
            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
        ];
    }
}
