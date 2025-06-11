@extends('adminlte::page')

@section('title', 'Crear Orden de Compra')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Crear Orden de Compra</h1>
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
                                    <span class="badge {{ $purchaseRequest->type === 'purchase' ? 'badge-primary' : 'badge-info' }}">
                                        {{ $purchaseRequest->type === 'purchase' ? 'Compra' : 'Materiales' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Solicitante:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->requester }}</dd>

                                <dt class="col-sm-4">Área/Sección:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->section_area }}</dd>

                                <dt class="col-sm-4">Fecha de solicitud:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->created_at->format('d/m/Y') }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Cotización seleccionada:</dt>
                                <dd class="col-sm-8">
                                    {{ $purchaseRequest->selectedQuotation ? $purchaseRequest->selectedQuotation->provider_name : 'N/A' }}
                                </dd>

                                <dt class="col-sm-4">Monto:</dt>
                                <dd class="col-sm-8">
                                    @if($purchaseRequest->selectedQuotation)
                                        ${{ number_format($purchaseRequest->selectedQuotation->total_amount, 2, ',', '.') }}
                                    @else
                                        N/A
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Aprobado por:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->approver ? $purchaseRequest->approver->name : 'N/A' }}</dd>

                                <dt class="col-sm-4">Fecha de aprobación:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->approval_date ? $purchaseRequest->approval_date->format('d/m/Y') : 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Orden de Compra</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> La orden de compra se generará automáticamente utilizando la plantilla del sistema una vez completado este formulario.
                    </div>
                    
                    <form action="{{ route('purchase-orders.store', $purchaseRequest->id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order_number">Número de Orden</label>
                                    <input type="text" class="form-control" value="Se generará automáticamente" disabled>
                                    <small class="text-muted">El sistema generará automáticamente un número de orden consecutivo.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="provider_id">Proveedor</label>
                                    @if($purchaseRequest->selectedQuotation && isset($purchaseRequest->selectedQuotation->provider_id))
                                        <input type="text" class="form-control" value="{{ \App\Models\Proveedor::find($purchaseRequest->selectedQuotation->provider_id)->nombre ?? $purchaseRequest->selectedQuotation->provider_name }}" disabled>
                                        <input type="hidden" name="provider_id" value="{{ $purchaseRequest->selectedQuotation->provider_id }}">
                                    @else
                                        <select class="form-control select2 @error('provider_id') is-invalid @enderror" id="provider_id" name="provider_id" required>
                                            <option value="">Seleccione un proveedor...</option>
                                            @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $provider)
                                                <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id || (isset($purchaseRequest->selectedQuotation->provider_name) && $provider->nombre == $purchaseRequest->selectedQuotation->provider_name) ? 'selected' : '' }}>
                                                    {{ $provider->nombre }} - {{ $provider->nit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <small class="text-muted">El proveedor se toma automáticamente de la cotización seleccionada.</small>
                                    @error('provider_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_terms">Términos de Pago</label>
                                    @if($purchaseRequest->selectedQuotation && $purchaseRequest->selectedQuotation->payment_method)
                                        <input type="text" class="form-control" value="{{ $purchaseRequest->selectedQuotation->payment_method }}" disabled>
                                        <input type="hidden" name="payment_terms" value="{{ $purchaseRequest->selectedQuotation->payment_method }}">
                                    @else
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" id="payment_terms" name="payment_terms" value="{{ old('payment_terms') }}" required>
                                    @endif
                                    <small class="text-muted">Los términos de pago se toman automáticamente de la cotización seleccionada.</small>
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required>
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="apply_iva">Aplicar IVA 19%</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="apply_iva" name="apply_iva" value="1" {{ old('apply_iva') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="apply_iva">Incluir IVA del 19% en el precio total</label>
                                    </div>
                                    <small class="text-muted">Marque esta opción si el precio debe incluir IVA.</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Items de la cotización original -->
                        <div class="card mt-4 mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Items de la Solicitud Original</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Unidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($purchaseRequest->purchase_items) && is_array($purchaseRequest->purchase_items))
                                                @foreach($purchaseRequest->purchase_items as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $item['description'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] ?? '1' }}</td>
                                                        <td>{{ $item['unit'] ?? 'Unidad' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay items en la solicitud original</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Items adicionales para la orden de compra -->
                        <div class="card mt-4 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Items Adicionales</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Agregue items adicionales si la orden de compra debe incluir más productos que los especificados en la solicitud original.
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="items-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 40%">Descripción</th>
                                                <th style="width: 15%">Cantidad</th>
                                                <th style="width: 15%">Unidad</th>
                                                <th style="width: 15%">Precio Unitario</th>
                                                <th style="width: 10%">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-container">
                                            <!-- Los items adicionales se agregarán aquí dinámicamente -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6">
                                                    <button type="button" class="btn btn-success btn-sm" id="add-item-btn">
                                                        <i class="fas fa-plus-circle"></i> Añadir Item
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observations">Observaciones</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Resumen de Costos</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Subtotal Cotización:</th>
                                                    <td>
                                                        $<span id="quotation-amount">{{ $purchaseRequest->selectedQuotation ? number_format($purchaseRequest->selectedQuotation->total_amount, 2, '.', ',') : '0.00' }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Subtotal Items Adicionales:</th>
                                                    <td>$<span id="additional-items-amount">0.00</span></td>
                                                </tr>
                                                <tr id="iva-row" class="d-none">
                                                    <th>IVA (19%):</th>
                                                    <td>$<span id="iva-amount">0.00</span></td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <th>TOTAL:</th>
                                                    <td>
                                                        $<span id="total-display">{{ $purchaseRequest->selectedQuotation ? number_format($purchaseRequest->selectedQuotation->total_amount, 2, '.', ',') : '0.00' }}</span>
                                                        <input type="hidden" id="total_amount" name="total_amount" value="{{ $purchaseRequest->selectedQuotation ? $purchaseRequest->selectedQuotation->total_amount : '0' }}">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Generar Orden de Compra
                            </button>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<style>
    .remove-item:hover {
        cursor: pointer;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione una opción',
            allowClear: true
        });
        
        // Fecha mínima de entrega (hoy)
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;
        document.getElementById("delivery_date").min = today;
        
        // Variables para el contador de items adicionales
        let itemCounter = 0;
        
        // Agregar un item adicional
        $('#add-item-btn').click(function() {
            addNewItem();
        });
        
        // Eliminar un item adicional
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            updateItemNumbers();
            calculateTotals();
        });
        
        // Actualizar totales cuando se marca/desmarca el IVA
        $('#apply_iva').change(function() {
            calculateTotals();
        });
        
        // Función para agregar un nuevo item
        function addNewItem() {
            itemCounter++;
            
            const newRow = `
                <tr id="item-row-${itemCounter}">
                    <td class="item-number">${itemCounter}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="additional_items[${itemCounter}][description]" placeholder="Descripción del item">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-quantity" name="additional_items[${itemCounter}][quantity]" min="1" value="1" onchange="calculateTotals()">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="additional_items[${itemCounter}][unit]" value="Unidad">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control form-control-sm item-price" name="additional_items[${itemCounter}][price]" min="0" step="0.01" value="0" onchange="calculateTotals()">
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            // Añadir la fila al contenedor
            $('#items-container').append(newRow);
            
            // Actualizar totales
            calculateTotals();
        }
        
        // Función para actualizar los números de los items
        function updateItemNumbers() {
            let index = 1;
            $('#items-container tr').each(function() {
                $(this).find('.item-number').text(index);
                index++;
            });
        }
        
        // Añadir un item inicial
        addNewItem();
    });
    
    // Función para calcular los totales
    function calculateTotals() {
        // Obtener el monto de la cotización
        let quotationAmount = parseFloat('{{ $purchaseRequest->selectedQuotation ? $purchaseRequest->selectedQuotation->total_amount : 0 }}');
        
        // Calcular el subtotal de items adicionales
        let additionalItemsTotal = 0;
        $('#items-container tr').each(function() {
            const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            additionalItemsTotal += (quantity * price);
        });
        
        // Subtotal (cotización + items adicionales)
        let subtotal = quotationAmount + additionalItemsTotal;
        
        // Verificar si se aplica IVA
        let total = subtotal;
        let ivaAmount = 0;
        
        if($('#apply_iva').is(':checked')) {
            ivaAmount = subtotal * 0.19;
            total = subtotal + ivaAmount;
            $('#iva-row').removeClass('d-none');
        } else {
            $('#iva-row').addClass('d-none');
        }
        
        // Actualizar los valores en la UI
        $('#quotation-amount').text(quotationAmount.toFixed(2));
        $('#additional-items-amount').text(additionalItemsTotal.toFixed(2));
        $('#iva-amount').text(ivaAmount.toFixed(2));
        $('#total-display').text(total.toFixed(2));
        $('#total_amount').val(total.toFixed(2));
    }
</script>
@stop