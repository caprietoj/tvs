@extends('adminlte::page')

@section('title', 'Previsualizar Carpeta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-folder-open mr-2"></i>{{ $document->name }}
        </h1>
        <div>
            <a href="{{ route('enfermeria.documents.download', $document->id) }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i>Descargar ZIP
            </a>
            <a href="{{ route('enfermeria.documents.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="text-white">
            <h3 class="card-title mb-1">
                <i class="fas fa-folder mr-2"></i>{{ $document->original_filename }}
            </h3>
            <small>
                <i class="fas fa-user mr-1"></i>Subido por: {{ $document->user->name }} | 
                <i class="fas fa-calendar mr-1"></i>{{ $document->created_at->format('d/m/Y H:i') }} |
                <i class="fas fa-file mr-1"></i>{{ $document->file_count }} archivo(s) |
                <i class="fas fa-hdd mr-1"></i>{{ $document->formatted_size }}
            </small>
        </div>
    </div>
    <div class="card-body">
        
        {{-- Pestañas de navegación --}}
        <ul class="nav nav-tabs mb-3" id="viewTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="grid-tab" data-toggle="tab" href="#gridView" role="tab">
                    <i class="fas fa-th mr-1"></i>Vista de Cuadrícula
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="list-tab" data-toggle="tab" href="#listView" role="tab">
                    <i class="fas fa-list mr-1"></i>Vista de Lista
                </a>
            </li>
        </ul>

        <div class="tab-content" id="viewTabsContent">
            
            {{-- Vista de Cuadrícula --}}
            <div class="tab-pane fade show active" id="gridView" role="tabpanel">
                <div class="folder-explorer">
                    <div class="breadcrumb-nav mb-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item active">
                                    <i class="fas fa-home mr-1"></i>{{ $document->original_filename }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="file-grid">
                        @if(count($structure['folders']) > 0 || count($structure['files']) > 0)
                            
                            {{-- Mostrar carpetas primero --}}
                            @foreach($structure['folders'] as $folderName => $folderContent)
                                <div class="file-item folder-item" data-type="folder" data-name="{{ $folderName }}">
                                    <div class="file-icon">
                                        <i class="fas fa-folder fa-3x text-warning"></i>
                                    </div>
                                    <div class="file-info">
                                        <div class="file-name" title="{{ $folderName }}">
                                            {{ $folderName }}
                                        </div>
                                        <div class="file-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-folder-open mr-1"></i>Carpeta
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Mostrar archivos --}}
                            @foreach($structure['files'] as $file)
                                <div class="file-item" data-type="file" data-url="{{ $file['url'] }}" data-ext="{{ $file['extension'] }}" data-name="{{ $file['name'] }}">
                                    <div class="file-icon">
                                        @if($file['extension'] == 'pdf')
                                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        @elseif(in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <i class="fas fa-file-image fa-3x text-info"></i>
                                        @elseif(in_array($file['extension'], ['doc', 'docx']))
                                            <i class="fas fa-file-word fa-3x text-primary"></i>
                                        @elseif(in_array($file['extension'], ['xls', 'xlsx']))
                                            <i class="fas fa-file-excel fa-3x text-success"></i>
                                        @elseif(in_array($file['extension'], ['ppt', 'pptx']))
                                            <i class="fas fa-file-powerpoint fa-3x text-warning"></i>
                                        @elseif(in_array($file['extension'], ['zip', 'rar', '7z']))
                                            <i class="fas fa-file-archive fa-3x text-secondary"></i>
                                        @else
                                            <i class="fas fa-file fa-3x text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="file-info">
                                        <div class="file-name" title="{{ $file['name'] }}">
                                            {{ $file['name'] }}
                                        </div>
                                        <div class="file-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-hdd mr-1"></i>{{ $file['size'] }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>Esta carpeta está vacía
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vista de Lista --}}
            <div class="tab-pane fade" id="listView" role="tabpanel">
                <div class="mb-3">
                    <input type="text" id="searchFiles" class="form-control" placeholder="🔍 Buscar archivos...">
                </div>
                
                @if(isset($structure['allFiles']) && count($structure['allFiles']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="filesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%"><i class="fas fa-file"></i></th>
                                    <th width="40%">Nombre</th>
                                    <th width="20%">Ubicación</th>
                                    <th width="15%">Tipo</th>
                                    <th width="10%">Tamaño</th>
                                    <th width="10%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($structure['allFiles'] as $file)
                                    <tr class="file-row" data-filename="{{ strtolower($file['name']) }}" data-location="{{ strtolower($file['folder']) }}">
                                        <td>
                                            @if($file['extension'] == 'pdf')
                                                <i class="fas fa-file-pdf fa-lg text-danger"></i>
                                            @elseif(in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <i class="fas fa-file-image fa-lg text-info"></i>
                                            @elseif(in_array($file['extension'], ['doc', 'docx']))
                                                <i class="fas fa-file-word fa-lg text-primary"></i>
                                            @elseif(in_array($file['extension'], ['xls', 'xlsx']))
                                                <i class="fas fa-file-excel fa-lg text-success"></i>
                                            @elseif(in_array($file['extension'], ['ppt', 'pptx']))
                                                <i class="fas fa-file-powerpoint fa-lg text-warning"></i>
                                            @else
                                                <i class="fas fa-file fa-lg text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $file['name'] }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-folder mr-1"></i>{{ $file['folder'] }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ strtoupper($file['extension']) }}</span>
                                        </td>
                                        <td>{{ $file['size'] }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-primary btn-preview-file" 
                                                        data-url="{{ $file['url'] }}" 
                                                        data-ext="{{ $file['extension'] }}"
                                                        data-name="{{ $file['name'] }}"
                                                        title="Previsualizar">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ $file['url'] }}" 
                                                   class="btn btn-success" 
                                                   download
                                                   title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="fileCount">{{ count($structure['allFiles']) }}</span> archivo(s) en total
                        </small>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>No hay archivos en esta carpeta
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal de previsualización --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye mr-2"></i>Previsualización
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="previewContainer" style="min-height: 600px; background-color: #525659;">
                    <!-- Contenido dinámico -->
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadFileBtn" class="btn btn-success" download>
                    <i class="fas fa-download mr-1"></i>Descargar
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .folder-explorer {
        background-color: #fff;
    }

    .nav-tabs .nav-link {
        color: #495057;
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .nav-tabs .nav-link.active {
        color: #364E76;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 600;
    }

    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
    }

    .breadcrumb {
        background-color: #f8f9fa;
        border-radius: 6px;
        padding: 12px 15px;
    }

    .file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 20px;
        padding: 20px 10px;
    }

    .file-item {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-item:hover {
        border-color: #364E76;
        box-shadow: 0 4px 12px rgba(54, 78, 118, 0.15);
        transform: translateY(-2px);
    }

    .file-item.folder-item:hover {
        border-color: #ffc107;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
    }

    .file-icon {
        margin-bottom: 10px;
    }

    .file-name {
        font-size: 13px;
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-meta {
        font-size: 11px;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,.08);
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    #previewContainer iframe,
    #previewContainer img {
        width: 100%;
        height: 600px;
        border: none;
        display: block;
    }

    #previewContainer img {
        object-fit: contain;
        background-color: #525659;
    }

    .modal-xl {
        max-width: 90%;
    }

    /* Estilos para la tabla de archivos */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .thead-light th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }

    #searchFiles {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
        font-size: 14px;
    }

    #searchFiles:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .file-row {
        transition: background-color 0.2s ease;
    }

    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Viewer de Office Online */
    #previewContainer .office-viewer {
        width: 100%;
        height: 600px;
        border: none;
    }

    .no-results-message {
        display: none;
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .no-results-message i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    
    // Función para previsualizar archivos
    function previewFile(fileUrl, fileExt, fileName) {
        $('#previewModalLabel').html('<i class="fas fa-eye mr-2"></i>' + fileName);
        $('#downloadFileBtn').attr('href', fileUrl);
        
        const container = $('#previewContainer');
        container.empty();
        
        if (fileExt === 'pdf') {
            // Previsualizar PDF
            container.html(`
                <iframe 
                    src="${fileUrl}" 
                    type="application/pdf" 
                    width="100%" 
                    height="600">
                    <p>Su navegador no soporta la visualización de PDFs. 
                       <a href="${fileUrl}" download>Descargar el archivo</a>
                    </p>
                </iframe>
            `);
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
            // Previsualizar imagen
            container.html(`
                <img src="${fileUrl}" alt="${fileName}" class="img-fluid">
            `);
        } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExt)) {
            // Previsualizar documentos de Office usando Office Online Viewer
            const officeViewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + fileUrl);
            container.html(`
                <iframe 
                    src="${officeViewerUrl}" 
                    class="office-viewer"
                    frameborder="0">
                    <p>Su navegador no soporta la visualización de documentos de Office. 
                       <a href="${fileUrl}" download>Descargar el archivo</a>
                    </p>
                </iframe>
            `);
        } else {
            // Otros tipos de archivo - mostrar mensaje
            container.html(`
                <div class="p-5 text-center text-white">
                    <i class="fas fa-file fa-5x mb-3"></i>
                    <h4>Previsualización no disponible</h4>
                    <p>Este tipo de archivo (${fileExt.toUpperCase()}) no puede ser previsualizado en el navegador.</p>
                    <a href="${fileUrl}" class="btn btn-success mt-3" download>
                        <i class="fas fa-download mr-1"></i>Descargar archivo
                    </a>
                </div>
            `);
        }
        
        $('#previewModal').modal('show');
    }
    
    // Manejar clic en archivos en la vista de cuadrícula
    $('#gridView .file-item[data-type="file"]').on('click', function() {
        const fileUrl = $(this).data('url');
        const fileExt = $(this).data('ext');
        const fileName = $(this).data('name');
        
        previewFile(fileUrl, fileExt, fileName);
    });

    // Manejar clic en archivos en la vista de lista
    $(document).on('click', '.btn-preview-file', function(e) {
        e.stopPropagation();
        const fileUrl = $(this).data('url');
        const fileExt = $(this).data('ext');
        const fileName = $(this).data('name');
        
        previewFile(fileUrl, fileExt, fileName);
    });

    // También permitir clic en la fila completa
    $(document).on('click', '.file-row', function(e) {
        if (!$(e.target).closest('.btn-group').length) {
            const previewBtn = $(this).find('.btn-preview-file');
            const fileUrl = previewBtn.data('url');
            const fileExt = previewBtn.data('ext');
            const fileName = previewBtn.data('name');
            
            previewFile(fileUrl, fileExt, fileName);
        }
    });

    // Manejar clic en carpetas
    $('.file-item[data-type="folder"]').on('click', function() {
        const folderName = $(this).data('name');
        Swal.fire({
            icon: 'info',
            title: 'Carpeta: ' + folderName,
            text: 'Esta es una subcarpeta. Todos los archivos se muestran en la vista de lista.',
            confirmButtonColor: '#364E76'
        });
    });

    // Búsqueda de archivos en tiempo real
    $('#searchFiles').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleCount = 0;
        
        $('.file-row').each(function() {
            const fileName = $(this).data('filename');
            const location = $(this).data('location');
            const searchText = fileName + ' ' + location;
            
            if (searchText.indexOf(searchTerm) > -1) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Actualizar contador
        $('#fileCount').text(visibleCount);
        
        // Mostrar mensaje si no hay resultados
        if (visibleCount === 0) {
            if ($('.no-results-message').length === 0) {
                $('#filesTable').after(`
                    <div class="no-results-message">
                        <i class="fas fa-search"></i>
                        <h5>No se encontraron archivos</h5>
                        <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    </div>
                `);
            }
            $('.no-results-message').show();
            $('#filesTable').hide();
        } else {
            $('.no-results-message').hide();
            $('#filesTable').show();
        }
    });

    // Limpiar búsqueda al cambiar de pestaña
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#listView') {
            $('#searchFiles').val('');
            $('.file-row').show();
            $('#fileCount').text($('.file-row').length);
            $('.no-results-message').remove();
            $('#filesTable').show();
        }
    });
});
</script>
@stop
