<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\RequestHistory;
use App\Models\QuotationItemSelection;
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
        // Obtener todas las solicitudes que estén listas para aprobación final
        $requests = PurchaseRequest::where(function($query) {
                // Solicitudes pre-aprobadas tradicionales
                $query->whereIn('status', ['pre-approved', 'Pre-aprobada'])
                      // O servicios sin cotización en estado pending
                      ->orWhere(function($subQuery) {
                          $subQuery->where('type', 'services')
                                   ->where('service_type', 'no_quotation')
                                   ->where('status', 'pending');
                      });
            })
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

        // Para solicitudes de servicios: pueden estar pending o pre-aprobadas
        if ($request->type === 'services' && in_array($request->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
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
        
        // Para solicitudes de compra: deben estar pre-aprobadas y tener cotización o selección mixta
        if ($purchaseRequest->type === 'purchase') {
            if (in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada'])) {
                // Verificar si tiene cotización pre-aprobada tradicional
                $hasPreApprovedQuotation = $purchaseRequest->preApprovedQuotation !== null;
                
                // Verificar si tiene selección mixta completa
                $hasMixedSelection = $this->hasMixedSelectionComplete($purchaseRequest);
                
                if ($hasPreApprovedQuotation || $hasMixedSelection) {
                    $validForApproval = true;
                } else {
                    return redirect()->back()
                        ->with('error', 'La solicitud de compra no tiene una cotización pre-aprobada seleccionada ni una selección mixta completa.');
                }
            }
        }
        
        // Para solicitudes de materiales: pueden estar pending o pre-aprobadas
        if (in_array($purchaseRequest->type, ['materials'])) {
            if (in_array($purchaseRequest->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
                $validForApproval = true;
            }
        }

        // Para solicitudes de servicios: pueden estar pending o pre-aprobadas
        if ($purchaseRequest->type === 'services') {
            if (in_array($purchaseRequest->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
                // Los servicios sin cotización no requieren validación de cotizaciones
                if ($purchaseRequest->isNoQuotationService()) {
                    $validForApproval = true;
                } else {
                    // Para servicios regulares, validar que tengan cotizaciones disponibles
                    $hasQuotations = $purchaseRequest->quotations()->count() > 0;
                    if ($hasQuotations || in_array($purchaseRequest->status, ['pre-approved', 'Pre-aprobada'])) {
                        $validForApproval = true;
                    } else {
                        return redirect()->back()
                            ->with('error', 'La solicitud de servicio regular requiere al menos una cotización para ser aprobada.');
                    }
                }
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

        // Crear orden de compra automáticamente para solicitudes de compra y servicios
        if (in_array($purchaseRequest->type, ['purchase', 'services'])) {
            $this->createPurchaseOrder($purchaseRequest);
        }

        // CORREGIDO: Enviar notificación solo al usuario que realizó la solicitud
        if ($purchaseRequest->user) {
            $purchaseRequest->user->notify(new PurchaseRequestApproved($purchaseRequest, 'user'));
            \Log::info('Notificación de aprobación enviada al usuario: ' . $purchaseRequest->user->email);
        }
        
        // CORREGIDO: Enviar notificación SOLO a compras (no a directores ni coordinadores)
        try {
            // Obtener email de compras desde configuración dinámica
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            // Verificar si es una solicitud de fotocopias para enviar emails diferenciados
            if ($purchaseRequest->isCopiesRequest()) {
                // Para fotocopias: solo enviar a compras con plantilla específica
                Notification::route('mail', $comprasEmail)
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'compras_fotocopias'));
                
                // Notificar a auxiliaralmacen solo para fotocopias
                $auxiliaralmacenEmail = config($configSource . '.sections.Auxiliar Almacén', 'auxiliaralmacen@tvs.edu.co');
                Notification::route('mail', $auxiliaralmacenEmail)
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'auxiliaralmacen'));
                
                \Log::info("Notificaciones de fotocopias enviadas - compras ({$comprasEmail}) y auxiliaralmacen ({$auxiliaralmacenEmail}) para solicitud #" . $purchaseRequest->id);
            } else {
                // CORREGIDO: Para órdenes de compra normales: enviar SOLO a compras (no a contabilidad ni tesorería)
                Notification::route('mail', $comprasEmail)
                    ->notify(new PurchaseRequestApproved($purchaseRequest, 'compras'));
                
                \Log::info("Notificación de orden de compra enviada SOLO a compras ({$comprasEmail}) para solicitud #" . $purchaseRequest->id);
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

            // Para servicios sin cotización, usar datos del proveedor ingresado manualmente
            if ($purchaseRequest->isNoQuotationService()) {
                $this->createPurchaseOrderForNoQuotationService($purchaseRequest);
                return;
            }

            // Lógica original para solicitudes de compra con cotizaciones
            // Obtener o crear un proveedor por defecto si no existe
            $provider = \App\Models\Proveedor::first();
            
            if (!$provider) {
                $provider = \App\Models\Proveedor::create([
                    'name' => 'Proveedor Por Asignar',
                    'email' => 'porAsignar@test.com',
                    'phone' => '000-000-0000',
                    'address' => 'Por definir',
                    'contact_person' => 'Por asignar',
                    'nit' => '000000000-0'
                ]);
            }

            // Obtener datos de la cotización o selección mixta
            $quotation = $purchaseRequest->selectedQuotation ?? $purchaseRequest->preApprovedQuotation;
            $hasMixedSelection = $this->hasMixedSelectionComplete($purchaseRequest);
            
            // Calcular monto total
            if ($hasMixedSelection) {
                // Para selección mixta, sumar todos los totales de las selecciones
                $totalAmount = $purchaseRequest->quotationItemSelections()->sum('total_price');
                $paymentTerms = 'Mixto - Ver selecciones individuales';
            } else {
                // Para cotización única
                $totalAmount = $quotation ? $quotation->total_amount : 0;
                $paymentTerms = $quotation ? ($quotation->payment_terms ?? 'Contado') : 'Contado';
            }
            
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
                'payment_terms' => $paymentTerms,
                'delivery_date' => now()->addDays(15), // 15 días por defecto
                'file_path' => 'pending_generation',
                'observations' => $hasMixedSelection 
                    ? 'Orden creada automáticamente - Selección mixta de proveedores'
                    : 'Orden creada automáticamente al aprobar solicitud',
                'created_by' => Auth::id(),
                'status' => 'pending'
            ]);

            // Generar el PDF inmediatamente
            $this->generatePurchaseOrderPdf($purchaseOrder, $purchaseRequest);

        } catch (\Exception $e) {
            \Log::error('Error al crear orden de compra automáticamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);
            // No lanzar excepción para no interrumpir el flujo de aprobación
        }
    }

    /**
     * Crear orden de compra para servicio sin cotización
     */
    private function createPurchaseOrderForNoQuotationService(PurchaseRequest $purchaseRequest): void
    {
        try {
            // Buscar o crear proveedor basado en la información ingresada
            $provider = \App\Models\Proveedor::where('name', $purchaseRequest->provider_name)->first();
            
            if (!$provider) {
                $provider = \App\Models\Proveedor::create([
                    'name' => $purchaseRequest->provider_name,
                    'nit' => $purchaseRequest->provider_nit ?? 'Sin NIT',
                    'email' => $purchaseRequest->provider_email ?? 'sin-email@proveedor.com',
                    'phone' => $purchaseRequest->provider_contact ?? 'Sin teléfono',
                    'address' => 'Por definir',
                    'contact_person' => $purchaseRequest->provider_contact ?? 'Sin contacto'
                ]);
            }

            // Usar el presupuesto de la solicitud como monto total
            $totalAmount = $purchaseRequest->service_budget ?? 0;
            
            // Calcular IVA si es necesario
            $includesIva = true;
            $subtotal = $totalAmount / 1.19;
            $ivaAmount = $totalAmount - $subtotal;
            
            // Crear la orden de compra
            $purchaseOrder = \App\Models\PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'order_number' => 'ORD-SV-' . str_pad($purchaseRequest->id, 4, '0', STR_PAD_LEFT),
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
                'includes_iva' => $includesIva,
                'payment_terms' => 'Contado',
                'delivery_date' => now()->addDays(30), // 30 días para servicios
                'file_path' => 'pending_generation',
                'observations' => 'Orden de servicio sin cotización - ' . $purchaseRequest->no_quotation_reason,
                'created_by' => Auth::id(),
                'status' => 'pending'
            ]);

            // Generar el PDF inmediatamente
            $this->generatePurchaseOrderPdf($purchaseOrder, $purchaseRequest);

            \Log::info('Orden de compra para servicio sin cotización creada automáticamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'order_id' => $purchaseOrder->id,
                'order_number' => $purchaseOrder->order_number,
                'provider' => $purchaseRequest->provider_name
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear orden de compra para servicio sin cotización', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generar PDF para la orden de compra
     */
    private function generatePurchaseOrderPdf(\App\Models\PurchaseOrder $purchaseOrder, PurchaseRequest $purchaseRequest): void
    {
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
    }

    /**
     * Verificar si la solicitud tiene una selección mixta completa
     */
    private function hasMixedSelectionComplete(PurchaseRequest $purchaseRequest)
    {
        // Obtener items de la solicitud
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
            
        if (empty($purchaseItems)) {
            return false;
        }
        
        // Contar selecciones existentes
        $selectionsCount = $purchaseRequest->quotationItemSelections()->count();
        
        // Verificar que hay selección para cada item
        return $selectionsCount === count($purchaseItems);
    }
}
