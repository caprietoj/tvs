<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderValidationService;
use Illuminate\Support\Facades\Log;

class PurchaseOrderObserver
{
    protected $validationService;

    public function __construct(PurchaseOrderValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Handle the PurchaseOrder "saved" event.
     */
    public function saved(PurchaseOrder $purchaseOrder): void
    {
        // Solo validar si tiene pdf_custom_data
        if (!empty($purchaseOrder->pdf_custom_data)) {
            try {
                // Evitar recursión infinita usando un flag
                if (!$purchaseOrder->isDirty() || $purchaseOrder->wasRecentlyCreated) {
                    return;
                }
                
                $this->validationService->validateOnSave($purchaseOrder);
                
            } catch (\Exception $e) {
                Log::error('Error en validación automática de orden', [
                    'order_number' => $purchaseOrder->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        // La validación se maneja en el evento saved
    }
}
