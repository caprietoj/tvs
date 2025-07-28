@extends('adminlte::page')

@section('title', 'Gestión Documental - Institucional')

@section('content_header')
    <h1 class="text-primary">Gestión Documental - Institucional</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Documentos Institucionales</h3>
            <a href="{{ route('institucional.documents.create') }}" class="btn btn-light">
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
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
                        <th>Tipo</th>
                        <th>Información</th>
                        <th>Subido por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($document->isFolder())
                                        <i class="fas fa-folder text-warning mr-2"></i>
                                    @else
                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                    @endif
                                    <span>{{ $document->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($document->isFolder())
                                    <span class="badge badge-info">CARPETA</span>
                                @else
                                    <span class="badge badge-secondary">PDF</span>
                                @endif
                            </td>
                            <td>
                                @if($document->isFolder())
                                    <small class="text-muted">
                                        {{ $document->file_count }} archivo(s)<br>
                                        {{ $document->formatted_size }}
                                    </small>
                                @else
                                    <small class="text-muted">
                                        {{ $document->formatted_size }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $document->user->name }}</td>
                            <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($document->isFolder())
                                        <a href="{{ route('institucional.documents.structure', $document->id) }}" 
                                           class="btn btn-sm btn-warning" title="Ver estructura">
                                            <i class="fas fa-sitemap"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('institucional.documents.download', $document->id) }}" 
                                       class="btn btn-sm btn-info" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form action="{{ route('institucional.documents.destroy', $document->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('¿Está seguro de eliminar este documento?');" 
                                          style="display: inline;">
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
                            <td colspan="6" class="text-center">No hay documentos disponibles</td>
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
    }

    .custom-card {
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn {
        border-radius: var(--border-radius);
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#documents-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[4, "desc"]], // Ordenar por fecha descendente
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": 5 } // Desactivar orden en columna de acciones
        ]
    });
});
</script>
@stop
