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
        $approvedRequests = PurchaseRequest::with(['selectedQuotation', 'user', 'approver', 'quotationItemSelections'])
            ->whereIn('status', ['approved', 'in_process'])
            ->whereNotIn('id', function($query) {
                $query->select('purchase_request_id')
                    ->from('purchase_orders')
                    ->whereNull('deleted_at');
            })
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
            ->orderBy('approval_date', 'desc')
            ->get();
            
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
        
        // Cargar las selecciones mixtas si existen
        $mixedSelections = $hasMixedSelection ? $purchaseRequest->quotationItemSelections()->with('quotation')->get() : collect();
        
        Log::info('Datos para la vista create', [
            'has_selected_quotation' => $hasSelectedQuotation,
            'has_mixed_selection' => $hasMixedSelection,
            'mixed_selections_count' => $mixedSelections->count(),
            'is_no_quotation_service' => $isNoQuotationService
        ]);
        
        return view('purchase-orders.create', compact('purchaseRequest', 'hasSelectedQuotation', 'hasMixedSelection', 'mixedSelections', 'isNoQuotationService'));
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
            } elseif ($hasMixedSelection && !$providerId) {
                // Crear o usar proveedor especial para selecciones mixtas
                $mixedProvider = \App\Models\Proveedor::firstOrCreate([
                    'nombre' => 'Selección Mixta de Proveedores',
                    'nit' => 'MIXTA-000'
                ], [
                    'email' => 'mixta@proveedores.com',
                    'direccion' => 'Múltiples proveedores',
                    'telefono' => '000-000-0000',
                    'persona_contacto' => 'Ver detalles de la orden',
                    'ciudad' => 'Múltiples ciudades',
                    'servicio_producto' => 'Selección Mixta'
                ]);
                $providerId = $mixedProvider->id;
                $orderNumber = 'OC-' . date('Ym') . '-' . str_pad(PurchaseOrder::count() + 1, 3, '0', STR_PAD_LEFT);
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
        // Verificar que el usuario sea administrador
        if (!auth()->user()->hasRole('admin')) {
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

        // Usar el alias 'order' para compatibilidad con la vista
        $order = $purchaseOrder;

        return view('purchase-orders.edit-pdf-new', compact('order'));
    }

    /**
     * Mostrar formulario mejorado para editar el PDF de una orden de compra (solo admin)
     * Vista que replica exactamente el formato del PDF con todos los impuestos
     */
    public function editPdfNew(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->hasRole('admin')) {
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

        // Usar el alias 'order' para compatibilidad con la vista
        $order = $purchaseOrder;

        return view('purchase-orders.edit-pdf-new', compact('order'));
    }

    /**
     * Actualizar los datos del PDF de una orden de compra (solo admin)
     */
    public function updatePdf(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para editar PDFs de órdenes de compra.');
        }

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'provider_name' => 'required|string|max:255',
            'provider_nit' => 'nullable|string|max:50',
            'provider_email' => 'nullable|email|max:255',
            'provider_phone' => 'nullable|string|max:50',
            'provider_address' => 'nullable|string|max:500',
            'provider_city' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'iva_rate' => 'nullable|numeric|min:0|max:100',
            'iva_amount' => 'nullable|numeric|min:0',
            'ipoconsumo_rate' => 'nullable|numeric|min:0|max:100',
            'ipoconsumo_amount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
            'additional_items' => 'nullable|array',
            'additional_items.*.description' => 'nullable|string|max:255',
            'additional_items.*.quantity' => 'nullable|numeric|min:0',
            'additional_items.*.unit_price' => 'nullable|numeric|min:0',
            'additional_items.*.total' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Guardar datos originales para el log
            $originalData = [
                'order_number' => $purchaseOrder->order_number,
                'total_amount' => $purchaseOrder->total_amount,
                'provider_id' => $purchaseOrder->provider_id,
                'pdf_custom_data' => $purchaseOrder->pdf_custom_data,
            ];

            // Preparar datos personalizados para guardar
            $customData = [
                'provider_name' => $request->provider_name,
                'provider_nit' => $request->provider_nit,
                'provider_email' => $request->provider_email,
                'provider_phone' => $request->provider_phone,
                'provider_address' => $request->provider_address,
                'provider_city' => $request->provider_city,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'iva_rate' => $request->iva_rate . '%',
                'iva_amount' => $request->iva_amount ?? 0,
                'ipoconsumo_rate' => $request->ipoconsumo_rate . '%',
                'ipoconsumo_amount' => $request->ipoconsumo_amount ?? 0,
                'subtotal' => $request->subtotal,
                'total' => $request->total,
                'items' => $request->items ?? [],
                'additional_items' => $request->additional_items ?? [],
                'observations' => $request->observations,
                'edited_by' => auth()->user()->id,
                'edited_at' => now()->toISOString(),
            ];

            // Actualizar los datos de la orden
            $updateResult = $purchaseOrder->update([
                'delivery_date' => $request->delivery_date,
                'subtotal' => $request->subtotal,
                'iva_amount' => $request->iva_amount ?? 0,
                'tax_amount' => $request->iva_amount ?? 0,
                'total_amount' => $request->total,
                'includes_iva' => ($request->iva_amount ?? 0) > 0,
                'observations' => $request->observations,
                'pdf_custom_data' => json_encode($customData),
                'updated_at' => now()
            ]);

            // Regenerar el PDF con los nuevos datos
            $pdfPath = $this->pdfService->generatePdf($purchaseOrder);
            $purchaseOrder->update(['file_path' => $pdfPath]);

            // Registrar la edición en el log
            Log::info('PDF de orden de compra editado por administrador', [
                'order_id' => $purchaseOrder->id,
                'order_number' => $purchaseOrder->order_number,
                'edited_by' => auth()->user()->id,
                'edited_by_name' => auth()->user()->name,
                'original_data' => $originalData,
                'new_subtotal' => $request->subtotal,
                'new_iva_amount' => $request->iva_amount ?? 0,
                'new_ipoconsumo_amount' => $request->ipoconsumo_amount ?? 0,
                'new_total' => $request->total,
                'pdf_path' => $pdfPath
            ]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'PDF de la orden de compra actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al actualizar PDF de orden de compra', [
                'order_id' => $purchaseOrder->id,
                'error' => $e->getMessage(),
                'edited_by' => auth()->user()->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar el PDF: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function regeneratePdf(PurchaseOrder $purchaseOrder)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->hasRole('admin')) {
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

            // PASO 2: Generar nuevo PDF con los datos recalculados
            $pdfPath = $this->pdfService->generatePdf($purchaseOrder);

            // PASO 3: Actualizar la orden con la nueva información
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
            // Si no hay items específicos con impuestos, aplicar IVA por defecto del 19%
            $ivaRate = 19;
            $ivaAmount = ($baseAmount * $ivaRate) / 100;
            
            $appliedTaxes['IVA'] = [
                'name' => 'IVA',
                'rate' => $ivaRate,
                'amount' => $ivaAmount
            ];
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
                $appliedTaxes['IVA 19%'] = [
                    'name' => 'IVA',
                    'rate' => 19,
                    'amount' => $quotation->iva_19_amount
                ];
                $totalTaxes += $quotation->iva_19_amount;
            }
            
            if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                $appliedTaxes['IVA 5%'] = [
                    'name' => 'IVA',
                    'rate' => 5,
                    'amount' => $quotation->iva_5_amount
                ];
                $totalTaxes += $quotation->iva_5_amount;
            }
            
            if ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) {
                $appliedTaxes['Impuesto al Consumo 8%'] = [
                    'name' => 'Impuesto al Consumo',
                    'rate' => 8,
                    'amount' => $quotation->ipoconsumo_8_amount
                ];
                $totalTaxes += $quotation->ipoconsumo_8_amount;
            }
            
            if ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0) {
                $appliedTaxes['Impuesto al Consumo 4%'] = [
                    'name' => 'Impuesto al Consumo',
                    'rate' => 4,
                    'amount' => $quotation->ipoconsumo_4_amount
                ];
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
                        $appliedTaxes['IVA 19%'] = [
                            'name' => 'IVA',
                            'rate' => $taxRate,
                            'amount' => 0
                        ];
                    }
                    $appliedTaxes['IVA 19%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_iva_5 && $quotation->iva_5_amount > 0) {
                    $taxRate = 5;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['IVA 5%'])) {
                        $appliedTaxes['IVA 5%'] = [
                            'name' => 'IVA',
                            'rate' => $taxRate,
                            'amount' => 0
                        ];
                    }
                    $appliedTaxes['IVA 5%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_ipoconsumo_8 && $quotation->ipoconsumo_8_amount > 0) {
                    $taxRate = 8;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['Impuesto al Consumo 8%'])) {
                        $appliedTaxes['Impuesto al Consumo 8%'] = [
                            'name' => 'Impuesto al Consumo',
                            'rate' => $taxRate,
                            'amount' => 0
                        ];
                    }
                    $appliedTaxes['Impuesto al Consumo 8%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
                
                if ($quotation->includes_ipoconsumo_4 && $quotation->ipoconsumo_4_amount > 0) {
                    $taxRate = 4;
                    $taxAmount = ($itemSubtotal * $taxRate) / 100;
                    
                    if (!isset($appliedTaxes['Impuesto al Consumo 4%'])) {
                        $appliedTaxes['Impuesto al Consumo 4%'] = [
                            'name' => 'Impuesto al Consumo',
                            'rate' => $taxRate,
                            'amount' => 0
                        ];
                    }
                    $appliedTaxes['Impuesto al Consumo 4%']['amount'] += $taxAmount;
                    $totalTaxes += $taxAmount;
                }
            } else {
                // Si no hay cotización asociada, aplicar IVA por defecto
                $taxRate = 19;
                $taxAmount = ($itemSubtotal * $taxRate) / 100;
                
                if (!isset($appliedTaxes['IVA 19%'])) {
                    $appliedTaxes['IVA 19%'] = [
                        'name' => 'IVA',
                        'rate' => $taxRate,
                        'amount' => 0
                    ];
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
}
