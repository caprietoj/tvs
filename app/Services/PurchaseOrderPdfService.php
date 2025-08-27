<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\QuotationItemSelection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PurchaseOrderPdfService
{
    /**
     * Genera PDF de orden de compra usando plantilla unificada
     */
    public function generatePdf(PurchaseOrder $order, $providerSelections = null)
    {
        Log::info('Generando PDF para orden unificada', ['order_id' => $order->id]);

        // Cargar relaciones necesarias
        $order->load(['provider']);

        // Obtener datos personalizados si existen
        $customData = $order->pdf_custom_data ? json_decode($order->pdf_custom_data, true) : [];

        // Preparar datos para la vista
        $data = [
            'order' => $order,
            'customData' => $customData
        ];

        // Detectar si es orden mixta o si se pasaron selecciones
        $hasMixedItems = $providerSelections !== null || 
                        QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre ?? '');
                        })->exists();

        if ($hasMixedItems) {
            // Usar selecciones pasadas como parámetro o buscarlas
            $data['quotationItemSelections'] = $providerSelections ?: $this->getQuotationItemSelections($order);
        }

        // Generar PDF usando plantilla unificada
        $pdf = Pdf::loadView('purchase-orders.pdf-template-custom', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Generar nombre del archivo
        $filename = 'orden_compra_' . $order->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = 'purchase_orders/' . $filename;

        // Guardar archivo
        Storage::disk('public')->put($filepath, $pdf->output());

        Log::info('PDF generado exitosamente', [
            'order_id' => $order->id,
            'filename' => $filename,
            'filepath' => $filepath
        ]);

        // Retornar solo la ruta del archivo para compatibilidad con el controlador
        return $filepath;
    }

    /**
     * Genera PDF y retorna el objeto PDF (para notificaciones)
     */
    public function createPdf(PurchaseOrder $order, $providerSelections = null)
    {
        Log::info('Generando PDF para notificación', ['order_id' => $order->id]);

        // Cargar relaciones necesarias
        $order->load(['provider']);

        // Obtener datos personalizados si existen
        $customData = $order->pdf_custom_data ? json_decode($order->pdf_custom_data, true) : [];

        // Preparar datos para la vista
        $data = [
            'order' => $order,
            'customData' => $customData
        ];

        // Detectar si es orden mixta o si se pasaron selecciones
        $hasMixedItems = $providerSelections !== null || 
                        QuotationItemSelection::whereHas('quotation', function($query) use ($order) {
                            $query->where('provider_name', $order->provider->nombre ?? '');
                        })->exists();

        if ($hasMixedItems) {
            // Usar selecciones pasadas como parámetro o buscarlas
            $data['quotationItemSelections'] = $providerSelections ?: $this->getQuotationItemSelections($order);
        }

        // Generar y retornar PDF sin guardar
        $pdf = Pdf::loadView('purchase-orders.pdf-template-custom', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        Log::info('PDF para notificación generado exitosamente', ['order_id' => $order->id]);

        return $pdf;
    }

    /**
     * Obtiene las selecciones de cotización para orden mixta
     */
    private function getQuotationItemSelections(PurchaseOrder $order)
    {
        if (!$order->provider) {
            Log::warning('Orden sin proveedor asignado', ['order_id' => $order->id]);
            return collect();
        }

        $allSelections = QuotationItemSelection::with(['quotation'])
            ->whereHas('quotation', function($query) use ($order) {
                $query->where('provider_name', $order->provider->nombre);
            })
            ->get();

        Log::info('Selecciones de cotización obtenidas', [
            'order_id' => $order->id,
            'provider_name' => $order->provider->nombre,
            'selections_count' => $allSelections->count()
        ]);

        return $allSelections;
    }

    /**
     * Genera PDF personalizado (alias del método principal)
     */
    public function generateCustomPdf(PurchaseOrder $order, $providerSelections = null)
    {
        return $this->generatePdf($order, $providerSelections);
    }
}
