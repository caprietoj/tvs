@extends('adminlte::page')

@section('title', 'Subir Documento - Enfermería')

@section('content_header')
    <h1 class="text-primary">Subir Documento - Enfermería</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Subir Nuevo Documento</h3>
        </div>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('enfermeria.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <input type="hidden" name="is_folder" id="is_folder" value="0">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <span id="name-label">Nombre del Documento</span> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature" id="name-icon"></i></span>
                            </div>
                            <input type="text" name="name" id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required 
                                placeholder="Ej: Protocolo de Atención">
                        </div>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="name-help">Ingrese un nombre descriptivo para identificar el documento.</small>
                    </div>

                    <!-- Upload Type Selection -->
                    <div class="form-group">
                        <label class="form-label">Tipo de Subida</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn upload-mode-toggle active" id="file-mode-btn">
                                <input type="radio" name="upload_mode" id="file-mode" autocomplete="off" checked>
                                <i class="fas fa-file-alt mr-2"></i>Archivo Individual
                            </label>
                            <label class="btn upload-mode-toggle" id="folder-mode-btn">
                                <input type="radio" name="upload_mode" id="folder-mode" autocomplete="off">
                                <i class="fas fa-folder mr-2"></i>Carpeta Completa
                            </label>
                        </div>
                    </div>

                    <!-- Upload para archivo individual -->
                    <div class="form-group" id="file-upload-section">
                        <label for="document" class="form-label">
                            Archivo PDF <span class="text-danger">*</span>
                        </label>
                        <div class="document-upload-container">
                            <input type="file" class="document-upload-input @error('document') is-invalid @enderror" 
                                id="document" name="document" accept=".pdf">
                            <label for="document" class="document-upload-label">
                                <div class="document-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="document-upload-text">
                                    <span id="document-text">Arrastre su archivo PDF aquí o haga clic para seleccionar</span>
                                </div>
                            </label>
                            <div class="document-upload-info" id="document-info">
                                <span class="document-upload-filename" id="document-name"></span>
                                <span class="document-upload-size" id="document-size"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ml-2" id="remove-document">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @error('document')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Formato permitido: PDF. Tamaño máximo: 10MB.</small>
                    </div>

                    <!-- Upload para carpeta -->
                    <div class="form-group d-none" id="folder-upload-section">
                        <label for="folder" class="form-label">
                            Seleccionar Carpeta <span class="text-danger">*</span>
                        </label>
                        <div class="folder-upload-container">
                            <input type="file" class="folder-upload-input @error('folder') is-invalid @enderror" 
                                id="folder" name="folder[]" webkitdirectory multiple>
                            <label for="folder" class="folder-upload-label">
                                <div class="folder-upload-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="folder-upload-text">
                                    <span id="folder-text">Haga clic para seleccionar una carpeta</span>
                                </div>
                            </label>
                            <div class="folder-upload-info" id="folder-info">
                                <div id="folder-details"></div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="remove-folder">
                                    <i class="fas fa-times"></i> Quitar carpeta
                                </button>
                            </div>
                        </div>
                        @error('folder')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Seleccione una carpeta con archivos. Todos los archivos y subcarpetas serán incluidos.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Vista previa para archivos -->
                    <div class="card" id="file-preview-card">
                        <div class="card-header">
                            <h5 class="mb-0">Vista previa</h5>
                        </div>
                        <div class="card-body p-0 d-flex align-items-center justify-content-center" style="height: 300px">
                            <div id="preview-placeholder" class="text-center p-4">
                                <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Vista previa del documento</p>
                            </div>
                            <div id="preview-container" class="d-none" style="height: 100%; width: 100%;">
                                <iframe id="pdf-preview" style="height: 100%; width: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Vista previa para carpetas -->
                    <div class="card d-none" id="folder-preview-card">
                        <div class="card-header">
                            <h5 class="mb-0">Estructura de la carpeta</h5>
                        </div>
                        <div class="card-body" style="height: 300px; overflow-y: auto;">
                            <div id="folder-structure" class="text-center p-4">
                                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Seleccione una carpeta para ver su estructura</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3" id="file-info">
                <div class="d-flex">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Información de documentos</h5>
                        <p class="mb-0">Los documentos subidos serán accesibles para todo el personal autorizado del departamento de Enfermería. Asegúrese de que el documento no contenga información sensible o confidencial.</p>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-3 d-none" id="folder-info-alert">
                <div class="d-flex">
                    <div class="mr-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Subida de carpetas</h5>
                        <p class="mb-0">Al subir una carpeta, se preservará toda la estructura de directorios y archivos. Esto es útil para documentos organizados en múltiples carpetas y subcarpetas.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('enfermeria.documents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-upload mr-2" id="submit-icon"></i><span id="submit-text">Subir Documento</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Cargando...</span>
                </div>
                <h4 class="modal-title">Subiendo documento</h4>
                <p>Por favor espere mientras se sube su documento...</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                        role="progressbar" style="width: 0%" id="uploadProgress"></div>
                </div>
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

    .form-label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        height: auto;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .btn {
        border-radius: 6px;
        padding: 0.6rem 1.2rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }

    .btn-primary:hover {
        background-color: #2a3d5f;
        border-color: #2a3d5f;
        transform: translateY(-1px);
    }

    /* Upload mode toggle */
    .upload-mode-toggle {
        border-radius: 6px;
        overflow: hidden;
    }

    .btn-group-toggle .btn {
        border-radius: 0;
        border: 2px solid #364E76;
        padding: 0.6rem 1.2rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        background-color: white;
        color: #364E76;
    }

    .btn-group-toggle .btn:first-child {
        border-radius: 6px 0 0 6px;
        border-right: 1px solid #364E76;
    }

    .btn-group-toggle .btn:last-child {
        border-radius: 0 6px 6px 0;
        border-left: 1px solid #364E76;
    }

    .btn-group-toggle .btn:hover {
        background-color: #f8f9fa;
        color: #364E76;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(54, 78, 118, 0.2);
    }

    .btn-group-toggle .btn.active,
    .btn-group-toggle .btn:active {
        background-color: #364E76 !important;
        border-color: #364E76 !important;
        color: white !important;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
    }

    .btn-group-toggle .btn.active:hover {
        background-color: #2a3d5f !important;
        border-color: #2a3d5f !important;
        transform: none;
    }

    /* Document upload styling */
    .document-upload-container {
        position: relative;
        width: 100%;
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .document-upload-container:hover {
        border-color: #364E76;
        background-color: #f1f5fb;
    }

    .document-upload-container.dragover {
        border-color: #28a745;
        background-color: rgba(40, 167, 69, 0.1);
    }

    /* Folder upload styling */
    .folder-upload-container {
        position: relative;
        width: 100%;
        border: 2px dashed #007bff;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9ff;
    }

    .folder-upload-container:hover {
        border-color: #0056b3;
        background-color: #e7f3ff;
    }

    .folder-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .document-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .document-upload-label, .folder-upload-label {
        display: block;
        padding: 20px;
        margin-bottom: 0;
        cursor: pointer;
    }

    .document-upload-icon, .folder-upload-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .folder-upload-icon {
        color: #007bff;
    }

    .document-upload-text, .folder-upload-text {
        color: #495057;
        font-size: 1rem;
    }

    .document-upload-info, .folder-upload-info {
        display: none;
        background-color: #e9ecef;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .folder-upload-info {
        display: none;
        background-color: #e7f3ff;
        border: 1px solid #bee5eb;
    }

    .document-upload-filename {
        font-weight: 600;
        color: #364E76;
        margin-right: 10px;
    }

    .document-upload-size {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .folder-structure-item {
        padding: 2px 0;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }

    .folder-structure-item i {
        margin-right: 8px;
        width: 16px;
    }

    .alert-info {
        background-color: #f1f5fb;
        border-left: 4px solid #17a2b8;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Variables para manejo de archivos
        const documentInput = document.getElementById('document');
        const folderInput = document.getElementById('folder');
        const documentInfo = document.getElementById('document-info');
        const folderInfo = document.getElementById('folder-info');
        const uploadContainer = document.querySelector('.document-upload-container');
        const folderContainer = document.querySelector('.folder-upload-container');
        const uploadForm = document.getElementById('uploadForm');
        const loadingModal = $('#loadingModal');
        const isFolderInput = document.getElementById('is_folder');

        // Toggle entre modos
        $('input[name="upload_mode"]').on('change', function() {
            const isFolder = $('#folder-mode').is(':checked');
            
            // Actualizar clases active
            if (isFolder) {
                $('#file-mode-btn').removeClass('active');
                $('#folder-mode-btn').addClass('active');
            } else {
                $('#folder-mode-btn').removeClass('active');
                $('#file-mode-btn').addClass('active');
            }
            
            if (isFolder) {
                // Modo carpeta
                $('#file-upload-section').addClass('d-none');
                $('#folder-upload-section').removeClass('d-none');
                $('#file-preview-card').addClass('d-none');
                $('#folder-preview-card').removeClass('d-none');
                $('#file-info').addClass('d-none');
                $('#folder-info-alert').removeClass('d-none');
                $('#is_folder').val('1');
                $('#document').removeAttr('required');
                $('#folder').attr('required', 'required');
                
                // Actualizar labels y textos
                $('#name-label').text('Nombre de la Carpeta');
                $('#name-icon').removeClass('fa-file-signature').addClass('fa-folder');
                $('#name-help').text('Ingrese un nombre descriptivo para identificar la carpeta.');
                $('#submit-text').text('Subir Carpeta');
                $('#submit-icon').removeClass('fa-upload').addClass('fa-folder-plus');
            } else {
                // Modo archivo
                $('#folder-upload-section').addClass('d-none');
                $('#file-upload-section').removeClass('d-none');
                $('#folder-preview-card').addClass('d-none');
                $('#file-preview-card').removeClass('d-none');
                $('#folder-info-alert').addClass('d-none');
                $('#file-info').removeClass('d-none');
                $('#is_folder').val('0');
                $('#folder').removeAttr('required');
                $('#document').attr('required', 'required');
                
                // Actualizar labels y textos
                $('#name-label').text('Nombre del Documento');
                $('#name-icon').removeClass('fa-folder').addClass('fa-file-signature');
                $('#name-help').text('Ingrese un nombre descriptivo para identificar el documento.');
                $('#submit-text').text('Subir Documento');
                $('#submit-icon').removeClass('fa-folder-plus').addClass('fa-upload');
            }
        });

        // Manejo de archivo individual
        if (documentInput) {
            documentInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    updateFileInfo(this.files[0]);
                }
            });
        }

        // Manejo de carpeta
        if (folderInput) {
            folderInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    updateFolderInfo(this.files);
                }
            });
        }

        // Botón remover archivo
        $('#remove-document').on('click', function() {
            documentInput.value = '';
            resetFileInfo();
        });

        // Botón remover carpeta
        $('#remove-folder').on('click', function() {
            folderInput.value = '';
            resetFolderInfo();
        });

        // Submit form
        uploadForm.addEventListener('submit', function() {
            const isFolder = $('#is_folder').val() === '1';
            if ((isFolder && folderInput.files.length > 0) || (!isFolder && documentInput.files.length > 0)) {
                loadingModal.modal('show');
                return true;
            }
            return false;
        });

        function updateFileInfo(file) {
            if (file.type !== 'application/pdf') {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipo de archivo no válido',
                    text: 'Por favor seleccione un archivo PDF',
                    confirmButtonColor: '#364E76'
                });
                documentInput.value = '';
                return;
            }

            const fileName = file.name;
            const fileSize = formatFileSize(file.size);
            
            $('#document-name').text(fileName);
            $('#document-size').text(fileSize);
            $('#document-info').css('display', 'flex');
            
            // Mostrar vista previa del PDF
            const fileURL = URL.createObjectURL(file);
            $('#pdf-preview').attr('src', fileURL);
            $('#preview-placeholder').addClass('d-none');
            $('#preview-container').removeClass('d-none');
        }

        function updateFolderInfo(files) {
            const fileCount = files.length;
            let totalSize = 0;

            // Calcular tamaño total
            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
            }

            // Mostrar información
            const folderDetails = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Archivos seleccionados:</strong>
                    <span class="badge badge-primary">${fileCount}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Tamaño total:</strong>
                    <span class="text-success">${formatFileSize(totalSize)}</span>
                </div>
            `;
            
            $('#folder-details').html(folderDetails);
            $('#folder-info').show();

            // Mostrar estructura en vista previa
            displayFolderStructure(files);
        }

        function displayFolderStructure(files) {
            const structure = $('<div class="folder-tree"></div>');
            const folders = new Set();
            
            // Obtener todas las carpetas únicas
            for (let i = 0; i < files.length; i++) {
                const path = files[i].webkitRelativePath;
                const pathParts = path.split('/');
                
                for (let j = 1; j < pathParts.length; j++) {
                    const folderPath = pathParts.slice(0, j).join('/');
                    if (folderPath) folders.add(folderPath);
                }
            }

            // Crear vista en árbol
            const sortedFolders = Array.from(folders).sort();

            // Mostrar estructura limitada
            let itemCount = 0;
            const maxItems = 15;

            sortedFolders.slice(0, maxItems).forEach(folder => {
                const level = folder.split('/').length - 1;
                const folderName = folder.split('/').pop();
                const indent = '&nbsp;'.repeat(level * 4);
                
                structure.append(`
                    <div class="folder-structure-item">
                        ${indent}<i class="fas fa-folder text-warning"></i> ${folderName}
                    </div>
                `);
                itemCount++;
            });

            if (sortedFolders.length > maxItems) {
                structure.append(`
                    <div class="folder-structure-item text-muted">
                        <i class="fas fa-ellipsis-h"></i> y ${sortedFolders.length - maxItems} carpetas más...
                    </div>
                `);
            }

            $('#folder-structure').html(structure);
        }

        function resetFileInfo() {
            $('#document-info').hide();
            $('#preview-container').addClass('d-none');
            $('#preview-placeholder').removeClass('d-none');
        }

        function resetFolderInfo() {
            $('#folder-info').hide();
            $('#folder-structure').html(`
                <div class="text-center p-4">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Seleccione una carpeta para ver su estructura</p>
                </div>
            `);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    });
</script>
@stop
