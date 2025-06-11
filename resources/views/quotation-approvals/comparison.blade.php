@extends('adminlte::page')

@section('title', 'Comparación de Cotizaciones')

@section('content_header')
    <h1 class="m-0 text-dark">Comparación de Cotizaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">Solicitud #{{ $request->request_number }}</h3>
                <div>
                    <a href="{{ route('quotation-approvals.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-list"></i> Volver al listado
                    </a>
                    <a href="{{ route('quotation-approvals.show', $request->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Ver detalles
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Información de la solicitud -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> Información de la Solicitud</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Número:</strong> {{ $request->request_number }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Solicitante:</strong> {{ $request->user->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Área/Sección:</strong> {{ $request->section_area }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Fecha:</strong> {{ $request->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>Descripción:</strong> {{ $request->description ?? 'No hay descripción disponible' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($request->quotations->count() > 0)
                    <!-- Gráfico comparativo -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Comparación Gráfica</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="comparisonChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla comparativa -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Comparativa Detallada</h3>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Verificar si hay alguna cotización seleccionada en esta solicitud
                                        $hasSelectedQuotation = $request->quotations->contains('is_selected', true);
                                        // Verificar si la solicitud está pre-aprobada
                                        $isPreapproved = in_array($request->status, ['Pre-aprobada', 'pre-approved']);
                                        // Determinar si se deben bloquear todas las cotizaciones
                                        $blockAllQuotations = $hasSelectedQuotation || $isPreapproved;
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Criterio</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <th>
                                                            {{ $quotation->provider_name }}
                                                            @if($quotation->is_selected)
                                                                <span class="badge badge-success ml-1">Pre-aprobada</span>
                                                            @endif
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th>Monto Total</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>
                                                            <strong>${{ number_format($quotation->total_amount, 0, ',', '.') }}</strong>
                                                            @php
                                                                $lowestAmount = $request->quotations->min('total_amount');
                                                            @endphp
                                                            @if($quotation->total_amount == $lowestAmount)
                                                                <span class="badge badge-success ml-2">Mejor precio</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Tiempo de Entrega</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>{{ $quotation->delivery_time ?? 'No especificado' }}</td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Forma de Pago</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>{{ $quotation->payment_method ?? 'No especificada' }}</td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Validez de la Oferta</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>{{ $quotation->validity ?? 'No especificada' }}</td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Garantía</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>{{ $quotation->warranty ?? 'No especificada' }}</td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Cotización</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>
                                                            @if($quotation->file_path)
                                                                <a href="{{ Storage::url($quotation->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-file-pdf"></i> Ver PDF
                                                                </a>
                                                            @else
                                                                <span class="text-muted">No disponible</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th>Acción</th>
                                                    @foreach($request->quotations as $quotation)
                                                        <td>
                                                            @if($quotation->is_selected)
                                                                <button class="btn btn-success btn-block" disabled>
                                                                    <i class="fas fa-check-double"></i> Cotización Pre-aprobada
                                                                </button>
                                                            @elseif($blockAllQuotations)
                                                                <button class="btn btn-secondary btn-block" disabled>
                                                                    <i class="fas fa-lock"></i> Ya existe una cotización pre-aprobada
                                                                </button>
                                                            @else
                                                                <button class="btn btn-success btn-block" data-toggle="modal" data-target="#preApproveModal" 
                                                                    data-quotation-id="{{ $quotation->id }}" data-provider="{{ $quotation->provider_name }}">
                                                                    <i class="fas fa-check-circle"></i> Pre-aprobar
                                                                </button>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay cotizaciones disponibles para comparar.
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
        .callout {
            padding: 15px;
            border-left: 5px solid #6c757d;
            border-radius: 3px;
            background-color: #f8f9fa;
            margin-bottom: 20px;
        }
        .callout-info {
            border-left-color: #17a2b8;
        }
        .callout h5 {
            margin-top: 0;
            margin-bottom: 15px;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Crear gráfico de comparación
        var ctx = document.getElementById('comparisonChart');
        if (ctx) {
            var providers = @json($request->quotations->pluck('provider_name'));
            var amounts = @json($request->quotations->pluck('total_amount'));
            
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: providers,
                    datasets: [{
                        label: 'Monto Total ($)',
                        data: amounts,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-CL');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.raw !== null) {
                                        label += '$' + context.raw.toLocaleString('es-CL');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@stop