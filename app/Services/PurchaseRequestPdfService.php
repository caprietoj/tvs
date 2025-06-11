<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class PurchaseRequestPdfService
{
    /**
     * Genera el PDF de una solicitud de compra
     */
    public function generatePdf(PurchaseRequest $purchaseRequest)
    {
        try {
            // Cargar las relaciones necesarias
            $purchaseRequest->load(['user']);
            
            // Configurar vista para el PDF
            $view = view('pdfs.purchase-request', [
                'purchaseRequest' => $purchaseRequest,
                'items' => $purchaseRequest->purchase_items ?? [],
                'user' => $purchaseRequest->user,
            ]);
            
            // Generar PDF
            $pdf = \PDF::loadHTML($view->render());
            $pdf->setPaper('letter', 'portrait');
            
            // Definir nombre de archivo
            $fileName = 'purchase_request_' . $purchaseRequest->id . '_' . now()->format('YmdHis') . '.pdf';
            
            Log::info('PDF generado exitosamente para solicitud de compra #' . $purchaseRequest->id);
            
            return $pdf;
        } catch (Exception $e) {
            Log::error('Error al generar PDF para solicitud de compra #' . $purchaseRequest->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera y descarga el PDF directamente
     */
    public function downloadPdf(PurchaseRequest $purchaseRequest)
    {
        $pdf = $this->generatePdf($purchaseRequest);
        $fileName = 'solicitud_compra_' . $purchaseRequest->id . '.pdf';
        
        return $pdf->download($fileName);
    }
    
    /**
     * Genera y muestra el PDF en el navegador
     */
    public function streamPdf(PurchaseRequest $purchaseRequest)
    {
        $pdf = $this->generatePdf($purchaseRequest);
        $fileName = 'solicitud_compra_' . $purchaseRequest->id . '.pdf';
        
        return $pdf->stream($fileName);
    }
}
