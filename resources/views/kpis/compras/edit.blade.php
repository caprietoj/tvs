@extends('adminlte::page')

@section('title', 'Editar KPI Compras')

@section('content_header')
    <h1 class="text-primary">Editar KPI - Compras</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

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

        <form action="{{ route('kpis.compras.update', $kpi->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type" class="form-label">
                            Tipo de KPI <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-control select2bs4" required>
                            <option value="measurement" {{ $kpi->type == 'measurement' ? 'selected' : '' }}>Medición</option>
                            <option value="informative" {{ $kpi->type == 'informative' ? 'selected' : '' }}>Informativo</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="threshold_id" class="form-label">
                            Nombre del KPI <span class="text-danger">*</span>
                        </label>
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

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="methodology" class="form-label">
                            Metodología de Medición <span class="text-danger">*</span>
                        </label>
                        <select name="methodology" id="methodology" class="form-control select2bs4 @error('methodology') is-invalid @enderror" required>
                            <option value="">Seleccione una metodología</option>
                            <option value="formulario" {{ $kpi->methodology == 'formulario' ? 'selected' : '' }}>Formulario</option>
                            <option value="encuesta" {{ $kpi->methodology == 'encuesta' ? 'selected' : '' }}>Encuesta</option>
                            <option value="archivo" {{ $kpi->methodology == 'archivo' ? 'selected' : '' }}>Archivo</option>
                            <option value="otro" {{ $kpi->methodology == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('methodology')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="analysis" class="form-label">
                            Análisis <span class="text-danger">*</span>
                        </label>
                        <textarea name="analysis" id="analysis" class="form-control @error('analysis') is-invalid @enderror" 
                                rows="3" required>{{ old('analysis', $kpi->analysis) }}</textarea>
                        @error('analysis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="methodology" class="form-label">
                    Metodología de Medición <span class="text-danger">*</span>
                </label>
                <textarea name="methodology" id="methodology" class="form-control" rows="3" required>{{ $kpi->methodology }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="frequency" class="form-label">
                            Frecuencia de Medición <span class="text-danger">*</span>
                        </label>
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
                        <label for="measurement_date" class="form-label">
                            Fecha de Medición <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="measurement_date" id="measurement_date" 
                               class="form-control" value="{{ $kpi->measurement_date }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="percentage" class="form-label">
                            Porcentaje Alcanzado (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="percentage" 
                               id="percentage" class="form-control" value="{{ $kpi->percentage }}" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="url" class="form-label">
                    URL de Referencia
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                    </div>
                    <input type="url" name="url" id="url" 
                           class="form-control @error('url') is-invalid @enderror" 
                           value="{{ $kpi->url }}" placeholder="https://ejemplo.com">
                </div>
                <small class="form-text text-muted">Ingrese una URL relacionada con este KPI (opcional)</small>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kpis.compras.index') }}" class="btn btn-secondary">
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
        --primary: #364E76;  /* Updated primary color */
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .custom-card {
        background: #ffffff;
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: block;
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

    .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: #364E76;  /* Changed from gradient to solid color */
        border-color: #364E76;
    }

    .btn-primary:hover {
        background: #2a3d5f;
        border-color: #2a3d5f;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
        border: none;
    }

    .alert {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--box-shadow);
    }

    @media (max-width: 768px) {
        .btn {
            width: 100%;
            justify-content: center;
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