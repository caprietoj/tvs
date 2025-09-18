@extends('adminlte::page')

@section('title', 'Crear Orden de Compra - Cotización Única')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Crear Orden de Compra - Cotización Única</h1>
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
                                    <span class="badge badge-primary">{{ ucfirst($purchaseRequest->type) }}</span>
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
                </div>
            </div>

            <!-- Información de la cotización -->
            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Cotización Seleccionada
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-6">Proveedor:</dt>
                                <dd class="col-sm-6">
                                    <strong>{{ $quotation->provider_name }}</strong>
                                </dd>
                                <dt class="col-sm-6">Subtotal:</dt>
                                <dd class="col-sm-6">${{ number_format($quotation->subtotal, 2) }}</dd>
                                @if($quotation->iva_amount > 0)
                                    <dt class="col-sm-6">IVA:</dt>
                                    <dd class="col-sm-6">${{ number_format($quotation->iva_amount, 2) }}</dd>
                                @endif
                                @if($quotation->tax_amount > 0)
                                    <dt class="col-sm-6">Impuestos:</dt>
                                    <dd class="col-sm-6">${{ number_format($quotation->tax_amount, 2) }}</dd>
                                @endif
                                <dt class="col-sm-6"><strong>Total:</strong></dt>
                                <dd class="col-sm-6">
                                    <strong class="text-success">
                                        ${{ number_format($quotation->total_amount, 2) }}
                                    </strong>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-6">Tiempo entrega:</dt>
                                <dd class="col-sm-6">{{ $quotation->delivery_time ?? 'No especificado' }}</dd>
                                <dt class="col-sm-6">Forma de pago:</dt>
                                <dd class="col-sm-6">{{ $quotation->payment_method ?? 'No especificado' }}</dd>
                                <dt class="col-sm-6">Validez:</dt>
                                <dd class="col-sm-6">{{ $quotation->validity ?? 'No especificado' }}</dd>
                                <dt class="col-sm-6">Garantía:</dt>
                                <dd class="col-sm-6">{{ $quotation->warranty ?? 'No especificado' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Items de la cotización o descripción del servicio -->
                    @if($quotation->items && count($quotation->items) > 0)
                        <hr>
                        <h6>Items de la Cotización:</h6>
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
                                    @foreach($quotation->items as $item)
                                        <tr>
                                            <td>{{ $item['description'] }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td>{{ $item['unit'] ?? 'Unidad' }}</td>
                                            <td>${{ number_format($item['price'], 2) }}</td>
                                            <td>${{ number_format($item['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($purchaseRequest->type === 'services' && $purchaseRequest->service_items)
                        <hr>
                        <h6>Detalles del Servicio Solicitado:</h6>
                        
                        @if($purchaseRequest->service_justification)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Justificación:</strong> {{ $purchaseRequest->service_justification }}
                        </div>
                        @endif
                        
                        <h6>Items del Servicio:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Cantidad</th>
                                        <th>Descripción</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequest->service_items as $item)
                                        <tr>
                                            <td>{{ $item['item'] }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td>{{ $item['description'] }}</td>
                                            <td>{{ $item['observations'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Monto Total de la Cotización:</strong> ${{ number_format($quotation->total_amount, 2, ',', '.') }}
                            </div>
                        </div>
                    @elseif($quotation->description)
                        <hr>
                        <h6>Descripción del Servicio:</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Servicio:</strong> {{ $quotation->description }}
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Monto Total:</strong> ${{ number_format($quotation->total_amount, 2, ',', '.') }}
                            </div>
                        </div>
                    @else
                        <hr>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Servicio sin descripción detallada</strong><br>
                            Monto Total: ${{ number_format($quotation->total_amount, 2, ',', '.') }}
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
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle mr-2"></i>Crear Orden de Compra
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $isNoQuotationService = $purchaseRequest->type === 'services' && 
                                                   !$purchaseRequest->selectedQuotation && 
                                                   !$purchaseRequest->quotationItemSelections()->exists();
                            $formAction = $isNoQuotationService ? 
                                         route('purchase-orders.create-no-quotation', $purchaseRequest) : 
                                         route('purchase-orders.create-from-quotation', $purchaseRequest);
                        @endphp
                        <form action="{{ $formAction }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    @if($isNoQuotationService)
                                        <!-- Campos para servicio sin cotización -->
                                        <div class="form-group">
                                            <label for="provider_select">Seleccionar Proveedor</label>
                                            <select class="form-control @error('provider_name') is-invalid @enderror" 
                                                    id="provider_select" name="provider_select">
                                                <option value="">-- Seleccione un proveedor --</option>
                                                @if(isset($proveedores))
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
                                                @endif
                                            </select>
                                            <small class="form-text text-muted">O escriba manualmente en el campo siguiente</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_name">Nombre del Proveedor</label>
                                            <input type="text" class="form-control @error('provider_name') is-invalid @enderror" 
                                                   id="provider_name" name="provider_name" 
                                                   value="{{ old('provider_name', $providerName ?? '') }}" required>
                                            @error('provider_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_nit">NIT del Proveedor</label>
                                            <input type="text" class="form-control @error('provider_nit') is-invalid @enderror" 
                                                   id="provider_nit" name="provider_nit" 
                                                   value="{{ old('provider_nit') }}" required>
                                            @error('provider_nit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_address">Dirección del Proveedor</label>
                                            <input type="text" class="form-control @error('provider_address') is-invalid @enderror" 
                                                   id="provider_address" name="provider_address" 
                                                   value="{{ old('provider_address') }}">
                                            @error('provider_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_phone">Teléfono del Proveedor</label>
                                            <input type="text" class="form-control @error('provider_phone') is-invalid @enderror" 
                                                   id="provider_phone" name="provider_phone" 
                                                   value="{{ old('provider_phone') }}">
                                            @error('provider_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_email">Email del Proveedor</label>
                                            <input type="email" class="form-control @error('provider_email') is-invalid @enderror" 
                                                   id="provider_email" name="provider_email" 
                                                   value="{{ old('provider_email') }}">
                                            @error('provider_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="provider_city">Ciudad del Proveedor</label>
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

                                        <div class="form-group">
                                            <label for="total_amount">Valor Total</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('total_amount') is-invalid @enderror" 
                                                   id="total_amount" name="total_amount" 
                                                   value="{{ old('total_amount', $purchaseRequest->budget ?? '') }}" required>
                                            @error('total_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="includes_iva" name="includes_iva" value="1" 
                                                       {{ old('includes_iva', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="includes_iva">
                                                    El valor incluye IVA
                                                </label>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Campos para servicios con cotización -->
                                        <div class="form-group">
                                            <label for="provider_id">Proveedor</label>
                                            @if($provider)
                                                <input type="text" class="form-control" value="{{ $provider->nombre }} - {{ $provider->nit }}" disabled>
                                                <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                            @else
                                                <select class="form-control @error('provider_id') is-invalid @enderror" id="provider_id" name="provider_id" required>
                                                    <option value="">Seleccione un proveedor...</option>
                                                    @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $prov)
                                                        <option value="{{ $prov->id }}" {{ $prov->nombre == $providerName ? 'selected' : '' }}>
                                                            {{ $prov->nombre }} - {{ $prov->nit }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            @error('provider_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="payment_terms">Términos de Pago</label>
                                        <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                               id="payment_terms" name="payment_terms" 
                                               value="{{ old('payment_terms', $quotation->payment_method ?? 'Contado') }}" required>
                                        @error('payment_terms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="delivery_date">Fecha de Entrega</label>
                                        <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                               id="delivery_date" name="delivery_date" 
                                               min="{{ date('Y-m-d') }}" 
                                               value="{{ old('delivery_date') }}" required>
                                        @error('delivery_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="observations">Observaciones</label>
                                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                  id="observations" name="observations" 
                                                  rows="3">{{ old('observations') }}</textarea>
                                        @error('observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
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

        // Manejar envío del formulario
        $('form').on('submit', function(e) {
            var $button = $(this).find('button[type="submit"]');
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');
            return true;
        });
    });
</script>
@stop
