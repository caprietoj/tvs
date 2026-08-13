@extends('adminlte::page')

@section('title', 'Estructura de Carpeta - SST - Seguridad y Salud en el Trabajo')

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
                <a href="{{ route('sst.documents.download', $document->id) }}" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-download mr-1"></i>Descargar ZIP
                </a>
                <a href="{{ route('sst.documents.index') }}" class="btn btn-secondary btn-sm">
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
            <div class="card-body">
                @if($document->folder_structure)
                    <div id="file-tree">
                        @include('sst.documents.partials.folder', ['folderData' => $document->folder_structure, 'folderName' => $document->original_filename, 'level' => 0])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No se pudo cargar la estructura de la carpeta.</p>
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

    /* File tree styling */
    .file-tree {
        font-family: 'Courier New', monospace;
        line-height: 1.6;
    }

    .tree-item {
        padding: 3px 0;
        border-left: 1px solid #dee2e6;
        margin-left: 10px;
        padding-left: 15px;
        position: relative;
    }

    .tree-item:before {
        content: '';
        position: absolute;
        left: -1px;
        top: 15px;
        width: 15px;
        height: 1px;
        background: #dee2e6;
    }

    .tree-item:last-child {
        border-left: 1px solid transparent;
    }

    .tree-item:last-child:before {
        content: '';
        position: absolute;
        left: -1px;
        top: 0;
        width: 1px;
        height: 15px;
        background: #dee2e6;
    }

    .tree-folder {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }

    .tree-file {
        color: #6c757d;
        padding: 2px 0;
    }

    .tree-file i {
        margin-right: 8px;
        width: 16px;
    }

    .file-size {
        font-size: 0.8rem;
        color: #adb5bd;
        margin-left: auto;
    }

    .tree-level-0 {
        border-left: none;
        margin-left: 0;
        padding-left: 0;
    }

    .tree-level-0:before {
        display: none;
    }

    .folder-toggle {
        cursor: pointer;
        user-select: none;
        padding: 2px;
        border-radius: 3px;
        transition: background-color 0.2s;
    }

    .folder-toggle:hover {
        background-color: #f8f9fa;
    }

    .folder-contents {
        transition: all 0.3s ease;
    }

    .folder-contents.collapsed {
        display: none;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Toggle folder expansion
        $('.folder-toggle').click(function() {
            const icon = $(this).find('i.fa-chevron-right, i.fa-chevron-down');
            const contents = $(this).siblings('.folder-contents');
            
            if (contents.hasClass('collapsed')) {
                contents.removeClass('collapsed');
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            } else {
                contents.addClass('collapsed');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            }
        });

        // Initialize some folders as collapsed if there are many levels
        $('.tree-level-2, .tree-level-3, .tree-level-4').each(function() {
            $(this).find('.folder-contents').first().addClass('collapsed');
            $(this).find('i.fa-chevron-down').first().removeClass('fa-chevron-down').addClass('fa-chevron-right');
        });
    });
</script>
@stop
