<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OrderCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseOrder;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
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
        $order = $this->purchaseOrder->load(['purchaseRequest', 'provider', 'creator']);
        
        $formattedAmount = number_format($order->total_amount, 2, ',', '.');
        $url = url('/storage/' . $order->file_path);
        
        return (new MailMessage)
                    ->subject('Nueva orden de compra para procesar - ' . $order->order_number)
                    ->greeting('Hola,')
                    ->line('Se ha generado una nueva orden de compra que requiere procesamiento de pago:')
                    ->line(new HtmlString('<strong>Número de Orden:</strong> ' . $order->order_number))
                    ->line(new HtmlString('<strong>Proveedor:</strong> ' . $order->provider->nombre))
                    ->line(new HtmlString('<strong>Monto Total:</strong> $' . $formattedAmount))
                    ->line(new HtmlString('<strong>Términos de pago:</strong> ' . $order->payment_terms))
                    ->line(new HtmlString('<strong>Fecha de entrega esperada:</strong> ' . $order->delivery_date->format('d/m/Y')))
                    ->line(new HtmlString('<strong>Solicitado por:</strong> ' . ($order->purchaseRequest->requester ?? 'N/A')))
                    ->line(new HtmlString('<strong>Departamento:</strong> ' . ($order->purchaseRequest->section_area ?? 'N/A')))
                    ->line(new HtmlString('<strong>Observaciones:</strong> ' . ($order->observations ?? 'Sin observaciones')))
                    ->action('Ver Orden de Compra', $url)
                    ->line('Por favor procesa esta orden de compra para su pago de acuerdo a los términos establecidos.')
                    ->salutation('Saludos del equipo de compras.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_order_id' => $this->purchaseOrder->id,
            'order_number' => $this->purchaseOrder->order_number,
            'provider_name' => $this->purchaseOrder->provider->nombre,
            'total_amount' => $this->purchaseOrder->total_amount,
        ];
    }
}
