<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderSent extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseOrder;
    protected $recipient;

    public function __construct(PurchaseOrder $purchaseOrder, $recipient = 'compras')
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->recipient = $recipient;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = new MailMessage;
        
        if ($this->recipient === 'compras') {
            $message->subject('Orden de Compra para Gestión - #' . $this->purchaseOrder->order_number)
                   ->greeting('Estimado Equipo de Compras')
                   ->line('Se ha enviado una nueva **orden de compra** para su gestión y procesamiento.')
                   ->line('**Detalles de la Orden:**')
                   ->line('• **Número de orden:** ' . $this->purchaseOrder->order_number)
                   ->line('• **Solicitud asociada:** ' . ($this->purchaseOrder->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseOrder->purchaseRequest->requester)
                   ->line('• **Proveedor:** ' . ($this->purchaseOrder->provider->nombre ?? 'N/A'))
                   ->line('• **Monto total:** $' . number_format($this->purchaseOrder->total_amount, 2, ',', '.'))
                   ->line('Por favor, proceder con la gestión correspondiente del proveedor.')
                   ->action('Ver Orden de Compra', route('purchase-orders.show', $this->purchaseOrder->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
                   
        } elseif ($this->recipient === 'contabilidad') {
            $message->subject('Orden de Compra para Registro Contable - #' . $this->purchaseOrder->order_number)
                   ->greeting('Estimado Equipo de Contabilidad')
                   ->line('Se ha enviado una **orden de compra** para su registro contable.')
                   ->line('**Información para registro:**')
                   ->line('• **Número de orden:** ' . $this->purchaseOrder->order_number)
                   ->line('• **Solicitud asociada:** ' . ($this->purchaseOrder->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseOrder->purchaseRequest->requester)
                   ->line('• **Área/Sección:** ' . $this->purchaseOrder->purchaseRequest->section_area)
                   ->line('• **Proveedor:** ' . ($this->purchaseOrder->provider->nombre ?? 'N/A'))
                   ->line('• **Monto a registrar:** $' . number_format($this->purchaseOrder->total_amount, 2, ',', '.'))
                   ->line('• **Presupuesto:** ' . ($this->purchaseOrder->purchaseRequest->budget ?? 'N/A'))
                   ->line('Por favor, proceder con el registro contable correspondiente.')
                   ->action('Ver Orden de Compra', route('purchase-orders.show', $this->purchaseOrder->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
                     } elseif ($this->recipient === 'tesoreria') {
            $message->subject('Orden de Compra para Gestión de Pagos - #' . $this->purchaseOrder->order_number)
                   ->greeting('Estimado Equipo de Tesorería')
                   ->line('Se ha enviado una **orden de compra** para su gestión de pagos.')
                   ->line('**Información para gestión:**')
                   ->line('• **Número de orden:** ' . $this->purchaseOrder->order_number)
                   ->line('• **Solicitud asociada:** ' . ($this->purchaseOrder->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseOrder->purchaseRequest->requester)
                   ->line('• **Proveedor:** ' . ($this->purchaseOrder->provider->nombre ?? 'N/A'))
                   ->line('• **Monto total:** $' . number_format($this->purchaseOrder->total_amount, 2, ',', '.'))
                   ->line('• **Forma de pago:** ' . ($this->purchaseOrder->payment_terms ?? 'Contado'))
                   ->line('• **Fecha de entrega:** ' . ($this->purchaseOrder->delivery_date ? $this->purchaseOrder->delivery_date->format('d/m/Y') : 'N/A'))
                   ->line('Por favor, proceder con la gestión de pagos correspondiente.')
                   ->action('Ver Orden de Compra', route('purchase-orders.show', $this->purchaseOrder->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
                   
        } elseif ($this->recipient === 'compras_copy') {
            $message->subject('Copia: Orden de Compra Enviada - #' . $this->purchaseOrder->order_number)
                   ->greeting('Estimado Equipo de Compras')
                   ->line('Se ha enviado una **orden de compra** a otro departamento para su gestión.')
                   ->line('**Información de la orden:**')
                   ->line('• **Número de orden:** ' . $this->purchaseOrder->order_number)
                   ->line('• **Solicitud asociada:** ' . ($this->purchaseOrder->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseOrder->purchaseRequest->requester)
                   ->line('• **Proveedor:** ' . ($this->purchaseOrder->provider->nombre ?? 'N/A'))
                   ->line('• **Monto total:** $' . number_format($this->purchaseOrder->total_amount, 2, ',', '.'))
                   ->line('Esta es una copia informativa para el seguimiento del proceso de compras.')
                   ->action('Ver Orden de Compra', route('purchase-orders.show', $this->purchaseOrder->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
        }
        
        return $message;
    }
}
