@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Gestión de Proveedores</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Proveedores</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-warning alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="mt-2">
                    <strong>Para instalar Laravel Excel:</strong>
                    <pre class="mt-2 bg-dark text-white p-2 rounded">composer require maatwebsite/excel</pre>
                </div>
            </div>
        @endif
        
        <div class="row mb-2">
            <div class="col-6">
                <div class="btn-group">
                    <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Agregar
                    </a>
                    <a href="{{ route('proveedores.import') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-file-import mr-1"></i> Importar
                    </a>
                    @if(class_exists('Maatwebsite\Excel\Facades\Excel'))
                        <a href="{{ route('proveedores.export') }}" class="btn btn-success ml-2">
                            <i class="fas fa-file-excel mr-1"></i> Exportar
                        </a>
                    @else
                        <button class="btn btn-secondary ml-2" title="Laravel Excel no está instalado" disabled>
                            <i class="fas fa-file-excel mr-1"></i> Exportar
                        </button>
                    @endif
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex justify-content-end">
                    <div class="form-group mr-2">
                        <select id="segmento-filter" class="form-control form-control-sm" style="width: 200px;">
                            <option value="">Todos los segmentos</option>
                            <option value="Papelería y útiles de oficina">Papelería y útiles de oficina</option>
                            <option value="Aseo y limpieza">Aseo y limpieza</option>
                            <option value="Tecnología y equipos de cómputo">Tecnología y equipos de cómputo</option>
                            <option value="Alimentos y cafetería">Alimentos y cafetería</option>
                            <option value="Materiales de construcción">Materiales de construcción</option>
                            <option value="Publicidad e impresión">Publicidad e impresión</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select id="estado-filter" class="form-control form-control-sm" style="width: 150px;">
                            <option value="">Todos los estados</option>
                            <option value="Seleccionado">Seleccionados</option>
                            <option value="No Seleccionado">No Seleccionados</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Proveedores</h3>
                    </div>
                    <div class="card-body">
                        <table id="proveedores-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre/Razón Social</th>
                                    <th>NIT</th>
                                    <th>Ciudad</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Contacto</th>
                                    <th>Puntaje Total</th>
                                    <th>Estado</th>
                                    <th>Segmento de mercado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($proveedores as $proveedor)
                                    <tr>
                                        <td>{{ $proveedor->nombre }}</td>
                                        <td>{{ $proveedor->nit }}</td>
                                        <td>{{ $proveedor->ciudad }}</td>
                                        <td>{{ $proveedor->telefono }}</td>
                                        <td>{{ $proveedor->email }}</td>
                                        <td>{{ $proveedor->persona_contacto }}</td>
                                        <td>{{ number_format($proveedor->puntaje_total, 2) }}</td>
                                        <td>
                                            @if($proveedor->estado == 'Seleccionado')
                                                <span class="badge badge-success">Seleccionado</span>
                                            @else
                                                <span class="badge badge-danger">No Seleccionado</span>
                                            @endif
                                        </td>
                                        <td>{{ $proveedor->market_segment }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('proveedores.show', $proveedor->id) }}" 
                                                   class="btn btn-info btn-sm" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('proveedores.edit', $proveedor->id) }}" 
                                                   class="btn btn-warning btn-sm"
                                                   title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="confirmarEliminacion('{{ $proveedor->id }}')"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <form id="form-eliminar-{{ $proveedor->id }}" 
                                                      action="{{ route('proveedores.destroy', $proveedor->id) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .card-primary:not(.card-outline) > .card-header {
            background-color: #364E76;
        }
        
        .btn-primary {
            background-color: #364E76;
            border-color: #364E76;
        }
        
        .btn-primary:hover {
            background-color: #2b3e5f;
            border-color: #2b3e5f;
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .card {
            background-color: #FEFEFE;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(54, 78, 118, 0.125);
            padding: 0.75rem 1.25rem;
        }
        
        .table thead th {
            border-bottom: 2px solid #364E76;
            color: #364E76;
            font-weight: 600;
        }
        
        .breadcrumb-item a {
            color: #364E76;
        }
        
        .btn-tool {
            color: #FEFEFE;
        }
        
        .btn-tool:hover {
            color: #e9ecef;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(54, 78, 118, 0.075);
        }
        
        .badge-danger {
            background-color: #dc3545;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #1f2d3d;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #364E76;
            border-radius: 4px;
            padding: 3px 6px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #364E76;
            border-radius: 4px;
            padding: 4px 24px 4px 8px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #364E76 !important;
            color: #FEFEFE !important;
            border: 1px solid #364E76;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #2b3e5f !important;
            color: #FEFEFE !important;
            border: 1px solid #2b3e5f;
        }
        
        table.dataTable thead th {
            background-color: #364E76;
            color: #FEFEFE;
            border-bottom: none;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(54, 78, 118, 0.05);
        }

        #estado-filter {
            border: 1px solid #364E76;
            border-radius: 4px;
            color: #364E76;
            background-color: #FEFEFE;
            font-size: 0.875rem;
            height: calc(1.8125rem + 2px);
        }
        
        #estado-filter:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#proveedores-table').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        targets: -1,
                        searchable: false,
                        orderable: false
                    }
                ]
            });

            // Modificar el filtro por estado
            $('#estado-filter').on('change', function() {
                let estado = $(this).val();
                // Usamos una expresión regular para búsqueda exacta
                let searchTerm = estado ? '^' + estado + '$' : '';
                table.column(7).search(searchTerm, true, false).draw();
            });

            // Agregar filtro por segmento de mercado
            $('#segmento-filter').on('change', function() {
                let segmento = $(this).val();
                table.column(8).search(segmento).draw();
            });

            // Resto del código existente...
        });

        function confirmarEliminacion(proveedorId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#364E76',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-eliminar-' + proveedorId).submit();
                }
            });
        }
        
        // Mensajes de éxito
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#364E76'
            });
        @endif
    </script>
@stop
