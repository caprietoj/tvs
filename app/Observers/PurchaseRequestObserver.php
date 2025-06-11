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
        // COMENTADO TEMPORALMENTE: Este observer estaba enviando emails con CC hardcodeado
        // Esto puede estar causando emails duplicados durante las pre-aprobaciones
        
        /*
        // Enviar email para cualquier tipo de solicitud
        try {
            Mail::to($purchaseRequest->user->email)
                ->cc('caprietoj@gmail.com')
                ->send(new PurchaseRequestCreated($purchaseRequest));
        } catch (\Exception $e) {
            \Log::error('Error al enviar email desde observer: ' . $e->getMessage());
        }
        */
        
        // TODO: Revisar si este observer es necesario y corregir el email hardcodeado
        \Log::info('PurchaseRequestObserver::created() ejecutado (emails deshabilitados temporalmente)', [
            'purchase_request_id' => $purchaseRequest->id,
            'request_number' => $purchaseRequest->request_number
        ]);
    }

    // ...existing code...
}