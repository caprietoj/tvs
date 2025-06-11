@extends('adminlte::page')

@section('title', 'Crear Documento')

@section('content_header')
    <h1 class="text-primary">Crear Nuevo Documento</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        <form action="{{ route('documents.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label">Nombre del Documento</label>
                <input type="text" 
                       class="form-control custom-input @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Ingrese el nombre del documento"
                       required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Descripción</label>
                <textarea class="form-control custom-input @error('description') is-invalid @enderror" 
                          id="description" 
                          name="description" 
                          rows="4"
                          placeholder="Ingrese una descripción del documento">{{ old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
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
    }

    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .custom-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        background-color: #ffffff;
    }

    .form-label {
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .custom-input {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .custom-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .custom-input::placeholder {
        color: #adb5bd;
        font-size: 0.9rem;
    }

    .form-actions {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.6rem 1.5rem;
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
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        transform: translateY(-2px);
    }

    .invalid-feedback {
        font-size: 0.875rem;
        color: var(--accent);
        margin-top: 0.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Validación del formulario
    $('form').on('submit', function(e) {
        let isValid = true;
        
        if ($('#name').val().trim() === '') {
            isValid = false;
            $('#name').addClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor complete todos los campos requeridos'
            });
        }
    });

    // Limpiar validación al escribir
    $('.custom-input').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@stop