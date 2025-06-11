@extends('adminlte::page')

@section('title', 'Editar Orden de Compra')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Editar Orden de Compra</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}">Detalle</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Orden de Compra #{{ $purchaseOrder->order_number }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> El PDF de la orden de compra se genera automáticamente al guardar los cambios. No es necesario adjuntar ningún archivo.
                    </div>
                    
                    <form action="{{ route('purchase-orders.update', $purchaseOrder->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order_number">Número de Orden <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('order_number') is-invalid @enderror" id="order_number" name="order_number" value="{{ old('order_number', $purchaseOrder->order_number) }}" required>
                                    @error('order_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Número consecutivo de la orden de compra.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="provider_id">Proveedor <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('provider_id') is-invalid @enderror" id="provider_id" name="provider_id" required>
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $provider)
                                            <option value="{{ $provider->id }}" {{ old('provider_id', $purchaseOrder->provider_id) == $provider->id ? 'selected' : '' }}>
                                                {{ $provider->nombre }} - {{ $provider->nit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('provider_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_terms">Términos de Pago <span class="text-danger">*</span></label>
                                    <select class="form-control @error('payment_terms') is-invalid @enderror" id="payment_terms" name="payment_terms" required>
                                        <option value="30 días" {{ old('payment_terms', $purchaseOrder->payment_terms) == '30 días' ? 'selected' : '' }}>30 días</option>
                                        <option value="15 días" {{ old('payment_terms', $purchaseOrder->payment_terms) == '15 días' ? 'selected' : '' }}>15 días</option>
                                        <option value="Contado" {{ old('payment_terms', $purchaseOrder->payment_terms) == 'Contado' ? 'selected' : '' }}>Contado</option>
                                        <option value="Pago anticipado" {{ old('payment_terms', $purchaseOrder->payment_terms) == 'Pago anticipado' ? 'selected' : '' }}>Pago anticipado</option>
                                        <option value="otro">Otro (especificar en observaciones)</option>
                                    </select>
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_amount">Monto Total <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" value="{{ old('total_amount', $purchaseOrder->total_amount) }}" step="0.01" min="0" required>
                                    </div>
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', $purchaseOrder->delivery_date->format('Y-m-d')) }}" required>
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="pdf_status">Estado de PDF</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info">
                                                <i class="far fa-file-pdf"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" value="{{ $purchaseOrder->file_path && $purchaseOrder->file_path != 'pending_generation' ? 'PDF Generado' : 'Pendiente de generación' }}" readonly>
                                        
                                        @if($purchaseOrder->file_path && $purchaseOrder->file_path != 'pending_generation')
                                        <div class="input-group-append">
                                            <a href="{{ route('purchase-orders.download', $purchaseOrder->id) }}" class="btn btn-outline-secondary" target="_blank">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                    <small class="text-muted">El PDF se generará o actualizará automáticamente al guardar los cambios.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3">{{ old('observations', $purchaseOrder->observations) }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Incluya detalles adicionales que deben aparecer en la orden de compra o especificaciones sobre los términos de pago.</small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Orden de Compra
                            </button>
                            <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title">Datos de la Solicitud</h3>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->purchaseRequest)
                        <dl>
                            <dt><i class="fas fa-hashtag text-primary mr-1"></i> Número:</dt>
                            <dd>{{ $purchaseOrder->purchaseRequest->request_number }}</dd>
                            
                            <dt><i class="fas fa-user text-primary mr-1"></i> Solicitante:</dt>
                            <dd>{{ $purchaseOrder->purchaseRequest->requester }}</dd>
                            
                            <dt><i class="fas fa-building text-primary mr-1"></i> Área/Sección:</dt>
                            <dd>{{ $purchaseOrder->purchaseRequest->section_area }}</dd>
                            
                            @if($purchaseOrder->purchaseRequest->selectedQuotation)
                                <dt><i class="fas fa-file-invoice text-primary mr-1"></i> Cotización seleccionada:</dt>
                                <dd>{{ $purchaseOrder->purchaseRequest->selectedQuotation->provider_name }} - ${{ number_format($purchaseOrder->purchaseRequest->selectedQuotation->total_amount, 2, ',', '.') }}</dd>
                            @endif
                        </dl>
                    @else
                        <p class="text-muted">No hay información de solicitud asociada.</p>
                    @endif
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h3 class="card-title">Ayuda</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-info-circle text-info mr-1"></i> Al guardar los cambios:</p>
                    <ul>
                        <li>Se actualizará la información de la orden</li>
                        <li>El PDF se regenerará automáticamente</li>
                        <li>El archivo PDF estará disponible para descargar desde la vista de detalles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    dt {
        font-weight: 600;
        margin-top: 10px;
    }
    
    dd {
        padding-left: 15px;
        margin-bottom: 8px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione una opción',
            allowClear: true
        });

        // Si se selecciona "otro" en términos de pago, mostrar alerta
        $('#payment_terms').on('change', function() {
            if ($(this).val() === 'otro') {
                $('#observations').css('border-color', '#17a2b8');
                $('<div id="payment-terms-alert" class="alert alert-info mt-2">Por favor, especifique los términos de pago en el campo de observaciones.</div>').insertAfter('#payment_terms');
            } else {
                $('#observations').css('border-color', '');
                $('#payment-terms-alert').remove();
            }
        });

        // Verificar al cargar la página
        if ($('#payment_terms').val() === 'otro') {
            $('#observations').css('border-color', '#17a2b8');
            $('<div id="payment-terms-alert" class="alert alert-info mt-2">Por favor, especifique los términos de pago en el campo de observaciones.</div>').insertAfter('#payment_terms');
        }
    });
</script>
@stop