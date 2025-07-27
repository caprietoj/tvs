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
            if ($order->file_path && $order->file_path !== 'pending_generation' && Storage::exists($order->file_path)) {
                Storage::delete($order->file_path);
                Log::info('PDF anterior eliminado para orden de compra #' . $order->id);
            }
            
            // Definir nombre de archivo
            $fileName = 'order_' . $order->id . '_' . now()->format('YmdHis') . '.pdf';
            
            // Guardar en storage
            $path = 'purchase_orders/' . $fileName;
            \Storage::put($path, $pdf->output());
            
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
}
