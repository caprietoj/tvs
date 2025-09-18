@extends('adminlte::page')

@section('title', 'Crear Orden de Compra - Servicio Sin Cotización')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Crear Orden de Compra - Servicio Sin Cotización</h1>
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
                                    <span class="badge badge-success">Servicios</span>
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
                                <dd class="col-sm-8">{{ $purchaseRequest->justification }}</dd>
                                <dt class="col-sm-4">Fecha Solicitud:</dt>
                                <dd class="col-sm-8">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Información del servicio -->
                    @if($purchaseRequest->service_details)
                        <hr>
                        <h6>Detalles del Servicio:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Proveedor:</dt>
                                    <dd class="col-sm-8">{{ $purchaseRequest->service_details['provider'] ?? 'No especificado' }}</dd>
                                    <dt class="col-sm-4">Descripción:</dt>
                                    <dd class="col-sm-8">{{ $purchaseRequest->service_details['description'] ?? 'No especificado' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Valor estimado:</dt>
                                    <dd class="col-sm-8">
                                        @if(isset($purchaseRequest->service_details['estimated_value']))
                                            ${{ number_format($purchaseRequest->service_details['estimated_value'], 2) }}
                                        @else
                                            No especificado
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verificar si ya existe orden -->
            @php
                $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
            @endphp

            @if($existingOrder)
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
                            <i class="fas fa-plus-circle mr-2"></i>Crear Orden de Compra para Servicio
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Servicio sin cotización:</strong> Complete los datos del proveedor y el valor del servicio manualmente.
                        </div>

                        <form action="{{ route('purchase-orders.create-no-quotation', $purchaseRequest) }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <!-- Información del proveedor -->
                                <div class="col-md-6">
                                    <h6 class="text-primary">Información del Proveedor</h6>
                                    
                                    <div class="form-group">
                                        <label for="provider_select">Seleccionar Proveedor *</label>
                                        <select class="form-control @error('provider_name') is-invalid @enderror" 
                                                id="provider_select" name="provider_select">
                                            <option value="">-- Seleccione un proveedor --</option>
                                            @foreach($proveedores as $proveedor)
                                                <option value="{{ $proveedor->id }}" 
                                                        data-nombre="{{ $proveedor->nombre }}"
                                                        data-nit="{{ $proveedor->nit }}"
                                                        data-direccion="{{ $proveedor->direccion }}"
                                                        data-telefono="{{ $proveedor->telefono }}"
                                                        data-email="{{ $proveedor->email }}"
                                                        data-ciudad="{{ $proveedor->ciudad }}"
                                                        data-contacto="{{ $proveedor->persona_contacto }}">
                                                    {{ $proveedor->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">O escriba manualmente en el campo siguiente</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_name">Nombre del Proveedor *</label>
                                        <input type="text" class="form-control @error('provider_name') is-invalid @enderror" 
                                               id="provider_name" name="provider_name" 
                                               value="{{ old('provider_name', $purchaseRequest->service_details['provider'] ?? '') }}" required>
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

                                    <div class="form-group">
                                        <label for="provider_city">Ciudad</label>
                                        <input type="text" class="form-control @error('provider_city') is-invalid @enderror" 
                                               id="provider_city" name="provider_city" 
                                               value="{{ old('provider_city') }}">
                                        @error('provider_city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_contact">Persona de Contacto</label>
                                        <input type="text" class="form-control @error('provider_contact') is-invalid @enderror" 
                                               id="provider_contact" name="provider_contact" 
                                               value="{{ old('provider_contact') }}">
                                        @error('provider_contact')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Información de la orden -->
                                <div class="col-md-6">
                                    <h6 class="text-primary">Información de la Orden</h6>
                                    
                                    <div class="form-group">
                                        <label for="total_amount">Valor Total del Servicio *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" 
                                                   class="form-control @error('total_amount') is-invalid @enderror" 
                                                   id="total_amount" name="total_amount" 
                                                   value="{{ old('total_amount', $purchaseRequest->service_details['estimated_value'] ?? '') }}" required>
                                        </div>
                                        @error('total_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>¿El valor incluye IVA? *</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="includes_iva" id="includes_iva_yes" value="1" 
                                                   {{ old('includes_iva', '1') == '1' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="includes_iva_yes">
                                                Sí, el valor incluye IVA
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="includes_iva" id="includes_iva_no" value="0" 
                                                   {{ old('includes_iva') == '0' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="includes_iva_no">
                                                No, el valor no incluye IVA (se calculará automáticamente)
                                            </label>
                                        </div>
                                        @error('includes_iva')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_terms">Términos de Pago *</label>
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                               id="payment_terms" name="payment_terms" 
                                               value="{{ old('payment_terms', 'Contado') }}" required>
                                        @error('payment_terms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="delivery_date">Fecha de Prestación del Servicio *</label>
                                        <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                               id="delivery_date" name="delivery_date" 
                                               min="{{ date('Y-m-d') }}" 
                                               value="{{ old('delivery_date') }}" required>
                                        @error('delivery_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="observations">Observaciones y Especificaciones</label>
                                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                  id="observations" name="observations" 
                                                  rows="4">{{ old('observations', $purchaseRequest->service_details['description'] ?? '') }}</textarea>
                                        @error('observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-file-pdf"></i> Crear Orden de Compra
                                </button>
                                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
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
    $(document).ready(function() {
        // Configurar fecha mínima
        $('#delivery_date').attr('min', new Date().toISOString().split('T')[0]);

        // Manejar envío del formulario
        $('form').on('submit', function(e) {
            var $button = $(this).find('button[type="submit"]');
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');
            return true;
        });

        // Formatear el campo de valor total
        $('#total_amount').on('input', function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value) && value > 0) {
                var subtotal = value / 1.19;
                var iva = value - subtotal;
                console.log('Total: $' + value.toFixed(2) + ' (Subtotal: $' + subtotal.toFixed(2) + ' + IVA: $' + iva.toFixed(2) + ')');
            }
        });

        // Manejo del selector de proveedores
        $('#provider_select').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val() !== '') {
                // Cargar datos del proveedor seleccionado
                $('#provider_name').val(selectedOption.data('nombre'));
                $('#provider_nit').val(selectedOption.data('nit'));
                $('#provider_address').val(selectedOption.data('direccion'));
                $('#provider_phone').val(selectedOption.data('telefono'));
                $('#provider_email').val(selectedOption.data('email'));
                $('#provider_city').val(selectedOption.data('ciudad'));
                $('#provider_contact').val(selectedOption.data('contacto'));
                
                // Deshabilitar campos para evitar edición accidental
                $('#provider_name, #provider_nit, #provider_address, #provider_phone, #provider_email, #provider_city, #provider_contact')
                    .attr('readonly', true);
            } else {
                // Limpiar y habilitar campos si no hay selección
                $('#provider_name, #provider_nit, #provider_address, #provider_phone, #provider_email, #provider_city, #provider_contact')
                    .val('')
                    .attr('readonly', false);
            }
        });

        // Permitir edición manual si se escribe directamente en el campo nombre
        $('#provider_name').on('input', function() {
            if ($(this).val() !== $('#provider_select').find('option:selected').data('nombre')) {
                $('#provider_select').val('');
                $('#provider_name, #provider_nit, #provider_address, #provider_phone, #provider_email, #provider_city, #provider_contact')
                    .attr('readonly', false);
            }
        });
    });
</script>
@stop
