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
                                <p class="info-box-text mt-3">
                                    {{ $request->description ?? 'No hay descripción disponible' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de cotizaciones -->
                <h4 class="mt-4 mb-3"><i class="fas fa-file-invoice-dollar mr-2"></i> Cotizaciones Disponibles</h4>
                
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
                        <input type="text" class="form-control" id="budget" name="budget" required 
                               placeholder="Ej: Presupuesto 2025 - Equipos de cómputo" 
                               maxlength="255">
                        <small class="form-text text-muted">Especifique el presupuesto o partida presupuestal donde se cargará esta compra.</small>
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