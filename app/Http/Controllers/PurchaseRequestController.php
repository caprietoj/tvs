<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\PurchaseRequestCreated;
use App\Mail\PurchaseRequestCreatedCompras;
use App\Mail\PurchaseRequestCreatedUsuario;
use App\Mail\ServiceNoQuotationCreated;
use App\Services\PurchaseRequestPdfService;
use App\Services\SectionClassifierService;
use App\Services\PurchaseRequestPermissionService;

class PurchaseRequestController extends Controller
{    /**
    * Constructor
    */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Enviar emails diferenciados para el usuario y el área de compras
     */
    private function sendDifferentiatedEmails(PurchaseRequest $purchaseRequest)
    {
        try {
            // Email para el usuario solicitante (sin botón)
            $userEmail = \App\Services\EmailTestModeService::interceptEmail(auth()->user()->email);
            
            Mail::to($userEmail)
                ->send(new PurchaseRequestCreatedUsuario($purchaseRequest));
            
            \Log::info('Email de confirmación enviado al usuario: ' . $userEmail . ' (original: ' . auth()->user()->email . ') para solicitud #' . $purchaseRequest->id);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de confirmación al usuario para solicitud #' . $purchaseRequest->id . ': ' . $e->getMessage());
        }

        try {
            // Email para el área de compras (con botón de acción)
            // Usar configuración dinámica para el correo de compras
            $comprasEmail = config(\App\Services\DynamicSectionEmailsService::getCurrentConfigSource() . '.default');
            $comprasEmail = \App\Services\EmailTestModeService::interceptEmail($comprasEmail);
            
            Mail::to($comprasEmail)
                ->send(new PurchaseRequestCreatedCompras($purchaseRequest));
            
            \Log::info('Email de acción enviado al área de compras para solicitud #' . $purchaseRequest->id . ' a: ' . $comprasEmail);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email al área de compras para solicitud #' . $purchaseRequest->id . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar emails de aprobación específicos para solicitudes de materiales basados en la sección
     */
    private function sendMaterialsApprovalEmails(PurchaseRequest $purchaseRequest)
    {
        $sectionClassifier = new SectionClassifierService();
        $approvalEmails = $sectionClassifier->getMaterialsApprovalEmails($purchaseRequest->section);
        
        // Si es una solicitud de fotocopias, agregar auxiliar almacén según configuración dinámica
        if ($purchaseRequest->isCopiesRequest()) {
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $auxiliarEmail = config($configSource . '.sections.Auxiliar Almacén', 'auxiliaralmacen@tvs.edu.co');
            $approvalEmails[] = $auxiliarEmail;
            \Log::info("Email auxiliar almacén ({$auxiliarEmail}) agregado para solicitud de fotocopias #" . $purchaseRequest->id);
        }
        
        if (!empty($approvalEmails)) {
            foreach ($approvalEmails as $email) {
                try {
                    $interceptedEmail = \App\Services\EmailTestModeService::interceptEmail($email);
                    
                    Mail::to($interceptedEmail)
                        ->send(new PurchaseRequestCreatedCompras($purchaseRequest));
                    
                    $requestType = $purchaseRequest->isCopiesRequest() ? 'fotocopias' : 'materiales';
                    \Log::info('Email de aprobación enviado a ' . $interceptedEmail . ' (original: ' . $email . ') para solicitud de ' . $requestType . ' #' . $purchaseRequest->id . ' (Sección: ' . $purchaseRequest->section . ')');
                } catch (\Exception $e) {
                    \Log::error('Error al enviar email de aprobación a ' . $email . ' para solicitud #' . $purchaseRequest->id . ': ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('No se encontraron emails de aprobación para la sección: ' . $purchaseRequest->section . ' en solicitud #' . $purchaseRequest->id);
        }
    }

    /**
     * Enviar emails específicos para servicios sin cotización
     */
    private function sendNoQuotationServiceEmails(PurchaseRequest $purchaseRequest)
    {
        try {
            // 1. Email de confirmación al usuario solicitante
            $userEmail = \App\Services\EmailTestModeService::interceptEmail(auth()->user()->email);
            
            Mail::to($userEmail)
                ->send(new ServiceNoQuotationCreated($purchaseRequest, 'user'));
            
            \Log::info('Email de confirmación enviado al usuario: ' . $userEmail . ' para servicio sin cotización #' . $purchaseRequest->id);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de confirmación al usuario para servicio sin cotización #' . $purchaseRequest->id . ': ' . $e->getMessage());
        }

        try {
            // 2. Email al área de compras (con información especial de servicio sin cotización)
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            // Interceptar email si está en modo de prueba
            $comprasEmail = \App\Services\EmailTestModeService::interceptEmail($comprasEmail);
            
            Mail::to($comprasEmail)
                ->send(new ServiceNoQuotationCreated($purchaseRequest, 'compras'));
            
            \Log::info('Email enviado al área de compras para servicio sin cotización #' . $purchaseRequest->id . ' a: ' . $comprasEmail);
        } catch (\Exception $e) {
            \Log::error('Error al enviar email al área de compras para servicio sin cotización #' . $purchaseRequest->id . ': ' . $e->getMessage());
        }

        try {
            // 3. Email a los responsables de pre-aprobación (directores/coordinadores según la sección)
            $this->sendServicePreApprovalEmails($purchaseRequest);
        } catch (\Exception $e) {
            \Log::error('Error al enviar emails de pre-aprobación para servicio sin cotización #' . $purchaseRequest->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Enviar emails de pre-aprobación para servicios sin cotización
     */
    private function sendServicePreApprovalEmails(PurchaseRequest $purchaseRequest)
    {
        $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
        $approvalEmails = [];

        // Determinar emails de pre-aprobación basados en la sección/área
        switch ($purchaseRequest->section_area) {
            case 'Pre Escolar':
            case 'Primaria':
            case 'Bachillerato':
                $approvalEmails[] = config($configSource . '.sections.Director Académico', 'director.academico@tvs.edu.co');
                break;
            
            case 'PEP':
            case 'PAI':
            case 'Diploma':
                $approvalEmails[] = config($configSource . '.sections.Coordinador IB', 'coordinador.ib@tvs.edu.co');
                break;
            
            case 'Administración':
            case 'Dirección General':
            case 'Compras':
            case 'Sistemas':
            case 'Mantenimiento':
                $approvalEmails[] = config($configSource . '.sections.Director Administrativo', 'director.administrativo@tvs.edu.co');
                break;
            
            case 'Psicología':
            case 'CAS':
                $approvalEmails[] = config($configSource . '.sections.Coordinador Bienestar', 'coordinador.bienestar@tvs.edu.co');
                break;
            
            default:
                // Para secciones no específicas, enviar al director administrativo por defecto
                $approvalEmails[] = config($configSource . '.sections.Director Administrativo', 'director.administrativo@tvs.edu.co');
                break;
        }

        // Enviar emails de pre-aprobación
        foreach ($approvalEmails as $email) {
            try {
                // Interceptar email si está en modo de prueba
                $interceptedEmail = \App\Services\EmailTestModeService::interceptEmail($email);
                
                Mail::to($interceptedEmail)
                    ->send(new ServiceNoQuotationCreated($purchaseRequest, 'pre_approval'));
                
                \Log::info('Email de pre-aprobación para servicio sin cotización enviado a ' . $interceptedEmail . ' (original: ' . $email . ') para solicitud #' . $purchaseRequest->id . ' (Sección: ' . $purchaseRequest->section_area . ')');
            } catch (\Exception $e) {
                \Log::error('Error al enviar email de pre-aprobación a ' . $email . ' para servicio sin cotización #' . $purchaseRequest->id . ': ' . $e->getMessage());
            }
        }
        
        if (empty($approvalEmails)) {
            \Log::warning('No se encontraron emails de pre-aprobación para la sección: ' . $purchaseRequest->section_area . ' en servicio sin cotización #' . $purchaseRequest->id);
        }
    }
    
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $permissionService = new PurchaseRequestPermissionService();
        
        // Aplicar filtros basados en permisos del usuario
        $query = PurchaseRequest::query();
        $query = $permissionService->applyQueryFilters($query);
        
        // Filtro por tipo si se especifica
        $typeFilter = $request->get('type');
        if ($typeFilter && in_array($typeFilter, ['purchase', 'materials', 'copies', 'services'])) {
            if ($typeFilter === 'purchase') {
                $query->where('type', 'purchase');
            } elseif ($typeFilter === 'services') {
                $query->where('type', 'services');
            } elseif ($typeFilter === 'materials') {
                $query->where('type', 'materials')
                      ->where(function($q) {
                          $q->whereNull('copy_items')
                            ->orWhereRaw('JSON_LENGTH(copy_items) = 0');
                      });
            } elseif ($typeFilter === 'copies') {
                $query->where('type', 'materials')
                      ->where(function($q) {
                          $q->whereNotNull('copy_items')
                            ->whereRaw('JSON_LENGTH(copy_items) > 0');
                      });
            }
        }
        
        $requests = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Mantener los parámetros de filtro en la paginación
        $requests->appends($request->query());
        
        return view('purchase-requests.index', compact('requests', 'typeFilter'));
    }

    /**
    * Show the form for creating a new resource.
    */
    public function create()
    {
    // Mostrar la página de selección de tipo de solicitud
    return view('purchase-requests.create');
    }

    /**
    * Show form for purchase request
    */
    public function createPurchaseForm()
    {
    return view('purchase-requests.create-purchase');
    }

    /**
    * Show form for services request
    */
    public function createServicesForm()
    {
    return view('purchase-requests.create-services');
    }

    /**
    * Show form for materials request
    */
    public function createMaterialsForm()
    {
    $user = auth()->user();
    // Obtener los productos del inventario para el campo de artículo
    $inventoryItems = \App\Models\InventoryItem::orderBy('producto')->get();
    return view('purchase-requests.create-materials', compact('user', 'inventoryItems'));
    }

    /**
    * Show form for copies request
    */
    public function createCopiesForm()
    {
    $user = auth()->user();
    return view('purchase-requests.create-copies', compact('user'));
    }

    /**
    * Store a newly created resource in storage.
    */
    public function store(Request $request)
    {
    // Validar tipo de solicitud
    $validatedType = $request->validate([
    'type' => 'required|in:purchase,materials,services',
    ]);

    if ($validatedType['type'] === 'purchase') {
        return $this->storePurchaseRequest($request);
    } elseif ($validatedType['type'] === 'services') {
        return $this->storeServicesRequest($request);
    } elseif ($validatedType['type'] === 'materials') {
        // Verificar si es una solicitud de fotocopias o de materiales
        // Si hay copy_items pero no hay material_items, es una solicitud de fotocopias
        if (isset($request->copy_items) && !isset($request->material_items)) {
            return $this->storeCopiesRequest($request);
        } else {
            return $this->storeMaterialsRequest($request);
        }
    }
    }

    /**
    * Store a purchase request
    */
    private function storePurchaseRequest(Request $request)
    {
        // Verificar que se están enviando items de compra
        $hasPurchaseItems = $request->has('purchase_items') && is_array($request->purchase_items) && 
                           count(array_filter($request->purchase_items, function($item) {
                               return !empty($item['description']);
                           })) > 0;
        
        if (!$hasPurchaseItems) {
            return redirect()->route('purchase-requests.create-purchase')
                ->with('error', 'Debe agregar al menos un artículo de compra. Para servicios, utilice el formulario específico de servicios.')
                ->withInput();
        }
        
        // Definir reglas de validación para compras únicamente
        $rules = [
            'requester' => 'required|string|max:255',
            'section_area' => 'required|string|max:255',
            'coordinator' => 'nullable|string|max:255',
            'purchase_justification' => 'required|string',
            'purchase_items' => 'required|array',
            'purchase_items.*.item' => 'required|integer',
            'purchase_items.*.quantity' => 'required|integer|min:1',
            'purchase_items.*.description' => 'required|string',
            'purchase_items.*.unit' => 'required|string',
            'purchase_items.*.observations' => 'nullable|string',
        ];
        
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.create-purchase')
                ->withErrors($validator)
                ->withInput();
        }

        // Crear la solicitud de compra
        $purchaseRequest = PurchaseRequest::create([
            'type' => 'purchase',
            'user_id' => Auth::id(),
            'requester' => $request->requester,
            'section_area' => $request->section_area,
            'coordinator' => $request->coordinator,
            'purchase_justification' => $request->purchase_justification,
            'purchase_items' => $request->purchase_items,
            'status' => 'pending',
        ]);

        // Enviar emails diferenciados
        $this->sendDifferentiatedEmails($purchaseRequest);

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Solicitud de compra creada exitosamente');
    }    /**
     * Store a services request
     */
    private function storeServicesRequest(Request $request)
    {
        // Validar que haya al menos un servicio con descripción
        $hasServiceItems = $request->has('service_items') && is_array($request->service_items) && 
                          count(array_filter($request->service_items, function($item) {
                              return !empty($item['description']);
                          })) > 0;
        
        if (!$hasServiceItems) {
            return redirect()->route('purchase-requests.create-services')
                ->with('error', 'Debe agregar al menos un servicio con descripción.')
                ->withInput();
        }
        
        // Definir reglas de validación base
        $rules = [
            'requester' => 'required|string|max:255',
            'section_area' => 'required|string|max:255',
            'coordinator' => 'nullable|string|max:255',
            'service_justification' => 'required|string',
            'service_budget' => 'nullable|numeric|min:0',
            'service_budget_text' => 'nullable|string|max:255',
            'general_observations' => 'nullable|string',
            'service_items' => 'required|array',
            'service_items.*.item' => 'required|integer',
            'service_items.*.quantity' => 'required|integer|min:1',
            'service_items.*.description' => 'required|string',
            'service_items.*.observations' => 'nullable|string',
            'service_type' => 'required|in:regular,no_quotation',
        ];

        // Agregar validación adicional para servicios sin cotización
        if ($request->service_type === 'no_quotation') {
            $rules['provider_name'] = 'required|string|max:255';
            $rules['provider_nit'] = 'nullable|string|max:255';
            $rules['provider_contact'] = 'nullable|string|max:255';
            $rules['provider_email'] = 'nullable|email|max:255';
            $rules['no_quotation_reason'] = 'required|string';
        }
        
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.create-services')
                ->withErrors($validator)
                ->withInput();
        }

        // Preparar datos para crear la solicitud
        $data = [
            'type' => 'services',
            'user_id' => Auth::id(),
            'requester' => $request->requester,
            'section_area' => $request->section_area,
            'coordinator' => $request->coordinator,
            'service_justification' => $request->service_justification,
            'service_budget' => $request->service_budget,
            'service_budget_text' => $request->service_budget_text,
            'service_items' => $request->service_items,
            'general_observations' => $request->general_observations,
            'status' => 'pending',
            'service_type' => $request->service_type,
        ];

        // Agregar campos del proveedor si es servicio sin cotización
        if ($request->service_type === 'no_quotation') {
            $data['provider_name'] = $request->provider_name;
            $data['provider_nit'] = $request->provider_nit;
            $data['provider_contact'] = $request->provider_contact;
            $data['provider_email'] = $request->provider_email;
            $data['no_quotation_reason'] = $request->no_quotation_reason;
        }

        // Crear la solicitud de servicios
        $purchaseRequest = PurchaseRequest::create($data);

        // Enviar emails diferenciados según el tipo de servicio
        if ($request->service_type === 'no_quotation') {
            // Para servicios sin cotización, enviar emails de pre-aprobación directamente
            $this->sendNoQuotationServiceEmails($purchaseRequest);
        } else {
            // Para servicios regulares, enviar emails normales
            $this->sendDifferentiatedEmails($purchaseRequest);
        }

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Solicitud de servicios creada exitosamente');
    }

    /**
    * Store a materials request
    */
    private function storeMaterialsRequest(Request $request)
    {
    $validator = Validator::make($request->all(), [
    'requester' => 'required|string|max:255',
    'code' => 'nullable|string|max:255',
    'grade' => 'nullable|string|max:255',
    'section' => 'required|string|max:255',
    'delivery_date' => 'required|date',
    'material_items' => 'required|array',
    'material_items.*.item' => 'required|integer',
    'material_items.*.article' => 'required|string',
    'material_items.*.quantity' => 'required|integer|min:1',
    'material_items.*.objective' => 'nullable|string',
    ]);
    
    if ($validator->fails()) {
    return redirect()->route('purchase-requests.create-materials')
    ->withErrors($validator)
    ->withInput();
    }

    // Verificar stock de productos solicitados
    $outOfStockItems = [];
    
    if (isset($request->material_items) && is_array($request->material_items)) {
        foreach ($request->material_items as $item) {
            if (!empty($item['article']) && !empty($item['quantity'])) {
                $inventoryItem = \App\Models\InventoryItem::where('producto', $item['article'])->first();
                
                // Verificar si el producto existe y si hay suficiente stock
                if (!$inventoryItem || $inventoryItem->stock < $item['quantity']) {
                    $outOfStockItems[] = [
                        'article' => $item['article'],
                        'quantity' => $item['quantity'],
                        'available' => $inventoryItem ? $inventoryItem->stock : 0
                    ];
                }
            }
        }
    }
    
    // Si hay productos sin stock, enviar correo de notificación
    if (!empty($outOfStockItems)) {
        try {
            $userEmail = \App\Services\EmailTestModeService::interceptEmail(auth()->user()->email);
            
            Mail::to($userEmail)
                ->send(new \App\Mail\NoStockNotification($outOfStockItems, auth()->user()->name));
            
            \Log::info('Email de notificación de productos sin stock enviado a: ' . $userEmail . ' (original: ' . auth()->user()->email . ')');
            
            return redirect()->route('purchase-requests.create-materials')
                ->with('warning', 'Algunos productos solicitados no tienen stock suficiente. Se ha enviado un correo con los detalles.')
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de notificación de stock: ' . $e->getMessage());
            // Continuar con la creación de la solicitud
        }
    }

    // Iniciar transacción de base de datos
    \DB::beginTransaction();
    
    try {
        // Crear la solicitud de materiales
        $materialsRequest = PurchaseRequest::create([
            'type' => 'materials',
            'user_id' => Auth::id(),
            'requester' => $request->requester,
            'code' => $request->code,
            'grade' => $request->grade,
            'section' => $request->section,
            'section_area' => $request->section, // También se guarda en section_area para consistencia
            'delivery_date' => $request->delivery_date,
            'material_items' => $request->material_items,
            'status' => 'pending',
        ]);
        
        // Descontar los productos del inventario
        $inventoryUpdated = false;
        $errorMessages = [];
        
        if (isset($request->material_items) && is_array($request->material_items)) {
            foreach ($request->material_items as $item) {
                if (!empty($item['article']) && !empty($item['quantity'])) {
                    $inventoryItem = \App\Models\InventoryItem::where('producto', $item['article'])->first();
                    
                    if ($inventoryItem) {
                        // Actualizar el stock del producto
                        $oldStock = $inventoryItem->stock;
                        $inventoryItem->stock = $oldStock - $item['quantity'];
                        
                        if ($inventoryItem->save()) {
                            $inventoryUpdated = true;
                            \Log::info("Producto descontado del inventario: {$item['article']}, Cantidad: {$item['quantity']}, Stock anterior: {$oldStock}, Nuevo stock: {$inventoryItem->stock}");
                            
                            // Registrar movimiento en el historial de inventario si existe tal modelo
                            try {
                                if (class_exists('\App\Models\InventoryMovement')) {
                                    \App\Models\InventoryMovement::create([
                                        'inventory_item_id' => $inventoryItem->id,
                                        'tipo_movimiento' => 'salida',
                                        'cantidad' => $item['quantity'],
                                        'detalle' => 'Solicitud de materiales #' . $materialsRequest->id,
                                        'solicitante' => $request->requester,
                                        'user_id' => Auth::id()
                                    ]);
                                }
                            } catch (\Exception $e) {
                                \Log::error('Error al registrar movimiento de inventario: ' . $e->getMessage());
                                // No interrumpir el flujo si falla el registro del movimiento
                            }
                        } else {
                            $errorMessages[] = "No se pudo actualizar el stock de {$item['article']}";
                        }
                    } else {
                        $errorMessages[] = "No se encontró el producto {$item['article']} en el inventario";
                    }
                }
            }
        }
        
        // Si hay errores en la actualización del inventario, registrarlos pero continuar
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                \Log::warning('Error al descontar inventario: ' . $message);
            }
        }
        
        // Verificar si la solicitud debe ser auto-aprobada
        $autoApproved = $materialsRequest->autoApproveIfEligible();
        
        // Enviar emails diferenciados para solicitud de materiales
        $this->sendDifferentiatedEmails($materialsRequest);
        
        // Enviar emails de aprobación específicos por sección
        $this->sendMaterialsApprovalEmails($materialsRequest);
        
        // Confirmar transacción si todo está bien
        \DB::commit();
        
        $message = 'Solicitud de materiales creada exitosamente';
        if ($inventoryUpdated) {
            $message .= ' y se actualizó el inventario';
        }
        
        // Agregar información sobre aprobación automática
        if ($autoApproved) {
            $totalMaterials = $materialsRequest->getTotalMaterialsQuantity();
            $message .= " y aprobada automáticamente (Total: {$totalMaterials} materiales ≤ 15)";
        } else {
            $totalMaterials = $materialsRequest->getTotalMaterialsQuantity();
            $message .= " (Total: {$totalMaterials} materiales > 15 - Requiere aprobación manual)";
        }
        
        return redirect()->route('purchase-requests.index')
            ->with('success', $message);
            
    } catch (\Exception $e) {
        // Si hay cualquier error, revertir la transacción
        \DB::rollback();
        \Log::error('Error al crear solicitud de materiales: ' . $e->getMessage());
        
        return redirect()->route('purchase-requests.create-materials')
            ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
            ->withInput();
    }
    }

    /**
    * Display the specified resource.
    */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $permissionService = new PurchaseRequestPermissionService();
        
        // Verificar si el usuario puede ver esta solicitud
        if (!$permissionService->canViewRequest($purchaseRequest)) {
            abort(403, 'No tienes permisos para ver esta solicitud.');
        }
        
        return view('purchase-requests.show', compact('purchaseRequest'));
    }

    /**
    * Show the form for editing the specified resource.
    */
    public function edit(PurchaseRequest $purchaseRequest)
    {
        $permissionService = new PurchaseRequestPermissionService();
        
        // Verificar permisos de edición
        $canEditRegular = $permissionService->canEditRequest($purchaseRequest);
        $canEditApprovedCopies = $permissionService->canEditApprovedCopiesRequest($purchaseRequest);
        
        // Si no tiene ningún permiso de edición, denegar acceso
        if (!$canEditRegular && !$canEditApprovedCopies) {
            abort(403, 'No tienes permisos para editar esta solicitud.');
        }
        
        if ($purchaseRequest->type === 'purchase') {
            return view('purchase-requests.edit-purchase', compact('purchaseRequest'));
        } elseif ($purchaseRequest->type === 'services') {
            // Si es una solicitud de servicios
            return view('purchase-requests.edit-services', compact('purchaseRequest'));
        } elseif ($purchaseRequest->isCopiesRequest()) {
            // Si es una solicitud de fotocopias
            return view('purchase-requests.edit-copies', compact('purchaseRequest'));
        } else {
            // Si es una solicitud de materiales
            // Obtener los productos del inventario para el campo de artículo
            $inventoryItems = \App\Models\InventoryItem::orderBy('producto')->get();
            return view('purchase-requests.edit-materials', compact('purchaseRequest', 'inventoryItems'));
        }
    }

    /**
    * Update the specified resource in storage.
    */
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $permissionService = new PurchaseRequestPermissionService();
        
        // Verificar permisos de edición
        $canEditRegular = $permissionService->canEditRequest($purchaseRequest);
        $canEditApprovedCopies = $permissionService->canEditApprovedCopiesRequest($purchaseRequest);
        
        // Si no tiene ningún permiso de edición, denegar acceso
        if (!$canEditRegular && !$canEditApprovedCopies) {
            abort(403, 'No tienes permisos para editar esta solicitud.');
        }
        
        if ($purchaseRequest->type === 'purchase') {
            return $this->updatePurchaseRequest($request, $purchaseRequest);
        } elseif ($purchaseRequest->type === 'services') {
            return $this->updateServicesRequest($request, $purchaseRequest);
        } elseif ($purchaseRequest->isCopiesRequest()) {
            return $this->updateCopiesRequest($request, $purchaseRequest);
        } else {
            return $this->updateMaterialsRequest($request, $purchaseRequest);
        }
    }

    /**
    * Update a purchase request
    */
    private function updatePurchaseRequest(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validator = Validator::make($request->all(), [
            'requester' => 'required|string|max:255',
            'section_area' => 'required|string|max:255',
            'purchase_justification' => 'required|string',
            'purchase_items' => 'required|array',
            'purchase_items.*.item' => 'required|integer',
            'purchase_items.*.quantity' => 'required|integer|min:1',
            'purchase_items.*.description' => 'required|string',
            'purchase_items.*.unit' => 'required|string',
            'purchase_items.*.observations' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.edit', $purchaseRequest)
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar la solicitud de compra
        $purchaseRequest->update([
            'requester' => $request->requester,
            'section_area' => $request->section_area,
            'purchase_justification' => $request->purchase_justification,
            'purchase_items' => $request->purchase_items,
        ]);
        
        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Solicitud de compra actualizada exitosamente');
    }

    /**
    * Update a materials request
    */
    private function updateMaterialsRequest(Request $request, PurchaseRequest $purchaseRequest)
    {
    $validator = Validator::make($request->all(), [
    'requester' => 'required|string|max:255',
    'code' => 'nullable|string|max:255',
    'grade' => 'nullable|string|max:255',
    'section' => 'required|string|max:255',
    'delivery_date' => 'required|date',
    'copy_items' => 'nullable|array',
    'copy_items.*.item' => 'nullable|integer',
    'copy_items.*.original' => 'nullable|string',
    'copy_items.*.copies_required' => 'nullable|integer|min:1',
    'copy_items.*.double_letter_color' => 'nullable|boolean',
    'copy_items.*.black_white' => 'nullable|boolean',
    'copy_items.*.color' => 'nullable|boolean',
    'copy_items.*.impresion' => 'nullable|boolean',
    'copy_items.*.total' => 'nullable|integer|min:0',
    'material_items' => 'nullable|array',
    'material_items.*.item' => 'required_with:material_items|integer',
    'material_items.*.article' => 'required_with:material_items|string',
    'material_items.*.quantity' => 'required_with:material_items|integer|min:1',
    'material_items.*.objective' => 'nullable|string',
    ]);
    
    if ($validator->fails()) {
    return redirect()->route('purchase-requests.edit', $purchaseRequest)
    ->withErrors($validator)
    ->withInput();
    }

    // Actualizar la solicitud de materiales
    $purchaseRequest->update([
    'requester' => $request->requester,
    'code' => $request->code,
    'grade' => $request->grade,
    'section' => $request->section,
    'section_area' => $request->section, // También se actualiza en section_area para consistencia
    'delivery_date' => $request->delivery_date,
    'copy_items' => $request->copy_items,
    'material_items' => $request->material_items,
    ]);
    
    return redirect()->route('purchase-requests.show', $purchaseRequest)
    ->with('success', 'Solicitud de materiales actualizada exitosamente');
    }

    /**
    * Update a copies request
    */    private function updateCopiesRequest(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validator = Validator::make($request->all(), [
            'requester' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'section' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'copy_items' => 'required|array',
            'copy_items.*.item' => 'required|integer',
            'copy_items.*.original' => 'required|string',
            'copy_items.*.copies_required' => 'required|integer|min:1',
            'copy_items.*.double_letter_color' => 'nullable|boolean',
            'copy_items.*.black_white' => 'nullable|boolean',
            'copy_items.*.color' => 'nullable|boolean',
            'copy_items.*.impresion' => 'nullable|boolean',
            'copy_items.*.total' => 'nullable|integer|min:0',
            'attached_files' => 'nullable|array|max:5', // Máximo 5 archivos
            'attached_files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480', // 20MB máximo cada archivo
            // Validaciones para especificaciones
            'paper_size' => 'nullable|string|in:Carta,Oficio,A4,A3,Tabloid',
            'paper_type' => 'nullable|string|in:Bond 75g,Bond 90g,Propalcote 115g,Propalcote 150g,Cartulina,Opalina',
            'paper_color' => 'nullable|string|in:Blanco,Amarillo,Rosa,Verde,Azul,Gris,Otro',
            'requires_binding' => 'nullable|boolean',
            'requires_lamination' => 'nullable|boolean',
            'requires_cutting' => 'nullable|boolean',
            'special_details' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.edit', $purchaseRequest)
                ->withErrors($validator)
                ->withInput();
        }

        // Manejar los archivos adjuntos múltiples
        $attachedFiles = $purchaseRequest->attached_files ?: []; // Mantener los archivos actuales
        if ($request->hasFile('attached_files')) {
            // Eliminar archivos anteriores si existen
            if (!empty($attachedFiles)) {
                foreach ($attachedFiles as $file) {
                    if (isset($file['file_path']) && \Storage::disk('public')->exists($file['file_path'])) {
                        \Storage::disk('public')->delete($file['file_path']);
                    }
                }
            }
            
            // Procesar nuevos archivos
            $attachedFiles = [];
            foreach ($request->file('attached_files') as $index => $file) {
                if ($file) {
                    $fileName = time() . '_' . $index . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $filePath = $file->storeAs('attached_files', $fileName, 'public');
                    $attachedFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
        }

        // Actualizar la solicitud de fotocopias
        $updateData = [
            'requester' => $request->requester,
            'code' => $request->code,
            'grade' => $request->grade,
            'section' => $request->section,
            'section_area' => $request->section, // También se actualiza en section_area para consistencia
            'delivery_date' => $request->delivery_date,
            'copy_items' => $request->copy_items,
            'attached_files' => $attachedFiles, // Actualizar con los nuevos archivos adjuntos múltiples
            // Campos de especificaciones
            'paper_size' => $request->paper_size,
            'paper_type' => $request->paper_type,
            'paper_color' => $request->paper_color,
            'requires_binding' => $request->requires_binding ? true : false,
            'requires_lamination' => $request->requires_lamination ? true : false,
            'requires_cutting' => $request->requires_cutting ? true : false,
            'special_details' => $request->special_details,
        ];

        // Si la solicitud está aprobada, agregar nota de edición post-aprobación
        if ($purchaseRequest->status === 'approved') {
            $editNote = "\n\n[EDITADO POST-APROBACIÓN el " . now()->format('d/m/Y H:i') . " por " . auth()->user()->name . "]";
            $updateData['special_details'] = ($request->special_details ?: '') . $editNote;
        }

        $purchaseRequest->update($updateData);

        $successMessage = $purchaseRequest->status === 'approved' 
            ? 'Solicitud de fotocopias aprobada actualizada exitosamente. Los cambios han sido registrados.'
            : 'Solicitud de fotocopias actualizada exitosamente';

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', $successMessage);
    }

    /**
     * Update a services request
     */
    private function updateServicesRequest(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Validar que haya al menos un servicio con descripción
        $hasServiceItems = $request->has('service_items') && is_array($request->service_items) && 
                          count(array_filter($request->service_items, function($item) {
                              return !empty($item['description']);
                          })) > 0;
        
        if (!$hasServiceItems) {
            return redirect()->route('purchase-requests.edit', $purchaseRequest)
                ->with('error', 'Debe agregar al menos un servicio con descripción.')
                ->withInput();
        }
        
        // Definir reglas de validación base
        $rules = [
            'requester' => 'required|string|max:255',
            'section_area' => 'required|string|max:255',
            'coordinator' => 'nullable|string|max:255',
            'service_justification' => 'required|string',
            'service_budget' => 'nullable|numeric|min:0',
            'service_budget_text' => 'nullable|string|max:255',
            'general_observations' => 'nullable|string',
            'service_items' => 'required|array',
            'service_items.*.item' => 'required|integer',
            'service_items.*.quantity' => 'required|integer|min:1',
            'service_items.*.description' => 'required|string',
            'service_items.*.observations' => 'nullable|string',
            'service_type' => 'required|in:regular,no_quotation',
        ];

        // Agregar validación adicional para servicios sin cotización
        if ($request->service_type === 'no_quotation') {
            $rules['provider_name'] = 'required|string|max:255';
            $rules['provider_nit'] = 'nullable|string|max:255';
            $rules['provider_contact'] = 'nullable|string|max:255';
            $rules['provider_email'] = 'nullable|email|max:255';
            $rules['no_quotation_reason'] = 'required|string';
        }
        
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.edit', $purchaseRequest)
                ->withErrors($validator)
                ->withInput();
        }

        // Preparar datos para actualizar la solicitud
        $data = [
            'requester' => $request->requester,
            'section_area' => $request->section_area,
            'coordinator' => $request->coordinator,
            'service_justification' => $request->service_justification,
            'service_budget' => $request->service_budget,
            'service_budget_text' => $request->service_budget_text,
            'service_items' => $request->service_items,
            'general_observations' => $request->general_observations,
            'service_type' => $request->service_type,
        ];

        // Actualizar campos del proveedor si es servicio sin cotización
        if ($request->service_type === 'no_quotation') {
            $data['provider_name'] = $request->provider_name;
            $data['provider_nit'] = $request->provider_nit;
            $data['provider_contact'] = $request->provider_contact;
            $data['provider_email'] = $request->provider_email;
            $data['no_quotation_reason'] = $request->no_quotation_reason;
        } else {
            // Limpiar campos del proveedor si se cambió a servicio regular
            $data['provider_name'] = null;
            $data['provider_nit'] = null;
            $data['provider_contact'] = null;
            $data['provider_email'] = null;
            $data['no_quotation_reason'] = null;
        }

        // Actualizar la solicitud de servicios
        $purchaseRequest->update($data);

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Solicitud de servicios actualizada exitosamente');
    }

    /**
    * Remove the specified resource from storage.
    */
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $permissionService = new PurchaseRequestPermissionService();
        
        // Verificar si el usuario puede eliminar esta solicitud
        if (!$permissionService->canDeleteRequest($purchaseRequest)) {
            abort(403, 'No tienes permisos para eliminar esta solicitud.');
        }
        
        $purchaseRequest->delete();
        
        return redirect()->route('purchase-requests.index')
            ->with('success', 'Solicitud eliminada exitosamente');
    }

    /**
    * Approve a purchase request
    */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
    if (!Auth::user()->can('approve-purchase-requests')) {
    return redirect()->back()->with('error', 'No tienes permisos para aprobar solicitudes');
    }
    
    $purchaseRequest->update([
    'status' => 'approved',
    'approved_by' => Auth::id(),
    'approval_date' => now(),
    ]);
    
    return redirect()->route('purchase-requests.show', $purchaseRequest)
    ->with('success', 'Solicitud aprobada exitosamente');
    }

    /**
    * Reject a purchase request
    */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
    if (!Auth::user()->can('approve-purchase-requests')) {
    return redirect()->back()->with('error', 'No tienes permisos para rechazar solicitudes');
    }
    
    $validator = Validator::make($request->all(), [
    'rejection_reason' => 'required|string|max:255',
    ]);
    
    if ($validator->fails()) {
    return redirect()->back()
    ->withErrors($validator)
    ->withInput();
    }
    
    $purchaseRequest->update([
    'status' => 'rejected',
    'approved_by' => Auth::id(),
    'approval_date' => now(),
    ]);
    
    return redirect()->route('purchase-requests.show', $purchaseRequest)
    ->with('success', 'Solicitud rechazada exitosamente');
    }

    /**
    * Store a copies request
    */
    private function storeCopiesRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requester' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'section' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'copy_items' => 'required|array',
            'copy_items.*.item' => 'required|integer',
            'copy_items.*.original' => 'required|string',
            'copy_items.*.copies_required' => 'required|integer|min:1',
            'copy_items.*.double_letter_color' => 'nullable|boolean',
            'copy_items.*.black_white' => 'nullable|boolean',
            'copy_items.*.color' => 'nullable|boolean',
            'copy_items.*.impresion' => 'nullable|boolean',
            'copy_items.*.total' => 'nullable|integer|min:0',
            'attached_files' => 'nullable|array|max:5', // Máximo 5 archivos
            'attached_files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480', // 20MB máximo cada archivo
            // Validaciones para especificaciones
            'paper_size' => 'nullable|string|in:Carta,Oficio,A4,A3,Tabloid',
            'paper_type' => 'nullable|string|in:Bond 75g,Bond 90g,Propalcote 115g,Propalcote 150g,Cartulina,Opalina',
            'paper_color' => 'nullable|string|in:Blanco,Amarillo,Rosa,Verde,Azul,Gris,Otro',
            'requires_binding' => 'nullable|boolean',
            'requires_lamination' => 'nullable|boolean',
            'requires_cutting' => 'nullable|boolean',
            'special_details' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('purchase-requests.create-copies')
                ->withErrors($validator)
                ->withInput();
        }

        // Manejar la subida de múltiples archivos adjuntos si existen
        $attachedFiles = [];
        if ($request->hasFile('attached_files')) {
            foreach ($request->file('attached_files') as $index => $file) {
                if ($file) {
                    $fileName = time() . '_' . $index . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $filePath = $file->storeAs('attached_files', $fileName, 'public');
                    $attachedFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
        }

        // Crear la solicitud de fotocopias (usamos 'materials' como tipo ya que es el valor permitido en la DB)
        $purchaseRequest = PurchaseRequest::create([
            'type' => 'materials', // Usamos 'materials' en lugar de 'copies' porque el ENUM no permite 'copies'
            'user_id' => Auth::id(),
            'requester' => $request->requester,
            'code' => $request->code,
            'grade' => $request->grade,
            'section' => $request->section,
            'section_area' => $request->section, // Añadimos section_area = section para consistencia
            'delivery_date' => $request->delivery_date,
            'copy_items' => $request->copy_items,
            'attached_files' => $attachedFiles, // Guardamos los archivos adjuntos múltiples
            'status' => 'pending',
            'request_date' => now(), // Añadimos la fecha de solicitud actual
            // Campos de especificaciones
            'paper_size' => $request->paper_size,
            'paper_type' => $request->paper_type,
            'paper_color' => $request->paper_color,
            'requires_binding' => $request->requires_binding ? true : false,
            'requires_lamination' => $request->requires_lamination ? true : false,
            'requires_cutting' => $request->requires_cutting ? true : false,
            'special_details' => $request->special_details,
        ]);

        // Verificar si la solicitud debe ser auto-aprobada
        $autoApproved = $purchaseRequest->autoApproveIfEligible();
        
        // Enviar emails diferenciados
        $this->sendDifferentiatedEmails($purchaseRequest);
        
        // Enviar emails de aprobación específicos por sección (las fotocopias también son materiales)
        $this->sendMaterialsApprovalEmails($purchaseRequest);

        // Mensaje de éxito con información sobre aprobación automática
        $successMessage = 'Solicitud de fotocopias creada exitosamente';
        if ($autoApproved) {
            $totalCopies = $purchaseRequest->getTotalCopiesQuantity();
            $successMessage .= " y aprobada automáticamente (Total: {$totalCopies} copias ≤ 15)";
        } else {
            $totalCopies = $purchaseRequest->getTotalCopiesQuantity();
            $successMessage .= " (Total: {$totalCopies} copias > 15 - Requiere aprobación manual)";
        }

        return redirect()->route('purchase-requests.index')
            ->with('success', $successMessage);
    }

    /**
     * Generar PDF de la solicitud de compra
     */
    public function generatePdf($id, PurchaseRequestPdfService $pdfService)
    {
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);
            
            // Verificar que la solicitud esté aprobada
            if ($purchaseRequest->status !== 'approved') {
                return redirect()->back()->with('error', 'Solo se pueden generar PDFs de solicitudes aprobadas.');
            }
            
            return $pdfService->downloadPdf($purchaseRequest);
            
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de solicitud de compra #' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF de la solicitud.');
        }
    }

