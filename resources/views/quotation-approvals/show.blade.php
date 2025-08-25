@extends('adminlte::page')

@section('title', 'Detalle de Solicitud para Preaprobación')

@section('content_header')
    <h1 class="m-0 text-dark">Detalle de Solicitud para Preaprobación</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">Solicitud #{{ $request->request_number }}</h3>
                <div>
                    <a href="{{ route('quotation-approvals.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('quotation-approvals.compare', $request->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-balance-scale"></i> Comparar Cotizaciones
                    </a>
                    @if($request->quotations->count() >= 2 && $request->status !== 'approved')
                        <a href="{{ route('quotation-selections.show', $request->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-tasks"></i> Selección Mixta
                        </a>
                    @elseif($request->quotations->count() >= 2 && $request->status === 'approved')
                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                            <i class="fas fa-tasks"></i> Selección Mixta
                            <small class="d-block">(Aprobada)</small>
                        </button>
                    @endif
                    
                    @if(auth()->user()->hasRole('admin'))
                        <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#resendRequestModal">
                            <i class="fas fa-paper-plane"></i> Reenviar Solicitud
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Alertas de sesión -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error</h5>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('approvalInfo'))
                    @php $info = session('approvalInfo'); @endphp
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-info-circle"></i> {{ $info['title'] }}</h5>
                        <p>{{ $info['message'] }}</p>
                        <p><strong>{{ $info['type'] == 'approved' ? 'Aprobada' : 'Pre-aprobada' }} por:</strong> {{ $info['approver'] }}</p>
                        <p><strong>Fecha:</strong> {{ $info['date'] }}</p>
                    </div>
                @endif
                
                <!-- Información de la solicitud -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <h5 class="info-box-text text-muted">Información de la Solicitud</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th style="width:40%">Número de solicitud:</th>
                                                <td>{{ $request->request_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Solicitante:</th>
                                                <td>{{ $request->user->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Área/Sección:</th>
                                                <td>{{ $request->section_area }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha:</th>
                                                <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Estado:</th>
                                                <td>
                                                    @if($request->status == 'Pre-aprobada')
                                                        <span class="badge badge-success">{{ $request->status }}</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ $request->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <h5 class="info-box-text text-muted">Descripción de la Solicitud</h5>
                                
                                @if($request->type === 'purchase' && $request->purchase_justification)
                                    <div class="mt-3">
                                        <h6 class="text-primary"><strong>JUSTIFICACIÓN DE LA COMPRA:</strong></h6>
                                        <p class="info-box-text" style="white-space: pre-wrap; word-wrap: break-word; overflow: visible; height: auto; max-height: none;">{{ $request->purchase_justification }}</p>
                                    </div>
                                @endif
                                
                                @if($request->type === 'services' && $request->service_justification)
                                    <div class="mt-3">
                                        <h6 class="text-primary"><strong>JUSTIFICACIÓN DEL SERVICIO:</strong></h6>
                                        <p class="info-box-text" style="white-space: pre-wrap; word-wrap: break-word; overflow: visible; height: auto; max-height: none;">{{ $request->service_justification }}</p>
                                    </div>
                                @endif
                                
                                @if(!$request->purchase_justification && !$request->service_justification)
                                    <p class="info-box-text mt-3 text-muted">
                                        No hay justificación disponible para esta solicitud.
                                    </p>
                                @endif

                                @if($request->description)
                                    <div class="mt-3">
                                        <h6 class="text-secondary"><strong>Descripción Adicional:</strong></h6>
                                        <p class="info-box-text" style="white-space: pre-wrap; word-wrap: break-word; overflow: visible; height: auto; max-height: none;">{{ $request->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Compra Compartida (si aplica) -->
                @if($sharedPurchaseInfo && $sharedPurchaseInfo['is_shared'])
                    <div class="alert alert-info alert-dismissible" style="color: white;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-share-alt"></i> ¡Compra Compartida Detectada!</h5>
                        <p><strong>Esta solicitud es compartida entre {{ $sharedPurchaseInfo['has_third_section'] ? 'tres' : 'dos' }} secciones y afectará los siguientes presupuestos:</strong></p>
                        
                        <div class="row mt-3">
                            <div class="{{ $sharedPurchaseInfo['has_third_section'] ? 'col-md-4' : 'col-md-6' }}">
                                <div class="card card-outline card-primary" style="color: black;">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-building mr-1"></i>
                                            {{ $sharedPurchaseInfo['my_section'] }}
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <p class="mb-1"><strong>Porcentaje:</strong> {{ $sharedPurchaseInfo['my_percentage'] }}%</p>
                                        @if($sharedPurchaseInfo['budget_impact']['total'] > 0)
                                            <p class="mb-0"><strong>Monto estimado:</strong> ${{ number_format($sharedPurchaseInfo['budget_impact']['my_section']['amount'], 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="{{ $sharedPurchaseInfo['has_third_section'] ? 'col-md-4' : 'col-md-6' }}">
                                <div class="card card-outline card-success" style="color: black;">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-building mr-1"></i>
                                            {{ $sharedPurchaseInfo['shared_section'] }}
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <p class="mb-1"><strong>Porcentaje:</strong> {{ $sharedPurchaseInfo['shared_percentage'] }}%</p>
                                        @if($sharedPurchaseInfo['budget_impact']['total'] > 0)
                                            <p class="mb-0"><strong>Monto estimado:</strong> ${{ number_format($sharedPurchaseInfo['budget_impact']['shared_section']['amount'], 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($sharedPurchaseInfo['has_third_section'])
                                <div class="col-md-4">
                                    <div class="card card-outline card-warning" style="color: black;">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-building mr-1"></i>
                                                {{ $sharedPurchaseInfo['third_shared_section'] }}
                                            </h6>
                                        </div>
                                        <div class="card-body py-2">
                                            <p class="mb-1"><strong>Porcentaje:</strong> {{ $sharedPurchaseInfo['third_shared_percentage'] }}%</p>
                                            @if($sharedPurchaseInfo['budget_impact']['total'] > 0)
                                                <p class="mb-0"><strong>Monto estimado:</strong> ${{ number_format($sharedPurchaseInfo['budget_impact']['third_section']['amount'] ?? 0, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @if($sharedPurchaseInfo['budget_impact']['total'] > 0)
                            <div class="mt-2" style="color: white;">
                                <p class="mb-0"><strong>Total estimado de la compra:</strong> ${{ number_format($sharedPurchaseInfo['budget_impact']['total'], 2) }}</p>
                                <small style="color: white; opacity: 0.8;">* Los montos son estimados basados en la cotización más baja disponible.</small>
                            </div>
                        @else
                            <div class="mt-2" style="color: white;">
                                <small style="color: white; opacity: 0.8;">* Los montos específicos se determinarán una vez que se reciban las cotizaciones.</small>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Productos/Servicios solicitados -->
                @if($request->type === 'purchase')
                    <!-- Productos (si hay) -->
                    @if(is_array($request->purchase_items) && count(array_filter($request->purchase_items, function($item) { return !empty($item['quantity']); })))
                        <div class="card card-primary card-outline mt-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-boxes mr-1"></i>
                                    Productos Solicitados
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 10%">Cant.</th>
                                                <th style="width: 35%">Descripción</th>
                                                <th style="width: 25%">Unidad/Presentación</th>
                                                <th style="width: 25%">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($request->purchase_items as $item)
                                                @if(!empty($item['quantity']))
                                                    <tr>
                                                        <td>{{ $item['item'] ?? '' }}</td>
                                                        <td>{{ $item['quantity'] ?? '' }}</td>
                                                        <td>{{ $item['description'] ?? '' }}</td>
                                                        <td>{{ $item['unit'] ?? '' }}</td>
                                                        <td>{{ $item['observations'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Servicios (si hay) -->
                    @if(is_array($request->service_items) && count(array_filter($request->service_items, function($item) { return !empty($item['quantity']); })))
                        <div class="card card-primary card-outline mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-concierge-bell mr-1"></i>
                                    Servicios Solicitados
                                </h3>
                            </div>
                            <div class="card-body">
                                @if($request->service_budget)
                                    <div class="alert alert-info">
                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                        <strong>Presupuesto:</strong> ${{ number_format($request->service_budget, 2) }} 
                                        @if($request->service_budget_text)
                                            ({{ $request->service_budget_text }})
                                        @endif
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 10%">Cant.</th>
                                                <th style="width: 60%">Descripción</th>
                                                <th style="width: 25%">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($request->service_items as $service)
                                                @if(!empty($service['quantity']))
                                                <tr>
                                                    <td>{{ $service['item'] ?? '' }}</td>
                                                    <td>{{ $service['quantity'] ?? '' }}</td>
                                                    <td>{{ $service['description'] ?? '' }}</td>
                                                    <td>{{ $service['observations'] ?? '' }}</td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Lista de cotizaciones -->
                <h4 class="mt-4 mb-3"><i class="fas fa-file-invoice-dollar mr-2"></i> Cotizaciones Disponibles</h4>
                
                @php
                    $hasItemSelections = $request->quotationItemSelections()->exists();
                    $selectionCount = $request->quotationItemSelections()->count();
                    $totalItems = count(is_array($request->purchase_items) ? $request->purchase_items : json_decode($request->purchase_items, true) ?? []);
                @endphp
                
                @if($hasItemSelections)
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle mr-2"></i>Selección Mixta en Progreso</h5>
                        <p class="mb-2">
                            Se ha iniciado una selección mixta de proveedores para esta solicitud.
                            <strong>{{ $selectionCount }} de {{ $totalItems }}</strong> productos han sido seleccionados.
                        </p>
                        <div class="mt-2">
                            @if($request->status === 'approved')
                                <button type="button" class="btn btn-secondary btn-sm" disabled>
                                    <i class="fas fa-tasks"></i> Ver/Continuar Selección Mixta
                                    <small class="d-block">(Solicitud aprobada)</small>
                                </button>
                            @else
                                <a href="{{ route('quotation-selections.show', $request->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-tasks"></i> Ver/Continuar Selección Mixta
                                </a>
                            @endif
                            @if($selectionCount == $totalItems)
                                <span class="badge badge-success ml-2">
                                    <i class="fas fa-check"></i> Selección Completa - Lista para Finalizar
                                </span>
                                @if(in_array($request->status, ['En pre-aprobación', 'En Cotización']) && $selectionCount == $totalItems)
                                    <button class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#preApproveMixedSelectionModal">
                                        <i class="fas fa-check-circle"></i> Pre-aprobar Selección Mixta
                                    </button>
                                @endif
                            @else
                                <span class="badge badge-warning ml-2">
                                    <i class="fas fa-clock"></i> Selección Incompleta
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($hasMixedSelections && $selectedQuotations->count() > 0)
                    <!-- Mostrar solo las cotizaciones seleccionadas en la selección mixta -->
                    <h5 class="mb-3"><i class="fas fa-check-circle mr-2 text-success"></i>Proveedores Seleccionados en la Selección Mixta</h5>
                    
                    <!-- Botón de pre-aprobación para selección mixta -->
                    @if($mixedSelections->count() > 0)
                        @if(in_array($request->status, ['En pre-aprobación', 'En Cotización']))
                            <div class="alert alert-success mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-check-circle mr-2"></i>Selección Mixta Lista para Pre-aprobación</h6>
                                        <small>{{ $mixedSelections->count() }} productos seleccionados de {{ $selectedQuotations->count() }} proveedores</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#preApproveMixedSelectionModal">
                                            <i class="fas fa-check-circle"></i> Pre-aprobar Selección Mixta
                                        </button>
                                        @if($request->status !== 'Pre-aprobada' && $request->status !== 'approved')
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                                <i class="fas fa-times-circle"></i> Rechazar Solicitud
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($request->status === 'Pre-aprobada')
                            <div class="alert alert-info mb-3">
                                <h6 class="mb-1"><i class="fas fa-check-double mr-2"></i>Selección Mixta Ya Pre-aprobada</h6>
                                <small>Esta selección mixta ya ha sido pre-aprobada y está lista para aprobación final.</small>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <h6>Estado no reconocido para pre-aprobación: {{ $request->status }}</h6>
                            </div>
                        @endif
                    @endif
                    <div class="row">
                        @foreach($selectedQuotations as $quotation)
                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">{{ $quotation->provider_name }}</h4>
                                        <span class="badge badge-success">Seleccionado</span>
                                    </div>
                                    <div class="card-body">
                                        <!-- Detalles generales de la cotización -->
                                        <div class="mb-3">
                                            <h6 class="text-primary">Información General:</h6>
                                            <p class="mb-1"><strong>Tiempo de Entrega:</strong> {{ $quotation->delivery_time ?? 'No especificado' }}</p>
                                            <p class="mb-1"><strong>Forma de Pago:</strong> {{ $quotation->payment_method ?? 'No especificada' }}</p>
                                            <p class="mb-1"><strong>Validez:</strong> {{ $quotation->validity ?? 'No especificada' }}</p>
                                            <p class="mb-1"><strong>Garantía:</strong> {{ $quotation->warranty ?? 'No especificada' }}</p>
                                        </div>
                                        
                                        <!-- Items seleccionados de este proveedor -->
                                        <div class="mb-3">
                                            <h6 class="text-primary">Productos Seleccionados:</h6>
                                            @if($quotation->selectedItems && $quotation->selectedItems->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Producto</th>
                                                                <th>Cantidad</th>
                                                                <th>Precio Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($quotation->selectedItems as $selection)
                                                                <tr>
                                                                    <td>
                                                                        {{ $selection->item_description ?? 'Producto #' . $selection->item_index }}
                                                                    </td>
                                                                    <td>{{ $selection->quantity }}</td>
                                                                    <td class="text-right">${{ number_format($selection->total_price, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="font-weight-bold bg-light">
                                                                <td colspan="2">Subtotal de este proveedor:</td>
                                                                <td class="text-right">${{ number_format($quotation->selectedItemsTotal, 2) }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">No se encontraron selecciones para este proveedor.</p>
                                            @endif
                                        </div>
                                        
                                        @if($quotation->file_path)
                                            <a href="{{ Storage::url($quotation->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-2">
                                                <i class="fas fa-file-pdf"></i> Ver PDF de Cotización
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Resumen total de la selección mixta -->
                    <div class="card card-success mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-calculator mr-2"></i>Resumen Total de la Selección Mixta</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Proveedores Seleccionados:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($selectedQuotations as $quotation)
                                            <li>
                                                <i class="fas fa-check text-success mr-2"></i>
                                                {{ $quotation->provider_name }} 
                                                <span class="text-muted">({{ $quotation->selectedItems->count() }} productos)</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Total General:</h6>
                                    <div class="alert alert-success">
                                        <h4 class="mb-0">
                                            <i class="fas fa-dollar-sign mr-2"></i>
                                            ${{ number_format($mixedSelections->sum('total_price'), 2) }}
                                        </h4>
                                        <small>{{ $mixedSelections->count() }} productos seleccionados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Mostrar todas las cotizaciones cuando no hay selección mixta -->
                    <div class="row">
                        @forelse($request->quotations as $quotation)
                            <div class="col-md-4">
                                <div class="card {{ $quotation->is_selected ? 'card-outline card-success' : 'card-outline card-primary' }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">{{ $quotation->provider_name }}</h3>
                                        @if($quotation->is_selected)
                                            <span class="badge badge-success">Pre-aprobada</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Monto Total:</strong> ${{ number_format($quotation->total_amount, 0, ',', '.') }}</p>
                                        <p><strong>Tiempo de Entrega:</strong> {{ $quotation->delivery_time ?? 'No especificado' }}</p>
                                        <p><strong>Forma de Pago:</strong> {{ $quotation->payment_method ?? 'No especificada' }}</p>
                                        <p><strong>Validez:</strong> {{ $quotation->validity ?? 'No especificada' }}</p>
                                        <p><strong>Garantía:</strong> {{ $quotation->warranty ?? 'No especificada' }}</p>
                                        
                                        @if($quotation->file_path)
                                            <a href="{{ Storage::url($quotation->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-2">
                                                <i class="fas fa-file-pdf"></i> Ver PDF de Cotización
                                            </a>
                                        @endif
                                        
                                        @if($quotation->is_selected)
                                            <button class="btn btn-success btn-block mt-3" disabled>
                                                <i class="fas fa-check-double"></i> Cotización Pre-aprobada
                                            </button>
                                        @elseif($request->status == 'Pre-aprobada')
                                            <button class="btn btn-secondary btn-block mt-3" disabled>
                                                <i class="fas fa-lock"></i> Ya existe una cotización pre-aprobada
                                            </button>
                                        @else
                                            <button class="btn btn-success btn-block mt-3 mr-2" data-toggle="modal" data-target="#preApproveModal" 
                                                data-quotation-id="{{ $quotation->id }}" data-provider="{{ $quotation->provider_name }}">
                                                <i class="fas fa-check-circle"></i> Pre-aprobar esta cotización
                                            </button>
                                            @if($request->status !== 'Pre-aprobada' && $request->status !== 'approved')
                                                <button class="btn btn-danger btn-block mt-2" data-toggle="modal" data-target="#rejectModal">
                                                    <i class="fas fa-times-circle"></i> Rechazar Solicitud
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle mr-2"></i>Sin Cotizaciones</h5>
                                    <p class="mb-3">Esta solicitud no tiene cotizaciones adjuntas. Esto puede ocurrir cuando:</p>
                                    <ul class="mb-3">
                                        <li>Se trata de un servicio que no requiere cotización previa</li>
                                        <li>Es una compra con proveedor único o establecido</li>
                                        <li>Es un proceso urgente que requiere aprobación directa</li>
                                    </ul>
                                    
                                    @if($request->status !== 'Pre-aprobada' && $request->status !== 'approved')
                                        <div class="mt-3">
                                            <button class="btn btn-success mr-2" data-toggle="modal" data-target="#preApproveWithoutQuotationModal">
                                                <i class="fas fa-check-circle"></i> Pre-aprobar Sin Cotización
                                            </button>
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                                <i class="fas fa-times-circle"></i> Rechazar Solicitud
                                            </button>
                                            <small class="form-text text-muted mt-2">
                                                Al pre-aprobar sin cotización, la solicitud continuará al siguiente paso del proceso de aprobación.
                                            </small>
                                        </div>
                                    @elseif($request->status === 'Pre-aprobada')
                                        <div class="alert alert-success mt-3">
                                            <i class="fas fa-check-circle mr-2"></i>Esta solicitud ya ha sido pre-aprobada sin cotización.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pre-aprobación -->
<div class="modal fade" id="preApproveModal" tabindex="-1" role="dialog" aria-labelledby="preApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="preApproveModalLabel">Confirmar Pre-aprobación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('quotation-approvals.pre-approve', $request->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="quotation_id" id="quotation_id">
                    <p>Estás a punto de pre-aprobar la cotización de <strong id="provider-name"></strong>.</p>
                    <p>Esta acción actualizará el estado de la solicitud y notificará al solicitante.</p>
                    
                    <div class="form-group">
                        <label for="comments">Comentarios (opcional):</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="budget">Presupuesto al que se cargará esta compra *:</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionLeft">
                                    @php 
                                        $budgetHierarchy = \App\Helpers\BudgetHelper::getBudgetHierarchy(); 
                                        $sections = array_keys($budgetHierarchy);
                                        $halfCount = ceil(count($sections) / 2);
                                        $leftSections = array_slice($sections, 0, $halfCount, true);
                                    @endphp
                                    @foreach($leftSections as $index => $section)
                                        @php $budgets = $budgetHierarchy[$section]; @endphp
                                        <div class="card">
                                            <div class="card-header" id="heading{{ $index }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#budgetAccordionLeft">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio" type="radio" name="budget" id="budget_{{ $index }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_{{ $index }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionRight">
                                    @php $rightSections = array_slice($sections, $halfCount, null, true); @endphp
                                    @foreach($rightSections as $index => $section)
                                        @php 
                                            $budgets = $budgetHierarchy[$section]; 
                                            $rightIndex = $index + $halfCount;
                                        @endphp
                                        <div class="card">
                                            <div class="card-header" id="heading{{ $rightIndex }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $rightIndex }}" aria-expanded="false" aria-controls="collapse{{ $rightIndex }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapse{{ $rightIndex }}" class="collapse" aria-labelledby="heading{{ $rightIndex }}" data-parent="#budgetAccordionRight">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio" type="radio" name="budget" id="budget_{{ $rightIndex }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_{{ $rightIndex }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Seleccione el rubro presupuestal donde se cargará esta compra.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="preApproveSubmitBtn">Confirmar Pre-aprobación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Pre-aprobación Sin Cotización -->
<div class="modal fade" id="preApproveWithoutQuotationModal" tabindex="-1" role="dialog" aria-labelledby="preApproveWithoutQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="preApproveWithoutQuotationModalLabel">Pre-aprobar Sin Cotización</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('quotation-approvals.pre-approve-without-quotation', $request->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atención:</strong> Está a punto de pre-aprobar la solicitud <strong>#{{ $request->request_number }}</strong> sin cotizaciones adjuntas.
                    </div>
                    
                    <div class="form-group">
                        <label for="approval_comments">Comentarios de Pre-aprobación *</label>
                        <textarea name="comments" id="approval_comments" class="form-control" rows="4" 
                                  placeholder="Explique por qué se pre-aprueba sin cotización (ej: servicio único, proveedor establecido, urgencia, etc.)" 
                                  required maxlength="500"></textarea>
                        <small class="form-text text-muted">Máximo 500 caracteres. Este comentario será visible en el historial de la solicitud.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="budget_line_no_quotation">Rubro Presupuestal *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionNoQuotationLeft">
                                    @php 
                                        $budgetHierarchy = \App\Helpers\BudgetHelper::getBudgetHierarchy(); 
                                        $sections = array_keys($budgetHierarchy);
                                        $halfCount = ceil(count($sections) / 2);
                                        $leftSections = array_slice($sections, 0, $halfCount, true);
                                    @endphp
                                    @foreach($leftSections as $index => $section)
                                        @php $budgets = $budgetHierarchy[$section]; @endphp
                                        <div class="card">
                                            <div class="card-header" id="headingNoQuotation{{ $index }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNoQuotation{{ $index }}" aria-expanded="false" aria-controls="collapseNoQuotation{{ $index }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseNoQuotation{{ $index }}" class="collapse" aria-labelledby="headingNoQuotation{{ $index }}" data-parent="#budgetAccordionNoQuotationLeft">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio-no-quotation" type="radio" name="budget_line" id="budget_no_quotation_{{ $index }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_no_quotation_{{ $index }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionNoQuotationRight">
                                    @php $rightSections = array_slice($sections, $halfCount, null, true); @endphp
                                    @foreach($rightSections as $index => $section)
                                        @php 
                                            $budgets = $budgetHierarchy[$section]; 
                                            $rightIndex = $index + $halfCount;
                                        @endphp
                                        <div class="card">
                                            <div class="card-header" id="headingNoQuotation{{ $rightIndex }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNoQuotation{{ $rightIndex }}" aria-expanded="false" aria-controls="collapseNoQuotation{{ $rightIndex }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseNoQuotation{{ $rightIndex }}" class="collapse" aria-labelledby="headingNoQuotation{{ $rightIndex }}" data-parent="#budgetAccordionNoQuotationRight">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio-no-quotation" type="radio" name="budget_line" id="budget_no_quotation_{{ $rightIndex }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_no_quotation_{{ $rightIndex }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Seleccione el rubro presupuestal donde se cargará esta solicitud.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm_no_quotation" required>
                            <label class="custom-control-label" for="confirm_no_quotation">
                                Confirmo que esta solicitud no requiere cotizaciones y puede continuar al proceso de aprobación final
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="confirmPreApproveBtn" disabled>
                        <i class="fas fa-check-circle"></i> Confirmar Pre-aprobación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Pre-aprobación de Selección Mixta -->
<div class="modal fade" id="preApproveMixedSelectionModal" tabindex="-1" role="dialog" aria-labelledby="preApproveMixedSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="preApproveMixedSelectionModalLabel">Pre-aprobar Selección Mixta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('quotation-approvals.pre-approve-mixed-selection', $request->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Selección Mixta Completa:</strong> Está a punto de pre-aprobar la selección mixta para la solicitud <strong>#{{ $request->request_number }}</strong>.
                    </div>
                    
                    @if(isset($mixedSelections) && $mixedSelections->count() > 0)
                        <div class="mb-3">
                            <h6>Resumen de Selecciones:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Proveedor</th>
                                            <th>Cantidad</th>
                                            <th>Precio Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mixedSelections as $selection)
                                            <tr>
                                                <td>{{ $selection->item_description ?? 'Producto #' . $selection->item_index }}</td>
                                                <td>{{ $selection->quotation->provider_name ?? 'N/A' }}</td>
                                                <td>{{ $selection->quantity }}</td>
                                                <td>${{ number_format($selection->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="3">Total General:</td>
                                            <td>${{ number_format($mixedSelections->sum('total_price'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="mixed_selection_comments">Comentarios de Pre-aprobación (opcional)</label>
                        <textarea name="comments" id="mixed_selection_comments" class="form-control" rows="3" 
                                  placeholder="Comentarios adicionales sobre la selección mixta..." 
                                  maxlength="500"></textarea>
                        <small class="form-text text-muted">Máximo 500 caracteres.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="mixed_selection_budget">Rubro Presupuestal *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionMixedLeft">
                                    @php 
                                        $budgetHierarchy = \App\Helpers\BudgetHelper::getBudgetHierarchy(); 
                                        $sections = array_keys($budgetHierarchy);
                                        $halfCount = ceil(count($sections) / 2);
                                        $leftSections = array_slice($sections, 0, $halfCount, true);
                                    @endphp
                                    @foreach($leftSections as $index => $section)
                                        @php $budgets = $budgetHierarchy[$section]; @endphp
                                        <div class="card">
                                            <div class="card-header" id="headingMixed{{ $index }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseMixed{{ $index }}" aria-expanded="false" aria-controls="collapseMixed{{ $index }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseMixed{{ $index }}" class="collapse" aria-labelledby="headingMixed{{ $index }}" data-parent="#budgetAccordionMixedLeft">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio-mixed" type="radio" name="budget_line" id="budget_mixed_{{ $index }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_mixed_{{ $index }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="accordion compact-accordion" id="budgetAccordionMixedRight">
                                    @php $rightSections = array_slice($sections, $halfCount, null, true); @endphp
                                    @foreach($rightSections as $index => $section)
                                        @php 
                                            $budgets = $budgetHierarchy[$section]; 
                                            $rightIndex = $index + $halfCount;
                                        @endphp
                                        <div class="card">
                                            <div class="card-header" id="headingMixed{{ $rightIndex }}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseMixed{{ $rightIndex }}" aria-expanded="false" aria-controls="collapseMixed{{ $rightIndex }}">
                                                        <i class="fas fa-chevron-down"></i> {{ $section }}
                                                    </button>
                                                </h2>
                                            </div>
                                            <div id="collapseMixed{{ $rightIndex }}" class="collapse" aria-labelledby="headingMixed{{ $rightIndex }}" data-parent="#budgetAccordionMixedRight">
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($budgets as $budgetIndex => $budget)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input budget-radio-mixed" type="radio" name="budget_line" id="budget_mixed_{{ $rightIndex }}_{{ $budgetIndex }}" value="{{ $budget }}">
                                                                    <label class="form-check-label" for="budget_mixed_{{ $rightIndex }}_{{ $budgetIndex }}">
                                                                        {{ $budget }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Seleccione el rubro presupuestal donde se cargará esta compra.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm_mixed_selection" required>
                            <label class="custom-control-label" for="confirm_mixed_selection">
                                Confirmo que la selección mixta es correcta y puede continuar al proceso de aprobación final
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="confirmMixedSelectionBtn" disabled>
                        <i class="fas fa-check-circle"></i> Confirmar Pre-aprobación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .info-box {
            min-height: auto;
            padding: 15px;
        }
        .info-box-content {
            padding: 0;
        }
        
        /* Estilos para acordeón de presupuesto */
        .accordion .card {
            margin-bottom: 5px;
        }
        
        .accordion .card-header {
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .accordion .btn-link {
            color: #495057;
            text-decoration: none;
            font-weight: 500;
        }
        
        .accordion .btn-link:hover {
            color: #007bff;
            text-decoration: none;
        }
        
        .accordion .card-body {
            padding: 0.75rem 1rem;
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* Estilos específicos para acordeón compacto */
        .compact-accordion .card-body {
            max-height: 150px;
            padding: 0.5rem 1rem;
        }
        
        .compact-accordion .form-check {
            margin-bottom: 0.25rem;
        }
        
        .compact-accordion .form-check-label {
            font-size: 0.9rem;
            font-weight: normal;
            cursor: pointer;
            line-height: 1.3;
        }
        
        .accordion .form-check {
            margin-bottom: 0.5rem;
        }
        
        .accordion .form-check-label {
            font-weight: normal;
            cursor: pointer;
        }
        
        .accordion .form-check-input:checked + .form-check-label {
            font-weight: bold;
            color: #007bff;
        }
        
        /* Rotar icono del acordeón */
        .accordion .btn-link[aria-expanded="true"] i {
            transform: rotate(180deg);
        }
        
        .accordion .btn-link i {
            transition: transform 0.3s ease;
        }
        
        /* Estilos para modal de reenvío */
        #resendRequestModal .modal-header {
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        #resendRequestModal .alert {
            border-radius: 0.375rem;
            border: none;
        }
        
        #resendRequestModal .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        
        #resendRequestModal .list-unstyled li {
            padding: 0.25rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        #resendRequestModal .list-unstyled li:last-child {
            border-bottom: none;
        }
    </style>

<!-- Modal de información de aprobación -->
@if(session('approvalInfo') || isset($approvalInfo))
    @php 
        $info = session('approvalInfo') ?? $approvalInfo; 
    @endphp
    <div class="modal fade" id="approvalInfoModal" tabindex="-1" role="dialog" aria-labelledby="approvalInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $info['type'] == 'approved' ? 'bg-success' : 'bg-info' }}">
                    <h5 class="modal-title text-white" id="approvalInfoModalLabel">
                        <i class="fas {{ $info['type'] == 'approved' ? 'fa-check-circle' : 'fa-info-circle' }}"></i>
                        {{ $info['title'] }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert {{ $info['type'] == 'approved' ? 'alert-success' : 'alert-info' }} border-0 text-white">
                        <p class="mb-3 text-white">
                            <i class="fas {{ $info['type'] == 'approved' ? 'fa-check-circle' : 'fa-info-circle' }} mr-2 text-white"></i>
                            {{ $info['message'] }}
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong class="text-white">{{ $info['type'] == 'approved' ? 'Aprobada' : 'Pre-aprobada' }} por:</strong><br>
                                <span class="text-white-50">{{ $info['approver'] }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-white">Fecha y hora:</strong><br>
                                <span class="text-white-50">{{ $info['date'] }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Información:</strong> 
                            @if($info['type'] == 'approved')
                                Esta solicitud ya ha completado todo el proceso de aprobación y no requiere acciones adicionales.
                            @else
                                Esta solicitud está esperando la aprobación final del director correspondiente.
                            @endif
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                    <a href="{{ route('quotation-approvals.index') }}" class="btn {{ $info['type'] == 'approved' ? 'btn-success' : 'btn-info' }}">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal de Rechazo -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="rejectModalLabel">
                    <i class="fas fa-times-circle"></i> Rechazar Solicitud
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('quotation-approvals.reject', $request->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atención:</strong> Está a punto de rechazar la solicitud <strong>#{{ $request->request_number }}</strong>.
                    </div>
                    
                    <div class="form-group">
                        <label for="rejection_reason">Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" 
                                  placeholder="Explique detalladamente el motivo por el cual se rechaza esta solicitud..." 
                                  required maxlength="1000"></textarea>
                        <small class="form-text text-muted">Máximo 1000 caracteres. Este motivo será enviado al solicitante y registrado en el historial.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm_rejection" required>
                            <label class="custom-control-label" for="confirm_rejection">
                                Confirmo que deseo rechazar esta solicitud y entiendo que esta acción no se puede deshacer
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Nota:</strong> Al rechazar esta solicitud se notificará automáticamente al solicitante, 
                            a la sección correspondiente y al área de compras. La solicitud cambiará su estado a "Rechazada" 
                            y no podrá continuar con el proceso de aprobación.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmRejectBtn" disabled>
                        <i class="fas fa-times-circle"></i> Confirmar Rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para reenviar solicitud -->
<div class="modal fade" id="resendRequestModal" tabindex="-1" role="dialog" aria-labelledby="resendRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white" id="resendRequestModalLabel">
                    <i class="fas fa-paper-plane mr-2"></i>Reenviar Solicitud de Pre-aprobación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('quotation-approvals.resend', $request->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Información:</strong> Está a punto de reenviar la solicitud <strong>#{{ $request->request_number }}</strong> 
                        para pre-aprobación a un correo específico.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-file-alt mr-1"></i> Información de la Solicitud</h6>
                            <ul class="list-unstyled">
                                <li><strong>Número:</strong> {{ $request->request_number }}</li>
                                <li><strong>Solicitante:</strong> {{ $request->requester }}</li>
                                <li><strong>Sección:</strong> {{ $request->section_area }}</li>
                                <li><strong>Estado:</strong> {{ $request->status }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resend_email"><i class="fas fa-envelope mr-1"></i> Correo de destino <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="resend_email" name="email" 
                                       placeholder="correo@ejemplo.com" required>
                                <small class="form-text text-muted">
                                    Ingrese el correo electrónico al cual enviar la solicitud para pre-aprobación.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="resend_message"><i class="fas fa-comment mr-1"></i> Mensaje adicional (opcional)</label>
                        <textarea class="form-control" id="resend_message" name="message" rows="3" 
                                  placeholder="Ingrese cualquier mensaje adicional para incluir en el correo..."></textarea>
                        <small class="form-text text-muted">Máximo 500 caracteres.</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Nota:</strong> Al reenviar esta solicitud se enviará un correo electrónico con toda la información 
                        necesaria y un enlace directo para revisar la solicitud.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane mr-1"></i> Reenviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    $(document).ready(function() {
        // Mostrar modal de información de aprobación si está presente
        @if(session('approvalInfo') || isset($approvalInfo))
            $('#approvalInfoModal').modal('show');
        @endif
        
        // Configurar el modal de pre-aprobación con cotización
        $('#preApproveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var quotationId = button.data('quotation-id');
            var provider = button.data('provider');
            
            var modal = $(this);
            modal.find('#quotation_id').val(quotationId);
            modal.find('#provider-name').text(provider);
            
            // Inicializar estado del botón
            const hasBudget = $('#budget').val() !== '';
            $('#preApproveSubmitBtn').prop('disabled', !hasBudget);
        });
        
        // Validación para el modal de pre-aprobación normal (radio buttons)
        $('.budget-radio').on('change', function() {
            const hasBudget = $('input[name="budget"]:checked').length > 0;
            $('#preApproveSubmitBtn').prop('disabled', !hasBudget);
        });
        
        // Limpiar modal de pre-aprobación normal al cerrar
        $('#preApproveModal').on('hidden.bs.modal', function () {
            $('#comments').val('');
            $('.budget-radio').prop('checked', false);
            $('#preApproveSubmitBtn').prop('disabled', true);
        });
        
        // Agregar evento de depuración para el formulario de pre-aprobación normal
        $('#preApproveModal form').on('submit', function(e) {
            console.log('Formulario de pre-aprobación normal enviándose...');
            console.log('Datos del formulario:', $(this).serialize());
            
            const quotationId = $('#quotation_id').val();
            const comments = $('#comments').val();
            const budget = $('input[name="budget"]:checked').val();
            
            console.log('Quotation ID:', quotationId);
            console.log('Comentarios:', comments);
            console.log('Presupuesto:', budget);
            
            if (!budget) {
                e.preventDefault();
                alert('Por favor seleccione un rubro presupuestal');
                return false;
            }
            
            if (!quotationId) {
                e.preventDefault();
                alert('Error: No se ha seleccionado una cotización');
                return false;
            }
            
            console.log('Formulario válido, enviando...');
        });
        
        // Manejar el modal de pre-aprobación sin cotización (radio buttons)
        $('#confirm_no_quotation').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasComments = $('#approval_comments').val().trim().length > 0;
            const hasBudgetLine = $('input[name="budget_line"]:checked').length > 0;
            $('#confirmPreApproveBtn').prop('disabled', !(isChecked && hasComments && hasBudgetLine));
        });
        
        $('#approval_comments').on('input', function() {
            const isChecked = $('#confirm_no_quotation').is(':checked');
            const hasComments = $(this).val().trim().length > 0;
            const hasBudgetLine = $('input[name="budget_line"]:checked').length > 0;
            $('#confirmPreApproveBtn').prop('disabled', !(isChecked && hasComments && hasBudgetLine));
        });
        
        $('.budget-radio-no-quotation').on('change', function() {
            const isChecked = $('#confirm_no_quotation').is(':checked');
            const hasComments = $('#approval_comments').val().trim().length > 0;
            const hasBudgetLine = $('input[name="budget_line"]:checked').length > 0;
            $('#confirmPreApproveBtn').prop('disabled', !(isChecked && hasComments && hasBudgetLine));
        });
        
        // Limpiar modal al cerrar
        $('#preApproveWithoutQuotationModal').on('hidden.bs.modal', function () {
            $('#approval_comments').val('');
            $('.budget-radio-no-quotation').prop('checked', false);
            $('#confirm_no_quotation').prop('checked', false);
            $('#confirmPreApproveBtn').prop('disabled', true);
        });
        
        // Manejar el modal de pre-aprobación de selección mixta (radio buttons)
        $('#confirm_mixed_selection').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasBudget = $('input[name="budget_line"]:checked').length > 0;
            $('#confirmMixedSelectionBtn').prop('disabled', !(isChecked && hasBudget));
        });
        
        $('.budget-radio-mixed').on('change', function() {
            const isChecked = $('#confirm_mixed_selection').is(':checked');
            const hasBudget = $('input[name="budget_line"]:checked').length > 0;
            $('#confirmMixedSelectionBtn').prop('disabled', !(isChecked && hasBudget));
        });
        
        // Limpiar modal de selección mixta al cerrar
        $('#preApproveMixedSelectionModal').on('hidden.bs.modal', function () {
            $('#mixed_selection_comments').val('');
            $('.budget-radio-mixed').prop('checked', false);
            $('#confirm_mixed_selection').prop('checked', false);
            $('#confirmMixedSelectionBtn').prop('disabled', true);
        });
        
        // Manejar el modal de rechazo
        $('#confirm_rejection').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasReason = $('#rejection_reason').val().trim().length > 0;
            $('#confirmRejectBtn').prop('disabled', !(isChecked && hasReason));
        });
        
        $('#rejection_reason').on('input', function() {
            const isChecked = $('#confirm_rejection').is(':checked');
            const hasReason = $(this).val().trim().length > 0;
            $('#confirmRejectBtn').prop('disabled', !(isChecked && hasReason));
        });
        
        // Limpiar modal de rechazo al cerrar
        $('#rejectModal').on('hidden.bs.modal', function () {
            $('#rejection_reason').val('');
            $('#confirm_rejection').prop('checked', false);
            $('#confirmRejectBtn').prop('disabled', true);
        });
        
        // Agregar evento de depuración para el formulario de rechazo
        $('#rejectModal form').on('submit', function(e) {
            console.log('Formulario de rechazo enviándose...');
            console.log('Datos del formulario:', $(this).serialize());
            
            const reason = $('#rejection_reason').val().trim();
            const confirmed = $('#confirm_rejection').is(':checked');
            
            console.log('Motivo:', reason);
            console.log('Confirmado:', confirmed);
            
            if (!reason) {
                e.preventDefault();
                alert('Por favor ingrese el motivo del rechazo');
                return false;
            }
            
            if (!confirmed) {
                e.preventDefault();
                alert('Por favor confirme que desea rechazar la solicitud');
                return false;
            }
            
            console.log('Formulario válido, enviando...');
        });
        
        // Agregar evento de depuración para el formulario de pre-aprobación de selección mixta
        $('#preApproveMixedSelectionModal form').on('submit', function(e) {
            console.log('Formulario de selección mixta enviándose...');
            console.log('Datos del formulario:', $(this).serialize());
            
            // Verificar que todos los campos requeridos estén completos
            const comments = $('#mixed_selection_comments').val();
            const budget = $('input[name="budget_line"]:checked').val();
            const confirmed = $('#confirm_mixed_selection').is(':checked');
            
            console.log('Comentarios:', comments);
            console.log('Presupuesto:', budget);
            console.log('Confirmado:', confirmed);
            
            if (!budget) {
                e.preventDefault();
                alert('Por favor seleccione un rubro presupuestal');
                return false;
            }
            
            if (!confirmed) {
                e.preventDefault();
                alert('Por favor confirme la selección mixta');
                return false;
            }
            
            console.log('Formulario válido, enviando...');
        });
    });
</script>
@stop