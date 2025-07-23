@extends('adminlte::page')

@section('title', 'Subir Encuesta de Enfermería')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-upload text-primary"></i>
                Subir Encuesta de Enfermería
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.internal-client.enfermeria') }}">Encuesta Enfermería</a></li>
                <li class="breadcrumb-item active">Subir</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-excel"></i>
                        Subir Archivo de Resultados
                    </h3>
                </div>
                <form action="{{ route('surveys.internal-client.enfermeria.process-upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Información Importante</h5>
                            <ul class="mb-0">
                                <li>El archivo debe estar en formato Excel (.xlsx o .xls) o CSV</li>
                                <li>La primera fila debe contener los encabezados exactamente como se muestra abajo</li>
                                <li>Los datos existentes para el mismo período serán reemplazados</li>
                                <li>Tamaño máximo del archivo: 10MB</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="survey_file">Archivo Excel de la Encuesta</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="survey_file" name="survey_file" 
                                           accept=".xlsx,.xls,.csv" required>
                                    <label class="custom-file-label" for="survey_file">Seleccionar archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-file-excel"></i>
                                    </span>
                                </div>
                            </div>
                            @error('survey_file')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="survey_year">Año de la Encuesta</label>
                                    <select class="form-control" id="survey_year" name="survey_year" required>
                                        <option value="">Seleccionar año...</option>
                                        @for($year = 2020; $year <= 2030; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('survey_year')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="survey_month">Mes de la Encuesta</label>
                                    <select class="form-control" id="survey_month" name="survey_month" required>
                                        <option value="">Seleccionar mes...</option>
                                        @foreach([
                                            1 => 'Enero',
                                            2 => 'Febrero',
                                            3 => 'Marzo',
                                            4 => 'Abril',
                                            5 => 'Mayo',
                                            6 => 'Junio',
                                            7 => 'Julio',
                                            8 => 'Agosto',
                                            9 => 'Septiembre',
                                            10 => 'Octubre',
                                            11 => 'Noviembre',
                                            12 => 'Diciembre'
                                        ] as $num => $name)
                                            <option value="{{ $num }}" {{ $num == date('n') ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('survey_month')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Estructura del Archivo</h5>
                            <p>El archivo debe contener las siguientes columnas en este orden:</p>
                            <ol>
                                <li><strong>Marca temporal</strong> - Fecha y hora de la respuesta</li>
                                <li><strong>Seleccione su dependencia</strong> - Departamento del encuestado</li>
                                <li><strong>¿Cómo califica su experiencia con el área de enfermería?</strong></li>
                                <li><strong>¿Considera que la presentación personal es adecuada?</strong></li>
                                <li><strong>Comentarios presentación personal</strong></li>
                                <li><strong>¿Cómo califica la disponibilidad del personal?</strong></li>
                                <li><strong>Comentarios disponibilidad</strong></li>
                                <li><strong>¿Considera que actúa con profesionalismo?</strong></li>
                                <li><strong>Comentarios profesionalismo</strong></li>
                                <li><strong>¿Considera que la respuesta es efectiva?</strong></li>
                                <li><strong>Comentarios respuesta efectiva</strong></li>
                                <li><strong>¿Cómo califica la limpieza y orden?</strong></li>
                                <li><strong>Comentarios limpieza</strong></li>
                                <li><strong>¿Realiza reportes oportunos?</strong></li>
                                <li><strong>Comentarios reportes</strong></li>
                                <li><strong>¿Considera que los reportes son claros?</strong></li>
                            </ol>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Subir y Procesar
                        </button>
                        <a href="{{ route('surveys.internal-client.enfermeria') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Ejemplo de Datos
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Formato de fecha:</strong> 6/6/2025 12:12:10</p>
                    <p><strong>Dependencias típicas:</strong></p>
                    <ul>
                        <li>Docente</li>
                        <li>Administrativo</li>
                    </ul>
                    <p><strong>Respuestas típicas:</strong></p>
                    <ul>
                        <li>Excelente</li>
                        <li>Buena</li>
                        <li>Regular</li>
                        <li>Sí</li>
                        <li>No</li>
                        <li>No conozco este proceso</li>
                    </ul>
                </div>
            </div>
            
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Importante
                    </h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Los datos del mismo período se reemplazarán completamente</li>
                        <li>Verifique que el archivo esté bien formateado</li>
                        <li>Los comentarios pueden estar vacíos</li>
                        <li>Se procesarán todas las filas excepto la primera (encabezados)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border: none;
    }
    
    .alert {
        border: none;
        border-radius: 0.375rem;
    }
    
    .custom-file-label::after {
        content: "Buscar";
    }
    
    .btn {
        border-radius: 0.375rem;
    }
    
    .form-control {
        border-radius: 0.375rem;
    }
    
    .input-group-text {
        border-radius: 0 0.375rem 0.375rem 0;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Check file
        if (!$('#survey_file').val()) {
            isValid = false;
            errorMessage += 'Por favor seleccione un archivo.\n';
        }
        
        // Check year
        if (!$('#survey_year').val()) {
            isValid = false;
            errorMessage += 'Por favor seleccione un año.\n';
        }
        
        // Check month
        if (!$('#survey_month').val()) {
            isValid = false;
            errorMessage += 'Por favor seleccione un mes.\n';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
});
</script>
@stop