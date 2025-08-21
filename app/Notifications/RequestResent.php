<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PurchaseRequest;

class RequestResent extends Notification
{
    use Queueable;

    protected $purchaseRequest;
    protected $message;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $purchaseRequest, $message, $type)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->message = $message;
        $this->type = $type; // 'pre-approval' o 'approval'
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
        $actionText = $this->type === 'pre-approval' ? 'Pre-aprobar Solicitud' : 'Aprobar Solicitud';
        $actionUrl = $this->type === 'pre-approval' 
            ? route('quotation-approvals.show', $this->purchaseRequest->id)
            : route('approvals.show', $this->purchaseRequest->id);

        $subject = $this->type === 'pre-approval' 
            ? 'Solicitud Reenviada para Pre-aprobación'
            : 'Solicitud Reenviada para Aprobación';

        return (new MailMessage)
            ->subject($subject . ' - #' . $this->purchaseRequest->request_number)
            ->greeting('Estimado/a colaborador/a,')
            ->line('Se ha reenviado una solicitud de compra para su revisión.')
            ->line('**Número de solicitud:** ' . $this->purchaseRequest->request_number)
            ->line('**Solicitante:** ' . $this->purchaseRequest->requester)
            ->line('**Sección:** ' . $this->purchaseRequest->section_area)
            ->line('**Estado actual:** ' . $this->purchaseRequest->status)
            ->when($this->message, function ($mail) {
                return $mail->line('**Mensaje del administrador:** ' . $this->message);
            })
            ->action($actionText, $actionUrl)
            ->line('Por favor, revise la solicitud y tome la acción correspondiente.')
            ->line('Gracias por su atención.')
            ->salutation('Cordialmente, Sistema de Compras TVS');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
