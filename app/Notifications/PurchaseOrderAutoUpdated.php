<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PurchaseOrderAutoUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $quotation;
    protected $purchaseRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseOrder $order, Quotation $quotation, PurchaseRequest $purchaseRequest)
    {
        $this->order = $order;
        $this->quotation = $quotation;
        $this->purchaseRequest = $purchaseRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            $orderNumber = $this->order->order_number ?? 'N/A';
            $providerName = $this->quotation->provider_name ?? 'N/A';
            $newTotal = number_format($this->quotation->total_amount, 0, ',', '.');
            $requestNumber = $this->purchaseRequest->request_number ?? 'N/A';
            $requesterName = $this->purchaseRequest->user ? $this->purchaseRequest->user->name : 'N/A';
            $sectionArea = $this->purchaseRequest->section_area ?? 'N/A';

            return (new MailMessage)
                ->subject('🔄 Orden de Compra Actualizada Automáticamente - ' . $orderNumber)
                ->greeting('Estimado Departamento de Compras:')
                ->line('Una orden de compra ha sido actualizada automáticamente debido a cambios en la cotización correspondiente.')
                
                ->line('**📋 Detalles de la Orden:**')
                ->line("• **Número de orden:** {$orderNumber}")
                ->line("• **Estado:** {$this->order->status}")
                ->line("• **Proveedor:** {$providerName}")
                ->line("• **Nuevo total:** \${$newTotal}")
                
                ->line('**📄 Detalles de la Solicitud:**')
                ->line("• **Número de solicitud:** {$requestNumber}")
                ->line("• **Solicitante:** {$requesterName}")
                ->line("• **Área/Sección:** {$sectionArea}")
                
                ->line('**🔄 Razón de la Actualización:**')
                ->line('La cotización de este proveedor fue modificada en una solicitud ya autorizada, por lo que el sistema actualizó automáticamente la orden de compra para mantener la coherencia.')
                
                ->line('**📋 Acciones Recomendadas:**')
                ->line('• Revisar los cambios en la orden de compra')
                ->line('• Verificar que los nuevos montos son correctos')
                ->line('• Contactar al proveedor si es necesario actualizar términos')
                ->line('• Regenerar el PDF de la orden si es necesario')
                
                ->action('Ver Orden de Compra', url("/purchase-orders/{$this->order->id}"))
                ->action('Ver Solicitud Original', url("/purchase-requests/{$this->purchaseRequest->id}"))
                
                ->line('**ℹ️ Información Adicional:**')
                ->line('Esta actualización fue realizada automáticamente por el sistema para mantener la coherencia entre las cotizaciones y las órdenes de compra.')
                ->line('Si considera que esta actualización no es correcta, puede editar manualmente la orden de compra.')
                
                ->salutation('Atentamente,<br>Sistema Automatizado de Intranet TVS');

        } catch (\Exception $e) {
            Log::error('Error al generar correo de actualización automática de orden', [
                'order_id' => $this->order->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Correo de respaldo en caso de error
            return (new MailMessage)
                ->subject('🔄 Orden de Compra Actualizada Automáticamente')
                ->greeting('Estimado Departamento de Compras:')
                ->line('Una orden de compra ha sido actualizada automáticamente.')
                ->line('Por favor, revise la orden en el sistema para ver los cambios.')
                ->action('Ver Órdenes de Compra', url('/purchase-orders'))
                ->salutation('Atentamente,<br>Sistema de Intranet TVS');
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'purchase_order_auto_updated',
            'title' => 'Orden de Compra Actualizada Automáticamente',
            'message' => "La orden {$this->order->order_number} fue actualizada automáticamente debido a cambios en la cotización de {$this->quotation->provider_name}",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'quotation_id' => $this->quotation->id,
            'purchase_request_id' => $this->purchaseRequest->id,
            'request_number' => $this->purchaseRequest->request_number,
            'provider_name' => $this->quotation->provider_name,
            'new_total' => $this->quotation->total_amount,
            'updated_at' => now()->toISOString(),
        ];
    }

    /**
     * Dispatch notification to a user
     */
    public static function dispatch(User $user, PurchaseOrder $order, Quotation $quotation, PurchaseRequest $purchaseRequest)
    {
        try {
            $user->notify(new static($order, $quotation, $purchaseRequest));
            
            Log::info('Notificación de actualización automática enviada', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de actualización automática', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
