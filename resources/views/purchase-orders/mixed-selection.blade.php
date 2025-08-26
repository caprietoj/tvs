@extends('adminlte::page')

@section('title', 'Crear Órdenes de Compra - Selección Mixta')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Crear Órdenes de Compra - Selección Mixta</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
            <li class="breadcrumb-item active">Crear Selección Mixta</li>
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

            <!-- Alerta de selección mixta -->
            @php
                $totalProviders = $providerSummary->count();
                $ordersCreated = 0;
                foreach($providerSummary as $summary) {
                    $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                        ->whereHas('provider', function($query) use ($summary) {
                            $query->where('nombre', $summary['provider_name']);
                        })
                        ->exists();
                    if ($existingOrder) {
                        $ordersCreated++;
                    }
                }
                $progressPercentage = $totalProviders > 0 ? round(($ordersCreated / $totalProviders) * 100) : 0;
            @endphp
            
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Selección Mixta Detectada</h5>
                <p>Se detectaron artículos de <strong>{{ $providerGroups->count() }}</strong> proveedores diferentes. 
                   Debe crear una orden de compra separada para cada proveedor.</p>
                <p><strong>Total de artículos:</strong> {{ $mixedSelections->count() }} | 
                   <strong>Monto total:</strong> ${{ number_format($mixedSelections->sum('total_price'), 2) }}</p>
                
                <!-- Progreso de órdenes -->
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Progreso de Órdenes:</strong>
                        <span class="badge badge-{{ $ordersCreated == $totalProviders ? 'success' : 'info' }}">
                            {{ $ordersCreated }}/{{ $totalProviders }} órdenes creadas
                        </span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped {{ $ordersCreated == $totalProviders ? 'bg-success' : 'bg-info' }}" 
                             role="progressbar" style="width: {{ $progressPercentage }}%" 
                             aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progressPercentage }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por proveedor -->
            <div class="row">
                @foreach($providerSummary as $summary)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info">
                                <h5 class="card-title text-white mb-0">
                                    <i class="fas fa-building mr-2"></i>{{ $summary['provider_name'] }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Artículos</small>
                                        <div class="h4">{{ $summary['items_count'] }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total</small>
                                        <div class="h4 text-success">${{ number_format($summary['total_amount'], 2) }}</div>
                                    </div>
                                </div>

                                <!-- Detalle de artículos -->
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Artículo</th>
                                                <th>Cant.</th>
                                                <th>Precio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($summary['items'] as $item)
                                                <tr>
                                                    <td>{{ $item->item_description }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>${{ number_format($item->total_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Verificar si ya existe orden para este proveedor -->
                                @php
                                    $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                                        ->whereHas('provider', function($query) use ($summary) {
                                            $query->where('nombre', $summary['provider_name']);
                                        })
                                        ->first();
                                @endphp

                                @if($existingOrder)
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> 
                                        <strong>Orden creada:</strong> 
                                        <a href="{{ route('purchase-orders.show', $existingOrder) }}" class="alert-link">
                                            #{{ $existingOrder->order_number }}
                                        </a>
                                    </div>
                                @else
                                    <!-- Formulario para crear orden -->
                                    <form action="{{ route('purchase-orders.create-for-provider', $purchaseRequest) }}" method="POST" class="provider-form">
                                        @csrf
                                        <input type="hidden" name="provider_name" value="{{ $summary['provider_name'] }}">
                                        
                                        <div class="form-group">
                                            <label for="payment_terms_{{ $loop->index }}">Términos de Pago</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                   id="payment_terms_{{ $loop->index }}" 
                                                   name="payment_terms" 
                                                   value="Contado" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="delivery_date_{{ $loop->index }}">Fecha de Entrega</label>
                                            <input type="date" class="form-control form-control-sm" 
                                                   id="delivery_date_{{ $loop->index }}" 
                                                   name="delivery_date" 
                                                   min="{{ date('Y-m-d') }}" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="observations_{{ $loop->index }}">Observaciones</label>
                                            <textarea class="form-control form-control-sm" 
                                                      id="observations_{{ $loop->index }}" 
                                                      name="observations" 
                                                      rows="2"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-file-pdf"></i> Crear Orden de Compra
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botones de navegación -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Órdenes de Compra
                            </a>
                            
                            @php
                                $allOrdersCreated = true;
                                foreach($providerSummary as $summary) {
                                    $existingOrder = \App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                                        ->whereHas('provider', function($query) use ($summary) {
                                            $query->where('nombre', $summary['provider_name']);
                                        })
                                        ->exists();
                                    if (!$existingOrder) {
                                        $allOrdersCreated = false;
                                        break;
                                    }
                                }
                            @endphp

                            @if($allOrdersCreated)
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle"></i> 
                                    <strong>¡Proceso completado!</strong> Se han creado todas las órdenes de compra para esta solicitud.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .provider-form {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }
    
    .card-header.bg-info {
        background-color: #17a2b8 !important;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Configurar fecha mínima para todos los campos de fecha
        $('input[type="date"]').each(function() {
            $(this).attr('min', new Date().toISOString().split('T')[0]);
        });

        // Manejar envío de formularios
        $('.provider-form').on('submit', function(e) {
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            
            // Deshabilitar botón y mostrar loading
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');
            
            // Permitir envío normal del formulario
            return true;
        });
    });
</script>
@stop
