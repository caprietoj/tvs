@extends('adminlte::page')

@section('title', 'Aprobación de Solicitudes')

@section('content_header')
<h1>Aprobación de Solicitudes</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Solicitudes pendientes de aprobación final</h3>
    </div>
    <div class="card-body">
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

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-12">
                <form method="GET" action="{{ route('approvals.index') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="request_number" class="mr-2"><strong>No. Solicitud:</strong></label>
                        <input type="text" name="request_number" id="request_number" class="form-control" 
                               value="{{ $requestNumberFilter ?? '' }}" placeholder="Buscar por número...">
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
                    <div class="form-group mr-3">
                        <label for="type" class="mr-2"><strong>Tipo:</strong></label>
                        <select name="type" id="type" class="form-control">
                            <option value="all">Todos los tipos</option>
                            <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>Compra</option>
                            <option value="materials" {{ $typeFilter === 'materials' ? 'selected' : '' }}>Materiales</option>
                            <option value="services" {{ $typeFilter === 'services' ? 'selected' : '' }}>Servicios</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('approvals.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table id="approvalsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Solicitud</th>
                        <th>Solicitante</th>
                        <th>Área/Sección</th>
                        <th>Fecha de solicitud</th>
                        <th>Tipo</th>
                        <th>Pre-aprobada por</th>
                        <th>Cotización seleccionada</th>
                        <th>Monto</th>
                        <th>Acciones</th>
                    </tr>
                    <!-- Fila de filtros -->
                    <tr>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar No. Solicitud..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar solicitante..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar área..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar fecha..."></th>
                        <th>
                            <select class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="Compra">Compra</option>
                                <option value="Materiales">Materiales</option>
                                <option value="Servicios">Servicios</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar pre-aprobador..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar cotización..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar monto..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->request_number }}</td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section_area }}</td>
                            <td>{{ $request->request_date->format('d/m/Y') }}</td>
                            <td>
                                @if($request->type === 'purchase')
                                    <span class="badge badge-primary">Compra</span>
                                @elseif($request->type === 'materials')
                                    <span class="badge badge-info">Materiales</span>
                                @elseif($request->type === 'services')
                                    <span class="badge badge-success">Servicios</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($request->type) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($request->type === 'services' && $request->service_type === 'no_quotation')
                                    <span class="text-info">Pendiente</span>
                                @else
                                    {{ $request->preApprover ? $request->preApprover->name : 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($request->type === 'services' && $request->service_type === 'no_quotation')
                                    @if($request->quotation_file_path)
                                        <a href="{{ asset('storage/' . $request->quotation_file_path) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver cotización/orden de renovación adjunta">
                                            <i class="fas fa-file-pdf"></i> Ver archivo adjunto
                                        </a>
                                    @else
                                        <span class="text-info">Servicio sin cotización</span>
                                    @endif
                                @elseif($request->hasMixedSelection())
                                    <span class="badge badge-warning">Mixta</span>
                                @elseif($request->preApprovedQuotation)
                                    {{ $request->preApprovedQuotation->provider_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($request->type === 'services' && $request->service_type === 'no_quotation' && $request->service_budget)
                                    ${{ number_format($request->service_budget, 2, ',', '.') }}
                                @elseif($request->hasMixedSelection())
                                    ${{ number_format($request->getMixedSelectionTotal(), 2, ',', '.') }}
                                @elseif($request->preApprovedQuotation)
                                    ${{ number_format($request->preApprovedQuotation->total_amount, 2, ',', '.') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('approvals.show', $request->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No hay solicitudes pendientes de aprobación.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap4.min.css">

<style>
    .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
    }
    .table {
        margin-bottom: 0;
        min-width: 1200px; /* Forzar ancho mínimo para mostrar todas las columnas */
    }
    
    .table-responsive {
        overflow-x: auto; /* Permitir scroll horizontal si es necesario */
    }
    .pagination {
        justify-content: center;
    }
    
    /* Estilos para filtros de DataTables */
    #approvalsTable thead tr:nth-child(2) th {
        padding: 5px;
        background-color: #f8f9fa;
    }
    
    #approvalsTable thead tr:nth-child(2) input,
    #approvalsTable thead tr:nth-child(2) select {
        width: 100%;
        padding: 4px 6px;
        box-sizing: border-box;
        font-size: 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        #approvalsTable thead tr:nth-child(2) th {
            padding: 2px;
        }
        
        #approvalsTable thead tr:nth-child(2) input,
        #approvalsTable thead tr:nth-child(2) select {
            font-size: 11px;
            padding: 2px 4px;
        }
        
        .table {
            min-width: 1000px; /* Ancho mínimo más pequeño en móviles */
        }
    }
    
    /* Asegurar que todas las columnas sean visibles */
    #approvalsTable th,
    #approvalsTable td {
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    /* Columnas específicas que pueden ser más anchas */
    #approvalsTable th:nth-child(6),
    #approvalsTable td:nth-child(6),
    #approvalsTable th:nth-child(7),
    #approvalsTable td:nth-child(7) {
        min-width: 120px;
    }
</style>
@stop

@section('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Ocultar temporalmente la fila de filtros para evitar problemas con DataTables
        $('#approvalsTable thead tr:nth-child(2)').hide();
        
        // Verificar estructura de tabla antes de inicializar DataTables
        console.log('Número de columnas en thead:', $('#approvalsTable thead tr:first th').length);
        console.log('Número de columnas en tbody tr:first:', $('#approvalsTable tbody tr:first td').length);
        
        // Inicializar DataTables con configuración simplificada
        try {
            var table = $('#approvalsTable').DataTable({
                "destroy": true, // Permitir reinicialización
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron registros",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "emptyTable": "No hay datos disponibles en la tabla"
                },
                "pageLength": 25,
                "scrollX": true,
                "order": [[3, 'desc']], // Ordenar por fecha por defecto
                "columnDefs": [
                    { "orderable": false, "targets": 8 } // Desactivar ordenamiento en columna de acciones
                ],
                            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
            });

        } catch (error) {
            console.error('Error al inicializar DataTables:', error);
            // Fallback: ocultar la segunda fila del thead si hay error
            $('#approvalsTable thead tr:nth-child(2)').hide();
            alert('Error al cargar los filtros de la tabla. La tabla funcionará sin filtros avanzados.');
        }

        // Ocultar los filtros antiguos del formulario ya que ahora usamos DataTables
        $('.form-inline').hide();

        } catch (error) {
            console.error('Error al inicializar DataTables:', error);
            // Fallback: ocultar la segunda fila del thead si hay error
            $('#approvalsTable thead tr:nth-child(2)').hide();
            alert('Error al cargar los filtros de la tabla. La tabla funcionará sin filtros avanzados.');
        }

        // Función para resaltar texto de búsqueda
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            return true; // Permitir todo el filtrado normal
        });
        
        // Ocultar el buscador global ya que usamos filtros por columna
        $('.dataTables_filter').hide();
        
        // Remover los filtros antiguos del formulario ya que ahora usamos DataTables
        $('.form-inline').hide();
    });
</script>
@stop