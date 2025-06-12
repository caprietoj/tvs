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

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('purchase-requests.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="type" class="mr-2"><strong>Filtrar por tipo:</strong></label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>
                                    Compra
                                </option>
                                <option value="materials" {{ $typeFilter === 'materials' ? 'selected' : '' }}>
                                    Materiales
                                </option>
                                <option value="copies" {{ $typeFilter === 'copies' ? 'selected' : '' }}>
                                    Fotocopias
                                </option>
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
                <div class="col-md-6 text-right">
                    @if($typeFilter)
                        <span class="badge badge-info">
                            Mostrando: 
                            @if($typeFilter === 'purchase') Compras
                            @elseif($typeFilter === 'materials') Materiales
                            @elseif($typeFilter === 'copies') Fotocopias
                            @endif
                        </span>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requestsTable">
                    <thead>
                        <tr>
                            <th>N° Solicitud</th>
                            <th>Tipo</th>
                            <th>Solicitante</th>
                            <th>Área/Sección</th>
                            <th>Fecha</th>
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
                                    @elseif($request->isCopiesRequest())
                                        <span class="badge badge-info">Fotocopias</span>
                                    @else
                                        <span class="badge badge-success">Materiales</span>
                                    @endif
                                </td>
                                <td>{{ $request->requester }}</td>
                                <td>{{ $request->section_area }}</td>
                                <td>{{ $request->request_date->format('d/m/Y') }}</td>
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
                                        @if($request->status == 'pending')
                                            <a href="{{ route('purchase-requests.edit', $request) }}" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
                                <td colspan="7" class="text-center">No hay solicitudes registradas</td>
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
    });
</script>
@stop