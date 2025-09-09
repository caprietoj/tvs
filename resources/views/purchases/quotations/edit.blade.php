@extends('adminlte::page')

@section('title', 'Editar cotización')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-edit text-primary mr-2"></i>
                Editar Cotización
            </h1>
            <small class="text-muted">Solicitud {{ $purchaseRequest->request_number }} - {{ $quotation->provider_name }}</small>
        </div>
        <div>
            <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la Solicitud
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Resumen de la solicitud (colapsible) -->
    <div class="card card-outline card-info collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-1"></i>
                Información de la Solicitud
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="row">
                <div class="col-md-2">
                    <strong>Número:</strong><br>
                    <span class="text-primary">{{ $purchaseRequest->request_number }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Solicitante:</strong><br>
                    {{ $purchaseRequest->requester }}
                </div>
                <div class="col-md-2">
                    <strong>Sección:</strong><br>
                    {{ $purchaseRequest->section_area }}
                </div>
                <div class="col-md-2">
                    <strong>Estado:</strong><br>
                    <span class="badge badge-info">{{ $purchaseRequest->status }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Progreso:</strong><br>
                    {{ $purchaseRequest->getQuotationProgress() }}
                </div>
            </div>
            @if($purchaseRequest->purchase_justification)
                <div class="mt-3">
                    <strong>Justificación:</strong><br>
                    <p class="text-muted mb-0">{{ $purchaseRequest->purchase_justification }}</p>
                </div>
            @endif
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Errores de validación</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('quotations.update', $quotation) }}" method="POST" enctype="multipart/form-data" id="quotationForm">
        @csrf
        @method('PUT')
        
        <!-- Información del Proveedor -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building mr-1"></i>
                    Información del Proveedor
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="provider_name">
                                <i class="fas fa-user-tie mr-1"></i>
                                Nombre del proveedor <span class="text-danger">*</span>
                            </label>
                            <select name="provider_name" id="provider_name" class="form-control select2" required>
                                <option value="">-- Seleccione un proveedor --</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->nombre }}" 
                                        {{ (old('provider_name', $quotation->provider_name) == $proveedor->nombre) ? 'selected' : '' }}>
                                        {{ $proveedor->nombre }} {{ $proveedor->nit ? '- ' . $proveedor->nit : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('provider_name'))
                                <small class="text-danger">{{ $errors->first('provider_name') }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-file-pdf mr-1"></i>
                                Archivo Actual
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                                </div>
                                <a href="{{ route('quotations.download', $quotation) }}" target="_blank" 
                                   class="form-control text-left" style="background: #f8f9fa;">
                                    Ver cotización actual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de Impuestos -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator mr-1"></i>
                    Configuración de Impuestos
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Nota:</strong> Puede aplicar impuestos de forma global (a todos los items) o individual (por cada item). 
                    Los impuestos globales e individuales son mutuamente excluyentes.
                </div>
                
                <div class="form-group">
                    <label><strong>Modo de aplicación de impuestos:</strong></label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="tax_application_mode" value="global" 
                                    {{ old('tax_application_mode', $quotation->tax_application_mode ?? 'global') == 'global' ? 'checked' : '' }} 
                                    onchange="toggleTaxMode('global')" class="custom-control-input" id="tax_global">
                                <label class="custom-control-label" for="tax_global">
                                    <i class="fas fa-globe mr-1"></i>
                                    Global (aplicar a todo el total)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="tax_application_mode" value="per_item" 
                                    {{ old('tax_application_mode', $quotation->tax_application_mode) == 'per_item' ? 'checked' : '' }} 
                                    onchange="toggleTaxMode('per_item')" class="custom-control-input" id="tax_per_item">
                                <label class="custom-control-label" for="tax_per_item">
                                    <i class="fas fa-list mr-1"></i>
                                    Por item individual
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="global-taxes">
                    <h5><i class="fas fa-percentage mr-1"></i> Impuestos Globales</h5>
                    <div class="row">
                        <!-- IVA 19% -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="includes_iva_19" value="1" 
                                        {{ old('includes_iva_19', $quotation->includes_iva_19) ? 'checked' : '' }} 
                                        onchange="toggleTaxInput('iva_19')" class="custom-control-input" id="iva_19_check">
                                    <label class="custom-control-label" for="iva_19_check">IVA 19%</label>
                                </div>
                                <div class="input-group input-group-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="iva_19_amount" id="iva_19_amount" step="0.01" 
                                        value="{{ old('iva_19_amount', $quotation->iva_19_amount ?? 0) }}" 
                                        placeholder="0.00" class="form-control"
                                        {{ old('includes_iva_19', $quotation->includes_iva_19) ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>

                        <!-- IVA 5% -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="includes_iva_5" value="1" 
                                        {{ old('includes_iva_5', $quotation->includes_iva_5) ? 'checked' : '' }} 
                                        onchange="toggleTaxInput('iva_5')" class="custom-control-input" id="iva_5_check">
                                    <label class="custom-control-label" for="iva_5_check">IVA 5%</label>
                                </div>
                                <div class="input-group input-group-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="iva_5_amount" id="iva_5_amount" step="0.01" 
                                        value="{{ old('iva_5_amount', $quotation->iva_5_amount ?? 0) }}" 
                                        placeholder="0.00" class="form-control"
                                        {{ old('includes_iva_5', $quotation->includes_iva_5) ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>

                        <!-- IPOCONSUMO 8% -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="includes_ipoconsumo_8" value="1" 
                                        {{ old('includes_ipoconsumo_8', $quotation->includes_ipoconsumo_8) ? 'checked' : '' }} 
                                        onchange="toggleTaxInput('ipoconsumo_8')" class="custom-control-input" id="ipoconsumo_8_check">
                                    <label class="custom-control-label" for="ipoconsumo_8_check">IPOCONSUMO 8%</label>
                                </div>
                                <div class="input-group input-group-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="ipoconsumo_8_amount" id="ipoconsumo_8_amount" step="0.01" 
                                        value="{{ old('ipoconsumo_8_amount', $quotation->ipoconsumo_8_amount ?? 0) }}" 
                                        placeholder="0.00" class="form-control"
                                        {{ old('includes_ipoconsumo_8', $quotation->includes_ipoconsumo_8) ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>

                        <!-- IPOCONSUMO 4% -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="includes_ipoconsumo_4" value="1" 
                                        {{ old('includes_ipoconsumo_4', $quotation->includes_ipoconsumo_4) ? 'checked' : '' }} 
                                        onchange="toggleTaxInput('ipoconsumo_4')" class="custom-control-input" id="ipoconsumo_4_check">
                                    <label class="custom-control-label" for="ipoconsumo_4_check">IPOCONSUMO 4%</label>
                                </div>
                                <div class="input-group input-group-sm mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="ipoconsumo_4_amount" id="ipoconsumo_4_amount" step="0.01" 
                                        value="{{ old('ipoconsumo_4_amount', $quotation->ipoconsumo_4_amount ?? 0) }}" 
                                        placeholder="0.00" class="form-control"
                                        {{ old('includes_ipoconsumo_4', $quotation->includes_ipoconsumo_4) ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items de la solicitud -->
        @if($purchaseRequest->type == 'purchase' && $purchaseRequest->purchase_items)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        Items Solicitados
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $purchaseItems = is_array($purchaseRequest->purchase_items) 
                            ? $purchaseRequest->purchase_items 
                            : json_decode($purchaseRequest->purchase_items, true);
                        $existingPrices = old('item_prices', $quotation->original_item_prices ?? []);
                        $existingQuantities = old('item_quantities', $quotation->original_item_quantities ?? []);
                        $existingDescriptions = old('item_descriptions', $quotation->original_item_descriptions ?? []);
                    @endphp
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th><i class="fas fa-hashtag mr-1"></i>Item</th>
                                    <th><i class="fas fa-sort-numeric-up mr-1"></i>Cantidad</th>
                                    <th><i class="fas fa-info-circle mr-1"></i>Descripción</th>
                                    <th><i class="fas fa-dollar-sign mr-1"></i>Precio Unitario</th>
                                    <th id="per-item-taxes-header" style="display: none;">
                                        <i class="fas fa-percent mr-1"></i>Impuestos
                                    </th>
                                    <th><i class="fas fa-calculator mr-1"></i>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseItems as $index => $item)
                                    @if(!empty($item['quantity']))
                                    <tr>
                                        <td class="align-middle">
                                            <span class="badge badge-primary">{{ $item['item'] ?? '' }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <input type="number" 
                                                name="item_quantities[{{ $index }}]" 
                                                value="{{ $existingQuantities[$index] ?? $item['quantity'] ?? '' }}" 
                                                min="0" 
                                                step="0.01"
                                                placeholder="{{ $item['quantity'] ?? 1 }}"
                                                class="form-control form-control-sm text-center"
                                                style="width: 80px;"
                                                onchange="updateItemQuantity({{ $index }})">
                                        </td>
                                        <td class="align-middle">
                                            <textarea name="item_descriptions[{{ $index }}]"
                                                class="form-control form-control-sm"
                                                rows="2"
                                                placeholder="{{ $item['description'] ?? '' }}"
                                                style="resize: vertical; min-height: 38px;">{{ $existingDescriptions[$index] ?? $item['description'] ?? '' }}</textarea>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" 
                                                    name="item_prices[{{ $index }}]" 
                                                    step="0.01" 
                                                    value="{{ $existingPrices[$index] ?? '' }}" 
                                                    placeholder="0.00"
                                                    class="form-control"
                                                    onchange="calculateItemTotalDynamic({{ $index }})">
                                            </div>
                                        </td>
                                        <td class="per-item-taxes" style="display: none;">
                                            <div class="row">
                                                @php
                                                    $itemTaxes = old("item_iva_19.$index", 
                                                        isset($quotation->original_item_taxes[$index]['iva_19']) && $quotation->original_item_taxes[$index]['iva_19'] ? '1' : '');
                                                @endphp
                                                <div class="col-6">
                                                    <div class="custom-control custom-checkbox custom-control-sm">
                                                        <input type="checkbox" name="item_iva_19[{{ $index }}]" value="1" 
                                                            {{ $itemTaxes ? 'checked' : '' }} 
                                                            class="custom-control-input" id="item_iva_19_{{ $index }}">
                                                        <label class="custom-control-label" for="item_iva_19_{{ $index }}">IVA 19%</label>
                                                    </div>
                                                </div>
                                                
                                                @php
                                                    $itemTaxes = old("item_iva_5.$index", 
                                                        isset($quotation->original_item_taxes[$index]['iva_5']) && $quotation->original_item_taxes[$index]['iva_5'] ? '1' : '');
                                                @endphp
                                                <div class="col-6">
                                                    <div class="custom-control custom-checkbox custom-control-sm">
                                                        <input type="checkbox" name="item_iva_5[{{ $index }}]" value="1" 
                                                            {{ $itemTaxes ? 'checked' : '' }} 
                                                            class="custom-control-input" id="item_iva_5_{{ $index }}">
                                                        <label class="custom-control-label" for="item_iva_5_{{ $index }}">IVA 5%</label>
                                                    </div>
                                                </div>
                                                
                                                @php
                                                    $itemTaxes = old("item_ipoconsumo_8.$index", 
                                                        isset($quotation->original_item_taxes[$index]['ipoconsumo_8']) && $quotation->original_item_taxes[$index]['ipoconsumo_8'] ? '1' : '');
                                                @endphp
                                                <div class="col-6">
                                                    <div class="custom-control custom-checkbox custom-control-sm">
                                                        <input type="checkbox" name="item_ipoconsumo_8[{{ $index }}]" value="1" 
                                                            {{ $itemTaxes ? 'checked' : '' }} 
                                                            class="custom-control-input" id="item_ipoconsumo_8_{{ $index }}">
                                                        <label class="custom-control-label" for="item_ipoconsumo_8_{{ $index }}">IPO 8%</label>
                                                    </div>
                                                </div>
                                                
                                                @php
                                                    $itemTaxes = old("item_ipoconsumo_4.$index", 
                                                        isset($quotation->original_item_taxes[$index]['ipoconsumo_4']) && $quotation->original_item_taxes[$index]['ipoconsumo_4'] ? '1' : '');
                                                @endphp
                                                <div class="col-6">
                                                    <div class="custom-control custom-checkbox custom-control-sm">
                                                        <input type="checkbox" name="item_ipoconsumo_4[{{ $index }}]" value="1" 
                                                            {{ $itemTaxes ? 'checked' : '' }} 
                                                            class="custom-control-input" id="item_ipoconsumo_4_{{ $index }}">
                                                        <label class="custom-control-label" for="item_ipoconsumo_4_{{ $index }}">IPO 4%</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-success badge-lg item-total" id="item_total_{{ $index }}">
                                                {{ isset($existingPrices[$index]) && isset($item['quantity']) ? 
                                                    '$' . number_format($existingPrices[$index] * $item['quantity'], 2) : '$0.00' }}
                                            </span>
                                            <input type="hidden" name="item_totals[{{ $index }}]" id="item_total_input_{{ $index }}" 
                                                value="{{ isset($existingPrices[$index]) && isset($item['quantity']) ? 
                                                    $existingPrices[$index] * $item['quantity'] : 0 }}">
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

                <!-- Items de servicios -->
                @if($purchaseRequest->type == 'services' && $purchaseRequest->service_type == 'regular' && $purchaseRequest->service_items)
                    <div class="form-section">
                        <h4>Servicios solicitados</h4>
                        @php
                            $serviceItems = is_array($purchaseRequest->service_items) 
                                ? $purchaseRequest->service_items 
                                : json_decode($purchaseRequest->service_items, true);
                            $existingPrices = old('item_prices', $quotation->original_item_prices ?? []);
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Cantidad</th>
                                        <th>Descripción</th>
                                        <th>Observaciones</th>
                                        <th>Precio Unitario</th>
                                        <th id="per-item-taxes-header-services" style="display: none;">Impuestos</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceItems as $index => $service)
                                        @if(!empty($service['quantity']))
                                        <tr>
                                            <td>{{ $service['item'] ?? '' }}</td>
                                            <td>{{ $service['quantity'] ?? '' }}</td>
                                            <td>{{ $service['description'] ?? '' }}</td>
                                            <td>{{ $service['observations'] ?? '' }}</td>
                                            <td>
                                                <input type="number" 
                                                    name="item_prices[{{ $index }}]" 
                                                    step="0.01" 
                                                    value="{{ $existingPrices[$index] ?? '' }}" 
                                                    placeholder="0.00"
                                                    onchange="calculateItemTotal({{ $index }}, {{ $service['quantity'] ?? 1 }})">
                                            </td>
                                            <td class="per-item-taxes" style="display: none;">
                                                <div class="tax-checkboxes">
                                                    @php
                                                        $itemTaxes = old("item_iva_19.$index", 
                                                            isset($quotation->original_item_taxes[$index]['iva_19']) && $quotation->original_item_taxes[$index]['iva_19'] ? '1' : '');
                                                    @endphp
                                                    <label><input type="checkbox" name="item_iva_19[{{ $index }}]" value="1" 
                                                        {{ $itemTaxes ? 'checked' : '' }}> IVA 19%</label>
                                                    
                                                    @php
                                                        $itemTaxes = old("item_iva_5.$index", 
                                                            isset($quotation->original_item_taxes[$index]['iva_5']) && $quotation->original_item_taxes[$index]['iva_5'] ? '1' : '');
                                                    @endphp
                                                    <label><input type="checkbox" name="item_iva_5[{{ $index }}]" value="1" 
                                                        {{ $itemTaxes ? 'checked' : '' }}> IVA 5%</label>
                                                    
                                                    @php
                                                        $itemTaxes = old("item_ipoconsumo_8.$index", 
                                                            isset($quotation->original_item_taxes[$index]['ipoconsumo_8']) && $quotation->original_item_taxes[$index]['ipoconsumo_8'] ? '1' : '');
                                                    @endphp
                                                    <label><input type="checkbox" name="item_ipoconsumo_8[{{ $index }}]" value="1" 
                                                        {{ $itemTaxes ? 'checked' : '' }}> IPO 8%</label>
                                                    
                                                    @php
                                                        $itemTaxes = old("item_ipoconsumo_4.$index", 
                                                            isset($quotation->original_item_taxes[$index]['ipoconsumo_4']) && $quotation->original_item_taxes[$index]['ipoconsumo_4'] ? '1' : '');
                                                    @endphp
                                                    <label><input type="checkbox" name="item_ipoconsumo_4[{{ $index }}]" value="1" 
                                                        {{ $itemTaxes ? 'checked' : '' }}> IPO 4%</label>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="item-total" id="item_total_{{ $index }}">
                                                    {{ isset($existingPrices[$index]) && isset($service['quantity']) ? 
                                                        '$' . number_format($existingPrices[$index] * $service['quantity'], 2) : '$0.00' }}
                                                </span>
                                                <input type="hidden" name="item_totals[{{ $index }}]" id="item_total_input_{{ $index }}" 
                                                    value="{{ isset($existingPrices[$index]) && isset($service['quantity']) ? 
                                                        $existingPrices[$index] * $service['quantity'] : 0 }}">
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Servicio general (para servicios sin cotización específica) -->
                @if($purchaseRequest->type == 'services' && ($purchaseRequest->service_type == 'immediate' || 
                    !$purchaseRequest->service_items || 
                    (is_array($purchaseRequest->service_items) && count($purchaseRequest->service_items) == 0)))
                    <div class="form-section">
                        <h4>Precio del servicio</h4>
                        <div class="form-group">
                            <label for="general_service_price">Precio total del servicio *</label>
                            <input type="number" name="general_service_price" id="general_service_price" 
                                step="0.01" value="{{ old('general_service_price', $quotation->original_item_prices['general'] ?? '') }}" 
                                placeholder="0.00" required>
                        </div>
                    </div>
                @endif

                <!-- Items adicionales -->
                <div class="form-section">
                    <h4>Items adicionales (opcionales)</h4>
                    <p class="help-text">Agregue items que no estén en la solicitud original pero que el proveedor incluye en su cotización.</p>
                    
                    <div id="additional-items-container">
                        @php
                            $additionalItems = old('additional_items', $quotation->additional_items ?? []);
                        @endphp
                        
                        @if(is_array($additionalItems) && count($additionalItems) > 0)
                            @foreach($additionalItems as $index => $item)
                                <div class="additional-item" data-index="{{ $index }}">
                                    <div class="item-grid">
                                        <div class="form-group">
                                            <label>Descripción</label>
                                            <input type="text" name="additional_items[{{ $index }}][description]" 
                                                value="{{ $item['description'] ?? '' }}" placeholder="Descripción del item">
                                        </div>
                                        <div class="form-group">
                                            <label>Cantidad</label>
                                            <input type="number" name="additional_items[{{ $index }}][quantity]" 
                                                value="{{ $item['quantity'] ?? 1 }}" min="0" step="0.01" 
                                                onchange="calculateAdditionalTotal({{ $index }})">
                                        </div>
                                        <div class="form-group">
                                            <label>Unidad</label>
                                            <input type="text" name="additional_items[{{ $index }}][unit]" 
                                                value="{{ $item['unit'] ?? '' }}" placeholder="ej: unidad, kg, m²">
                                        </div>
                                        <div class="form-group">
                                            <label>Precio unitario</label>
                                            <input type="number" name="additional_items[{{ $index }}][price]" 
                                                value="{{ $item['price'] ?? '' }}" step="0.01" placeholder="0.00" 
                                                onchange="calculateAdditionalTotal({{ $index }})">
                                        </div>
                                        <div class="form-group">
                                            <label>Total</label>
                                            <span class="additional-total" id="additional_total_{{ $index }}">
                                                ${{ number_format(($item['total'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn-remove" onclick="removeAdditionalItem({{ $index }})">Eliminar</button>
                                        </div>
                                    </div>
                                    
                                    <div class="additional-item-taxes">
                                        <label><strong>Impuestos para este item:</strong></label>
                                        <div class="tax-checkboxes">
                                            <label><input type="checkbox" name="additional_items[{{ $index }}][includes_iva_19]" value="1" 
                                                {{ isset($item['includes_iva_19']) && $item['includes_iva_19'] ? 'checked' : '' }}> IVA 19%</label>
                                            <label><input type="checkbox" name="additional_items[{{ $index }}][includes_iva_5]" value="1" 
                                                {{ isset($item['includes_iva_5']) && $item['includes_iva_5'] ? 'checked' : '' }}> IVA 5%</label>
                                            <label><input type="checkbox" name="additional_items[{{ $index }}][includes_ipoconsumo_8]" value="1" 
                                                {{ isset($item['includes_ipoconsumo_8']) && $item['includes_ipoconsumo_8'] ? 'checked' : '' }}> IPOCONSUMO 8%</label>
                                            <label><input type="checkbox" name="additional_items[{{ $index }}][includes_ipoconsumo_4]" value="1" 
                                                {{ isset($item['includes_ipoconsumo_4']) && $item['includes_ipoconsumo_4'] ? 'checked' : '' }}> IPOCONSUMO 4%</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <button type="button" onclick="addAdditionalItem()" class="btn-add">+ Agregar item adicional</button>
                </div>

        <!-- Totales de la Cotización -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator mr-1"></i>
                    Totales de la Cotización
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subtotal">
                                <i class="fas fa-minus mr-1"></i>
                                Subtotal <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="subtotal" id="subtotal" step="0.01" 
                                    value="{{ old('subtotal', $quotation->subtotal) }}" 
                                    placeholder="0.00" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">
                                <i class="fas fa-equals mr-1"></i>
                                Total Final <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="total_amount" id="total_amount" step="0.01" 
                                    value="{{ old('total_amount', $quotation->total_amount) }}" 
                                    placeholder="0.00" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condiciones Comerciales -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-handshake mr-1"></i>
                    Condiciones Comerciales
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delivery_time">
                                <i class="fas fa-truck mr-1"></i>
                                Tiempo de entrega
                            </label>
                            <input type="text" name="delivery_time" id="delivery_time" 
                                value="{{ old('delivery_time', $quotation->delivery_time) }}" 
                                placeholder="ej: 5-10 días hábiles" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="validity">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Validez de la oferta
                            </label>
                            <input type="text" name="validity" id="validity" 
                                value="{{ old('validity', $quotation->validity) }}" 
                                placeholder="ej: 15 días calendario" class="form-control">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">
                                <i class="fas fa-credit-card mr-1"></i>
                                Forma de pago
                            </label>
                            <input type="text" name="payment_method" id="payment_method" 
                                value="{{ old('payment_method', $quotation->payment_method) }}" 
                                placeholder="ej: Contado, 30 días" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="warranty">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Garantía
                            </label>
                            <input type="text" name="warranty" id="warranty" 
                                value="{{ old('warranty', $quotation->warranty) }}" 
                                placeholder="ej: 12 meses por defectos de fabricación" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archivo de Cotización -->
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-pdf mr-1"></i>
                    Archivo de la Cotización
                </h3>
            </div>
            <div class="card-body">
                @if($quotation->file_path)
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Archivo Actual</h5>
                        <a href="{{ route('quotations.download', $quotation) }}" target="_blank" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i> Ver cotización actual
                        </a>
                    </div>
                @endif
                
                <div class="form-group">
                    <label for="quotation_file">
                        <i class="fas fa-upload mr-1"></i>
                        Subir nueva cotización (PDF) - Opcional
                    </label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="quotation_file" id="quotation_file" 
                                accept=".pdf" class="custom-file-input">
                            <label class="custom-file-label" for="quotation_file" data-browse="Examinar">
                                Seleccionar archivo PDF...
                            </label>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Si no selecciona un archivo, se mantendrá el archivo actual. Máximo 5MB.
                    </small>
                    @if ($errors->has('quotation_file'))
                        <small class="text-danger">{{ $errors->first('quotation_file') }}</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save mr-1"></i> Actualizar Cotización
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    
    <style>
        .badge-lg {
            font-size: 0.9em;
            padding: 0.5em 0.8em;
        }
        
        .card-tools .btn-tool {
            color: #ffffff;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,.075);
        }
        
        .input-group-sm .form-control {
            height: calc(1.8125rem + 2px);
        }
        
        .custom-control-sm .custom-control-label {
            font-size: 0.875rem;
        }
        
        .custom-control-sm .custom-control-label::before {
            width: 1rem;
            height: 1rem;
        }
        
        .custom-control-sm .custom-control-label::after {
            width: 1rem;
            height: 1rem;
        }
        
        .alert .btn {
            margin-top: 0.5rem;
        }
        
        .custom-file-label::after {
            content: "Examinar";
        }
        
        .per-item-taxes .row > div {
            padding: 0.2rem;
        }
        
        @media (max-width: 768px) {
            .per-item-taxes .row {
                flex-direction: column;
            }
            
            .per-item-taxes .row > div {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        
        /* Animaciones suaves */
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Loading state para botones */
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Animaciones para actualizaciones de totales */
        .bg-light {
            background-color: #e8f4fd !important;
            transition: background-color 0.3s ease;
        }
        
        .total-updating {
            background: linear-gradient(45deg, #e8f4fd, #cce7ff);
            animation: pulse-total 0.8s ease-in-out;
        }
        
        @keyframes pulse-total {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        /* Feedback visual para inputs que están siendo calculados */
        input.calculating {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Efecto para valores calculados automáticamente */
        input.auto-calculated {
            background-color: #e8f5e8 !important;
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            transition: all 0.3s ease;
        }
        
        input.auto-calculated::placeholder {
            color: #28a745 !important;
        }
    </style>
@stop

@section('js')
    <!-- Select2 JS -->
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    
    <script>
        let additionalItemIndex = {{ count(old('additional_items', $quotation->additional_items ?? [])) }};
        
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
            
            // Inicializar el modo de impuestos
            const currentMode = $('input[name="tax_application_mode"]:checked').val();
            toggleTaxMode(currentMode);
            
            // Configurar estado inicial de los inputs de impuestos
            $('#global-taxes input[type="checkbox"]').each(function() {
                const taxType = $(this).attr('name').replace('includes_', '');
                toggleTaxInput(taxType);
            });
            
            // Agregar event listeners para checkboxes de impuestos
            $('#global-taxes input[type="checkbox"]').on('change', function() {
                const taxType = $(this).attr('name').replace('includes_', '');
                toggleTaxInput(taxType);
            });
            
            // Actualizar label del archivo cuando se selecciona
            $('#quotation_file').on('change', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Seleccionar archivo PDF...');
            });
            
            // Event listeners para actualizar automáticamente los totales
            $('input[name^="item_prices"]').on('input change', function() {
                console.log('Precio de item cambiado, actualizando totales...');
                updateTotals();
            });
            
            $('#general_service_price').on('input change', function() {
                console.log('Precio de servicio general cambiado, actualizando totales...');
                updateTotals();
            });
            
            $('input[name$="[quantity]"], input[name$="[price]"]').on('input change', function() {
                console.log('Cantidad o precio de item adicional cambiado, actualizando totales...');
                updateTotals();
            });
            
            // Calcular totales iniciales
            updateTotals();
            
            // Agregar loading state al formulario
            $('#quotationForm').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.addClass('loading');
                submitBtn.prop('disabled', true);
            });
            
            // Mostrar notificaciones Toast
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
        });
        
        function toggleTaxMode(mode) {
            const globalTaxes = $('#global-taxes');
            const perItemTaxesHeaders = $('#per-item-taxes-header, #per-item-taxes-header-services');
            const perItemTaxes = $('.per-item-taxes');
            
            if (mode === 'global') {
                globalTaxes.show();
                perItemTaxesHeaders.hide();
                perItemTaxes.hide();
                
                // Desmarcar todos los impuestos por item
                $('.per-item-taxes input[type="checkbox"]').prop('checked', false);
            } else {
                globalTaxes.hide();
                perItemTaxesHeaders.show();
                perItemTaxes.show();
                
                // Desmarcar todos los impuestos globales
                $('#global-taxes input[type="checkbox"]').each(function() {
                    $(this).prop('checked', false);
                    const taxType = $(this).attr('name').replace('includes_', '');
                    const relatedInput = $('#' + taxType + '_amount');
                    if (relatedInput.length) {
                        relatedInput.prop('disabled', true).val('');
                    }
                });
            }
            
            // Actualizar totales cuando cambie el modo de impuestos
            updateTotals();
        }
        
        function toggleTaxInput(taxType) {
            const checkbox = $(`input[name="includes_${taxType}"]`);
            const input = $(`#${taxType}_amount`);
            
            if (input.length) {
                input.prop('disabled', !checkbox.prop('checked'));
                
                if (checkbox.prop('checked')) {
                    // Calcular automáticamente el valor del impuesto cuando se marca
                    const subtotal = parseFloat($('#subtotal').val()) || 0;
                    let taxRate = 0;
                    
                    // Determinar la tasa de impuesto
                    switch(taxType) {
                        case 'iva_19':
                            taxRate = 0.19;
                            break;
                        case 'iva_5':
                            taxRate = 0.05;
                            break;
                        case 'ipoconsumo_8':
                            taxRate = 0.08;
                            break;
                        case 'ipoconsumo_4':
                            taxRate = 0.04;
                            break;
                    }
                    
                    // Calcular y asignar el valor automáticamente
                    if (taxRate > 0 && subtotal > 0) {
                        const calculatedAmount = subtotal * taxRate;
                        input.val(calculatedAmount.toFixed(2));
                        
                        // Efecto visual para mostrar que fue calculado automáticamente
                        input.addClass('auto-calculated');
                        setTimeout(() => input.removeClass('auto-calculated'), 1500);
                    }
                } else {
                    // Limpiar el valor cuando se desmarca
                    input.val('');
                }
                
                // Agregar event listener para actualizar totales cuando cambie el valor del impuesto
                input.off('input change').on('input change', function() {
                    updateTotals();
                });
            }
            
            // Actualizar totales cuando se marque/desmarque un impuesto
            updateTotals();
        }
        
        // Nueva función para actualizar cantidad y recalcular total
        function updateItemQuantity(index) {
            calculateItemTotalDynamic(index);
            updateTotals();
        }
        
        // Nueva función que obtiene la cantidad dinámicamente
        function calculateItemTotalDynamic(index) {
            const quantityInput = $(`input[name="item_quantities[${index}]"]`);
            const quantity = parseFloat(quantityInput.val()) || 1;
            calculateItemTotal(index, quantity);
        }
        
        function calculateItemTotal(index, quantity) {
            const priceInput = $(`input[name="item_prices[${index}]"]`);
            const totalSpan = $(`#item_total_${index}`);
            const totalInput = $(`#item_total_input_${index}`);
            
            if (priceInput.length && totalSpan.length && totalInput.length) {
                const price = parseFloat(priceInput.val()) || 0;
                const total = price * quantity;
                
                totalSpan.text('$' + total.toLocaleString('es-CO', {
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2
                }));
                totalInput.val(total);
                
                // Animación visual
                totalSpan.addClass('badge-warning').removeClass('badge-success');
                setTimeout(() => {
                    totalSpan.removeClass('badge-warning').addClass('badge-success');
                }, 300);
                
                // Actualizar totales generales
                updateTotals();
            }
        }
        
        function calculateAdditionalTotal(index) {
            const quantityInput = $(`input[name="additional_items[${index}][quantity]"]`);
            const priceInput = $(`input[name="additional_items[${index}][price]"]`);
            const totalSpan = $(`#additional_total_${index}`);
            
            if (quantityInput.length && priceInput.length && totalSpan.length) {
                const quantity = parseFloat(quantityInput.val()) || 0;
                const price = parseFloat(priceInput.val()) || 0;
                const total = quantity * price;
                
                totalSpan.text('$' + total.toLocaleString('es-CO', {
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2
                }));
                
                // Actualizar totales generales
                updateTotals();
            }
        }
        
        function updateTotals() {
            // Agregar feedback visual
            $('#subtotal, #total_amount').addClass('calculating');
            
            let subtotal = 0;
            
            // Sumar todos los totales de items individuales
            $('input[name^="item_totals"]').each(function() {
                const value = parseFloat($(this).val()) || 0;
                subtotal += value;
            });
            
            // Sumar precio de servicio general si existe
            const generalServicePrice = parseFloat($('#general_service_price').val()) || 0;
            subtotal += generalServicePrice;
            
            // Sumar items adicionales
            $('input[name$="[quantity]"]').each(function() {
                const name = $(this).attr('name');
                if (name.includes('additional_items')) {
                    const index = name.match(/\[(\d+)\]/)[1];
                    const quantity = parseFloat($(this).val()) || 0;
                    const price = parseFloat($(`input[name="additional_items[${index}][price]"]`).val()) || 0;
                    subtotal += (quantity * price);
                }
            });
            
            // Actualizar subtotal con animación
            const currentSubtotal = parseFloat($('#subtotal').val()) || 0;
            if (Math.abs(currentSubtotal - subtotal) > 0.01) {
                $('#subtotal').addClass('total-updating');
                setTimeout(() => {
                    $('#subtotal').removeClass('total-updating');
                }, 800);
            }
            $('#subtotal').val(subtotal.toFixed(2));
            
            // Calcular y actualizar automáticamente los valores de impuestos
            updateTaxAmounts(subtotal);
            
            // Calcular total con impuestos
            let total = subtotal;
            const taxMode = $('input[name="tax_application_mode"]:checked').val();
            
            if (taxMode === 'global') {
                // Aplicar impuestos globales
                if ($('#iva_19_check').is(':checked')) {
                    const ivaAmount = parseFloat($('#iva_19_amount').val()) || 0;
                    total += ivaAmount;
                }
                if ($('#iva_5_check').is(':checked')) {
                    const ivaAmount = parseFloat($('#iva_5_amount').val()) || 0;
                    total += ivaAmount;
                }
                if ($('#ipoconsumo_8_check').is(':checked')) {
                    const ipoAmount = parseFloat($('#ipoconsumo_8_amount').val()) || 0;
                    total += ipoAmount;
                }
                if ($('#ipoconsumo_4_check').is(':checked')) {
                    const ipoAmount = parseFloat($('#ipoconsumo_4_amount').val()) || 0;
                    total += ipoAmount;
                }
            }
            
            // Actualizar total final con animación
            const currentTotal = parseFloat($('#total_amount').val()) || 0;
            if (Math.abs(currentTotal - total) > 0.01) {
                $('#total_amount').addClass('total-updating');
                setTimeout(() => {
                    $('#total_amount').removeClass('total-updating');
                }, 800);
            }
            $('#total_amount').val(total.toFixed(2));
            
            // Remover feedback visual
            setTimeout(() => {
                $('#subtotal, #total_amount').removeClass('calculating');
            }, 300);
            
            console.log('Totales actualizados:', { subtotal: subtotal, total: total });
        }
        
        /**
         * Actualizar automáticamente los montos de impuestos basados en el subtotal
         */
        function updateTaxAmounts(subtotal) {
            // Solo actualizar si el modo es global
            const taxMode = $('input[name="tax_application_mode"]:checked').val();
            if (taxMode !== 'global') {
                return;
            }
            
            // IVA 19%
            if ($('#iva_19_check').is(':checked')) {
                const ivaInput = $('#iva_19_amount');
                if (ivaInput.length && !ivaInput.is(':focus')) { // No actualizar si el usuario está editando
                    const calculatedIva = subtotal * 0.19;
                    ivaInput.val(calculatedIva.toFixed(2));
                    ivaInput.addClass('auto-calculated');
                    setTimeout(() => ivaInput.removeClass('auto-calculated'), 1000);
                }
            }
            
            // IVA 5%
            if ($('#iva_5_check').is(':checked')) {
                const ivaInput = $('#iva_5_amount');
                if (ivaInput.length && !ivaInput.is(':focus')) {
                    const calculatedIva = subtotal * 0.05;
                    ivaInput.val(calculatedIva.toFixed(2));
                    ivaInput.addClass('auto-calculated');
                    setTimeout(() => ivaInput.removeClass('auto-calculated'), 1000);
                }
            }
            
            // IPOCONSUMO 8%
            if ($('#ipoconsumo_8_check').is(':checked')) {
                const ipoInput = $('#ipoconsumo_8_amount');
                if (ipoInput.length && !ipoInput.is(':focus')) {
                    const calculatedIpo = subtotal * 0.08;
                    ipoInput.val(calculatedIpo.toFixed(2));
                    ipoInput.addClass('auto-calculated');
                    setTimeout(() => ipoInput.removeClass('auto-calculated'), 1000);
                }
            }
            
            // IPOCONSUMO 4%
            if ($('#ipoconsumo_4_check').is(':checked')) {
                const ipoInput = $('#ipoconsumo_4_amount');
                if (ipoInput.length && !ipoInput.is(':focus')) {
                    const calculatedIpo = subtotal * 0.04;
                    ipoInput.val(calculatedIpo.toFixed(2));
                    ipoInput.addClass('auto-calculated');
                    setTimeout(() => ipoInput.removeClass('auto-calculated'), 1000);
                }
            }
        }
        
        function addAdditionalItem() {
            const container = $('#additional-items-container');
            const newItem = $(`
                <div class="card additional-item" data-index="${additionalItemIndex}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" name="additional_items[${additionalItemIndex}][description]" 
                                        placeholder="Descripción del item" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" name="additional_items[${additionalItemIndex}][quantity]" 
                                        value="1" min="0" step="0.01" class="form-control"
                                        onchange="calculateAdditionalTotal(${additionalItemIndex})">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Unidad</label>
                                    <input type="text" name="additional_items[${additionalItemIndex}][unit]" 
                                        placeholder="ej: unidad, kg" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Precio unitario</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="additional_items[${additionalItemIndex}][price]" 
                                            step="0.01" placeholder="0.00" class="form-control"
                                            onchange="calculateAdditionalTotal(${additionalItemIndex})">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total</label>
                                    <div class="p-2 bg-light rounded">
                                        <span class="badge badge-success" id="additional_total_${additionalItemIndex}">$0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm d-block" 
                                        onclick="removeAdditionalItem(${additionalItemIndex})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <label><strong>Impuestos para este item:</strong></label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="additional_items[${additionalItemIndex}][includes_iva_19]" 
                                                value="1" class="custom-control-input" id="add_iva_19_${additionalItemIndex}">
                                            <label class="custom-control-label" for="add_iva_19_${additionalItemIndex}">IVA 19%</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="additional_items[${additionalItemIndex}][includes_iva_5]" 
                                                value="1" class="custom-control-input" id="add_iva_5_${additionalItemIndex}">
                                            <label class="custom-control-label" for="add_iva_5_${additionalItemIndex}">IVA 5%</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="additional_items[${additionalItemIndex}][includes_ipoconsumo_8]" 
                                                value="1" class="custom-control-input" id="add_ipo_8_${additionalItemIndex}">
                                            <label class="custom-control-label" for="add_ipo_8_${additionalItemIndex}">IPOCONSUMO 8%</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="additional_items[${additionalItemIndex}][includes_ipoconsumo_4]" 
                                                value="1" class="custom-control-input" id="add_ipo_4_${additionalItemIndex}">
                                            <label class="custom-control-label" for="add_ipo_4_${additionalItemIndex}">IPOCONSUMO 4%</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            container.append(newItem);
            
            // Agregar event listeners para los nuevos inputs
            newItem.find('input[name$="[quantity]"], input[name$="[price]"]').on('input change', function() {
                const index = $(this).closest('.additional-item').data('index');
                calculateAdditionalTotal(index);
            });
            
            // Animación de entrada
            newItem.hide().slideDown(300);
            
            additionalItemIndex++;
        }
        
        function removeAdditionalItem(index) {
            const item = $(`.additional-item[data-index="${index}"]`);
            if (item.length) {
                item.slideUp(300, function() {
                    $(this).remove();
                    // Actualizar totales después de eliminar
                    updateTotals();
                });
            }
        }
    </script>
@stop
