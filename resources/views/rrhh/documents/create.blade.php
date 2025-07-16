@extends('adminlte::page')

@section('title', 'Subir Documento - Recursos Humanos')

@section('content_header')
    <h1 class="text-primary">Subir Documento - Recursos Humanos</h1>
@stop

@section('content')
<div class="card main-card custom-card">
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

        <form action="{{ route('rrhh.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nombre del Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input type="text" name="name" id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required 
                                placeholder="Ej: Política de Personal">
                        </div>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ingrese un nombre descriptivo para identificar el documento.</small>
                    </div>

                    <!-- Upload Type Selection -->
                    <div class="form-group">
                        <label class="form-label">Tipo de Subida</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn upload-mode-toggle active" id="file-tab">
                                <input type="radio" name="upload_type" id="file" value="file" checked>
                                <i class="fas fa-file-pdf mr-2"></i>Archivo PDF
                            </label>
                            <label class="btn upload-mode-toggle" id="folder-tab">
                                <input type="radio" name="upload_type" id="folder" value="folder">
                                <i class="fas fa-folder mr-2"></i>Carpeta
                            </label>
                        </div>
                    </div>
                    <!-- File Upload -->
                    <div class="form-group" id="file-upload-container">
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

                    <!-- Folder Upload -->
                    <div class="form-group d-none" id="folder-upload-container">
                        <label for="folder" class="form-label">
                            Seleccionar Carpeta <span class="text-danger">*</span>
                        </label>
                        <div class="folder-upload-container">
                            <input type="file" class="folder-upload-input @error('folder') is-invalid @enderror" 
                                id="folder" name="folder[]" webkitdirectory directory multiple>
                            <!-- Campos ocultos para enviar estructura -->
                            <div id="hidden-inputs-container"></div>
                            <label for="folder" class="folder-upload-label">
                                <div class="folder-upload-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="folder-upload-text">
                                    <span id="folder-text">Haga clic para seleccionar una carpeta</span>
                                </div>
                            </label>
                            <div class="folder-upload-info" id="folder-info">
                                <div class="folder-summary">
                                    <span class="folder-upload-filename" id="folder-name"></span>
                                    <span class="folder-upload-count" id="folder-count"></span>
                                    <span class="folder-upload-size" id="folder-size"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ml-2" id="remove-folder">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="folder-files-preview d-none" id="folder-preview">
                                <small class="text-muted">Vista previa de archivos:</small>
                                <div class="file-list" id="file-list"></div>
                            </div>
                        </div>
                        @error('folder')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Seleccione una carpeta de su computadora. Se subirán todos los archivos contenidos.<br>
                            <strong>Nota:</strong> Compatible con Chrome, Edge y navegadores modernos.
                        </small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card preview-card">
                        <div class="card-header" style="background-color: transparent;">
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
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <div class="d-flex">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Información de documentos</h5>
                        <p class="mb-2">Los documentos subidos serán accesibles para todo el personal autorizado del departamento de Recursos Humanos.</p>
                        <p class="mb-0">
                            <strong>Archivo PDF:</strong> Suba documentos individuales en formato PDF.<br>
                            <strong>Carpeta:</strong> Seleccione una carpeta completa de su computadora. Se mantendrá la estructura de archivos y subcarpetas.<br>
                            <small class="text-muted">Nota: La selección de carpetas funciona en navegadores modernos (Chrome, Edge, Firefox).</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('rrhh.documents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-upload mr-2"></i>Subir Documento
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
    .custom-card {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 10px;
        overflow: hidden;
        background-color: #ffffff;
    }

    .main-card .card-header {
        background: linear-gradient(135deg, 0%, #4a628a 100%);
        border-bottom: none;
        padding: 1.25rem;
    }

    .preview-card .card-header {
        background-color: transparent !important;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
    }

    .card-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        color: #495057;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .alert-info {
        background-color: #f1f5fb;
        border-left: 4px solid #17a2b8;
        color: #495057;
    }

    /* Upload type toggle */
    .upload-mode-toggle {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        border: 1px solid #ddd;
        background-color: white;
        color: #495057;
        transition: all 0.3s ease;
        border-radius: 6px 0 0 6px;
    }

    .upload-mode-toggle:hover {
        background-color: #f8f9fa;
        border-color: #364E76;
        color: #364E76;
    }

    .upload-mode-toggle:last-child {
        border-radius: 0 6px 6px 0;
    }

    .upload-mode-toggle.active {
        background-color: #364E76;
        border-color: #364E76;
        color: white;
    }

    .upload-mode-toggle.active:hover {
        background-color: #2a3a5a;
        border-color: #2a3a5a;
    }

    /* Folder upload styling */
    .folder-upload-container {
        position: relative;
        width: 100%;
        border: 2px dashed #17a2b8;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .folder-upload-container:hover {
        border-color: #138496;
        background-color: #e3f2fd;
    }

    .folder-upload-container.dragover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }

    .folder-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .folder-upload-label {
        display: block;
        padding: 20px;
        margin-bottom: 0;
        cursor: pointer;
    }

    .folder-upload-icon {
        font-size: 3rem;
        color: #17a2b8;
        margin-bottom: 15px;
    }

    .folder-upload-text {
        font-size: 1.1rem;
        color: #495057;
        font-weight: 500;
    }

    .folder-upload-info {
        display: none;
        padding: 15px;
        background-color: #e8f5e8;
        border-radius: 6px;
        margin-top: 10px;
    }

    .folder-upload-info.show {
        display: block;
    }

    .folder-summary {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .folder-upload-filename {
        font-weight: bold;
        color: #155724;
    }

    .folder-upload-count,
    .folder-upload-size {
        font-size: 0.9rem;
        color: #155724;
    }

    .folder-files-preview {
        max-height: 200px;
        overflow-y: auto;
        margin-top: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .file-list {
        text-align: left;
    }

    .file-item {
        padding: 5px 0;
        border-bottom: 1px solid #dee2e6;
        font-size: 0.9rem;
    }

    .file-item:last-child {
        border-bottom: none;
    }

    /* Document upload styling */
    .document-upload-container {
        position: relative;
        width: 100%;
        border: 2px dashed #007bff;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .document-upload-container:hover {
        border-color: #0056b3;
        background-color: #e3f2fd;
    }

    .document-upload-container.dragover {
        border-color: #0056b3;
        background-color: rgba(0, 123, 255, 0.1);
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

    .document-upload-label {
        display: block;
        padding: 20px;
        margin-bottom: 0;
        cursor: pointer;
    }

    .document-upload-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 15px;
    }

    .document-upload-text {
        font-size: 1.1rem;
        color: #495057;
        font-weight: 500;
    }

    .document-upload-info {
        display: none;
        padding: 15px;
        background-color: #d4edda;
        border-radius: 6px;
        margin-top: 10px;
    }

    .document-upload-info.show {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .document-upload-filename {
        font-weight: bold;
        color: #155724;
    }

    .document-upload-size {
        font-size: 0.9rem;
        color: #155724;
        margin-left: 10px;
    }

    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #2a3a5a;
        border-color: #2a3a5a;
        transform: translateY(-2px);
    }

    .btn-secondary {
        padding: 12px 24px;
        font-weight: 600;
    }

    .progress {
        height: 8px;
    }

    .progress-bar {
        transition: width 0.3s ease;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .upload-mode-toggle {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Elements
        const fileTab = $('#file-tab');
        const folderTab = $('#folder-tab');
        const fileUploadContainer = $('#file-upload-container');
        const folderUploadContainer = $('#folder-upload-container');
        const documentInput = $('#document');
        const folderInput = $('#folder');
        const uploadForm = $('#uploadForm');
        const loadingModal = $('#loadingModal');
        const uploadProgress = $('#uploadProgress');
        const submitBtn = $('#submitBtn');
        const hiddenInputsContainer = $('#hidden-inputs-container');

        // Preview elements
        const previewContainer = $('#preview-container');
        const previewPlaceholder = $('#preview-placeholder');
        const pdfPreview = $('#pdf-preview');

        // Upload type toggle
        $('input[name="upload_type"]').change(function() {
            const uploadType = $(this).val();
            
            if (uploadType === 'file') {
                fileUploadContainer.removeClass('d-none');
                folderUploadContainer.addClass('d-none');
                fileTab.addClass('active');
                folderTab.removeClass('active');
                
                // Clear folder input
                folderInput.val('');
                $('#folder-info').removeClass('show');
                $('#folder-preview').addClass('d-none');
                hiddenInputsContainer.empty();
            } else {
                fileUploadContainer.addClass('d-none');
                folderUploadContainer.removeClass('d-none');
                folderTab.addClass('active');
                fileTab.removeClass('active');
                
                // Clear document input
                documentInput.val('');
                $('#document-info').removeClass('show');
                previewContainer.addClass('d-none');
                previewPlaceholder.removeClass('d-none');
            }
        });

        // File upload handling
        documentInput.on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#document-name').text(file.name);
                $('#document-size').text(formatFileSize(file.size));
                $('#document-info').addClass('show');
                
                // Show PDF preview
                if (file.type === 'application/pdf') {
                    showPDFPreview(file);
                }
            }
        });

        // Remove document
        $('#remove-document').on('click', function() {
            documentInput.val('');
            $('#document-info').removeClass('show');
            previewContainer.addClass('d-none');
            previewPlaceholder.removeClass('d-none');
        });

        // Folder upload handling
        folderInput.on('change', function() {
            const files = Array.from(this.files);
            if (files.length > 0) {
                const firstFile = files[0];
                const folderName = firstFile.webkitRelativePath.split('/')[0];
                
                $('#folder-name').text(folderName);
                $('#folder-count').text(`${files.length} archivos`);
                
                let totalSize = files.reduce((sum, file) => sum + file.size, 0);
                $('#folder-size').text(formatFileSize(totalSize));
                $('#folder-info').addClass('show');
                
                // Show file preview
                showFolderPreview(files);
                
                // Create hidden inputs for file paths
                createHiddenInputs(files);
            }
        });

        // Remove folder
        $('#remove-folder').on('click', function() {
            folderInput.val('');
            $('#folder-info').removeClass('show');
            $('#folder-preview').addClass('d-none');
            hiddenInputsContainer.empty();
        });

        // Form submission
        uploadForm.on('submit', function(e) {
            const uploadType = $('input[name="upload_type"]:checked').val();
            
            // Add hidden field to indicate upload type
            $('<input>').attr({
                type: 'hidden',
                name: 'is_folder',
                value: uploadType === 'folder' ? '1' : '0'
            }).appendTo(uploadForm);

            loadingModal.modal('show');
            submitBtn.prop('disabled', true);
            simulateProgress();
        });

        // Drag and drop for document
        const documentContainer = $('.document-upload-container');
        documentContainer.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        documentContainer.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });

        documentContainer.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                documentInput[0].files = files;
                documentInput.trigger('change');
            }
        });

        function showFolderPreview(files) {
            const fileList = $('#file-list');
            fileList.empty();
            
            files.slice(0, 10).forEach(file => {
                const fileItem = $('<div class="file-item"></div>');
                fileItem.html(`
                    <i class="fas fa-file mr-2"></i>
                    ${file.webkitRelativePath} 
                    <small class="text-muted">(${formatFileSize(file.size)})</small>
                `);
                fileList.append(fileItem);
            });
            
            if (files.length > 10) {
                fileList.append(`<div class="file-item text-muted">... y ${files.length - 10} archivos más</div>`);
            }
            
            $('#folder-preview').removeClass('d-none');
        }

        function createHiddenInputs(files) {
            hiddenInputsContainer.empty();
            
            files.forEach(file => {
                const input = $('<input>').attr({
                    type: 'hidden',
                    name: `file_paths[${file.name}]`,
                    value: file.webkitRelativePath
                });
                hiddenInputsContainer.append(input);
            });
        }

        function showPDFPreview(file) {
            const fileURL = URL.createObjectURL(file);
            pdfPreview.attr('src', fileURL);
            previewContainer.removeClass('d-none');
            previewPlaceholder.addClass('d-none');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function simulateProgress() {
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress > 90) {
                    clearInterval(interval);
                    progress = 90; // Stay at 90% until actual completion
                }
                uploadProgress.css('width', progress + '%');
            }, 300);
        }
    });
</script>
@stop
