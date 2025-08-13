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
                                    @if($purchaseRequest->type === 'purchase')
                                        <span class="badge badge-primary">Compra</span>
                                    @elseif($purchaseRequest->type === 'services')
                                        <span class="badge badge-success">Servicios</span>
                                        @if(isset($isNoQuotationService) && $isNoQuotationService)
                                            <span class="badge badge-warning ml-1">Sin Cotización</span>
                                        @endif
                                    @else
                                        <span class="badge badge-info">Materiales</span>
                                    @endif
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
                    
                    <form action="{{ route('purchase-orders.store', $purchaseRequest->id) }}" method="POST" id="order-form">
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
                                    @if(isset($isNoQuotationService) && $isNoQuotationService)
                                        <!-- Caso de servicio sin cotización -->
                                        <div class="card">
                                            <div class="card-header bg-warning">
                                                <h6 class="m-0 text-dark">
                                                    <i class="fas fa-hand-holding-usd mr-2"></i>
                                                    Servicio sin Cotización
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Proveedor:</dt>
                                                    <dd class="col-sm-8">{{ $purchaseRequest->provider_name }}</dd>
                                                    
                                                    <dt class="col-sm-4">NIT:</dt>
                                                    <dd class="col-sm-8">{{ $purchaseRequest->provider_nit ?? 'N/A' }}</dd>
                                                    
                                                    <dt class="col-sm-4">Contacto:</dt>
                                                    <dd class="col-sm-8">{{ $purchaseRequest->provider_contact ?? 'N/A' }}</dd>
                                                    
                                                    <dt class="col-sm-4">Email:</dt>
                                                    <dd class="col-sm-8">{{ $purchaseRequest->provider_email ?? 'N/A' }}</dd>
                                                    
                                                    <dt class="col-sm-4">Presupuesto:</dt>
                                                    <dd class="col-sm-8">
                                                        @if($purchaseRequest->service_budget)
                                                            ${{ number_format($purchaseRequest->service_budget, 2) }}
                                                        @else
                                                            {{ $purchaseRequest->service_budget_text ?? 'N/A' }}
                                                        @endif
                                                    </dd>
                                                </dl>
                                                <small class="text-muted">
                                                    Este servicio no requiere cotización. El proveedor y presupuesto fueron especificados en la solicitud.
                                                </small>
                                            </div>
                                        </div>
                                    @elseif(isset($hasMixedSelection) && $hasMixedSelection)
                                        <!-- Caso de selección mixta -->
                                        <div class="card">
                                            <div class="card-header bg-info">
                                                <h6 class="m-0 text-white">
                                                    <i class="fas fa-balance-scale mr-2"></i>
                                                    Selección Mixta de Proveedores
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Proveedor</th>
                                                                <th>Cantidad</th>
                                                                <th>Precio Unit.</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($mixedSelections as $selection)
                                                                <tr>
                                                                    <td>{{ $selection->item_description }}</td>
                                                                    <td>{{ $selection->quotation->provider_name }}</td>
                                                                    <td>{{ $selection->quantity }}</td>
                                                                    <td>${{ number_format($selection->unit_price, 2) }}</td>
                                                                    <td>${{ number_format($selection->total_price, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light">
                                                                <th colspan="4">Total:</th>
                                                                <th>${{ number_format($mixedSelections->sum('total_price'), 2) }}</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Información importante:</strong><br>
                                                    Se generará una orden de compra separada para cada proveedor seleccionado en la tabla anterior. 
                                                    Total de órdenes a crear: <strong>{{ $mixedSelections->groupBy('quotation.provider_name')->count() }}</strong>
                                                </div>
                                                <small class="text-muted">
                                                    Esta selección mixta incluye productos de múltiples proveedores. Cada proveedor recibirá su propia orden de compra.
                                                </small>
                                            </div>
                                        </div>
                                    @elseif($purchaseRequest->selectedQuotation && isset($purchaseRequest->selectedQuotation->provider_id))
                                        <!-- Caso de cotización tradicional con proveedor conocido -->
                                        <input type="text" class="form-control" value="{{ \App\Models\Proveedor::find($purchaseRequest->selectedQuotation->provider_id)->nombre ?? $purchaseRequest->selectedQuotation->provider_name }}" disabled>
                                        <input type="hidden" name="provider_id" value="{{ $purchaseRequest->selectedQuotation->provider_id }}">
                                    @else
                                        <!-- Caso de cotización sin proveedor asignado -->
                                        <select class="form-control select2 @error('provider_id') is-invalid @enderror" id="provider_id" name="provider_id" required>
                                            <option value="">Seleccione un proveedor...</option>
                                            @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $provider)
                                                <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id || (isset($purchaseRequest->selectedQuotation->provider_name) && $provider->nombre == $purchaseRequest->selectedQuotation->provider_name) ? 'selected' : '' }}>
                                                    {{ $provider->nombre }} - {{ $provider->nit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    
                                    @if(!isset($hasMixedSelection) || !$hasMixedSelection)
                                        <small class="text-muted">El proveedor se toma automáticamente de la cotización seleccionada.</small>
                                    @endif
                                    
                                    @error('provider_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_terms">Términos de Pago</label>
                                    @if(isset($isNoQuotationService) && $isNoQuotationService)
                                        <!-- Para servicios sin cotización -->
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                               id="payment_terms" name="payment_terms" 
                                               value="{{ old('payment_terms', 'Contado') }}" required>
                                        <small class="text-muted">Para servicios sin cotización, se sugiere pago de contado.</small>
                                    @elseif(isset($hasMixedSelection) && $hasMixedSelection)
                                        <!-- Para selección mixta, permitir entrada manual -->
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                               id="payment_terms" name="payment_terms" 
                                               value="{{ old('payment_terms', 'Según condiciones por proveedor') }}" required>
                                        <small class="text-muted">Para selección mixta, especifique los términos generales de pago.</small>
                                    @elseif($purchaseRequest->selectedQuotation && $purchaseRequest->selectedQuotation->payment_method)
                                        <!-- Para cotización tradicional -->
                                        <input type="text" class="form-control" value="{{ $purchaseRequest->selectedQuotation->payment_method }}" disabled>
                                        <input type="hidden" name="payment_terms" value="{{ $purchaseRequest->selectedQuotation->payment_method }}">
                                        <small class="text-muted">Los términos de pago se toman automáticamente de la cotización seleccionada.</small>
                                    @else
                                        <!-- Para otros casos -->
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" id="payment_terms" name="payment_terms" value="{{ old('payment_terms') }}" required>
                                        <small class="text-muted">Especifique los términos de pago para esta orden.</small>
                                    @endif
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega <span class="text-danger">*</span></label>
                                    @php
                                        $defaultDate = isset($isNoQuotationService) && $isNoQuotationService 
                                            ? now()->addDays(30)->format('Y-m-d') 
                                            : now()->addDays(15)->format('Y-m-d');
                                    @endphp
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                           id="delivery_date" name="delivery_date" 
                                           value="{{ old('delivery_date', $defaultDate) }}" required>
                                    @if(isset($isNoQuotationService) && $isNoQuotationService)
                                        <small class="text-muted">Para servicios sin cotización se sugieren 30 días.</small>
                                    @endif
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3" placeholder="Observaciones adicionales para la orden de compra">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        
                        <!-- Información de la cotización seleccionada -->
                        <div class="card mt-4 mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Información de la Cotización Seleccionada</h5>
                            </div>
                            <div class="card-body">
                                @if($purchaseRequest->selectedQuotation)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <dl class="row">
                                                <dt class="col-sm-6">Proveedor:</dt>
                                                <dd class="col-sm-6">{{ $purchaseRequest->selectedQuotation->provider_name }}</dd>

                                                <dt class="col-sm-6">Subtotal:</dt>
                                                <dd class="col-sm-6">
                                                    ${{ number_format($purchaseRequest->selectedQuotation->subtotal ?? $purchaseRequest->selectedQuotation->total_amount, 2, ',', '.') }}
                                                </dd>

                                                @if($purchaseRequest->selectedQuotation->includes_iva ?? false)
                                                    <dt class="col-sm-6">IVA (19%):</dt>
                                                    <dd class="col-sm-6">
                                                        ${{ number_format($purchaseRequest->selectedQuotation->iva_amount ?? 0, 2, ',', '.') }}
                                                    </dd>
                                                @endif

                                                <dt class="col-sm-6"><strong>Total:</strong></dt>
                                                <dd class="col-sm-6">
                                                    <strong class="text-success">
                                                        ${{ number_format($purchaseRequest->selectedQuotation->total_amount, 2, ',', '.') }}
                                                    </strong>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <dl class="row">
                                                <dt class="col-sm-6">Tiempo de entrega:</dt>
                                                <dd class="col-sm-6">{{ $purchaseRequest->selectedQuotation->delivery_time ?? 'No especificado' }}</dd>

                                                <dt class="col-sm-6">Forma de pago:</dt>
                                                <dd class="col-sm-6">{{ $purchaseRequest->selectedQuotation->payment_method ?? 'No especificado' }}</dd>

                                                <dt class="col-sm-6">Validez:</dt>
                                                <dd class="col-sm-6">{{ $purchaseRequest->selectedQuotation->validity ?? 'No especificado' }}</dd>

                                                <dt class="col-sm-6">Garantía:</dt>
                                                <dd class="col-sm-6">{{ $purchaseRequest->selectedQuotation->warranty ?? 'No especificado' }}</dd>
                                            </dl>
                                        </div>
                                    </div>

                                    @if($purchaseRequest->selectedQuotation->additional_items && count($purchaseRequest->selectedQuotation->additional_items) > 0)
                                        <hr>
                                        <h6 class="text-muted">Items Adicionales en la Cotización:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Descripción</th>
                                                        <th>Cantidad</th>
                                                        <th>Unidad</th>
                                                        <th>Precio Unit.</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($purchaseRequest->selectedQuotation->additional_items as $item)
                                                        <tr>
                                                            <td>{{ $item['description'] }}</td>
                                                            <td>{{ $item['quantity'] }}</td>
                                                            <td>{{ $item['unit'] ?? 'Unidad' }}</td>
                                                            <td>${{ number_format($item['price'], 2, ',', '.') }}</td>
                                                            <td>${{ number_format($item['total'], 2, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No hay una cotización seleccionada para esta solicitud.
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> La orden de compra utilizará todos los datos (precios, IVA, items adicionales) 
                            de la cotización seleccionada. Solo debe completar los campos de términos de pago, fecha de entrega y observaciones.
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Generar Orden de Compra
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
<script>
    $(document).ready(function() {
        console.log('=== PAGE LOADED ===');
        
        // Configurar fecha mínima de entrega (hoy)
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;
        
        var deliveryInput = document.getElementById("delivery_date");
        if (deliveryInput) {
            deliveryInput.min = today;
            console.log('Set minimum delivery date to:', today);
        }
        
        // Validación del formulario antes del envío
        $('#order-form').on('submit', function(e) {
            console.log('=== FORM SUBMITTED ===');
            
            // Verificar campos requeridos
            var paymentTerms = $('input[name="payment_terms"]').val();
            var deliveryDate = $('input[name="delivery_date"]').val();
            
            console.log('Payment terms:', paymentTerms);
            console.log('Delivery date:', deliveryDate);
            
            if (!paymentTerms || paymentTerms.trim() === '') {
                alert('Por favor complete el campo de términos de pago');
                e.preventDefault();
                return false;
            }
            
            if (!deliveryDate || deliveryDate.trim() === '') {
                alert('Por favor complete la fecha de entrega');
                e.preventDefault();
                return false;
            }
            
            console.log('=== VALIDATION PASSED - SUBMITTING FORM ===');
            
            // Mostrar loading en el botón
            $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');
            
            // Permitir el envío normal del formulario
            return true;
        });
        
        console.log('=== JAVASCRIPT SETUP COMPLETE ===');
    });
</script>
@stop