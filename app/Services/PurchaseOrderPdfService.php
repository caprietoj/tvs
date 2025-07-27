<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class PurchaseOrderPdfService
{
    public function __construct()
    {
        // Constructor simplificado
    }
    
    /**
     * Genera el PDF de una orden de compra
     */
    public function generatePdf(PurchaseOrder $order, $providerSelections = null)
    {
        try {
            // Cargar la solicitud y sus relaciones
            $order->load(['purchaseRequest.user', 'purchaseRequest.selectedQuotation', 'purchaseRequest.approver', 'provider']);
            
            // Verificar si hay datos personalizados del PDF
            $hasCustomData = !empty($order->pdf_custom_data);
            
            if ($hasCustomData) {
                // Usar datos personalizados para generar el PDF
                return $this->generateCustomPdf($order);
            }
            
            // Verificar si es un servicio sin cotización
            if ($order->purchaseRequest->isNoQuotationService()) {
                // Calcular variables necesarias para el template
                $subtotalCalculado = $order->subtotal;
                $totalFinal = $order->total_amount;
                
                // Procesar impuestos aplicados desde additional_items
                $appliedTaxes = isset($order->additional_items['applied_taxes_detail']) 
                    ? $order->additional_items['applied_taxes_detail'] 
                    : [];
                
                $iva19Amount = 0;
                $iva5Amount = 0;
                $ipoconsumo8Amount = 0;
                $ipoconsumo4Amount = 0;
                $hasDetailedTaxes = false;
                
                // Si hay impuestos detallados, usarlos
                if (!empty($appliedTaxes)) {
                    foreach ($appliedTaxes as $tax) {
                        $hasDetailedTaxes = true;
                        switch ($tax['type']) {
                            case 'IVA':
                                if ($tax['rate'] == 19) {
                                    $iva19Amount = $tax['amount'];
                                } elseif ($tax['rate'] == 5) {
                                    $iva5Amount = $tax['amount'];
                                }
                                break;
                            case 'IMPUESTO_CONSUMO':
                                if ($tax['rate'] == 8) {
                                    $ipoconsumo8Amount = $tax['amount'];
                                } elseif ($tax['rate'] == 4) {
                                    $ipoconsumo4Amount = $tax['amount'];
                                }
                                break;
                        }
                    }
                }
                
                // RESPALDO: Si no hay impuestos detallados, usar el IVA tradicional de la BD
                if (!$hasDetailedTaxes && $order->includes_iva && $order->iva_amount > 0) {
                    $iva19Amount = $order->iva_amount;
                }
                
                // Usar plantilla para servicios sin cotización
                $view = view('purchase-orders.pdf-template-service', [
                    'order' => $order,
                    'purchaseRequest' => $order->purchaseRequest,
                    'includesIva' => $order->includes_iva,
                    'subtotal' => $order->subtotal,
                    'ivaAmount' => $order->iva_amount,
                    'totalFinal' => $totalFinal,
                    'subtotalCalculado' => $subtotalCalculado,
                    'iva19Amount' => $iva19Amount,
                    'iva5Amount' => $iva5Amount,
                    'ipoconsumo8Amount' => $ipoconsumo8Amount,
                    'ipoconsumo4Amount' => $ipoconsumo4Amount,
                    'hasDetailedTaxes' => $hasDetailedTaxes,
                ]);
            } else {
                // Verificar si tenemos selecciones específicas del proveedor (selección mixta)
                if ($providerSelections !== null) {
                    // Usar selecciones específicas del proveedor para selección mixta
                    $view = view('purchase-orders.pdf-template-mixed', [
                        'order' => $order,
                        'purchaseRequest' => $order->purchaseRequest,
                        'mixedSelections' => $providerSelections,
                        'includesIva' => $order->includes_iva,
                        'subtotal' => $order->subtotal,
                        'ivaAmount' => $order->iva_amount,
                    ]);
                } else {
                    // Verificar si es una orden con selección mixta (caso legacy)
                    $hasMixedSelection = $order->purchaseRequest->quotationItemSelections()->exists();
                    
                    if ($hasMixedSelection) {
                        // Cargar todas las selecciones mixtas
                        $mixedSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
                        
                        // Usar plantilla para selección mixta
                        $view = view('purchase-orders.pdf-template-mixed', [
                            'order' => $order,
                            'purchaseRequest' => $order->purchaseRequest,
                            'mixedSelections' => $mixedSelections,
                            'includesIva' => $order->includes_iva,
                            'subtotal' => $order->subtotal,
                            'ivaAmount' => $order->iva_amount,
                        ]);
                    } else {
                        // Obtener datos de la cotización seleccionada para orden tradicional
                        $quotation = $order->purchaseRequest->selectedQuotation;
                        
                        // Usar plantilla tradicional
                        $view = view('purchase-orders.pdf-template-new', [
                            'order' => $order,
                            'purchaseRequest' => $order->purchaseRequest,
                            'quotation' => $quotation,
                            'items' => $order->purchaseRequest->purchase_items ?? [],
                            'additionalItems' => $order->additional_items ?? [],
                            'includesIva' => $order->includes_iva,
                            'subtotal' => $order->subtotal,
                            'ivaAmount' => $order->iva_amount,
                            'iva_amount' => $order->iva_amount,
                            'tax_consumption' => 0, // Si no se usa impuesto al consumo
                            'discount' => 0, // Si no se manejan descuentos
                        ]);
                    }
                }
            }
            
            // Generar PDF
            $pdf = \PDF::loadHTML($view->render());
            $pdf->setPaper('letter', 'portrait');
            
            // Si ya existe un PDF, eliminarlo
            if ($order->file_path && $order->file_path !== 'pending_generation' && Storage::disk('public')->exists($order->file_path)) {
                Storage::disk('public')->delete($order->file_path);
                Log::info('PDF anterior eliminado para orden de compra #' . $order->id);
            }
            
            // Definir nombre de archivo
            $fileName = 'order_' . $order->order_number . '_' . time() . '.pdf';
            
            // Guardar en storage público
            $path = 'purchase_orders/' . $fileName;
            Storage::disk('public')->put($path, $pdf->output());
            
            Log::info('PDF generado exitosamente para orden de compra #' . $order->id . ' en ruta: ' . $path);
            
            return $path;
        } catch (Exception $e) {
            Log::error('Error al generar PDF para orden de compra #' . $order->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera y devuelve directamente el objeto PDF (para pruebas y adjuntos)
     */
    public function createPdf(PurchaseOrder $order, $providerSelections = null)
    {
        try {
            // Cargar la solicitud y sus relaciones
            $order->load(['purchaseRequest.user', 'purchaseRequest.selectedQuotation', 'purchaseRequest.approver', 'provider']);
            
            // Verificar si es un servicio sin cotización
            if ($order->purchaseRequest->isNoQuotationService()) {
                // Calcular variables necesarias para el template
                $subtotalCalculado = $order->subtotal;
                $totalFinal = $order->total_amount;
                
                // Procesar impuestos aplicados desde additional_items
                $appliedTaxes = isset($order->additional_items['applied_taxes_detail']) 
                    ? $order->additional_items['applied_taxes_detail'] 
                    : [];
                
                $iva19Amount = 0;
                $iva5Amount = 0;
                $ipoconsumo8Amount = 0;
                $ipoconsumo4Amount = 0;
                $hasDetailedTaxes = false;
                
                // Si hay impuestos detallados, usarlos
                if (!empty($appliedTaxes)) {
                    foreach ($appliedTaxes as $tax) {
                        $hasDetailedTaxes = true;
                        switch ($tax['type']) {
                            case 'IVA':
                                if ($tax['rate'] == 19) {
                                    $iva19Amount = $tax['amount'];
                                } elseif ($tax['rate'] == 5) {
                                    $iva5Amount = $tax['amount'];
                                }
                                break;
                            case 'IMPUESTO_CONSUMO':
                                if ($tax['rate'] == 8) {
                                    $ipoconsumo8Amount = $tax['amount'];
                                } elseif ($tax['rate'] == 4) {
                                    $ipoconsumo4Amount = $tax['amount'];
                                }
                                break;
                        }
                    }
                }
                
                // RESPALDO: Si no hay impuestos detallados, usar el IVA tradicional de la BD
                if (!$hasDetailedTaxes && $order->includes_iva && $order->iva_amount > 0) {
                    $iva19Amount = $order->iva_amount;
                }
                
                // Usar plantilla para servicios sin cotización
                $view = view('purchase-orders.pdf-template-service', [
                    'order' => $order,
                    'purchaseRequest' => $order->purchaseRequest,
                    'includesIva' => $order->includes_iva,
                    'subtotal' => $order->subtotal,
                    'ivaAmount' => $order->iva_amount,
                    'totalFinal' => $totalFinal,
                    'subtotalCalculado' => $subtotalCalculado,
                    'iva19Amount' => $iva19Amount,
                    'iva5Amount' => $iva5Amount,
                    'ipoconsumo8Amount' => $ipoconsumo8Amount,
                    'ipoconsumo4Amount' => $ipoconsumo4Amount,
                    'hasDetailedTaxes' => $hasDetailedTaxes,
                ]);
            } else {
                // Verificar si tenemos selecciones específicas del proveedor (selección mixta)
                if ($providerSelections !== null) {
                    // Usar selecciones específicas del proveedor para selección mixta
                    $view = view('purchase-orders.pdf-template-mixed', [
                        'order' => $order,
                        'purchaseRequest' => $order->purchaseRequest,
                        'mixedSelections' => $providerSelections,
                        'includesIva' => $order->includes_iva,
                        'subtotal' => $order->subtotal,
                        'ivaAmount' => $order->iva_amount,
                    ]);
                } else {
                    // Verificar si es una orden con selección mixta (caso legacy)
                    $hasMixedSelection = $order->purchaseRequest->quotationItemSelections()->exists();
                    
                    if ($hasMixedSelection) {
                        // Cargar todas las selecciones mixtas
                        $mixedSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
                        
                        // Usar plantilla para selección mixta
                        $view = view('purchase-orders.pdf-template-mixed', [
                            'order' => $order,
                            'purchaseRequest' => $order->purchaseRequest,
                            'mixedSelections' => $mixedSelections,
                            'includesIva' => $order->includes_iva,
                            'subtotal' => $order->subtotal,
                            'ivaAmount' => $order->iva_amount,
                        ]);
                    } else {
                        // Obtener datos de la cotización seleccionada para orden tradicional
                        $quotation = $order->purchaseRequest->selectedQuotation;
                        
                        // Usar plantilla tradicional
                        $view = view('purchase-orders.pdf-template-new', [
                            'order' => $order,
                            'purchaseRequest' => $order->purchaseRequest,
                            'quotation' => $quotation,
                            'items' => $order->purchaseRequest->purchase_items ?? [],
                            'additionalItems' => $order->additional_items ?? [],
                            'includesIva' => $order->includes_iva,
                            'subtotal' => $order->subtotal,
                            'ivaAmount' => $order->iva_amount,
                            'iva_amount' => $order->iva_amount,
                            'tax_consumption' => 0,
                            'discount' => 0,
                        ]);
                    }
                }
            }
            
            // Generar PDF
            $pdf = \PDF::loadHTML($view->render());
            $pdf->setPaper('letter', 'portrait');
            
            return $pdf;
        } catch (Exception $e) {
            Log::error('Error al crear PDF para orden de compra #' . $order->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getPdfPath(PurchaseOrder $order): string
    {
        return $order->file_path;
    }
    
    /**
     * Genera PDF usando datos personalizados pero manteniendo el template original
     */
    private function generateCustomPdf(PurchaseOrder $order)
    {
        try {
            $customData = json_decode($order->pdf_custom_data, true);
            
            // Crear un pseudo-proveedor con los datos personalizados
            $customProvider = (object) [
                'nombre' => $customData['provider_name'] ?? $order->provider->nombre ?? '',
                'nit' => $customData['provider_nit'] ?? $order->provider->nit ?? '',
                'email' => $customData['provider_email'] ?? $order->provider->email ?? '',
                'telefono' => $customData['provider_phone'] ?? $order->provider->telefono ?? '',
                'direccion' => $customData['provider_address'] ?? $order->provider->direccion ?? '',
            ];
            
            // Temporalmente reemplazar el proveedor con datos personalizados
            $originalProvider = $order->provider;
            $order->setRelation('provider', $customProvider);
            
            // Actualizar totales si están en los datos personalizados
            if (isset($customData['subtotal'])) {
                $order->subtotal = $customData['subtotal'];
            }
            if (isset($customData['iva_amount'])) {
                $order->iva_amount = $customData['iva_amount'];
                $order->tax_amount = $customData['iva_amount'];
                $order->includes_iva = $customData['iva_amount'] > 0;
            }
            if (isset($customData['total'])) {
                $order->total_amount = $customData['total'];
            }
            
            // Usar la lógica original para determinar el template correcto
            $view = null;
            
            // Verificar si es un servicio sin cotización
            if ($order->purchaseRequest->isNoQuotationService()) {
                // Usar template de servicio con datos personalizados
                $view = view('purchase-orders.pdf-template-service', [
                    'order' => $order,
                    'purchaseRequest' => $order->purchaseRequest,
                    'customItems' => $customData['items'] ?? [],
                    'includesIva' => $order->includes_iva,
                    'subtotal' => $order->subtotal,
                    'ivaAmount' => $order->iva_amount,
                ]);
            } else {
                // Para órdenes normales, usar template tradicional
                $quotation = $order->purchaseRequest->selectedQuotation;
                $view = view('purchase-orders.pdf-template-new', [
                    'order' => $order,
                    'purchaseRequest' => $order->purchaseRequest,
                    'quotation' => $quotation,
                    'items' => $customData['items'] ?? $order->purchaseRequest->purchase_items ?? [],
                    'additionalItems' => $order->additional_items ?? [],
                    'includesIva' => $order->includes_iva,
                    'subtotal' => $order->subtotal,
                    'ivaAmount' => $order->iva_amount,
                    'iva_amount' => $order->iva_amount,
                    'tax_consumption' => 0,
                    'discount' => 0,
                ]);
            }
            
            // Restaurar el proveedor original
            $order->setRelation('provider', $originalProvider);
            
            // Generar PDF
            $pdf = \PDF::loadHTML($view->render());
            $pdf->setPaper('letter', 'portrait');
            
            // Guardar el PDF
            $filename = 'order_' . $order->order_number . '_' . time() . '.pdf';
            $pdfPath = 'purchase_orders/' . $filename;
            
            Storage::disk('public')->put($pdfPath, $pdf->output());
            
            return $pdfPath;
            
        } catch (Exception $e) {
            Log::error('Error al generar PDF personalizado para orden #' . $order->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
