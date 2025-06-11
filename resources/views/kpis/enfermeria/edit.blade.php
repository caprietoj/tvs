@extends('adminlte::page')

@section('title', 'Editar KPI - Enfermería')

@section('content_header')
    <h1>Editar KPI - Enfermería</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kpis.enfermeria.update', $kpi->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="type">Tipo de KPI</label>
                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="measurement" {{ $kpi->type == 'measurement' ? 'selected' : '' }}>Medición</option>
                    <option value="informative" {{ $kpi->type == 'informative' ? 'selected' : '' }}>Informativo</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="threshold_id">Nombre del KPI</label>
                <select name="threshold_id" id="threshold_id" class="form-control @error('threshold_id') is-invalid @enderror" required>
                    <option value="">Seleccione un KPI</option>
                    @foreach($thresholds as $threshold)
                        <option value="{{ $threshold->id }}" {{ $kpi->threshold_id == $threshold->id ? 'selected' : '' }}>
                            {{ $threshold->kpi_name }} (Umbral: {{ $threshold->value }}%)
                        </option>
                    @endforeach
                </select>
                @error('threshold_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                                  rows="3" required readonly>{{ $kpi->analysis }}</textarea>
                        @error('analysis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="frequency">Frecuencia de Medición</label>
                <select name="frequency" id="frequency" class="form-control @error('frequency') is-invalid @enderror" required>
                    <option value="Diario" {{ $kpi->frequency == 'Diario' ? 'selected' : '' }}>Diario</option>
                    <option value="Quincenal" {{ $kpi->frequency == 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
                    <option value="Mensual" {{ $kpi->frequency == 'Mensual' ? 'selected' : '' }}>Mensual</option>
                    <option value="Semestral" {{ $kpi->frequency == 'Semestral' ? 'selected' : '' }}>Semestral</option>
                </select>
                @error('frequency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="measurement_date">Fecha de Medición</label>
                <input type="date" name="measurement_date" id="measurement_date" 
                       class="form-control @error('measurement_date') is-invalid @enderror" 
                       value="{{ old('measurement_date', $kpi->measurement_date) }}" required>
                @error('measurement_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="percentage">Porcentaje Alcanzado (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="percentage" 
                       id="percentage" class="form-control @error('percentage') is-invalid @enderror" 
                       value="{{ old('percentage', $kpi->percentage) }}" required>
                @error('percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

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

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Actualizar KPI</button>
                <a href="{{ route('kpis.enfermeria.index') }}" class="btn btn-secondary">Volver</a>
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
        --primary: #364E76;
        --accent: #ED3236;
        --input-height: 50px;
        --border-radius: 8px;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        color: #2d3748;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
        display: block;
    }

    .form-control {
        height: var(--input-height) !important;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius);
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        outline: none;
    }

    select.form-control {
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .select2-container--bootstrap4 .select2-selection {
        height: var(--input-height) !important;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius);
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: calc(var(--input-height) - 2px) !important;
        padding-left: 1rem !important;
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        height: calc(var(--input-height) - 2px) !important;
        top: 0 !important;
    }

    .btn {
        height: var(--input-height);
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
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
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(54, 78, 118, 0.2);
    }

    .invalid-feedback {
        color: var(--accent);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    @media (max-width: 768px) {
        .form-control, .btn {
            font-size: 16px;
            width: 100%;
        }
        
        .btn {
            margin-bottom: 0.5rem;
        }
    }

    /* Estilos mejorados para Select2 */
    .select2-container--bootstrap4 .select2-selection {
        height: var(--input-height) !important;
        padding: 0.75rem 1rem !important;
        font-size: 1rem;
        line-height: 1.5;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        padding-right: 2.5rem !important;
        background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 1rem center/16px 12px !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.5 !important;
        color: #495057;
        height: auto;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        display: none;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .select2-container--bootstrap4 .select2-dropdown {
        border-color: var(--primary);
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .select2-container--bootstrap4 .select2-results__option {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary);
    }

    .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 0.5rem;
    }

    .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--primary);
        outline: none;
    }

    /* Ajustes responsivos para Select2 */
    @media (max-width: 768px) {
        .select2-container--bootstrap4 .select2-selection {
            font-size: 16px;
        }

        .select2-container--bootstrap4 .select2-results__option {
            font-size: 16px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-container').addClass('w-100');
    
    // Inicialización mejorada de Select2
    $('.form-control[name="type"], .form-control[name="threshold_id"], .form-control[name="frequency"]').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Seleccione una opción',
        allowClear: true,
        dropdownAutoWidth: true,
        containerCssClass: 'select2-container--full',
        dropdownCssClass: 'select2-dropdown--full'
    });

    // Validación del lado del cliente
    $('form').on('submit', function(e) {
        let percentage = parseFloat($('#percentage').val());
        if (percentage < 0 || percentage > 100) {
            e.preventDefault();
            alert('El porcentaje debe estar entre 0 y 100');
            return false;
        }
    });
});
</script>
@stop