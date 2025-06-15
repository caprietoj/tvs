<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class QuotationsCompletedCompras extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Determinar el nombre del solicitante
        $solicitanteName = $this->purchaseRequest->user ? $this->purchaseRequest->user->name : 'No disponible';
        
        // Contar cotizaciones
        $quotationsCount = $this->purchaseRequest->quotations()->count();
          return (new MailMessage)
                ->subject('Cotizaciones Completadas - Solicitud en Pre-aprobación')
                ->greeting('Estimado Departamento de Compras:')
                ->line('✅ **Las cotizaciones han sido completadas y enviadas exitosamente.**')
                ->line('')
                ->line('Se adjuntaron **' . $quotationsCount . ' cotizaciones** para la siguiente solicitud:')
                ->line('')
                ->line('**📋 Detalles de la solicitud:**')
                ->line("• **Número:** {$this->purchaseRequest->request_number}")
                ->line("• **Solicitante:** {$solicitanteName}")
                ->line("• **Área/Sección:** {$this->purchaseRequest->section_area}")
                ->line("• **Fecha de solicitud:** " . $this->purchaseRequest->created_at->format('d/m/Y'))
                ->line("• **Cotizaciones adjuntadas:** {$quotationsCount}")
                ->line('')
                ->line('📧 **Estado actual:** Las cotizaciones fueron enviadas automáticamente al coordinador de ' . $this->purchaseRequest->section_area . ' para su revisión y pre-aprobación.')
                ->line('')
                ->line('🔄 **Próximos pasos:**')
                ->line('1. El coordinador revisará las cotizaciones recibidas')
                ->line('2. Seleccionará la mejor opción según criterios técnicos y económicos')
                ->line('3. Pre-aprobará la solicitud en el sistema')
                ->line('4. Una vez pre-aprobada, recibirán instrucciones específicas para generar la orden de compra')
                ->line('')
                ->line('ℹ️ **Información importante:**')
                ->line('• Esta es una notificación informativa - no requiere acción de su parte')
                ->line('• El proceso de pre-aprobación está en curso')
                ->line('• Recibirán otra notificación cuando se requiera su intervención')
                ->line('')
                ->line('Gracias por su atención y por mantener el flujo de compras funcionando eficientemente.')
                ->salutation('Cordialmente,')
                ->salutation('Sistema de Compras - The Victoria School');
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
            'type' => 'quotations_completed_info'
        ];
    }
}
