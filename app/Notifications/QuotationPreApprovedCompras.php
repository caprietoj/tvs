<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class QuotationPreApprovedCompras extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;
    protected $quotation;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, Quotation $quotation = null)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->quotation = $quotation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Intentar obtener la cotización pre-aprobada
        $quotation = $this->quotation;
        
        if (!$quotation && $this->purchaseRequest->pre_approved_quotation_id) {
            try {
                if ($this->purchaseRequest->preApprovedQuotation) {
                    $quotation = $this->purchaseRequest->preApprovedQuotation;
                } else {
                    $quotation = Quotation::find($this->purchaseRequest->pre_approved_quotation_id);
                }
            } catch (\Exception $e) {
                Log::error('Error al obtener la cotización pre-aprobada para compras: ' . $e->getMessage());
            }
        }

        // Determinar el nombre del solicitante
        $solicitanteName = $this->purchaseRequest->user ? $this->purchaseRequest->user->name : 'No disponible';

        if (!$quotation) {
            // Correo informativo sin detalles de cotización
            return (new MailMessage)
                ->subject('Solicitud Pre-aprobada - Información para Compras')
                ->greeting('Estimado Departamento de Compras:')
                ->line('Se ha pre-aprobado una solicitud de compra que está pendiente de aprobación final.')
                ->line('**Detalles de la solicitud:**')
                ->line("• Número de solicitud: {$this->purchaseRequest->request_number}")
                ->line("• Área/Sección: {$this->purchaseRequest->section_area}")
                ->line("• Solicitante: {$solicitanteName}")
                ->line('**Información importante:**')
                ->line('• Esta solicitud aún requiere aprobación final del director de área.')
                ->line('• Una vez aprobada, recibirán una notificación para generar la orden de compra.')
                ->line('• No se requiere ninguna acción por parte de compras en este momento.')
                ->salutation('Atentamente,')
                ->salutation('Sistema de Intranet TVS');
        }

        // Correo informativo con detalles de cotización
        $formattedAmount = number_format($quotation->total_amount, 0, ',', '.');
        $deliveryTime = $quotation->delivery_time ?: 'No especificado';
        $paymentMethod = $quotation->payment_method ?: 'No especificada';
        
        return (new MailMessage)
                ->subject('Solicitud Pre-aprobada - Información para Compras')
                ->greeting('Estimado Departamento de Compras:')
                ->line('Se ha pre-aprobado una solicitud de compra que está pendiente de aprobación final.')
                ->line('**Detalles de la solicitud:**')
                ->line("• Número de solicitud: {$this->purchaseRequest->request_number}")
                ->line("• Área/Sección: {$this->purchaseRequest->section_area}")
                ->line("• Solicitante: {$solicitanteName}")
                ->line('**Detalles de la cotización seleccionada:**')
                ->line("• Proveedor seleccionado: {$quotation->provider_name}")
                ->line("• Monto: $" . $formattedAmount)
                ->line("• Tiempo de Entrega: {$deliveryTime}")
                ->line("• Forma de Pago: {$paymentMethod}")
                ->line('**Información importante:**')
                ->line('• Esta solicitud aún requiere aprobación final del director de área.')
                ->line('• Una vez aprobada, recibirán una notificación para generar la orden de compra.')
                ->line('• No se requiere ninguna acción por parte de compras en este momento.')
                ->salutation('Atentamente,')
                ->salutation('Sistema de Intranet TVS');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'request_number' => $this->purchaseRequest->request_number,
            'type' => 'pre_approved_info'
        ];
    }
}
