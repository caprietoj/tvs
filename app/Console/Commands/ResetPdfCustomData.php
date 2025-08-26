<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class ResetPdfCustomData extends Command
{
    protected $signature = 'app:reset-pdf-custom-data {order_id}';
    protected $description = 'Restablece los datos pdf_custom_data de una orden de compra específica';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $this->info("Buscando orden de compra con ID: $orderId");
        
        try {
            // Buscar la orden con las relaciones necesarias
            $order = PurchaseOrder::with([
                'purchaseRequest', 
                'purchaseRequest.quotationItemSelections.quotation',
                'provider'
            ])->findOrFail($orderId);
            
            $this->info("Orden encontrada: {$order->order_number}");
            
            // Restablecer los datos pdf_custom_data
            $order->pdf_custom_data = null;
            
            // Forzar actualización de timestamps
            $order->updated_at = now();
            
            $order->save();
            
            $this->info("Los datos de pdf_custom_data han sido eliminados correctamente.");
            
            // Ahora podemos reconstruir los datos con las selecciones correctas si es necesario
            if ($order->purchaseRequest && $order->purchaseRequest->quotationItemSelections()->exists()) {
                $this->info("Esta es una orden de selección mixta. Obteniendo selecciones específicas para el proveedor {$order->provider->nombre}");
                
                $providerSpecificSelections = $order->purchaseRequest->quotationItemSelections()
                    ->with('quotation')
                    ->whereHas('quotation', function($query) use ($order) {
                        $query->where('provider_name', $order->provider->nombre);
                    })
                    ->get();
                
                $this->info("Se encontraron {$providerSpecificSelections->count()} selecciones específicas para este proveedor.");
                
                // Crear un nuevo array para los items con precios correctos
                $customItems = [];
                
                foreach ($providerSpecificSelections as $selection) {
                    $realPrice = null;
                    
                    if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                        $prices = $selection->quotation->original_item_prices;
                        
                        // Intentar obtener el precio por índice exacto
                        if (isset($prices[$selection->item_index])) {
                            $realPrice = $prices[$selection->item_index];
                            $this->info("Precio encontrado por índice exacto [{$selection->item_index}]: $realPrice");
                        } elseif (is_array($prices)) {
                            // Fallback: usar array_values
                            $values = array_values($prices);
                            if (isset($values[$selection->item_index])) {
                                $realPrice = $values[$selection->item_index];
                                $this->info("Precio encontrado por array_values [{$selection->item_index}]: $realPrice");
                            }
                        }
                    }
                    
                    if ($realPrice !== null) {
                        $customItems[] = [
                            'description' => $selection->item_description,
                            'quantity' => $selection->quantity,
                            'unit_price' => $realPrice,
                            'total' => $realPrice * $selection->quantity
                        ];
                        
                        $this->info("Item agregado: {$selection->item_description} - Cantidad: {$selection->quantity} - Precio: $realPrice - Total: " . ($realPrice * $selection->quantity));
                    } else {
                        $this->warn("⚠️ No se encontró precio para el item {$selection->item_description}");
                    }
                }
                
                // Calcular subtotal, IVA y total
                $totalBruto = array_sum(array_column($customItems, 'total'));
                
                // Calcular el subtotal sin IVA (Usando 19% como tasa estándar)
                $ivaRate = 19; // 19% es el IVA estándar en Colombia
                $subtotalSinIva = round($totalBruto / (1 + ($ivaRate/100)), 0);
                $ivaAmount = $totalBruto - $subtotalSinIva;
                
                $this->info("Total bruto: $totalBruto");
                $this->info("Subtotal sin IVA (19%): $subtotalSinIva");
                $this->info("IVA calculado: $ivaAmount");
                
                // Crear nuevos datos personalizados para el PDF con los precios correctos
                $customData = [
                    'provider_name' => $order->provider->nombre,
                    'provider_nit' => $order->provider->nit,
                    'provider_email' => $order->provider->email,
                    'provider_phone' => $order->provider->telefono,
                    'provider_address' => $order->provider->direccion,
                    'provider_city' => $order->provider->ciudad,
                    'delivery_address' => 'COLEGIO VICTORIA CALLE 32 F SUR 17G 26',
                    'payment_method' => $order->payment_method ?? 'Contado',
                    'budget' => $order->purchaseRequest->budget ?? '',
                    'iva_rate' => $ivaRate.'%',
                    'iva_amount' => $ivaAmount,
                    'ipoconsumo_rate' => '0%',
                    'ipoconsumo_amount' => 0,
                    'subtotal' => $subtotalSinIva,
                    'total' => $totalBruto,
                    'items' => $customItems,
                    'edited_by' => auth()->id() ?? 1,
                    'edited_at' => now()->toISOString(),
                ];
                
                // Actualizar la orden con los nuevos datos personalizados
                $order->pdf_custom_data = json_encode($customData);
                $order->subtotal = $subtotalSinIva;
                $order->total_amount = $totalBruto;
                $order->iva_amount = $ivaAmount;
                $order->includes_iva = true;
                $order->updated_at = now();
                
                $order->save();
                
                $this->info("Se han reconstruido los datos personalizados del PDF con los precios correctos.");
            } else {
                $this->info("Esta no es una orden de selección mixta. No se requieren acciones adicionales.");
            }
            
            $this->info("Proceso completado con éxito para la orden {$order->order_number}.");
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Orden de compra con ID $orderId no encontrada.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Error en ResetPdfCustomData: " . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
