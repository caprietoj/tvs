@extends('adminlte::page')

@section('title', 'Solicitudes de Compra y Materiales')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Solicitudes de Compra y Materiales</h1>
        <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Solicitud
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Solicitudes</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    @if(session('partial_success') && session('out_of_stock_warning'))
                        <br><br>
                        <button type="button" class="btn btn-warning btn-sm" id="showPartialSuccessModal">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Ver productos sin stock
                        </button>
                    @endif
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Modal para éxito parcial -->
            @if(session('partial_success') && session('out_of_stock_warning'))
            <div class="modal fade" id="partialSuccessModal" tabindex="-1" role="dialog" aria-labelledby="partialSuccessModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #ffc107; color: #212529;">
                            <h5 class="modal-title" id="partialSuccessModalLabel">
                                <i class="fas fa-check-circle mr-2"></i>Solicitud Creada con Productos Excluidos
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check mr-2"></i>
                                <strong>¡Solicitud creada exitosamente!</strong> Se guardaron únicamente los productos con stock disponible.
                            </div>
                            <div class="alert alert-warning">
                                <strong>Los siguientes productos fueron excluidos por falta de stock:</strong>
                            </div>
                            <ul class="list-group mb-3">
                                @foreach(session('out_of_stock_warning') as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><strong>{{ $item }}</strong></span>
                                    <span class="badge badge-warning badge-pill">Sin Stock</span>
                                </li>
                                @endforeach
                            </ul>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Recomendación:</strong> Para obtener estos productos, deberá crear una Solicitud de Compra siguiendo el procedimiento establecido.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="{{ route('purchase-requests.create-purchase') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart mr-2"></i>Crear Solicitud de Compra
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('purchase-requests.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="type" class="mr-2"><strong>Tipo:</strong></label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>
                                    Compra
                                </option>
                                <option value="services" {{ $typeFilter === 'services' ? 'selected' : '' }}>
                                    Servicios
                                </option>
                                <option value="materials" {{ $typeFilter === 'materials' ? 'selected' : '' }}>
                                    Materiales
                                </option>
                                <option value="copies" {{ $typeFilter === 'copies' ? 'selected' : '' }}>
                                    Fotocopias
                                </option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="section" class="mr-2"><strong>Área/Sección:</strong></label>
                            <select name="section" id="section" class="form-control">
                                <option value="all">Todas las secciones</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section }}" {{ $sectionFilter === $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Indicadores de filtros activos -->
            <div class="row mb-2">
                <div class="col-md-12">
                    @if($typeFilter || $sectionFilter)
                        <div class="mb-2">
                            <strong>Filtros activos:</strong>
                            @if($typeFilter)
                                <span class="badge badge-info mr-1">
                                    Tipo: 
                                    @if($typeFilter === 'purchase') Compras
                                    @elseif($typeFilter === 'services') Servicios
                                    @elseif($typeFilter === 'materials') Materiales
                                    @elseif($typeFilter === 'copies') Fotocopias
                                    @endif
                                </span>
                            @endif
                            @if($sectionFilter && $sectionFilter !== 'all')
                                <span class="badge badge-success">
                                    Sección: {{ $sectionFilter }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Botón de aprobación masiva para fotocopias -->
            @if($canBulkApproveCopies)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Aprobación masiva disponible:</strong> 
                                Hay {{ $copiesCount }} solicitudes de fotocopias pendientes en la sección "{{ $sectionFilter }}"
                            </div>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#bulkApproveModal">
                                <i class="fas fa-check-double mr-1"></i>
                                Aprobar todas las fotocopias
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requestsTable">
                    <thead>
                        <tr>
                            <th>N° Solicitud</th>
                            <th>Tipo</th>
                            <th>Solicitante</th>
                            <th>Área/Sección</th>
                            <th>Fecha</th>
                            @if(in_array($typeFilter, ['copies', 'materials']))
                                <th>Observaciones</th>
                            @endif
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>
                                    @if($request->type == 'purchase')
                                        <span class="badge badge-primary">Compra</span>
                                    @elseif($request->type == 'services')
                                        <span class="badge badge-warning">Servicios</span>
                                        @if($request->service_type === 'no_quotation')
                                            <br><small><span class="badge badge-secondary">Sin Cotización</span></small>
                                        @endif
                                    @elseif($request->isCopiesRequest())
                                        <span class="badge badge-info">Fotocopias</span>
                                    @else
                                        <span class="badge badge-success">Materiales</span>
                                    @endif
                                </td>
                                <td>{{ $request->requester }}</td>
                                <td>{{ $request->section_area }}</td>
                                <td>{{ $request->request_date->format('d/m/Y') }}</td>
                                @if(in_array($typeFilter, ['copies', 'materials']))
                                    <td class="text-center">
                                        @if($typeFilter === 'copies' && $request->special_details)
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-toggle="popover" 
                                                    data-trigger="hover" 
                                                    data-placement="left"
                                                    data-content="{{ $request->special_details }}" 
                                                    title="Observaciones de Fotocopias">
                                                <i class="fas fa-comment-alt"></i>
                                            </button>
                                        @elseif($typeFilter === 'materials' && $request->observations)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-toggle="popover" 
                                                    data-trigger="hover" 
                                                    data-placement="left"
                                                    data-content="{{ $request->observations }}" 
                                                    title="Observaciones de Materiales">
                                                <i class="fas fa-sticky-note"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @if($request->status == 'pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge badge-success">Aprobada</span>
                                    @elseif($request->status == 'in_process')
                                        <span class="badge badge-success">Aprobada</span>
                                    @elseif($request->status == 'rejected')
                                        <span class="badge badge-danger">Rechazada</span>
                                    @elseif($request->status == 'En Cotización')
                                        <span class="badge badge-info">En Cotización</span>
                                    @elseif($request->status == 'En pre-aprobación')
                                        <span class="badge badge-primary">En pre-aprobación</span>
                                    @elseif($request->status == 'Pre-aprobada')
                                        <span class="badge badge-primary">Pre-aprobada</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $request->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @php
                                            $permissionService = new \App\Services\PurchaseRequestPermissionService();
                                        @endphp
                                        @if($permissionService->canEditRequest($request))
                                            <a href="{{ route('purchase-requests.edit', $request) }}" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($permissionService->canDeleteRequest($request))
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal" 
                                                data-request-id="{{ $request->id }}"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $typeFilter === 'copies' ? '8' : '7' }}" class="text-center">No hay solicitudes registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar esta solicitud?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aprobación Masiva -->
