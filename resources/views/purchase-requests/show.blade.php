@extends('adminlte::page')

@section('title', 'Detalle de Solicitud')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">
                @if($purchaseRequest->type == 'purchase')
                    <i class="fas fa-shopping-cart text-primary mr-2"></i>
                @elseif($purchaseRequest->isCopiesRequest())
                    <i class="fas fa-copy text-info mr-2"></i>
                @else
                    <i class="fas fa-boxes text-success mr-2"></i>
                @endif
                Solicitud #{{ $purchaseRequest->request_number }}
            </h1>
            <p class="text-muted">
                <small>Creada el {{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</small>
            </p>
        </div>
        <div class="d-flex">
            @if($purchaseRequest->status == 'pending')
                <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="btn btn-outline-primary btn-sm mr-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            
            {{-- Botón especial para editar solicitudes de fotocopias aprobadas (solo almacén y admin) --}}
            @php
                $permissionService = new \App\Services\PurchaseRequestPermissionService();
            @endphp
            @if($permissionService->canEditApprovedCopiesRequest($purchaseRequest))
                <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="btn btn-outline-warning btn-sm mr-2" 
                   title="Editar fotocopias antes de entregar">
                    <i class="fas fa-edit"></i> Editar Fotocopias
                </a>
            @endif
            
            @if(auth()->user()->can('approve-purchase-requests'))
                @if($purchaseRequest->status == 'Pre-aprobada')
                    <a href="{{ route('approvals.show', $purchaseRequest->id) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-check-circle"></i> Autorizar
                    </a>
                @elseif($purchaseRequest->status == 'pending' && in_array($purchaseRequest->type, ['materials']))
                    <a href="{{ route('approvals.show', $purchaseRequest->id) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-check-circle"></i> Autorizar
                    </a>
                @endif
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Status Timeline -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-body p-3">
                    <div class="timeline-steps">
                        <div class="timeline-step {{ $purchaseRequest->created_at ? 'active' : '' }}">
                            <div class="timeline-content">
                                <div class="inner-circle {{ $purchaseRequest->created_at ? 'bg-primary' : 'bg-secondary' }}">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <p class="h6 mt-3 mb-1">Creada</p>
                                <p class="h6 text-muted mb-0 mb-lg-0">
                                    {{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('d/m/Y') : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="timeline-step {{ $purchaseRequest->status != 'pending' && $purchaseRequest->status != 'approved' ? 'active' : '' }}">
                            <div class="timeline-content">
                                <div class="inner-circle {{ $purchaseRequest->status != 'pending' && $purchaseRequest->status != 'approved' ? 
                                    ($purchaseRequest->status == 'En Cotización' ? 'bg-info' : 
                                     ($purchaseRequest->status == 'Pre-aprobada' ? 'bg-warning' : 
                                      ($purchaseRequest->status == 'rejected' ? 'bg-danger' : 'bg-primary'))) 
                                    : 'bg-secondary' }}">
                                    <i class="fas {{ 
                                        $purchaseRequest->status == 'En Cotización' ? 'fa-file-invoice' : 
                                        ($purchaseRequest->status == 'Pre-aprobada' ? 'fa-thumbs-up' : 
                                         ($purchaseRequest->status == 'rejected' ? 'fa-times' : 'fa-cog')) 
                                    }}"></i>
                                </div>
                                <p class="h6 mt-3 mb-1">Procesada</p>
                                <p class="h6 text-muted mb-0 mb-lg-0">
                                    {{ $purchaseRequest->status != 'pending' && $purchaseRequest->status != 'approved' && $purchaseRequest->approval_date ? $purchaseRequest->approval_date->format('d/m/Y') : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="timeline-step {{ $purchaseRequest->status == 'approved' ? 'active' : '' }}">
                            <div class="timeline-content">
                                <div class="inner-circle {{ $purchaseRequest->status == 'approved' ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="h6 mt-3 mb-1">Aprobada</p>
                                <p class="h6 text-muted mb-0 mb-lg-0">
                                    {{ $purchaseRequest->status == 'approved' && $purchaseRequest->approval_date ? $purchaseRequest->approval_date->format('d/m/Y') : '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Información general -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Información General
                    </h3>
                    <div class="card-tools">
                        <span class="badge 
                            @if($purchaseRequest->status == 'pending') badge-warning 
                            @elseif($purchaseRequest->status == 'approved') badge-success 
                            @elseif($purchaseRequest->status == 'En Cotización') badge-info
                            @elseif($purchaseRequest->status == 'Pre-aprobada') badge-warning
                            @else badge-danger @endif">
                            @if($purchaseRequest->status == 'pending') Pendiente 
                            @elseif($purchaseRequest->status == 'approved') Aprobada 
                            @elseif($purchaseRequest->status == 'En Cotización') En Cotización
                            @elseif($purchaseRequest->status == 'Pre-aprobada') Pre-aprobada
                            @else Rechazada @endif
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table">
                        <tr>
                            <th style="width: 40%"><i class="fas fa-user mr-1 text-primary"></i> Solicitante:</th>
                            <td>{{ $purchaseRequest->requester }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-building mr-1 text-primary"></i> Sección/Área:</th>
                            <td>{{ $purchaseRequest->section_area }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar mr-1 text-primary"></i> Fecha solicitud:</th>
                            <td>{{ $purchaseRequest->request_date instanceof \DateTime ? $purchaseRequest->request_date->format('d/m/Y') : 'No establecida' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-user-tie mr-1 text-primary"></i> Usuario:</th>
                            <td>{{ $purchaseRequest->user->name }}</td>
                        </tr>
                        @if($purchaseRequest->status != 'pending')
                        <tr>
                            <th><i class="fas fa-calendar-check mr-1 text-primary"></i> Fecha respuesta:</th>
                            <td>{{ $purchaseRequest->approval_date instanceof \DateTime ? $purchaseRequest->approval_date->format('d/m/Y H:i') : 'No establecida' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-user-check mr-1 text-primary"></i> Revisado por:</th>
                            <td>{{ $purchaseRequest->approver->name ?? 'N/A' }}</td>
                        </tr>
                        @endif
                        @if($purchaseRequest->status == 'rejected' && $purchaseRequest->rejection_reason)
                        <tr class="bg-light">
                            <th colspan="2" class="text-danger">
                                <i class="fas fa-exclamation-circle mr-1"></i> Motivo de rechazo:
                            </th>
                        </tr>
                        <tr class="bg-light">
                            <td colspan="2" class="text-danger">
                                {{ $purchaseRequest->rejection_reason }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Información adicional para materiales -->
            @if($purchaseRequest->type != 'purchase')
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list mr-1"></i>
                        Detalles Adicionales
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <tr>
                            <th style="width: 40%"><i class="fas fa-hashtag mr-1 text-primary"></i> Código:</th>
                            <td>{{ $purchaseRequest->code }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-graduation-cap mr-1 text-primary"></i> Grado:</th>
                            <td>{{ $purchaseRequest->grade }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-chalkboard mr-1 text-primary"></i> Sección:</th>
                            <td>{{ $purchaseRequest->section }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar-alt mr-1 text-primary"></i> Fecha Entrega:</th>
                            <td>{{ $purchaseRequest->delivery_date ? $purchaseRequest->delivery_date->format('d/m/Y') : 'No especificada' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-8">
            <!-- Detalles específicos según el tipo de solicitud -->
            @if($purchaseRequest->type == 'purchase')
                <!-- Solicitud de compra -->
                @if($purchaseRequest->purchase_justification)
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-comment-alt mr-1"></i>
                            Justificación de la Compra
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $purchaseRequest->purchase_justification }}</p>
                    </div>
                </div>
                @endif

                <!-- Artículos de compra -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-basket mr-1"></i>
                            Artículos Solicitados
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
                                    @if(is_array($purchaseRequest->purchase_items))
                                        @php $hasItems = false; @endphp
                                        @foreach($purchaseRequest->purchase_items as $item)
                                            @if(!empty($item['quantity']))
                                                @php $hasItems = true; @endphp
                                                <tr>
                                                    <td>{{ $item['item'] ?? '' }}</td>
                                                    <td>{{ $item['quantity'] ?? '' }}</td>
                                                    <td>{{ $item['description'] ?? '' }}</td>
                                                    <td>{{ $item['unit'] ?? '' }}</td>
                                                    <td>{{ $item['observations'] ?? '' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if(!$hasItems)
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No hay artículos registrados</td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No hay artículos registrados</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cotizaciones (si existen) -->
                @if($purchaseRequest->type == 'purchase')
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice-dollar mr-1"></i>
                                Cotizaciones
                            </h3>
                            <div class="card-tools">
                                @if(!$purchaseRequest->hasRequiredQuotations() && in_array($purchaseRequest->status, ['pending', 'En Cotización']))
                                    <a href="{{ route('quotations.create', $purchaseRequest) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Agregar Cotización
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($purchaseRequest->quotations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Proveedor</th>
                                                <th>Monto</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchaseRequest->quotations as $quotation)
                                                <tr>
                                                    <td>{{ $quotation->provider_name }}</td>
                                                    <td>${{ number_format($quotation->total_amount, 2) }}</td>
                                                    <td>{{ $quotation->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if($quotation->is_selected)
                                                            <span class="badge badge-success">Seleccionada</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('quotations.download', $quotation) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        
                                                        @if($purchaseRequest->status == 'En Cotización' && $purchaseRequest->hasRequiredQuotations() && !$quotation->is_selected)
                                                            <a href="{{ route('quotations.select', $quotation) }}" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-check"></i> Seleccionar
                                                            </a>
                                                        @endif
                                                        
                                                        @if(!$purchaseRequest->purchaseOrder)
                                                            <form action="{{ route('quotations.destroy', $quotation) }}" method="POST" class="d-inline" 
                                                                onsubmit="return confirm('¿Está seguro de que desea eliminar esta cotización?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Botón para enviar email de pre-aprobación -->
                                @if($purchaseRequest->quotations->count() > 0 && 
                                    $purchaseRequest->status == 'En Cotización' && 
                                    (auth()->user()->hasRole(['admin', 'compras']) || 
                                     auth()->user()->id == $purchaseRequest->user_id))
                                    <div class="p-3 border-top">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="mb-1"><i class="fas fa-paper-plane mr-2 text-primary"></i>Notificación de Pre-aprobación</h6>
                                                <p class="text-muted mb-0 small">
                                                    Enviar las cotizaciones adjuntadas al supervisor correspondiente para su pre-aprobación.
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <form action="{{ route('quotations.send-preapproval-email', $purchaseRequest) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                            onclick="return confirm('¿Está seguro de enviar las cotizaciones para pre-aprobación?')">
                                                        <i class="fas fa-envelope"></i> Enviar para Pre-aprobación
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Cuadro comparativo si hay más de una cotización -->
                                @if($purchaseRequest->quotations->count() > 1)
                                    <div class="p-3">
                                        <h5><i class="fas fa-chart-bar mr-2"></i>Cuadro Comparativo</h5>
                                        <div class="chart-container" style="position: relative; height:200px;">
                                            <canvas id="quotationChart"></canvas>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="p-3 text-center text-muted">
                                    No hay cotizaciones registradas para esta solicitud.
                                    @if(in_array($purchaseRequest->status, ['pending', 'En Cotización']))
                                        <br>
                                        <a href="{{ route('quotations.create', $purchaseRequest) }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus"></i> Agregar Cotización
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Configuración de Cotizaciones (solo para administradores) -->
                    @if(auth()->user()->hasRole(['admin', 'compras']) && $purchaseRequest->type == 'purchase')
                        <div class="card card-secondary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cogs mr-1"></i>
                                    Configuración de Cotizaciones
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            <strong>Estado actual:</strong> 
                                            Requiere {{ $purchaseRequest->getRequiredQuotationsCount() }} cotizaciones 
                                            ({{ $purchaseRequest->getQuotationProgress() }})
                                        </p>
                                        @if($purchaseRequest->canProceedEarly())
                                            <p class="text-info mb-0">
                                                <i class="fas fa-info-circle"></i> 
                                                Se permite proceder con menos cotizaciones si es necesario.
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <form action="{{ route('purchase-requests.configure-quotations', $purchaseRequest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="form-group mb-2">
                                                <label for="required_quotations" class="sr-only">Cotizaciones requeridas</label>
                                                <select name="required_quotations" id="required_quotations" class="form-control form-control-sm">
                                                    <option value="1" {{ $purchaseRequest->getRequiredQuotationsCount() == 1 ? 'selected' : '' }}>1 cotización</option>
                                                    <option value="2" {{ $purchaseRequest->getRequiredQuotationsCount() == 2 ? 'selected' : '' }}>2 cotizaciones</option>
                                                    <option value="3" {{ $purchaseRequest->getRequiredQuotationsCount() == 3 ? 'selected' : '' }}>3 cotizaciones</option>
                                                    <option value="4" {{ $purchaseRequest->getRequiredQuotationsCount() == 4 ? 'selected' : '' }}>4 cotizaciones</option>
                                                    <option value="5" {{ $purchaseRequest->getRequiredQuotationsCount() == 5 ? 'selected' : '' }}>5 cotizaciones</option>
                                                </select>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="can_proceed_early" id="can_proceed_early" class="form-check-input" value="1" {{ $purchaseRequest->canProceedEarly() ? 'checked' : '' }}>
                                                <label for="can_proceed_early" class="form-check-label small">Permitir proceder temprano</label>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-save"></i> Actualizar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                
                <!-- Servicios (si hay) -->
                @if(is_array($purchaseRequest->service_items) && count(array_filter($purchaseRequest->service_items, function($item) { return !empty($item['quantity']); })))
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-concierge-bell mr-1"></i>
                                Servicios Solicitados
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($purchaseRequest->service_budget)
                                <div class="alert alert-info">
                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                    <strong>Presupuesto:</strong> ${{ number_format($purchaseRequest->service_budget, 2) }} 
                                    @if($purchaseRequest->service_budget_text)
                                        ({{ $purchaseRequest->service_budget_text }})
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
                                        @foreach($purchaseRequest->service_items as $service)
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
            @else
                <!-- Fotocopias -->
                @if(is_array($purchaseRequest->copy_items) && count(array_filter($purchaseRequest->copy_items, function($item) { return !empty($item['copies_required']); })))
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-copy mr-1"></i>
                                Fotocopias Solicitadas
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 30%">Original</th>
                                            <th style="width: 15%">Copias Req.</th>
                                            <th style="width: 15%">Doble Carta Color</th>
                                            <th style="width: 15%">B/N</th>
                                            <th style="width: 15%">Color</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseRequest->copy_items as $copy)
                                            @if(!empty($copy['copies_required']))
                                            <tr>
                                                <td>{{ $copy['item'] ?? '' }}</td>
                                                <td>{{ $copy['original'] ?? '' }}</td>
                                                <td>{{ $copy['copies_required'] ?? '' }}</td>
                                                <td>{{ $copy['double_letter_color'] ?? '0' }}</td>
                                                <td>{{ $copy['black_white'] ?? '0' }}</td>
                                                <td>{{ $copy['color'] ?? '0' }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Archivo Original -->
                    @if($purchaseRequest->original_file)
                        <div class="card card-info card-outline mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    Archivo Original Adjunto
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            <i class="fas fa-file mr-2 text-info"></i>
                                            <strong>Archivo disponible para descarga</strong>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Este es el archivo original que se necesita fotocopiar. 
                                            Puede descargarlo para revisión o referencia.
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <a href="{{ route('purchase-requests.download-original', $purchaseRequest->id) }}" 
                                           class="btn btn-info btn-sm shadow-sm">
                                            <i class="fas fa-download mr-1"></i>
                                            Descargar Archivo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Especificaciones de Fotocopias -->
                    @if($purchaseRequest->isCopiesRequest() && 
                        ($purchaseRequest->paper_size || $purchaseRequest->paper_type || $purchaseRequest->paper_color || 
                         $purchaseRequest->requires_binding || $purchaseRequest->requires_lamination || 
                         $purchaseRequest->requires_cutting || $purchaseRequest->special_details))
                        <div class="card card-secondary card-outline mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cogs mr-2"></i>
                                    Especificaciones
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Especificaciones de papel -->
                                    @if($purchaseRequest->paper_size || $purchaseRequest->paper_type || $purchaseRequest->paper_color)
                                        <div class="col-md-6">
                                            <h6 class="text-secondary mb-3">
                                                <i class="fas fa-file-alt mr-1"></i>
                                                Características del Papel
                                            </h6>
                                            @if($purchaseRequest->paper_size)
                                                <p class="mb-2">
                                                    <strong>Tamaño:</strong> 
                                                    <span class="badge badge-info">{{ $purchaseRequest->paper_size }}</span>
                                                </p>
                                            @endif
                                            @if($purchaseRequest->paper_type)
                                                <p class="mb-2">
                                                    <strong>Tipo:</strong> 
                                                    <span class="badge badge-secondary">{{ $purchaseRequest->paper_type }}</span>
                                                </p>
                                            @endif
                                            @if($purchaseRequest->paper_color)
                                                <p class="mb-2">
                                                    <strong>Color:</strong> 
                                                    <span class="badge badge-light">{{ $purchaseRequest->paper_color }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Servicios adicionales -->
                                    @if($purchaseRequest->requires_binding || $purchaseRequest->requires_lamination || $purchaseRequest->requires_cutting)
                                        <div class="col-md-6">
                                            <h6 class="text-secondary mb-3">
                                                <i class="fas fa-tools mr-1"></i>
                                                Servicios Adicionales
                                            </h6>
                                            @if($purchaseRequest->requires_binding)
                                                <p class="mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    Requiere anillado
                                                </p>
                                            @endif
                                            @if($purchaseRequest->requires_lamination)
                                                <p class="mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    Requiere laminado
                                                </p>
                                            @endif
                                            @if($purchaseRequest->requires_cutting)
                                                <p class="mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    Requiere recortes
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Detalles especiales -->
                                @if($purchaseRequest->special_details)
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <h6 class="text-secondary mb-2">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                Detalles Especiales
                                            </h6>
                                            <div class="alert alert-light border">
                                                <i class="fas fa-quote-left text-muted mr-2"></i>
                                                {{ $purchaseRequest->special_details }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Materiales -->
                @if(is_array($purchaseRequest->material_items) && count(array_filter($purchaseRequest->material_items, function($item) { return !empty($item['quantity']); })))
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-boxes mr-1"></i>
                                Materiales Solicitados
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 60%">Artículo</th>
                                            <th style="width: 15%">Cantidad</th>
                                            <th style="width: 20%">Objetivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseRequest->material_items as $material)
                                            @if(!empty($material['quantity']))
                                            <tr>
                                                <td>{{ $material['item'] ?? '' }}</td>
                                                <td>{{ $material['article'] ?? '' }}</td>
                                                <td>{{ $material['quantity'] ?? '' }}</td>
                                                <td>{{ $material['objective'] ?? '' }}</td>
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

            <!-- Control de Estado de Entrega para Fotocopias -->
            @if($purchaseRequest->isCopiesRequest() && in_array($purchaseRequest->status, ['approved', 'authorized']) && (auth()->user()->hasRole('compras') || auth()->user()->hasRole('admin')))
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-truck mr-1"></i>
                            Control de Entrega
                        </h3>
                        @if($purchaseRequest->delivery_status)
                            <div class="card-tools">
                                <span class="badge 
                                    @if($purchaseRequest->isDelivered()) badge-success 
                                    @elseif($purchaseRequest->isNotDelivered()) badge-danger 
                                    @else badge-warning @endif">
                                    @if($purchaseRequest->isDelivered()) Entregado 
                                    @elseif($purchaseRequest->isNotDelivered()) No Entregado 
                                    @else Pendiente @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($purchaseRequest->delivery_status)
                            <!-- Mostrar estado actual -->
                            <div class="alert 
                                @if($purchaseRequest->isDelivered()) alert-success 
                                @elseif($purchaseRequest->isNotDelivered()) alert-danger 
                                @else alert-warning @endif">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong>
                                            <i class="fas 
                                                @if($purchaseRequest->isDelivered()) fa-check-circle 
                                                @elseif($purchaseRequest->isNotDelivered()) fa-times-circle 
                                                @else fa-clock @endif mr-1"></i>
                                            Estado: 
                                            @if($purchaseRequest->isDelivered()) Entregado 
                                            @elseif($purchaseRequest->isNotDelivered()) No Entregado 
                                            @else Pendiente @endif
                                        </strong>
                                        @if($purchaseRequest->delivery_marked_at)
                                            <br><small>Marcado el {{ $purchaseRequest->delivery_marked_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                        @if($purchaseRequest->deliveryMarker)
                                            <br><small>Por: {{ $purchaseRequest->deliveryMarker->name }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#deliveryModal">
                                            <i class="fas fa-edit"></i> Cambiar Estado
                                        </button>
                                    </div>
                                </div>
                                @if($purchaseRequest->delivery_notes)
                                    <hr class="my-2">
                                    <strong>Notas:</strong> {{ $purchaseRequest->delivery_notes }}
                                @endif
                            </div>
                        @else
                                <!-- Controles para marcar estado inicial -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Esta solicitud de fotocopias está lista para entrega.</strong>
                                    <br>Por favor, marque el estado de entrega una vez que haya realizado la gestión correspondiente.
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#deliveryModal">
                                        <i class="fas fa-check"></i> Marcar como Entregado
                                    </button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deliveryModal">
                                        <i class="fas fa-times"></i> Marcar como No Entregado
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal para gestión de estado de entrega -->
                <div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog" aria-labelledby="deliveryModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="{{ route('purchase-requests.mark-delivery-status', $purchaseRequest) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deliveryModalLabel">
                                        <i class="fas fa-truck mr-2"></i>Gestión de Estado de Entrega
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="delivery_status">
                                            <i class="fas fa-clipboard-check mr-1"></i>Estado de Entrega <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="delivery_status" name="delivery_status" required>
                                            <option value="">Seleccione un estado</option>
                                            <option value="delivered" {{ $purchaseRequest->isDelivered() ? 'selected' : '' }}>
                                                ✅ Entregado
                                            </option>
                                            <option value="not_delivered" {{ $purchaseRequest->isNotDelivered() ? 'selected' : '' }}>
                                                ❌ No Entregado
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="delivery_notes">
                                            <i class="fas fa-sticky-note mr-1"></i>Notas (Opcional)
                                        </label>
                                        <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3" 
                                                  placeholder="Agregue cualquier observación sobre la entrega...">{{ $purchaseRequest->delivery_notes }}</textarea>
                                        <small class="form-text text-muted">
                                            Puede incluir detalles como lugar de entrega, persona que recibió, observaciones, etc.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>Guardar Estado
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
            </a>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Timeline Steps */
    .timeline-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }
    
    .timeline-steps:before {
        content: '';
        position: absolute;
        background: #e5e5e5;
        height: 3px;
        width: 100%;
        top: 20px;
        z-index: 0;
    }
    
    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }
    
    .timeline-step.active .inner-circle {
        background-color: var(--primary);
    }
    
    .timeline-content {
        text-align: center;
    }
    
    .inner-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e5e5e5;
        color: white;
    }
    
    /* Card improvements */
    .card-primary.card-outline {
        border-top: 3px solid #007bff;
    }
    
    .card-header {
        padding: 0.75rem 1.25rem;
        background-color: rgba(0,0,0,.03);
    }
    
    .card-title {
        margin-bottom: 0;
    }
    
    /* Table improvements */
    .table th {
        background-color: #f8f9fa;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    /* Delivery status styles */
    .delivery-status-card {
        border-left: 4px solid #007bff;
    }
    
    .delivery-status-card.delivered {
        border-left-color: #28a745;
    }
    
    .delivery-status-card.not-delivered {
        border-left-color: #dc3545;
    }
    
    .delivery-status-card.pending {
        border-left-color: #ffc107;
    }
    
    .delivery-controls .btn {
        min-width: 140px;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .modal-title i {
        color: #007bff;
    }
    
    /* Alert improvements */
    .alert {
        border: none;
        border-radius: 8px;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
        color: #721c24;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }

    /* Responsive improvements */
    @media (max-width: 767.98px) {
        .timeline-steps {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .timeline-steps:before {
            display: none;
        }
        
        .timeline-step {
            width: 100%;
            margin-bottom: 1rem;
            flex-direction: row;
            align-items: center;
        }
        
        .timeline-content {
            margin-left: 1rem;
            text-align: left;
        }
        
        .delivery-controls .btn {
            min-width: auto;
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        // Tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Animación de entrada para las cards
        $('.card').each(function(index) {
            $(this).css({
                'animation-delay': (index * 0.1) + 's',
                'animation': 'fadeInUp 0.5s ease forwards'
            });
        });
        
        // Gráfico comparativo de cotizaciones
        @if(isset($purchaseRequest) && $purchaseRequest->quotations && $purchaseRequest->quotations->count() > 1)
            var ctx = document.getElementById('quotationChart').getContext('2d');
            var quotationChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        @foreach($purchaseRequest->quotations as $q)
                            '{{ $q->provider_name }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Monto ($)',
                        data: [
                            @foreach($purchaseRequest->quotations as $q)
                                {{ $q->total_amount }},
                            @endforeach
                        ],
                        backgroundColor: [
                            @foreach($purchaseRequest->quotations as $q)
                                '{{ $q->is_selected ? "rgba(40, 167, 69, 0.7)" : "rgba(0, 123, 255, 0.7)" }}',
                            @endforeach
                        ],
                        borderColor: [
                            @foreach($purchaseRequest->quotations as $q)
                                '{{ $q->is_selected ? "rgb(40, 167, 69)" : "rgb(0, 123, 255)" }}',
                            @endforeach
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                });
        @endif
    });
    
    // Animación para las cards
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeIn');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        cards.forEach(card => {
            observer.observe(card);
        });
        
        // Manejo del modal de estado de entrega
        $('#deliveryModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            // Si se hace clic en un botón específico, preseleccionar el estado
            if (button.hasClass('btn-success')) {
                modal.find('#delivery_status').val('delivered');
            } else if (button.hasClass('btn-danger')) {
                modal.find('#delivery_status').val('not_delivered');
            }
        });
        
        // Validación del formulario de entrega
        $('form[action*="mark-delivery-status"]').on('submit', function(e) {
            var status = $('#delivery_status').val();
            if (!status) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Estado requerido',
                    text: 'Por favor seleccione un estado de entrega.',
                    confirmButtonColor: '#007bff'
                });
                return false;
            }
            
            // Confirmación antes de enviar
            e.preventDefault();
            var statusText = status === 'delivered' ? 'ENTREGADO' : 'NO ENTREGADO';
            var notes = $('#delivery_notes').val();
            var confirmText = 'Se marcará la solicitud como ' + statusText;
            if (notes) {
                confirmText += '\n\nNotas: ' + notes;
            }
            
            Swal.fire({
                title: '¿Confirmar estado de entrega?',
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: status === 'delivered' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
        
        // Cambio de color del select según la opción seleccionada
        $('#delivery_status').on('change', function() {
            var $this = $(this);
            $this.removeClass('border-success border-danger border-warning');
            
            if (this.value === 'delivered') {
                $this.addClass('border-success');
            } else if (this.value === 'not_delivered') {
                $this.addClass('border-danger');
            } else {
                $this.addClass('border-warning');
            }
        });
    });
</script>
@stop