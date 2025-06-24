<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\RequestHistory;
use App\Models\PurchaseOrder;
use App\Notifications\OrderCreated;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrdersController extends Controller
{
    protected $pdfService;

    /**
     * Constructor
     */
    public function __construct(PurchaseOrderPdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
    }
    
    /**
     * Mostrar todas las órdenes de compra.
     */
    public function index()
    {
        // Obtener las órdenes de compra existentes
        $orders = PurchaseOrder::with(['purchaseRequest', 'purchaseRequest.user', 'provider'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Obtener las solicitudes aprobadas pendientes de generar órdenes de compra
        $approvedRequests = PurchaseRequest::with(['selectedQuotation', 'user', 'approver'])
            ->whereIn('status', ['approved', 'in_process'])
            ->whereNotIn('id', function($query) {
                $query->select('purchase_request_id')
                    ->from('purchase_orders')
                    ->whereNull('deleted_at');
            })
            ->where('selected_quotation_id', '!=', null)
            ->orderBy('approval_date', 'desc')
            ->get();
            
        return view('purchase-orders.index', compact('orders', 'approvedRequests'));
    }

    /**
     * Mostrar formulario para crear una nueva orden de compra.
     */
    public function create(PurchaseRequest $purchaseRequest)
    {
        // Verificar que la solicitud esté aprobada
        if (!in_array($purchaseRequest->status, ['approved', 'in_process'])) {
            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('error', 'Solo se pueden generar órdenes de compra para solicitudes aprobadas.');
        }
        
        // Verificar que tenga una cotización seleccionada
        if (!$purchaseRequest->selected_quotation_id) {
            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('error', 'La solicitud no tiene una cotización seleccionada.');
        }
        
        return view('purchase-orders.create', compact('purchaseRequest'));
    }

    /**
     * Guardar una nueva orden de compra.
     */
    public function store(Request $request, $purchaseRequestId)
    {
        $request->validate([
            'provider_id' => 'required|exists:proveedors,id',
            'payment_terms' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'observations' => 'nullable|string',
        ]);
        
        // Obtener la solicitud de compra
        $purchaseRequest = PurchaseRequest::findOrFail($purchaseRequestId);
        
        // Verificar que la solicitud esté aprobada
        if (!in_array($purchaseRequest->status, ['approved', 'in_process'])) {
            return redirect()->route('purchase-orders.index')->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
        }
        
        // Verificar que no exista una orden para esta solicitud
        if (PurchaseOrder::where('purchase_request_id', $purchaseRequestId)->exists()) {
            return redirect()->route('purchase-orders.index')->with('error', 'Ya existe una orden de compra para esta solicitud.');
        }

        // Verificar que tenga una cotización seleccionada
        if (!$purchaseRequest->selected_quotation_id || !$purchaseRequest->selectedQuotation) {
            return redirect()->route('purchase-orders.index')->with('error', 'La solicitud no tiene una cotización seleccionada válida.');
        }

        // Obtener los datos de precio directamente de la cotización seleccionada
        $selectedQuotation = $purchaseRequest->selectedQuotation;
        $total = $selectedQuotation->total_amount;
        $subtotal = $selectedQuotation->subtotal ?? $selectedQuotation->total_amount;
        $ivaAmount = $selectedQuotation->iva_amount ?? 0;
        $includesIva = $selectedQuotation->includes_iva ?? false;
        $additionalItems = $selectedQuotation->additional_items ?? [];
        
        try {
            DB::beginTransaction();
            
            // Crear la orden de compra
            $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 3, '0', STR_PAD_LEFT);
            
            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'purchase_request_id' => $purchaseRequestId,
                'provider_id' => $request->provider_id,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(),
                'payment_terms' => $request->payment_terms,
                'delivery_date' => $request->delivery_date,
                'observations' => $request->observations,
                'total_amount' => $total,
                'file_path' => 'pending_generation',
                'status' => 'pending',
                'additional_items' => $additionalItems,
                'includes_iva' => $includesIva,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
            ]);
            
            // Generar PDF
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($order);
            
            // Actualizar la ruta del archivo
            $order->update(['file_path' => $pdfPath]);
            
            // NO cambiar el estado de la solicitud - debe permanecer como 'approved'
            // La solicitud ya fue aprobada y la orden de compra es el resultado de esa aprobación
            
            DB::commit();
            
            return redirect()->route('purchase-orders.show', $order->id)->with('success', 'Orden de compra generada correctamente.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear orden de compra: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Error al crear la orden de compra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar una orden de compra.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['purchaseRequest', 'purchaseRequest.user', 'provider']);
        
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Mostrar formulario para editar una orden.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        // Solo administradores pueden editar órdenes de compra
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No tienes permisos para editar órdenes de compra.');
        }

        // Se pueden editar órdenes pendientes o aprobadas (solo admin)
        if (!in_array($purchaseOrder->status, ['pending', 'approved'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede editar una orden que ya ha sido procesada o enviada.');
        }
        
        return view('purchase-orders.edit', compact('purchaseOrder'));
    }

    /**
     * Actualizar una orden de compra.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Solo administradores pueden actualizar órdenes de compra
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No tienes permisos para editar órdenes de compra.');
        }

        // Se pueden actualizar órdenes pendientes o aprobadas (solo admin)
        if (!in_array($purchaseOrder->status, ['pending', 'approved'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede editar una orden que ya ha sido procesada o enviada.');
        }
        
        // Validar datos
        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'provider_id' => 'required|exists:proveedors,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_terms' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'order_file' => 'nullable|file|mimes:pdf|max:10240',
            'observations' => 'nullable|string',
        ]);
        
        // Actualizar archivo si se ha subido uno nuevo
        $filePath = $purchaseOrder->file_path;
        if ($request->hasFile('order_file')) {
            // Eliminar archivo anterior
            Storage::disk('public')->delete($purchaseOrder->file_path);
            
            // Subir nuevo archivo
            $filePath = $request->file('order_file')->store('purchase_orders', 'public');
        }
        
        // Actualizar la orden
        $purchaseOrder->update([
            'order_number' => $validated['order_number'],
            'provider_id' => $validated['provider_id'],
            'total_amount' => $validated['total_amount'],
            'payment_terms' => $validated['payment_terms'],
            'delivery_date' => $validated['delivery_date'],
            'file_path' => $filePath,
            'observations' => $validated['observations'],
        ]);
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Orden de compra actualizada exitosamente.');
    }

    /**
     * Generar el PDF de la orden de compra.
     */
    public function generatePdf(PurchaseOrder $purchaseOrder)
    {
        try {
            $pdfPath = $this->pdfService->generatePdf($purchaseOrder);
            $purchaseOrder->update(['file_path' => $pdfPath]);
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'PDF de la orden de compra generado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al generar el PDF: ' . $e->getMessage());
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Descargar el archivo PDF de la orden de compra.
     */
    public function download(PurchaseOrder $purchaseOrder)
    {
        // Verificar si el archivo existe o si el file_path es el valor temporal
        if ($purchaseOrder->file_path === 'pending_generation' || !Storage::exists($purchaseOrder->file_path)) {
            try {
                // Intentar regenerar el PDF
                \Log::info('Intentando regenerar PDF para orden #' . $purchaseOrder->id);
                $pdfPath = $this->pdfService->generatePdf($purchaseOrder);
                $purchaseOrder->update(['file_path' => $pdfPath]);
                
                // Log de la regeneración exitosa
                \Log::info('PDF regenerado exitosamente para orden #' . $purchaseOrder->id . ' en ruta: ' . $pdfPath);
            } catch (\Exception $e) {
                // Log del error
                \Log::error('Error al regenerar el PDF para orden #' . $purchaseOrder->id . ': ' . $e->getMessage());
                
                return redirect()->back()->with('error', 'No se pudo generar el PDF de la orden de compra. Por favor contacte al administrador del sistema. Error: ' . $e->getMessage());
            }
        }
        
        // Verificar nuevamente si el archivo existe después de intentar regenerarlo
        if (!Storage::exists($purchaseOrder->file_path)) {
            \Log::error('Archivo PDF no encontrado en ruta: ' . $purchaseOrder->file_path);
            return redirect()->back()->with('error', 'El archivo PDF de la orden de compra no está disponible en la ruta especificada: ' . $purchaseOrder->file_path);
        }
        
        // Obtener el contenido del PDF
        try {
            $pdfContent = Storage::get($purchaseOrder->file_path);
            
            // Preparar respuesta para descarga
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="orden_' . $purchaseOrder->order_number . '.pdf"',
            ];
            
            return response($pdfContent, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error al descargar el PDF para orden #' . $purchaseOrder->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la descarga del PDF. Por favor contacte al administrador del sistema.');
        }
    }

    /**
     * Visualizar el PDF de la orden de compra en el navegador.
     */
    public function view(PurchaseOrder $purchaseOrder)
    {
        // Verificar si el archivo existe o si el file_path es el valor temporal
        if ($purchaseOrder->file_path === 'pending_generation' || !Storage::exists($purchaseOrder->file_path)) {
            try {
                // Intentar regenerar el PDF
                \Log::info('Intentando regenerar PDF para visualización de orden #' . $purchaseOrder->id);
                $pdfPath = $this->pdfService->generatePdf($purchaseOrder);
                $purchaseOrder->update(['file_path' => $pdfPath]);
                
                // Log de la regeneración exitosa
                \Log::info('PDF regenerado exitosamente para visualización de orden #' . $purchaseOrder->id . ' en ruta: ' . $pdfPath);
            } catch (\Exception $e) {
                // Log del error
                \Log::error('Error al regenerar el PDF para visualización de orden #' . $purchaseOrder->id . ': ' . $e->getMessage());
                
                return redirect()->back()->with('error', 'No se pudo generar el PDF de la orden de compra. Por favor contacte al administrador del sistema. Error: ' . $e->getMessage());
            }
        }
        
        // Verificar nuevamente si el archivo existe después de intentar regenerarlo
        if (!Storage::exists($purchaseOrder->file_path)) {
            \Log::error('Archivo PDF no encontrado en ruta para visualización: ' . $purchaseOrder->file_path);
            return redirect()->back()->with('error', 'El archivo PDF de la orden de compra no está disponible en la ruta especificada: ' . $purchaseOrder->file_path);
        }
        
        // Obtener el contenido del PDF para visualización
        try {
            $pdfContent = Storage::get($purchaseOrder->file_path);
            
            // Preparar respuesta para visualización en navegador
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="orden_' . $purchaseOrder->order_number . '.pdf"',
            ];
            
            return response($pdfContent, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error al visualizar el PDF para orden #' . $purchaseOrder->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la visualización del PDF. Por favor contacte al administrador del sistema.');
        }
    }

    /**
     * Aprobar una orden de compra.
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        // Solo administradores pueden aprobar órdenes de compra
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No tienes permisos para aprobar órdenes de compra.');
        }

        // Solo se pueden aprobar órdenes pendientes
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Solo se pueden aprobar órdenes pendientes.');
        }
        
        // Actualizar estado de la orden
        $purchaseOrder->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden de compra aprobada',
            'notes' => 'Orden ' . $purchaseOrder->order_number . ' aprobada por administrador',
        ]);
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'La orden de compra ha sido aprobada correctamente.');
    }

    /**
     * Enviar la orden de compra a contabilidad para su pago.
     */
    public function sendToAccounting(PurchaseOrder $purchaseOrder)
    {
        // Aprobar automáticamente la orden y enviarla a contabilidad
        $purchaseOrder->update([
            'status' => 'sent_to_accounting',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'sent_to_accounting_at' => now(),
            'sent_by' => Auth::id(),
        ]);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden aprobada y enviada a contabilidad',
            'notes' => 'Aprobada automáticamente y enviada para pago',
        ]);
        
        // Configurar correos según el entorno
        $isProduction = app()->environment('production');
        
        if ($isProduction) {
            $contabilidadEmail = 'contabilidad@tvs.edu.co';
            $asistenteContabilidadEmail = 'asistentecontabilidad@tvs.edu.co';
            $tesoreriaEmail = 'tesoreria@tvs.edu.co';
            $comprasEmail = 'compras@tvs.edu.co';
        } else {
            $contabilidadEmail = 'contabilidad@test.com';
            $asistenteContabilidadEmail = 'asistentecontabilidad@test.com';
            $tesoreriaEmail = 'tesoreria@test.com';
            $comprasEmail = 'compras@test.com';
        }
        
        // Enviar notificación a los cuatro departamentos
        try {
            // Enviar a contabilidad
            Notification::route('mail', $contabilidadEmail)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a asistente de contabilidad
            Notification::route('mail', $asistenteContabilidadEmail)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a tesorería
            Notification::route('mail', $tesoreriaEmail)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a compras
            Notification::route('mail', $comprasEmail)
                ->notify(new OrderCreated($purchaseOrder));
                
            \Log::info('Orden de compra aprobada y enviada a contabilidad (' . $contabilidadEmail . '), asistente contabilidad (' . $asistenteContabilidadEmail . '), tesorería (' . $tesoreriaEmail . ') y compras (' . $comprasEmail . ') - Orden #' . $purchaseOrder->order_number);
                
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'La orden de compra ha sido aprobada y enviada a contabilidad, asistente de contabilidad, tesorería y compras para su procesamiento.');
        } catch (\Exception $e) {
            \Log::error('Error al enviar la orden: ' . $e->getMessage());
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('warning', 'La orden ha sido aprobada pero hubo un error al enviar la notificación. Por favor contacte a los departamentos correspondientes.');
        }
    }

    /**
     * Enviar la orden de compra a Compras
     */
    public function sendToCompras(PurchaseOrder $purchaseOrder)
    {
        try {
            // Actualizar estado
            $purchaseOrder->update([
                'status' => 'sent_to_compras',
                'sent_by' => Auth::id(),
            ]);
            
            // Registrar en historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Orden enviada a Compras',
                'notes' => 'Enviada para gestión y procesamiento',
            ]);
            
            // Enviar notificación
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            $asistenteContabilidadEmail = config($configSource . '.sections.Asistente Contabilidad');
            
            $notification = Notification::route('mail', $comprasEmail);
            
            if ($asistenteContabilidadEmail) {
                $notification = $notification->route('mail', $asistenteContabilidadEmail);
            }
            
            $notification->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'compras'));
            
            return redirect()->back()->with('success', 'Orden de compra enviada a Compras exitosamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar orden a Compras: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar la orden a Compras.');
        }
    }

    /**
     * Enviar la orden de compra a Contabilidad
     */
    public function sendToContabilidad(PurchaseOrder $purchaseOrder)
    {
        try {
            // Actualizar estado
            $purchaseOrder->update([
                'status' => 'sent_to_accounting',
                'sent_to_accounting_at' => now(),
                'sent_by' => Auth::id(),
            ]);
            
            // Registrar en historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Orden enviada a Contabilidad',
                'notes' => 'Enviada para registro contable',
            ]);
            
            // Enviar notificación
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $contabilidadEmail = config($configSource . '.sections.Contabilidad');
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            $asistenteContabilidadEmail = config($configSource . '.sections.Asistente Contabilidad');
            
            $notification = Notification::route('mail', $contabilidadEmail);
            
            if ($asistenteContabilidadEmail) {
                $notification = $notification->route('mail', $asistenteContabilidadEmail);
            }
            
            $notification->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'contabilidad'));
            
            // Notificar también a compras para que esté al tanto
            Notification::route('mail', $comprasEmail)
                ->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'compras_copy'));
            
            return redirect()->back()->with('success', 'Orden de compra enviada a Contabilidad exitosamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar orden a Contabilidad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar la orden a Contabilidad.');
        }
    }

    /**
     * Enviar la orden de compra a Tesorería
     */
    public function sendToTesoreria(PurchaseOrder $purchaseOrder)
    {
        try {
            // Actualizar estado
            $purchaseOrder->update([
                'status' => 'sent_to_treasury',
                'sent_by' => Auth::id(),
            ]);
            
            // Registrar en historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Orden enviada a Tesorería',
                'notes' => 'Enviada para gestión de pagos',
            ]);
            
            // Enviar notificación
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $tesoreriaEmail = config($configSource . '.sections.Tesorería', 'tesoreria@test.com');
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            Notification::route('mail', $tesoreriaEmail)
                ->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'tesoreria'));
            
            // Notificar también a compras para que esté al tanto
            Notification::route('mail', $comprasEmail)
                ->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'compras_copy'));
            
            return redirect()->back()->with('success', 'Orden de compra enviada a Tesorería exitosamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar orden a Tesorería: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar la orden a Tesorería.');
        }
    }

    /**
     * Marcar la orden como pagada.
     */
    public function markAsPaid(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Validar datos
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string|max:255',
            'payment_receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);
        
        try {
            // Procesar la subida del archivo
            $receiptPath = null;
            if ($request->hasFile('payment_receipt') && $request->file('payment_receipt')->isValid()) {
                $file = $request->file('payment_receipt');
                $filename = 'comprobante_pago_' . $purchaseOrder->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $receiptPath = $file->storeAs('purchase-orders/payment-receipts', $filename, 'public');
            }
            
            // Actualizar estado de la orden
            $purchaseOrder->update([
                'status' => 'paid',
                'payment_date' => $validated['payment_date'],
                'payment_reference' => $validated['payment_reference'],
                'payment_receipt_path' => $receiptPath,
            ]);
            
            // Registrar en historial
            $historyNotes = 'Fecha de pago: ' . $validated['payment_date'];
            if ($validated['payment_reference']) {
                $historyNotes .= ' - Referencia: ' . $validated['payment_reference'];
            }
            if ($receiptPath) {
                $historyNotes .= ' - Comprobante adjuntado';
            }
            
            RequestHistory::create([
                'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Orden pagada',
                'notes' => $historyNotes,
            ]);
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'La orden de compra ha sido marcada como pagada y el comprobante ha sido guardado.');
                
        } catch (\Exception $e) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Ha ocurrido un error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar una orden de compra.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Solo se pueden cancelar órdenes que no estén pagadas
        if ($purchaseOrder->status === 'paid') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede cancelar una orden que ya ha sido pagada.');
        }
        
        // Validar datos
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10',
        ]);
        
        // Actualizar estado de la orden
        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden cancelada',
            'notes' => $validated['cancellation_reason'],
        ]);
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'La orden de compra ha sido cancelada.');
    }

    /**
     * Eliminar una orden de compra.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Solo administradores pueden eliminar órdenes de compra
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No tienes permisos para eliminar órdenes de compra.');
        }

        // Se pueden eliminar órdenes pendientes o aprobadas (solo admin)
        if (!in_array($purchaseOrder->status, ['pending', 'approved'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede eliminar una orden que ya ha sido procesada o enviada.');
        }
        
        // Registrar en historial antes de eliminar
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden de compra eliminada',
            'notes' => 'Orden ' . $purchaseOrder->order_number . ' eliminada por el administrador',
        ]);
        
        // Eliminar la orden de compra
        $purchaseOrder->delete();
        
        return redirect()->route('purchase-orders.index')
            ->with('success', 'La orden de compra ha sido eliminada correctamente.');
    }

    /**
     * Descargar el comprobante de pago de una orden.
     */
    public function downloadPaymentReceipt(PurchaseOrder $purchaseOrder)
    {
        // Verificar que la orden esté pagada y tenga comprobante
        if ($purchaseOrder->status !== 'paid' || !$purchaseOrder->payment_receipt_path) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Esta orden no tiene un comprobante de pago disponible.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($purchaseOrder->payment_receipt_path)) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'El archivo del comprobante de pago no se encuentra disponible.');
        }

        // Generar nombre del archivo para descarga
        $fileExtension = pathinfo($purchaseOrder->payment_receipt_path, PATHINFO_EXTENSION);
        $downloadName = 'Comprobante_Pago_Orden_' . $purchaseOrder->order_number . '.' . $fileExtension;

        // Descargar el archivo
        return Storage::disk('public')->download($purchaseOrder->payment_receipt_path, $downloadName);
    }
}
