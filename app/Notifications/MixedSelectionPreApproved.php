<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MixedSelectionPreApproved extends Notification
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
            ->subject("Selección Mixta {$statusText} - Solicitud #{$this->purchaseRequest->request_number}")
            ->greeting('¡Hola!')
            ->line("Se ha enviado una selección mixta de proveedores para pre-aprobación.")
            ->line("**Solicitud:** #{$this->purchaseRequest->request_number}")
            ->line("**Solicitante:** {$this->purchaseRequest->requester}")
            ->line("**Área/Sección:** {$this->purchaseRequest->section_area}")
            ->line("**Estado de selección:** {$statusText}")
            ->line("**Progreso:** {$progressText}")
            ->line('Por favor, revise la selección realizada y proceda con la aprobación correspondiente.')
            ->action('Revisar y Pre-aprobar', route('quotation-approvals.show', $this->purchaseRequest->id))
            ->line('Gracias por su atención.')
            ->salutation('Equipo de Compras - The Victoria School');
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
