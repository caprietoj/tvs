@extends('adminlte::page')

@section('title', 'Editar KPI - Sistemas')

@section('content_header')
    <h1>Editar KPI - Sistemas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Formulario de Edición de KPI</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kpis.sistemas.update', $kpi->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type">Tipo de KPI <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control select2bs4" required>
                            <option value="measurement" {{ $kpi->type == 'measurement' ? 'selected' : '' }}>Medición</option>
                            <option value="informative" {{ $kpi->type == 'informative' ? 'selected' : '' }}>Informativo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="threshold_id">Nombre del KPI <span class="text-danger">*</span></label>
                        <select name="threshold_id" id="threshold_id" class="form-control select2bs4" required>
                            <option value="">Seleccione un KPI</option>
                            @foreach($thresholds as $threshold)
                                <option value="{{ $threshold->id }}" {{ $kpi->threshold_id == $threshold->id ? 'selected' : '' }}>
                                    {{ $threshold->kpi_name }} (Umbral: {{ $threshold->value }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="methodology">Metodología de Medición <span class="text-danger">*</span></label>
                <textarea name="methodology" id="methodology" class="form-control" rows="3" required>{{ old('methodology', $kpi->methodology) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="frequency">Frecuencia de Medición <span class="text-danger">*</span></label>
                        <select name="frequency" id="frequency" class="form-control select2bs4" required>
                            <option value="Diario" {{ $kpi->frequency == 'Diario' ? 'selected' : '' }}>Diario</option>
                            <option value="Quincenal" {{ $kpi->frequency == 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
                            <option value="Mensual" {{ $kpi->frequency == 'Mensual' ? 'selected' : '' }}>Mensual</option>
                            <option value="Semestral" {{ $kpi->frequency == 'Semestral' ? 'selected' : '' }}>Semestral</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="measurement_date">Fecha de Medición <span class="text-danger">*</span></label>
                        <input type="date" name="measurement_date" id="measurement_date" 
                               class="form-control" value="{{ $kpi->measurement_date }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="percentage">Porcentaje Alcanzado (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="percentage" 
                               id="percentage" class="form-control" value="{{ $kpi->percentage }}" required>
                    </div>
                </div>
            </div>
            
            <!-- Campo URL que faltaba -->
            <div class="form-group">
                <label for="url">URL de Referencia</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                    </div>
                    <input type="url" name="url" id="url" 
                           class="form-control @error('url') is-invalid @enderror" 
                           value="{{ old('url', $kpi->url) }}" placeholder="https://ejemplo.com">
                </div>
                <small class="form-text text-muted">Ingrese una URL relacionada con este KPI (opcional)</small>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kpis.sistemas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Actualizar KPI
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%) !important;
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        height: auto;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    .select2-container--bootstrap4 .select2-selection {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        min-height: 45px;
        padding: 0.5rem;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(26, 72, 132, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5298 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }

    .text-danger {
        color: var(--danger) !important;
    }

    @media (max-width: 768px) {
        .btn {
            width: 100%;
            margin: 0.5rem 0;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#percentage').on('input', function() {
        let value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });
});
</script>
@stop