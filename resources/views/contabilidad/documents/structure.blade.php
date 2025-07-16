@extends('adminlte::page')

@section('title', 'Estructura de Carpeta - Contabilidad')

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
                <a href="{{ route('contabilidad.documents.download', $document->id) }}" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-download mr-1"></i>Descargar ZIP
                </a>
                <a href="{{ route('contabilidad.documents.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Información de la carpeta -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong><i class="fas fa-info-circle mr-1"></i>Información</strong><br>
                            <small class="text-muted">
                                <strong>Archivo original:</strong> {{ $document->original_filename }}<br>
                                <strong>Subido por:</strong> {{ $document->user->name }}<br>
                                <strong>Fecha:</strong> {{ $document->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-files-o mr-1"></i>Total de archivos</strong><br>
                            <span class="h4 text-primary">{{ $document->file_count }}</span>
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
                            <thead class="bg-light">
                                <tr>
                                    <th>Nombre del archivo</th>
                                    <th>Ruta</th>
                                    <th>Extensión</th>
                                    <th>Tamaño</th>
                                    <th>Modificado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->folder_structure as $file)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $extension = strtolower($file['extension'] ?? '');
                                                    $iconClass = 'fas fa-file text-muted';
                                                    $iconColor = '#6c757d';
                                                    
                                                    switch($extension) {
                                                        case 'pdf':
                                                            $iconClass = 'fas fa-file-pdf';
                                                            $iconColor = '#dc3545';
                                                            break;
                                                        case 'doc':
                                                        case 'docx':
                                                            $iconClass = 'fas fa-file-word';
                                                            $iconColor = '#2b579a';
                                                            break;
                                                        case 'xls':
                                                        case 'xlsx':
                                                            $iconClass = 'fas fa-file-excel';
                                                            $iconColor = '#1d6f42';
                                                            break;
                                                        case 'ppt':
                                                        case 'pptx':
                                                            $iconClass = 'fas fa-file-powerpoint';
                                                            $iconColor = '#b7472a';
                                                            break;
                                                        case 'jpg':
                                                        case 'jpeg':
                                                        case 'png':
                                                        case 'gif':
                                                            $iconClass = 'fas fa-file-image';
                                                            $iconColor = '#fd7e14';
                                                            break;
                                                        case 'txt':
                                                            $iconClass = 'fas fa-file-alt';
                                                            $iconColor = '#6c757d';
                                                            break;
                                                        case 'zip':
                                                        case 'rar':
                                                            $iconClass = 'fas fa-file-archive';
                                                            $iconColor = '#e83e8c';
                                                            break;
                                                    }
                                                @endphp
                                                <i class="{{ $iconClass }} mr-2" style="color: {{ $iconColor }}"></i>
                                                <span>{{ $file['name'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ dirname($file['path']) != '.' ? dirname($file['path']) : '/' }}</small>
                                        </td>
                                        <td>
                                            @if($file['extension'])
                                                <span class="badge badge-secondary">{{ strtoupper($file['extension']) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ formatFileSize($file['size'] ?? 0) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($file['modified'])->format('d/m/Y H:i') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>No se pudo cargar la estructura de archivos</h5>
                        <p class="text-muted">La información de los archivos en esta carpeta no está disponible.</p>
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
        --warning: #ffc107;
        --info: #17a2b8;
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

    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table td {
        vertical-align: middle;
    }

    .alert-info {
        background-color: #f1f5fb;
        border-left: 4px solid #17a2b8;
        color: #495057;
    }

    .badge {
        font-size: 0.75em;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#files-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [[0, 'asc']],
            "pageLength": 25,
            "scrollX": true,
            "lengthChange": false,
            "searching": false,
            "dom": 'rt<"bottom"ip><"clear">'
        });
    });
</script>
@stop

@php
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
}
@endphp
