@extends('adminlte::page')

@section('title', 'Nueva Solicitud')

@section('content_header')
    <h1 class="text-primary">Nueva Solicitud de Documento</h1>
@stop

@section('content')
    <div class="card custom-card">
        <div class="card-body">
            <form action="{{ route('document-requests.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                </div>
                <div class="form-group">
                    <label>Documento</label>
                    <select name="document_id" class="form-control" required>
                        @foreach($documents as $document)
                            <option value="{{ $document->id }}">{{ $document->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
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

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
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

    /* Estilos mejorados para el select */
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: #fff url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E") right 1rem center/8px 10px no-repeat;
        padding: 0.75rem 1rem;
        padding-right: 2.5rem;
        font-size: 0.95rem;
        line-height: 1.2;
        height: auto;
        min-height: 45px;
        white-space: normal;
        word-wrap: break-word;
        text-overflow: clip;
    }

    select.form-control option {
        padding: 10px;
        line-height: 1.4;
        min-height: 2.5rem;
        white-space: normal;
        word-wrap: break-word;
        font-size: 0.95rem;
    }

    /* Ajustes específicos para diferentes navegadores */
    @-moz-document url-prefix() {
        select.form-control {
            height: auto !important;
            padding: 0.75rem 2rem 0.75rem 1rem;
        }
        
        select.form-control option {
            padding: 0.75rem;
        }
    }

    @media screen and (-webkit-min-device-pixel-ratio:0) {
        select.form-control {
            height: auto !important;
            padding: 0.75rem 2rem 0.75rem 1rem;
        }
        
        select.form-control option {
            padding: 0.75rem;
        }
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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

    .btn i {
        font-size: 1rem;
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
            font-size: 16px; /* Prevents zoom on mobile */
        }

        .btn {
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
        }
    }
</style>
@stop