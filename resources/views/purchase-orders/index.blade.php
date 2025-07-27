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
                                @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('purchase-orders.edit-pdf', $order->id) }}" class="btn btn-sm btn-warning" title="Editar PDF personalizado">
                                    <i class="fas fa-file-pdf"></i> Editar PDF
                                </a>
                                @endif
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
                                
                                {{-- Botón de regenerar orden completa solo para administradores --}}
                                @if(auth()->user()->hasRole('admin'))
                                <form action="{{ route('purchase-orders.regenerate-pdf', $order->id) }}" method="POST" class="d-inline regenerate-pdf-form" data-order-number="{{ $order->order_number }}">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-secondary regenerate-pdf-btn" title="Regenerar orden completa desde la solicitud original">
                                        <i class="fas fa-sync-alt"></i> Regenerar
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

<!-- Modal de confirmación para regenerar PDF -->
<div class="modal fade" id="confirmRegeneratePdfModal" tabindex="-1" role="dialog" aria-labelledby="confirmRegeneratePdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white" id="confirmRegeneratePdfModalLabel">
                    <i class="fas fa-sync-alt mr-2"></i>Regenerar Orden Completa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-file-invoice text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">¿Está seguro de que desea regenerar completamente esta orden?</h6>
                <div class="alert alert-info">
                    <p class="mb-2"><strong>Orden:</strong> <span id="modalOrderNumber"></span></p>
                    <p class="mb-2"><small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Esta acción realizará un recálculo completo:</strong>
                    </small></p>
                    <ul class="mb-0 small text-muted">
                        <li>Recuperará todos los datos desde la solicitud original</li>
                        <li>Recalculará impuestos (IVA, Impuesto al Consumo, etc.)</li>
                        <li>Actualizará subtotales y totales</li>
                        <li>Regenerará el PDF con la información corregida</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <small><i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Importante:</strong> Los valores actuales de la orden serán reemplazados por los cálculos correctos de la solicitud original.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="confirmRegenerateBtn">
                    <i class="fas fa-sync-alt mr-1"></i>Regenerar Orden Completa
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de procesamiento -->
<div class="modal fade" id="processingModal" tabindex="-1" role="dialog" aria-labelledby="processingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Procesando...</span>
                </div>
                <h6>Regenerando orden completa...</h6>
                <p class="text-muted mb-0"><small>Recalculando impuestos y totales desde la solicitud original. Por favor espere.</small></p>
            </div>
        </div>
    </div>
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
    
    /* Estilos para el botón de regenerar PDF */
    .btn-warning.regenerate-pdf {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    
    .btn-warning.regenerate-pdf:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #212529;
    }
    
    .btn-warning.regenerate-pdf:disabled {
        background-color: #ffc107;
        border-color: #ffc107;
        opacity: 0.6;
        color: #212529;
    }
    
    /* Mejorar espaciado en los botones de acciones */
    .btn-sm {
        margin: 1px;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    /* Responsive para botones en móviles */
    @media (max-width: 768px) {
        .btn-sm {
            display: block;
            width: 100%;
            margin: 2px 0;
        }
        
        .d-inline {
            display: block !important;
        }
    }
    
    /* Estilos específicos para modales de regeneración */
    #confirmRegeneratePdfModal .modal-header {
        border-bottom: 2px solid #ffc107;
    }
    
    #confirmRegeneratePdfModal .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    #confirmRegeneratePdfModal .alert-info {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        border-left: 4px solid #17a2b8;
    }
    
    #processingModal .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    #processingModal .spinner-border {
        border-width: 0.25rem;
    }
    
    /* Animación para el icono de PDF */
    #confirmRegeneratePdfModal .fa-file-pdf {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    /* Hover effects para botones */
    .regenerate-pdf-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .regenerate-pdf-btn {
        transition: all 0.2s ease-in-out;
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

        // Manejar clics en botones de regenerar PDF con modal
        $('.regenerate-pdf-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const orderNumber = form.data('order-number');
            
            // Configurar el modal con la información de la orden
            $('#modalOrderNumber').text(orderNumber);
            
            // Mostrar el modal de confirmación
            $('#confirmRegeneratePdfModal').modal('show');
            
            // Configurar el botón de confirmación
            $('#confirmRegenerateBtn').off('click').on('click', function() {
                // Ocultar modal de confirmación
                $('#confirmRegeneratePdfModal').modal('hide');
                
                // Mostrar modal de procesamiento
                $('#processingModal').modal('show');
                
                // Enviar el formulario
                form[0].submit();
            });
        });

        // Cerrar modal de procesamiento si hay errores
        @if(session('error'))
            $('#processingModal').modal('hide');
        @endif

        // Si hay éxito, también cerrar el modal de procesamiento
        @if(session('success'))
            $('#processingModal').modal('hide');
        @endif
    });
</script>
@stop