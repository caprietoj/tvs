@extends('adminlte::page')

@section('title', 'Crear Orden de Compra - Productos Sin Cotización')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Crear Orden de Compra - Productos Sin Cotización</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Información de la solicitud -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Solicitud #{{ $purchaseRequest->request_number }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Tipo:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-primary">
                                        {{ $purchaseRequest->type == 'purchase' ? 'Compras' : ucfirst($purchaseRequest->type) }}
                                    </span>
                                    <span class="badge badge-warning ml-1">Sin Cotización</span>
                                </dd>
                                <dt class="col-sm-4">Solicitante:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->requester }}</dd>
                                <dt class="col-sm-4">Área/Sección:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->section_area }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-success">{{ ucfirst($purchaseRequest->status) }}</span>
                                </dd>
                                <dt class="col-sm-4">Justificación:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->purchase_justification ?? 'No especificada' }}</dd>
                                <dt class="col-sm-4">Fecha Aprobación:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->approval_date ? $purchaseRequest->approval_date->format('d/m/Y H:i') : 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Items solicitados -->
                    @if($purchaseRequest->purchase_items && count($purchaseRequest->purchase_items) > 0)
                    <div class="mt-3">
                        <h6 class="text-primary">Items Solicitados:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Especificaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequest->purchase_items as $item)
                                    <tr>
                                        <td>{{ $item['description'] ?? 'Sin descripción' }}</td>
                                        <td>{{ $item['quantity'] ?? 1 }}</td>
                                        <td>{{ $item['specifications'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @php
                $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
            @endphp

            @if($existingOrder)
                <!-- Orden ya existe -->
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Orden de Compra Ya Creada</h5>
                    <p>Ya existe una orden de compra para esta solicitud:</p>
                    <p><strong>Orden #{{ $existingOrder->order_number }}</strong></p>
                    <a href="{{ route('purchase-orders.show', $existingOrder) }}" class="btn btn-success">
                        <i class="fas fa-eye"></i> Ver Orden de Compra
                    </a>
                </div>
            @else
                <!-- Formulario para crear orden -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-plus-circle mr-2"></i>Crear Orden de Compra Manual
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Orden sin cotización:</strong> Complete los datos del proveedor y los items manualmente. Esta funcionalidad está disponible para casos especiales donde la orden es autorizada sin cotización previa.
                        </div>

                        <form action="{{ route('purchase-orders.create-no-quotation-purchase', $purchaseRequest) }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <!-- Información del proveedor -->
                                <div class="col-md-6">
                                    <h6 class="text-primary">Información del Proveedor</h6>
                                    
                                    <!-- Selector de Proveedor -->
                                    <div class="form-group">
                                        <label for="provider_select">Seleccionar Proveedor</label>
                                        <select class="form-control" id="provider_select">
                                            <option value="">Seleccione un proveedor existente o complete manualmente</option>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" 
                                                        data-name="{{ $provider->nombre }}"
                                                        data-nit="{{ $provider->nit }}"
                                                        data-address="{{ $provider->direccion }}"
                                                        data-phone="{{ $provider->telefono }}"
                                                        data-email="{{ $provider->email }}">
                                                    {{ $provider->nombre }} ({{ $provider->nit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Si selecciona un proveedor existente, se completarán automáticamente los campos.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="provider_name">Nombre del Proveedor *</label>
                                        <input type="text" class="form-control @error('provider_name') is-invalid @enderror" 
                                               id="provider_name" name="provider_name" 
                                               value="{{ old('provider_name') }}" required>
                                        @error('provider_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_nit">NIT del Proveedor *</label>
                                        <input type="text" class="form-control @error('provider_nit') is-invalid @enderror" 
                                               id="provider_nit" name="provider_nit" 
                                               value="{{ old('provider_nit') }}" required>
                                        @error('provider_nit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_address">Dirección</label>
                                        <input type="text" class="form-control @error('provider_address') is-invalid @enderror" 
                                               id="provider_address" name="provider_address" 
                                               value="{{ old('provider_address') }}">
                                        @error('provider_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_phone">Teléfono</label>
                                        <input type="text" class="form-control @error('provider_phone') is-invalid @enderror" 
                                               id="provider_phone" name="provider_phone" 
                                               value="{{ old('provider_phone') }}">
                                        @error('provider_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_email">Email</label>
                                        <input type="email" class="form-control @error('provider_email') is-invalid @enderror" 
                                               id="provider_email" name="provider_email" 
                                               value="{{ old('provider_email') }}">
                                        @error('provider_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Información de la orden -->
                                <div class="col-md-6">
                                    <h6 class="text-primary">Información de la Orden</h6>
                                    
                                    <div class="form-group">
                                        <label for="subtotal_amount">Subtotal (sin impuestos) *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control @error('subtotal_amount') is-invalid @enderror" 
                                                   id="subtotal_amount" name="subtotal_amount" step="0.01" min="0"
                                                   value="{{ old('subtotal_amount') }}" required>
                                        </div>
                                        @error('subtotal_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Configuración de Impuestos -->
                                    <div class="form-group">
                                        <label class="form-label">Impuestos Aplicables</label>
                                        
                                        <!-- IVA 19% -->
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input tax-checkbox" id="includes_iva_19" name="includes_iva_19" 
                                                   value="1" {{ old('includes_iva_19') ? 'checked' : '' }} 
                                                   data-rate="19" onchange="calculateTotals()">
                                            <label class="form-check-label" for="includes_iva_19">
                                                Aplicar IVA (19%)
                                            </label>
                                        </div>

                                        <!-- IVA 5% -->
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input tax-checkbox" id="includes_iva_5" name="includes_iva_5" 
                                                   value="1" {{ old('includes_iva_5') ? 'checked' : '' }} 
                                                   data-rate="5" onchange="calculateTotals()">
                                            <label class="form-check-label" for="includes_iva_5">
                                                Aplicar IVA (5%)
                                            </label>
                                        </div>

                                        <!-- Ipoconsumo 8% -->
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input tax-checkbox" id="includes_ipoconsumo_8" name="includes_ipoconsumo_8" 
                                                   value="1" {{ old('includes_ipoconsumo_8') ? 'checked' : '' }} 
                                                   data-rate="8" onchange="calculateTotals()">
                                            <label class="form-check-label" for="includes_ipoconsumo_8">
                                                Aplicar Ipoconsumo (8%)
                                            </label>
                                        </div>

                                        <!-- Ipoconsumo 4% -->
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input tax-checkbox" id="includes_ipoconsumo_4" name="includes_ipoconsumo_4" 
                                                   value="1" {{ old('includes_ipoconsumo_4') ? 'checked' : '' }} 
                                                   data-rate="4" onchange="calculateTotals()">
                                            <label class="form-check-label" for="includes_ipoconsumo_4">
                                                Aplicar Ipoconsumo (4%)
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Montos calculados -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="iva_19_amount">IVA 19%</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="iva_19_amount" name="iva_19_amount" 
                                                           step="0.01" min="0" value="{{ old('iva_19_amount', 0) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="iva_5_amount">IVA 5%</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="iva_5_amount" name="iva_5_amount" 
                                                           step="0.01" min="0" value="{{ old('iva_5_amount', 0) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ipoconsumo_8_amount">Ipoconsumo 8%</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="ipoconsumo_8_amount" name="ipoconsumo_8_amount" 
                                                           step="0.01" min="0" value="{{ old('ipoconsumo_8_amount', 0) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ipoconsumo_4_amount">Ipoconsumo 4%</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="ipoconsumo_4_amount" name="ipoconsumo_4_amount" 
                                                           step="0.01" min="0" value="{{ old('ipoconsumo_4_amount', 0) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_amount">Total Final</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control bg-light font-weight-bold" id="total_amount" name="total_amount" 
                                                   step="0.01" min="0" value="{{ old('total_amount', 0) }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_terms">Términos de Pago *</label>
                                        <select class="form-control @error('payment_terms') is-invalid @enderror" 
                                                id="payment_terms" name="payment_terms" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Contado" {{ old('payment_terms') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                            <option value="30 días" {{ old('payment_terms') == '30 días' ? 'selected' : '' }}>30 días</option>
                                            <option value="45 días" {{ old('payment_terms') == '45 días' ? 'selected' : '' }}>45 días</option>
                                            <option value="60 días" {{ old('payment_terms') == '60 días' ? 'selected' : '' }}>60 días</option>
                                        </select>
                                        @error('payment_terms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="delivery_date">Fecha de Entrega *</label>
                                        <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                               id="delivery_date" name="delivery_date" 
                                               value="{{ old('delivery_date', now()->addDays(15)->format('Y-m-d')) }}" required>
                                        @error('delivery_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Items de la orden -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary">Items de la Orden</h6>
                                    <div class="alert alert-warning">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            Complete los detalles de cada item. Los precios deben coincidir con el subtotal especificado arriba.
                                        </small>
                                    </div>

                                    <div id="items-container">
                                        <!-- Los items se cargarán desde la solicitud o se pueden agregar manualmente -->
                                        @if($purchaseRequest->purchase_items && count($purchaseRequest->purchase_items) > 0)
                                            @foreach($purchaseRequest->purchase_items as $index => $item)
                                            <div class="item-row border rounded p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Descripción *</label>
                                                        <input type="text" class="form-control" 
                                                               name="items[{{ $index }}][description]" 
                                                               value="{{ old('items.'.$index.'.description', $item['description'] ?? '') }}" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Cantidad *</label>
                                                        <input type="number" class="form-control item-quantity" 
                                                               name="items[{{ $index }}][quantity]" 
                                                               value="{{ old('items.'.$index.'.quantity', $item['quantity'] ?? 1) }}" 
                                                               min="1" required onchange="calculateItemTotal(this)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Precio Unit.</label>
                                                        <input type="number" class="form-control item-price" 
                                                               name="items[{{ $index }}][unit_price]" 
                                                               value="{{ old('items.'.$index.'.unit_price', 0) }}" 
                                                               step="0.01" min="0" onchange="calculateItemTotal(this)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Total</label>
                                                        <input type="number" class="form-control item-total" 
                                                               name="items[{{ $index }}][total]" 
                                                               value="{{ old('items.'.$index.'.total', 0) }}" 
                                                               step="0.01" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeItem(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            <!-- Item por defecto si no hay items en la solicitud -->
                                            <div class="item-row border rounded p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Descripción *</label>
                                                        <input type="text" class="form-control" name="items[0][description]" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Cantidad *</label>
                                                        <input type="number" class="form-control item-quantity" name="items[0][quantity]" 
                                                               value="1" min="1" required onchange="calculateItemTotal(this)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Precio Unit.</label>
                                                        <input type="number" class="form-control item-price" name="items[0][unit_price]" 
                                                               value="0" step="0.01" min="0" onchange="calculateItemTotal(this)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Total</label>
                                                        <input type="number" class="form-control item-total" name="items[0][total]" 
                                                               value="0" step="0.01" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeItem(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-secondary btn-sm" onclick="addItem()">
                                        <i class="fas fa-plus"></i> Agregar Item
                                    </button>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="observations">Observaciones</label>
                                        <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', 'Orden autorizada sin cotización - Creación manual') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Crear Orden de Compra
                                    </button>
                                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    let itemIndex = {{ $purchaseRequest->purchase_items ? count($purchaseRequest->purchase_items) : 1 }};

    function calculateTotal() {
        const subtotal = parseFloat(document.getElementById('subtotal_amount').value) || 0;
        const includesIva = document.getElementById('includes_iva').checked;
        
        let iva = 0;
        if (includesIva) {
            iva = subtotal * 0.19;
        }
        
        const total = subtotal + iva;
        
        document.getElementById('iva_amount').value = iva.toFixed(2);
        document.getElementById('total_amount').value = total.toFixed(2);
    }

    // Función para cargar datos del proveedor seleccionado
    function loadProviderData() {
        const providerSelect = document.getElementById('provider_select');
        const selectedOption = providerSelect.options[providerSelect.selectedIndex];
        
        if (selectedOption.value) {
            document.getElementById('provider_name').value = selectedOption.dataset.name || '';
            document.getElementById('provider_nit').value = selectedOption.dataset.nit || '';
            document.getElementById('provider_address').value = selectedOption.dataset.address || '';
            document.getElementById('provider_phone').value = selectedOption.dataset.phone || '';
            document.getElementById('provider_email').value = selectedOption.dataset.email || '';
        }
    }

    // Función para calcular todos los totales
    function calculateTotals() {
        const subtotal = parseFloat(document.getElementById('subtotal_amount').value) || 0;
        
        // Calcular IVA 19%
        let iva19Amount = 0;
        const includesIva19 = document.getElementById('includes_iva_19').checked;
        if (includesIva19) {
            iva19Amount = subtotal * 0.19;
        }
        
        // Calcular IVA 5%
        let iva5Amount = 0;
        const includesIva5 = document.getElementById('includes_iva_5').checked;
        if (includesIva5) {
            iva5Amount = subtotal * 0.05;
        }
        
        // Calcular Ipoconsumo 8%
        let ipoconsumo8Amount = 0;
        const includesIpoconsumo8 = document.getElementById('includes_ipoconsumo_8').checked;
        if (includesIpoconsumo8) {
            ipoconsumo8Amount = subtotal * 0.08;
        }
        
        // Calcular Ipoconsumo 4%
        let ipoconsumo4Amount = 0;
        const includesIpoconsumo4 = document.getElementById('includes_ipoconsumo_4').checked;
        if (includesIpoconsumo4) {
            ipoconsumo4Amount = subtotal * 0.04;
        }
        
        // Calcular total
        const totalAmount = subtotal + iva19Amount + iva5Amount + ipoconsumo8Amount + ipoconsumo4Amount;
        
        // Actualizar campos
        document.getElementById('iva_19_amount').value = iva19Amount.toFixed(2);
        document.getElementById('iva_5_amount').value = iva5Amount.toFixed(2);
        document.getElementById('ipoconsumo_8_amount').value = ipoconsumo8Amount.toFixed(2);
        document.getElementById('ipoconsumo_4_amount').value = ipoconsumo4Amount.toFixed(2);
        document.getElementById('total_amount').value = totalAmount.toFixed(2);
    }

    function calculateItemTotal(element) {
        const row = element.closest('.item-row');
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const total = quantity * price;
        
        row.querySelector('.item-total').value = total.toFixed(2);
        
        // Opcional: actualizar el subtotal automáticamente basado en la suma de items
        updateSubtotalFromItems();
    }

    function updateSubtotalFromItems() {
        let totalItems = 0;
        document.querySelectorAll('.item-total').forEach(function(input) {
            totalItems += parseFloat(input.value) || 0;
        });
        
        document.getElementById('subtotal_amount').value = totalItems.toFixed(2);
        calculateTotals();
    }

    function addItem() {
        const container = document.getElementById('items-container');
        const newItem = document.createElement('div');
        newItem.className = 'item-row border rounded p-3 mb-3';
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label>Descripción *</label>
                    <input type="text" class="form-control" name="items[${itemIndex}][description]" required>
                </div>
                <div class="col-md-2">
                    <label>Cantidad *</label>
                    <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                           value="1" min="1" required onchange="calculateItemTotal(this)">
                </div>
                <div class="col-md-2">
                    <label>Precio Unit.</label>
                    <input type="number" class="form-control item-price" name="items[${itemIndex}][unit_price]" 
                           value="0" step="0.01" min="0" onchange="calculateItemTotal(this)">
                </div>
                <div class="col-md-2">
                    <label>Total</label>
                    <input type="number" class="form-control item-total" name="items[${itemIndex}][total]" 
                           value="0" step="0.01" readonly>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(newItem);
        itemIndex++;
    }

    function removeItem(button) {
        const itemRows = document.querySelectorAll('.item-row');
        if (itemRows.length > 1) {
            button.closest('.item-row').remove();
            updateSubtotalFromItems();
        } else {
            alert('Debe mantener al menos un item en la orden.');
        }
    }

    // Calcular total al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotals();
        
        // Calcular totales de items existentes
        document.querySelectorAll('.item-quantity, .item-price').forEach(function(input) {
            calculateItemTotal(input);
        });
        
        // Listener para el campo subtotal
        document.getElementById('subtotal_amount').addEventListener('input', calculateTotals);
        
        // Listener para el selector de proveedor
        document.getElementById('provider_select').addEventListener('change', loadProviderData);
        
        // Listener para el campo de tasa de impuesto adicional
        document.getElementById('tax_rate').addEventListener('input', function() {
            updateTaxRate();
            calculateTotals();
        });
        
        // Limpiar campos del proveedor al cambiar manualmente
        ['provider_name', 'provider_nit', 'provider_address', 'provider_phone', 'provider_email'].forEach(function(fieldId) {
            document.getElementById(fieldId).addEventListener('input', function() {
                document.getElementById('provider_select').value = '';
            });
        });
    });
</script>
@stop
