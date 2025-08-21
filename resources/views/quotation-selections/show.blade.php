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
                                            <!-- Input oculto para almacenar la selección -->
                                            <input type="hidden" class="selected-provider-input" 
                                                   data-item-index="{{ $index }}" 
                                                   value="{{ $selection->quotation_id }}">
                                            
                                            <!-- Contenedor de proveedores -->
                                            <div class="provider-options" data-item-index="{{ $index }}">
                                                @foreach($quotations as $quotation)
                                                    @php
                                                        $unitPrice = 0;
                                                        if (isset($quotation->quotation_items[$index]['unit_price'])) {
                                                            $unitPrice = $quotation->quotation_items[$index]['unit_price'];
                                                        } else {
                                                            $totalItems = count($purchaseItems);
                                                            $unitPrice = $totalItems > 0 ? ($quotation->total_amount / $totalItems) : 0;
                                                        }
                                                        $totalPrice = $unitPrice * ($item['quantity'] ?? 1);
                                                        $isSelected = $selection->quotation_id == $quotation->id;
                                                    @endphp
                                                    <div class="provider-card {{ $isSelected ? 'selected' : '' }}" 
                                                         data-quotation-id="{{ $quotation->id }}"
                                                         data-provider-name="{{ $quotation->provider_name }}"
                                                         data-unit-price="{{ $unitPrice }}"
                                                         data-total-price="{{ $totalPrice }}"
                                                         data-item-index="{{ $index }}"
                                                         onclick="selectProvider(this)">
                                                        <div class="provider-name">{{ $quotation->provider_name }}</div>
                                                        <div class="provider-check">
                                                            <span class="checkmark">✓</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <small class="text-success mt-2 d-block" style="font-weight: 500;">
                                                <i class="fas fa-check-circle"></i> Seleccionado por {{ $selection->selectedBy->name }} el {{ $selection->selected_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    @else
                                        <div class="provider-selection">
                                            <!-- Input oculto para almacenar la selección -->
                                            <input type="hidden" class="selected-provider-input" 
                                                   data-item-index="{{ $index }}" 
                                                   value="">
                                            
                                            <!-- Contenedor de proveedores -->
                                            <div class="provider-options" data-item-index="{{ $index }}">
                                                @foreach($quotations as $quotation)
                                                    @php
                                                        $unitPrice = 0;
                                                        if (isset($quotation->quotation_items[$index]['unit_price'])) {
                                                            $unitPrice = $quotation->quotation_items[$index]['unit_price'];
                                                        } else {
                                                            $totalItems = count($purchaseItems);
                                                            $unitPrice = $totalItems > 0 ? ($quotation->total_amount / $totalItems) : 0;
                                                        }
                                                        $totalPrice = $unitPrice * ($item['quantity'] ?? 1);
                                                    @endphp
                                                    <div class="provider-card" 
                                                         data-quotation-id="{{ $quotation->id }}"
                                                         data-provider-name="{{ $quotation->provider_name }}"
                                                         data-unit-price="{{ $unitPrice }}"
                                                         data-total-price="{{ $totalPrice }}"
                                                         data-item-index="{{ $index }}"
                                                         onclick="selectProvider(this)">
                                                        <div class="provider-name">{{ $quotation->provider_name }}</div>
                                                        <div class="provider-check">
                                                            <span class="checkmark">✓</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
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

// Función principal para seleccionar proveedor desde las cards
function selectProvider(cardElement) {
    const itemIndex = cardElement.dataset.itemIndex;
    const quotationId = cardElement.dataset.quotationId;
    const providerName = cardElement.dataset.providerName;
    const unitPrice = cardElement.dataset.unitPrice;
    
    console.log('Proveedor seleccionado:', {
        itemIndex: itemIndex,
        quotationId: quotationId,
        providerName: providerName,
        unitPrice: unitPrice
    });
    
    // Agregar animación de selección
    cardElement.classList.add('selecting');
    setTimeout(() => {
        cardElement.classList.remove('selecting');
    }, 300);
    
    // Actualizar el estado visual inmediatamente
    const providerOptions = cardElement.parentElement;
    const allCards = providerOptions.querySelectorAll('.provider-card');
    
    // Remover selección anterior
    allCards.forEach(card => {
        card.classList.remove('selected');
    });
    
    // Agregar selección actual
    cardElement.classList.add('selected');
    
    // Actualizar input oculto
    const hiddenInput = providerOptions.parentElement.querySelector('.selected-provider-input');
    if (hiddenInput) {
        hiddenInput.value = quotationId;
    }
    
    // Guardar la selección automáticamente
    saveProviderSelection(itemIndex, quotationId, unitPrice, providerName);
}

// Función para manejar el cambio de proveedor en los selects (compatibilidad)
function handleProviderChange(selectElement) {
    console.log('handleProviderChange llamado para compatibilidad - ya no se usa');
    // Esta función se mantiene para compatibilidad pero ya no se usa
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

// Función para actualizar contadores usando las cards
function updateSelectionCounts() {
    const totalItems = {{ count($purchaseItems) }};
    
    // Contar cards seleccionadas
    const selectedCards = document.querySelectorAll('.provider-card.selected');
    const selectedCount = selectedCards.length;
    
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

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    console.log('DOM listo, configurando eventos para provider cards...');
    
    // Agregar eventos click a todas las provider cards
    document.querySelectorAll('.provider-card').forEach(card => {
        card.addEventListener('click', function() {
            selectProvider(this);
        });
    });
    
    // Marcar cards preseleccionadas basándose en selecciones existentes
    @if(isset($existingSelections) && is_array($existingSelections))
        @foreach($existingSelections as $itemIndex => $quotationId)
            const existingCard = document.querySelector('.provider-card[data-item-index="{{ $itemIndex }}"][data-quotation-id="{{ $quotationId }}"]');
            if (existingCard) {
                existingCard.classList.add('selected');
                console.log('Card preseleccionada encontrada para item {{ $itemIndex }}');
            }
        @endforeach
    @endif
    
    // Actualizar contadores iniciales
    updateSelectionCounts();
    
    console.log('Sistema de provider cards configurado correctamente');
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
</script>
@stop
