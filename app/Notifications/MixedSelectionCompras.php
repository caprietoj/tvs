<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MixedSelectionCompras extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $isComplete;
    protected $selectedCount;
    protected $totalCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, $isComplete, $selectedCount, $totalCount)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->isComplete = $isComplete;
        $this->selectedCount = $selectedCount;
        $this->totalCount = $totalCount;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $statusText = $this->isComplete ? 'Completa' : 'Parcial';
        $progressText = $this->isComplete ? 
            'Se han seleccionado proveedores para todos los items.' : 
            "Se han seleccionado {$this->selectedCount} de {$this->totalCount} items.";

        return (new MailMessage)
            ->subject("Información: Selección Mixta {$statusText} - Solicitud #{$this->purchaseRequest->request_number}")
            ->greeting('¡Hola Equipo de Compras!')
            ->line("Se ha enviado una selección mixta de proveedores para pre-aprobación.")
            ->line("**Solicitud:** #{$this->purchaseRequest->request_number}")
            ->line("**Solicitante:** {$this->purchaseRequest->requester}")
            ->line("**Área/Sección:** {$this->purchaseRequest->section_area}")
            ->line("**Estado de selección:** {$statusText}")
            ->line("**Progreso:** {$progressText}")
            ->line('Esta es una notificación informativa. La solicitud está siendo revisada por el supervisor correspondiente.')
            ->line('Pueden monitorear el progreso desde el panel de control.')
            ->action('Ver Detalles', route('purchase-requests.show', $this->purchaseRequest->id))
            ->line('Gracias por su atención.')
            ->salutation('Sistema de Gestión de Compras - The Victoria School');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'request_number' => $this->purchaseRequest->request_number,
            'is_complete' => $this->isComplete,
            'selected_count' => $this->selectedCount,
            'total_count' => $this->totalCount
        ];
    }
}
