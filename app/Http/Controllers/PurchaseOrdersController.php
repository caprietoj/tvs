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
            ->where('status', 'approved')
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
        if ($purchaseRequest->status !== 'approved') {
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
            'total_amount' => 'required|numeric|min:0',
            'additional_items' => 'nullable|array',
        ]);
        
        // Obtener la solicitud de compra
        $purchaseRequest = PurchaseRequest::findOrFail($purchaseRequestId);
        
        // Verificar que la solicitud esté aprobada
        if ($purchaseRequest->status !== 'approved') {
            return redirect()->route('purchase-orders.index')->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
        }
        
        // Verificar que no exista una orden para esta solicitud
        if (PurchaseOrder::where('purchase_request_id', $purchaseRequestId)->exists()) {
            return redirect()->route('purchase-orders.index')->with('error', 'Ya existe una orden de compra para esta solicitud.');
        }
        
        // Procesar items adicionales
        $additionalItems = [];
        if ($request->has('additional_items') && is_array($request->additional_items)) {
            foreach ($request->additional_items as $key => $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && isset($item['price'])) {
                    $quantity = floatval($item['quantity']);
                    $price = floatval($item['price']);
                    $total = $quantity * $price;
                    
                    $additionalItems[] = [
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit' => $item['unit'] ?? 'Unidad',
                        'price' => $price,
                        'total' => $total
                    ];
                }
            }
        }
        
        // Calcular subtotal, IVA y total
        $quotationAmount = $purchaseRequest->selectedQuotation ? $purchaseRequest->selectedQuotation->total_amount : 0;
        
        $additionalItemsTotal = 0;
        foreach ($additionalItems as $item) {
            $additionalItemsTotal += $item['total'];
        }
        
        $subtotal = $quotationAmount + $additionalItemsTotal;
        $includesIva = $request->has('apply_iva');
        $ivaAmount = $includesIva ? $subtotal * 0.19 : 0;
        $total = $subtotal + $ivaAmount;
        
        try {
            DB::beginTransaction();
            
            // Crear la orden de compra
            $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 3, '0', STR_PAD_LEFT);
            
            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'purchase_request_id' => $purchaseRequestId,
                'provider_id' => $request->provider_id,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(), // Agregamos el campo created_by
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
            
            // Actualizar el estado de la solicitud
            $purchaseRequest->update(['status' => 'in_process']);
            
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
        // Solo se pueden editar órdenes pendientes
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede editar una orden que ya ha sido procesada.');
        }
        
        return view('purchase-orders.edit', compact('purchaseOrder'));
    }

    /**
     * Actualizar una orden de compra.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Solo se pueden actualizar órdenes pendientes
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'No se puede editar una orden que ya ha sido procesada.');
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
     * Enviar la orden de compra a contabilidad para su pago.
     */
    public function sendToAccounting(PurchaseOrder $purchaseOrder)
    {
        // Actualizar estado de la orden
        $purchaseOrder->update([
            'status' => 'sent_to_accounting',
            'sent_to_accounting_at' => now(),
            'sent_by' => Auth::id(),
        ]);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden enviada a contabilidad',
            'notes' => 'Enviada para pago',
        ]);
        
        // Verificar si es una solicitud de fotocopias o materiales
        $purchaseRequest = $purchaseOrder->purchaseRequest;
        $isPhotocopiesOrMaterials = $purchaseRequest->isCopiesRequest() || $purchaseRequest->isMaterialsRequest();
        
        // Enviar notificación según el tipo de solicitud
        try {
            if ($isPhotocopiesOrMaterials) {
                // Para fotocopias y materiales, solo enviar a compras
                Notification::route('mail', 'compras@tvs.edu.co')
                    ->notify(new OrderCreated($purchaseOrder));
                    
                \Log::info('Orden de ' . ($purchaseRequest->isCopiesRequest() ? 'fotocopias' : 'materiales') . 
                          ' enviada solo a compras (no a contabilidad) - Orden #' . $purchaseOrder->order_number);
            } else {
                // Para órdenes de compra normales, enviar a contabilidad y compras
                Notification::route('mail', 'contabilidad@tvs.edu.co')
                    ->route('mail', 'compras@tvs.edu.co')
                    ->notify(new OrderCreated($purchaseOrder));
                    
                \Log::info('Orden de compra normal enviada a contabilidad y compras - Orden #' . $purchaseOrder->order_number);
            }
                
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'La orden de compra ha sido enviada para su procesamiento.');
        } catch (\Exception $e) {
            \Log::error('Error al enviar la orden: ' . $e->getMessage());
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('warning', 'La orden ha sido procesada pero hubo un error al enviar la notificación. Por favor contacte al departamento correspondiente.');
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
            'payment_reference' => 'required|string|max:255',
        ]);
        
        // Actualizar estado de la orden
        $purchaseOrder->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
            'payment_reference' => $validated['payment_reference'],
        ]);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Orden pagada',
            'notes' => 'Referencia de pago: ' . $validated['payment_reference'],
        ]);
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'La orden de compra ha sido marcada como pagada.');
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
}
