@extends('adminlte::page')

@section('title', 'Subir Documento - Contabilidad')

@section('content_header')
    <h1 class="text-primary">Subir Documento - Contabilidad</h1>
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

        <form action="{{ route('contabilidad.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
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
                                placeholder="Ej: Estado Financiero 2023">
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
                    <div class="card">
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
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <div class="d-flex">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Información de documentos</h5>
                        <p class="mb-2">Los documentos subidos serán accesibles para todo el personal autorizado del departamento de Contabilidad.</p>
                        <p class="mb-0">
                            <strong>Archivo PDF:</strong> Suba documentos individuales en formato PDF.<br>
                            <strong>Carpeta:</strong> Seleccione una carpeta completa de su computadora. Se mantendrá la estructura de archivos y subcarpetas.<br>
                            <small class="text-muted">Nota: La selección de carpetas funciona en navegadores modernos (Chrome, Edge, Firefox).</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('contabilidad.documents.index') }}" class="btn btn-secondary">
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
        color: #6c757d;
        margin-bottom: 10px;
    }

    .document-upload-text {
        color: #495057;
        font-size: 1rem;
    }

    .document-upload-info {
        display: none;
        background-color: #e9ecef;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
        align-items: center;
        justify-content: space-between;
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
        margin-bottom: 10px;
    }

    .folder-upload-text {
        color: #495057;
        font-size: 1rem;
    }

    .folder-upload-info {
        display: none;
        background-color: #e9ecef;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .folder-upload-filename {
        font-weight: 600;
        color: #17a2b8;
        margin-right: 10px;
    }

    .folder-upload-size {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .folder-summary {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .folder-upload-count {
        font-size: 0.85rem;
        color: #17a2b8;
        font-weight: 500;
    }

    .folder-files-preview {
        margin-top: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 6px;
        max-height: 200px;
        overflow-y: auto;
    }

    .file-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-top: 5px;
    }

    .file-item {
        display: flex;
        align-items: center;
        padding: 3px 6px;
        background-color: white;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .file-item i {
        margin-right: 6px;
        width: 12px;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements for file upload
        const documentInput = document.getElementById('document');
        const documentText = document.getElementById('document-text');
        const documentInfo = document.getElementById('document-info');
        const documentName = document.getElementById('document-name');
        const documentSize = document.getElementById('document-size');
        const removeDocument = document.getElementById('remove-document');
        const uploadContainer = document.querySelector('.document-upload-container');
        
        // Elements for folder upload
        const folderInput = document.getElementById('folder');
        const folderText = document.getElementById('folder-text');
        const folderInfo = document.getElementById('folder-info');
        const folderName = document.getElementById('folder-name');
        const folderCount = document.getElementById('folder-count');
        const folderSize = document.getElementById('folder-size');
        const removeFolder = document.getElementById('remove-folder');
        const folderContainer = document.querySelector('.folder-upload-container');
        const folderPreview = document.getElementById('folder-preview');
        const fileList = document.getElementById('file-list');
        const hiddenInputsContainer = document.getElementById('hidden-inputs-container');
        
        // Upload type containers
        const fileUploadContainer = document.getElementById('file-upload-container');
        const folderUploadContainer = document.getElementById('folder-upload-container');
        const fileTab = document.getElementById('file-tab');
        const folderTab = document.getElementById('folder-tab');
        
        // Other elements
        const previewContainer = document.getElementById('preview-container');
        const previewPlaceholder = document.getElementById('preview-placeholder');
        const pdfPreview = document.getElementById('pdf-preview');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingModal = $('#loadingModal');
        const uploadProgress = document.getElementById('uploadProgress');

        // Upload type toggle functionality
        fileTab.addEventListener('click', function() {
            fileUploadContainer.classList.remove('d-none');
            folderUploadContainer.classList.add('d-none');
            documentInput.required = true;
            folderInput.required = false;
            resetFileInfo();
            resetFolderInfo();
        });

        folderTab.addEventListener('click', function() {
            folderUploadContainer.classList.remove('d-none');
            fileUploadContainer.classList.add('d-none');
            folderInput.required = true;
            documentInput.required = false;
            resetFileInfo();
            resetFolderInfo();
        });

        // File upload drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function() {
                uploadContainer.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function() {
                uploadContainer.classList.remove('dragover');
            });
        });

        uploadContainer.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                documentInput.files = files;
                updateFileInfo(files[0]);
            }
        });

        // Note: Drag and drop is not supported for folder selection
        // Only click to select is available for folder upload

        // File selection functionality
        documentInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateFileInfo(this.files[0]);
            }
        });

        // Folder selection functionality
        folderInput.addEventListener('change', function() {
            console.log('Folder input changed, files count:', this.files.length);
            if (this.files.length > 0) {
                console.log('First file path:', this.files[0].webkitRelativePath);
                updateFolderInfo(this.files);
            }
        });

        // Remove file functionality
        removeDocument.addEventListener('click', function() {
            documentInput.value = '';
            resetFileInfo();
        });

        // Remove folder functionality
        removeFolder.addEventListener('click', function() {
            folderInput.value = '';
            resetFolderInfo();
        });

        // Submit form with loading modal
        uploadForm.addEventListener('submit', function() {
            const hasFile = documentInput.files.length > 0;
            const hasFolder = folderInput.files.length > 0;
            
            if (hasFile || hasFolder) {
                loadingModal.modal('show');
                simulateProgress();
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
                    confirmButtonColor: '#364E76',
                });
                resetFileInfo();
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El tamaño máximo permitido es 10MB',
                    confirmButtonColor: '#364E76',
                });
                resetFileInfo();
                return;
            }

            // Display file info
            documentText.textContent = 'Archivo seleccionado';
            documentInfo.style.display = 'flex';
            documentName.textContent = file.name;
            documentSize.textContent = formatFileSize(file.size);
            
            // Show PDF preview
            showPDFPreview(file);
        }

        function updateFolderInfo(files) {
            console.log('updateFolderInfo called with', files.length, 'files');
            
            if (files.length === 0) {
                resetFolderInfo();
                return;
            }

            // Limpiar inputs hidden previos
            hiddenInputsContainer.innerHTML = '';

            // Calcular tamaño total y obtener información de la carpeta
            let totalSize = 0;
            let folderPath = '';
            const fileInfos = [];

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                totalSize += file.size;
                
                console.log(`File ${i}:`, file.name, 'Path:', file.webkitRelativePath, 'Size:', file.size);
                
                if (i === 0) {
                    // Obtener el nombre de la carpeta raíz del primer archivo
                    const pathParts = file.webkitRelativePath.split('/');
                    folderPath = pathParts[0];
                    console.log('Root folder detected:', folderPath);
                }

                // Crear input hidden para la ruta de este archivo
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `folder_paths[${i}]`;
                hiddenInput.value = file.webkitRelativePath || file.name;
                hiddenInputsContainer.appendChild(hiddenInput);
                
                console.log('Created hidden input:', hiddenInput.name, '=', hiddenInput.value);

                // Preparar información del archivo para preview
                fileInfos.push({
                    name: file.name,
                    path: file.webkitRelativePath || file.name,
                    size: file.size,
                    type: file.type
                });
            }

            console.log('Total size:', totalSize, 'Root folder:', folderPath);

            // Validar tamaño total (100MB máximo para carpetas)
            const maxSize = 100 * 1024 * 1024; // 100MB
            if (totalSize > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Carpeta muy grande',
                    text: `El tamaño total de la carpeta (${formatFileSize(totalSize)}) excede el límite de ${formatFileSize(maxSize)}`,
                    confirmButtonColor: '#364E76',
                });
                resetFolderInfo();
                return;
            }

            // Mostrar información de la carpeta
            folderText.textContent = 'Carpeta seleccionada';
            folderInfo.style.display = 'flex';
            folderName.textContent = folderPath;
            folderCount.textContent = `${files.length} archivo(s)`;
            folderSize.textContent = formatFileSize(totalSize);
            
            // Mostrar preview de archivos
            showFilePreview(fileInfos);
            
            // Ocultar preview de PDF
            previewContainer.classList.add('d-none');
            previewPlaceholder.classList.remove('d-none');
        }

        function showFilePreview(fileInfos) {
            folderPreview.classList.remove('d-none');
            fileList.innerHTML = '';

            // Mostrar solo los primeros 10 archivos para no sobrecargar la UI
            const filesToShow = fileInfos.slice(0, 10);
            
            filesToShow.forEach(fileInfo => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                // Determinar icono basado en tipo de archivo
                const icon = getFileIcon(fileInfo.name);
                
                fileItem.innerHTML = `
                    <i class="${icon}"></i>
                    <span style="flex: 1;">${fileInfo.path}</span>
                    <small style="color: #6c757d;">${formatFileSize(fileInfo.size)}</small>
                `;
                
                fileList.appendChild(fileItem);
            });

            // Si hay más archivos, mostrar un indicador
            if (fileInfos.length > 10) {
                const moreItem = document.createElement('div');
                moreItem.className = 'file-item';
                moreItem.style.fontStyle = 'italic';
                moreItem.style.color = '#6c757d';
                moreItem.innerHTML = `<i class="fas fa-ellipsis-h"></i> y ${fileInfos.length - 10} archivo(s) más...`;
                fileList.appendChild(moreItem);
            }
        }

        function getFileIcon(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            
            switch(extension) {
                case 'pdf':
                    return 'fas fa-file-pdf text-danger';
                case 'doc':
                case 'docx':
                    return 'fas fa-file-word text-primary';
                case 'xls':
                case 'xlsx':
                    return 'fas fa-file-excel text-success';
                case 'ppt':
                case 'pptx':
                    return 'fas fa-file-powerpoint text-warning';
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    return 'fas fa-file-image text-info';
                case 'txt':
                    return 'fas fa-file-alt text-secondary';
                case 'zip':
                case 'rar':
                    return 'fas fa-file-archive text-dark';
                default:
                    return 'fas fa-file text-muted';
            }
        }

        function resetFileInfo() {
            documentText.textContent = 'Arrastre su archivo PDF aquí o haga clic para seleccionar';
            documentInfo.style.display = 'none';
            documentName.textContent = '';
            documentSize.textContent = '';
            
            // Hide preview
            previewContainer.classList.add('d-none');
            previewPlaceholder.classList.remove('d-none');
        }

        function resetFolderInfo() {
            folderText.textContent = 'Haga clic para seleccionar una carpeta';
            folderInfo.style.display = 'none';
            folderName.textContent = '';
            folderCount.textContent = '';
            folderSize.textContent = '';
            folderPreview.classList.add('d-none');
            fileList.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';
        }

        function showPDFPreview(file) {
            const fileURL = URL.createObjectURL(file);
            pdfPreview.src = fileURL;
            previewContainer.classList.remove('d-none');
            previewPlaceholder.classList.add('d-none');
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
                uploadProgress.style.width = progress + '%';
            }, 300);
        }
    });
</script>
@stop
