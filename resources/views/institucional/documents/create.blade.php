@extends('adminlte::page')

@section('title', 'Subir Documento - Institucional')

@section('content_header')
    <h1 class="text-primary">Subir Documento - Institucional</h1>
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

        <form action="{{ route('institucional.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nombre del Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature" id="name-icon"></i></span>
                            </div>
                            <input type="text" name="name" id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required 
                                placeholder="Ej: Manual de Procesos Institucionales">
                        </div>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="name-help">Ingrese un nombre descriptivo para identificar el documento.</small>
                    </div>

                    <!-- Upload Mode Toggle -->
                    <div class="form-group">
                        <label class="form-label">Tipo de Subida</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-primary active" id="file-mode-btn">
                                <input type="radio" name="upload_mode" id="file-mode" value="file" checked> 
                                <i class="fas fa-file-pdf mr-1"></i>Archivo PDF
                            </label>
                            <label class="btn btn-outline-info" id="folder-mode-btn">
                                <input type="radio" name="upload_mode" id="folder-mode" value="folder"> 
                                <i class="fas fa-folder mr-1"></i>Carpeta
                            </label>
                        </div>
                        <input type="hidden" id="is_folder" name="is_folder" value="0">
                    </div>

                    <!-- File Upload Section -->
                    <div id="file-upload-section">
                        <div class="form-group">
                            <label for="document" class="form-label">
                                Seleccionar Archivo PDF <span class="text-danger">*</span>
                            </label>
                            <div class="document-upload-container">
                                <input type="file" class="document-upload-input @error('document') is-invalid @enderror" 
                                    id="document" name="document" accept=".pdf">
                                <label for="document" class="document-upload-label">
                                    <div class="document-upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="document-upload-text">
                                        <span id="document-text">Haga clic aquí o arrastre el archivo PDF</span>
                                    </div>
                                </label>
                                <div class="document-upload-info" id="document-info">
                                    <div class="file-info">
                                        <span class="document-upload-filename" id="document-name"></span>
                                        <span class="document-upload-size" id="document-size"></span>
                                    </div>
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
                    </div>

                    <!-- Folder Upload Section -->
                    <div class="form-group d-none" id="folder-upload-section">
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
                        <p class="mb-2">Los documentos subidos serán accesibles únicamente para el administrador y el asistente general.</p>
                        <p class="mb-0">
                            <strong>Archivo PDF:</strong> Suba documentos individuales en formato PDF.<br>
                            <strong>Carpeta:</strong> Seleccione una carpeta completa de su computadora. Se mantendrá la estructura de archivos y subcarpetas.<br>
                            <small class="text-muted">Nota: La selección de carpetas funciona en navegadores modernos (Chrome, Edge, Firefox).</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('institucional.documents.index') }}" class="btn btn-secondary">
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
        --border-radius: 8px;
    }

    .custom-card {
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .btn {
        border-radius: var(--border-radius);
    }

    /* Document Upload Styles */
    .document-upload-container {
        position: relative;
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
        font-weight: 500;
    }

    .document-upload-info {
        display: none;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 4px;
        margin-top: 10px;
    }

    .file-info {
        flex-grow: 1;
        text-align: left;
    }

    .document-upload-filename {
        display: block;
        font-weight: bold;
        color: #495057;
    }

    .document-upload-size {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Folder Upload Styles */
    .folder-upload-container {
        position: relative;
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
        font-weight: 500;
    }

    .folder-upload-info {
        display: none;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background-color: #d1ecf1;
        border-radius: 4px;
        margin-top: 10px;
    }

    .folder-summary {
        flex-grow: 1;
        text-align: left;
    }

    .folder-upload-filename {
        display: block;
        font-weight: bold;
        color: #0c5460;
    }

    .folder-upload-count,
    .folder-upload-size {
        display: block;
        font-size: 0.875rem;
        color: #0c5460;
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

    .file-list-item {
        padding: 2px 0;
        font-size: 0.875rem;
        color: #495057;
    }

    /* Button Toggle Styles */
    .btn-group-toggle .btn {
        border-radius: 4px !important;
    }

    .btn-group-toggle .btn:first-child {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .btn-group-toggle .btn:last-child {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Elements
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
    const fileUploadSection = document.getElementById('file-upload-section');
    const folderUploadSection = document.getElementById('folder-upload-section');
    const fileModeBtn = document.getElementById('file-mode-btn');
    const folderModeBtn = document.getElementById('folder-mode-btn');
    
    // Other elements
    const previewContainer = document.getElementById('preview-container');
    const previewPlaceholder = document.getElementById('preview-placeholder');
    const pdfPreview = document.getElementById('pdf-preview');
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingModal = $('#loadingModal');
    const uploadProgress = document.getElementById('uploadProgress');

    // Upload mode toggle
    $('input[name="upload_mode"]').on('change', function() {
        const isFolder = $('#folder-mode').is(':checked');
        
        if (isFolder) {
            // Modo carpeta
            fileUploadSection.classList.add('d-none');
            folderUploadSection.classList.remove('d-none');
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
            folderUploadSection.classList.add('d-none');
            fileUploadSection.classList.remove('d-none');
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

    // File input handling
    if (documentInput) {
        documentInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateFileInfo(this.files[0]);
            }
        });
    }

    // Folder input handling
    if (folderInput) {
        folderInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateFolderInfo(this.files);
            }
        });
    }

    // Remove file functionality
    $('#remove-document').on('click', function() {
        documentInput.value = '';
        resetFileInfo();
    });

    // Remove folder functionality
    $('#remove-folder').on('click', function() {
        folderInput.value = '';
        resetFolderInfo();
    });

    // Submit form
    uploadForm.addEventListener('submit', function() {
        const isFolder = $('#is_folder').val() === '1';
        if ((isFolder && folderInput.files.length > 0) || (!isFolder && documentInput.files.length > 0)) {
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
                confirmButtonColor: '#364E76'
            });
            documentInput.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: 'El tamaño máximo permitido es 10MB',
                confirmButtonColor: '#364E76'
            });
            resetFileInfo();
            return;
        }

        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        
        documentText.textContent = 'Archivo seleccionado';
        documentInfo.style.display = 'flex';
        documentName.textContent = fileName;
        documentSize.textContent = fileSize;
        
        // Show PDF preview
        showPDFPreview(file);
    }

    function updateFolderInfo(files) {
        if (files.length === 0) {
            resetFolderInfo();
            return;
        }

        // Clear previous hidden inputs
        hiddenInputsContainer.innerHTML = '';

        let totalSize = 0;
        let folderPath = '';
        const fileInfos = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            totalSize += file.size;
            
            if (i === 0) {
                const pathParts = file.webkitRelativePath.split('/');
                folderPath = pathParts[0];
            }

            // Create hidden input for file path
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `folder_paths[${i}]`;
            hiddenInput.value = file.webkitRelativePath || file.name;
            hiddenInputsContainer.appendChild(hiddenInput);

            fileInfos.push({
                name: file.name,
                path: file.webkitRelativePath,
                size: file.size
            });
        }

        // Check total size
        if (totalSize > 100 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Carpeta muy grande',
                text: 'El tamaño total máximo permitido es 100MB',
                confirmButtonColor: '#364E76'
            });
            resetFolderInfo();
            return;
        }

        // Display folder info
        folderText.textContent = 'Carpeta seleccionada';
        folderInfo.style.display = 'flex';
        folderName.textContent = folderPath;
        folderCount.textContent = `${files.length} archivos`;
        folderSize.textContent = formatFileSize(totalSize);

        // Show file preview
        displayFilePreview(fileInfos);
    }

    function displayFilePreview(fileInfos) {
        fileList.innerHTML = '';
        const maxFiles = 10; // Show only first 10 files
        
        fileInfos.slice(0, maxFiles).forEach(fileInfo => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-list-item';
            fileItem.innerHTML = `
                <i class="fas fa-file-pdf text-danger mr-2"></i>
                ${fileInfo.name} 
                <small class="text-muted">(${formatFileSize(fileInfo.size)})</small>
            `;
            fileList.appendChild(fileItem);
        });

        if (fileInfos.length > maxFiles) {
            const moreItem = document.createElement('div');
            moreItem.className = 'file-list-item text-muted';
            moreItem.innerHTML = `<small>... y ${fileInfos.length - maxFiles} archivos más</small>`;
            fileList.appendChild(moreItem);
        }

        folderPreview.classList.remove('d-none');
    }

    function resetFileInfo() {
        documentText.textContent = 'Haga clic aquí o arrastre el archivo PDF';
        documentInfo.style.display = 'none';
        previewContainer.classList.add('d-none');
        previewPlaceholder.classList.remove('d-none');
    }

    function resetFolderInfo() {
        folderText.textContent = 'Haga clic para seleccionar una carpeta';
        folderInfo.style.display = 'none';
        folderPreview.classList.add('d-none');
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
