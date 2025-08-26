<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\QuotationItemSelection;
use Illuminate\Support\Facades\Log;

class FixOrderPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-order-prices {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually fix the unit and total prices for a specific purchase order based on original quotation prices.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('orderId');
        $this->info("Buscando orden de compra con ID: {$orderId}");

        $purchaseOrder = PurchaseOrder::with('purchaseRequest.quotationItemSelections.quotation')->find($orderId);

        if (!$purchaseOrder) {
            $this->error("Orden de compra con ID {$orderId} no encontrada.");
            return 1;
        }

        $purchaseRequest = $purchaseOrder->purchaseRequest;

        if (!$purchaseRequest || !$purchaseRequest->quotationItemSelections()->exists()) {
            $this->warn("La orden de compra no tiene una solicitud de compra o selecciones de items asociadas.");
            return 1;
        }

        $this->info("Procesando selecciones para la solicitud de compra ID: {$purchaseRequest->id}");

        $selections = $purchaseRequest->quotationItemSelections;
        $updatedCount = 0;

        foreach ($selections as $selection) {
            $this->line("--- Procesando selección ID: {$selection->id} para item '{$selection->item_description}' ---");

            if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                $prices = $selection->quotation->original_item_prices;
                $realPrice = null;

                // Intentar por índice exacto
                if (isset($prices[$selection->item_index])) {
                    $realPrice = $prices[$selection->item_index];
                    $this->info("Precio encontrado por índice exacto [{$selection->item_index}]: {$realPrice}");
                } 
                // Fallback por posición en el array
                elseif (is_array($prices)) {
                    $values = array_values($prices);
                    if (isset($values[$selection->item_index])) {
                        $realPrice = $values[$selection->item_index];
                        $this->warn("Usando fallback de posición [{$selection->item_index}] para encontrar precio: {$realPrice}");
                    }
                }

                if ($realPrice !== null) {
                    $oldPrice = $selection->unit_price;
                    $newPrice = (float)$realPrice;
                    $newTotal = $newPrice * $selection->quantity;

                    if (abs($oldPrice - $newPrice) > 0.01) {
                        $selection->unit_price = $newPrice;
                        $selection->total_price = $newTotal;
                        $selection->save(); // Guardar el cambio en la BD

                        $this->info("¡PRECIO ACTUALIZADO! De {$oldPrice} a {$newPrice}. Nuevo total: {$newTotal}");
                        Log::info("Precio actualizado para selection_id {$selection->id}", [
                            'new_unit_price' => $newPrice,
                            'new_total_price' => $newTotal
                        ]);
                        $updatedCount++;
                    } else {
                        $this->line("El precio ya era correcto ({$oldPrice}). No se necesita actualización.");
                    }
                } else {
                    $this->error("No se pudo encontrar un precio real para el item con índice {$selection->item_index}.");
                }
            } else {
                $this->error("La selección no tiene cotización o la cotización no tiene precios originales.");
            }
        }

        $this->info("\nProceso completado. Se actualizaron {$updatedCount} precios.");
        return 0;
    }
}
