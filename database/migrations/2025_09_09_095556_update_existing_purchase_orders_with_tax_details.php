<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar todas las órdenes existentes con los nuevos campos de impuestos
        $orders = PurchaseOrder::all();
        
        foreach ($orders as $order) {
            // Obtener la solicitud de compra relacionada
            $purchaseRequest = $order->purchaseRequest;
            
            // Determinar los impuestos aplicados
            $appliedTaxes = ['iva_19']; // Por defecto IVA 19%
            $subtotalAmount = $order->subtotal ?? 0;
            $taxAmount19 = 0;
            $taxAmount8 = 0;
            $taxAmount5 = 0;
            $taxAmount4 = 0;
            
            // Si la solicitud tiene impuestos aplicados específicos, usarlos
            if ($purchaseRequest && $purchaseRequest->applied_taxes && is_array($purchaseRequest->applied_taxes)) {
                $appliedTaxes = $purchaseRequest->applied_taxes;
                
                // Calcular impuestos específicos basados en el subtotal
                foreach ($appliedTaxes as $tax) {
                    switch ($tax) {
                        case 'iva_19':
                            $taxAmount19 = round($subtotalAmount * 0.19, 2);
                            break;
                        case 'iva_5':
                            $taxAmount5 = round($subtotalAmount * 0.05, 2);
                            break;
                        case 'consumo_8':
                            $taxAmount8 = round($subtotalAmount * 0.08, 2);
                            break;
                        case 'consumo_4':
                            $taxAmount4 = round($subtotalAmount * 0.04, 2);
                            break;
                    }
                }
            } else {
                // Si no hay impuestos específicos, usar el IVA existente
                $taxAmount19 = $order->iva_amount ?? 0;
            }
            
            $totalTaxAmount = $taxAmount19 + $taxAmount8 + $taxAmount5 + $taxAmount4;
            
            // Actualizar la orden con los nuevos campos
            $order->update([
                'applied_taxes' => $appliedTaxes,
                'subtotal_amount' => $subtotalAmount,
                'tax_amount_19' => $taxAmount19,
                'tax_amount_8' => $taxAmount8,
                'tax_amount_5' => $taxAmount5,
                'tax_amount_4' => $taxAmount4,
                'tax_amount' => $totalTaxAmount
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir, los campos se mantendrán
    }
};
