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
        $request = PurchaseRequest::with(['quotations', 'user', 'preApprover', 'preApprovedQuotation', 'quotationItemSelections'])
            ->findOrFail($id);
        
        // Validar que la solicitud esté en un estado apropiado para aprobación
        $validForApproval = false;
        
        // Para solicitudes de compra: deben estar pre-aprobadas y tener cotización o selección mixta
        if ($request->type === 'purchase') {
            if (in_array($request->status, ['pre-approved', 'Pre-aprobada'])) {
                // Verificar si tiene cotización pre-aprobada tradicional
                $hasPreApprovedQuotation = $request->preApprovedQuotation !== null;
                
                // Verificar si tiene selección mixta completa
                $hasMixedSelection = $this->hasMixedSelectionComplete($request);
                
                // Verificar si fue pre-aprobada sin cotización (nuevo flujo)
                $isPreApprovedWithoutQuotation = ($request->quotations->count() === 0) && 
                                               ($request->preApprovedQuotation === null);
                
                if ($hasPreApprovedQuotation || $hasMixedSelection || $isPreApprovedWithoutQuotation) {
                    $validForApproval = true;
                }
            }
        }
        
        // Para solicitudes de materiales: pueden estar pending o pre-aprobadas
        if (in_array($request->type, ['materials']) && in_array($request->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
            $validForApproval = true;
        }

        // Para solicitudes de servicios: pueden estar pending o pre-aprobadas
        if ($request->type === 'services' && in_array($request->status, ['pending', 'pre-approved', 'Pre-aprobada'])) {
            // Los servicios sin cotización no requieren validación de cotizaciones
            if ($request->isNoQuotationService()) {
                $validForApproval = true;
            } else {
                // Para servicios regulares, validar que tengan cotizaciones disponibles
                $hasQuotations = $request->quotations()->count() > 0;
                if ($hasQuotations || in_array($request->status, ['pre-approved', 'Pre-aprobada'])) {
                    $validForApproval = true;
                }
            }
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
                
                // Verificar si fue pre-aprobada sin cotización (nuevo flujo)
                $isPreApprovedWithoutQuotation = ($purchaseRequest->quotations->count() === 0) && 
                                               ($purchaseRequest->preApprovedQuotation === null);
                
                if ($hasPreApprovedQuotation || $hasMixedSelection || $isPreApprovedWithoutQuotation) {
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

            // Verificar si hay selección mixta de proveedores
            $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
            
            if ($hasMixedSelection) {
                \Log::info('Selección mixta detectada, creando órdenes individuales', [
                    'purchase_request_id' => $purchaseRequest->id,
                    'selections_count' => $purchaseRequest->quotationItemSelections()->count()
                ]);
                // Crear órdenes de compra individuales por proveedor
                $this->createMixedSelectionPurchaseOrders($purchaseRequest);
                return;
            }

            // Lógica original para solicitudes con cotización única
            $this->createSinglePurchaseOrder($purchaseRequest);

        } catch (\Exception $e) {
            \Log::error('Error al crear orden de compra automáticamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);
            // No lanzar excepción para no interrumpir el flujo de aprobación
        }
    }

    /**
     * Crear órdenes de compra individuales para selección mixta de proveedores
     */
    private function createMixedSelectionPurchaseOrders(PurchaseRequest $purchaseRequest): void
    {
        \Log::info('Iniciando creación de órdenes individuales para selección mixta', [
            'purchase_request_id' => $purchaseRequest->id
        ]);
        
        $itemSelections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
        
        \Log::info('Selecciones obtenidas', [
            'purchase_request_id' => $purchaseRequest->id,
            'total_selections' => $itemSelections->count(),
            'selections' => $itemSelections->map(function($sel) {
                return [
                    'quotation_id' => $sel->quotation_id,
                    'provider' => $sel->quotation->provider_name ?? 'N/A',
                    'item' => $sel->item_description,
                    'total' => $sel->total_price
                ];
            })
        ]);
        
        // Agrupar las selecciones por proveedor (quotation_id)
        $selectionsByProvider = $itemSelections->groupBy('quotation_id');
        
        \Log::info('Agrupación por proveedor', [
            'purchase_request_id' => $purchaseRequest->id,
            'providers_count' => $selectionsByProvider->count(),
            'providers' => $selectionsByProvider->keys()->toArray()
        ]);
        
        $orderCounter = 1;
        
        foreach ($selectionsByProvider as $quotationId => $providerSelections) {
            $quotation = $providerSelections->first()->quotation;
            
            // Buscar o crear proveedor basado en el nombre de la cotización
            $provider = \App\Models\Proveedor::where('nombre', $quotation->provider_name)->first();
            
            if (!$provider) {
                $provider = \App\Models\Proveedor::create([
                    'nombre' => $quotation->provider_name,
                    'email' => 'proveedor@contacto.com',
                    'telefono' => '000-000-0000',
                    'direccion' => 'Por definir',
                    'persona_contacto' => 'Por asignar',
                    'nit' => '000000000-0'
                ]);
            }
            
            // Calcular total para este proveedor
            $totalAmount = $providerSelections->sum('total_price');
            
            // Calcular IVA correctamente - para selecciones mixtas asumimos que los precios ya incluyen IVA
            $includesIva = true;
            $subtotal = round($totalAmount / 1.19, 2); // Calcular subtotal sin IVA
            $ivaAmount = round($totalAmount - $subtotal, 2); // Calcular IVA
            
            // Crear orden de compra individual para este proveedor
            $purchaseOrder = \App\Models\PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'order_number' => 'ORD-' . str_pad($purchaseRequest->id, 4, '0', STR_PAD_LEFT) . '-' . $orderCounter,
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
                'includes_iva' => $includesIva,
                'payment_terms' => $quotation->payment_terms ?? 'Contado',
                'delivery_date' => now()->addDays(15),
                'file_path' => 'pending_generation',
                'observations' => 'Orden creada automáticamente - Proveedor: ' . $quotation->provider_name,
                'created_by' => Auth::id() ?? 1, // Usar 1 como fallback si no hay usuario autenticado
                'status' => 'pending'
            ]);
            
            // Generar el PDF para esta orden individual
            $this->generatePurchaseOrderPdf($purchaseOrder, $purchaseRequest, $providerSelections);
            
            $orderCounter++;
            
            \Log::info('Orden de compra individual creada para selección mixta', [
                'purchase_request_id' => $purchaseRequest->id,
                'order_id' => $purchaseOrder->id,
                'provider' => $quotation->provider_name,
                'total_amount' => $totalAmount
            ]);
        }
    }

    /**
     * Crear orden de compra única para cotización tradicional
     */
    private function createSinglePurchaseOrder(PurchaseRequest $purchaseRequest): void
    {
        // Obtener datos de la cotización
        $quotation = $purchaseRequest->selectedQuotation ?? $purchaseRequest->preApprovedQuotation;
        
        if (!$quotation) {
            \Log::error('No se encontró cotización para crear orden de compra', [
                'purchase_request_id' => $purchaseRequest->id
            ]);
            return;
        }

        // Buscar el proveedor por nombre de la cotización
        $provider = \App\Models\Proveedor::where('nombre', $quotation->provider_name)->first();
        
        // Si no existe el proveedor, crearlo basado en los datos de la cotización
        if (!$provider) {
            \Log::info('Creando nuevo proveedor desde cotización', [
                'provider_name' => $quotation->provider_name,
                'purchase_request_id' => $purchaseRequest->id
            ]);
            
            $provider = \App\Models\Proveedor::create([
                'nombre' => $quotation->provider_name,
                'email' => $quotation->provider_email ?? 'sin-email@proveedor.com',
                'telefono' => $quotation->provider_phone ?? '000-000-0000',
                'direccion' => $quotation->provider_address ?? 'Por definir',
                'persona_contacto' => $quotation->provider_contact ?? 'Por asignar',
                'nit' => $quotation->provider_nit ?? '000000000-0',
                'ciudad' => 'Por definir',
                'servicio_producto' => 'Productos/Servicios'
            ]);
        } else {
            \Log::info('Usando proveedor existente', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->nombre,
                'purchase_request_id' => $purchaseRequest->id
            ]);
        }

        $totalAmount = $quotation->total_amount;
        $paymentTerms = $quotation->payment_terms ?? 'Contado';
        
        // Calcular IVA correctamente
        $includesIva = $quotation->includes_iva_19 ?? true; // Verificar si incluye IVA
        
        if ($includesIva) {
            // Si el total ya incluye IVA, calcular subtotal e IVA
            $subtotal = round($totalAmount / 1.19, 2);
            $ivaAmount = round($totalAmount - $subtotal, 2);
        } else {
            // Si el total no incluye IVA, calcular IVA y nuevo total
            $subtotal = $totalAmount;
            $ivaAmount = round($totalAmount * 0.19, 2);
            $totalAmount = $subtotal + $ivaAmount;
        }
        
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
            'delivery_date' => now()->addDays(15),
            'file_path' => 'pending_generation',
            'observations' => 'Orden creada automáticamente al aprobar solicitud',
            'created_by' => Auth::id() ?? 1, // Usar 1 como fallback si no hay usuario autenticado
            'status' => 'pending'
        ]);

        \Log::info('Orden de compra creada exitosamente', [
            'purchase_order_id' => $purchaseOrder->id,
            'order_number' => $purchaseOrder->order_number,
            'provider_id' => $provider->id,
            'provider_name' => $provider->nombre,
            'total_amount' => $totalAmount
        ]);

        // Generar el PDF inmediatamente
        $this->generatePurchaseOrderPdf($purchaseOrder, $purchaseRequest);
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
            
            // Calcular IVA correctamente - para servicios sin cotización asumimos que el presupuesto NO incluye IVA
            $includesIva = true; // Queremos que la orden final incluya IVA
            $subtotal = $totalAmount; // El presupuesto es el subtotal
            $ivaAmount = round($totalAmount * 0.19, 2); // Calcular 19% de IVA
            $totalAmount = $subtotal + $ivaAmount; // Total final con IVA
            
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
    private function generatePurchaseOrderPdf(\App\Models\PurchaseOrder $purchaseOrder, PurchaseRequest $purchaseRequest, $providerSelections = null): void
    {
        try {
            $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($purchaseOrder, $providerSelections);
            
            if ($pdfPath) {
                $purchaseOrder->update(['file_path' => $pdfPath]);
                \Log::info('PDF generado automáticamente para orden de compra', [
                    'purchase_request_id' => $purchaseRequest->id,
                    'order_id' => $purchaseOrder->id,
                    'pdf_path' => $pdfPath,
                    'provider_selections' => $providerSelections ? $providerSelections->count() : 0
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