    /**
     * Marcar el estado de entrega de fotocopias
     */
    public function markDeliveryStatus(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Verificar permisos
        if (!Auth::user()->hasRole(['compras', 'admin', 'almacen'])) {
            return redirect()->back()->with('error', 'No tienes permisos para marcar el estado de entrega.');
        }

        // Verificar que sea una solicitud de fotocopias
        if (!$purchaseRequest->isCopiesRequest()) {
            return redirect()->back()->with('error', 'Solo se puede marcar el estado de entrega para solicitudes de fotocopias.');
        }

        // Verificar que la solicitud esté autorizada/aprobada
        if (!in_array($purchaseRequest->status, ['approved', 'authorized'])) {
            return redirect()->back()->with('error', 'Solo se puede marcar el estado de entrega para solicitudes autorizadas/aprobadas.');
        }

        // Validar la entrada
        $validator = Validator::make($request->all(), [
            'delivery_status' => 'required|in:delivered,not_delivered',
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Marcar el estado de entrega
        $success = $purchaseRequest->markDeliveryStatus(
            $request->delivery_status,
            Auth::id(),
            $request->delivery_notes
        );

        if ($success) {
            $statusText = $request->delivery_status === 'delivered' ? 'entregada' : 'no entregada';
            return redirect()->back()->with('success', "Fotocopia marcada como {$statusText} exitosamente.");
        } else {
            return redirect()->back()->with('error', 'Error al actualizar el estado de entrega.');
        }
    }

    /**
     * Descargar el archivo original adjunto a una solicitud de fotocopias
     */
    public function downloadOriginal(PurchaseRequest $purchaseRequest)
    {
        // Verificar que el usuario puede ver esta solicitud
        $permissionService = new PurchaseRequestPermissionService();
        if (!$permissionService->canViewRequest($purchaseRequest)) {
            abort(403, 'No tienes permisos para acceder a este archivo.');
        }

        // Verificar que es una solicitud de fotocopias
        if (!$purchaseRequest->isCopiesRequest()) {
            return redirect()->back()->with('error', 'Solo las solicitudes de fotocopias pueden tener archivos originales.');
        }

        // Verificar que tiene archivo adjunto
        if (!$purchaseRequest->original_file) {
            return redirect()->back()->with('error', 'Esta solicitud no tiene un archivo original adjunto.');
        }

        // Verificar que el archivo existe
        if (!\Storage::disk('public')->exists($purchaseRequest->original_file)) {
            return redirect()->back()->with('error', 'El archivo original no se encuentra en el servidor.');
        }

        // Descargar el archivo
        return \Storage::disk('public')->download($purchaseRequest->original_file);
    }
    
    /**
     * Descargar un archivo adjunto específico de una solicitud de fotocopias
     */
    public function downloadAttachedFile($id, $fileIndex)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        // Verificar que el usuario puede ver esta solicitud
        $permissionService = new PurchaseRequestPermissionService();
        if (!$permissionService->canViewRequest($purchaseRequest)) {
            abort(403, 'No tienes permisos para acceder a este archivo.');
        }

        // Verificar que es una solicitud de fotocopias
        if (!$purchaseRequest->isCopiesRequest()) {
            return redirect()->back()->with('error', 'Solo las solicitudes de fotocopias pueden tener archivos adjuntos.');
        }

        // Verificar que tiene archivos adjuntos
        if (!$purchaseRequest->attached_files || !is_array($purchaseRequest->attached_files)) {
            return redirect()->back()->with('error', 'Esta solicitud no tiene archivos adjuntos.');
        }

        // Verificar que el índice del archivo existe
        if (!isset($purchaseRequest->attached_files[$fileIndex])) {
            return redirect()->back()->with('error', 'El archivo solicitado no existe.');
        }

        $file = $purchaseRequest->attached_files[$fileIndex];
        
        // Verificar que el archivo tiene la estructura correcta
        if (!isset($file['file_path'])) {
            return redirect()->back()->with('error', 'El archivo no tiene una ruta válida.');
        }

        // Verificar que el archivo existe en el servidor
        if (!\Storage::disk('public')->exists($file['file_path'])) {
            return redirect()->back()->with('error', 'El archivo no se encuentra en el servidor.');
        }

        // Obtener el nombre original o generar uno
        $originalName = $file['original_name'] ?? 'archivo_adjunto_' . ($fileIndex + 1);
        
        // Descargar el archivo con su nombre original
        return \Storage::disk('public')->download($file['file_path'], $originalName);
    }
    
    /**
     * Configure quotation requirements for a purchase request
     */
    public function configureQuotations(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Verificar permisos
        if (!Auth::user()->hasRole(['admin', 'compras'])) {
            return redirect()->back()->with('error', 'No tienes permisos para configurar las cotizaciones.');
        }

        // Validar entrada
        $validator = Validator::make($request->all(), [
            'required_quotations' => 'required|integer|min:1|max:10',
            'can_proceed_early' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar configuración
        $purchaseRequest->configureRequiredQuotations(
            $request->required_quotations,
            $request->boolean('can_proceed_early')
        );

        return redirect()->back()
            ->with('success', 'Configuración de cotizaciones actualizada correctamente. Ahora se requieren ' . 
                    $request->required_quotations . ' cotizaciones.');
    }
}