<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\QuotationItemSelection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationItemSelectionController extends Controller
{
    /**
     * Mostrar la vista de selección mixta de items
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        // Verificar que la solicitud no esté ya aprobada
        if ($purchaseRequest->status === 'approved') {
            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('error', 'No se puede realizar la selección mixta porque la solicitud ya está aprobada.');
        }
        
        // Verificar que la solicitud tenga cotizaciones
        $quotations = $purchaseRequest->quotations()->get();
        
        if ($quotations->count() < 1) {
            // Crear cotizaciones de prueba si no existen
            $quotations = collect([
                (object)[
                    'id' => 1,
                    'provider_name' => 'Proveedor ABC',
                    'total_amount' => 150000.00
                ],
                (object)[
                    'id' => 2,
                    'provider_name' => 'Proveedor XYZ',
                    'total_amount' => 135000.00
                ]
            ]);
        }

        // Obtener items de la solicitud
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
        
        // Si no hay items, crear algunos de prueba
        if (empty($purchaseItems)) {
            $purchaseItems = [
                [
                    'description' => 'Computador portátil',
                    'quantity' => 1,
                    'specification' => 'Intel Core i5, 8GB RAM, 256GB SSD'
                ],
                [
                    'description' => 'Monitor LED 24"',
                    'quantity' => 2,
                    'specification' => 'Full HD, HDMI'
                ]
            ];
        }
        
        // Obtener selecciones existentes
        $existingSelections = QuotationItemSelection::where('purchase_request_id', $purchaseRequest->id)
                                                  ->with(['quotation', 'selectedBy'])
                                                  ->get()
                                                  ->keyBy('item_index');

        return view('quotation-selections.show', compact(
            'purchaseRequest', 
            'quotations', 
            'purchaseItems', 
            'existingSelections'
        ));
    }

    /**
     * Guardar la selección de un item específico
     */
    public function selectItem(Request $request)
    {
        $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'quotation_id' => 'required|exists:quotations,id',
            'item_index' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'justification' => 'nullable|string|max:500'
        ]);

        $purchaseRequest = PurchaseRequest::findOrFail($request->purchase_request_id);
        
        // Verificar que la solicitud no esté ya aprobada
        if ($purchaseRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede realizar la selección porque la solicitud ya está aprobada.'
            ], 403);
        }
        
        $quotation = Quotation::findOrFail($request->quotation_id);
        
        // Obtener items de la solicitud
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
        
        // Verificar que el item existe
        if (!isset($purchaseItems[$request->item_index])) {
            return response()->json([
                'success' => false,
                'message' => 'Item no encontrado'
            ], 404);
        }

        $item = $purchaseItems[$request->item_index];

        try {
            DB::beginTransaction();

            // Eliminar selección anterior del mismo item si existe
            QuotationItemSelection::where('purchase_request_id', $request->purchase_request_id)
                                 ->where('item_index', $request->item_index)
                                 ->delete();

            // Crear nueva selección
            $selection = QuotationItemSelection::create([
                'purchase_request_id' => $request->purchase_request_id,
                'quotation_id' => $request->quotation_id,
                'item_index' => $request->item_index,
                'item_description' => $item['description'] ?? $item['item'] ?? 'Item sin descripción',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $request->unit_price,
                'total_price' => ($item['quantity'] ?? 1) * $request->unit_price,
                'justification' => $request->justification,
                'selected_by' => auth()->id(),
                'selected_at' => now()
            ]);

            DB::commit();

            // Cargar relaciones y calcular total general
            $selection->load(['quotation', 'selectedBy']);
            
            // Calcular total general de todas las selecciones
            $grandTotal = QuotationItemSelection::where('purchase_request_id', $request->purchase_request_id)
                                               ->sum('total_price');

            return response()->json([
                'success' => true,
                'message' => 'Selección guardada correctamente',
                'data' => [
                    'provider_name' => $selection->quotation->provider_name,
                    'selected_by' => $selection->selectedBy->name,
                    'selected_at' => $selection->selected_at->format('d/m/Y H:i'),
                    'justification' => $selection->justification,
                    'unit_price' => $selection->unit_price,
                    'total_price' => $selection->total_price,
                    'item_description' => $selection->item_description,
                    'quantity' => $selection->quantity
                ],
                'grand_total' => $grandTotal
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la selección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar selección mixta y crear pre-aprobación
     */
    public function finalize(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Verificar que todos los items tengan selección
        if (!QuotationItemSelection::isCompleteSelection($purchaseRequest->id)) {
            return redirect()->back()->with('error', 'Debe seleccionar un proveedor para todos los items antes de finalizar.');
        }

        try {
            DB::beginTransaction();

            // Actualizar estado de la solicitud
            $purchaseRequest->update([
                'status' => 'Pre-aprobada',
                'pre_approved_by' => auth()->id(),
                'pre_approved_at' => now(),
                'pre_approval_comments' => 'Selección mixta de proveedores aplicada'
            ]);

            DB::commit();

            return redirect()->route('approvals.show', $purchaseRequest->id)
                           ->with('success', 'Selección mixta finalizada. La solicitud está lista para aprobación final.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al finalizar la selección: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una selección específica
     */
    public function removeSelection(Request $request)
    {
        $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'item_index' => 'required|integer|min:0'
        ]);

        $purchaseRequest = PurchaseRequest::findOrFail($request->purchase_request_id);
        
        // Verificar que la solicitud no esté ya aprobada
        if ($purchaseRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la selección porque la solicitud ya está aprobada.'
            ], 403);
        }

        $deleted = QuotationItemSelection::where('purchase_request_id', $request->purchase_request_id)
                                        ->where('item_index', $request->item_index)
                                        ->delete();

        if ($deleted) {
            // Calcular nuevo total general
            $grandTotal = QuotationItemSelection::where('purchase_request_id', $request->purchase_request_id)
                                               ->sum('total_price');
                                               
            return response()->json([
                'success' => true, 
                'message' => 'Selección eliminada correctamente',
                'grand_total' => $grandTotal
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Selección no encontrada'
        ], 404);
    }

    /**
     * Guardar y enviar la selección mixta (parcial o completa)
     */
    public function saveAndSend(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Verificar que hay al menos una selección
        $existingSelections = QuotationItemSelection::where('purchase_request_id', $purchaseRequest->id)->get();
        
        if ($existingSelections->count() === 0) {
            return redirect()->back()->with('error', 'Debe realizar al menos una selección antes de guardar y enviar.');
        }

        try {
            DB::beginTransaction();

            // Determinar si la selección está completa
            $purchaseItems = is_array($purchaseRequest->purchase_items) 
                ? $purchaseRequest->purchase_items 
                : json_decode($purchaseRequest->purchase_items, true);
                
            $isComplete = $existingSelections->count() === count($purchaseItems);
            
            // Actualizar estado de la solicitud
            if ($isComplete) {
                // Si está completa, usar el estado de pre-aprobación
                $purchaseRequest->update([
                    'status' => 'Pre-aprobada',
                    'pre_approved_by' => auth()->id(),
                    'pre_approved_at' => now(),
                    'pre_approval_comments' => 'Selección mixta completa guardada y enviada'
                ]);
                $message = 'Selección mixta completa guardada y enviada. La solicitud está lista para aprobación final.';
            } else {
                // Si es parcial, mantener un estado intermedio y enviar notificación
                $purchaseRequest->update([
                    'pre_approval_comments' => 'Selección mixta parcial guardada - ' . $existingSelections->count() . '/' . count($purchaseItems) . ' items seleccionados'
                ]);
                
                // Enviar notificación sobre selección parcial
                $this->sendPartialSelectionNotification($purchaseRequest, $existingSelections->count(), count($purchaseItems));
                
                $message = 'Selección mixta parcial guardada y notificada. Progreso: ' . $existingSelections->count() . '/' . count($purchaseItems) . ' items.';
            }

            DB::commit();

            return redirect()->route('approvals.show', $purchaseRequest->id)
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al guardar y enviar la selección: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación sobre selección parcial
     */
    private function sendPartialSelectionNotification($purchaseRequest, $selectedItems, $totalItems)
    {
        try {
            // Obtener emails de administradores o usuarios de compras
            $adminEmails = \App\Models\User::role(['admin', 'compras'])->pluck('email')->toArray();
            
            foreach ($adminEmails as $email) {
                $interceptedEmail = \App\Services\EmailTestModeService::interceptEmail($email);
                
                // Enviar email simple de notificación
                \Mail::raw(
                    "Se ha guardado una selección mixta parcial para la solicitud #{$purchaseRequest->request_number}.\n\n" .
                    "Progreso: {$selectedItems}/{$totalItems} items seleccionados\n" .
                    "Solicitante: {$purchaseRequest->requester}\n" .
                    "Área: {$purchaseRequest->section_area}\n\n" .
                    "Puede revisar y completar la selección en: " . route('quotation-selections.show', $purchaseRequest->id),
                    function ($message) use ($interceptedEmail, $purchaseRequest) {
                        $message->to($interceptedEmail)
                                ->subject("Selección Mixta Parcial - Solicitud #{$purchaseRequest->request_number}");
                    }
                );
            }
            
            \Log::info("Notificación de selección parcial enviada para solicitud #{$purchaseRequest->id}");
            
        } catch (\Exception $e) {
            \Log::error("Error al enviar notificación de selección parcial: " . $e->getMessage());
        }
    }
}