@if($canBulkApproveCopies)
<div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bulkApproveModalLabel">
                    <i class="fas fa-check-double mr-2"></i>
                    Confirmar Aprobación Masiva
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                </div>
                <h6 class="text-center mb-3">¿Está seguro de que desea aprobar todas las solicitudes de fotocopias?</h6>
                <div class="alert alert-info">
                    <strong>Detalles de la acción:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Sección:</strong> {{ $sectionFilter }}</li>
                        <li><strong>Solicitudes a aprobar:</strong> {{ $copiesCount }}</li>
                        <li><strong>Tipo:</strong> Fotocopias</li>
                    </ul>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta acción marcará todas las solicitudes como aprobadas y no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <form method="POST" action="{{ route('purchase-requests.bulk-approve-copies') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="section" value="{{ $sectionFilter }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-double mr-1"></i>
                        Aprobar {{ $copiesCount }} solicitudes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@stop

@section('css')
<style>
    .badge {
        font-size: 90%;
    }
    
    /* Estilos para la paginación */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #3490dc;
        border-color: #3490dc;
    }
    
    .page-link {
        color: #3490dc;
    }
    
    .page-link:hover {
        color: #1d68a7;
    }
    
    /* Asegurar que la tabla sea responsive */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ajustar el ancho de las columnas */
    #requestsTable th:nth-child(1), 
    #requestsTable td:nth-child(1) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(2), 
    #requestsTable td:nth-child(2) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(3), 
    #requestsTable td:nth-child(3) {
        width: 20%;
    }
    
    #requestsTable th:nth-child(4), 
    #requestsTable td:nth-child(4) {
        width: 20%;
    }
    
    #requestsTable th:nth-child(5), 
    #requestsTable td:nth-child(5) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(6), 
    #requestsTable td:nth-child(6) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(7), 
    #requestsTable td:nth-child(7) {
        width: 20%;
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // DataTables con configuración de idioma español directamente
        // Desactivamos la paginación de DataTables ya que usamos la de Laravel
        $('#requestsTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
            }
        });
        
        // Modal de eliminación
        $('#deleteModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const requestId = button.data('request-id');
            const form = document.getElementById('deleteForm');
            form.action = `/purchase-requests/${requestId}`;
        });
        
        // Inicializar popovers para observaciones
        $('[data-toggle="popover"]').popover({
            html: true,
            container: 'body'
        });
        
        // Manejar modal de éxito parcial
        $('#showPartialSuccessModal').on('click', function() {
            $('#partialSuccessModal').modal('show');
        });
        
        // Si hay un éxito parcial, mostrar automáticamente después de 2 segundos
        @if(session('partial_success'))
            setTimeout(function() {
                $('#partialSuccessModal').modal('show');
            }, 2000);
        @endif
    });
</script>
@stop