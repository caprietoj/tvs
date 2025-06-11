@extends('adminlte::page')

@section('title', 'Editar Solicitud')

@section('content_header')
    <h1 class="text-primary">Editar Solicitud de Documento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('document-requests.update', $documentRequest->id) }}" method="POST" enctype="multipart/form-data" id="updateForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" class="form-control" value="{{ $documentRequest->user->name }}" disabled>
                </div>
                <div class="form-group">
                    <label>Documento</label>
                    <select name="document_id" class="form-control" required>
                        @foreach($documents as $document)
                            <option value="{{ $document->id }}" {{ $document->id == $documentRequest->document_id ? 'selected' : '' }}>
                                {{ $document->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" class="form-control" rows="3" required>{{ $documentRequest->description }}</textarea>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="status" class="form-control" required>
                        <option value="abierto" {{ $documentRequest->status == 'abierto' ? 'selected' : '' }}>Abierto</option>
                        <option value="en proceso" {{ $documentRequest->status == 'en proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="cerrado" {{ $documentRequest->status == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Adjuntar Certificado (PDF, JPG, DOCX)</label>
                    <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.docx">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="{{ route('document-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --warning: #ffc107;
    }

    /* Header Styles */
    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        background-color: #ffffff;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.95rem;
    }

    .form-control {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        outline: none;
    }

    .form-control:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
        color: #6c757d;
    }

    /* Select Styles */
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: #fff url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E") right 1rem center/8px 10px no-repeat;
        padding-right: 2.5rem;
        height: auto;
        min-height: 45px;
    }

    select.form-control option {
        padding: 0.75rem;
        font-size: 0.95rem;
    }

    /* Textarea Styles */
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    /* File Input Styles */
    .form-control[type="file"] {
        padding: 8px;
        height: auto;
        line-height: 1.5;
        font-size: 0.95rem;
    }

    .form-control[type="file"]::-webkit-file-upload-button {
        -webkit-appearance: none;
        background-color: var(--primary);
        color: white;
        padding: 0.5rem 1.25rem;
        border: none;
        border-radius: 4px;
        margin-right: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.5;
        height: auto;
    }

    .form-control[type="file"]::file-selector-button {
        background-color: var(--primary);
        color: white;
        padding: 0.5rem 1.25rem;
        border: none;
        border-radius: 4px;
        margin-right: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.5;
        height: auto;
    }

    .form-control[type="file"]::-webkit-file-upload-button:hover,
    .form-control[type="file"]::file-selector-button:hover {
        background-color: #2a3d5d;
        transform: translateY(-1px);
    }

    /* Firefox specific styles */
    @-moz-document url-prefix() {
        .form-control[type="file"] {
            padding: 0.5rem;
        }
    }

    /* Edge and Chrome specific styles */
    @media screen and (-webkit-min-device-pixel-ratio:0) {
        .form-control[type="file"] {
            padding: 0.5rem;
        }
    }

    /* Button Styles */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
        transform: translateY(-2px);
    }

    /* Validation Styles */
    .is-invalid {
        border-color: var(--accent) !important;
    }

    .invalid-feedback {
        color: var(--accent);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-control {
            font-size: 16px;
        }

        .btn {
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
        }

        input[type="file"].form-control {
            padding: 0.75rem;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#updateForm').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button to prevent double submission
        $('#submitBtn').prop('disabled', true);
        
        // Create FormData object to handle file upload
        let formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Solicitud actualizada correctamente',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '{{ route("document-requests.index") }}';
                });
            },
            error: function(xhr) {
                // Re-enable submit button on error
                $('#submitBtn').prop('disabled', false);
                
                let errorMessage = 'Ocurrió un error al actualizar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
});
</script>
@stop