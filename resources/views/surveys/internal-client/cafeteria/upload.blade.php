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
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .alert {
        border-radius: 10px;
    }
    
    .custom-file-label::after {
        content: "Buscar";
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style>
@endsection

@section('js')
<script>
    // Actualizar nombre del archivo seleccionado
    document.getElementById('survey_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Seleccionar archivo...';
        document.querySelector('.custom-file-label').textContent = fileName;
    });
    
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('survey_file');
        const yearSelect = document.getElementById('survey_year');
        const monthSelect = document.getElementById('survey_month');
        
        if (!fileInput.value || !yearSelect.value || !monthSelect.value) {
            e.preventDefault();
            alert('Por favor, complete todos los campos obligatorios.');
            return false;
        }
        
        // Validar tamaño del archivo (10MB)
        if (fileInput.files[0] && fileInput.files[0].size > 10 * 1024 * 1024) {
            e.preventDefault();
            alert('El archivo es demasiado grande. Máximo 10MB.');
            return false;
        }
        
        // Confirmar subida
        if (!confirm('¿Está seguro de que desea subir este archivo? Los datos existentes del mismo período serán reemplazados.')) {
            e.preventDefault();
            return false;
        }
    });
    
    console.log('Formulario de subida de encuesta de enfermería cargado correctamente');
</script>
@stop
