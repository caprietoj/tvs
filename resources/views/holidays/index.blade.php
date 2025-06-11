@extends('adminlte::page')

@section('title', 'Días Festivos')

@section('content_header')
    <h1>Administrar Días Festivos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">Días Festivos</h3>
                <div>
                    <a href="{{ route('holidays.import.form') }}" class="btn btn-info mr-2">
                        <i class="fas fa-file-import"></i> Importar
                    </a>
                    <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nuevo Día Festivo
                    </a>
                </div>
            </div>
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

            <div class="table-responsive">
                <table id="holidays-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td>{{ $holiday->id }}</td>
                                <td>{{ $holiday->date->format('d/m/Y') }}</td>
                                <td>{{ $holiday->name }}</td>
                                <td>{{ $holiday->description ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('{{ $holiday->id }}', '{{ $holiday->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $holiday->id }}" 
                                              action="{{ route('holidays.destroy', $holiday) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay días festivos registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#holidays-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[1, "asc"]] // Ordenar por fecha ascendente
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
        
        function confirmDelete(id, name) {
            if (confirm('¿Está seguro que desea eliminar el día festivo "' + name + '"?\n\nEsto puede afectar a los días de ciclo generados.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@stop