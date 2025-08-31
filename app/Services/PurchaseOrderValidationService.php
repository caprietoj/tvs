<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class PurchaseOrderValidationService
{
    /**
     * Valida y repara una orden de compra
     */
    public function validateAndRepair(PurchaseOrder $order, bool $dryRun = false): array
    {
        $fixes = [];
        $needsRepair = false;
        
        // Obtener datos actuales
        $customData = $this->getCustomData($order);
        
        // 1. Validar y reparar items
        $itemsResult = $this->validateAndRepairItems($customData);
        if ($itemsResult['repaired']) {
            $fixes[] = $itemsResult['fix'];
            $customData = $itemsResult['data'];
            $needsRepair = true;
        }
        
        // 2. Validar y reparar cálculos de IVA
        $ivaResult = $this->validateAndRepairIva($customData);
        if ($ivaResult['repaired']) {
            $fixes[] = $ivaResult['fix'];
            $customData = $ivaResult['data'];
            $needsRepair = true;
        }
        
        // 3. Validar y reparar totales
        $totalsResult = $this->validateAndRepairTotals($customData);
        if ($totalsResult['repaired']) {
            $fixes[] = $totalsResult['fix'];
            $customData = $totalsResult['data'];
            $needsRepair = true;
        }
        
        // 4. Aplicar cambios si no es dry-run
        if ($needsRepair && !$dryRun) {
            $order->pdf_custom_data = json_encode($customData);
            $order->save();
            
            // Regenerar PDF automáticamente
            try {
                $pdfService = new PurchaseOrderPdfService();
                $pdfService->generatePdf($order);
                $fixes[] = 'PDF regenerado';
            } catch (\Exception $e) {
                Log::error('Error regenerando PDF', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'repaired' => $needsRepair,
            'fixes' => $fixes,
            'data' => $customData
        ];
    }
    
    /**
     * Obtiene y valida los datos personalizados de la orden
     */
    private function getCustomData(PurchaseOrder $order): array
    {
        if (empty($order->pdf_custom_data)) {
            return [];
        }
        
        $data = json_decode($order->pdf_custom_data, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Valida y repara los items de la orden
     */
    private function validateAndRepairItems(array $customData): array
    {
        $repaired = false;
        $fix = '';
        
        // Verificar si existen items
        if (!isset($customData['items']) || empty($customData['items'])) {
            // Intentar reconstruir items desde datos disponibles
            if (isset($customData['subtotal']) && $customData['subtotal'] > 0) {
                $customData['items'] = [[
                    'description' => 'Producto/Servicio',
                    'quantity' => '1',
                    'unit_price' => $customData['subtotal'],
                    'total' => $customData['subtotal']
                ]];
                $repaired = true;
                $fix = 'Items reconstruidos desde subtotal';
            }
        } else {
            // Validar formato de items existentes
            $itemsFixed = false;
            foreach ($customData['items'] as $index => $item) {
                // Detectar y corregir valores multiplicados por 100
                if (isset($item['unit_price'])) {
                    $originalPrice = $item['unit_price'];
                    $correctedPrice = $this->detectAndCorrectMultipliedValue($originalPrice);
                    
                    if (abs($correctedPrice - $originalPrice) > 0.01) {
                        $customData['items'][$index]['unit_price'] = $correctedPrice;
                        $itemsFixed = true;
                    }
                }
                
                if (isset($item['total'])) {
                    $originalTotal = $item['total'];
                    $correctedTotal = $this->detectAndCorrectMultipliedValue($originalTotal);
                    
                    if (abs($correctedTotal - $originalTotal) > 0.01) {
                        $customData['items'][$index]['total'] = $correctedTotal;
                        $itemsFixed = true;
                    }
                }
                
                // Convertir precios con formato string a números
                if (isset($item['unit_price']) && is_string($item['unit_price'])) {
                    $cleanPrice = $this->cleanNumericValue($item['unit_price']);
                    if ($cleanPrice !== $item['unit_price']) {
                        $customData['items'][$index]['unit_price'] = $cleanPrice;
                        $itemsFixed = true;
                    }
                }
                
                if (isset($item['total']) && is_string($item['total'])) {
                    $cleanTotal = $this->cleanNumericValue($item['total']);
                    if ($cleanTotal !== $item['total']) {
                        $customData['items'][$index]['total'] = $cleanTotal;
                        $itemsFixed = true;
                    }
                }
                
                // Recalcular total basado en cantidad y precio unitario corregidos
                $quantity = floatval($item['quantity'] ?? 1);
                $unitPrice = floatval($customData['items'][$index]['unit_price'] ?? 0);
                $expectedTotal = $quantity * $unitPrice;
                
                if (abs($expectedTotal - floatval($customData['items'][$index]['total'] ?? 0)) > 0.01) {
                    $customData['items'][$index]['total'] = $expectedTotal;
                    $itemsFixed = true;
                }
                
                // Validar cantidad
                if (!isset($item['quantity']) || empty($item['quantity'])) {
                    $customData['items'][$index]['quantity'] = '1';
                    $itemsFixed = true;
                }
                
                // Validar descripción
                if (!isset($item['description']) || empty($item['description'])) {
                    $customData['items'][$index]['description'] = 'Producto/Servicio';
                    $itemsFixed = true;
                }
            }
            
            if ($itemsFixed) {
                $repaired = true;
                $fix = 'Items corregidos (valores multiplicados por 100 detectados y reparados)';
            }
        }
        
        return [
            'repaired' => $repaired,
            'fix' => $fix,
            'data' => $customData
        ];
    }
    
    /**
     * Valida y repara los cálculos de IVA
     */
    private function validateAndRepairIva(array $customData): array
    {
        $repaired = false;
        $fix = '';
        
        $subtotal = $this->cleanNumericValue($customData['subtotal'] ?? 0);
        $ivaRate = $this->cleanNumericValue($customData['iva_rate'] ?? 0);
        $ivaAmount = $this->cleanNumericValue($customData['iva_amount'] ?? 0);
        
        // Calcular IVA correcto
        $expectedIvaAmount = ($subtotal * $ivaRate) / 100;
        
        // Verificar si el IVA está mal calculado (tolerancia de $1)
        if (abs($ivaAmount - $expectedIvaAmount) > 1) {
            $customData['iva_amount'] = round($expectedIvaAmount);
            $repaired = true;
            $fix = sprintf('IVA corregido: $%s → $%s', 
                number_format($ivaAmount, 0, ',', '.'), 
                number_format($expectedIvaAmount, 0, ',', '.')
            );
        }
        
        return [
            'repaired' => $repaired,
            'fix' => $fix,
            'data' => $customData
        ];
    }
    
    /**
     * Valida y repara los totales
     */
    private function validateAndRepairTotals(array $customData): array
    {
        $repaired = false;
        $fix = '';
        
        // Detectar y corregir valores multiplicados por 100
        $subtotal = $this->detectAndCorrectMultipliedValue($customData['subtotal'] ?? 0);
        $ivaAmount = $this->detectAndCorrectMultipliedValue($customData['iva_amount'] ?? 0);
        $ipoconsumoAmount = $this->detectAndCorrectMultipliedValue($customData['ipoconsumo_amount'] ?? 0);
        $currentTotal = $this->detectAndCorrectMultipliedValue($customData['total'] ?? 0);
        
        // Actualizar valores si fueron corregidos
        if (abs($subtotal - floatval($customData['subtotal'] ?? 0)) > 0.01) {
            $customData['subtotal'] = $subtotal;
            $repaired = true;
        }
        
        if (abs($ivaAmount - floatval($customData['iva_amount'] ?? 0)) > 0.01) {
            $customData['iva_amount'] = $ivaAmount;
            $repaired = true;
        }
        
        if (abs($currentTotal - floatval($customData['total'] ?? 0)) > 0.01) {
            $customData['total'] = $currentTotal;
            $repaired = true;
        }
        
        // Calcular total correcto
        $expectedTotal = $subtotal + $ivaAmount + $ipoconsumoAmount;
        
        // Recalcular subtotal basado en items si existen
        if (isset($customData['items']) && !empty($customData['items'])) {
            $calculatedSubtotal = 0;
            foreach ($customData['items'] as $item) {
                $calculatedSubtotal += floatval($item['total'] ?? 0);
            }
            
            // Si el subtotal calculado desde items difiere significativamente, corregirlo
            if (abs($calculatedSubtotal - $subtotal) > 0.01) {
                $customData['subtotal'] = $calculatedSubtotal;
                $subtotal = $calculatedSubtotal;
                $repaired = true;
                
                // Recalcular IVA basado en el nuevo subtotal
                $ivaRate = floatval($customData['iva_rate'] ?? 19);
                $newIvaAmount = $subtotal * ($ivaRate / 100);
                if (abs($newIvaAmount - $ivaAmount) > 0.01) {
                    $customData['iva_amount'] = $newIvaAmount;
                    $ivaAmount = $newIvaAmount;
                }
                
                // Recalcular total
                $expectedTotal = $subtotal + $ivaAmount + $ipoconsumoAmount;
            }
        }
        
        // Verificar si el total está mal calculado (tolerancia de $1)
        if (abs($currentTotal - $expectedTotal) > 1) {
            $customData['total'] = round($expectedTotal);
            $repaired = true;
        }
        
        // Crear mensaje de reparación
        if ($repaired) {
            if (empty($fix)) {
                $fix = 'Totales recalculados y corregidos (valores multiplicados por 100 detectados)';
            }
        }
        
        // Validar que el campo total del modelo coincida
        // (Este campo se usa en la base de datos)
        if (isset($customData['total'])) {
            $customData['calculated_total'] = $customData['total'];
        }
        
        return [
            'repaired' => $repaired,
            'fix' => $fix,
            'data' => $customData
        ];
    }
    
    /**
     * Limpia y convierte valores numéricos desde strings formateados
     */
    private function cleanNumericValue($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        if (is_string($value)) {
            // Si ya es un número sin formato especial, solo convertir
            if (preg_match('/^\d+$/', $value)) {
                return (float) $value;
            }
            
            // Manejar formato colombiano: 164.891.304 o 164,891,304
            if (preg_match('/^[\d,\.]+$/', $value)) {
                // Si contiene tanto puntos como comas, asumir formato: 1.234.567,89
                if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
                    $value = str_replace('.', '', $value); // quitar puntos de miles
                    $value = str_replace(',', '.', $value); // cambiar coma decimal a punto
                }
                // Si solo contiene puntos múltiples, asumir miles: 164.891.304
                elseif (substr_count($value, '.') > 1) {
                    $value = str_replace('.', '', $value); // quitar todos los puntos de miles
                }
                // Si solo contiene comas múltiples, asumir miles: 164,891,304
                elseif (substr_count($value, ',') > 1) {
                    $value = str_replace(',', '', $value); // quitar todas las comas de miles
                }
                // Si contiene una sola coma al final, asumir decimal: 123,45
                elseif (preg_match('/,\d{1,2}$/', $value)) {
                    $value = str_replace(',', '.', $value);
                }
            }
            
            return (float) $value;
        }
        
        return 0.0;
    }
    
    /**
     * Detecta y corrige valores que fueron multiplicados por 100 incorrectamente
     */
    private function detectAndCorrectMultipliedValue($value): float
    {
        $numericValue = floatval($value);
        
        // Si el valor es muy grande (más de 10 millones), probablemente está multiplicado por 100
        if ($numericValue > 10000000) {
            $divided = $numericValue / 100;
            
            // Verificar si el valor dividido tiene sentido para un precio colombiano
            // (entre 1000 y 10,000,000 pesos)
            if ($divided >= 1000 && $divided <= 10000000) {
                return $divided;
            }
        }
        
        // Si el valor parece correcto, devolverlo sin cambios
        return $numericValue;
    }
    
    /**
     * Valida una orden después de ser creada o editada
     */
    public function validateOnSave(PurchaseOrder $order): void
    {
        // Validación automática en tiempo real
        $result = $this->validateAndRepair($order, false);
        
        if ($result['repaired']) {
            Log::info('Orden auto-reparada al guardar', [
                'order_number' => $order->order_number,
                'fixes' => $result['fixes']
            ]);
        }
    }
}
