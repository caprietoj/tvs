<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MixedSelectionFinalApproval extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $mixedSelections;
    protected $totalAmount;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, $mixedSelections, $totalAmount)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->mixedSelections = $mixedSelections;
        $this->totalAmount = $totalAmount;
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
        return (new MailMessage)
            ->subject("Selección Mixta Pre-aprobada - Solicitud #{$this->purchaseRequest->request_number}")
            ->greeting('¡Hola!')
            ->line("La selección mixta de proveedores ha sido **pre-aprobada** y está lista para aprobación final.")
            ->line("**Solicitud:** #{$this->purchaseRequest->request_number}")
            ->line("**Solicitante:** {$this->purchaseRequest->requester}")
            ->line("**Área/Sección:** {$this->purchaseRequest->section_area}")
            ->line("**Total de items seleccionados:** " . ($this->mixedSelections ? $this->mixedSelections->count() : 0))
            ->line("**Monto total:** $" . number_format($this->totalAmount, 2))
            ->line("**Rubro presupuestal:** " . ($this->purchaseRequest->budget_line ?? 'No especificado'))
            ->line('')
            ->line('Por favor, revise la selección mixta y proceda con la **aprobación final**.')
            ->action('Revisar y Aprobar', route('approvals.show', $this->purchaseRequest->id))
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
            'mixed_selections_count' => $this->mixedSelections ? $this->mixedSelections->count() : 0,
            'total_amount' => $this->totalAmount,
        ];
    }
}
