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
                            @else
                                <span class="badge badge-warning ml-2">
                                    <i class="fas fa-clock"></i> Selección Incompleta
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
                
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
                            <div class="alert alert-info">
                                No hay cotizaciones disponibles para esta solicitud.
                            </div>
                        </div>
                    @endforelse
                </div>
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
        // Configurar el modal de pre-aprobación
        $('#preApproveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var quotationId = button.data('quotation-id');
            var provider = button.data('provider');
            
            var modal = $(this);
            modal.find('#quotation_id').val(quotationId);
            modal.find('#provider-name').text(provider);
        });
    });
</script>
@stop