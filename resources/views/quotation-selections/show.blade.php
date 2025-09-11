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
    <input type="hidden" id="total-items-count" value="{{ count($purchaseItems) }}">
    
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
            <!-- Campo oculto para JavaScript -->
            <input type="hidden" id="total-items-count" value="{{ count($purchaseItems) }}">
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background: linear-gradient(135deg, #364E76 0%, #4a5d8a 100%); color: white;">
                        <tr>
                            <th style="width: 8%; text-align: center; vertical-align: middle; font-weight: 600;">#</th>
                            <th style="width: 45%; padding: 12px; font-weight: 600;">Descripción del Item</th>
                            <th style="width: 12%; text-align: center; vertical-align: middle; font-weight: 600;">Cantidad</th>
                            <th style="width: 35%; padding: 12px; font-weight: 600;">Seleccionar Proveedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseItems as $index => $item)
                            <tr id="item-row-{{ $index }}" style="transition: all 0.2s ease;">
                                <td class="align-middle text-center" style="font-weight: 600; color: #364E76; font-size: 1.1em;">
                                    {{ $index + 1 }}
                                </td>
                                <td class="align-middle" style="padding: 15px;">
                                    <div>
                                        <strong style="color: #2c3e50; font-size: 1.05em;">{{ $item['description'] ?? $item['name'] ?? 'Sin descripción' }}</strong>
                                        @if(isset($item['specification']))
                                            <br><small class="text-muted" style="font-style: italic; margin-top: 5px; display: block;">{{ $item['specification'] }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle text-center" style="padding: 15px;">
                                    <span class="badge badge-info" style="font-size: 1em; padding: 8px 12px; border-radius: 20px;">
                                        {{ $item['quantity'] ?? 1 }}
                                    </span>
                                </td>
                                <td class="align-middle" style="padding: 15px;">
                                    @if(isset($existingSelections[$index]))
                                        @php $selection = $existingSelections[$index]; @endphp
                                        <div class="provider-selection">
                                            <!-- Select para seleccionar proveedor con precio -->
                                            <select class="form-control provider-select" 
                                                    data-item-index="{{ $index }}" 
                                                    onchange="handleProviderSelectChange(this)">
                                                <option value="">Seleccione un proveedor...</option>
                                                @foreach($quotations as $quotation)
                                                    @php
                                                        $unitPrice = 0;
                                                        $priceSource = 'fallback';
                                                        $hasItemPrice = false;
                                                        
                                                        // Verificar si este proveedor cotizó específicamente este item
                                                        if (isset($quotation->original_item_prices[$index])) {
                                                            $unitPrice = (float)($quotation->original_item_prices[$index] ?? 0);
                                                            $priceSource = 'specific';
                                                            $hasItemPrice = true;
                                                        }
                                                        
                                                        // Solo mostrar este proveedor si cotizó el item específico
                                                        if (!$hasItemPrice) {
                                                            continue; // Saltar este proveedor si no cotizó este item
                                                        }
                                                        
                                                        $totalPrice = (float)($unitPrice ?? 0) * (float)($item['quantity'] ?? 1);
                                                        $isSelected = $selection->quotation_id == $quotation->id;
                                                    @endphp
                                                    <option value="{{ $quotation->id }}" 
                                                            data-provider-name="{{ $quotation->provider_name }}"
                                                            data-unit-price="{{ $unitPrice }}"
                                                            data-total-price="{{ $totalPrice }}"
                                                            {{ $isSelected ? 'selected' : '' }}>
                                                        {{ $quotation->provider_name }} - ${{ number_format((float)($unitPrice ?? 0), 2, ',', '.') }}
                                                        @if($priceSource === 'fallback')
                                                            <small>(est.)</small>
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-success mt-2 d-block" style="font-weight: 500;">
                                                <i class="fas fa-check-circle"></i> Seleccionado por {{ $selection->selectedBy->name }} el {{ $selection->selected_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    @else
                                        <div class="provider-selection">
                                            <!-- Select para seleccionar proveedor con precio -->
                                            <select class="form-control provider-select" 
                                                    data-item-index="{{ $index }}" 
                                                    onchange="handleProviderSelectChange(this)">
                                                <option value="">Seleccione un proveedor...</option>
                                                @foreach($quotations as $quotation)
                                                    @php
                                                        $unitPrice = 0;
                                                        $priceSource = 'fallback';
                                                        $hasItemPrice = false;
                                                        
                                                        // Verificar si este proveedor cotizó específicamente este item
                                                        if (isset($quotation->original_item_prices[$index])) {
                                                            $unitPrice = (float)($quotation->original_item_prices[$index] ?? 0);
                                                            $priceSource = 'specific';
                                                            $hasItemPrice = true;
                                                        }
                                                        
                                                        // Solo mostrar este proveedor si cotizó el item específico
                                                        if (!$hasItemPrice) {
                                                            continue; // Saltar este proveedor si no cotizó este item
                                                        }
                                                        
                                                        $totalPrice = (float)($unitPrice ?? 0) * (float)($item['quantity'] ?? 1);
                                                    @endphp
                                                    <option value="{{ $quotation->id }}" 
                                                            data-provider-name="{{ $quotation->provider_name }}"
                                                            data-unit-price="{{ $unitPrice }}"
                                                            data-total-price="{{ $totalPrice }}">
                                                        {{ $quotation->provider_name }} - ${{ number_format((float)($unitPrice ?? 0), 2, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($existingSelections->count() === count($purchaseItems))
                <!-- Elementos estáticos ocultos inicialmente para evitar conflictos -->
                <div class="alert alert-success mt-3" id="complete-selection-alert" style="display: none;">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Selección completa.</strong> Todos los items tienen un proveedor asignado.
                </div>
                
                <div class="text-center mt-3" id="complete-buttons" style="display: none;">
                    <form action="{{ route('quotation-selections.save-and-send', $purchaseRequest) }}" method="POST" style="display: inline;" id="save-and-send-form">
                        @csrf
                        <button type="button" class="btn btn-primary btn-lg" onclick="showPreapprovalConfirmModal()">
                            <i class="fas fa-save mr-2"></i>Guardar y Enviar
                        </button>
                    </form>
                </div>
            @elseif($existingSelections->count() > 0)
                <!-- Elementos estáticos ocultos inicialmente para evitar conflictos -->
                <div class="alert alert-info mt-3" id="partial-selection-alert" style="display: none;">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Selección parcial.</strong> 
                    Ha seleccionado <span id="selected-count">{{ $existingSelections->count() }}</span>/<span id="total-count">{{ count($purchaseItems) }}</span> items.
                    Puede guardar y enviar el progreso actual o continuar seleccionando.
                </div>
                
                <div class="text-center mt-3" id="partial-buttons" style="display: none;">
                    <form action="{{ route('quotation-selections.save-and-send', $purchaseRequest) }}" method="POST" style="display: inline;" id="partial-save-and-send-form">
                        @csrf
                        <button type="button" class="btn btn-primary btn-lg" onclick="showPreapprovalConfirmModal()">
                            <i class="fas fa-save mr-2"></i>Guardar y Enviar
                        </button>
                    </form>
                </div>
            @else
                <!-- Elementos estáticos ocultos inicialmente para evitar conflictos -->
                <div class="alert alert-warning mt-3" id="no-selection-alert" style="display: none;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Selección incompleta.</strong> 
                    Debe seleccionar un proveedor para todos los items antes de finalizar.
                    Progreso: <span id="progress-count">{{ $existingSelections->count() }}</span>/<span id="progress-total">{{ count($purchaseItems) }}</span> items
                </div>
            @endif

            <!-- Contenedores dinámicos que se mostrarán/ocultarán con JavaScript -->
            <div id="dynamic-complete-alert" class="alert alert-success mt-3" style="display: none;">
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Selección completa.</strong> Todos los items tienen un proveedor asignado.
            </div>
            
            <div id="dynamic-complete-buttons" class="text-center mt-3" style="display: none;">
                <form action="{{ route('quotation-selections.save-and-send', $purchaseRequest) }}" method="POST" style="display: inline;" id="dynamic-save-and-send-form">
                    @csrf
                    <button type="button" class="btn btn-primary btn-lg" onclick="showPreapprovalConfirmModal()">
                        <i class="fas fa-save mr-2"></i>Guardar y Enviar
                    </button>
                </form>
            </div>

            <div id="dynamic-partial-alert" class="alert alert-info mt-3" style="display: none;">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Selección parcial.</strong> 
                Ha seleccionado <span id="dynamic-selected-count">0</span>/<span id="dynamic-total-count">{{ count($purchaseItems) }}</span> items.
                Puede guardar y enviar el progreso actual o continuar seleccionando.
            </div>
            
            <div id="dynamic-partial-buttons" class="text-center mt-3" style="display: none;">
                <form action="{{ route('quotation-selections.save-and-send', $purchaseRequest) }}" method="POST" style="display: inline;" id="dynamic-partial-save-and-send-form">
                    @csrf
                    <button type="button" class="btn btn-primary btn-lg" onclick="showPreapprovalConfirmModal()">
                        <i class="fas fa-save mr-2"></i>Guardar y Enviar
                    </button>
                </form>
            </div>

            <div id="dynamic-no-selection-alert" class="alert alert-warning mt-3" style="display: none;">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Sin selecciones.</strong> 
                Debe seleccionar al menos un proveedor para comenzar.
                Progreso: <span id="dynamic-progress-count">0</span>/<span id="dynamic-progress-total">{{ count($purchaseItems) }}</span> items
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para pre-aprobación -->
<div class="modal fade" id="preapprovalConfirmModal" tabindex="-1" role="dialog" aria-labelledby="preapprovalConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title" id="preapprovalConfirmModalLabel">
                    <i class="fas fa-paper-plane mr-2"></i>Confirmar Envío a Pre-aprobación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>¿Está seguro de que desea enviar esta selección a pre-aprobación?</strong>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Solicitud</h6>
                                <p class="mb-0"><strong>#{{ $purchaseRequest->request_number }}</strong></p>
                                <small class="text-muted">{{ $purchaseRequest->requester }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Items Seleccionados</h6>
                                <p class="mb-0"><span id="modal-selected-count">0</span>/<span id="modal-total-count">{{ count($purchaseItems) }}</span></p>
                                <small class="text-muted">proveedores asignados</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="preapproval-comments">
                        <i class="fas fa-comment mr-2"></i>Comentarios adicionales (opcional):
                    </label>
                    <textarea class="form-control" 
                              id="preapproval-comments" 
                              name="comments" 
                              rows="3" 
                              placeholder="Agregue cualquier comentario o justificación para las selecciones realizadas..."></textarea>
                    <small class="form-text text-muted">
                        Estos comentarios serán visibles para el aprobador.
                    </small>
                </div>
                
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="confirm-send-checkbox">
                    <label class="custom-control-label" for="confirm-send-checkbox">
                        Confirmo que he revisado todas las selecciones y deseo enviar a pre-aprobación
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmSendBtn" disabled>
                    <i class="fas fa-paper-plane mr-2"></i>Enviar a Pre-aprobación
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
    
    /* Estilos para las cards de proveedores */
    .provider-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    /* Estilos para los selects de proveedores */
    .provider-select {
        border: 2px solid #e3e6f0;
        border-radius: 6px;
        font-size: 0.95em;
        font-weight: 500;
        padding: 8px 12px;
        transition: all 0.3s ease;
        background-color: white;
        color: #495057;
    }
    
    .provider-select:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        outline: none;
    }
    
    .provider-select:hover {
        border-color: #364E76;
    }
    
    .provider-select option {
        padding: 8px;
        font-weight: 500;
    }
    
    .provider-select option:checked {
        background-color: #364E76;
        color: white;
    }
    
    /* Estilos para las tarjetas de proveedores (mantenidos para compatibilidad) */
    .provider-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        min-height: 50px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .provider-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(54, 78, 118, 0.15);
        border-color: #364E76;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .provider-card.selected {
        background: linear-gradient(135deg, #364E76 0%, #4a5d8a 100%);
        border-color: #364E76;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(54, 78, 118, 0.3);
    }
    
    .provider-card.selected:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(54, 78, 118, 0.4);
    }
    
    .provider-name {
        font-weight: 600;
        font-size: 0.95em;
        flex: 1;
        margin-right: 10px;
        line-height: 1.3;
    }
    
    .provider-check {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        background: white;
        flex-shrink: 0;
    }
    
    .provider-card:hover .provider-check {
        border-color: #364E76;
        background: #f8f9fa;
    }
    
    .provider-card.selected .provider-check {
        background: white;
        border-color: white;
        color: #364E76;
    }
    
    .checkmark {
        font-size: 14px;
        font-weight: bold;
        opacity: 0;
        transform: scale(0);
        transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    .provider-card.selected .checkmark {
        opacity: 1;
        transform: scale(1);
    }
    
    /* Animación de selección */
    @keyframes selectAnimation {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .provider-card.selecting {
        animation: selectAnimation 0.3s ease;
    }
    
    /* Responsivo para pantallas pequeñas */
    @media (max-width: 768px) {
        .provider-card {
            padding: 10px 12px;
            min-height: 45px;
        }
        
        .provider-name {
            font-size: 0.9em;
        }
        
        .provider-check {
            width: 20px;
            height: 20px;
        }
    }
    
    .table th {
        border-top: none;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .table td {
        border-top: 1px solid #e3e6f0;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(54, 78, 118, 0.05);
        transform: scale(1.01);
        transition: all 0.2s ease;
    }
    
    .badge-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    }
    
    .selection-confirmation {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
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

<!-- JavaScript simplificado para selección directa de proveedores -->
<script>
console.log('Inicializando selección directa de proveedores...');

// Función principal para seleccionar proveedor desde las cards (LEGACY - mantenida para compatibilidad)
function selectProvider(cardElement) {
    console.log('selectProvider (legacy) - Esta función ya no se usa con los selects');
    // Esta función se mantiene por compatibilidad pero ya no se usa
    // El nuevo sistema usa handleProviderSelectChange() para los selects
}

// Función para manejar el cambio de proveedor en los selects
function handleProviderSelectChange(selectElement) {
    console.log('handleProviderSelectChange llamado');
    
    const selectedOption = selectElement.selectedOptions[0];
    const itemIndex = selectElement.getAttribute('data-item-index');
    
    if (!selectedOption || !selectedOption.value) {
        console.log('No hay opción seleccionada o valor vacío');
        return;
    }
    
    const quotationId = selectedOption.value;
    const providerName = selectedOption.getAttribute('data-provider-name');
    const unitPrice = selectedOption.getAttribute('data-unit-price');
    const totalPrice = selectedOption.getAttribute('data-total-price');
    
    console.log('Datos extraídos del select:', {
        itemIndex: itemIndex,
        quotationId: quotationId,
        providerName: providerName,
        unitPrice: unitPrice,
        totalPrice: totalPrice
    });
    
    // Guardar la selección automáticamente
    saveProviderSelection(itemIndex, quotationId, unitPrice, providerName);
}

// Función para manejar el cambio de proveedor en los selects (función legacy mantenida para compatibilidad)
function handleProviderChange(selectElement) {
    console.log('handleProviderChange (legacy) redirigiendo a nueva función');
    handleProviderSelectChange(selectElement);
}

// Función para guardar la selección del proveedor
function saveProviderSelection(itemIndex, quotationId, price, providerName) {
    console.log('Iniciando saveProviderSelection con datos:', {
        itemIndex: itemIndex,
        quotationId: quotationId,
        price: price,
        priceType: typeof price,
        providerName: providerName
    });
    
    // Convertir price a número si es string
    let numericPrice = price;
    if (typeof price === 'string') {
        numericPrice = parseFloat(price);
    }
    
    console.log('Precio después de conversión:', {
        original: price,
        converted: numericPrice,
        isNaN: isNaN(numericPrice)
    });
    
    // Validar datos antes de enviar
    if (!quotationId) {
        console.error('quotationId está vacío:', quotationId);
        alert('Error: ID de cotización no válido.');
        return;
    }
    
    if (!price && price !== 0) {
        console.error('price está vacío o undefined:', price);
        alert('Error: Precio no encontrado. Verifique que la cotización tenga precios definidos.');
        return;
    }
    
    if (isNaN(numericPrice) || numericPrice < 0) {
        console.error('price no es un número válido:', {
            price: price,
            numericPrice: numericPrice,
            isNaN: isNaN(numericPrice)
        });
        alert('Error: Precio no válido (' + price + '). Por favor, recargue la página e intente nuevamente.');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('item_index', itemIndex);
    formData.append('quotation_id', quotationId);
    formData.append('unit_price', numericPrice);
    
    console.log('Enviando selección:', {
        itemIndex: itemIndex,
        quotationId: quotationId,
        price: numericPrice,
        providerName: providerName,
        url: '{{ route("quotation-selections.save-selection", $purchaseRequest->id) }}'
    });
    
    fetch('{{ route("quotation-selections.save-selection", $purchaseRequest->id) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status, response.statusText);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        
        if (data.success) {
            console.log('Selección guardada exitosamente');
            
            // Mostrar confirmación en la card seleccionada
            const selectedCard = document.querySelector(`.provider-card[data-item-index="${itemIndex}"][data-quotation-id="${quotationId}"]`);
            if (selectedCard) {
                // Agregar mensaje de confirmación a la card
                let confirmMessage = selectedCard.querySelector('.selection-confirmation');
                if (!confirmMessage) {
                    confirmMessage = document.createElement('div');
                    confirmMessage.className = 'selection-confirmation';
                    confirmMessage.innerHTML = '<i class="fas fa-check-circle"></i> Guardado';
                    selectedCard.appendChild(confirmMessage);
                }
                
                // Hacer el mensaje visible temporalmente
                confirmMessage.style.display = 'block';
                setTimeout(() => {
                    confirmMessage.style.display = 'none';
                }, 3000);
            }
            
            // Actualizar contadores y botones
            updateSelectionCounts();
        } else {
            console.error('Error al guardar selección:', data.message);
            alert('Error al guardar la selección: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        console.error('Detalles del error:', error.message);
        alert('Error de conexión al guardar la selección. Detalles: ' + error.message);
    });
}

// Función para actualizar contadores usando los selects
function updateSelectionCounts() {
    const totalItems = {{ count($purchaseItems) }};
    
    // Contar selects con valores seleccionados
    const selectedSelects = document.querySelectorAll('.provider-select');
    let selectedCount = 0;
    
    selectedSelects.forEach(select => {
        if (select.value && select.value !== '') {
            selectedCount++;
        }
    });
    
    console.log(`Selecciones actuales: ${selectedCount}/${totalItems}`);
    
    // Ocultar todos los elementos dinámicos primero
    const dynamicElements = [
        'dynamic-complete-alert',
        'dynamic-complete-buttons', 
        'dynamic-partial-alert',
        'dynamic-partial-buttons',
        'dynamic-no-selection-alert'
    ];
    
    dynamicElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    });
    
    // Mostrar elementos apropiados según el estado
    if (selectedCount === totalItems) {
        // Selección completa
        const completeAlert = document.getElementById('dynamic-complete-alert');
        const completeButtons = document.getElementById('dynamic-complete-buttons');
        if (completeAlert) completeAlert.style.display = 'block';
        if (completeButtons) completeButtons.style.display = 'block';
    } else if (selectedCount > 0) {
        // Selección parcial
        const partialAlert = document.getElementById('dynamic-partial-alert');
        const partialButtons = document.getElementById('dynamic-partial-buttons');
        if (partialAlert) partialAlert.style.display = 'block';
        if (partialButtons) partialButtons.style.display = 'block';
    } else {
        // Sin selecciones
        const noSelectionAlert = document.getElementById('dynamic-no-selection-alert');
        if (noSelectionAlert) noSelectionAlert.style.display = 'block';
    }
}

// Función para enviar a pre-aprobación (mantenemos para compatibilidad con botones existentes)
window.confirmSaveAndSend = function() {
    const activeForm = document.querySelector('#save-and-send-form') ||
                       document.querySelector('#partial-save-and-send-form') ||
                       document.querySelector('#dynamic-save-and-send-form') ||
                       document.querySelector('#dynamic-partial-save-and-send-form');
    
    if (activeForm) {
        console.log('Enviando formulario:', activeForm.id);
        activeForm.submit();
    } else {
        console.error('No se encontró formulario activo para enviar');
        alert('Error: No se pudo encontrar el formulario. Por favor, recargue la página e intente nuevamente.');
    }
};

// Función para mostrar el modal de confirmación de pre-aprobación
function showPreapprovalConfirmModal() {
    console.log('Mostrando modal de confirmación de pre-aprobación');
    
    // Actualizar contadores en el modal
    const totalItems = {{ count($purchaseItems) }};
    let selectedCount = 0;
    
    document.querySelectorAll('.provider-select').forEach(select => {
        if (select.value && select.value !== '') {
            selectedCount++;
        }
    });
    
    // Actualizar contadores en el modal
    const modalSelectedCount = document.getElementById('modal-selected-count');
    const modalTotalCount = document.getElementById('modal-total-count');
    if (modalSelectedCount) modalSelectedCount.textContent = selectedCount;
    if (modalTotalCount) modalTotalCount.textContent = totalItems;
    
    // Mostrar el modal
    $('#preapprovalConfirmModal').modal('show');
}

// Función para confirmar y enviar
function confirmPreapprovalSend() {
    const comments = document.getElementById('preapproval-comments').value;
    
    // Buscar el formulario activo
    const activeForm = document.querySelector('#save-and-send-form') ||
                       document.querySelector('#partial-save-and-send-form') ||
                       document.querySelector('#dynamic-save-and-send-form') ||
                       document.querySelector('#dynamic-partial-save-and-send-form');
    
    if (activeForm) {
        // Agregar comentarios al formulario si hay
        if (comments.trim()) {
            const commentsInput = document.createElement('input');
            commentsInput.type = 'hidden';
            commentsInput.name = 'comments';
            commentsInput.value = comments.trim();
            activeForm.appendChild(commentsInput);
        }
        
        console.log('Enviando formulario con comentarios:', comments);
        
        // Deshabilitar el botón y mostrar loading
        const confirmBtn = document.getElementById('confirmSendBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        }
        
        // Enviar el formulario
        activeForm.submit();
    } else {
        console.error('No se encontró formulario activo para enviar');
        alert('Error: No se pudo encontrar el formulario. Por favor, recargue la página e intente nuevamente.');
    }
}

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    console.log('DOM listo, configurando eventos para selects de proveedores...');
    
    // Agregar eventos change a todos los selects de proveedores
    document.querySelectorAll('.provider-select').forEach(select => {
        select.addEventListener('change', function() {
            handleProviderSelectChange(this);
        });
    });
    
    // Contar selecciones existentes basándose en los selects con valores
    let existingSelectionsCount = 0;
    document.querySelectorAll('.provider-select').forEach(select => {
        if (select.value && select.value !== '') {
            existingSelectionsCount++;
            console.log('Select preseleccionado encontrado:', select.value);
        }
    });
    
    console.log('Total de selecciones preexistentes:', existingSelectionsCount);
    
    // Actualizar contadores iniciales
    updateSelectionCounts();
    
    console.log('Sistema de selects de proveedores configurado correctamente');
    
    // === MANEJO DEL MODAL DE CONFIRMACIÓN ===
    
    // Habilitar/deshabilitar botón de confirmar según checkbox
    $('#confirm-send-checkbox').on('change', function() {
        const confirmBtn = document.getElementById('confirmSendBtn');
        if (confirmBtn) {
            confirmBtn.disabled = !this.checked;
        }
    });
    
    // Manejar clic en el botón de confirmar envío
    $('#confirmSendBtn').on('click', function() {
        if ($('#confirm-send-checkbox').is(':checked')) {
            confirmPreapprovalSend();
        }
    });
    
    // Limpiar modal al cerrarlo
    $('#preapprovalConfirmModal').on('hidden.bs.modal', function() {
        $('#preapproval-comments').val('');
        $('#confirm-send-checkbox').prop('checked', false);
        const confirmBtn = document.getElementById('confirmSendBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar a Pre-aprobación';
        }
    });
});
</script>

@php
    $providerLabels = $quotations->pluck('provider_name')->toArray();
    $providerAmounts = $quotations->pluck('total_amount')->toArray();
@endphp
<script>
    window.providerLabels = @json($providerLabels);
    window.providerAmounts = @json($providerAmounts);
</script>
<!-- JavaScript externo -->
<script src="{{ asset('js/quotation-chart.js') }}"></script>
@stop
