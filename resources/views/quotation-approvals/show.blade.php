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
                    @if($request->quotations->count() >= 2 && $request->status !== 'Pre-aprobada' && $request->status !== 'approved')
                        <a href="{{ route('quotation-selections.show', $request->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-tasks"></i> Selección Mixta
                        </a>
                    @elseif($request->quotations->count() >= 2 && $request->status === 'approved')
                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                            <i class="fas fa-tasks"></i> Selección Mixta
                            <small class="d-block">(Aprobada)</small>
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
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
                                        <h6 class="text-primary"><strong>Justificación de la Compra:</strong></h6>
                                        <p class="info-box-text">{{ $request->purchase_justification }}</p>
                                    </div>
                                @endif
                                
                                @if($request->type === 'purchase' && $request->service_justification)
                                    <div class="mt-3">
                                        <h6 class="text-primary"><strong>Justificación del Servicio:</strong></h6>
                                        <p class="info-box-text">{{ $request->service_justification }}</p>
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
                                        <p class="info-box-text">{{ $request->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

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
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#preApproveMixedSelectionModal">
                                            <i class="fas fa-check-circle"></i> Pre-aprobar Selección Mixta
                                        </button>
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
                                            <button class="btn btn-success btn-block mt-3" data-toggle="modal" data-target="#preApproveModal" 
                                                data-quotation-id="{{ $quotation->id }}" data-provider="{{ $quotation->provider_name }}">
                                                <i class="fas fa-check-circle"></i> Pre-aprobar esta cotización
                                            </button>
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
                                            <button class="btn btn-success" data-toggle="modal" data-target="#preApproveWithoutQuotationModal">
                                                <i class="fas fa-check-circle"></i> Pre-aprobar Sin Cotización
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
                        <select class="form-control" id="budget" name="budget" required>
                            <option value="">Seleccione un rubro presupuestal...</option>
                            <option value="Tecnología Institucional">Tecnología Institucional</option>
                            <option value="Tecnología (secciones)">Tecnología (secciones)</option>
                            <option value="Internet/Arrendamientos Tecnológicos">Internet/Arrendamientos Tecnológicos</option>
                            <option value="Capacitación EMC/Docentes">Capacitación EMC/Docentes</option>
                            <option value="Capacitación Administración">Capacitación Administración</option>
                            <option value="Capacitación General">Capacitación General</option>
                            <option value="Capacitación COPASST">Capacitación COPASST</option>
                            <option value="Equipos y Dotación Salones/Oficinas">Equipos y Dotación Salones/Oficinas</option>
                            <option value="Mercadeo">Mercadeo</option>
                            <option value="Eventos">Eventos</option>
                            <option value="Reparaciones Mayores">Reparaciones Mayores</option>
                            <option value="Útiles de Oficina y Papelería">Útiles de Oficina y Papelería</option>
                            <option value="Bachillerato Internacional">Bachillerato Internacional</option>
                            <option value="Textos y Útiles de Consumo">Textos y Útiles de Consumo</option>
                            <option value="Deportes">Deportes</option>
                            <option value="Biblioteca Institucional">Biblioteca Institucional</option>
                            <option value="Materiales">Materiales</option>
                            <option value="Servicios Públicos">Servicios Públicos</option>
                            <option value="Vigilancia">Vigilancia</option>
                            <option value="Honorarios">Honorarios</option>
                            <option value="Comisiones Bancarias">Comisiones Bancarias</option>
                            <option value="Arrendamientos">Arrendamientos</option>
                            <option value="Cafetería">Cafetería</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Salarios Academia">Salarios Academia</option>
                            <option value="Salarios Administrativos">Salarios Administrativos</option>
                        </select>
                        <small class="form-text text-muted">Seleccione el rubro presupuestal donde se cargará esta compra.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Pre-aprobación</button>
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
                        <select name="budget_line" id="budget_line_no_quotation" class="form-control" required>
                            <option value="">Seleccione un rubro presupuestal</option>
                            <option value="Textos y Guías de Estudio">Textos y Guías de Estudio</option>
                            <option value="Laboratorios">Laboratorios</option>
                            <option value="Equipos y Dotación Salones/Oficinas">Equipos y Dotación Salones/Oficinas</option>
                            <option value="Mercadeo">Mercadeo</option>
                            <option value="Eventos">Eventos</option>
                            <option value="Reparaciones Mayores">Reparaciones Mayores</option>
                            <option value="Útiles de Oficina y Papelería">Útiles de Oficina y Papelería</option>
                            <option value="Bachillerato Internacional">Bachillerato Internacional</option>
                            <option value="Textos y Útiles de Consumo">Textos y Útiles de Consumo</option>
                            <option value="Deportes">Deportes</option>
                            <option value="Biblioteca Institucional">Biblioteca Institucional</option>
                            <option value="Materiales">Materiales</option>
                            <option value="Servicios Públicos">Servicios Públicos</option>
                            <option value="Vigilancia">Vigilancia</option>
                            <option value="Honorarios">Honorarios</option>
                            <option value="Comisiones Bancarias">Comisiones Bancarias</option>
                            <option value="Arrendamientos">Arrendamientos</option>
                            <option value="Cafetería">Cafetería</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Salarios Academia">Salarios Academia</option>
                            <option value="Salarios Administrativos">Salarios Administrativos</option>
                        </select>
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
                        <select name="budget_line" id="mixed_selection_budget" class="form-control" required>
                            <option value="">Seleccione un rubro presupuestal</option>
                            <option value="Tecnología Institucional">Tecnología Institucional</option>
                            <option value="Tecnología (secciones)">Tecnología (secciones)</option>
                            <option value="Internet/Arrendamientos Tecnológicos">Internet/Arrendamientos Tecnológicos</option>
                            <option value="Capacitación EMC/Docentes">Capacitación EMC/Docentes</option>
                            <option value="Capacitación Administración">Capacitación Administración</option>
                            <option value="Capacitación General">Capacitación General</option>
                            <option value="Capacitación COPASST">Capacitación COPASST</option>
                            <option value="Equipos y Dotación Salones/Oficinas">Equipos y Dotación Salones/Oficinas</option>
                            <option value="Mercadeo">Mercadeo</option>
                            <option value="Eventos">Eventos</option>
                            <option value="Reparaciones Mayores">Reparaciones Mayores</option>
                            <option value="Útiles de Oficina y Papelería">Útiles de Oficina y Papelería</option>
                            <option value="Bachillerato Internacional">Bachillerato Internacional</option>
                            <option value="Textos y Útiles de Consumo">Textos y Útiles de Consumo</option>
                            <option value="Deportes">Deportes</option>
                            <option value="Biblioteca Institucional">Biblioteca Institucional</option>
                            <option value="Materiales">Materiales</option>
                            <option value="Servicios Públicos">Servicios Públicos</option>
                            <option value="Vigilancia">Vigilancia</option>
                            <option value="Honorarios">Honorarios</option>
                            <option value="Comisiones Bancarias">Comisiones Bancarias</option>
                            <option value="Arrendamientos">Arrendamientos</option>
                            <option value="Cafetería">Cafetería</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Salarios Academia">Salarios Academia</option>
                            <option value="Salarios Administrativos">Salarios Administrativos</option>
                        </select>
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
    </style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Configurar el modal de pre-aprobación con cotización
        $('#preApproveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var quotationId = button.data('quotation-id');
            var provider = button.data('provider');
            
            var modal = $(this);
            modal.find('#quotation_id').val(quotationId);
            modal.find('#provider-name').text(provider);
        });
        
        // Manejar el modal de pre-aprobación sin cotización
        $('#confirm_no_quotation').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasComments = $('#approval_comments').val().trim().length > 0;
            const hasBudgetLine = $('#budget_line_no_quotation').val() !== '';
            $('#confirmPreApproveBtn').prop('disabled', !(isChecked && hasComments && hasBudgetLine));
        });
        
        $('#approval_comments, #budget_line_no_quotation').on('input change', function() {
            const isChecked = $('#confirm_no_quotation').is(':checked');
            const hasComments = $('#approval_comments').val().trim().length > 0;
            const hasBudgetLine = $('#budget_line_no_quotation').val() !== '';
            $('#confirmPreApproveBtn').prop('disabled', !(isChecked && hasComments && hasBudgetLine));
        });
        
        // Limpiar modal al cerrar
        $('#preApproveWithoutQuotationModal').on('hidden.bs.modal', function () {
            $('#approval_comments').val('');
            $('#budget_line_no_quotation').val('');
            $('#confirm_no_quotation').prop('checked', false);
            $('#confirmPreApproveBtn').prop('disabled', true);
        });
        
        // Manejar el modal de pre-aprobación de selección mixta
        $('#confirm_mixed_selection').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasBudget = $('#mixed_selection_budget').val() !== '';
            $('#confirmMixedSelectionBtn').prop('disabled', !(isChecked && hasBudget));
        });
        
        $('#mixed_selection_budget').on('change', function() {
            const isChecked = $('#confirm_mixed_selection').is(':checked');
            const hasBudget = $(this).val() !== '';
            $('#confirmMixedSelectionBtn').prop('disabled', !(isChecked && hasBudget));
        });
        
        // Limpiar modal de selección mixta al cerrar
        $('#preApproveMixedSelectionModal').on('hidden.bs.modal', function () {
            $('#mixed_selection_comments').val('');
            $('#mixed_selection_budget').val('');
            $('#confirm_mixed_selection').prop('checked', false);
            $('#confirmMixedSelectionBtn').prop('disabled', true);
        });
        
        // Agregar evento de depuración para el formulario de selección mixta
        $('#preApproveMixedSelectionModal form').on('submit', function(e) {
            console.log('Formulario de selección mixta enviándose...');
            console.log('Datos del formulario:', $(this).serialize());
            
            // Verificar que todos los campos requeridos estén completos
            const comments = $('#mixed_selection_comments').val();
            const budget = $('#mixed_selection_budget').val();
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