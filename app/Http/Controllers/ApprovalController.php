<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\RequestHistory;
use App\Notifications\RequestApproved;
use App\Notifications\PurchaseRequestApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ApprovalController extends Controller
{
    /**
     * Constructor que asegura que el usuario esté autenticado
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar la lista de solicitudes pendientes de aprobación final.
     */
    public function index()
    {
        // Obtener todas las solicitudes de compra que estén en estado "Pre-aprobada" o "pre-approved"
        $requests = PurchaseRequest::whereIn('status', ['pre-approved', 'Pre-aprobada'])
            ->with(['quotations', 'user', 'preApprover', 'preApprovedQuotation'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('approvals.index', compact('requests'));
    }

    /**
     * Mostrar los detalles de una solicitud específica para aprobación.
     */
    public function show($id)
    {
        $request = PurchaseRequest::with(['quotations', 'user', 'preApprover', 'preApprovedQuotation'])
            ->findOrFail($id);
        
        // Validar que la solicitud esté en un estado apropiado para aprobación
        $validForApproval = false;
        
        // Para solicitudes de compra: deben estar pre-aprobadas
        if ($request->type === 'purchase' && in_array($request->status, ['pre-approved', 'Pre-aprobada'])) {
            $validForApproval = true;
        }
        
        // Para solicitudes de materiales: pueden estar pending o pre-aprobadas
        if (in_array($request->type, ['materials']) && in_array($request->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
            $validForApproval = true;
        }
        
        if (!$validForApproval) {
            return redirect()->route('approvals.index')
                ->with('error', 'Esta solicitud no está en un estado válido para aprobación.');
        }

        return view('approvals.show', compact('request'));
    }

    /**
     * Aprobar definitivamente una solicitud pre-aprobada.
     */
    public function approve(Request $request, $id)
    {
        // Validar la entrada
        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        // Obtener la solicitud
        $purchaseRequest = PurchaseRequest::with(['preApprovedQuotation', 'user'])
            ->findOrFail($id);

        // Verificar que la solicitud esté en un estado válido para aprobación
        $validForApproval = false;
        
        // Para solicitudes de compra: deben estar pre-aprobadas y tener cotización
        if ($purchaseRequest->type === 'purchase') {
            if (in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada']) && $purchaseRequest->preApprovedQuotation) {
                $validForApproval = true;
            } else if (in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada']) && !$purchaseRequest->preApprovedQuotation) {
                return redirect()->back()
                    ->with('error', 'La solicitud de compra no tiene una cotización pre-aprobada seleccionada.');
            }
        }
        
        // Para solicitudes de materiales: pueden estar pending o pre-aprobadas
        if (in_array($purchaseRequest->type, ['materials'])) {
            if (in_array($purchaseRequest->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
                $validForApproval = true;
            }
        }
        
        if (!$validForApproval) {
            return redirect()->back()
                ->with('error', 'Esta solicitud no está en un estado válido para aprobación.');
        }

        // Actualizar el estado de la solicitud a aprobada
        $updateData = [
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
        ];
        
        // Solo agregar selected_quotation_id para solicitudes de compra
        if ($purchaseRequest->type === 'purchase' && $purchaseRequest->pre_approved_quotation_id) {
            $updateData['selected_quotation_id'] = $purchaseRequest->pre_approved_quotation_id;
        }
        
        $purchaseRequest->update($updateData);

        // Registrar en el historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Aprobación final',
            'notes' => $validated['comments'] ?? 'Solicitud aprobada definitivamente'
        ]);

        // Crear automáticamente la orden de compra SOLO para solicitudes de compra
        if ($purchaseRequest->type === 'purchase') {
            $this->createPurchaseOrder($purchaseRequest);
        }

        // Enviar notificación personalizada al usuario que realizó la solicitud
        if ($purchaseRequest->user) {
            $purchaseRequest->user->notify(new PurchaseRequestApproved($purchaseRequest, 'user'));
        }
        
        // Enviar notificación a compras@tvs.edu.co y contabilidad@tvs.edu.co
        try {
            // Verificar si es una solicitud de fotocopias para enviar emails diferenciados
            if ($purchaseRequest->isCopiesRequest()) {
                // Para fotocopias: solo enviar a compras con plantilla específica
                Notification::route('mail', 'compras@tvs.edu.co')
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'compras_fotocopias'));
                
                // Notificar a auxiliaralmacen@tvs.edu.co
                Notification::route('mail', 'auxiliaralmacen@tvs.edu.co')
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'auxiliaralmacen'));
                
                \Log::info('Notificaciones de fotocopias enviadas - compras y auxiliaralmacen para solicitud #' . $purchaseRequest->id);
            } else {
                // Para órdenes de compra normales: enviar a compras, contabilidad y tesorería
                Notification::route('mail', 'compras@tvs.edu.co')
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'compras'));
                
                Notification::route('mail', 'contabilidad@tvs.edu.co')
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'contabilidad'));
                
                Notification::route('mail', 'tesoreria@tvs.edu.co')
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'contabilidad'));
                
                \Log::info('Notificaciones de orden de compra enviadas - compras, contabilidad y tesorería para solicitud #' . $purchaseRequest->id);
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de aprobación: ' . $e->getMessage(), [
                'purchase_request_id' => $purchaseRequest->id
            ]);
        }

        return redirect()->route('approvals.index')
            ->with('success', 'La solicitud ha sido aprobada correctamente.');
    }

    /**
     * Rechazar una solicitud pre-aprobada.
     */
    public function reject(Request $request, $id)
    {
        // Validar la entrada
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        // Obtener la solicitud
        $purchaseRequest = PurchaseRequest::with(['user'])
            ->findOrFail($id);

        // Verificar que la solicitud esté en un estado válido para rechazo
        $validForRejection = false;
        
        // Para solicitudes de compra: deben estar pre-aprobadas
        if ($purchaseRequest->type === 'purchase' && in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada'])) {
            $validForRejection = true;
        }
        
        // Para solicitudes de materiales: pueden estar pending o pre-aprobadas
        if (in_array($purchaseRequest->type, ['materials']) && in_array($purchaseRequest->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
            $validForRejection = true;
        }
        
        if (!$validForRejection) {
            return redirect()->back()
                ->with('error', 'Esta solicitud no está en un estado válido para rechazo.');
        }

        // Actualizar el estado de la solicitud a rechazada
        $purchaseRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approval_date' => now(),
            'rejection_reason' => $validated['rejection_reason']
        ]);

        // Registrar en el historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Solicitud rechazada',
            'notes' => $validated['rejection_reason']
        ]);

        // Notificar al usuario que creó la solicitud
        if ($purchaseRequest->user) {
            $purchaseRequest->user->notify(new \App\Notifications\PurchaseRequestRejected($purchaseRequest));
            \Log::info('Notificación de rechazo enviada al usuario', [
                'purchase_request_id' => $purchaseRequest->id,
                'user_email' => $purchaseRequest->user->email
            ]);
        }

        return redirect()->route('approvals.index')
            ->with('success', 'La solicitud ha sido rechazada correctamente.');
    }

    /**
     * Actualizar el presupuesto de una solicitud pre-aprobada.
     */
    public function updateBudget(Request $request, $id)
    {
        // Validar la entrada
        $validated = $request->validate([
            'budget' => 'required|string|max:255',
        ]);

        // Obtener la solicitud
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        // Verificar que la solicitud esté en estado 'pre-approved' o 'Pre-aprobada'
        if (!in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada'])) {
            return redirect()->back()
                ->with('error', 'Solo se puede modificar el presupuesto de solicitudes que estén en estado "Pre-aprobada".');
        }

        // Actualizar el presupuesto
        $purchaseRequest->update([
            'budget' => $validated['budget']
        ]);

        // Registrar en el historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Presupuesto actualizado',
            'notes' => "Presupuesto cambiado a: {$validated['budget']}"
        ]);

        return redirect()->back()
            ->with('success', 'El presupuesto ha sido actualizado correctamente.');
    }

    /**
     * Crear automáticamente la orden de compra para una solicitud aprobada
     */
    private function createPurchaseOrder(PurchaseRequest $purchaseRequest): void
    {
        try {
            // Verificar si ya existe una orden de compra
            $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
            
            if ($existingOrder) {
                \Log::info('Orden de compra ya existe para la solicitud', [
                    'purchase_request_id' => $purchaseRequest->id,
                    'order_id' => $existingOrder->id
                ]);
                return;
            }

            // Obtener o crear un proveedor por defecto si no existe
            $provider = \App\Models\Proveedor::first();
            
            if (!$provider) {
                $provider = \App\Models\Proveedor::create([
                    'name' => 'Proveedor Por Asignar',
                    'email' => 'porAsignar@tvs.edu.co',
                    'phone' => '000-000-0000',
                    'address' => 'Por definir',
                    'contact_person' => 'Por asignar'
                ]);
            }

            // Obtener datos de la cotización seleccionada
            $quotation = $purchaseRequest->selectedQuotation ?? $purchaseRequest->preApprovedQuotation;
            $totalAmount = $quotation ? $quotation->total_amount : 0;
            
            // Calcular IVA si es necesario
            $includesIva = true;
            $subtotal = $totalAmount / 1.19; // Asumir que el total ya incluye IVA
            $ivaAmount = $totalAmount - $subtotal;
            
            // Crear la orden de compra
            $purchaseOrder = \App\Models\PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'order_number' => 'ORD-' . str_pad($purchaseRequest->id, 4, '0', STR_PAD_LEFT),
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
                'includes_iva' => $includesIva,
                'payment_terms' => $quotation ? ($quotation->payment_terms ?? 'Contado') : 'Contado',
                'delivery_date' => now()->addDays(15), // 15 días por defecto
                'file_path' => 'pending_generation',
                'observations' => 'Orden creada automáticamente al aprobar solicitud',
                'created_by' => Auth::id(),
                'status' => 'pending'
            ]);

            // Generar el PDF inmediatamente
            try {
                $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($purchaseOrder);
                
                if ($pdfPath) {
                    $purchaseOrder->update(['file_path' => $pdfPath]);
                    \Log::info('PDF generado automáticamente para orden de compra', [
                        'purchase_request_id' => $purchaseRequest->id,
                        'order_id' => $purchaseOrder->id,
                        'pdf_path' => $pdfPath
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al generar PDF automáticamente', [
                    'purchase_request_id' => $purchaseRequest->id,
                    'order_id' => $purchaseOrder->id,
                    'error' => $e->getMessage()
                ]);
            }

            \Log::info('Orden de compra creada automáticamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'order_id' => $purchaseOrder->id,
                'order_number' => $purchaseOrder->order_number
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear orden de compra automáticamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);
            // No lanzar excepción para no interrumpir el flujo de aprobación
        }
    }
}
