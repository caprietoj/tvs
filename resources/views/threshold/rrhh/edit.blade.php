{{-- resources/views/threshold/rrhh/edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Editar Indicador - Recursos Humanos')

@section('content_header')
    <h1>Editar Configuración del Indicador - Recursos Humanos</h1>
@stop

@section('css')
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,.12);
    }

    .card-body {
        padding: 2rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: block;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        height: auto;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
        box-shadow: 0 2px 4px rgba(26, 72, 132, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5298 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .btn {
            width: 100%;
            margin-top: 1rem;
        }
    }
</style>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('umbral.rrhh.update', $threshold->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $threshold->id }}">
            <div class="form-group">
                <label for="kpi_name">Nombre del Indicador</label>
                <input type="text" name="kpi_name" id="kpi_name" class="form-control" value="{{ $threshold->kpi_name }}" required>
            </div>
            <div class="form-group">
                <label for="value">Valor del Indicador (%)</label>
                <input type="number" step="0.01" name="value" id="value" class="form-control" value="{{ $threshold->value }}" required>
            </div>
            <div class="d-flex justify-content-between">
                <a href="{{ route('umbral.rrhh.show') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@stop
