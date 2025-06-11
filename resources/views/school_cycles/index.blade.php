@extends('adminlte::page')

@section('title', 'Ciclos Escolares')

@section('content_header')
    <h1>Administrar Ciclos Escolares</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">Ciclos Escolares</h3>
                <a href="{{ route('school-cycles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nuevo Ciclo Escolar
                </a>
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
                <table id="cycles-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Fecha de Inicio</th>
                            <th>Longitud de Ciclo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schoolCycles as $cycle)
                            <tr>
                                <td>{{ $cycle->id }}</td>
                                <td>{{ $cycle->name }}</td>
                                <td>{{ $cycle->start_date->format('d/m/Y') }}</td>
                                <td>{{ $cycle->cycle_length }} días</td>
                                <td>
                                    @if($cycle->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('school-cycles.show', $cycle) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('school-cycles.edit', $cycle) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$cycle->active)
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete('{{ $cycle->id }}', '{{ $cycle->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $cycle->id }}" 
                                                action="{{ route('school-cycles.destroy', $cycle) }}" 
                                                method="POST" 
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay ciclos escolares registrados</td>
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
            $('#cycles-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
        
        function confirmDelete(id, name) {
            if (confirm('¿Está seguro que desea eliminar el ciclo escolar "' + name + '"?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@stop