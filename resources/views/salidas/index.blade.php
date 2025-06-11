@extends('adminlte::page')

@section('title', 'Salidas Pedagógicas')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Salidas Pedagógicas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Salidas</li>
                </ol>
            </div>
        </div>
        @if(!auth()->user()->hasRole('profesor'))
        <div class="row mb-2">
            <div class="col-sm-12">
                <a href="{{ route('salidas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Salida Pedagógica
                </a>
            </div>
        </div>
        @endif
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de Salidas Pedagógicas</h3>
            </div>
            <div class="card-body">
                <table id="salidas-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Consecutivo</th>
                            <th>Fecha Solicitud</th>
                            <th>Grados</th>
                            <th>Lugar</th>
                            <th>Responsable</th>
                            <th>Fecha Salida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salidas as $salida)
                            <tr>
                                <td>{{ $salida->consecutivo }}</td>
                                <td>{{ $salida->fecha_solicitud->format('d/m/Y') }}</td>
                                <td>{{ $salida->grados }}</td>
                                <td>{{ $salida->lugar }}</td>
                                <td>{{ $salida->responsable->name }}</td>
                                <td>{{ $salida->fecha_salida->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $salida->estado === 'Programada' ? 'primary' : 
                                        ($salida->estado === 'Realizada' ? 'success' : 'danger') }}">
                                        {{ $salida->estado }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('salidas.show', $salida) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!auth()->user()->hasRole('profesor'))
                                        <a href="{{ route('salidas.edit', $salida) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminacion('{{ $salida->id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="form-eliminar-{{ $salida->id }}" 
                                            action="{{ route('salidas.destroy', $salida) }}" 
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        .card-header { background-color: #364E76 !important; color: white; }
        .btn-primary { background-color: #364E76; border-color: #364E76; }
        .btn-primary:hover { background-color: #2B3E5F; border-color: #2B3E5F; }
        .dt-buttons {
            margin-bottom: 10px;
        }
        .dt-buttons .btn {
            background-color: #364E76;
            border-color: #364E76;
        }
        .dt-buttons .btn:hover {
            background-color: #2B3E5F;
            border-color: #2B3E5F;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            var buttonsConfig = [];
            
            // Solo agregar botón de exportar si el usuario no es profesor
            @if(!auth()->user()->hasRole('profesor'))
            buttonsConfig.push({
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-1"></i> Exportar a Excel',
                className: 'btn btn-sm btn-primary',
                title: 'Salidas Pedagógicas - ' + new Date().toLocaleDateString(),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6] // Exclude actions column
                }
            });
            @endif

            $('#salidas-table').DataTable({
                language: {
                    url: '{{ asset("js/dataTables.spanish.js") }}'
                },
                order: [[0, 'desc']],
                dom: 'Bfrtip',
                buttons: buttonsConfig
            });
        });

        function confirmarEliminacion(id) {
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
                    document.getElementById('form-eliminar-' + id).submit();
                }
            });
        }

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
