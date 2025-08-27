<?php

/**
 * Comando para corregir los precios de órdenes de compra
 * 
 * Este comando analiza cada orden de compra y asegura que los precios
 * almacenados en pdf_custom_data sean los correctos de la cotización original.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Support\Facades\Log;

class FixOrderPricesV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fix-prices-v2 {order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige los precios de órdenes de compra asegurando que se usen los precios originales';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        if ($orderId) {
            $orders = PurchaseOrder::where('id', $orderId)->get();
            $this->info("Procesando orden específica ID: {$orderId}");
        } else {
            $orders = PurchaseOrder::whereNotNull('pdf_custom_data')->get();
            $this->info("Procesando todas las órdenes con datos personalizados: " . $orders->count());
        }

        $fixed = 0;
        $errors = 0;

        foreach ($orders as $order) {
            try {
                $this->info("Procesando orden #{$order->order_number} (ID: {$order->id})");
                
                // Cargar relaciones necesarias
                $order->load(['purchaseRequest.selectedQuotation', 'purchaseRequest.quotationItemSelections.quotation']);
                $purchaseRequest = $order->purchaseRequest;
                
                if (!$purchaseRequest) {
                    $this->warn("  La orden no tiene solicitud de compra asociada");
                    continue;
                }
                
                // Obtener datos personalizados
                $customData = json_decode($order->pdf_custom_data, true) ?? [];
                
                if (empty($customData)) {
                    $this->warn("  La orden no tiene datos personalizados");
                    continue;
                }
                
                // Verificar si hay items en los datos personalizados
                if (!isset($customData['items']) || empty($customData['items'])) {
                    $this->warn("  No hay items en los datos personalizados");
                    continue;
                }
                
                $items = $customData['items'];
                $originalItems = $items; // Guardar una copia para comparación
                $itemsFixed = 0;
                
                // Determinar si es selección mixta
                $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
                
                // CORRECCIÓN DE PRECIOS
                if ($hasMixedSelection) {
                    // Para órdenes con selección mixta
                    $this->info("  Orden con selección mixta detectada");
                    
                    // Obtener solo las selecciones del proveedor específico
                    $providerSelections = $purchaseRequest->quotationItemSelections()
                        ->whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre);
                        })
                        ->with('quotation')
                        ->get();
                    
                    $this->info("  Encontradas " . $providerSelections->count() . " selecciones para este proveedor");
                    
                    // Mapear selecciones con items
                    foreach ($items as $index => &$item) {
                        $description = $item['description'] ?? '';
                        $matching = null;
                        
                        // Buscar la selección que corresponde a este item
                        foreach ($providerSelections as $selection) {
                            if (trim(strtolower($selection->item_description)) == trim(strtolower($description))) {
                                $matching = $selection;
                                break;
                            }
                        }
                        
                        if ($matching) {
                            // Si encontramos la selección correspondiente, corregir el precio
                            if ($matching->quotation && isset($matching->quotation->original_item_prices[$matching->item_index])) {
                                $oldPrice = $item['unit_price'];
                                $realPrice = $matching->quotation->original_item_prices[$matching->item_index];
                                
                                // Asegurar que sean números y convertir formatos
                                if (is_string($oldPrice)) {
                                    $oldPrice = str_replace([',', '.'], '', $oldPrice);
                                    $oldPrice = floatval($oldPrice) / 1000; // Si está en formato con miles
                                }
                                
                                // Corregir el precio
                                $item['unit_price'] = $realPrice;
                                $item['quantity'] = floatval($item['quantity']);
                                $item['total'] = $item['quantity'] * $realPrice;
                                
                                $itemsFixed++;
                                $this->info("  ✓ Corregido: {$description} - Precio: {$oldPrice} -> {$realPrice}");
                            } else {
                                $this->warn("  ! No se encontró precio original para: {$description}");
                            }
                        } else {
                            $this->warn("  ! No se encontró selección para: {$description}");
                        }
                    }
                } else if ($purchaseRequest->selectedQuotation) {
                    // Para órdenes regulares con cotización seleccionada
                    $selectedQuotation = $purchaseRequest->selectedQuotation;
                    $this->info("  Orden regular con cotización seleccionada ID: {$selectedQuotation->id}");
                    
                    if (isset($selectedQuotation->original_item_prices)) {
                        $originalPrices = $selectedQuotation->original_item_prices;
                        $this->info("  La cotización tiene " . count($originalPrices) . " precios originales");
                        
                        // Aplicar los precios originales a los items
                        foreach ($items as $index => &$item) {
                            if (isset($originalPrices[$index])) {
                                $oldPrice = $item['unit_price'];
                                $realPrice = $originalPrices[$index];
                                
                                // Asegurar que sean números y convertir formatos
                                if (is_string($oldPrice)) {
                                    $oldPrice = str_replace([',', '.'], '', $oldPrice);
                                    $oldPrice = floatval($oldPrice) / 1000; // Si está en formato con miles
                                }
                                
                                // Corregir el precio
                                $item['unit_price'] = $realPrice;
                                $item['quantity'] = floatval($item['quantity']);
                                $item['total'] = $item['quantity'] * $realPrice;
                                
                                $itemsFixed++;
                                $this->info("  ✓ Corregido: {$item['description']} - Precio: {$oldPrice} -> {$realPrice}");
                            } else {
                                $this->warn("  ! No se encontró precio original para el índice {$index}");
                            }
                        }
                    } else {
                        $this->warn("  La cotización no tiene precios originales");
                    }
                } else {
                    $this->warn("  La orden no tiene cotización seleccionada");
                }
                
                // Actualizar subtotal y totales
                if ($itemsFixed > 0) {
                    $subtotal = 0;
                    
                    // Recalcular subtotal
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
                    
                    // Actualizar orden
                    $order->pdf_custom_data = json_encode($customData);
                    $order->subtotal = $subtotal;
                    $order->iva_amount = $ivaAmount;
                    $order->tax_amount = $ivaAmount + $ipoconsumoAmount;
                    $order->total_amount = $totalAmount;
                    $order->save();
                    
                    $this->info("  ✓ Orden actualizada con {$itemsFixed} items corregidos");
                    $this->info("  ✓ Subtotal: " . number_format($subtotal, 0, ',', '.') . ", Total: " . number_format($totalAmount, 0, ',', '.'));
                    $fixed++;
                } else {
                    $this->warn("  No se realizaron correcciones en esta orden");
                }
                
            } catch (\Exception $e) {
                $this->error("Error al procesar orden #{$order->order_number}: " . $e->getMessage());
                Log::error("Error en FixOrderPricesV2: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'exception' => $e
                ]);
                $errors++;
            }
        }
        
        $this->info("Proceso completado: {$fixed} órdenes corregidas, {$errors} errores");
        return 0;
    }
}
