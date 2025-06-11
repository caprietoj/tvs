@extends('adminlte::page')

@section('title', 'Bloqueos de Espacios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gestión de Bloqueos de Espacios</h1>
        <div>
            <a href="{{ route('space-blocks.create-weekly') }}" class="btn btn-institutional-blue mr-2">
                <i class="fas fa-calendar-week"></i> Bloqueo por dias de la semana...
            </a>
            <a href="{{ route('space-blocks.create') }}" class="btn btn-institutional-blue">
                <i class="fas fa-plus"></i> Bloqueo por ciclo escolar...
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
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
            
            @if($spaceBlocks->isEmpty())
                <div class="alert alert-info">
                    No hay bloqueos de espacios registrados. Cree un nuevo bloqueo para comenzar.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Ciclo Escolar</th>
                                <th>Día del Ciclo</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spaceBlocks as $block)
                                <tr>
                                    <td>{{ $block->space->name }}</td>
                                    <td>{{ $block->schoolCycle->name }}</td>
                                    <td>{{ $block->cycle_day }}</td>
                                    <td>{{ $block->reason ?? 'No especificado' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('space-blocks.edit', $block) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('space-blocks.destroy', $block) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar este bloqueo?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <style>
        .btn-institutional-blue {
            background-color: #364E76;
            border-color: #364E76;
            color: white;
        }
        
        .btn-institutional-blue:hover, .btn-institutional-blue:focus, .btn-institutional-blue:active {
            background-color: #2A3D5F;
            border-color: #2A3D5F;
            color: white;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[ 0, "asc" ], [ 2, "asc" ]]
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
@stop