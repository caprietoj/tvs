<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class ManualFixOrderPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fix-manual {order_id} {item_index?} {new_price?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige manualmente el precio de un item específico en una orden';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        $itemIndex = $this->argument('item_index');
        $newPrice = $this->argument('new_price');
        
        $order = PurchaseOrder::find($orderId);
        
        if (!$order) {
            $this->error("No se encontró la orden con ID: {$orderId}");
            return 1;
        }
        
        $this->info("CORRECCIÓN MANUAL DE PRECIOS PARA ORDEN: {$order->order_number} (ID: {$order->id})");
        
        // Obtener datos personalizados
        $customData = json_decode($order->pdf_custom_data, true);
        if (empty($customData) || !isset($customData['items'])) {
            $this->warn("La orden no tiene datos personalizados para el PDF o no tiene items");
            return 1;
        }
        
        $items = $customData['items'];
        $this->info("Total de items en PDF: " . count($items));
        
        // Mostrar items actuales
        $this->table(
            ['#', 'Descripción', 'Cantidad', 'Precio Unitario', 'Total'],
            $this->formatItemsForTable($items)
        );
        
        // Si no se proporcionaron índice y precio, permitir ingreso interactivo
        if ($itemIndex === null) {
            $itemIndex = $this->ask('Ingrese el índice del item a modificar (0 para el primero):');
        }
        
        if ($newPrice === null) {
            $newPrice = $this->ask('Ingrese el nuevo precio unitario:');
        }
        
        // Validar índice
        if (!is_numeric($itemIndex) || $itemIndex < 0 || $itemIndex >= count($items)) {
            $this->error("Índice de item inválido. Debe estar entre 0 y " . (count($items) - 1));
            return 1;
        }
        
        // Validar precio
        if (!is_numeric($newPrice) || $newPrice <= 0) {
            $this->error("Precio inválido. Debe ser un número positivo.");
            return 1;
        }
        
        // Hacer backup de los datos antes de modificar
        $backupData = $order->pdf_custom_data;
        $oldPrice = $items[$itemIndex]['unit_price'];
        $quantity = floatval($items[$itemIndex]['quantity']);
        
        // Actualizar precio
        $items[$itemIndex]['unit_price'] = floatval($newPrice);
        $items[$itemIndex]['total'] = $quantity * floatval($newPrice);
        
        // Recalcular subtotal
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['total'] ?? 0;
        }
        
        // Recalcular impuestos
        $ivaRate = intval(str_replace('%', '', $customData['iva_rate'] ?? '0'));
        $ipoconsumoRate = intval(str_replace('%', '', $customData['ipoconsumo_rate'] ?? '0'));
        
        $ivaAmount = ($ivaRate > 0) ? round(($subtotal * $ivaRate) / 100) : 0;
        $ipoconsumoAmount = ($ipoconsumoRate > 0) ? round(($subtotal * $ipoconsumoRate) / 100) : 0;
        $totalAmount = $subtotal + $ivaAmount + $ipoconsumoAmount;
        
        // Actualizar datos personalizados
        $customData['items'] = $items;
        $customData['subtotal'] = $subtotal;
        $customData['iva_amount'] = $ivaAmount;
        $customData['ipoconsumo_amount'] = $ipoconsumoAmount;
        $customData['total'] = $totalAmount;
        
        // Confirmar cambios
        $this->info("Cambios propuestos:");
        $this->line("- Item: " . $items[$itemIndex]['description']);
        $this->line("- Precio anterior: {$oldPrice}");
        $this->line("- Precio nuevo: {$newPrice}");
        $this->line("- Subtotal nuevo: {$subtotal}");
        $this->line("- Total nuevo: {$totalAmount}");
        
        if ($this->confirm('¿Desea aplicar estos cambios?', true)) {
            // Actualizar orden
            $order->pdf_custom_data = json_encode($customData);
            $order->subtotal = $subtotal;
            $order->iva_amount = $ivaAmount;
            $order->tax_amount = $ivaAmount + $ipoconsumoAmount;
            $order->total_amount = $totalAmount;
            $order->save();
            
            // Regenerar PDF
            try {
                $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($order);
                $order->file_path = $pdfPath;
                $order->save();
                
                $this->info("PDF regenerado exitosamente: {$pdfPath}");
            } catch (\Exception $e) {
                $this->error("Error al regenerar el PDF: " . $e->getMessage());
                Log::error("Error en ManualFixOrderPrices al regenerar PDF: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'exception' => $e
                ]);
            }
            
            $this->info("Cambios aplicados exitosamente.");
        } else {
            $this->info("Operación cancelada. No se realizaron cambios.");
        }
        
        return 0;
    }
    
    /**
     * Format items for display in table
     */
    private function formatItemsForTable($items)
    {
        $tableRows = [];
        foreach ($items as $index => $item) {
            $tableRows[] = [
                'index' => $index,
                'description' => $item['description'] ?? 'Sin descripción',
                'quantity' => $item['quantity'] ?? '0',
                'unit_price' => $item['unit_price'] ?? '0',
                'total' => $item['total'] ?? '0'
            ];
        }
        return $tableRows;
    }
}
