<?php

namespace App\Observers;

use App\Models\PurchaseRequest;
use App\Mail\PurchaseRequestCreated;
use Illuminate\Support\Facades\Mail;

class PurchaseRequestObserver
{
    /**
     * Handle the PurchaseRequest "created" event.
     */
    public function created(PurchaseRequest $purchaseRequest)
    {
        // Enviar email para cualquier tipo de solicitud usando el interceptor
        try {
            $emailTestService = new \App\Services\EmailTestModeService();
            $interceptedEmail = $emailTestService->interceptEmail($purchaseRequest->user->email, 'General');
            
            Mail::to($interceptedEmail)
                ->send(new PurchaseRequestCreated($purchaseRequest));
                
            \Log::info('Email enviado desde PurchaseRequestObserver', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'to_original' => $purchaseRequest->user->email,
                'to_intercepted' => $interceptedEmail
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email desde observer: ' . $e->getMessage());
        }
    }

    // ...existing code...
}