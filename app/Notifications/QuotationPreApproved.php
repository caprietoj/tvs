<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class QuotationPreApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;
    protected $quotation;
    protected $directorEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, $directorEmail, Quotation $quotation = null)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->directorEmail = $directorEmail;
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
        // Intentar obtener la cotización pre-aprobada de diferentes maneras
        $quotation = $this->quotation;
        
        // Si no tenemos la cotización pasada directamente, intentamos obtenerla de la relación
        if (!$quotation && $this->purchaseRequest->pre_approved_quotation_id) {
            try {
                // Intentar obtener la cotización desde la relación
                if ($this->purchaseRequest->preApprovedQuotation) {
                    $quotation = $this->purchaseRequest->preApprovedQuotation;
                } else {
                    // Si la relación no funciona, cargar directamente
                    $quotation = Quotation::find($this->purchaseRequest->pre_approved_quotation_id);
                }
            } catch (\Exception $e) {
                Log::error('Error al obtener la cotización pre-aprobada: ' . $e->getMessage());
            }
        }

        // Verificar que tengamos datos de la cotización
        if (!$quotation) {
            Log::warning('No se pudo obtener la información de la cotización pre-aprobada para la notificación', [
                'purchase_request_id' => $this->purchaseRequest->id,
                'pre_approved_quotation_id' => $this->purchaseRequest->pre_approved_quotation_id ? $this->purchaseRequest->pre_approved_quotation_id : 'No definido'
            ]);
            
            // Determinar el nombre del solicitante de forma segura
            $solicitanteName = 'No disponible';
            if ($this->purchaseRequest->user) {
                $solicitanteName = $this->purchaseRequest->user->name;
            }
            
            // Enviar un correo sin detalles de la cotización
            return (new MailMessage)
                ->subject('Nueva Solicitud Pre-aprobada Requiere Aprobación Final')
                ->greeting('Estimado(a) Director(a):')
                ->line('Se ha pre-aprobado una solicitud que requiere su aprobación final.')
                ->line('Detalles de la solicitud:')
                ->line("Número de solicitud: {$this->purchaseRequest->request_number}")
                ->line("Área/Sección: {$this->purchaseRequest->section_area}")
                ->line("Solicitante: {$solicitanteName}")
                ->action('Ver Solicitud', url("/approvals/{$this->purchaseRequest->id}"))
                ->line('Esta solicitud no será procesada hasta que se complete la aprobación final.')
                ->salutation('Atentamente,');
        }

        // Si tenemos la cotización, enviar el correo con todos los detalles
        $formattedAmount = number_format($quotation->total_amount, 0, ',', '.');
        
        // Determinar el nombre del solicitante de forma segura
        $solicitanteName = 'No disponible';
        if ($this->purchaseRequest->user) {
            $solicitanteName = $this->purchaseRequest->user->name;
        }
        
        // Determinar tiempo de entrega y forma de pago de forma segura
        $deliveryTime = 'No especificado';
        if ($quotation->delivery_time) {
            $deliveryTime = $quotation->delivery_time;
        }
        
        $paymentMethod = 'No especificada';
        if ($quotation->payment_method) {
            $paymentMethod = $quotation->payment_method;
        }
        
        return (new MailMessage)
                ->subject('Nueva Solicitud Pre-aprobada Requiere Aprobación Final')
                ->greeting('Estimado(a) Director(a):')
                ->line('Se ha pre-aprobado una solicitud que requiere su aprobación final.')
                ->line('Detalles de la solicitud:')
                ->line("Número de solicitud: {$this->purchaseRequest->request_number}")
                ->line("Área/Sección: {$this->purchaseRequest->section_area}")
                ->line("Solicitante: {$solicitanteName}")
                ->line("Proveedor seleccionado: {$quotation->provider_name}")
                ->line("Monto: $" . $formattedAmount)
                ->line("Tiempo de Entrega: {$deliveryTime}")
                ->line("Forma de Pago: {$paymentMethod}")
                ->action('Ver Solicitud', url("/approvals/{$this->purchaseRequest->id}"))
                ->line('Esta solicitud no será procesada hasta que se complete la aprobación final.')
                ->salutation('Atentamente,');
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
        ];
    }
}
