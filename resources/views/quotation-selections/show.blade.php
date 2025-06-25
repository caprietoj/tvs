@extends('adminlte::page')

@section('title', 'Selección Mixta de Proveedores')

@section('adminlte_css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="color: #364E76;">
                <i class="fas fa-balance-scale mr-2"></i>Selección Mixta de Proveedores
            </h1>
            <p class="text-muted">Solicitud #{{ $purchaseRequest->request_number }}</p>
        </div>
        <div>
            <a href="{{ route('approvals.show', $purchaseRequest->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Campo oculto para JavaScript -->
    <input type="hidden" name="purchase_request_id" value="{{ $purchaseRequest->id }}">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Información de la solicitud -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Información de la Solicitud</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Solicitante:</strong><br>
                    {{ $purchaseRequest->requester }}
                </div>
                <div class="col-md-3">
                    <strong>Área/Sección:</strong><br>
                    {{ $purchaseRequest->section_area }}
                </div>
                <div class="col-md-3">
                    <strong>Fecha de solicitud:</strong><br>
                    {{ $purchaseRequest->request_date->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Total de cotizaciones:</strong><br>
                    <span class="badge badge-info">{{ $quotations->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de cotizaciones disponibles -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #364E76;">
            <h5 class="mb-0" style="color: #364E76;">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Cotizaciones Disponibles
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        @foreach($quotations as $index => $quotation)
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-primary">
                                                    <i class="fas fa-building text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Cotización {{ $index + 1 }}
                                                </div>
                                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                    {{ $quotation->provider_name }}
                                                </div>
                                                <div class="h6 mb-0 text-success">
                                                    ${{ number_format($quotation->total_amount, 2, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0">Comparación de Cotizaciones</h6>
                        </div>
                        <div class="card-body">
                            @if(count($quotations) >= 2)
                                <div class="chart-container" style="position: relative; height:250px;">
                                    <canvas id="quotationChart"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                    <p class="mb-0">Se necesitan al menos 2 cotizaciones para mostrar la comparación</p>
                                    <small>Actualmente hay {{ count($quotations) }} cotización(es)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selección de items -->
    <div class="card">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h5 class="mb-0">
                <i class="fas fa-shopping-cart mr-2"></i>Selección de Items por Proveedor
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 30%;">Descripción del Item</th>
                            <th style="width: 10%;">Cantidad</th>
                            <th style="width: 25%;">Proveedor Seleccionado</th>
                            <th style="width: 15%;">Precio Unitario</th>
                            <th style="width: 15%;">Total</th>
                            <th style="width: 10%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseItems as $index => $item)
                            <tr id="item-row-{{ $index }}">
                                <td class="align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle">
                                    <strong>{{ $item['description'] ?? $item['name'] ?? 'Sin descripción' }}</strong>
                                    @if(isset($item['specification']))
                                        <br><small class="text-muted">{{ $item['specification'] }}</small>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-secondary">{{ $item['quantity'] ?? 1 }}</span>
                                </td>
                                <td class="align-middle">
                                    @if(isset($existingSelections[$index]))
                                        @php $selection = $existingSelections[$index]; @endphp
                                        <div class="selected-provider" id="selected-{{ $index }}">
                                            <strong class="text-success">{{ $selection->quotation->provider_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Seleccionado por {{ $selection->selectedBy->name }}
                                                <br>{{ $selection->selected_at->format('d/m/Y H:i') }}
                                            </small>
                                            @if($selection->justification)
                                                <br><small class="text-info">
                                                    <i class="fas fa-comment"></i> {{ $selection->justification }}
                                                </small>
                                            @endif
                                        </div>
                                    @else
                                        <div class="selection-area" id="selection-{{ $index }}">
                                            <button type="button" class="btn btn-primary btn-sm select-provider-btn" 
                                                    data-item-index="{{ $index }}"
                                                    data-item-description="{{ $item['description'] ?? $item['name'] ?? 'Sin descripción' }}"
                                                    data-item-quantity="{{ $item['quantity'] ?? 1 }}">
                                                <i class="fas fa-hand-pointer"></i> Seleccionar Proveedor
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span id="unit-price-{{ $index }}">
                                        @if(isset($existingSelections[$index]))
                                            ${{ number_format($existingSelections[$index]->unit_price, 2, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span id="total-price-{{ $index }}">
                                        @if(isset($existingSelections[$index]))
                                            <strong class="text-success">
                                                ${{ number_format($existingSelections[$index]->total_price, 2, ',', '.') }}
                                            </strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if(isset($existingSelections[$index]))
                                        <button type="button" class="btn btn-warning btn-sm change-selection-btn"
                                                data-item-index="{{ $index }}"
                                                data-item-description="{{ $item['description'] ?? $item['name'] ?? 'Sin descripción' }}"
                                                data-item-quantity="{{ $item['quantity'] ?? 1 }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm remove-selection-btn"
                                                data-item-index="{{ $index }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f8f9fa;">
                        <tr>
                            <th colspan="5" class="text-right">Total Seleccionado:</th>
                            <th class="text-center">
                                <strong class="text-success" id="grand-total">
                                    ${{ number_format($existingSelections->sum('total_price'), 2, ',', '.') }}
                                </strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($existingSelections->count() === count($purchaseItems))
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Selección completa.</strong> Todos los items tienen un proveedor asignado.
                </div>
                
                <div class="text-center mt-3">
                    <form action="{{ route('quotation-selections.finalize', $purchaseRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-double mr-2"></i>Finalizar Selección Mixta
                        </button>
                    </form>
                </div>
            @else
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Selección incompleta.</strong> 
                    Debe seleccionar un proveedor para todos los items antes de finalizar.
                    Progreso: {{ $existingSelections->count() }}/{{ count($purchaseItems) }} items
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para selección de proveedor -->
<div class="modal fade" id="providerSelectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-hand-pointer mr-2"></i>Seleccionar Proveedor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6><strong>Item:</strong> <span id="modal-item-description"></span></h6>
                    <p><strong>Cantidad:</strong> <span id="modal-item-quantity"></span></p>
                </div>
                
                <form id="selectionForm">
                    @csrf
                    <input type="hidden" id="modal-purchase-request-id" value="{{ $purchaseRequest->id }}">
                    <input type="hidden" id="modal-item-index">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Seleccionar</th>
                                    <th>Proveedor</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotations as $quotation)
                                    <tr>
                                        <td class="text-center">
                                            <div class="form-check">
                                                <input class="form-check-input quotation-radio" 
                                                       type="radio" 
                                                       name="quotation_id" 
                                                       value="{{ $quotation->id }}"
                                                       id="quotation-{{ $quotation->id }}">
                                                <label class="form-check-label" for="quotation-{{ $quotation->id }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $quotation->provider_name }}</strong>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" 
                                                       class="form-control unit-price-input" 
                                                       step="0.01" 
                                                       min="0"
                                                       data-quotation-id="{{ $quotation->id }}"
                                                       placeholder="0.00">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <strong class="total-display" data-quotation-id="{{ $quotation->id }}">
                                                $0.00
                                            </strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-group">
                        <label for="justification">Justificación (opcional):</label>
                        <textarea class="form-control" 
                                  id="justification" 
                                  name="justification" 
                                  rows="3" 
                                  placeholder="Explique por qué seleccionó este proveedor para este item..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveSelectionBtn">
                    <i class="fas fa-save mr-2"></i>Guardar Selección
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary {
        background-color: #364E76 !important;
    }
    
    .border-left-primary {
        border-left: 0.25rem solid #364E76 !important;
    }
    
    .selected-provider {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #28a745;
    }
    
    .table th {
        border-top: none;
    }
    
    .btn-sm {
        margin: 0 2px;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
    }
    
    #quotationChart {
        max-height: 250px;
    }
    
    @media (max-width: 768px) {
        .chart-container {
            height: 200px;
        }
        
        #quotationChart {
            max-height: 200px;
        }
        
        .col-md-8, .col-md-4 {
            margin-bottom: 20px;
        }
    }
    
    .quotation-card {
        transition: all 0.3s ease;
        cursor: default;
    }
    
    .quotation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('js')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JavaScript externo -->
<script src="{{ asset('js/quotation-chart.js') }}"></script>
@stop
