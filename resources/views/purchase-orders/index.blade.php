@extends('adminlte::page')

@section('title', 'Órdenes de Compra')

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
            @if(auth()->user()->can('admin'))
                <div class="card-tools">
                    <form action="{{ route('purchases.orders.repair-all') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning" title="Validar y reparar todas las órdenes con problemas" onclick="return confirm('¿Estás seguro de que deseas reparar todas las órdenes? Esto puede tomar algunos minutos.')">
                            <i class="fas fa-tools"></i> Reparar Todas las Órdenes
                        </button>
                    </form>
                </div>
            @endif
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
                        <tr>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar número..." data-column="0"></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar solicitud..." data-column="1"></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar proveedor..." data-column="2"></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar monto..." data-column="3"></th>
                            <th>
                                <select class="form-control form-control-sm column-filter" data-column="4">
                                    <option value="">Todos los estados</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Aprobado">Aprobado</option>
                                    <option value="Enviado a Contabilidad">Enviado a Contabilidad</option>
                                    <option value="Pagado">Pagado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar fecha..." data-column="5"></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar creado..." data-column="6"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr class="{{ $order->is_viewed ? 'order-viewed' : '' }}" data-order-id="{{ $order->id }}">
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
                                @if((auth()->user()->hasRole('admin') || auth()->user()->hasRole('compras')) && auth()->id() !== 11)
                                <a href="{{ route('purchase-orders.edit-pdf', $order->id) }}" class="btn btn-sm btn-warning" title="Editar PDF personalizado">
                                    <i class="fas fa-file-pdf"></i> Editar PDF
                                </a>
                                @endif
                                @if(($order->isPending() || ($order->status == 'approved' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('compras')))) && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('compras')) && auth()->id() !== 11)
                                <a href="{{ route('purchase-orders.edit', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('purchase-orders.destroy', $order->id) }}" method="POST" class="d-inline delete-order-form" data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-order-btn">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                                @endif
                                
                                {{-- Botón marcar como vista solo para admin, contabilidad y tesorería --}}
                                @php
                                    $allowedEmails = ['asistentecontabilidad@tvs.edu.co', 'contabilidad@tvs.edu.co', 'tesoreria@tvs.edu.co'];
                                    $canMarkViewed = auth()->user()->hasRole('admin') || in_array(auth()->user()->email, $allowedEmails);
                                @endphp
                                @if($canMarkViewed)
                                <button type="button" class="btn btn-sm {{ $order->is_viewed ? 'btn-success' : 'btn-outline-success' }} toggle-viewed-btn" 
                                        data-order-id="{{ $order->id }}" 
                                        title="{{ $order->is_viewed ? 'Marcar como NO vista' : 'Marcar como vista' }}">
                                    <i class="fas {{ $order->is_viewed ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $order->is_viewed ? 'Vista' : 'No vista' }}
                                </button>
                                @endif
                                
                                {{-- Botón de regenerar orden completa solo para administradores y compras (excepto compras@tvs.edu.co) --}}
                                @if((auth()->user()->hasRole('admin') || auth()->user()->hasRole('compras')) && auth()->id() !== 11)
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
            </div>
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
    /* Estilo para órdenes marcadas como vistas */
    .order-viewed {
        background-color: #d4edda !important; /* Verde suave */
    }
    
    .order-viewed td {
        background-color: transparent !important;
    }
    
    /* Estilos para paginación profesional */
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
    
    /* Estilos para los filtros */
    .column-filter {
        margin: 2px 0;
        font-size: 12px;
        height: 30px;
    }
    
    thead tr:nth-child(2) th {
        padding: 5px !important;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar DataTable para la tabla principal
        var ordersTable = $('#ordersTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            "pageLength": 25,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[ 6, "desc" ]], // Ordenar por fecha de creación descendente
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });

        // Filtros en tiempo real para cada columna
        $('.column-filter').on('keyup change', function() {
            var columnIndex = $(this).data('column');
            var value = this.value;
            
            // Filtro especial para la columna de monto (columna 3)
            if (columnIndex == 3 && value) {
                // Limpiar formato de números: eliminar $ y comas
                var cleanValue = value.replace(/[$,]/g, '');
                if (!isNaN(cleanValue) && cleanValue !== '') {
                    // Buscar por el número sin formato
                    ordersTable
                        .column(columnIndex)
                        .search(cleanValue, true, false) // regex=true, smart=false
                        .draw();
                } else {
                    // Si no es un número, buscar como texto normal
                    ordersTable
                        .column(columnIndex)
                        .search(value)
                        .draw();
                }
            } else {
                // Filtro normal para otras columnas
                ordersTable
                    .column(columnIndex)
                    .search(value)
                    .draw();
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
                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
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

        // Manejar clics en botones de marcar como vista
        $('.toggle-viewed-btn').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const orderId = button.data('order-id');
            const row = button.closest('tr');
            
            console.log('Toggle clicked for order:', orderId);
            console.log('Current row class:', row.attr('class'));
            
            // Deshabilitar el botón temporalmente
            button.prop('disabled', true);
            
            // Realizar petición AJAX
            $.ajax({
                url: `/purchase-orders/${orderId}/toggle-viewed`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Server response:', response);
                    
                    if (response.success) {
                        // Actualizar el estado visual de la fila
                        if (response.is_viewed) {
                            console.log('Setting as viewed');
                            row.addClass('order-viewed');
                            button.removeClass('btn-outline-success').addClass('btn-success');
                            button.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                            button.attr('title', 'Marcar como NO vista');
                            button.html('<i class="fas fa-eye"></i> Vista');
                        } else {
                            console.log('Setting as not viewed');
                            row.removeClass('order-viewed');
                            button.removeClass('btn-success').addClass('btn-outline-success');
                            button.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                            button.attr('title', 'Marcar como vista');
                            button.html('<i class="fas fa-eye-slash"></i> No vista');
                        }
                        
                        console.log('New row class:', row.attr('class'));
                        
                        // Forzar re-render del estilo
                        row[0].offsetHeight; // Trigger reflow
                        
                        // Mostrar mensaje de éxito
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Error al actualizar el estado');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Error al actualizar el estado de la orden';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'No tienes permisos para realizar esta acción';
                    }
                    
                    toastr.error(errorMessage);
                },
                complete: function() {
                    // Rehabilitar el botón
                    button.prop('disabled', false);
                }
            });
        });

        // Manejar clics en botones de eliminar orden
        $('.delete-order-btn').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const form = button.closest('form');
            const orderId = form.data('order-id');
            const orderNumber = form.data('order-number');
            const row = button.closest('tr');
            
            // Confirmar eliminación
            if (!confirm(`¿Está seguro de que desea eliminar la orden de compra ${orderNumber}?`)) {
                return;
            }
            
            // Deshabilitar el botón y mostrar estado de carga
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
            
            // Realizar petición AJAX usando la URL del formulario directamente
            $.ajax({
                url: form.attr('action'), // Usar directamente la URL del formulario
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        // Animar la fila antes de eliminarla
                        row.fadeOut(300, function() {
                            // Eliminar la fila del DataTable
                            ordersTable.row(row).remove().draw();
                        });
                        
                        toastr.success(response.message || 'Orden de compra eliminada correctamente');
                    } else {
                        toastr.error(response.message || 'Error al eliminar la orden de compra');
                        // Restaurar el botón
                        button.prop('disabled', false);
                        button.html('<i class="fas fa-trash"></i> Eliminar');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Error al eliminar la orden de compra';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'No tienes permisos para eliminar esta orden';
                    } else if (xhr.status === 404) {
                        errorMessage = 'La orden de compra no fue encontrada';
                    }
                    
                    toastr.error(errorMessage);
                    
                    // Restaurar el botón
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-trash"></i> Eliminar');
                }
            });
        });
    });
</script>
@stop