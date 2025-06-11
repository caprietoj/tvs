@extends('adminlte::page')

@section('title', 'Detalles de Orden de Compra')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Detalles de Orden de Compra</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
            <li class="breadcrumb-item active">Detalles</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Advertencia</h5>
            {{ session('warning') }}
        </div>
    @endif

    <div class="row">
        <!-- Información de la Orden de Compra -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Orden #{{ $purchaseOrder->order_number }}</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('purchase-orders.download', $purchaseOrder->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                            <a href="{{ route('purchase-orders.view', $purchaseOrder->id) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                <i class="fas fa-eye"></i> Ver PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Información General</h5>
                            <dl class="row">
                                <dt class="col-sm-5">Número de Orden:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->order_number }}</dd>
                                
                                <dt class="col-sm-5">Solicitud:</dt>
                                <dd class="col-sm-7">
                                    @if($purchaseOrder->purchaseRequest)
                                        <a href="{{ route('purchase-requests.show', $purchaseOrder->purchaseRequest->id) }}">
                                            {{ $purchaseOrder->purchaseRequest->request_number }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">Proveedor:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->provider->nombre ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Monto Total:</dt>
                                <dd class="col-sm-7">${{ number_format($purchaseOrder->total_amount, 2, ',', '.') }}</dd>
                                
                                <dt class="col-sm-5">Términos de Pago:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->payment_terms }}</dd>
                                
                                <dt class="col-sm-5">Fecha de Entrega:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->delivery_date->format('d/m/Y') }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h5>Estado de la Orden</h5>
                            <dl class="row">
                                <dt class="col-sm-5">Estado:</dt>
                                <dd class="col-sm-7">
                                    @switch($purchaseOrder->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                            @break
                                        @case('sent_to_accounting')
                                            <span class="badge badge-info">Enviado a Contabilidad</span>
                                            @break
                                        @case('paid')
                                            <span class="badge badge-success">Pagado</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">Cancelado</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $purchaseOrder->status }}</span>
                                    @endswitch
                                </dd>
                                
                                @if($purchaseOrder->sent_to_accounting_at)
                                <dt class="col-sm-5">Enviado a Contabilidad:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->sent_to_accounting_at->format('d/m/Y H:i') }}</dd>
                                
                                <dt class="col-sm-5">Enviado por:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->sender->name ?? 'N/A' }}</dd>
                                @endif
                                
                                @if($purchaseOrder->payment_date)
                                <dt class="col-sm-5">Fecha de Pago:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->payment_date->format('d/m/Y') }}</dd>
                                
                                <dt class="col-sm-5">Referencia de Pago:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->payment_reference }}</dd>
                                @endif
                                
                                @if($purchaseOrder->cancelled_at)
                                <dt class="col-sm-5">Cancelado el:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->cancelled_at->format('d/m/Y H:i') }}</dd>
                                
                                <dt class="col-sm-5">Cancelado por:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->canceller->name ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Motivo:</dt>
                                <dd class="col-sm-7">{{ $purchaseOrder->cancellation_reason }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @if($purchaseOrder->observations)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Observaciones</h5>
                            <p>{{ $purchaseOrder->observations }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Acciones</h5>
                            <div class="btn-group">
                                @if($purchaseOrder->isPending())
                                    <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#sendToAccountingModal">
                                        @if($purchaseOrder->purchaseRequest->isCopiesRequest() || $purchaseOrder->purchaseRequest->isMaterialsRequest())
                                            <i class="fas fa-check"></i> Autorizar Orden
                                        @else
                                            <i class="fas fa-paper-plane"></i> Enviar a Contabilidad
                                        @endif
                                    </button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelOrderModal">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                @endif

                                @if($purchaseOrder->isSentToAccounting())
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#markAsPaidModal">
                                        <i class="fas fa-check-circle"></i> Marcar como Pagado
                                    </button>
                                @endif

                                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Solicitante</h3>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->purchaseRequest)
                    <dl>
                        <dt>Solicitante:</dt>
                        <dd>{{ $purchaseOrder->purchaseRequest->requester }}</dd>
                        
                        <dt>Área/Sección:</dt>
                        <dd>{{ $purchaseOrder->purchaseRequest->section_area }}</dd>
                        
                        <dt>Usuario:</dt>
                        <dd>{{ $purchaseOrder->purchaseRequest->user->name ?? 'N/A' }}</dd>
                        
                        <dt>Fecha de Solicitud:</dt>
                        <dd>{{ $purchaseOrder->purchaseRequest->created_at->format('d/m/Y') }}</dd>
                    </dl>
                    @else
                    <p class="text-muted">No hay información del solicitante disponible.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para enviar a contabilidad -->
<div class="modal fade" id="sendToAccountingModal" tabindex="-1" role="dialog" aria-labelledby="sendToAccountingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('purchase-orders.send-to-accounting', $purchaseOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="sendToAccountingModalLabel">
                        @if($purchaseOrder->purchaseRequest->isCopiesRequest() || $purchaseOrder->purchaseRequest->isMaterialsRequest())
                            Autorizar Orden
                        @else
                            Enviar a Contabilidad
                        @endif
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($purchaseOrder->purchaseRequest->isCopiesRequest())
                        <p>¿Está seguro de que desea autorizar esta orden de fotocopias?</p>
                        <p>Se enviará una notificación por correo electrónico a <strong>compras@tvs.edu.co</strong> con los detalles de la orden.</p>
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> Las órdenes de fotocopias no requieren aprobación de contabilidad para pago.</small>
                        </div>
                    @elseif($purchaseOrder->purchaseRequest->isMaterialsRequest())
                        <p>¿Está seguro de que desea autorizar esta orden de materiales?</p>
                        <p>Se enviará una notificación por correo electrónico a <strong>compras@tvs.edu.co</strong> con los detalles de la orden.</p>
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> Las órdenes de materiales no requieren aprobación de contabilidad para pago.</small>
                        </div>
                    @else
                        <p>¿Está seguro de que desea enviar esta orden de compra a contabilidad para su pago?</p>
                        <p>Se enviará una notificación por correo electrónico a <strong>contabilidad@tvs.edu.co</strong> con copia a <strong>compras@tvs.edu.co</strong> con los detalles de la orden.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        @if($purchaseOrder->purchaseRequest->isCopiesRequest() || $purchaseOrder->purchaseRequest->isMaterialsRequest())
                            Autorizar
                        @else
                            Enviar
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para marcar como pagado -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" role="dialog" aria-labelledby="markAsPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('purchase-orders.mark-as-paid', $purchaseOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="markAsPaidModalLabel">Marcar como Pagado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Ingrese los detalles del pago:</p>
                    <div class="form-group">
                        <label for="payment_date">Fecha de Pago</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_reference">Referencia de Pago</label>
                        <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Ej. Transferencia #12345" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para cancelar orden -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('purchase-orders.cancel', $purchaseOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancelar Orden</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.
                    </div>
                    <div class="form-group">
                        <label for="cancellation_reason">Motivo de cancelación <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required minlength="10"></textarea>
                        <small class="form-text text-muted">Indique el motivo por el cual se está cancelando esta orden de compra.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </form>
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
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
    }
    dl dt {
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Configuración de fecha máxima para el campo de fecha de pago
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;
        
        document.getElementById("payment_date").max = today;
    });
</script>
@stop