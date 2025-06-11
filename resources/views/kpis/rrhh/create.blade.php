@extends('adminlte::page')

@section('title', 'Nuevo KPI - Recursos Humanos')

@section('content_header')
    <h1>Registrar KPI - Recursos Humanos</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Formulario de Registro de KPI</h3>
            <button type="button" class="btn btn-light" id="calculatorBtn">
                <i class="fas fa-calculator"></i> Calculadora
            </button>
        </div>
    </div>
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

        <form action="{{ route('kpis.rrhh.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type" class="form-label">
                            Tipo de KPI <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-control select2bs4" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="measurement">Medición</option>
                            <option value="informative">Informativo</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="threshold_id" class="form-label">
                            Nombre del Indicador <span class="text-danger">*</span>
                        </label>
                        <select name="threshold_id" id="threshold_id" class="form-control select2bs4" required>
                            <option value="">Seleccione un Indicador</option>
                            @foreach($thresholds as $threshold)
                                <option value="{{ $threshold->id }}">
                                    {{ $threshold->kpi_name }} (Umbral: {{ $threshold->value }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="methodology" class="form-label">
                    Metodología de Medición <span class="text-danger">*</span>
                </label>
                <textarea name="methodology" id="methodology" class="form-control" rows="3" required>{{ old('methodology') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="frequency" class="form-label">
                            Frecuencia de Medición <span class="text-danger">*</span>
                        </label>
                        <select name="frequency" id="frequency" class="form-control select2bs4" required>
                            <option value="">Seleccione una frecuencia</option>
                            <option value="Diario">Diario</option>
                            <option value="Quincenal">Quincenal</option>
                            <option value="Mensual">Mensual</option>
                            <option value="Semestral">Semestral</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="measurement_date" class="form-label">
                            Fecha de Medición <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="measurement_date" id="measurement_date" 
                               class="form-control" value="{{ old('measurement_date') }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="percentage" class="form-label">
                            Porcentaje Alcanzado (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="percentage" 
                               id="percentage" class="form-control" value="{{ old('percentage') }}" required>
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
                           value="{{ old('url') }}" placeholder="https://ejemplo.com">
                </div>
                <small class="form-text text-muted">Ingrese una URL relacionada con este KPI (opcional)</small>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kpis.rrhh.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Registrar KPI
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para datos de encuesta -->
<div class="modal fade" id="surveyDataModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title">Procesar Datos de Encuesta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6 class="font-weight-bold mb-2">¿Cómo se calcula el porcentaje de satisfacción?</h6>
                    <p class="mb-1">El sistema procesa las siguientes preguntas clave:</p>
                    <ol class="pl-3 mb-0">
                        <li>Satisfacción con la atención de RRHH</li>
                        <li>Amabilidad y profesionalismo</li>
                        <li>Solución oportuna de requerimientos</li>
                        <li>Eficiencia en tiempo de respuesta</li>
                        <li>Facilidad de trámites</li>
                    </ol>
                    <p class="mt-2 mb-0">
                        <strong>Cálculo:</strong><br>
                        - "Muy satisfecho" = 5 puntos<br>
                        - "Satisfecho" = 4 puntos<br>
                        - "Neutral" = 3 puntos<br>
                        - "Insatisfecho" = 2 puntos<br>
                        - "Muy insatisfecho" = 1 punto<br>
                        <strong>Fórmula:</strong> (Suma total de puntos / (Número de respuestas × 5)) × 100
                    </p>
                </div>
                <div class="form-group">
                    <label>Pegue los datos de la encuesta aquí</label>
                    <textarea class="form-control" id="surveyData" rows="10" 
                        placeholder="Pegue aquí los datos de la encuesta..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="calculateBtn">Calcular</button>
            </div>
        </div>
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

    .custom-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%) !important;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
    }

    .card-header .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 2rem;
    }

    .form-label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
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

    .alert {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--box-shadow);
    }

    .text-danger {
        color: var(--danger) !important;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin: 0.5rem 0;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Seleccione una opción'
    });

    $('#percentage').on('input', function() {
        let value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });

    $('#calculatorBtn').click(function() {
        $('#surveyDataModal').modal('show');
    });

    $('#calculateBtn').click(function() {
        const data = $('#surveyData').val();
        if (!data.trim()) {
            Swal.fire('Error', 'Por favor ingrese datos de la encuesta', 'error');
            return;
        }

        try {
            const rows = data.split('\n').map(row => row.split('\t'));
            let totalPoints = 0;
            let count = 0;

            // Empezar desde la segunda fila (ignorar encabezados)
            for (let i = 1; i < rows.length; i++) {
                if (rows[i].length >= 7) {
                    // Preguntas a evaluar (índices 2, 4, 5, 6, 7)
                    const ratings = [rows[i][2], rows[i][4], rows[i][5], rows[i][6], rows[i][7]];
                    
                    ratings.forEach(rating => {
                        if (rating) {
                            if (rating.includes('Muy satisfecho') || rating.includes('Excelente')) {
                                totalPoints += 5;
                            } else if (rating.includes('Satisfecho') || rating.includes('Bueno')) {
                                totalPoints += 4;
                            } else if (rating.includes('Neutral') || rating.includes('Regular')) {
                                totalPoints += 3;
                            } else if (rating.includes('Insatisfecho') || rating.includes('Malo')) {
                                totalPoints += 2;
                            } else if (rating.includes('Muy insatisfecho') || rating.includes('Muy malo')) {
                                totalPoints += 1;
                            }
                            count++;
                        }
                    });
                }
            }

            // Calcular porcentaje
            const percentage = count > 0 ? ((totalPoints / (count * 5)) * 100).toFixed(2) : 0;

            // Establecer el porcentaje en el campo
            $('#percentage').val(percentage);

            // Cerrar modal
            $('#surveyDataModal').modal('hide');

            // Mostrar resultado
            Swal.fire({
                icon: 'success',
                title: 'Cálculo completado',
                text: `Porcentaje de satisfacción: ${percentage}%`
            });

        } catch (error) {
            Swal.fire('Error', 'Error al procesar los datos', 'error');
            console.error(error);
        }
    });
});
</script>
@stop