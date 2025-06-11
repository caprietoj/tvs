@extends('adminlte::page')

@section('title', 'Detalles de Solicitud Pre-aprobada')

@section('content_header')
<h1>Detalles de Solicitud Pre-aprobada</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información de la Solicitud #{{ $request->id }}</h3>
                <div class="card-tools">
                    <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-muted">Información General</h5>
                        <dl class="row">
                            <dt class="col-sm-5">Tipo:</dt>
                            <dd class="col-sm-7">
                                <span class="badge {{ $request->type === 'purchase' ? 'badge-primary' : 'badge-info' }}">
                                    {{ $request->type === 'purchase' ? 'Compra' : 'Materiales' }}
                                </span>
                            </dd>

                            <dt class="col-sm-5">Solicitante:</dt>
                            <dd class="col-sm-7">{{ $request->requester }}</dd>

                            <dt class="col-sm-5">Área/Sección:</dt>
                            <dd class="col-sm-7">{{ $request->section_area }}</dd>

                            <dt class="col-sm-5">Usuario:</dt>
                            <dd class="col-sm-7">{{ $request->user ? $request->user->name : 'N/A' }}</dd>

                            <dt class="col-sm-5">Fecha de solicitud:</dt>
                            <dd class="col-sm-7">{{ $request->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-muted">Información de Pre-aprobación</h5>
                        <dl class="row">
                            <dt class="col-sm-5">Pre-aprobada por:</dt>
                            <dd class="col-sm-7">{{ $request->preApprover ? $request->preApprover->name : 'N/A' }}</dd>

                            <dt class="col-sm-5">Fecha de pre-aprobación:</dt>
                            <dd class="col-sm-7">{{ $request->pre_approved_at ? $request->pre_approved_at->format('d/m/Y H:i') : 'N/A' }}</dd>

                            <dt class="col-sm-5">Cotización seleccionada:</dt>
                            <dd class="col-sm-7">{{ $request->preApprovedQuotation ? $request->preApprovedQuotation->provider_name : 'N/A' }}</dd>

                            <dt class="col-sm-5">Monto:</dt>
                            <dd class="col-sm-7">
                                @if($request->preApprovedQuotation)
                                    ${{ number_format($request->preApprovedQuotation->total_amount, 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </dd>

                            <dt class="col-sm-5">Comentarios:</dt>
                            <dd class="col-sm-7">{{ $request->pre_approval_comments ?? 'Sin comentarios' }}</dd>

                            <dt class="col-sm-5">Presupuesto asignado:</dt>
                            <dd class="col-sm-7">
                                <div class="d-flex align-items-center">
                                    <div id="budget-display" class="mr-2">
                                        @if($request->budget)
                                            <span class="badge badge-info p-2">{{ $request->budget }}</span>
                                        @else
                                            <span class="text-muted">No especificado</span>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBudget()">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </div>
                                
                                <!-- Formulario de edición oculto -->
                                <div id="budget-edit-form" style="display: none;" class="mt-2">
                                    <form action="{{ route('approvals.update-budget', $request->id) }}" method="POST" class="d-flex align-items-center">
                                        @csrf
                                        <input type="text" 
                                               name="budget" 
                                               value="{{ $request->budget ?? '' }}" 
                                               class="form-control form-control-sm mr-2" 
                                               placeholder="Especificar presupuesto"
                                               required>
                                        <button type="submit" class="btn btn-sm btn-success mr-1">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditBudget()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>

                @if($request->type === 'purchase')
                    <h5 class="text-muted mt-4">Detalles de la Solicitud</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $items = is_array($request->purchase_items) ? $request->purchase_items : json_decode($request->purchase_items, true);
                                @endphp
                                @forelse($items ?? [] as $index => $item)
                                    <tr>
                                        <td>{{ $item['item'] ?? $item->item ?? 'N/A' }}</td>
                                        <td>{{ $item['description'] ?? $item->description ?? 'N/A' }}</td>
                                        <td>{{ $item['quantity'] ?? $item->quantity ?? 'N/A' }}</td>
                                        <td>{{ $item['unit'] ?? $item->unit ?? 'N/A' }}</td>
                                        <td>{{ $item['observations'] ?? $item->observations ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay items para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($request->service_items)
                        <h5 class="text-muted mt-4">Servicios</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $services = is_array($request->service_items) ? $request->service_items : json_decode($request->service_items, true);
                                    @endphp
                                    @forelse($services ?? [] as $index => $service)
                                        <tr>
                                            <td>{{ $service['item'] ?? $service->item ?? 'N/A' }}</td>
                                            <td>{{ $service['description'] ?? $service->description ?? 'N/A' }}</td>
                                            <td>{{ $service['quantity'] ?? $service->quantity ?? 'N/A' }}</td>
                                            <td>{{ $service['observations'] ?? $service->observations ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No hay servicios para mostrar</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif

                @if($request->preApprovedQuotation)
                    <h5 class="text-muted mt-4">Cotización Pre-aprobada</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Monto total</th>
                                    <th>Archivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $request->preApprovedQuotation->provider_name }}</td>
                                    <td>${{ number_format($request->preApprovedQuotation->total_amount, 2, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('quotations.download', $request->preApprovedQuotation->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <h5 class="text-muted mt-4">Acciones</h5>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#approveModal">
                            <i class="fas fa-check"></i> Aprobar solicitud
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Rechazar solicitud
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de la Solicitud</h3>
            </div>
            <div class="card-body p-0">
                <div class="timeline timeline-inverse p-3">
                    @forelse($request->history()->orderBy('created_at', 'desc')->get() as $history)
                        <div class="time-label">
                            <span class="bg-primary">
                                {{ $history->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-history bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $history->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header no-border">{{ $history->action }}</h3>
                                <div class="timeline-body">
                                    <p><strong>Por:</strong> {{ $history->user->name }}</p>
                                    <p>{{ $history->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="timeline-item">
                            <div class="timeline-body">
                                No hay historial disponible
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aprobación -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('approvals.approve', $request->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Aprobar Solicitud</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea aprobar definitivamente esta solicitud?</p>
                    <div class="form-group">
                        <label for="comments">Comentarios (opcional)</label>
                        <textarea name="comments" id="comments" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprobar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Rechazo -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('approvals.reject', $request->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Solicitud</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea rechazar esta solicitud?</p>
                    <div class="form-group">
                        <label for="rejection_reason">Motivo del rechazo <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="3" required></textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
    }
    .timeline-header {
        font-weight: bold;
        font-size: 16px;
    }
    .timeline-body p {
        margin-bottom: 5px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
    
    function editBudget() {
        document.getElementById('budget-display').style.display = 'none';
        document.getElementById('budget-edit-form').style.display = 'block';
        // Enfocar el input
        document.querySelector('#budget-edit-form input[name="budget"]').focus();
    }
    
    function cancelEditBudget() {
        document.getElementById('budget-display').style.display = 'block';
        document.getElementById('budget-edit-form').style.display = 'none';
    }
</script>
@stop