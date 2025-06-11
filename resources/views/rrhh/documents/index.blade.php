@extends('adminlte::page')

@section('title', 'Gestión Documental - Recursos Humanos')

@section('content_header')
    <h1 class="text-primary">Gestión Documental - Recursos Humanos</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Documentos</h3>
            <a href="{{ route('rrhh.documents.create') }}" class="btn btn-light">
                <i class="fas fa-plus-circle"></i> Nuevo Documento
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped" id="documents-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Subido por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>{{ $document->name }}</td>
                            <td>{{ $document->user->name }}</td>
                            <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('rrhh.documents.download', $document->id) }}" 
                                       class="btn btn-sm btn-info" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form action="{{ route('rrhh.documents.destroy', $document->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('¿Está seguro de eliminar este documento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay documentos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .custom-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.2rem;
    }

    .btn {
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-group .btn {
        border-radius: 4px;
        margin-right: 2px;
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#documents-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [[2, 'desc']],
            "pageLength": 10
        });
    });
</script>
@stop
