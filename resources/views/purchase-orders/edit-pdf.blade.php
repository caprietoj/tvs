@extends('adminlte::page')

@section('title', 'Editar PDF - Orden de Compra')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Editar PDF de Orden de Compra</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}">Detalle</a></li>
            <li class="breadcrumb-item active">Editar PDF</li>
        </ol>
    </div>
</div>
@stop

@section('content')
@php
    // Definir $customData al principio para usarlo en toda la vista
    $customData = $purchaseOrder->pdf_custom_data ? json_decode($purchaseOrder->pdf_custom_data, true) : null;
@endphp

<div class="container-fluid">
    <!-- Alertas de éxito y error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Por favor corrija los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Editar PDF de Orden de Compra #{{ $purchaseOrder->order_number }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>¡Atención!</strong> Esta función está disponible solo para administradores. 
                        Los cambios aquí realizados modificarán permanentemente el PDF de la orden de compra.
                        Se recomienda tomar un respaldo antes de realizar modificaciones.
                    </div>
                    
                    <form action="{{ route('purchase-orders.update-pdf', $purchaseOrder->id) }}" method="POST" id="editPdfForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información General -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order_number">Número de Orden <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('order_number') is-invalid @enderror" 
                                           id="order_number" name="order_number" 
                                           value="{{ old('order_number', $purchaseOrder->order_number) }}" required>
                                    @error('order_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order_date">Fecha de Orden <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                           id="order_date" name="order_date" 
                                           value="{{ old('order_date', optional($purchaseOrder->order_date)->format('Y-m-d')) }}" required>
                                    @error('order_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información del Proveedor -->
                        <h5 class="mt-4 mb-3"><i class="fas fa-building mr-2"></i>Información del Proveedor</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_name">Nombre del Proveedor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('provider_name') is-invalid @enderror" 
                                           id="provider_name" name="provider_name" 
                                           value="{{ old('provider_name', $purchaseOrder->provider->nombre ?? '') }}" required>
                                    @error('provider_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_nit">NIT del Proveedor</label>
                                    <input type="text" class="form-control @error('provider_nit') is-invalid @enderror" 
                                           id="provider_nit" name="provider_nit" 
                                           value="{{ old('provider_nit', $purchaseOrder->provider->nit ?? '') }}">
                                    @error('provider_nit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_email">Email del Proveedor</label>
                                    <input type="email" class="form-control @error('provider_email') is-invalid @enderror" 
                                           id="provider_email" name="provider_email" 
                                           value="{{ old('provider_email', $purchaseOrder->provider->email ?? '') }}">
                                    @error('provider_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_phone">Teléfono del Proveedor</label>
                                    <input type="text" class="form-control @error('provider_phone') is-invalid @enderror" 
                                           id="provider_phone" name="provider_phone" 
                                           value="{{ old('provider_phone', $purchaseOrder->provider->telefono ?? '') }}">
                                    @error('provider_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="provider_address">Dirección del Proveedor</label>
                            <textarea class="form-control @error('provider_address') is-invalid @enderror" 
                                      id="provider_address" name="provider_address" rows="2">{{ old('provider_address', $purchaseOrder->provider->direccion ?? '') }}</textarea>
                            @error('provider_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Términos y Condiciones -->
                        <h5 class="mt-4 mb-3"><i class="fas fa-file-contract mr-2"></i>Términos y Condiciones</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega</label>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                           id="delivery_date" name="delivery_date" 
                                           value="{{ old('delivery_date', optional($purchaseOrder->delivery_date)->format('Y-m-d')) }}">
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_terms">Términos de Pago</label>
                                    <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                           id="payment_terms" name="payment_terms" 
                                           value="{{ old('payment_terms', $purchaseOrder->payment_terms ?? '') }}">
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items de la Orden -->
                        <h5 class="mt-4 mb-3"><i class="fas fa-list mr-2"></i>Items de la Orden</h5>
                        
                        <div id="itemsContainer">
                            @php
                                $items = old('items', $purchaseOrder->pdf_custom_data ? 
                                    json_decode($purchaseOrder->pdf_custom_data, true)['items'] ?? [] : 
                                    []);
                                
                                // Si no hay items personalizados, usar los datos de la solicitud original
                                if (empty($items) && $purchaseOrder->purchaseRequest) {
                                    // Primero intentar con selecciones mixtas
                                    if ($purchaseOrder->purchaseRequest->quotationItemSelections->count() > 0) {
                                        $items = $purchaseOrder->purchaseRequest->quotationItemSelections->map(function($selection) {
                                            return [
                                                'description' => $selection->quotation->description ?? 'Descripción no disponible',
                                                'quantity' => $selection->quantity,
                                                'unit_price' => $selection->unit_price,
                                                'total_price' => $selection->total_price
                                            ];
                                        })->toArray();
                                    }
                                    // Si no hay selecciones mixtas, intentar con cotización seleccionada
                                    elseif ($purchaseOrder->purchaseRequest->selectedQuotation) {
                                        $quotation = $purchaseOrder->purchaseRequest->selectedQuotation;
                                        if (isset($quotation->additional_items) && is_array($quotation->additional_items)) {
                                            $items = array_map(function($item) {
                                                return [
                                                    'description' => $item['description'] ?? 'Descripción no disponible',
                                                    'quantity' => $item['quantity'] ?? 1,
                                                    'unit_price' => $item['unit_price'] ?? $item['price'] ?? 0,
                                                    'total_price' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0)
                                                ];
                                            }, $quotation->additional_items);
                                        }
                                    }
                                    // Si no hay items específicos, crear uno básico con el total de la orden
                                    if (empty($items)) {
                                        $items = [[
                                            'description' => 'Servicios/Productos según solicitud',
                                            'quantity' => 1,
                                            'unit_price' => $purchaseOrder->subtotal ?? 0,
                                            'total_price' => $purchaseOrder->subtotal ?? 0
                                        ]];
                                    }
                                }
                                
                                // Si aún no hay items, crear uno vacío
                                if (empty($items)) {
                                    $items = [[
                                        'description' => '',
                                        'quantity' => 1,
                                        'unit_price' => 0,
                                        'total_price' => 0
                                    ]];
                                }
                            @endphp
                            
                            @foreach($items as $index => $item)
                                <div class="item-row border p-3 mb-3 rounded">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Descripción <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="items[{{ $index }}][description]" 
                                                          rows="2" required>{{ $item['description'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control item-quantity" 
                                                       name="items[{{ $index }}][quantity]" 
                                                       value="{{ $item['quantity'] ?? 1 }}" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Unitario <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control item-unit-price" 
                                                       name="items[{{ $index }}][unit_price]" 
                                                       value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input type="number" class="form-control item-total-price" 
                                                       name="items[{{ $index }}][total_price]" 
                                                       value="{{ $item['total_price'] ?? 0 }}" min="0" step="0.01" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-block remove-item">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="button" id="addItem" class="btn btn-success mb-4">
                            <i class="fas fa-plus mr-2"></i>Agregar Item
                        </button>

                        <!-- Totales -->
                        <h5 class="mt-4 mb-3"><i class="fas fa-calculator mr-2"></i>Totales</h5>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subtotal">Subtotal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('subtotal') is-invalid @enderror" 
                                           id="subtotal" name="subtotal" 
                                           value="{{ old('subtotal', $customData['subtotal'] ?? $purchaseOrder->subtotal ?? 0) }}" 
                                           min="0" step="0.01" required readonly>
                                    @error('subtotal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="iva_rate">Tasa de IVA (%)</label>
                                    <select class="form-control" id="iva_rate" name="iva_rate">
                                        @php
                                            $savedIvaRate = $customData['iva_rate'] ?? null;
                                            
                                            // Si no hay tasa guardada, calcular basándose en el monto actual
                                            if ($savedIvaRate === null && $purchaseOrder->iva_amount > 0 && $purchaseOrder->subtotal > 0) {
                                                $calculatedRate = ($purchaseOrder->iva_amount / $purchaseOrder->subtotal) * 100;
                                                if (abs($calculatedRate - 5) < 1) {
                                                    $savedIvaRate = 5;
                                                } elseif (abs($calculatedRate - 19) < 1) {
                                                    $savedIvaRate = 19;
                                                } else {
                                                    $savedIvaRate = 0;
                                                }
                                            } elseif ($savedIvaRate === null) {
                                                $savedIvaRate = 0;
                                            }
                                        @endphp
                                        <option value="0" {{ $savedIvaRate == 0 ? 'selected' : '' }}>Sin IVA (0%)</option>
                                        <option value="5" {{ $savedIvaRate == 5 ? 'selected' : '' }}>IVA 5%</option>
                                        <option value="19" {{ $savedIvaRate == 19 ? 'selected' : '' }}>IVA 19%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax_amount">Valor IVA <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('tax_amount') is-invalid @enderror" 
                                           id="tax_amount" name="tax_amount" 
                                           value="{{ old('tax_amount', $purchaseOrder->iva_amount ?? 0) }}" 
                                           min="0" step="0.01" required readonly>
                                    @error('tax_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="total_amount">Total <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('total_amount') is-invalid @enderror" 
                                           id="total_amount" name="total_amount" 
                                           value="{{ old('total_amount', $customData['total'] ?? $purchaseOrder->total_amount ?? 0) }}" 
                                           min="0" step="0.01" required readonly>
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                      id="observations" name="observations" rows="3">{{ old('observations', $purchaseOrder->observations ?? '') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-2"></i>Actualizar PDF
                            </button>
                            <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Cancelar
                            </a>
                            <a href="{{ route('purchase-orders.view', $purchaseOrder->id) }}" class="btn btn-info" target="_blank">
                                <i class="fas fa-eye mr-2"></i>Ver PDF Actual
                            </a>
                        </div>
                        
                        <!-- Información adicional sobre la orden -->
                        <div class="alert alert-info mt-3">
                            <h6><i class="fas fa-info-circle mr-2"></i>Información de la Orden</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <strong>Solicitud asociada:</strong> {{ $purchaseOrder->purchaseRequest->request_number ?? 'N/A' }}<br>
                                        <strong>Solicitante:</strong> {{ $purchaseOrder->purchaseRequest->requester ?? 'N/A' }}<br>
                                        <strong>Estado actual:</strong> 
                                        <span class="badge badge-{{ $purchaseOrder->status === 'pending' ? 'warning' : ($purchaseOrder->status === 'approved' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($purchaseOrder->status) }}
                                        </span>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small>
                                        <strong>Creada:</strong> {{ $purchaseOrder->created_at->format('d/m/Y H:i') }}<br>
                                        <strong>Última actualización:</strong> {{ $purchaseOrder->updated_at->format('d/m/Y H:i') }}<br>
                                        @if($purchaseOrder->pdf_custom_data)
                                            <strong>PDF personalizado:</strong> <span class="text-success">Sí</span>
                                        @else
                                            <strong>PDF personalizado:</strong> <span class="text-muted">No</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let itemIndex = {{ count($items) }};
    
    // Agregar nuevo item
    $('#addItem').click(function() {
        const itemHtml = `
            <div class="item-row border p-3 mb-3 rounded">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Descripción <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="items[${itemIndex}][description]" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Precio Unitario <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-unit-price" name="items[${itemIndex}][unit_price]" value="0" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
                            <input type="number" class="form-control item-total-price" name="items[${itemIndex}][total_price]" value="0" min="0" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-block remove-item">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#itemsContainer').append(itemHtml);
        itemIndex++;
        calculateTotals();
    });
    
    // Eliminar item
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calculateTotals();
        } else {
            alert('Debe mantener al menos un item en la orden.');
        }
    });
    
    // Calcular totales cuando cambian cantidad o precio
    $(document).on('input', '.item-quantity, .item-unit-price', function() {
        const row = $(this).closest('.item-row');
        const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
        const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
        const total = quantity * unitPrice;
        
        row.find('.item-total-price').val(total.toFixed(2));
        calculateTotals();
    });
    
    // Calcular IVA cuando cambia la tasa
    $('#iva_rate').change(function() {
        calculateTotals();
    });
    
    // Función para calcular totales generales
    function calculateTotals() {
        let subtotal = 0;
        
        $('.item-total-price').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        const ivaRate = parseFloat($('#iva_rate').val()) / 100 || 0;
        const taxAmount = subtotal * ivaRate;
        const total = subtotal + taxAmount;
        
        $('#subtotal').val(subtotal.toFixed(2));
        $('#tax_amount').val(taxAmount.toFixed(2));
        $('#total_amount').val(total.toFixed(2));
        
        // Actualizar visual feedback
        updateTotalDisplay(subtotal, taxAmount, total);
    }
    
    // Función para actualizar display visual de totales
    function updateTotalDisplay(subtotal, tax, total) {
        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        });
        
        // Añadir tooltips con formato de moneda
        $('#subtotal').attr('title', 'Subtotal: ' + formatter.format(subtotal));
        $('#tax_amount').attr('title', 'IVA: ' + formatter.format(tax));
        $('#total_amount').attr('title', 'Total: ' + formatter.format(total));
    }
    
    // Cargar datos del proveedor desde la base de datos
    function loadProviderData() {
        const providerId = {{ $purchaseOrder->provider_id ?? 'null' }};
        if (providerId) {
            // Los datos ya están cargados desde el servidor
            console.log('Datos del proveedor cargados desde el servidor');
        }
    }
    
    // Validación mejorada del formulario
    $('#editPdfForm').submit(function(e) {
        // Validar que hay al menos un item
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un item a la orden de compra.');
            return false;
        }
        
        // Validar que todos los items tienen descripción
        let hasEmptyDescription = false;
        $('.item-row textarea[name*="[description]"]').each(function() {
            if ($(this).val().trim() === '') {
                hasEmptyDescription = true;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (hasEmptyDescription) {
            e.preventDefault();
            alert('Todos los items deben tener una descripción.');
            return false;
        }
        
        // Validar totales
        const total = parseFloat($('#total_amount').val());
        if (total <= 0) {
            e.preventDefault();
            alert('El total de la orden debe ser mayor a cero.');
            return false;
        }
        
        // Confirmación final
        if (!confirm('¿Está seguro de que desea actualizar el PDF de esta orden de compra?\n\nEsta acción modificará permanentemente el PDF y no se puede deshacer.')) {
            e.preventDefault();
            return false;
        }
        
        // Mostrar loading
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...');
    });
    
    // Inicializar
    loadProviderData();
    calculateTotals();
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-resize para textareas
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Formatear números mientras se escriben
    $('.item-unit-price, #subtotal, #tax_amount, #total_amount').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
    
    console.log('Vista de edición de PDF inicializada correctamente');
    console.log('Items cargados:', $('.item-row').length);
    console.log('Proveedor:', $('#provider_name').val());
});
</script>
@stop

@section('css')
<style>
.item-row {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6 !important;
    transition: all 0.3s ease;
}

.item-row:hover {
    background-color: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-warning .card-header {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
    font-weight: 600;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
}

.item-total-price {
    background-color: #e9ecef;
    font-weight: bold;
}

#subtotal, #tax_amount, #total_amount {
    background-color: #f8f9fa;
    font-weight: bold;
    font-size: 1.1em;
}

#total_amount {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

h5 {
    color: #495057;
    border-bottom: 2px solid #ffc107;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #495057;
    text-decoration: underline;
}

.btn-group .btn + .btn {
    margin-left: 10px;
}

.is-invalid {
    animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}

.tooltip-inner {
    max-width: 300px;
    font-size: 0.9em;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-md-2, .col-md-3, .col-md-4, .col-md-6 {
        margin-bottom: 15px;
    }
    
    .btn-block {
        margin-top: 10px;
    }
}

/* Custom scrollbar for textareas */
textarea::-webkit-scrollbar {
    width: 8px;
}

textarea::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@stop
