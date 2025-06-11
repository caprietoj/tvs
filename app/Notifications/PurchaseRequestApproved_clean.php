<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PurchaseRequestApproved extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $recipient;

    public function __construct(PurchaseRequest $purchaseRequest, $recipient = 'user')
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->recipient = $recipient;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildBaseMessage();
        
        if (in_array($this->recipient, ['compras', 'contabilidad'])) {
            $this->attachPurchaseOrderPdf($message);
        }
        
        return $message;
    }

    private function buildBaseMessage(): MailMessage
    {
        $message = new MailMessage;
        
        if ($this->recipient === 'user') {
            $message->subject('Solicitud de Compra Autorizada - #' . $this->purchaseRequest->request_number)
                   ->greeting('¡Hola ' . $this->purchaseRequest->requester . '!')
                   ->line('Le informamos que su solicitud de compra ha sido **autorizada** exitosamente.')
                   ->line('**Detalles de la solicitud:**')
                   ->line('• **Número:** ' . ($this->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Área/Sección:** ' . $this->purchaseRequest->section_area)
                   ->line('• **Fecha de solicitud:** ' . $this->purchaseRequest->request_date->format('d/m/Y'))
                   ->line('• **Fecha de autorización:** ' . now()->format('d/m/Y H:i'));
                   
            if ($this->purchaseRequest->selectedQuotation) {
                $message->line('• **Proveedor seleccionado:** ' . $this->purchaseRequest->selectedQuotation->provider_name)
                       ->line('• **Monto autorizado:** $' . number_format($this->purchaseRequest->selectedQuotation->total_amount, 2, ',', '.'));
            }
            
            $message->line('Su solicitud procederá al área de compras para su procesamiento.')
                   ->line('Puede revisar los detalles completos en el sistema.')
                   ->action('Ver Solicitud', url('/purchase-requests/' . $this->purchaseRequest->id))
                   ->line('¡Gracias por utilizar nuestro sistema!')
                   ->salutation('Saludos cordiales,<br>Equipo de Compras<br>The Victoria School');
                   
        } elseif ($this->recipient === 'compras') {
            $message->subject('Nueva Orden de Compra Generada - #' . $this->purchaseRequest->request_number)
                   ->greeting('Estimado Equipo de Compras')
                   ->line('Se ha generado una nueva **orden de compra** que requiere su procesamiento.')
                   ->line('**Detalles de la solicitud autorizada:**')
                   ->line('• **Número de solicitud:** ' . ($this->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseRequest->requester)
                   ->line('• **Área/Sección:** ' . $this->purchaseRequest->section_area)
                   ->line('• **Fecha de autorización:** ' . now()->format('d/m/Y H:i'));
                   
            if ($this->purchaseRequest->selectedQuotation) {
                $message->line('• **Proveedor:** ' . $this->purchaseRequest->selectedQuotation->provider_name)
                       ->line('• **Monto total:** $' . number_format($this->purchaseRequest->selectedQuotation->total_amount, 2, ',', '.'));
            }
            
            $message->line('**Se adjunta la orden de compra en PDF para su procesamiento.**')
                   ->line('Por favor, proceder con la gestión correspondiente.')
                   ->action('Ver Solicitud', url('/purchase-requests/' . $this->purchaseRequest->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
                   
        } elseif ($this->recipient === 'contabilidad') {
            $message->subject('Orden de Compra para Registro Contable - #' . $this->purchaseRequest->request_number)
                   ->greeting('Estimado Equipo de Contabilidad')
                   ->line('Se ha generado una nueva **orden de compra** que debe ser registrada contablemente.')
                   ->line('**Información para registro:**')
                   ->line('• **Número de solicitud:** ' . ($this->purchaseRequest->request_number ?? 'N/A'))
                   ->line('• **Solicitante:** ' . $this->purchaseRequest->requester)
                   ->line('• **Área/Sección:** ' . $this->purchaseRequest->section_area)
                   ->line('• **Fecha de autorización:** ' . now()->format('d/m/Y H:i'));
                   
            if ($this->purchaseRequest->selectedQuotation) {
                $message->line('• **Proveedor:** ' . $this->purchaseRequest->selectedQuotation->provider_name)
                       ->line('• **Monto a registrar:** $' . number_format($this->purchaseRequest->selectedQuotation->total_amount, 2, ',', '.'));
            }
            
            $message->line('**Se adjunta la orden de compra en PDF para el registro contable.**')
                   ->line('Por favor, proceder con el registro correspondiente.')
                   ->action('Ver Solicitud', url('/purchase-requests/' . $this->purchaseRequest->id))
                   ->salutation('Sistema de Gestión de Compras<br>The Victoria School');
        }
        
        return $message;
    }

    private function attachPurchaseOrderPdf(MailMessage $message): void
    {
        try {
            $purchaseOrder = PurchaseOrder::where('purchase_request_id', $this->purchaseRequest->id)->first();
            
            if ($purchaseOrder) {
                $pdfService = app(PurchaseOrderPdfService::class);
                $pdf = $pdfService->createPdf($purchaseOrder);
                $fileName = 'orden_compra_' . ($purchaseOrder->order_number ?? $purchaseOrder->id) . '.pdf';
                
                $message->attachData($pdf->output(), $fileName, [
                    'mime' => 'application/pdf',
                ]);
                
                Log::info('PDF con nueva plantilla adjuntado exitosamente para destinatario: ' . $this->recipient, [
                    'purchase_request_id' => $this->purchaseRequest->id,
                    'file_name' => $fileName,
                    'recipient' => $this->recipient
                ]);
            }
        } catch (\Exception $e) {            
            Log::error('Error al adjuntar orden de compra en notificación: ' . $e->getMessage());
        }
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'message' => 'Su solicitud de compra #' . ($this->purchaseRequest->request_number ?? 'N/A') . ' ha sido autorizada.',
            'type' => 'purchase_request_approved'
        ];
    }
}
