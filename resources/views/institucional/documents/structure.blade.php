@extends('adminlte::page')

@section('title', 'Estructura de Carpeta - Institucional')

@section('content_header')
    <h1 class="text-primary">Estructura de Carpeta - {{ $document->name }}</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">
                <i class="fas fa-folder mr-2"></i>{{ $document->name }}
            </h3>
            <div>
                <a href="{{ route('institucional.documents.download', $document->id) }}" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-download mr-1"></i>Descargar ZIP
                </a>
                <a href="{{ route('institucional.documents.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Información de la carpeta -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong><i class="fas fa-file-alt mr-1"></i>Archivos</strong><br>
                                <span class="h4 text-info">{{ $document->file_count }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-user mr-1"></i>Subido por</strong><br>
                                <span class="h5">{{ $document->user->name }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-hdd mr-1"></i>Tamaño total</strong><br>
                                <span class="h4 text-success">{{ $document->formatted_size }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-sitemap mr-1"></i>Tipo</strong><br>
                                <span class="badge badge-info h5">CARPETA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estructura de archivos -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt mr-2"></i>Archivos contenidos
                </h5>
            </div>
            <div class="card-body p-0">
                @if($document->folder_structure && count($document->folder_structure) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="files-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ruta</th>
                                    <th>Tamaño</th>
                                    <th>Tipo</th>
                                    <th>Fecha modificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->folder_structure as $file)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                                            {{ $file['name'] ?? 'Sin nombre' }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $file['path'] ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if(isset($file['size']))
                                                {{ formatFileSize($file['size']) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($file['extension']))
                                                <span class="badge badge-secondary">{{ strtoupper($file['extension']) }}</span>
                                            @else
                                                <span class="badge badge-secondary">PDF</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ isset($file['modified']) ? date('d/m/Y H:i', strtotime($file['modified'])) : 'N/A' }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center p-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No se encontró información de estructura de archivos.</p>
                    </div>
                @endif
            </div>
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
        border-top: none;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.75rem;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .text-info {
        color: #17a2b8 !important;
    }

    .text-success {
        color: #28a745 !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#files-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[0, "asc"]], // Ordenar por nombre ascendente
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "width": "30%", "targets": 0 }, // Nombre
            { "width": "25%", "targets": 1 }, // Ruta
            { "width": "15%", "targets": 2 }, // Tamaño
            { "width": "15%", "targets": 3 }, // Tipo
            { "width": "15%", "targets": 4 }  // Fecha
        ]
    });
});

// Función para formatear tamaño de archivo (disponible globalmente)
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

@php
    function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
@endphp
@stop
