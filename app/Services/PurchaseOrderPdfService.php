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
    public function generatePdf(PurchaseOrder $order)
    {
        try {
            // Cargar la solicitud y sus relaciones
            $order->load(['purchaseRequest', 'purchaseRequest.user', 'provider']);
            
            // Obtener datos de la cotización seleccionada
            $quotation = $order->purchaseRequest->selectedQuotation;
            
            // Configurar vista para el PDF (usando plantilla mejorada)
            $view = view('purchases.orders.pdf_improved', [
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
    public function createPdf(PurchaseOrder $order)
    {
        try {
            // Cargar la solicitud y sus relaciones
            $order->load(['purchaseRequest', 'purchaseRequest.user', 'provider']);
            
            // Obtener datos de la cotización seleccionada
            $quotation = $order->purchaseRequest->selectedQuotation;
            
            // Configurar vista para el PDF (usando plantilla mejorada)
            $view = view('purchases.orders.pdf_improved', [
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
