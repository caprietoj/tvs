@extends('adminlte::page')

@section('title', 'Órdenes de Compra')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Órdenes de Compra</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Órdenes de Compra</li>
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

    <!-- Solicitudes aprobadas pendientes de órdenes -->
    @if(count($approvedRequests) > 0)
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Solicitudes Aprobadas Pendientes</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="pendingRequestsTable">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Solicitante</th>
                            <th>Departamento</th>
                            <th>Cotización Seleccionada</th>
                            <th>Monto</th>
                            <th>Fecha Aprobación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedRequests as $request)
                        <tr>
                            <td>{{ $request->request_number }}</td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section_area }}</td>
                            <td>
                                @if($request->selectedQuotation)
                                    {{ $request->selectedQuotation->provider_name }}
                                @elseif($request->quotationItemSelections->count() > 0)
                                    <span class="badge badge-info">Selección Mixta</span>
                                    <small class="d-block">{{ $request->quotationItemSelections->count() }} proveedores</small>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($request->selectedQuotation)
                                    ${{ number_format($request->selectedQuotation->total_amount, 2, ',', '.') }}
                                @elseif($request->quotationItemSelections->count() > 0)
                                    ${{ number_format($request->quotationItemSelections->sum('total_price'), 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $request->approval_date ? $request->approval_date->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('purchase-orders.create', $request->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-invoice"></i> Generar Orden
                                </a>
                                <a href="{{ route('purchase-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Ver Solicitud
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Órdenes de Compra Existentes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Órdenes de Compra</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Número de Orden</th>
                            <th>Solicitud</th>
                            <th>Proveedor</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Fecha de Entrega</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>
                                @if($order->purchaseRequest)
                                <a href="{{ route('purchase-requests.show', $order->purchaseRequest->id) }}">
                                    {{ $order->purchaseRequest->request_number }}
                                </a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if($order->provider)
                                {{ $order->provider->nombre }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td>${{ number_format($order->total_amount, 2, ',', '.') }}</td>
                            <td>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-success">Aprobado</span>
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
                                        <span class="badge badge-secondary">{{ $order->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $order->delivery_date->format('d/m/Y') }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                @if(($order->isPending() || ($order->status == 'approved' && auth()->user()->hasRole('admin'))) && auth()->user()->hasRole('admin'))
                                <a href="{{ route('purchase-orders.edit', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('purchase-orders.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea eliminar esta orden de compra?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay órdenes de compra registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación mejorada -->
            @if($orders->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info">
                    <small class="text-muted">
                        Mostrando {{ $orders->firstItem() }} a {{ $orders->lastItem() }} de {{ $orders->total() }} órdenes
                    </small>
                </div>
                <div class="pagination-wrapper">
                    {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    /* Estilos para paginación profesional */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .pagination-wrapper .pagination {
        margin: 0;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .pagination-wrapper .page-link {
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        text-decoration: none;
        background-color: #fff;
        border-radius: 0;
        transition: all 0.15s ease-in-out;
    }
    
    .pagination-wrapper .page-item:first-child .page-link {
        margin-left: 0;
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    
    .pagination-wrapper .page-item:last-child .page-link {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    
    .pagination-wrapper .page-link:hover {
        z-index: 2;
        color: #364E76;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .pagination-wrapper .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #364E76;
        border-color: #364E76;
    }
    
    .pagination-wrapper .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .pagination-info {
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    /* Responsive para móviles */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .pagination-info {
            order: 2;
        }
        
        .pagination-wrapper {
            order: 1;
        }
        
        .pagination-wrapper .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
    }
    
    /* Mejorar la apariencia general de la tabla */
    .table-responsive {
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    #ordersTable thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });

        $('#pendingRequestsTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });

        // Mostrar mensajes de éxito o error
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    });
</script>
@stop