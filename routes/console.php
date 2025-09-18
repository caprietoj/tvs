<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('check-quotations-with-item-taxes', function () {
    $quotations = \App\Models\Quotation::whereNotNull('original_item_taxes')
        ->orWhere('tax_application_mode', 'per_item')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    $this->info("Cotizaciones con impuestos por ítem encontradas: " . $quotations->count());
    
    foreach ($quotations as $quotation) {
        $this->line("Cotización ID: {$quotation->id}");
        $this->line("  - Proveedor: {$quotation->provider_name}");
        $this->line("  - Modo impuestos: " . ($quotation->tax_application_mode ?? 'N/A'));
        $this->line("  - Total: $" . number_format($quotation->total_amount, 0, ',', '.'));
        
        if ($quotation->original_item_taxes) {
            $itemTaxes = is_array($quotation->original_item_taxes) 
                ? $quotation->original_item_taxes 
                : json_decode($quotation->original_item_taxes, true);
            
            if ($itemTaxes && is_array($itemTaxes)) {
                $this->line("  - Ítems con impuestos: " . count($itemTaxes));
                foreach ($itemTaxes as $index => $taxes) {
                    $appliedTaxes = [];
                    foreach ($taxes as $taxType => $applied) {
                        if ($applied) {
                            $appliedTaxes[] = $taxType;
                        }
                    }
                    if (!empty($appliedTaxes)) {
                        $this->line("    - Ítem {$index}: " . implode(', ', $appliedTaxes));
                    }
                }
            }
        }
        
        // Verificar si hay órdenes asociadas
        $orders = \App\Models\PurchaseOrder::whereHas('purchaseRequest', function($query) use ($quotation) {
            $query->where('selected_quotation_id', $quotation->id);
        })->get();
        
        if ($orders->count() > 0) {
            $this->line("  - Órdenes generadas: " . $orders->count());
            foreach ($orders as $order) {
                $this->line("    - {$order->order_number} (Total: $" . number_format($order->total_amount, 0, ',', '.') . ")");
            }
        }
        
        $this->line("----");
    }
    
})->purpose('Verificar cotizaciones con impuestos por ítem');

Artisan::command('regenerate-pdf {order_number}', function ($orderNumber) {
    $order = \App\Models\PurchaseOrder::where('order_number', $orderNumber)->first();
    
    if (!$order) {
        $this->error("Orden {$orderNumber} no encontrada");
        return;
    }
    
    $this->info("Regenerando PDF para orden: {$order->order_number}");
    
    // Regenerar PDF
    $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
    $pdfPath = $pdfService->generatePdf($order);
    
    // Actualizar la ruta del PDF en la orden
    $order->update(['file_path' => $pdfPath]);
    
    $this->info("PDF regenerado exitosamente: {$pdfPath}");
    
    // Mostrar información de debug
    if ($order->pdf_custom_data) {
        $customData = json_decode($order->pdf_custom_data, true);
        $this->info("Items en custom_data: " . count($customData['items'] ?? []));
        
        $hasItemTaxes = false;
        foreach ($customData['items'] ?? [] as $index => $item) {
            if (isset($item['applied_taxes']) && !empty($item['applied_taxes'])) {
                $hasItemTaxes = true;
                $this->line("  - Ítem " . ($index + 1) . ": {$item['description']} - Impuestos: " . implode(', ', $item['applied_taxes']));
            }
        }
        
        if (!$hasItemTaxes) {
            $this->warn("No se encontraron impuestos específicos por ítem, usando template estándar o simulación");
        }
    }
    
})->purpose('Regenerar PDF de una orden de compra');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
