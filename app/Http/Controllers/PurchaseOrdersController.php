<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\RequestHistory;
use App\Models\PurchaseOrder;
use App\Models\Proveedor;
use App\Notifications\OrderCreated;
use App\Services\PurchaseOrderPdfService;
use App\Helpers\BudgetHelper;
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
        $orders = PurchaseOrder::with(['purchaseRequest', 'purchaseRequest.user', 'provider', 'viewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Obtener las solicitudes aprobadas pendientes de generar órdenes de compra
        $approvedRequests = PurchaseRequest::with(['selectedQuotation', 'user', 'approver', 'quotationItemSelections'])
            ->whereIn('status', ['approved', 'in_process'])
            ->where(function($query) {
                // Solicitudes con cotización seleccionada tradicional
                $query->where('selected_quotation_id', '!=', null)
                      // O solicitudes con selección mixta completa
                      ->orWhereHas('quotationItemSelections')
                      // O servicios sin cotización aprobados
                      ->orWhere(function($subQuery) {
                          $subQuery->where('type', 'services')
                                   ->where('service_type', 'no_quotation');
                      });
            })
            ->get()
            ->filter(function($request) {
                // Para selecciones mixtas, verificar si faltan órdenes por proveedores
                $hasMixedSelection = $request->quotationItemSelections()->exists();
                
                if ($hasMixedSelection) {
                    // Obtener proveedores con items seleccionados
                    $selections = $request->quotationItemSelections()->with('quotation')->get();
                    $providersWithSelections = $selections->groupBy('quotation.provider_name')->keys();
                    
                    // Obtener proveedores que ya tienen órdenes
                    $providersWithOrders = $request->purchaseOrders()
                        ->whereNull('deleted_at')
                        ->whereHas('provider')
                        ->get()
                        ->pluck('provider.nombre')
                        ->filter();
                    
                    // Mostrar si aún faltan proveedores por procesar
                    return $providersWithSelections->diff($providersWithOrders)->isNotEmpty();
                } else {
                    // Para cotizaciones tradicionales y servicios, excluir si ya tiene orden
                    return !$request->purchaseOrders()->whereNull('deleted_at')->exists();
                }
            })
            ->sortByDesc('approval_date');
            
        return view('purchase-orders.index', compact('orders', 'approvedRequests'));
    }

    /**
     * Mostrar formulario para crear una nueva orden de compra.
     */
    public function create(PurchaseRequest $purchaseRequest)
    {
        Log::info('=== CREATE METHOD CALLED ===', [
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Verificar que la solicitud esté aprobada
        if (!in_array($purchaseRequest->status, ['approved', 'in_process'])) {
            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('error', 'Solo se pueden generar órdenes de compra para solicitudes aprobadas.');
        }
        
        // Verificar que tenga una cotización seleccionada, selección mixta, o sea un servicio sin cotización
        $hasSelectedQuotation = $purchaseRequest->selected_quotation_id !== null;
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        $isNoQuotationService = $purchaseRequest->isNoQuotationService();
        
        if (!$hasSelectedQuotation && !$hasMixedSelection && !$isNoQuotationService) {
            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('error', 'La solicitud no tiene una cotización seleccionada, selección mixta, ni es un servicio sin cotización.');
        }
        
        Log::info('Redirigiendo a interfaz de creación manual', [
            'has_selected_quotation' => $hasSelectedQuotation,
            'has_mixed_selection' => $hasMixedSelection,
            'is_no_quotation_service' => $isNoQuotationService
        ]);
        
        // Redirigir SIEMPRE a la interfaz de creación manual apropiada
        return $this->showOrderCreationInterface(request(), $purchaseRequest);
    }

    /**
     * Guardar una nueva orden de compra.
     */
    public function store(Request $request, PurchaseRequest $purchaseRequest)
    {
        Log::info('=== STORE METHOD CALLED ===', [
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            Log::info('Iniciando creación de orden de compra', [
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => auth()->id()
            ]);
            
            // Obtener la solicitud de compra - ya tenemos el modelo inyectado
            $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
            $isNoQuotationService = $purchaseRequest->isNoQuotationService();
            
            Log::info('Solicitud encontrada y analizada', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_type' => $purchaseRequest->type,
                'service_type' => $purchaseRequest->service_type,
                'has_mixed_selection' => $hasMixedSelection,
                'has_selected_quotation' => $purchaseRequest->selected_quotation_id !== null,
                'is_no_quotation_service' => $isNoQuotationService,
                'request_status' => $purchaseRequest->status
            ]);
            
            // Validación condicional
            $validationRules = [
                'payment_terms' => 'required|string|max:255',
                'delivery_date' => 'required|date',
                'observations' => 'nullable|string',
            ];
            
            // Solo requerir provider_id para cotizaciones tradicionales (NO para servicios sin cotización ni selección mixta)
            if (!$hasMixedSelection && !$isNoQuotationService) {
                $validationRules['provider_id'] = 'required|exists:proveedors,id';
            }
            
            $request->validate($validationRules);
            
            Log::info('Validación completada exitosamente');
            
            // Verificar que la solicitud esté aprobada
            if (!in_array($purchaseRequest->status, ['approved', 'in_process'])) {
                Log::warning('Solicitud no está aprobada', [
                    'status' => $purchaseRequest->status,
                    'expected' => ['approved', 'in_process']
                ]);
                return redirect()->route('purchase-orders.index')->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
            }

            // Redirigir SIEMPRE a la interfaz de creación manual
            return $this->showOrderCreationInterface($request, $purchaseRequest);
            
            // Verificar que no exista una orden para esta solicitud
            $existingOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->exists();
            if ($existingOrder) {
                Log::warning('Ya existe una orden para esta solicitud', [
                    'purchase_request_id' => $purchaseRequest->id
                ]);
                return redirect()->route('purchase-orders.index')->with('error', 'Ya existe una orden de compra para esta solicitud.');
            }

            Log::info('Verificaciones previas completadas, continuando con creación...');
            
            // Verificar que tenga una cotización seleccionada, selección mixta, o sea un servicio sin cotización
            $hasSelectedQuotation = $purchaseRequest->selected_quotation_id && $purchaseRequest->selectedQuotation;
            $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
            $isNoQuotationService = $purchaseRequest->isNoQuotationService();
            
            if (!$hasSelectedQuotation && !$hasMixedSelection && !$isNoQuotationService) {
                Log::warning('Solicitud no cumple con los requisitos', [
                    'has_selected_quotation' => $hasSelectedQuotation,
                    'has_mixed_selection' => $hasMixedSelection,
                    'is_no_quotation_service' => $isNoQuotationService
                ]);
                return redirect()->route('purchase-orders.index')->with('error', 'La solicitud no tiene una cotización seleccionada válida, selección mixta, ni es un servicio sin cotización.');
            }

            Log::info('Calculando precios y totales...');

            // Obtener los datos de precio
            if ($isNoQuotationService) {
                // Para servicios sin cotización, usar los datos del presupuesto
                $total = $purchaseRequest->service_budget ?? 0;
                $subtotal = $total / 1.19; // Asumir IVA incluido
                $ivaAmount = $total - $subtotal;
                $includesIva = true;
                $additionalItems = [];
                
                Log::info('Calculando precios para servicio sin cotización', [
                    'service_budget' => $purchaseRequest->service_budget,
                    'total' => $total,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount
                ]);
            } elseif ($hasMixedSelection) {
                // Para selección mixta, calcular totales
                $total = $purchaseRequest->quotationItemSelections()->sum('total_price');
                $subtotal = $total / 1.19; // Asumir IVA incluido
                $ivaAmount = $total - $subtotal;
                $includesIva = true;
                $additionalItems = [];
            } else {
                // Para cotización única tradicional
                $selectedQuotation = $purchaseRequest->selectedQuotation;
                $total = $selectedQuotation->total_amount;
                $subtotal = $selectedQuotation->subtotal ?? $selectedQuotation->total_amount;
                $ivaAmount = $selectedQuotation->iva_amount ?? 0;
                $includesIva = $selectedQuotation->includes_iva ?? false;
                $additionalItems = $selectedQuotation->additional_items ?? [];
            }
            
            DB::beginTransaction();
            
            // Determinar el proveedor según el tipo de solicitud
            $providerId = $request->provider_id;
            
            if ($isNoQuotationService) {
                // Para servicios sin cotización, crear o buscar el proveedor ingresado manualmente
                $provider = \App\Models\Proveedor::where('nombre', $purchaseRequest->provider_name)->first();
                
                if (!$provider) {
                    $provider = \App\Models\Proveedor::create([
                        'nombre' => $purchaseRequest->provider_name,
                        'nit' => $purchaseRequest->provider_nit ?? 'Sin NIT',
                        'email' => $purchaseRequest->provider_email ?? 'sin-email@proveedor.com',
                        'telefono' => $purchaseRequest->provider_contact ?? 'Sin teléfono',
                        'direccion' => 'Por definir',
                        'persona_contacto' => $purchaseRequest->provider_contact ?? 'Sin contacto',
                        'ciudad' => 'Por definir',
                        'servicio_producto' => $purchaseRequest->service_type ?? 'Servicio'
                    ]);
                }
                $providerId = $provider->id;
                
                Log::info('Proveedor para servicio sin cotización', [
                    'provider_id' => $providerId,
                    'provider_name' => $provider->nombre
                ]);
                
                // Usar prefijo especial para servicios
                $orderNumber = 'ORD-SV-' . str_pad($purchaseRequest->id, 4, '0', STR_PAD_LEFT);
            } else {
                // Para cotizaciones normales
                $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 3, '0', STR_PAD_LEFT);
            }
            
            // Preparar observaciones
            $observations = $request->observations;
            if ($isNoQuotationService) {
                $observations = 'Servicio sin cotización - ' . $purchaseRequest->no_quotation_reason;
                if ($request->observations) {
                    $observations .= ' | ' . $request->observations;
                }
            }
            
            Log::info('Creando orden de compra', [
                'order_number' => $orderNumber,
                'provider_id' => $providerId,
                'total_amount' => $total,
                'observations' => $observations
            ]);
            
            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $providerId,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(),
                'order_date' => $request->order_date ?? now()->toDateString(),
                'payment_terms' => $request->payment_terms ?? ($isNoQuotationService ? 'Contado' : 'A definir'),
                'delivery_date' => $request->delivery_date ?? now()->addDays($isNoQuotationService ? 30 : 15),
                'observations' => $observations,
                'total_amount' => $total,
                'file_path' => 'pending_generation',
                'status' => 'pending',
                'additional_items' => $additionalItems,
                'includes_iva' => $includesIva,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
            ]);
            
            Log::info('Orden de compra creada exitosamente', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);
            
            // Generar PDF
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($order);
            
            // Actualizar la ruta del archivo
            $order->update(['file_path' => $pdfPath]);
            
            // NO cambiar el estado de la solicitud - debe permanecer como 'approved'
            // La solicitud ya fue aprobada y la orden de compra es el resultado de esa aprobación
            
            DB::commit();
            
            Log::info('Proceso completado exitosamente, redirigiendo a la orden creada');
            
            return redirect()->route('purchase-orders.show', $order->id)->with('success', 'Orden de compra generada correctamente.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear orden de compra: ' . $e->getMessage(), [
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
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
        // Solo administradores y personal de compras pueden editar órdenes de compra
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
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
        // Solo administradores y personal de compras pueden actualizar órdenes de compra
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
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
        
        // Regenerar PDF automáticamente después de editar (solo si no se subió archivo manual)
        if (!$request->hasFile('order_file')) {
            try {
                // FILTRAR POR PROVEEDOR PARA ÓRDENES DE SELECCIÓN MIXTA
                $providerSelections = null;
                
                \Log::info('=== UPDATE PDF REGENERATION START ===', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'provider_id' => $purchaseOrder->provider_id,
                    'request_type' => $purchaseOrder->purchaseRequest->type ?? 'unknown',
                    'has_quotation_item_selections' => $purchaseOrder->purchaseRequest->quotationItemSelections()->exists(),
                    'quotation_item_selections_count' => $purchaseOrder->purchaseRequest->quotationItemSelections()->count()
                ]);

                // Verificar si es una orden de selección mixta
                $hasMixedSelections = $purchaseOrder->purchaseRequest->quotationItemSelections()->exists();
                
                if ($hasMixedSelections) {
                    \Log::info('UPDATE PDF - MIXED SELECTION DETECTED - Filtering by provider', [
                        'provider_id' => $purchaseOrder->provider_id,
                        'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                    ]);

                    // Obtener todas las selecciones
                    $allSelections = $purchaseOrder->purchaseRequest->quotationItemSelections;
                    \Log::info('UPDATE PDF - Total selections found', ['count' => $allSelections->count()]);

                    // Filtrar solo las selecciones del proveedor de esta orden
                    $providerSelections = $allSelections->filter(function ($selection) use ($purchaseOrder) {
                        $match = $selection->quotation->provider_name == $purchaseOrder->provider->nombre;
                        \Log::info('UPDATE PDF - Selection filter', [
                            'selection_id' => $selection->id,
                            'selection_provider_name' => $selection->quotation->provider_name,
                            'order_provider_name' => $purchaseOrder->provider->nombre,
                            'matches' => $match
                        ]);
                        return $match;
                    });

                    \Log::info('UPDATE PDF - Filtered selections for PDF generation', [
                        'filtered_count' => $providerSelections->count(),
                        'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                    ]);
                }
                
                $pdfService = app(PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($purchaseOrder, $providerSelections);
                $purchaseOrder->update(['file_path' => $pdfPath]);
                
                \Log::info('PDF regenerado automáticamente después de editar orden', [
                    'order_id' => $purchaseOrder->id,
                    'pdf_path' => $pdfPath
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al regenerar PDF después de editar orden: ' . $e->getMessage(), [
                    'order_id' => $purchaseOrder->id
                ]);
                // No fallar la actualización por error en PDF, solo registrar el error
            }
        }
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Orden de compra actualizada exitosamente.');
    }

    /**
     * Generar el PDF de la orden de compra.
     */
    public function generatePdf(PurchaseOrder $purchaseOrder)
    {
        try {
            // FILTRAR POR PROVEEDOR PARA ÓRDENES DE SELECCIÓN MIXTA
            $providerSelections = null;
            
            \Log::info('=== GENERATE PDF START ===', [
                'purchase_order_id' => $purchaseOrder->id,
                'provider_id' => $purchaseOrder->provider_id,
                'request_type' => $purchaseOrder->purchaseRequest->type ?? 'unknown',
                'has_quotation_item_selections' => $purchaseOrder->purchaseRequest->quotationItemSelections()->exists(),
                'quotation_item_selections_count' => $purchaseOrder->purchaseRequest->quotationItemSelections()->count()
            ]);

            // Verificar si es una orden de selección mixta
            $hasMixedSelections = $purchaseOrder->purchaseRequest->quotationItemSelections()->exists();
            
            if ($hasMixedSelections) {
                \Log::info('MIXED SELECTION DETECTED BY QUOTATION_ITEM_SELECTIONS - Filtering by provider', [
                    'provider_id' => $purchaseOrder->provider_id,
                    'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                ]);

                // Obtener todas las selecciones
                $allSelections = $purchaseOrder->purchaseRequest->quotationItemSelections;
                \Log::info('Total selections found', ['count' => $allSelections->count()]);

                // Filtrar solo las selecciones del proveedor de esta orden
                $providerSelections = $allSelections->filter(function ($selection) use ($purchaseOrder) {
                    $match = $selection->quotation->provider_name == $purchaseOrder->provider->nombre;
                    \Log::info('Selection filter', [
                        'selection_id' => $selection->id,
                        'selection_provider_name' => $selection->quotation->provider_name,
                        'order_provider_name' => $purchaseOrder->provider->nombre,
                        'matches' => $match
                    ]);
                    return $match;
                });

                \Log::info('Filtered selections for PDF generation', [
                    'filtered_count' => $providerSelections->count(),
                    'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                ]);
            }

            $pdfPath = $this->pdfService->generatePdf($purchaseOrder, $providerSelections);
            $purchaseOrder->update(['file_path' => $pdfPath]);
            
            \Log::info('PDF generated successfully', ['pdf_path' => $pdfPath]);
            
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
        if ($purchaseOrder->file_path === 'pending_generation' || !Storage::disk('public')->exists($purchaseOrder->file_path)) {
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
        if (!Storage::disk('public')->exists($purchaseOrder->file_path)) {
            \Log::error('Archivo PDF no encontrado en ruta: ' . $purchaseOrder->file_path);
            return redirect()->back()->with('error', 'El archivo PDF de la orden de compra no está disponible en la ruta especificada: ' . $purchaseOrder->file_path);
        }
        
        // Obtener el contenido del PDF
        try {
            $pdfContent = Storage::disk('public')->get($purchaseOrder->file_path);
            
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
        if ($purchaseOrder->file_path === 'pending_generation' || !Storage::disk('public')->exists($purchaseOrder->file_path)) {
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
        if (!Storage::disk('public')->exists($purchaseOrder->file_path)) {
            \Log::error('Archivo PDF no encontrado en ruta para visualización: ' . $purchaseOrder->file_path);
            return redirect()->back()->with('error', 'El archivo PDF de la orden de compra no está disponible en la ruta especificada: ' . $purchaseOrder->file_path);
        }
        
        // Obtener el contenido del PDF para visualización
        try {
            $pdfContent = Storage::disk('public')->get($purchaseOrder->file_path);
            
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
            $emailTestService = new \App\Services\EmailTestModeService();
            
            // Enviar a contabilidad
            $interceptedContabilidad = $emailTestService->interceptEmail($contabilidadEmail, 'Contabilidad');
            Notification::route('mail', $interceptedContabilidad)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a asistente de contabilidad
            $interceptedAsistente = $emailTestService->interceptEmail($asistenteContabilidadEmail, 'Contabilidad');
            Notification::route('mail', $interceptedAsistente)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a tesorería
            $interceptedTesoreria = $emailTestService->interceptEmail($tesoreriaEmail, 'Tesorería');
            Notification::route('mail', $interceptedTesoreria)
                ->notify(new OrderCreated($purchaseOrder));
                
            // Enviar a compras
            $interceptedCompras = $emailTestService->interceptEmail($comprasEmail, 'Compras');
            Notification::route('mail', $interceptedCompras)
                ->notify(new OrderCreated($purchaseOrder));
                
            \Log::info('Orden de compra aprobada y enviada (interceptado por EmailTestModeService) a contabilidad (' . $interceptedContabilidad . '), asistente contabilidad (' . $interceptedAsistente . '), tesorería (' . $interceptedTesoreria . ') y compras (' . $interceptedCompras . ') - Orden #' . $purchaseOrder->order_number);
                
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
            
            $emailTestService = new \App\Services\EmailTestModeService();
            $interceptedCompras = $emailTestService->interceptEmail($comprasEmail, 'Compras');
            
            $notification = Notification::route('mail', $interceptedCompras);
            
            if ($asistenteContabilidadEmail) {
                $interceptedAsistente = $emailTestService->interceptEmail($asistenteContabilidadEmail, 'Contabilidad');
                $notification = $notification->route('mail', $interceptedAsistente);
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
            
            $emailTestService = new \App\Services\EmailTestModeService();
            $interceptedContabilidad = $emailTestService->interceptEmail($contabilidadEmail, 'Contabilidad');
            
            $notification = Notification::route('mail', $interceptedContabilidad);
            
            if ($asistenteContabilidadEmail) {
                $interceptedAsistente = $emailTestService->interceptEmail($asistenteContabilidadEmail, 'Contabilidad');
                $notification = $notification->route('mail', $interceptedAsistente);
            }
            
            $notification->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'contabilidad'));
            
            // Notificar también a compras para que esté al tanto
            $interceptedCompras = $emailTestService->interceptEmail($comprasEmail, 'Compras');
            Notification::route('mail', $interceptedCompras)
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
            
            $emailTestService = new \App\Services\EmailTestModeService();
            $interceptedTesoreria = $emailTestService->interceptEmail($tesoreriaEmail, 'Tesorería');
            $interceptedCompras = $emailTestService->interceptEmail($comprasEmail, 'Compras');
            
            Notification::route('mail', $interceptedTesoreria)
                ->notify(new \App\Notifications\PurchaseOrderSent($purchaseOrder, 'tesoreria'));
            
            // Notificar también a compras para que esté al tanto
            Notification::route('mail', $interceptedCompras)
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
        // Solo administradores y personal de compras pueden eliminar órdenes de compra
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
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

    /**
     * Regenerar completamente una orden de compra.
     * Recalcula toda la información desde la solicitud original hasta la orden final.
     * Solo disponible para administradores.
     */
    /**
     * Mostrar formulario para editar el PDF de una orden de compra (solo admin)
     */
    public function editPdf(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para editar PDFs de órdenes de compra.');
        }

        // Cargar relaciones necesarias
        $purchaseOrder->load([
            'purchaseRequest.user',
            'purchaseRequest.selectedQuotation.quotationItemSelections',
            'purchaseRequest.quotationItemSelections.quotation',
            'purchaseRequest.approver',
            'provider'
        ]);

        // CORRECCIÓN CRÍTICA: Filtrar selecciones mixtas por proveedor específico
        $purchaseRequest = $purchaseOrder->purchaseRequest;
        $providerSpecificSelections = collect();
        
        if ($purchaseRequest && $purchaseRequest->quotationItemSelections()->exists()) {
            // Para órdenes de selección mixta, filtrar por proveedor específico
            $providerSpecificSelections = $purchaseRequest->quotationItemSelections()
                ->whereHas('quotation', function($query) use ($purchaseOrder) {
                    $query->where('provider_name', $purchaseOrder->provider->nombre);
                })
                ->with('quotation')
                ->get();
            
            // CORRECCIÓN: Obtener precios reales de las cotizaciones originales
            $providerSpecificSelections = $providerSpecificSelections->map(function($selection) {
                if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                    $prices = $selection->quotation->original_item_prices;
                    $realPrice = null;

                    // 1) Intentar por índice exacto
                    if (isset($prices[$selection->item_index])) {
                        $realPrice = $prices[$selection->item_index];
                        Log::info('✅ Precio por índice exacto', [
                            'item_index' => $selection->item_index,
                            'real_price' => $realPrice
                        ]);
                    } elseif (is_array($prices)) {
                        // 2) Fallback: usar array_values y tomar la posición
                        $values = array_values($prices);
                        if (isset($values[$selection->item_index])) {
                            $realPrice = $values[$selection->item_index];
                            Log::info('✅ Precio por posición en array_values', [
                                'item_index' => $selection->item_index,
                                'real_price' => $realPrice
                            ]);
                        }
                    }

                    if ($realPrice !== null) {
                        $selection->unit_price = $realPrice;
                        $selection->total_price = $realPrice * $selection->quantity;
                        Log::info('✅ PRECIO CORREGIDO', [
                            'item' => $selection->item_description,
                            'new_price' => $realPrice,
                            'quantity' => $selection->quantity,
                            'new_total' => $selection->total_price
                        ]);
                    } else {
                        Log::warning('⚠️ No se encontró precio específico para item, se mantiene unit_price existente', [
                            'item' => $selection->item_description,
                            'item_index' => $selection->item_index,
                            'existing_unit_price' => $selection->unit_price
                        ]);
                    }
                }
                return $selection;
            });
            
            Log::info('🎯 EDITPDF - Filtrando vista de edición', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
                'total_selections' => $purchaseRequest->quotationItemSelections()->count(),
                'filtered_selections' => $providerSpecificSelections->count()
            ]);
        }

        // Usar el alias 'order' para compatibilidad con la vista
        $order = $purchaseOrder;

        // Obtener opciones de presupuesto
        $budgetOptions = BudgetHelper::getBudgetOptions();

        return view('purchase-orders.edit-pdf-new', compact('order', 'budgetOptions', 'providerSpecificSelections'));
    }

    /**
     * Mostrar formulario mejorado para editar el PDF de una orden de compra (solo admin)
     * Vista que replica exactamente el formato del PDF con todos los impuestos
     */
    public function editPdfNew(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para editar PDFs de órdenes de compra.');
        }

        // Cargar relaciones necesarias
        $purchaseOrder->load([
            'purchaseRequest.user',
            'purchaseRequest.selectedQuotation.quotationItemSelections',
            'purchaseRequest.quotationItemSelections.quotation',
            'purchaseRequest.approver',
            'provider'
        ]);

        // RESTAURAR: Usar el sistema original pero con verificación de precios correctos
        $purchaseRequest = $purchaseOrder->purchaseRequest;
        $selectedQuotation = $purchaseRequest->selectedQuotation;
        
        // Verificar y registrar disponibilidad de precios originales
        if ($selectedQuotation && isset($selectedQuotation->original_item_prices)) {
            Log::critical('💰 PRECIOS ORIGINALES ENCONTRADOS', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
                'prices' => $selectedQuotation->original_item_prices
            ]);
        } else {
            Log::critical('❌ NO HAY PRECIOS ORIGINALES', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
            ]);
        }

        // Preparar items para la edición con precios correctos
        $editItems = [];
        
        // Si es una selección mixta, obtener solo items del proveedor específico
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        if ($hasMixedSelection) {
            $providerSelections = $purchaseRequest->quotationItemSelections()
                ->whereHas('quotation', function($query) use ($purchaseOrder) {
                    $query->where('provider_name', $purchaseOrder->provider->nombre);
                })
                ->with('quotation')
                ->get();
                
            foreach ($providerSelections as $selection) {
                // Obtener el precio real de la cotización original
                $realPrice = null;
                if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                    if (isset($selection->quotation->original_item_prices[$selection->item_index])) {
                        $realPrice = $selection->quotation->original_item_prices[$selection->item_index];
                    }
                }
                
                // Si no se encontró precio, usar el que tiene guardado
                if ($realPrice === null) {
                    $realPrice = $selection->unit_price ?? 0;
                }
                
                $editItems[] = [
                    'description' => $selection->item_description ?? '',
                    'quantity' => floatval($selection->quantity ?? 0),
                    'unit_price' => floatval($realPrice),
                    'total' => (floatval($realPrice) * floatval($selection->quantity ?? 0))
                ];
            }
            
            Log::critical('🧾 ITEMS MEZCLADOS PREPARADOS', [
                'items_count' => count($editItems),
                'sample' => array_slice($editItems, 0, 2)
            ]);
        }
        // Para órdenes normales, usar items del purchase request con precios de la cotización
        else {
            $purchaseItems = $purchaseRequest->purchase_items ?? [];
            if (is_string($purchaseItems)) {
                $purchaseItems = json_decode($purchaseItems, true) ?? [];
            }
            
            foreach ($purchaseItems as $index => $item) {
                $realPrice = 0;
                
                // Si hay cotización seleccionada, intentar obtener precio original
                if ($selectedQuotation && isset($selectedQuotation->original_item_prices)) {
                    $realPrice = $selectedQuotation->original_item_prices[$index] ?? 0;
                }
                
                $editItems[] = [
                    'description' => $item['description'] ?? '',
                    'quantity' => floatval($item['quantity'] ?? 0),
                    'unit_price' => floatval($realPrice),
                    'total' => (floatval($realPrice) * floatval($item['quantity'] ?? 0))
                ];
            }
            
            Log::critical('� ITEMS ESTÁNDAR PREPARADOS', [
                'items_count' => count($editItems),
                'sample' => array_slice($editItems, 0, 2)
            ]);
        }

        // Usar el alias 'order' para compatibilidad con la vista
        $order = $purchaseOrder;

        // Obtener opciones de presupuesto
        $budgetOptions = BudgetHelper::getBudgetOptions();

        // Usar la vista de edición tradicional pero con los items preparados con precios correctos
        return view('purchase-orders.edit-pdf-new', compact('order', 'budgetOptions', 'editItems', 'selectedQuotation'));
    }

    /**
     * Actualizar los datos del PDF de una orden de compra (solo admin)
     */
    public function updatePdf(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para editar PDFs de órdenes de compra.');
        }

        try {
            // FORZAR: Para órdenes con selección mixta, IGNORAR datos previos del PDF
            $purchaseRequest = $purchaseOrder->purchaseRequest;
            $hasMixedSelection = $purchaseRequest ? $purchaseRequest->quotationItemSelections()->exists() : false;
            
            // Siempre empezar con datos limpios para asegurar consistencia en los cálculos
            $currentCustomData = [];
            
            if ($hasMixedSelection) {
                Log::info('🔥 FORZANDO LIMPIEZA - Selección mixta detectada', [
                    'order' => $purchaseOrder->order_number,
                    'provider' => $purchaseOrder->provider->nombre,
                    'cleaning_previous_data' => true
                ]);
            } else {
                // Para órdenes normales, recuperar solo datos no relacionados con precios
                $savedCustomData = json_decode($purchaseOrder->pdf_custom_data ?? '{}', true);
                
                // Transferir solo datos no relacionados con cálculos
                $nonPriceFields = ['provider_name', 'provider_nit', 'provider_email', 
                                  'provider_phone', 'provider_address', 'provider_city', 
                                  'delivery_address', 'payment_method', 'budget',
                                  'observations', 'shared_budget_info'];
                
                foreach ($nonPriceFields as $field) {
                    if (isset($savedCustomData[$field])) {
                        $currentCustomData[$field] = $savedCustomData[$field];
                    }
                }
            }
            
            // Los items que llegan del formulario
            $itemsToSave = $request->items ?? [];
            
            // Cargar cotización seleccionada para tener acceso a los precios originales
            $purchaseOrder->purchaseRequest->load(['selectedQuotation']);
            $selectedQuotation = $purchaseOrder->purchaseRequest->selectedQuotation;
            
            // DIAGNÓSTICO EXTENSO: Analizar qué precios están disponibles
            Log::critical('🔍 ANÁLISIS DE PRECIOS PARA ORDEN #' . $purchaseOrder->order_number, [
                'provider' => $purchaseOrder->provider->nombre,
                'has_selectedQuotation' => ($selectedQuotation ? 'SI' : 'NO'),
                'has_original_item_prices' => ($selectedQuotation && isset($selectedQuotation->original_item_prices) ? 'SI' : 'NO'),
                'original_prices' => ($selectedQuotation && isset($selectedQuotation->original_item_prices) ? $selectedQuotation->original_item_prices : 'NO DISPONIBLE'),
                'first_items' => array_slice($itemsToSave, 0, 3)
            ]);
            
            // CORRECCIÓN CRÍTICA: FORZAR USO DE PRECIOS ORIGINALES DE LA COTIZACIÓN PARA EVITAR BUG DE $4.949
            if ($selectedQuotation && isset($selectedQuotation->original_item_prices)) {
                $originalPrices = $selectedQuotation->original_item_prices;
                
                Log::critical('🔒 UPDATEPDF - RESTAURANDO SISTEMA ORIGINAL', [
                    'order' => $purchaseOrder->order_number,
                    'original_prices_count' => count($originalPrices),
                    'items_count' => count($itemsToSave),
                    'original_prices' => $originalPrices
                ]);
                
                // Para cada item, usar SIEMPRE el precio original cuando esté disponible
                foreach ($itemsToSave as $index => &$item) {
                    if (isset($originalPrices[$index])) {
                        $oldPrice = $item['unit_price'];
                        // Convertir explícitamente a número para evitar errores
                        $item['unit_price'] = floatval($originalPrices[$index]);
                        // Asegurarnos de que la cantidad también sea numérica
                        $item['quantity'] = floatval($item['quantity']);
                        // Recalcular el total
                        $item['total'] = $item['unit_price'] * $item['quantity'];
                        
                        Log::critical('✅ UPDATEPDF - Precio original aplicado', [
                            'item' => $item['description'],
                            'old_price' => $oldPrice,
                            'original_price' => $item['unit_price'],
                            'quantity' => $item['quantity'],
                            'total' => $item['total']
                        ]);
                    } else {
                        Log::warning('⚠️ No se encontró precio original para el ítem #' . $index, [
                            'description' => $item['description'] ?? 'Sin descripción'
                        ]);
                    }
                }
            }
            
            if ($hasMixedSelection) {
                // Para órdenes mixtas: verificar que solo tengamos items del proveedor
                $providerSelections = $purchaseRequest->quotationItemSelections()
                    ->with('quotation')
                    ->whereHas('quotation', function($query) use ($purchaseOrder) {
                        $query->where('provider_name', $purchaseOrder->provider->nombre);
                    })
                    ->get();
                
                $expectedItemCount = $providerSelections->count();
                $receivedItemCount = count($itemsToSave);
                
                // FORZAR: Solo guardar los primeros N items que corresponden al proveedor
                $itemsToSave = array_slice($itemsToSave, 0, $expectedItemCount);
                
                Log::info('🎯 FORZANDO FILTRADO CORRECTO', [
                    'order' => $purchaseOrder->order_number,
                    'provider' => $purchaseOrder->provider->nombre,
                    'expected_items' => $expectedItemCount,
                    'received_items' => $receivedItemCount,
                    'final_items_saved' => count($itemsToSave)
                ]);
            }
            // CORRECCIÓN CRÍTICA: Usar la plantilla fija con precios corregidos
            Log::critical('🔧 APLICANDO SOLUCIÓN DE PRECIOS FIJOS', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
                'using_fixed_template' => true,
                'items_to_save' => count($itemsToSave)
            ]);
            
            // INTERCEPTAR: Si llegan más de 10 items, es sospechoso de ser error de selección mixta
            if (count($itemsToSave) > 10) {
                Log::warning('🚨 INTERCEPTANDO POSIBLE ERROR - Demasiados items detectados', [
                    'order' => $purchaseOrder->order_number,
                    'items_received' => count($itemsToSave),
                    'forcing_cleanup' => true
                ]);
                
                // VERIFICAR si es selección mixta por contenido
                $hasAromatica = false;
                $hasCafe = false;
                foreach ($itemsToSave as $item) {
                    if (isset($item['description'])) {
                        if (str_contains(strtolower($item['description']), 'aromática')) $hasAromatica = true;
                        if (str_contains(strtolower($item['description']), 'café')) $hasCafe = true;
                    }
                }
                
                if ($hasAromatica && $hasCafe && count($itemsToSave) >= 14) {
                    // ESTO ES DEFINITIVAMENTE EL ERROR - FORZAR LIMPIEZA
                    Log::error('🔥 ERROR CONFIRMADO - Forzando corrección inmediata', [
                        'order' => $purchaseOrder->order_number,
                        'detected_items' => count($itemsToSave),
                        'has_aromatica' => $hasAromatica,
                        'has_cafe' => $hasCafe
                    ]);
                    
                    // FORZAR: Solo los primeros 7 items
                    $itemsToSave = array_slice($itemsToSave, 0, 7);
                    
                    Log::info('✅ CORRECCIÓN FORZADA APLICADA', [
                        'order' => $purchaseOrder->order_number,
                        'items_after_correction' => count($itemsToSave)
                    ]);
                }
            }
            
            // Recalcular los totales e impuestos correctamente
            $rawSubtotal = floatval($request->subtotal);
            $rawTotal = floatval($request->total);
            $ivaRate = intval($request->iva_rate);
            $ipoconsumoRate = intval($request->ipoconsumo_rate);
            
            // Verificar si los datos enviados son consistentes o necesitan recálculo
            $calculatedTotal = $rawSubtotal;
            $ivaAmount = 0;
            $ipoconsumoAmount = 0;
            
            if ($ivaRate > 0) {
                $ivaAmount = round(($rawSubtotal * $ivaRate) / 100);
                $calculatedTotal += $ivaAmount;
            }
            
            if ($ipoconsumoRate > 0) {
                $ipoconsumoAmount = round(($rawSubtotal * $ipoconsumoRate) / 100);
                $calculatedTotal += $ipoconsumoAmount;
            }
            
            // Si hay inconsistencia entre el total calculado y el enviado, recalcular
            if (abs($calculatedTotal - $rawTotal) > 5) { // Permitir pequeña variación por redondeo
                Log::warning("Inconsistencia detectada en los cálculos de impuestos. Recalculando.", [
                    'order' => $purchaseOrder->order_number,
                    'input_subtotal' => $rawSubtotal,
                    'input_total' => $rawTotal,
                    'calculated_total' => $calculatedTotal,
                    'difference' => $calculatedTotal - $rawTotal
                ]);
                
                // Determinar el subtotal real a partir del total si el IVA es estándar
                if ($ivaRate == 19 && $ipoconsumoRate == 0) {
                    $correctedSubtotal = round($rawTotal / 1.19);
                    $correctedIvaAmount = $rawTotal - $correctedSubtotal;
                    
                    Log::info("Corrigiendo subtotal e IVA basado en el total", [
                        'original_subtotal' => $rawSubtotal,
                        'corrected_subtotal' => $correctedSubtotal,
                        'corrected_iva' => $correctedIvaAmount,
                        'total' => $rawTotal
                    ]);
                    
                    $rawSubtotal = $correctedSubtotal;
                    $ivaAmount = $correctedIvaAmount;
                }
            }
            
            // Preparar nuevos datos personalizados con cálculos corregidos
            $customData = array_merge($currentCustomData, [
                'provider_name' => $request->provider_name,
                'provider_nit' => $request->provider_nit,
                'provider_email' => $request->provider_email,
                'provider_phone' => $request->provider_phone,
                'provider_address' => $request->provider_address,
                'provider_city' => $request->provider_city,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'budget' => $request->budget,
                'iva_rate' => $ivaRate . '%',
                'iva_amount' => $ivaAmount,
                'ipoconsumo_rate' => $ipoconsumoRate . '%',
                'ipoconsumo_amount' => $ipoconsumoAmount,
                'subtotal' => $rawSubtotal,
                'total' => $rawTotal,
                'items' => $itemsToSave,
                'additional_items' => $request->additional_items ?? [],
                'observations' => $request->observations,
                'shared_budget_info' => $request->shared_budget_info,
                'edited_by' => auth()->user()->id,
                'edited_at' => now()->toISOString(),
            ]);

            // Actualizar la orden de compra
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->subtotal = $rawSubtotal;
            $purchaseOrder->iva_amount = $ivaAmount;
            $purchaseOrder->tax_amount = $ivaAmount + $ipoconsumoAmount;
            $purchaseOrder->total_amount = $rawTotal;
            $purchaseOrder->includes_iva = $ivaAmount > 0;
            $purchaseOrder->observations = $request->observations;
            $purchaseOrder->pdf_custom_data = json_encode($customData);
            
            if ($request->provider_id) {
                $purchaseOrder->provider_id = $request->provider_id;
            }
            
            $purchaseOrder->save();

            // Regenerar el PDF
            $pdfPath = $this->pdfService->generatePdf($purchaseOrder);
            $purchaseOrder->file_path = $pdfPath;
            $purchaseOrder->save();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'PDF de la orden de compra actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar PDF', [
                'order_id' => $purchaseOrder->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar el PDF: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function regeneratePdf(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No tienes permisos para regenerar órdenes de compra.');
        }

        try {
            // Cargar todas las relaciones necesarias para el recálculo completo
            $purchaseOrder->load([
                'purchaseRequest.user',
                'purchaseRequest.selectedQuotation',
                'purchaseRequest.quotationItemSelections.quotation',
                'purchaseRequest.approver',
                'provider'
            ]);

            $purchaseRequest = $purchaseOrder->purchaseRequest;
            
            if (!$purchaseRequest) {
                throw new \Exception('No se encontró la solicitud de compra asociada a esta orden.');
            }

            Log::info('Iniciando regeneración completa para orden: ' . $purchaseOrder->order_number, [
                'purchase_request_id' => $purchaseRequest->id,
                'purchase_request_number' => $purchaseRequest->request_number
            ]);

            // PASO 1: Recalcular impuestos y totales desde la solicitud original
            $this->recalculateOrderFromRequest($purchaseOrder, $purchaseRequest);

            // PASO 1.5: CORRECCIÓN CRÍTICA - Asegurarse de que la cotización original esté cargada
            $purchaseRequest->load(['selectedQuotation']);
            
            // Verificar si hay precios originales en la cotización y registrarlos
            if ($purchaseRequest->selectedQuotation && isset($purchaseRequest->selectedQuotation->original_item_prices)) {
                $originalPrices = $purchaseRequest->selectedQuotation->original_item_prices;
                Log::info('🔒 REGENERATE PDF - Precios originales disponibles', [
                    'order' => $purchaseOrder->order_number,
                    'prices_count' => count($originalPrices),
                    'first_prices' => array_slice($originalPrices, 0, 3)
                ]);
            }

            // PASO 2: Filtrar por proveedor para órdenes de selección mixta antes de generar PDF
            $providerSelections = null;
            $hasMixedSelections = $purchaseOrder->purchaseRequest->quotationItemSelections()->exists();
            
            if ($hasMixedSelections) {
                Log::info('REGENERATE PDF - MIXED SELECTION DETECTED - Filtering by provider', [
                    'provider_id' => $purchaseOrder->provider_id,
                    'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                ]);

                // Obtener todas las selecciones
                $allSelections = $purchaseOrder->purchaseRequest->quotationItemSelections;
                Log::info('REGENERATE PDF - Total selections found', ['count' => $allSelections->count()]);

                // Filtrar solo las selecciones del proveedor de esta orden
                $providerSelections = $allSelections->filter(function ($selection) use ($purchaseOrder) {
                    $match = $selection->quotation->provider_name == $purchaseOrder->provider->nombre;
                    Log::info('REGENERATE PDF - Selection filter', [
                        'selection_id' => $selection->id,
                        'selection_provider_name' => $selection->quotation->provider_name,
                        'order_provider_name' => $purchaseOrder->provider->nombre,
                        'matches' => $match
                    ]);
                    return $match;
                });

                Log::info('REGENERATE PDF - Filtered selections for PDF generation', [
                    'filtered_count' => $providerSelections->count(),
                    'provider_name' => $purchaseOrder->provider->nombre ?? 'unknown'
                ]);
            }

            // PASO 3: Generar nuevo PDF con los datos recalculados y filtrados
            $pdfPath = $this->pdfService->generatePdf($purchaseOrder, $providerSelections);

            // PASO 4: Actualizar la orden con la nueva información
            $purchaseOrder->file_path = $pdfPath;
            $purchaseOrder->updated_at = now();
            $purchaseOrder->save();

            Log::info('Regeneración completa exitosa para orden: ' . $purchaseOrder->order_number, [
                'pdf_path' => $pdfPath,
                'total_amount_recalculated' => $purchaseOrder->total_amount,
                'applied_taxes_recalculated' => $purchaseOrder->applied_taxes,
                'regenerated_by' => auth()->user()->id
            ]);

            return redirect()->route('purchase-orders.index')
                ->with('success', 'La orden ' . $purchaseOrder->order_number . ' ha sido completamente regenerada con todos los cálculos actualizados desde la solicitud ' . $purchaseRequest->request_number . '.');

        } catch (\Exception $e) {
            Log::error('Error en regeneración completa para orden: ' . $purchaseOrder->order_number, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->user()->id
            ]);

            return redirect()->route('purchase-orders.index')
                ->with('error', 'Error al regenerar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Recalcular completamente una orden de compra desde la solicitud original
     */
    private function recalculateOrderFromRequest(PurchaseOrder $purchaseOrder, $purchaseRequest)
    {
        Log::info('Recalculando orden desde solicitud', [
            'order_id' => $purchaseOrder->id,
            'request_id' => $purchaseRequest->id,
            'request_type' => $purchaseRequest->type
        ]);

        // Inicializar variables para el cálculo
        $subtotal = 0;
        $totalTaxes = 0;
        $appliedTaxes = [];
        $items = [];

        // Procesar según el tipo de solicitud
        if ($purchaseRequest->type === 'services') {
            // Para servicios, usar los datos de la solicitud directamente
            $this->processServiceRequest($purchaseRequest, $subtotal, $totalTaxes, $appliedTaxes, $items);
        } else {
            // Para productos, usar cotización seleccionada o selecciones mixtas
            if ($purchaseRequest->selectedQuotation) {
                $this->processSelectedQuotation($purchaseRequest->selectedQuotation, $subtotal, $totalTaxes, $appliedTaxes, $items);
            } elseif ($purchaseRequest->quotationItemSelections->count() > 0) {
                $this->processMixedSelections($purchaseRequest->quotationItemSelections, $subtotal, $totalTaxes, $appliedTaxes, $items);
            } else {
                throw new \Exception('No se encontró cotización seleccionada ni selecciones mixtas para la solicitud.');
            }
        }

        // Actualizar la orden con los valores recalculados
        $purchaseOrder->subtotal = $subtotal;
        $purchaseOrder->iva_amount = $totalTaxes;
        $purchaseOrder->total_amount = $subtotal + $totalTaxes;
        $purchaseOrder->includes_iva = $totalTaxes > 0;
        
        // Almacenar el desglose detallado de impuestos en additional_items si es necesario
        if (!empty($appliedTaxes)) {
            $currentAdditionalItems = $purchaseOrder->additional_items ?? [];
            $currentAdditionalItems['applied_taxes_detail'] = $appliedTaxes;
            $purchaseOrder->additional_items = $currentAdditionalItems;
        }
        
        // Actualizar información adicional desde la solicitud
        $purchaseOrder->observations = $purchaseRequest->description ?? $purchaseOrder->observations;

        Log::info('Recálculo completado', [
            'subtotal' => $subtotal,
            'tax_amount' => $totalTaxes,
            'total_amount' => $subtotal + $totalTaxes,
            'applied_taxes' => $appliedTaxes
        ]);
    }

    /**
     * Procesar solicitud de servicios
     */
    private function processServiceRequest($purchaseRequest, &$subtotal, &$totalTaxes, &$appliedTaxes, &$items)
    {
        Log::info('Procesando solicitud de servicios', ['request_id' => $purchaseRequest->id]);

        // Para servicios, el valor base puede estar en service_budget o amount
        $baseAmount = $purchaseRequest->service_budget ?? $purchaseRequest->amount ?? 0;
        $subtotal = $baseAmount;

        // Aplicar impuestos desde la configuración de la solicitud
        $serviceItems = $purchaseRequest->service_items ?? [];
        
        if (!empty($serviceItems) && is_array($serviceItems)) {
            foreach ($serviceItems as $item) {
                // Verificar si el item tiene impuestos configurados
                if (isset($item['taxes']) && is_array($item['taxes'])) {
                    foreach ($item['taxes'] as $tax) {
                        $taxName = $tax['name'] ?? 'IVA';
                        $taxRate = (float)($tax['rate'] ?? 0);
                        $taxAmount = ($baseAmount * $taxRate) / 100;
                        
                        if (!isset($appliedTaxes[$taxName])) {
                            $appliedTaxes[$taxName] = $this->createTaxRecord($taxName, $taxRate);
                        }
                        $appliedTaxes[$taxName]['amount'] += $taxAmount;
                        $totalTaxes += $taxAmount;
                    }
                }
            }
        } else {
            // Si no hay items específicos con impuestos, aplicar IVA por defecto del 19%
            $ivaRate = 19;
            $ivaAmount = ($baseAmount * $ivaRate) / 100;
            
            $appliedTaxes['IVA'] = $this->createTaxRecord('IVA', $ivaRate, $ivaAmount);
            $totalTaxes = $ivaAmount;
        }
        
        Log::info('Servicio procesado', [
            'base_amount' => $baseAmount,
            'total_taxes' => $totalTaxes,
            'applied_taxes' => $appliedTaxes
        ]);
    }

    /**
     * Procesar cotización seleccionada
     */
    private function processSelectedQuotation($quotation, &$subtotal, &$totalTaxes, &$appliedTaxes, &$items)
    {
        Log::info('Procesando cotización seleccionada', ['quotation_id' => $quotation->id]);

        // Usar additional_items que es donde se almacenan los items de la cotización
        $quotationItems = $quotation->additional_items ?? [];
        
        if (!empty($quotationItems) && is_array($quotationItems)) {
            foreach ($quotationItems as $item) {
                $quantity = (float)($item['quantity'] ?? 1);
                $unitPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                $itemSubtotal = $quantity * $unitPrice;
                $subtotal += $itemSubtotal;

                // Procesar impuestos del item si existen
                if (isset($item['taxes']) && is_array($item['taxes'])) {
                    foreach ($item['taxes'] as $tax) {
                        $taxName = $tax['name'] ?? 'IVA';
                        $taxRate = (float)($tax['rate'] ?? 0);
                        $taxAmount = ($itemSubtotal * $taxRate) / 100;
                        
                        if (!isset($appliedTaxes[$taxName])) {
                            $appliedTaxes[$taxName] = [
                                'name' => $taxName,
                                'rate' => $taxRate,
                                'amount' => 0
                            ];
                        }
                        $appliedTaxes[$taxName]['amount'] += $taxAmount;
                        $totalTaxes += $taxAmount;
                    }
                }
            }
        } else {
            // Si no hay items detallados, usar totales de la cotización
            $subtotal = $quotation->subtotal ?? $quotation->total_amount ?? 0;
            
            // Usar impuestos ya calculados en la cotización
            if ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) {
                $appliedTaxes['IVA 19%'] = $this->createTaxRecord('IVA', 19, $quotation->iva_19_amount);
                $totalTaxes += $quotation->iva_19_amount;
            }
            
            if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                $appliedTaxes['IVA 5%'] = $this->createTaxRecord('IVA', 5, $quotation->iva_5_amount);
                $totalTaxes += $quotation->iva_5_amount;
            }
            
            if ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) {
                $appliedTaxes['Impuesto al Consumo 8%'] = $this->createTaxRecord('Impuesto al Consumo', 8, $quotation->ipoconsumo_8_amount);
                $totalTaxes += $quotation->ipoconsumo_8_amount;
            }
            
            if ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0) {
                $appliedTaxes['Impuesto al Consumo 4%'] = $this->createTaxRecord('Impuesto al Consumo', 4, $quotation->ipoconsumo_4_amount);
                $totalTaxes += $quotation->ipoconsumo_4_amount;
            }
            
            // Si el subtotal es 0, calcular desde total menos impuestos
            if ($subtotal == 0 && $quotation->total_amount > 0) {
                $subtotal = $quotation->total_amount - $totalTaxes;
            }
        }
        
        Log::info('Cotización procesada', [
            'subtotal' => $subtotal,
            'total_taxes' => $totalTaxes,
            'applied_taxes' => $appliedTaxes
        ]);
    }

    /**
     * Procesar selecciones mixtas
     */
    private function processMixedSelections($selections, &$subtotal, &$totalTaxes, &$appliedTaxes, &$items)
    {
        Log::info('Procesando selecciones mixtas', ['selections_count' => $selections->count()]);

        foreach ($selections as $selection) {
            $itemSubtotal = $selection->quantity * $selection->unit_price;
            $subtotal += $itemSubtotal;

            // Para selecciones mixtas, los impuestos generalmente se aplican desde la cotización asociada
            if ($selection->quotation) {
                // Aplicar proporcionalmente los impuestos de la cotización
                $quotation = $selection->quotation;
                
                if ($quotation->includes_iva_19 && $quotation->iva_19_amount > 0) {
                    $taxRate = 19;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['IVA 19%'])) {
                        $appliedTaxes['IVA 19%'] = $this->createTaxRecord('IVA', $taxRate);
                    }
                    $appliedTaxes['IVA 19%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                    $taxRate = 5;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['IVA 5%'])) {
                        $appliedTaxes['IVA 5%'] = $this->createTaxRecord('IVA', $taxRate);
                    }
                    $appliedTaxes['IVA 5%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) {
                    $taxRate = 8;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['Impuesto al Consumo 8%'])) {
                        $appliedTaxes['Impuesto al Consumo 8%'] = $this->createTaxRecord('Impuesto al Consumo', $taxRate);
                    }
                    $appliedTaxes['Impuesto al Consumo 8%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0) {
                    $taxRate = 4;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['Impuesto al Consumo 4%'])) {
                        $appliedTaxes['Impuesto al Consumo 4%'] = $this->createTaxRecord('Impuesto al Consumo', $taxRate);
                    }
                    $appliedTaxes['Impuesto al Consumo 4%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
            } else {
                // Si no hay cotización asociada, aplicar IVA por defecto
                $taxRate = 19;
                $taxAmount = ($itemSubtotal * $taxRate) / 100;
                
                if (!isset($appliedTaxes['IVA 19%'])) {
                    $appliedTaxes['IVA 19%'] = $this->createTaxRecord('IVA', $taxRate);
                }
                $appliedTaxes['IVA 19%']['amount'] += $taxAmount;
                $totalTaxes += $taxAmount;
            }
        }
        
        Log::info('Selecciones mixtas procesadas', [
            'subtotal' => $subtotal,
            'total_taxes' => $totalTaxes,
            'applied_taxes' => $appliedTaxes
        ]);
    }

    /**
     * Mostrar interfaz para creación manual de órdenes de compra
     */
    private function showOrderCreationInterface(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Detectar tipo de solicitud y preparar datos apropiados
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        $hasSelectedQuotation = $purchaseRequest->selected_quotation_id && $purchaseRequest->selectedQuotation;
        $isNoQuotationService = $purchaseRequest->type === 'services' && !$hasSelectedQuotation && !$hasMixedSelection;

        if ($hasMixedSelection) {
            // Selección mixta - Múltiples proveedores
            $mixedSelections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
            $providerGroups = $mixedSelections->groupBy('quotation.provider_name');
            
            $providerSummary = $providerGroups->map(function ($selections, $providerName) {
                return [
                    'provider_name' => $providerName,
                    'items_count' => $selections->count(),
                    'total_amount' => $selections->sum('total_price'),
                    'items' => $selections
                ];
            });

            Log::info('Mostrando interfaz de selección mixta', [
                'purchase_request_id' => $purchaseRequest->id,
                'providers_count' => $providerGroups->count(),
                'total_items' => $mixedSelections->count()
            ]);

            return view('purchase-orders.mixed-selection', compact(
                'purchaseRequest', 
                'mixedSelections', 
                'providerGroups',
                'providerSummary'
            ));
            
        } elseif ($hasSelectedQuotation) {
            // Cotización única - Un solo proveedor
            $quotation = $purchaseRequest->selectedQuotation;
            $providerName = $quotation->provider_name;
            
            // Buscar el proveedor
            $provider = \App\Models\Proveedor::where('nombre', $providerName)->first();
            
            Log::info('Mostrando interfaz de cotización única', [
                'purchase_request_id' => $purchaseRequest->id,
                'provider_name' => $providerName,
                'quotation_id' => $quotation->id
            ]);

            return view('purchase-orders.single-provider', compact(
                'purchaseRequest',
                'quotation',
                'provider',
                'providerName'
            ));
            
        } elseif ($isNoQuotationService) {
            // Servicio sin cotización
            Log::info('Mostrando interfaz de servicio sin cotización', [
                'purchase_request_id' => $purchaseRequest->id
            ]);

            return view('purchase-orders.no-quotation-service', compact(
                'purchaseRequest'
            ));
            
        } else {
            // Caso no identificado - redirigir con error
            return redirect()->route('purchase-orders.index')
                ->with('error', 'No se pudo determinar el tipo de solicitud para crear la orden de compra.');
        }
    }

    /**
     * Crear múltiples órdenes de compra para selección mixta de proveedores
     */
    private function createMixedSelectionOrders(Request $request, PurchaseRequest $purchaseRequest)
    {
        Log::info('Iniciando creación de órdenes múltiples para selección mixta', [
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => auth()->id()
        ]);

        try {
            DB::beginTransaction();
            
            // Obtener las selecciones mixtas con todas las relaciones necesarias
            $itemSelections = $purchaseRequest->quotationItemSelections()
                ->with(['quotation', 'quotation.quotationItems'])
                ->get();
            
            Log::info('Selecciones mixtas obtenidas', [
                'total_selections' => $itemSelections->count(),
                'selections_data' => $itemSelections->map(function($selection) {
                    return [
                        'id' => $selection->id,
                        'quotation_id' => $selection->quotation_id,
                        'item_description' => $selection->item_description,
                        'quantity' => $selection->quantity,
                        'unit_price' => $selection->unit_price,
                        'total_price' => $selection->total_price,
                        'provider_name' => $selection->quotation->provider_name ?? 'Sin proveedor'
                    ];
                })->toArray()
            ]);
            
            // Agrupar por provider_name en lugar de quotation_id para evitar confusiones
            $selectionsByProvider = $itemSelections->groupBy(function($selection) {
                return $selection->quotation->provider_name;
            });
            
            Log::info('Selecciones agrupadas por proveedor', [
                'providers_count' => $selectionsByProvider->count(),
                'providers_list' => $selectionsByProvider->keys()->toArray(),
                'items_per_provider' => $selectionsByProvider->map(function($group, $providerName) {
                    return [
                        'provider' => $providerName,
                        'items_count' => $group->count(),
                        'total_amount' => $group->sum('total_price'),
                        'items' => $group->map(function($item) {
                            return [
                                'description' => $item->item_description,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);
            
            $createdOrders = [];
            $orderCounter = 1;
            
            foreach ($selectionsByProvider as $providerName => $providerSelections) {
                // Validar que tengamos un proveedor válido
                if (empty($providerName) || $providerName === 'Sin proveedor') {
                    Log::warning('Proveedor inválido encontrado, saltando', [
                        'provider_name' => $providerName,
                        'selections_count' => $providerSelections->count()
                    ]);
                    continue;
                }
                
                // Obtener la primera quotation para datos del proveedor
                $firstSelection = $providerSelections->first();
                $quotation = $firstSelection->quotation;
                
                // Validar que la quotation existe y tiene datos válidos
                if (!$quotation) {
                    Log::error('Quotation no encontrada para selección', [
                        'selection_id' => $firstSelection->id,
                        'provider_name' => $providerName
                    ]);
                    continue;
                }
                
                // Buscar o crear proveedor basado en el nombre de la cotización
                $provider = \App\Models\Proveedor::where('nombre', $providerName)->first();
                
                if (!$provider) {
                    Log::info('Creando nuevo proveedor', ['provider_name' => $providerName]);
                    $provider = \App\Models\Proveedor::create([
                        'nombre' => $providerName,
                        'email' => $quotation->provider_email ?? 'proveedor@contacto.com',
                        'telefono' => $quotation->provider_phone ?? '000-000-0000',
                        'direccion' => 'Por definir',
                        'persona_contacto' => 'Por asignar',
                        'nit' => $quotation->provider_nit ?? '000000000-0'
                    ]);
                } else {
                    Log::info('Proveedor existente encontrado', [
                        'provider_id' => $provider->id,
                        'provider_name' => $provider->nombre
                    ]);
                }
                
                // Calcular total para este proveedor SOLO con sus items
                $totalAmount = $providerSelections->sum('total_price');
                
                // Validar que el total sea mayor a 0
                if ($totalAmount <= 0) {
                    Log::warning('Total amount inválido para proveedor', [
                        'provider_name' => $providerName,
                        'total_amount' => $totalAmount
                    ]);
                    continue;
                }
                
                // Calcular IVA correctamente - para selecciones mixtas asumimos que los precios ya incluyen IVA
                $includesIva = true;
                $subtotal = round($totalAmount / 1.19, 2); // Calcular subtotal sin IVA
                $ivaAmount = round($totalAmount - $subtotal, 2); // Calcular IVA
                
                // Generar número de orden único para este proveedor
                $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + $orderCounter, 3, '0', STR_PAD_LEFT);
                
                // Preparar observaciones específicas para este proveedor
                $observations = $request->observations ?? '';
                $providerObservations = 'Orden para proveedor: ' . $providerName;
                if ($observations) {
                    $providerObservations .= ' | ' . $observations;
                }
                
                Log::info('Creando orden individual para proveedor', [
                    'provider_name' => $providerName,
                    'provider_id' => $provider->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount,
                    'items_count' => $providerSelections->count(),
                    'items_detail' => $providerSelections->map(function($sel) {
                        return [
                            'description' => $sel->item_description,
                            'qty' => $sel->quantity,
                            'price' => $sel->unit_price,
                            'total' => $sel->total_price
                        ];
                    })->toArray()
                ]);
                
                // Crear orden de compra individual para este proveedor
                $order = PurchaseOrder::create([
                    'order_number' => $orderNumber,
                    'purchase_request_id' => $purchaseRequest->id,
                    'provider_id' => $provider->id,
                    'user_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'order_date' => $request->order_date ?? now()->toDateString(),
                    'payment_terms' => $request->payment_terms ?? $quotation->payment_terms ?? 'Contado',
                    'delivery_date' => $request->delivery_date ?? now()->addDays(15),
                    'observations' => $providerObservations,
                    'total_amount' => $totalAmount,
                    'file_path' => 'pending_generation',
                    'status' => 'pending',
                    'additional_items' => [],
                    'includes_iva' => $includesIva,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount,
                ]);
                
                // Generar PDF específico para este proveedor CON SUS ITEMS EXACTOS
                try {
                    $pdfService = app(PurchaseOrderPdfService::class);
                    $pdfPath = $pdfService->generatePdf($order, $providerSelections);
                    
                    // Actualizar la ruta del archivo
                    $order->update(['file_path' => $pdfPath]);
                    
                    Log::info('PDF generado exitosamente', [
                        'order_id' => $order->id,
                        'pdf_path' => $pdfPath,
                        'provider_selections_count' => $providerSelections->count()
                    ]);
                } catch (\Exception $pdfError) {
                    Log::error('Error al generar PDF para orden', [
                        'order_id' => $order->id,
                        'provider_name' => $providerName,
                        'error' => $pdfError->getMessage()
                    ]);
                    // Continuar con la siguiente orden aunque falle el PDF
                    $order->update(['file_path' => 'error_generation']);
                }
                
                $createdOrders[] = $order;
                $orderCounter++;
                
                Log::info('Orden individual creada exitosamente', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'provider' => $providerName,
                    'items_included' => $providerSelections->pluck('item_description')->toArray(),
                    'total_amount' => $totalAmount
                ]);
            }
            
            // Validar que se hayan creado órdenes
            if (empty($createdOrders)) {
                throw new \Exception('No se pudieron crear órdenes de compra. Verifique que las selecciones mixtas tengan proveedores válidos.');
            }
            
            DB::commit();
            
            Log::info('Todas las órdenes de selección mixta creadas exitosamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'orders_created' => count($createdOrders),
                'order_ids' => array_column($createdOrders, 'id'),
                'providers_processed' => $selectionsByProvider->keys()->toArray(),
                'total_amount_all_orders' => array_sum(array_column($createdOrders, 'total_amount'))
            ]);
            
            // Redirigir a la lista con mensaje de éxito
            $orderCount = count($createdOrders);
            $providerList = $selectionsByProvider->keys()->implode(', ');
            return redirect()->route('purchase-orders.index')
                ->with('success', "Se crearon exitosamente {$orderCount} órdenes de compra para los proveedores: {$providerList}.");
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al crear órdenes de selección mixta', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Error al crear las órdenes de compra: ' . $e->getMessage());
        }
    }

    /**
     * Separar una orden mixta creando una nueva orden para un proveedor específico
     */
    public function separateMixedOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para separar órdenes de compra.');
        }

        try {
            DB::beginTransaction();

            $quotationId = $request->quotation_id;
            $providerName = $request->provider_name;

            Log::info('Iniciando separación de orden mixta', [
                'original_order_id' => $purchaseOrder->id,
                'quotation_id' => $quotationId,
                'provider_name' => $providerName
            ]);

            // Obtener las selecciones del proveedor específico
            $providerSelections = $purchaseOrder->purchaseRequest
                ->quotationItemSelections()
                ->where('quotation_id', $quotationId)
                ->with('quotation')
                ->get();

            if ($providerSelections->isEmpty()) {
                throw new \Exception('No se encontraron items para este proveedor.');
            }

            // Buscar o crear el proveedor
            $provider = \App\Models\Proveedor::where('nombre', $providerName)->first();
            if (!$provider) {
                $quotation = $providerSelections->first()->quotation;
                $provider = \App\Models\Proveedor::create([
                    'nombre' => $providerName,
                    'email' => $quotation->provider_email ?? 'proveedor@contacto.com',
                    'telefono' => $quotation->provider_phone ?? '000-000-0000',
                    'direccion' => 'Por definir',
                    'persona_contacto' => 'Por asignar',
                    'nit' => $quotation->provider_nit ?? '000000000-0'
                ]);
            }

            // Calcular totales para la nueva orden
            $totalAmount = $providerSelections->sum('total_price');
            $includesIva = true;
            $subtotal = round($totalAmount / 1.19, 2);
            $ivaAmount = round($totalAmount - $subtotal, 2);

            // Generar número de orden único
            $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 3, '0', STR_PAD_LEFT);

            // Crear la nueva orden
            $newOrder = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'provider_id' => $provider->id,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(),
                'order_date' => now()->toDateString(),
                'payment_terms' => $purchaseOrder->payment_terms ?? 'Contado',
                'delivery_date' => $purchaseOrder->delivery_date ?? now()->addDays(15),
                'observations' => "Orden separada de {$purchaseOrder->order_number} - Proveedor: {$providerName}",
                'total_amount' => $totalAmount,
                'file_path' => 'pending_generation',
                'status' => 'pending',
                'additional_items' => [],
                'includes_iva' => $includesIva,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
            ]);

            // Generar PDF para la nueva orden
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($newOrder, $providerSelections);
            $newOrder->update(['file_path' => $pdfPath]);

            DB::commit();

            Log::info('Orden separada creada exitosamente', [
                'original_order_id' => $purchaseOrder->id,
                'new_order_id' => $newOrder->id,
                'new_order_number' => $newOrder->order_number,
                'provider' => $providerName
            ]);

            return redirect()->route('purchase-orders.show', $newOrder->id)
                ->with('success', "Se creó exitosamente la orden separada {$newOrder->order_number} para el proveedor {$providerName}.");

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al separar orden mixta', [
                'order_id' => $purchaseOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al separar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Remover items de un proveedor específico de una orden mixta
     */
    public function removeProviderItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para modificar órdenes de compra.');
        }

        try {
            DB::beginTransaction();

            $quotationId = $request->quotation_id;
            $providerName = $request->provider_name;

            Log::info('Removiendo items de proveedor de orden mixta', [
                'order_id' => $purchaseOrder->id,
                'quotation_id' => $quotationId,
                'provider_name' => $providerName
            ]);

            // Obtener las selecciones del proveedor específico
            $providerSelections = $purchaseOrder->purchaseRequest
                ->quotationItemSelections()
                ->where('quotation_id', $quotationId)
                ->get();

            if ($providerSelections->isEmpty()) {
                throw new \Exception('No se encontraron items para este proveedor.');
            }

            $totalToRemove = $providerSelections->sum('total_price');

            // Eliminar las selecciones del proveedor
            $purchaseOrder->purchaseRequest
                ->quotationItemSelections()
                ->where('quotation_id', $quotationId)
                ->delete();

            // Recalcular totales de la orden original
            $remainingSelections = $purchaseOrder->purchaseRequest
                ->quotationItemSelections()
                ->get();

            if ($remainingSelections->isEmpty()) {
                // Si no quedan items, eliminar la orden
                $purchaseOrder->delete();
                
                DB::commit();
                
                return redirect()->route('purchase-orders.index')
                    ->with('success', 'Todos los items fueron removidos. La orden ha sido eliminada.');
            } else {
                // Recalcular totales
                $newTotal = $remainingSelections->sum('total_price');
                $newSubtotal = round($newTotal / 1.19, 2);
                $newIvaAmount = round($newTotal - $newSubtotal, 2);

                // Actualizar la orden
                $purchaseOrder->update([
                    'total_amount' => $newTotal,
                    'subtotal' => $newSubtotal,
                    'iva_amount' => $newIvaAmount,
                    'observations' => ($purchaseOrder->observations ?? '') . " | Items de {$providerName} removidos"
                ]);

                // Regenerar PDF
                $pdfService = app(PurchaseOrderPdfService::class);
                $pdfPath = $pdfService->generatePdf($purchaseOrder);
                $purchaseOrder->update(['file_path' => $pdfPath]);
            }

            DB::commit();

            Log::info('Items de proveedor removidos exitosamente', [
                'order_id' => $purchaseOrder->id,
                'provider' => $providerName,
                'amount_removed' => $totalToRemove,
                'remaining_total' => $purchaseOrder->total_amount ?? 0
            ]);

            return redirect()->back()
                ->with('success', "Se removieron exitosamente los items de {$providerName} de la orden.");

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al remover items de proveedor', [
                'order_id' => $purchaseOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al remover los items: ' . $e->getMessage());
        }
    }

    /**
     * Crear una orden alternativa basada en una cotización diferente
     */
    public function createAlternativeOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para crear órdenes alternativas.');
        }

        try {
            DB::beginTransaction();

            $quotationId = $request->quotation_id;
            $providerName = $request->provider_name;

            Log::info('Creando orden alternativa', [
                'original_order_id' => $purchaseOrder->id,
                'quotation_id' => $quotationId,
                'provider_name' => $providerName
            ]);

            // Verificar que la cotización existe y pertenece a la misma solicitud
            $quotation = \App\Models\Quotation::with(['items', 'purchaseRequest'])
                ->where('id', $quotationId)
                ->where('purchase_request_id', $purchaseOrder->purchase_request_id)
                ->first();

            if (!$quotation) {
                throw new \Exception('La cotización especificada no existe o no pertenece a esta solicitud.');
            }

            // Buscar o crear el proveedor
            $provider = \App\Models\Proveedor::where('nombre', $providerName)->first();
            if (!$provider) {
                $provider = \App\Models\Proveedor::create([
                    'nombre' => $providerName,
                    'email' => $quotation->provider_email ?? 'proveedor@contacto.com',
                    'telefono' => $quotation->provider_phone ?? '000-000-0000',
                    'direccion' => $quotation->provider_address ?? 'Por definir',
                    'persona_contacto' => 'Por asignar',
                    'nit' => $quotation->provider_nit ?? '000000000-0'
                ]);
            }

            // Calcular totales basados en la cotización
            $totalAmount = $quotation->total_amount;
            $includesIva = $quotation->includes_iva ?? true;
            
            if ($includesIva) {
                $subtotal = round($totalAmount / 1.19, 2);
                $ivaAmount = round($totalAmount - $subtotal, 2);
            } else {
                $subtotal = $totalAmount;
                $ivaAmount = round($totalAmount * 0.19, 2);
                $totalAmount = $subtotal + $ivaAmount;
            }

            // Generar número de orden único
            $orderNumber = 'ORD-' . str_pad(PurchaseOrder::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);

            // Crear la nueva orden alternativa
            $newOrder = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'provider_id' => $provider->id,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(),
                'order_date' => now()->toDateString(),
                'payment_terms' => $purchaseOrder->payment_terms ?? 'Contado',
                'delivery_date' => $purchaseOrder->delivery_date ?? now()->addDays(15),
                'observations' => "Orden alternativa de {$purchaseOrder->order_number} - Proveedor: {$providerName}",
                'total_amount' => $totalAmount,
                'file_path' => 'pending_generation',
                'status' => 'pending',
                'additional_items' => [],
                'includes_iva' => $includesIva,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
                'selected_quotation_id' => $quotationId,
            ]);

            // Crear selecciones de items basadas en la cotización
            foreach ($quotation->items as $item) {
                \App\Models\QuotationItemSelection::create([
                    'purchase_request_id' => $purchaseOrder->purchase_request_id,
                    'quotation_id' => $quotationId,
                    'item_description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'selected_by' => auth()->id(),
                    'selected_at' => now(),
                ]);
            }

            // Generar PDF para la nueva orden
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($newOrder);
            $newOrder->update(['file_path' => $pdfPath]);

            DB::commit();

            Log::info('Orden alternativa creada exitosamente', [
                'original_order_id' => $purchaseOrder->id,
                'new_order_id' => $newOrder->id,
                'new_order_number' => $newOrder->order_number,
                'provider' => $providerName,
                'total_amount' => $totalAmount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se creó exitosamente la orden alternativa {$newOrder->order_number} para el proveedor {$providerName}.",
                'new_order_number' => $newOrder->order_number,
                'redirect_url' => route('purchase-orders.show', $newOrder->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al crear orden alternativa', [
                'order_id' => $purchaseOrder->id,
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden alternativa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revertir una orden de compra a selección múltiple
     */
    public function revertToMixedSelection(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador o personal de compras
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('compras')) {
            abort(403, 'No tienes permisos para revertir órdenes de compra.');
        }

        try {
            DB::beginTransaction();

            Log::info('Iniciando reversión de orden a selección múltiple', [
                'order_id' => $purchaseOrder->id,
                'order_number' => $purchaseOrder->order_number,
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'user_id' => auth()->id()
            ]);

            // Verificar que la orden tenga cotizaciones alternativas
            $purchaseRequest = $purchaseOrder->purchaseRequest;
            $allQuotations = $purchaseRequest->quotations()->count();
            
            if ($allQuotations < 2) {
                throw new \Exception('Esta orden no tiene cotizaciones alternativas disponibles para revertir.');
            }

            // Verificar que no sea una orden mixta (sería contradictorio revertirla)
            $isMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
            if ($isMixedSelection) {
                throw new \Exception('Esta orden ya es una selección mixta. No se puede revertir.');
            }

            // Guardar información para el log
            $originalOrderData = [
                'order_number' => $purchaseOrder->order_number,
                'provider_name' => $purchaseOrder->provider->nombre ?? 'N/A',
                'total_amount' => $purchaseOrder->total_amount,
                'selected_quotation_id' => $purchaseOrder->selected_quotation_id
            ];

            // Restaurar el estado de la solicitud de compra
            $purchaseRequest->update([
                'selected_quotation_id' => null,
                'status' => 'approved', // Volver al estado aprobado pero sin selección
                'selection_notes' => ($purchaseRequest->selection_notes ?? '') . 
                    "\n[" . now()->format('Y-m-d H:i:s') . "] Orden {$purchaseOrder->order_number} revertida por " . auth()->user()->name
            ]);

            // Eliminar la orden de compra actual
            $purchaseOrder->update([
                'observations' => ($purchaseOrder->observations ?? '') . 
                    "\n[REVERTIDA] Esta orden fue revertida a selección múltiple el " . now()->format('Y-m-d H:i:s') . " por " . auth()->user()->name
            ]);
            
            // Soft delete de la orden
            $purchaseOrder->delete();

            DB::commit();

            Log::info('Orden revertida exitosamente a selección múltiple', [
                'original_order_data' => $originalOrderData,
                'purchase_request_id' => $purchaseRequest->id,
                'quotations_available' => $allQuotations,
                'reverted_by' => auth()->user()->name,
                'reverted_at' => now()->toDateTimeString()
            ]);

            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('success', 
                    "La orden {$originalOrderData['order_number']} ha sido revertida exitosamente. " .
                    "Ahora puede realizar una nueva selección múltiple entre los {$allQuotations} proveedores disponibles."
                );

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error al revertir orden a selección múltiple', [
                'order_id' => $purchaseOrder->id,
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Error al revertir la orden: ' . $e->getMessage());
        }
    }

    /**
     * Marcar/desmarcar una orden de compra como vista (solo contabilidad/tesorería/admin)
     */
    public function toggleViewed(PurchaseOrder $purchaseOrder)
    {
        // Verificar permisos: admin o usuarios específicos de contabilidad/tesorería
        $allowedEmails = [
            'asistentecontabilidad@tvs.edu.co',
            'contabilidad@tvs.edu.co',
            'tesoreria@tvs.edu.co'
        ];
        
        if (!auth()->user()->hasRole('admin') && !in_array(auth()->user()->email, $allowedEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para marcar órdenes como vistas.'
            ], 403);
        }

        try {
            // Toggle del estado visto
            $newViewedStatus = !$purchaseOrder->is_viewed;
            
            $purchaseOrder->update([
                'is_viewed' => $newViewedStatus,
                'viewed_by' => $newViewedStatus ? auth()->id() : null,
                'viewed_at' => $newViewedStatus ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'is_viewed' => $newViewedStatus,
                'message' => $newViewedStatus ? 'Orden marcada como vista' : 'Orden desmarcada como vista',
                'viewed_by' => $newViewedStatus ? auth()->user()->name : null,
                'viewed_at' => $newViewedStatus ? $purchaseOrder->viewed_at->format('d/m/Y H:i') : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado visto de orden', [
                'order_id' => $purchaseOrder->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la orden.'
            ], 500);
        }
    }

    /**
     * Crear una orden de compra para un proveedor específico en selección mixta
     */
    public function createForProvider(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'provider_name' => 'required|string',
            'payment_terms' => 'required|string',
            'delivery_date' => 'required|date',
            'observations' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la solicitud esté aprobada
            if ($purchaseRequest->status !== 'approved') {
                return redirect()->back()->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
            }

            // Obtener selecciones para el proveedor específico
            $allSelections = $purchaseRequest->quotationItemSelections()->with('quotation')->get();
            $providerSelections = $allSelections->filter(function($selection) use ($request) {
                return $selection->quotation && 
                       $selection->quotation->provider_name === $request->provider_name;
            });

            if ($providerSelections->isEmpty()) {
                return redirect()->back()->with('error', 'No se encontraron selecciones para el proveedor especificado.');
            }

            // Verificar si ya existe una orden para este proveedor
            $existingOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                ->whereHas('provider', function($query) use ($request) {
                    $query->where('nombre', $request->provider_name);
                })
                ->exists();

            if ($existingOrder) {
                return redirect()->back()->with('error', 'Ya existe una orden de compra para este proveedor.');
            }

            // Buscar el ID del proveedor
            $provider = Proveedor::where('nombre', $request->provider_name)->first();
            if (!$provider) {
                return redirect()->back()->with('error', 'Proveedor no encontrado en la base de datos.');
            }

            // Verificar si la cotización original incluye IVA
            $firstSelection = $providerSelections->first();
            $quotation = $firstSelection->quotation;
            $quotationIncludesIva = $quotation->includes_iva;
            
            // Calcular totales basándose en si la cotización incluye IVA
            $totalAmount = $providerSelections->sum('total_price');
            
            if ($quotationIncludesIva) {
                // El total ya incluye IVA, calcular subtotal
                $subtotal = round($totalAmount / 1.19, 2);
                $ivaAmount = round($totalAmount - $subtotal, 2);
                $finalTotal = $totalAmount;
            } else {
                // El total no incluye IVA, calcularlo
                $subtotal = $totalAmount;
                $ivaAmount = round($totalAmount * 0.19, 2);
                $finalTotal = $subtotal + $ivaAmount;
            }

            Log::info('Cálculo de IVA para orden', [
                'provider_name' => $request->provider_name,
                'quotation_includes_iva' => $quotationIncludesIva,
                'selections_total' => $totalAmount,
                'calculated_subtotal' => $subtotal,
                'calculated_iva' => $ivaAmount,
                'final_total' => $finalTotal
            ]);

            // Crear la orden de compra
            $orderNumber = $this->generateOrderNumber();
            
            $purchaseOrder = PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'order_number' => $orderNumber,
                'total_amount' => $finalTotal,
                'subtotal' => $subtotal,
                'includes_iva' => $quotationIncludesIva,
                'iva_amount' => $ivaAmount,
                'order_date' => now(),
                'payment_terms' => $request->payment_terms,
                'delivery_date' => $request->delivery_date,
                'observations' => $request->observations,
                'created_by' => auth()->id(),
                'status' => 'pending',
                'file_path' => 'pending_generation'
            ]);

            // Generar PDF
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($purchaseOrder, $providerSelections);
            
            // Actualizar la ruta del PDF
            $purchaseOrder->update(['file_path' => $pdfPath]);

            DB::commit();

            Log::info('Orden de compra creada para proveedor específico', [
                'order_id' => $purchaseOrder->id,
                'provider_name' => $request->provider_name,
                'total_amount' => $totalAmount,
                'items_count' => $providerSelections->count()
            ]);

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', "Orden de compra #{$orderNumber} creada exitosamente para {$request->provider_name}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear orden para proveedor específico', [
                'purchase_request_id' => $purchaseRequest->id,
                'provider_name' => $request->provider_name,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Crear una orden de compra desde una cotización única
     */
    public function createFromQuotation(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'provider_id' => 'required|exists:proveedores,id',
            'payment_terms' => 'required|string',
            'delivery_date' => 'required|date',
            'observations' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la solicitud esté aprobada
            if ($purchaseRequest->status !== 'approved') {
                return redirect()->back()->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
            }

            // Verificar si ya existe una orden para esta solicitud
            $existingOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->exists();
            if ($existingOrder) {
                return redirect()->back()->with('error', 'Ya existe una orden de compra para esta solicitud.');
            }

            $quotation = $purchaseRequest->selectedQuotation;
            if (!$quotation) {
                return redirect()->back()->with('error', 'No se encontró la cotización seleccionada.');
            }

            // Crear la orden de compra
            $orderNumber = $this->generateOrderNumber();
            
            $purchaseOrder = PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $request->provider_id,
                'order_number' => $orderNumber,
                'total_amount' => $quotation->total_amount,
                'subtotal' => $quotation->subtotal,
                'includes_iva' => $quotation->includes_iva,
                'iva_amount' => $quotation->iva_amount,
                'tax_amount' => $quotation->tax_amount,
                'order_date' => now(),
                'payment_terms' => $request->payment_terms,
                'delivery_date' => $request->delivery_date,
                'observations' => $request->observations,
                'created_by' => auth()->id(),
                'status' => 'pending',
                'file_path' => 'pending_generation'
            ]);

            // Generar PDF
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($purchaseOrder);
            
            // Actualizar la ruta del PDF
            $purchaseOrder->update(['file_path' => $pdfPath]);

            DB::commit();

            Log::info('Orden de compra creada desde cotización única', [
                'order_id' => $purchaseOrder->id,
                'quotation_id' => $quotation->id,
                'total_amount' => $quotation->total_amount
            ]);

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', "Orden de compra #{$orderNumber} creada exitosamente");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear orden desde cotización', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Crear una orden de compra para servicio sin cotización
     */
    public function createNoQuotation(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'provider_name' => 'required|string',
            'provider_nit' => 'required|string',
            'provider_address' => 'nullable|string',
            'provider_phone' => 'nullable|string',
            'provider_email' => 'nullable|email',
            'total_amount' => 'required|numeric|min:0',
            'includes_iva' => 'required|boolean',
            'payment_terms' => 'required|string',
            'delivery_date' => 'required|date',
            'observations' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la solicitud esté aprobada
            if ($purchaseRequest->status !== 'approved') {
                return redirect()->back()->with('error', 'Solo se pueden crear órdenes de compra para solicitudes aprobadas.');
            }

            // Verificar si ya existe una orden para esta solicitud
            $existingOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->exists();
            if ($existingOrder) {
                return redirect()->back()->with('error', 'Ya existe una orden de compra para esta solicitud.');
            }

            // Buscar o crear el proveedor
            $provider = Proveedor::firstOrCreate(
                ['nit' => $request->provider_nit],
                [
                    'nombre' => $request->provider_name,
                    'direccion' => $request->provider_address,
                    'telefono' => $request->provider_phone,
                    'email' => $request->provider_email,
                ]
            );

            // Calcular valores basándose en si incluye IVA
            $inputAmount = $request->total_amount;
            $includesIva = $request->boolean('includes_iva');
            
            if ($includesIva) {
                // El valor ingresado incluye IVA
                $totalAmount = $inputAmount;
                $subtotal = round($totalAmount / 1.19, 2);
                $ivaAmount = round($totalAmount - $subtotal, 2);
            } else {
                // El valor ingresado no incluye IVA
                $subtotal = $inputAmount;
                $ivaAmount = round($subtotal * 0.19, 2);
                $totalAmount = $subtotal + $ivaAmount;
            }

            Log::info('Cálculo de IVA para orden sin cotización', [
                'provider_name' => $request->provider_name,
                'input_amount' => $inputAmount,
                'includes_iva' => $includesIva,
                'calculated_subtotal' => $subtotal,
                'calculated_iva' => $ivaAmount,
                'final_total' => $totalAmount
            ]);

            // Crear la orden de compra
            $orderNumber = $this->generateOrderNumber();
            
            $purchaseOrder = PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_id' => $provider->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'includes_iva' => $includesIva,
                'iva_amount' => $ivaAmount,
                'order_date' => now(),
                'payment_terms' => $request->payment_terms,
                'delivery_date' => $request->delivery_date,
                'observations' => $request->observations,
                'created_by' => auth()->id(),
                'status' => 'pending',
                'file_path' => 'pending_generation'
            ]);

            // Generar PDF
            $pdfService = app(PurchaseOrderPdfService::class);
            $pdfPath = $pdfService->generatePdf($purchaseOrder);
            
            // Actualizar la ruta del PDF
            $purchaseOrder->update(['file_path' => $pdfPath]);

            DB::commit();

            Log::info('Orden de compra creada para servicio sin cotización', [
                'order_id' => $purchaseOrder->id,
                'provider_name' => $request->provider_name,
                'total_amount' => $totalAmount
            ]);

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', "Orden de compra #{$orderNumber} creada exitosamente");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear orden sin cotización', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de orden único
     */
    private function generateOrderNumber()
    {
        $prefix = 'ORD-';
        
        // Buscar el número más alto existente con formato ORD-XXXX
        $lastOrder = PurchaseOrder::withTrashed()
            ->where('order_number', 'LIKE', 'ORD-%')
            ->orderByRaw('CAST(SUBSTRING(order_number, 5) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastOrder) {
            // Extraer el número del formato ORD-XXXX
            $lastNumber = intval(substr($lastOrder->order_number, 4));
            $nextId = $lastNumber + 1;
        } else {
            $nextId = 1;
        }
        
        return $prefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Crea un registro de impuesto con la estructura correcta
     * 
     * @param string $name Nombre del impuesto (IVA, Impuesto al Consumo)
     * @param float $rate Tasa del impuesto (19, 5, 8, 4)
     * @param float $amount Monto del impuesto
     * @return array Estructura de impuesto consistente
     */
    private function createTaxRecord($name, $rate, $amount = 0)
    {
        // Determinar el tipo de impuesto basado en el nombre
        $type = 'IVA';
        if (strpos(strtolower($name), 'consumo') !== false) {
            $type = 'IMPUESTO_CONSUMO';
        }
        
        return [
            'name' => $name,
            'type' => $type,
            'rate' => $rate,
            'amount' => $amount
        ];
    }
}
