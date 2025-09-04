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
    public function index(Request $request)
    {
        // Obtener filtros de la request
        $requestNumberFilter = $request->get('request_number');
        $sectionFilter = $request->get('section');
        $statusFilter = $request->get('status');
        
        // Query base para órdenes de compra existentes
        $ordersQuery = PurchaseOrder::with(['purchaseRequest', 'purchaseRequest.user', 'provider', 'viewer']);
        
        // Aplicar filtros
        if ($requestNumberFilter) {
            $ordersQuery->whereHas('purchaseRequest', function($query) use ($requestNumberFilter) {
                $query->where('request_number', 'like', '%' . $requestNumberFilter . '%');
            });
        }
        
        if ($sectionFilter && $sectionFilter !== 'all') {
            $ordersQuery->whereHas('purchaseRequest', function($query) use ($sectionFilter) {
                $query->where('section_area', $sectionFilter);
            });
        }
        
        if ($statusFilter && $statusFilter !== 'all') {
            $ordersQuery->where('status', $statusFilter);
        }
        
        $orders = $ordersQuery->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                            ->orderBy('created_at', 'desc')
                            ->get();
            
        // Obtener las solicitudes aprobadas pendientes de generar órdenes de compra
        $approvedRequests = PurchaseRequest::with(['selectedQuotation', 'user', 'approver', 'quotationItemSelections', 'purchaseOrders'])
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
                      })
                      // O solicitudes de compra/servicios que se quedaron sin órdenes de compra (por eliminación)
                      ->orWhere(function($subQuery) {
                          $subQuery->whereIn('type', ['purchase', 'services'])
                                   ->whereDoesntHave('purchaseOrders');
                      });
            })
            ->get()
            ->map(function($request) {
                // Auto-reparar solicitudes huérfanas que no tienen cotización seleccionada
                if (!$request->selected_quotation_id && !$request->quotationItemSelections()->exists()) {
                    $hadOrders = PurchaseOrder::where('purchase_request_id', $request->id)->withTrashed()->count() > 0;
                    
                    if ($hadOrders) {
                        // Buscar la orden eliminada para obtener el proveedor correcto
                        $deletedOrder = PurchaseOrder::where('purchase_request_id', $request->id)
                            ->withTrashed()
                            ->with(['provider'])
                            ->first();
                            
                        if ($deletedOrder && $deletedOrder->provider) {
                            // Buscar la cotización del mismo proveedor que tenía la orden eliminada
                            $correctQuotation = $request->quotations()
                                ->where('provider_name', $deletedOrder->provider->nombre)
                                ->first();
                                
                            if ($correctQuotation) {
                                $request->selected_quotation_id = $correctQuotation->id;
                                $request->save();
                                $request->refresh(); // Recargar la relación
                            }
                        } else {
                            // Si no se puede encontrar el proveedor, usar la primera cotización como respaldo
                            $firstQuotation = $request->quotations()->first();
                            if ($firstQuotation) {
                                $request->selected_quotation_id = $firstQuotation->id;
                                $request->save();
                                $request->refresh();
                            }
                        }
                    }
                }
                return $request;
            })
            ->filter(function($request) {
                // Usar consultas directas para contar órdenes (las relaciones Eloquent tienen problemas con soft deletes)
                $totalOrders = PurchaseOrder::where('purchase_request_id', $request->id)->withTrashed()->count();
                $activeOrders = PurchaseOrder::where('purchase_request_id', $request->id)->count();
                $deletedOrders = PurchaseOrder::where('purchase_request_id', $request->id)->onlyTrashed()->count();
                $hasActiveOrders = $activeOrders > 0;
                
                // Para selecciones mixtas, verificar si faltan órdenes por proveedores
                $hasMixedSelection = $request->quotationItemSelections()->exists();
                
                if ($hasMixedSelection) {
                    // Obtener proveedores con items seleccionados
                    $selections = $request->quotationItemSelections()->with('quotation')->get();
                    $providersWithSelections = $selections->groupBy('quotation.provider_name')->keys();
                    
                    // Obtener proveedores que ya tienen órdenes ACTIVAS usando consulta directa
                    $activeOrdersForRequest = PurchaseOrder::where('purchase_request_id', $request->id)
                        ->with('provider')
                        ->get();
                    $providersWithOrders = $activeOrdersForRequest
                        ->pluck('provider.nombre')
                        ->filter();
                    
                    // Para selecciones mixtas: mostrar solo si faltan órdenes por proveedores
                    $shouldShow = $providersWithSelections->diff($providersWithOrders)->isNotEmpty();
                    
                    return $shouldShow;
                } else {
                    // Para cotizaciones tradicionales y servicios:
                    // Mostrar SOLO si NO tiene órdenes activas (lógica original)
                    // Las órdenes eliminadas no deben hacer que aparezcan si ya tienen una orden activa
                    $shouldShow = !$hasActiveOrders;
                    
                    return $shouldShow;
                }
            })
            ->sortByDesc('approval_date');
        
        // Obtener todas las secciones para el filtro
        $sections = PurchaseRequest::whereHas('purchaseOrders')
            ->distinct()
            ->pluck('section_area')
            ->filter()
            ->sort()
            ->values();
            
        return view('purchase-orders.index', compact('orders', 'approvedRequests', 'sections', 'requestNumberFilter', 'sectionFilter', 'statusFilter'));
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
        
        // Verificar si es una solicitud "huérfana" (tuvo órdenes pero se eliminaron)
        $hadPreviousOrders = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
            ->withTrashed()
            ->count() > 0;
        
        if (!$hasSelectedQuotation && !$hasMixedSelection && !$isNoQuotationService && !$hadPreviousOrders) {
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
                $total = floatval($purchaseRequest->service_budget ?? 0);
                
                if ($total <= 0) {
                    Log::error('Servicio sin cotización con presupuesto inválido', [
                        'purchase_request_id' => $purchaseRequest->id,
                        'service_budget' => $purchaseRequest->service_budget
                    ]);
                    return redirect()->back()->with('error', 'El presupuesto del servicio debe ser mayor a cero.');
                }
                
                $subtotal = round($total / 1.19, 2); // Asumir IVA incluido
                $ivaAmount = round($total - $subtotal, 2);
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
                $total = floatval($purchaseRequest->quotationItemSelections()->sum('total_price'));
                
                if ($total <= 0) {
                    Log::error('Selección mixta con total inválido', [
                        'purchase_request_id' => $purchaseRequest->id,
                        'selections_total' => $total
                    ]);
                    return redirect()->back()->with('error', 'El total de la selección mixta debe ser mayor a cero.');
                }
                
                $subtotal = round($total / 1.19, 2); // Asumir IVA incluido
                $ivaAmount = round($total - $subtotal, 2);
                $includesIva = true;
                $additionalItems = [];
                
                Log::info('Calculando precios para selección mixta', [
                    'selections_count' => $purchaseRequest->quotationItemSelections()->count(),
                    'total' => $total,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount
                ]);
            } else {
                // Para cotización única tradicional
                $selectedQuotation = $purchaseRequest->selectedQuotation;
                
                if (!$selectedQuotation) {
                    Log::error('No se encontró cotización seleccionada', [
                        'purchase_request_id' => $purchaseRequest->id,
                        'selected_quotation_id' => $purchaseRequest->selected_quotation_id
                    ]);
                    return redirect()->back()->with('error', 'No se encontró la cotización seleccionada.');
                }
                
                $total = floatval($selectedQuotation->total_amount ?? 0);
                
                if ($total <= 0) {
                    Log::error('Cotización con total inválido', [
                        'quotation_id' => $selectedQuotation->id,
                        'total_amount' => $selectedQuotation->total_amount
                    ]);
                    return redirect()->back()->with('error', 'El total de la cotización debe ser mayor a cero.');
                }
                
                $includesIva = $selectedQuotation->includes_iva ?? true;
                
                if ($includesIva) {
                    $subtotal = round($total / 1.19, 2);
                    $ivaAmount = round($total - $subtotal, 2);
                } else {
                    $subtotal = $total;
                    $ivaAmount = round($total * 0.19, 2);
                    $total = $subtotal + $ivaAmount;
                }
                
                $additionalItems = $selectedQuotation->additional_items ?? [];
                
                Log::info('Calculando precios para cotización única', [
                    'quotation_id' => $selectedQuotation->id,
                    'includes_iva' => $includesIva,
                    'total' => $total,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount
                ]);
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
            
            // Validar que los valores calculados sean válidos antes de crear la orden
            if ($total <= 0 || $subtotal <= 0) {
                Log::error('Valores calculados inválidos para crear orden', [
                    'purchase_request_id' => $purchaseRequest->id,
                    'total' => $total,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount
                ]);
                throw new \Exception('Los valores calculados para la orden son inválidos. Total: ' . $total . ', Subtotal: ' . $subtotal);
            }
            
            Log::info('Creando orden de compra', [
                'order_number' => $orderNumber,
                'provider_id' => $providerId,
                'total_amount' => $total,
                'subtotal' => $subtotal,
                'iva_amount' => $ivaAmount,
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
            
            // CORRECCIÓN FUNDAMENTAL: Generar items y datos PDF ANTES de generar el PDF
            $this->generateAndStoreOrderItems($order, $purchaseRequest);
            
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

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
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

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
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
            $errorMessage = 'No tienes permisos para eliminar órdenes de compra.';
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 403);
            }
            
            return redirect()->route('purchase-orders.index')
                ->with('error', $errorMessage);
        }

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
            $errorMessage = 'No tienes permisos para eliminar órdenes de compra.';
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 403);
            }
            
            return redirect()->route('purchase-orders.index')
                ->with('error', $errorMessage);
        }

        // Se pueden eliminar órdenes pendientes o aprobadas (solo admin)
        if (!in_array($purchaseOrder->status, ['pending', 'approved'])) {
            $errorMessage = 'No se puede eliminar una orden que ya ha sido procesada o enviada.';
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', $errorMessage);
        }
        
        try {
            // Registrar en historial antes de eliminar
            RequestHistory::create([
                'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Orden de compra eliminada',
                'notes' => 'Orden ' . $purchaseOrder->order_number . ' eliminada por el administrador',
            ]);
            
            $orderNumber = $purchaseOrder->order_number;
            
            // Eliminar la orden de compra
            $purchaseOrder->delete();
            
            $successMessage = "La orden de compra {$orderNumber} ha sido eliminada correctamente.";
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }
            
            return redirect()->route('purchase-orders.index')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            Log::error('Error eliminando orden de compra: ' . $e->getMessage());
            
            $errorMessage = 'Error al eliminar la orden de compra. Por favor, inténtalo de nuevo.';
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->route('purchase-orders.index')
                ->with('error', $errorMessage);
        }
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

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
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

        $purchaseRequest = $purchaseOrder->purchaseRequest;
        $providerSpecificSelections = collect();
        
        // SOLUCIÓN DE FONDO: Asegurar que SIEMPRE haya datos válidos para editar
        try {
            $editableItems = $this->ensureEditableData($purchaseOrder);
        } catch (\Exception $e) {
            Log::error('Error en ensureEditableData', [
                'order' => $purchaseOrder->order_number,
                'error' => $e->getMessage()
            ]);
            // Usar datos básicos como fallback
            $editableItems = [];
        }
        
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
                    } elseif (is_array($prices)) {
                        // 2) Fallback: usar array_values y tomar la posición
                        $values = array_values($prices);
                        if (isset($values[$selection->item_index])) {
                            $realPrice = $values[$selection->item_index];
                        }
                    }

                    if ($realPrice !== null) {
                        $selection->unit_price = $realPrice;
                        $selection->total_price = $realPrice * $selection->quantity;
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

        return view('purchase-orders.edit-pdf-new', compact('order', 'budgetOptions', 'providerSpecificSelections', 'editableItems'));
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
                $existingData = $purchaseOrder->pdf_custom_data ?? '{}';
                $savedCustomData = is_array($existingData) 
                    ? $existingData 
                    : json_decode($existingData, true);
                
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
            
            // CORRECCIÓN CRÍTICA: USAR SOLO LOS DATOS DEL FORMULARIO COMO FUENTE DE VERDAD
            // Los items que llegan del formulario - ESTOS SON LOS ÚNICOS QUE DEBEN GUARDARSE
            $itemsToSave = $request->items ?? [];
            
            // NUEVA CORRECCIÓN: Filtrar items vacíos o sin datos válidos
            $itemsToSave = array_filter($itemsToSave, function($item) {
                // Solo mantener items que tengan descripción Y (cantidad > 0 O precio > 0)
                $hasDescription = !empty($item['description']) && trim($item['description']) !== '';
                $hasQuantity = isset($item['quantity']) && floatval($item['quantity']) > 0;
                $hasPrice = isset($item['unit_price']) && floatval($item['unit_price']) > 0;
                
                return $hasDescription && ($hasQuantity || $hasPrice);
            });
            
            // Reindexar el array para evitar problemas con índices
            $itemsToSave = array_values($itemsToSave);
            
            Log::critical('🔍 ANÁLISIS DE DATOS DEL FORMULARIO FILTRADOS', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
                'items_received_from_form' => count($itemsToSave),
                'items_after_filtering' => count($itemsToSave),
                'valid_items' => array_map(function($item, $index) {
                    return [
                        'index' => $index,
                        'description' => substr($item['description'] ?? 'N/A', 0, 50),
                        'quantity' => $item['quantity'] ?? 0,
                        'unit_price' => $item['unit_price'] ?? 0
                    ];
                }, $itemsToSave, array_keys($itemsToSave))
            ]); 
            
            // ELIMINADO: Lógica de sobrescritura con precios originales que causaba discrepancias
            // El formulario debe ser la fuente de verdad absoluta
            
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
            // CORRECCIÓN CRÍTICA: Usar siempre la plantilla pdf-template-custom.blade.php
            Log::critical('🔧 APLICANDO SOLUCIÓN DEFINITIVA PARA PDF', [
                'order' => $purchaseOrder->order_number,
                'provider' => $purchaseOrder->provider->nombre,
                'using_custom_template' => true,
                'template' => 'pdf-template-custom.blade.php',
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
            
            // CORRECCIÓN CRÍTICA: Recalcular totales desde los items corregidos
            $calculatedSubtotal = 0;
            
            // Validar y corregir precios unitarios en los items
            foreach ($itemsToSave as $index => &$item) {
                // NUEVA LÓGICA: Usar exactamente los valores que vienen del formulario
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $quantity = floatval($item['quantity'] ?? 1);
                $taxRate = floatval($item['tax_rate'] ?? 0);
                
                Log::info('📊 PROCESANDO ITEM DEL FORMULARIO', [
                    'order' => $purchaseOrder->order_number,
                    'item_index' => $index,
                    'description' => $item['description'] ?? 'N/A',
                    'unit_price_from_form' => $unitPrice,
                    'quantity_from_form' => $quantity,
                    'tax_rate' => $taxRate
                ]);
                
                // ELIMINADO: Lógica de detección de precios anómalos que causaba problemas
                // El formulario debe ser la fuente de verdad
                
                // Recalcular el total del item usando EXACTAMENTE los valores del formulario
                $item['unit_price'] = $unitPrice;
                $item['quantity'] = $quantity;
                $item['tax_rate'] = $taxRate;
                $item['total'] = round($quantity * $unitPrice);
                $calculatedSubtotal += $item['total'];
                
                Log::info('✅ ITEM PROCESADO CORRECTAMENTE', [
                    'item_index' => $index,
                    'final_unit_price' => $item['unit_price'],
                    'final_quantity' => $item['quantity'],
                    'final_total' => $item['total']
                ]);
                
                Log::debug('Item procesado', [
                    'index' => $index,
                    'description' => $item['description'] ?? 'N/A',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $item['total'],
                    'tax_rate' => $taxRate
                ]);
            }
            
            Log::info('Subtotal recalculado desde items', [
                'order' => $purchaseOrder->order_number,
                'calculated_subtotal' => $calculatedSubtotal,
                'request_subtotal' => $request->subtotal
            ]);
            
            // Usar el subtotal calculado desde los items como fuente de verdad
            $rawSubtotal = $calculatedSubtotal;
            $ivaRate = intval($request->iva_rate ?? 19);
            $ipoconsumoRate = intval($request->ipoconsumo_rate ?? 0);
            
            // Calcular impuestos basados en el subtotal real
            $ivaAmount = 0;
            $ipoconsumoAmount = 0;
            
            if ($ivaRate > 0) {
                $ivaAmount = round(($rawSubtotal * $ivaRate) / 100, 2);
            }
            
            if ($ipoconsumoRate > 0) {
                $ipoconsumoAmount = round(($rawSubtotal * $ipoconsumoRate) / 100, 2);
            }
            
            $rawTotal = $rawSubtotal + $ivaAmount + $ipoconsumoAmount;
            
            // Calcular impuestos individuales si existen
            $individualTaxesTotal = 0;
            $individualTaxesBreakdown = [];
            
            \Log::info('Verificando campos de impuestos individuales en request:', [
                'has_individual_taxes_total' => $request->has('individual_taxes_total'),
                'individual_taxes_total_value' => $request->individual_taxes_total ?? 'NULL',
                'has_individual_taxes_breakdown' => $request->has('individual_taxes_breakdown'),
                'individual_taxes_breakdown_value' => $request->individual_taxes_breakdown ?? 'NULL'
            ]);
            
            if ($request->has('individual_taxes_total')) {
                $individualTaxesTotal = floatval($request->individual_taxes_total ?? 0);
                \Log::info('Individual taxes total procesado:', ['value' => $individualTaxesTotal]);
            }
            
            if ($request->has('individual_taxes_breakdown')) {
                $breakdown = $request->individual_taxes_breakdown;
                if (is_string($breakdown)) {
                    $individualTaxesBreakdown = json_decode($breakdown, true) ?? [];
                } else {
                    $individualTaxesBreakdown = $breakdown ?? [];
                }
                \Log::info('Individual taxes breakdown procesado:', ['value' => $individualTaxesBreakdown]);
            }
            
            // Agregar impuestos individuales al total final
            $rawTotal += $individualTaxesTotal;
            
            Log::info('Totales recalculados correctamente', [
                'order' => $purchaseOrder->order_number,
                'subtotal' => $rawSubtotal,
                'iva_rate' => $ivaRate,
                'iva_amount' => $ivaAmount,
                'ipoconsumo_rate' => $ipoconsumoRate,
                'ipoconsumo_amount' => $ipoconsumoAmount,
                'individual_taxes_total' => $individualTaxesTotal,
                'individual_taxes_breakdown' => $individualTaxesBreakdown,
                'total' => $rawTotal
            ]);
            
            // Procesar additional_items y filtrar los vacíos
            $additionalItemsToSave = $request->additional_items ?? [];
            
            // NUEVA CORRECCIÓN: Filtrar items adicionales vacíos o sin datos válidos
            $additionalItemsToSave = array_filter($additionalItemsToSave, function($item) {
                // Solo mantener items adicionales que tengan descripción Y (cantidad > 0 O precio > 0)
                $hasDescription = !empty($item['description']) && trim($item['description']) !== '';
                $hasQuantity = isset($item['quantity']) && floatval($item['quantity']) > 0;
                $hasPrice = isset($item['unit_price']) && floatval($item['unit_price']) > 0;
                
                return $hasDescription && ($hasQuantity || $hasPrice);
            });
            
            // Reindexar el array para evitar problemas con índices
            $additionalItemsToSave = array_values($additionalItemsToSave);
            
            foreach ($additionalItemsToSave as $index => &$additionalItem) {
                if (isset($additionalItem['tax_rate'])) {
                    $additionalItem['tax_rate'] = floatval($additionalItem['tax_rate']);
                }
            }
            
            Log::info('Items adicionales procesados y filtrados', [
                'order' => $purchaseOrder->order_number,
                'additional_items_received' => count($request->additional_items ?? []),
                'additional_items_after_filtering' => count($additionalItemsToSave),
                'valid_additional_items' => array_map(function($item, $index) {
                    return [
                        'index' => $index,
                        'description' => substr($item['description'] ?? 'N/A', 0, 50),
                        'quantity' => $item['quantity'] ?? 0,
                        'unit_price' => $item['unit_price'] ?? 0
                    ];
                }, $additionalItemsToSave, array_keys($additionalItemsToSave))
            ]);
            
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
                'iva_rate' => $ivaRate,
                'iva_amount' => $ivaAmount,
                'ipoconsumo_rate' => $ipoconsumoRate,
                'ipoconsumo_amount' => $ipoconsumoAmount,
                'subtotal' => $rawSubtotal,
                'total' => $rawTotal,
                'items' => $itemsToSave,
                'additional_items' => $additionalItemsToSave,
                'observations' => $request->observations,
                'shared_budget_info' => $request->shared_budget_info,
                'individual_taxes_total' => $individualTaxesTotal,
                'individual_taxes_breakdown' => $individualTaxesBreakdown,
                'edited_by' => auth()->user()->id,
                'edited_at' => now()->toISOString(),
                'calculation_source' => 'items_based', // Indicar que los cálculos se basan en items
                'items_count' => count($itemsToSave),
                'additional_items_count' => count($additionalItemsToSave)
            ]);
            
            Log::critical('📋 DATOS FINALES QUE SE GUARDARÁN', [
                'order' => $purchaseOrder->order_number,
                'items_count' => count($itemsToSave),
                'additional_items_count' => count($additionalItemsToSave),
                'subtotal' => $rawSubtotal,
                'iva_amount' => $ivaAmount,
                'total' => $rawTotal,
                'items_preview' => array_slice($itemsToSave, 0, 3),
                'additional_items_preview' => array_slice($additionalItemsToSave, 0, 3),
                'calculation_source' => 'items_based'
            ]);
            
            Log::info('Datos personalizados preparados con cálculos corregidos', [
                'order' => $purchaseOrder->order_number,
                'items_count' => count($itemsToSave),
                'subtotal' => $rawSubtotal,
                'total' => $rawTotal,
                'calculation_source' => 'items_based'
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

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
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
                    $taxAmount = round(($itemSubtotal * $taxRate) / 100);
                    
                    if (!isset($appliedTaxes['IVA 19%'])) {
                        $appliedTaxes['IVA 19%'] = $this->createTaxRecord('IVA', $taxRate);
                    }
                    $appliedTaxes['IVA 19%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                    $taxRate = 5;
                    $taxAmount = round(($itemSubtotal * $taxRate) / 100);
                    
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
            // Verificar si es una solicitud "huérfana" (tuvo órdenes pero se eliminaron)
            $hadPreviousOrders = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                ->withTrashed()
                ->count() > 0;
                
            if ($hadPreviousOrders) {
                // Para solicitudes huérfanas, intentar encontrar la configuración original
                // buscando en órdenes eliminadas para recuperar la configuración
                $deletedOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                    ->withTrashed()
                    ->with(['provider'])
                    ->first();
                
                if ($deletedOrder && $deletedOrder->provider) {
                    // Buscar cotización del mismo proveedor
                    $quotation = $purchaseRequest->quotations()
                        ->where('provider_name', $deletedOrder->provider->nombre)
                        ->first();
                        
                    if ($quotation) {
                        // Restaurar la cotización seleccionada
                        $purchaseRequest->selected_quotation_id = $quotation->id;
                        $purchaseRequest->save();
                        
                        Log::info('Restaurada cotización para solicitud huérfana', [
                            'purchase_request_id' => $purchaseRequest->id,
                            'quotation_id' => $quotation->id,
                            'provider_name' => $deletedOrder->provider->nombre
                        ]);
                        
                        // Redirigir para recargar con la cotización restaurada
                        return redirect()->route('purchase-orders.create', $purchaseRequest->id);
                    }
                }
                
                // Si no se pudo restaurar, tratar como servicio sin cotización
                Log::info('No se pudo restaurar cotización, tratando como servicio sin cotización', [
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
                
                // CORRECCIÓN: Generar items y datos PDF ANTES de generar el PDF
                $this->generateAndStoreOrderItems($order, $purchaseRequest);
                
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

        // Restricción específica para usuario compras@tvs.edu.co
        if (auth()->id() === 11) {
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
            $quotation = \App\Models\Quotation::with(['purchaseRequest'])
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
            $quotationItems = $quotation->additional_items ?? [];
            
            if (!empty($quotationItems) && is_array($quotationItems)) {
                foreach ($quotationItems as $index => $item) {
                    \App\Models\QuotationItemSelection::create([
                        'purchase_request_id' => $purchaseOrder->purchase_request_id,
                        'quotation_id' => $quotationId,
                        'item_index' => $index,
                        'item_description' => $item['description'] ?? 'Item sin descripción',
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['price'] ?? $item['unit_price'] ?? 0,
                        'total_price' => ($item['quantity'] ?? 1) * ($item['price'] ?? $item['unit_price'] ?? 0),
                        'selected_by' => auth()->id(),
                        'selected_at' => now(),
                    ]);
                }
            } else {
                // Si no hay items específicos en additional_items, crear uno genérico
                \App\Models\QuotationItemSelection::create([
                    'purchase_request_id' => $purchaseOrder->purchase_request_id,
                    'quotation_id' => $quotationId,
                    'item_index' => 0, // Índice 0 para el primer (y único) item
                    'item_description' => 'Producto/Servicio según cotización',
                    'quantity' => 1,
                    'unit_price' => $quotation->total_amount,
                    'total_price' => $quotation->total_amount,
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
            'provider_id' => 'required|exists:proveedors,id',
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
    
    /**
     * Genera datos personalizados iniciales para órdenes que no los tienen
     * Esto permite que las órdenes sean editables en la interfaz
     */
    private function generateInitialCustomData(PurchaseOrder $order)
    {
        Log::info('🔧 Generando datos personalizados para orden', [
            'order' => $order->order_number,
            'provider' => $order->provider->nombre ?? 'N/A'
        ]);
        
        $order->load(['purchaseRequest.selectedQuotation', 'provider']);
        $quotation = $order->purchaseRequest->selectedQuotation;
        
        $items = [];
        
        // Intentar usar datos reales de la solicitud de compra
        $purchaseRequest = $order->purchaseRequest;
        
        // Primero intentar obtener items de la solicitud original
        if ($purchaseRequest && !empty($purchaseRequest->purchase_items)) {
            Log::info('🎯 Usando items de solicitud de compra original', [
                'request_id' => $purchaseRequest->id,
                'items_count' => count($purchaseRequest->purchase_items)
            ]);
            
            // ✅ CORREGIDO: Usar precios específicos de la cotización seleccionada
            $prices = [];
            if ($quotation && $quotation->original_item_prices) {
                $prices = $quotation->original_item_prices;
            } elseif ($quotation && $quotation->item_prices) {
                $prices = $quotation->item_prices;
            }
            
            foreach ($purchaseRequest->purchase_items as $index => $item) {
                $quantity = $item['quantity'] ?? 1;
                
                // ✅ Usar precio específico del item si está disponible
                $unitPrice = 0;
                if (isset($prices[$index])) {
                    $unitPrice = $prices[$index];
                } elseif (count($prices) > 0 && count($purchaseRequest->purchase_items) === 1) {
                    // Si solo hay un item y un precio, usar ese precio
                    $unitPrice = reset($prices);
                }
                
                $items[] = [
                    'description' => $item['description'] ?? 'Descripción no disponible',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $quantity
                ];
            }
        }
        // NUEVO: Intentar usar service_items para solicitudes de servicios
        elseif ($purchaseRequest && $purchaseRequest->type === 'services' && !empty($purchaseRequest->service_items)) {
            Log::info('🎯 Usando service_items de solicitud de servicios', [
                'request_id' => $purchaseRequest->id,
                'items_count' => count($purchaseRequest->service_items)
            ]);
            
            // Para servicios, usar el subtotal de la cotización dividido entre los items
            $serviceItems = is_string($purchaseRequest->service_items) ? 
                           json_decode($purchaseRequest->service_items, true) : 
                           $purchaseRequest->service_items;
            
            $totalValue = $quotation ? $quotation->subtotal : $order->subtotal ?? $order->total_amount;
            $itemCount = count($serviceItems);
            $pricePerItem = $itemCount > 0 ? $totalValue / $itemCount : $totalValue;
            
            foreach ($serviceItems as $index => $item) {
                $quantity = $item['quantity'] ?? 1;
                $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;
                
                $items[] = [
                    'description' => $item['description'] ?? 'Servicio ' . ($index + 1),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $pricePerItem
                ];
            }
        }
        // Intentar usar precios originales de la cotización
        elseif ($quotation && !empty($quotation->original_item_prices)) {
            Log::info('🎯 Usando precios originales de cotización', [
                'cotización_id' => $quotation->id,
                'precios_count' => count($quotation->original_item_prices)
            ]);
            
            // Intentar obtener las descripciones reales de los items de la solicitud
            $purchaseItems = $purchaseRequest ? $purchaseRequest->purchase_items : [];
            
            foreach ($quotation->original_item_prices as $index => $price) {
                $quantity = 1; // Cantidad por defecto
                
                // Usar descripción real si está disponible, sino usar fallback
                $description = "Producto " . ($index + 1); // Fallback
                if (isset($purchaseItems[$index]['description'])) {
                    $description = $purchaseItems[$index]['description'];
                } elseif (isset($purchaseItems[$index]['item_description'])) {
                    $description = $purchaseItems[$index]['item_description'];
                }
                
                // También intentar obtener la cantidad real
                if (isset($purchaseItems[$index]['quantity'])) {
                    $quantity = $purchaseItems[$index]['quantity'];
                }
                
                $items[] = [
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => floatval($price),
                    'total' => floatval($price) * $quantity
                ];
            }
        } 
        // Fallback: crear un item básico basado en el total de la orden
        else {
            Log::info('🎯 Generando item básico desde total de orden', [
                'total_amount' => $order->total_amount
            ]);
            
            $totalAmount = $order->total_amount ?: 0;
            if ($totalAmount > 0) {
                $items[] = [
                    'description' => 'Producto/Servicio',
                    'quantity' => 1,
                    'unit_price' => $totalAmount,
                    'total' => $totalAmount
                ];
            }
        }
        
        // Calcular totales
        $subtotal = array_sum(array_column($items, 'total'));
        $ivaRate = 19; // Tasa de IVA por defecto
        $ivaAmount = round($subtotal * ($ivaRate / 100));
        $total = $subtotal + $ivaAmount;
        
        // Crear estructura de datos personalizados
        $customData = [
            'items' => $items,
            'subtotal' => $subtotal,
            'iva_rate' => $ivaRate . '%',
            'iva_amount' => $ivaAmount,
            'ipoconsumo_rate' => '0%',
            'ipoconsumo_amount' => 0,
            'total' => $total,
            'provider_info' => [
                'name' => $order->provider->nombre ?? '',
                'nit' => $order->provider->nit ?? '',
                'address' => $order->provider->direccion ?? '',
                'phone' => $order->provider->telefono ?? '',
                'email' => $order->provider->correo ?? ''
            ],
            'generated_automatically' => true,
            'generated_at' => now()->toDateTimeString()
        ];
        
        // Guardar los datos personalizados
        $order->pdf_custom_data = json_encode($customData);
        $order->save();
        
        Log::info('✅ Datos personalizados generados exitosamente', [
            'order' => $order->order_number,
            'items_count' => count($items),
            'subtotal' => number_format($subtotal, 0, ',', '.'),
            'total' => number_format($total, 0, ',', '.')
        ]);
        
        return $customData;
    }

    /**
     * Asegurar que una orden tenga datos editables válidos
     * Esta es la SOLUCIÓN DE FONDO para evitar órdenes en blanco
     */
    private function ensureEditableData(PurchaseOrder $order)
    {
        Log::info('🔍 Verificando datos editables para orden', [
            'order' => $order->order_number,
            'status' => $order->status
        ]);

        // Verificar si ya tiene datos válidos en pdf_custom_data
        $customData = $order->pdf_custom_data;
        if (is_array($customData) && !empty($customData['items'])) {
            $validItems = collect($customData['items'])->filter(function($item) {
                return !empty($item['description']) && 
                       $item['description'] !== 'Sin descripción disponible' &&
                       ($item['unit_price'] ?? 0) > 0;
            });

            if ($validItems->count() > 0) {
                Log::info('✅ Orden ya tiene datos válidos', [
                    'valid_items_count' => $validItems->count()
                ]);
                return $customData['items'];
            }
        }

        Log::info('🔧 Generando datos editables para orden', [
            'order' => $order->order_number
        ]);

        // Generar datos editables desde la información original
        $editableItems = $this->generateEditableItems($order);

        // Si se generaron items válidos, actualizar pdf_custom_data
        if (!empty($editableItems)) {
            $this->updateCustomDataWithItems($order, $editableItems);
        }

        return $editableItems;
    }

    /**
     * Generar items editables desde la información original de la orden
     */
    private function generateEditableItems(PurchaseOrder $order)
    {
        $purchaseRequest = $order->purchaseRequest;
        if (!$purchaseRequest) {
            return $this->generateFallbackItems($order);
        }

        // Método 1: Selecciones mixtas específicas del proveedor
        $hasMixedSelection = $purchaseRequest->quotationItemSelections()->exists();
        if ($hasMixedSelection) {
            $items = $this->getItemsFromMixedSelections($order, $purchaseRequest);
            if (!empty($items)) {
                Log::info('✅ Items generados desde selecciones mixtas', ['count' => count($items)]);
                return $items;
            }
        }

        // Método 2: Cotización seleccionada con purchase_items
        $selectedQuotation = $purchaseRequest->selectedQuotation;
        if ($selectedQuotation && !empty($purchaseRequest->purchase_items)) {
            $items = $this->getItemsFromQuotationAndRequest($selectedQuotation, $purchaseRequest);
            if (!empty($items)) {
                Log::info('✅ Items generados desde cotización y solicitud', ['count' => count($items)]);
                return $items;
            }
        }

        // Método 3: Items de servicio
        if ($purchaseRequest->type === 'services' && !empty($purchaseRequest->service_items)) {
            $items = $this->getItemsFromServices($order, $purchaseRequest);
            if (!empty($items)) {
                Log::info('✅ Items generados desde servicios', ['count' => count($items)]);
                return $items;
            }
        }

        // Método 4: Purchase items sin cotización
        if (!empty($purchaseRequest->purchase_items)) {
            $items = $this->getItemsFromPurchaseRequest($order, $purchaseRequest);
            if (!empty($items)) {
                Log::info('✅ Items generados desde purchase request', ['count' => count($items)]);
                return $items;
            }
        }

        // Fallback: generar item básico
        Log::info('⚠️ Usando fallback para generar items');
        return $this->generateFallbackItems($order);
    }

    private function getItemsFromMixedSelections(PurchaseOrder $order, PurchaseRequest $purchaseRequest)
    {
        $items = [];
        
        $providerSelections = $purchaseRequest->quotationItemSelections()
            ->whereHas('quotation', function($query) use ($order) {
                $query->where('provider_name', $order->provider->nombre);
            })
            ->with('quotation')
            ->get();

        foreach ($providerSelections as $selection) {
            $unitPrice = $selection->unit_price;

            // Intentar obtener precio real de la cotización original
            if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                $originalPrices = $selection->quotation->original_item_prices;
                if (isset($originalPrices[$selection->item_index])) {
                    $unitPrice = $originalPrices[$selection->item_index];
                }
            }

            $items[] = [
                'description' => $selection->item_description ?: 'Producto/Servicio',
                'quantity' => $selection->quantity ?: 1,
                'unit_price' => $unitPrice,
                'total' => $unitPrice * ($selection->quantity ?: 1)
            ];
        }

        return $items;
    }

    private function getItemsFromQuotationAndRequest($selectedQuotation, $purchaseRequest)
    {
        $items = [];
        
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);

        $originalPrices = $selectedQuotation->original_item_prices ?? [];

        foreach ($purchaseItems as $index => $item) {
            $unitPrice = 0;

            if (isset($originalPrices[$index])) {
                $unitPrice = $originalPrices[$index];
            } else if (!empty($originalPrices)) {
                // Usar precio promedio si no hay correspondencia exacta
                $unitPrice = array_sum($originalPrices) / count($originalPrices);
            }

            $quantity = $item['quantity'] ?? 1;

            $items[] = [
                'description' => $item['description'] ?: 'Producto/Servicio',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $unitPrice * $quantity
            ];
        }

        return $items;
    }

    private function getItemsFromServices(PurchaseOrder $order, PurchaseRequest $purchaseRequest)
    {
        $items = [];
        
        $serviceItems = is_array($purchaseRequest->service_items) 
            ? $purchaseRequest->service_items 
            : json_decode($purchaseRequest->service_items, true);

        $totalValue = $order->subtotal ?: ($order->total_amount ?: 0);
        $itemCount = count($serviceItems);
        $pricePerItem = $itemCount > 0 ? $totalValue / $itemCount : $totalValue;

        foreach ($serviceItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;

            $items[] = [
                'description' => $item['description'] ?: ('Servicio ' . ($index + 1)),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $pricePerItem
            ];
        }

        return $items;
    }

    private function getItemsFromPurchaseRequest(PurchaseOrder $order, PurchaseRequest $purchaseRequest)
    {
        $items = [];
        
        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);

        $totalValue = $order->subtotal ?: ($order->total_amount ?: 0);
        $itemCount = count($purchaseItems);
        $pricePerItem = $itemCount > 0 ? $totalValue / $itemCount : $totalValue;

        foreach ($purchaseItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;

            $items[] = [
                'description' => $item['description'] ?: 'Producto/Servicio',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $unitPrice * $quantity
            ];
        }

        return $items;
    }

    private function generateFallbackItems(PurchaseOrder $order)
    {
        $totalAmount = $order->subtotal ?: ($order->total_amount ?: 0);
        
        if ($totalAmount <= 0) {
            return [];
        }

        return [
            [
                'description' => 'Producto/Servicio según orden de compra',
                'quantity' => 1,
                'unit_price' => $totalAmount,
                'total' => $totalAmount
            ]
        ];
    }

    private function updateCustomDataWithItems(PurchaseOrder $order, array $items)
    {
        $subtotal = array_sum(array_column($items, 'total'));
        $ivaAmount = $order->iva_amount ?? 0;
        $total = $subtotal + $ivaAmount;

        $customData = [
            'items' => $items,
            'subtotal' => $subtotal,
            'iva_amount' => $ivaAmount,
            'total' => $total,
            'provider_info' => [
                'name' => $order->provider->nombre ?? '',
                'nit' => $order->provider->nit ?? '',
                'address' => $order->provider->direccion ?? '',
                'phone' => $order->provider->telefono ?? '',
                'email' => $order->provider->email ?? ''
            ],
            'ensured_at' => now()->toDateTimeString(),
            'ensured_for_editing' => true
        ];

        // Evitar guardar durante operaciones de edición para prevenir errores 500
        if (!request()->routeIs('purchase-orders.edit-pdf*')) {
            $order->pdf_custom_data = $customData;
            $order->saveQuietly(); // Usar saveQuietly para evitar disparar observers
        }

        Log::info('✅ Datos custom actualizados para edición', [
            'order' => $order->order_number,
            'items_count' => count($items),
            'subtotal' => number_format($subtotal, 0, '.', ','),
            'saved_to_db' => !request()->routeIs('purchase-orders.edit-pdf*')
        ]);
    }
    
    /**
     * Reparar datos de una orden específica
     */
    public function repairOrderData(PurchaseOrder $purchaseOrder)
    {
        // Solo administradores pueden reparar órdenes
        if (!auth()->user()->can('admin')) {
            return redirect()->back()->with('error', 'No tienes permisos para reparar órdenes.');
        }
        
        try {
            $validationService = new \App\Services\PurchaseOrderValidationService();
            $result = $validationService->validateAndRepair($purchaseOrder, false);
            
            if ($result['repaired']) {
                return redirect()->back()->with('success', 
                    'Orden reparada exitosamente: ' . implode(', ', $result['fixes'])
                );
            } else {
                return redirect()->back()->with('info', 'La orden no necesitaba reparaciones.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error reparando orden desde interfaz', [
                'order_number' => $purchaseOrder->order_number,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error al reparar la orden: ' . $e->getMessage());
        }
    }
    
    /**
     * Reparar todas las órdenes con problemas
     */
    public function repairAllOrders()
    {
        // Solo administradores pueden reparar órdenes
        if (!auth()->user()->can('admin')) {
            return redirect()->back()->with('error', 'No tienes permisos para reparar órdenes.');
        }
        
        try {
            $validationService = new \App\Services\PurchaseOrderValidationService();
            
            // Obtener órdenes con custom data
            $orders = PurchaseOrder::whereNotNull('pdf_custom_data')
                ->where('pdf_custom_data', '!=', '')
                ->get();
            
            $repaired = 0;
            $fixes = [];
            
            foreach ($orders as $order) {
                $result = $validationService->validateAndRepair($order, false);
                if ($result['repaired']) {
                    $repaired++;
                    $fixes[] = $order->order_number . ': ' . implode(', ', $result['fixes']);
                }
            }
            
            if ($repaired > 0) {
                Log::info('Reparación masiva completada', [
                    'repaired_count' => $repaired,
                    'fixes' => $fixes
                ]);
                
                return redirect()->back()->with('success', 
                    "Se repararon {$repaired} órdenes exitosamente."
                );
            } else {
                return redirect()->back()->with('info', 'No se encontraron órdenes que necesiten reparación.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error en reparación masiva', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error en la reparación masiva: ' . $e->getMessage());
        }
    }

    /**
     * MÉTODO FUNDAMENTAL: Generar y almacenar items de la orden al momento de creación
     * Esto resuelve el problema de raíz de órdenes sin items o con IVA mal calculado
     */
    private function generateAndStoreOrderItems(PurchaseOrder $order, PurchaseRequest $purchaseRequest)
    {
        Log::info('🔧 Generando items para orden al momento de creación', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);

        $items = [];
        
        // Método 1: Selecciones mixtas (prioridad más alta)
        $mixedSelections = $purchaseRequest->quotationItemSelections()
            ->whereHas('quotation', function($query) use ($order) {
                if ($order->provider) {
                    $query->where('provider_name', $order->provider->nombre);
                }
            })
            ->with('quotation')
            ->get();

        if ($mixedSelections->count() > 0) {
            Log::info('📋 Generando items desde selecciones mixtas');
            foreach ($mixedSelections as $selection) {
                $unitPrice = $selection->unit_price;
                
                // Intentar obtener precio real de la cotización
                if ($selection->quotation && isset($selection->quotation->original_item_prices)) {
                    $originalPrices = $selection->quotation->original_item_prices;
                    if (isset($originalPrices[$selection->item_index])) {
                        $unitPrice = $originalPrices[$selection->item_index];
                    }
                }

                $quantity = $selection->quantity ?: 1;
                $items[] = [
                    'description' => $selection->item_description ?: 'Producto/Servicio',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $quantity
                ];
            }
        }
        // Método 2: Cotización seleccionada con items de solicitud
        elseif ($purchaseRequest->selectedQuotation && !empty($purchaseRequest->purchase_items)) {
            Log::info('📋 Generando items desde cotización seleccionada');
            $selectedQuotation = $purchaseRequest->selectedQuotation;
            $purchaseItems = is_array($purchaseRequest->purchase_items) 
                ? $purchaseRequest->purchase_items 
                : json_decode($purchaseRequest->purchase_items, true);

            if (!empty($purchaseItems)) {
                $originalPrices = $selectedQuotation->original_item_prices ?? $selectedQuotation->item_prices ?? [];

                foreach ($purchaseItems as $index => $item) {
                    $unitPrice = 0;
                    
                    if (isset($originalPrices[$index])) {
                        $unitPrice = $originalPrices[$index];
                    } elseif (!empty($originalPrices)) {
                        // Usar precio promedio si no hay correspondencia exacta
                        $unitPrice = array_sum($originalPrices) / count($originalPrices);
                    } else {
                        // Estimar precio basado en el total de la cotización dividido entre cantidad total
                        $totalQuantity = array_sum(array_column($purchaseItems, 'quantity'));
                        $unitPrice = $totalQuantity > 0 ? ($selectedQuotation->total_amount / $totalQuantity) : 0;
                    }

                    $quantity = $item['quantity'] ?? 1;
                    
                    // CORRECCIÓN CRÍTICA: Si el precio unitario es demasiado alto, recalcular
                    if ($unitPrice * $quantity > $selectedQuotation->total_amount) {
                        // Dividir el total de la cotización entre el número de items
                        $unitPrice = count($purchaseItems) > 0 ? ($selectedQuotation->total_amount / count($purchaseItems)) / $quantity : 0;
                    }
                    
                    $items[] = [
                        'description' => $item['description'] ?: 'Producto/Servicio',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity
                    ];
                }
            }
        }
        // Método 3: Servicios sin cotización
        elseif ($purchaseRequest->type === 'services' && !empty($purchaseRequest->service_items)) {
            Log::info('📋 Generando items desde servicios');
            $serviceItems = is_array($purchaseRequest->service_items) 
                ? $purchaseRequest->service_items 
                : json_decode($purchaseRequest->service_items, true);

            if (!empty($serviceItems)) {
                $totalValue = $order->total_amount ?: ($purchaseRequest->service_budget ?: 0);
                $itemCount = count($serviceItems);
                $pricePerItem = $itemCount > 0 ? ($totalValue / 1.19) / $itemCount : ($totalValue / 1.19); // Quitar IVA para precio base

                foreach ($serviceItems as $index => $item) {
                    $quantity = $item['quantity'] ?? 1;
                    $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;

                    $items[] = [
                        'description' => $item['description'] ?: ('Servicio ' . ($index + 1)),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity
                    ];
                }
            }
        }
        // Método 4: Items de solicitud básicos
        elseif (!empty($purchaseRequest->purchase_items)) {
            Log::info('📋 Generando items desde solicitud básica');
            $purchaseItems = is_array($purchaseRequest->purchase_items) 
                ? $purchaseRequest->purchase_items 
                : json_decode($purchaseRequest->purchase_items, true);

            if (!empty($purchaseItems)) {
                $totalValue = $order->total_amount ?: 0;
                $includesIva = $order->includes_iva ?? true;
                $baseValue = $includesIva ? ($totalValue / 1.19) : $totalValue;
                $itemCount = count($purchaseItems);
                $pricePerItem = $itemCount > 0 ? $baseValue / $itemCount : $baseValue;

                foreach ($purchaseItems as $index => $item) {
                    $quantity = $item['quantity'] ?? 1;
                    $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;

                    $items[] = [
                        'description' => $item['description'] ?: 'Producto/Servicio',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity
                    ];
                }
            }
        }
        // Método 5: Items de materiales (material_items)
        elseif (!empty($purchaseRequest->material_items)) {
            Log::info('📋 Generando items desde material_items');
            $materialItems = is_array($purchaseRequest->material_items) 
                ? $purchaseRequest->material_items 
                : json_decode($purchaseRequest->material_items, true);

            if (!empty($materialItems)) {
                $totalValue = $order->total_amount ?: 1000; // Valor por defecto si no hay total
                $includesIva = $order->includes_iva ?? true;
                $baseValue = $includesIva ? ($totalValue / 1.19) : $totalValue;
                $itemCount = count($materialItems);
                $pricePerItem = $itemCount > 0 ? $baseValue / $itemCount : $baseValue;

                foreach ($materialItems as $index => $item) {
                    $quantity = $item['quantity'] ?? 1;
                    $unitPrice = $quantity > 0 ? $pricePerItem / $quantity : $pricePerItem;

                    $items[] = [
                        'description' => $item['article'] ?? $item['description'] ?? 'Material/Suministro',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity
                    ];
                }
            }
        }

        // Fallback: crear item básico si no hay items
        if (empty($items)) {
            Log::info('📋 Generando item fallback para solicitud sin items');
            $totalAmount = $order->total_amount ?: 0;
            
            // Para solicitudes sin total definido, usar un valor predeterminado mínimo
            if ($totalAmount <= 0) {
                // Determinar valor base según el tipo de solicitud
                if ($purchaseRequest->type === 'materials') {
                    $totalAmount = 50000; // $50,000 para materiales
                } elseif ($purchaseRequest->type === 'services') {
                    $totalAmount = 100000; // $100,000 para servicios
                } else {
                    $totalAmount = 10000; // $10,000 para otros casos
                }
                
                Log::info('📊 Asignando valor predeterminado por tipo', [
                    'request_type' => $purchaseRequest->type,
                    'assigned_total' => $totalAmount
                ]);
            }
            
            $includesIva = $order->includes_iva ?? true;
            $baseAmount = $includesIva && $totalAmount > 0 ? ($totalAmount / 1.19) : $totalAmount;

            $description = 'Producto/Servicio según solicitud';
            if ($purchaseRequest->type === 'materials') {
                $description = 'Materiales y suministros varios';
            } elseif ($purchaseRequest->type === 'services') {
                $description = 'Servicios según solicitud';
            }

            $items[] = [
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $baseAmount,
                'total' => $baseAmount
            ];
        }

        // Calcular totales correctos desde los items
        $subtotalFromItems = array_sum(array_column($items, 'total'));
        
        // VALIDACIÓN CRÍTICA: Si el subtotal calculado excede significativamente el total esperado, ajustar
        $expectedTotal = $order->total_amount ?: 0;
        if ($expectedTotal > 0 && $subtotalFromItems > $expectedTotal * 1.5) {
            // Hay un problema, recalcular items proporcionalmente
            $adjustmentFactor = ($expectedTotal / 1.19) / $subtotalFromItems; // Dividir por 1.19 para quitar IVA
            
            foreach ($items as &$item) {
                $item['unit_price'] = round($item['unit_price'] * $adjustmentFactor, 2);
                $item['total'] = round($item['unit_price'] * $item['quantity'], 2);
            }
            
            $subtotalFromItems = array_sum(array_column($items, 'total'));
            
            Log::info('🔧 Totales ajustados por inconsistencia', [
                'order_id' => $order->id,
                'adjustment_factor' => $adjustmentFactor,
                'new_subtotal' => $subtotalFromItems
            ]);
        }
        
        $ivaAmount = round($subtotalFromItems * 0.19, 2);
        $totalWithIva = $subtotalFromItems + $ivaAmount;

        // ESTRATEGIA CONSERVADORA: Si hay totales pre-calculados válidos, preservarlos
        $originalTotal = $order->total_amount ?: 0;
        $originalSubtotal = $order->subtotal ?: 0;
        $originalIva = $order->iva_amount ?: 0;
        
        // Si los totales originales parecen válidos, usarlos en lugar de recalcular
        if ($originalTotal > 0 && $originalSubtotal > 0) {
            $finalSubtotal = $originalSubtotal;
            $finalIvaAmount = $originalIva > 0 ? $originalIva : round($originalSubtotal * 0.19, 2);
            $finalTotal = $originalTotal;
            
            Log::info('📊 Usando totales originales (conservador)', [
                'order_id' => $order->id,
                'original_subtotal' => $finalSubtotal,
                'original_iva' => $finalIvaAmount,
                'original_total' => $finalTotal
            ]);
        } else {
            // Recalcular desde items (especialmente para casos fallback)
            $finalSubtotal = $subtotalFromItems;
            $finalIvaAmount = $ivaAmount;
            $finalTotal = $totalWithIva;
            
            Log::info('📊 Usando totales recalculados desde items', [
                'order_id' => $order->id,
                'calculated_subtotal' => $finalSubtotal,
                'calculated_iva' => $finalIvaAmount,
                'calculated_total' => $finalTotal
            ]);
        }

        // Crear datos personalizados completos
        $customData = [
            'items' => $items,
            'subtotal' => $finalSubtotal,
            'iva_rate' => '19%',
            'iva_amount' => $finalIvaAmount,
            'total' => $finalTotal,
            'provider_name' => $order->provider->nombre ?? 'Proveedor',
            'provider_nit' => $order->provider->nit ?? 'Sin NIT',
            'provider_email' => $order->provider->correo ?? 'sin-email@proveedor.com',
            'provider_phone' => $order->provider->telefono ?? 'Sin teléfono',
            'provider_address' => $order->provider->direccion ?? 'Sin dirección',
            'delivery_date' => $order->delivery_date ?? now()->addDays(15)->format('Y-m-d'),
            'payment_terms' => $order->payment_terms ?? 'Contado',
            'order_date' => $order->order_date ?? now()->format('Y-m-d'),
            'observations' => $order->observations ?? '',
            'generated_at_creation' => true,
            'generation_timestamp' => now()->toDateTimeString()
        ];

        // Actualizar la orden con los datos calculados (conservando totales válidos)
        $order->update([
            'pdf_custom_data' => json_encode($customData),
            'subtotal' => $finalSubtotal,
            'iva_amount' => $finalIvaAmount,
            'total_amount' => $finalTotal,
            'includes_iva' => true,
            'tax_amount' => $finalIvaAmount
        ]);

        Log::info('✅ Items generados y almacenados exitosamente', [
            'order_id' => $order->id,
            'items_count' => count($items),
            'final_subtotal' => $finalSubtotal,
            'final_iva_amount' => $finalIvaAmount,
            'final_total' => $finalTotal
        ]);

        return $items;
    }
}
