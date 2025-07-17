@extends('adminlte::page')

@section('title', 'Subir Encuesta de Almacén')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-upload text-primary"></i>
                Subir Encuesta de Almacén
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.internal-client.warehouse') }}">Encuesta Almacén</a></li>
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
                <form action="{{ route('surveys.internal-client.warehouse.process-upload') }}" method="POST" enctype="multipart/form-data">
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

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_format" required>
                                <label class="custom-control-label" for="confirm_format">
                                    Confirmo que el archivo tiene el formato correcto y contiene los encabezados requeridos
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Subir y Procesar
                        </button>
                        <a href="{{ route('surveys.internal-client.warehouse') }}" class="btn btn-secondary">
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
                        <i class="fas fa-table"></i>
                        Estructura del Archivo
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Encabezados requeridos (en orden):</strong></p>
                    <ol class="text-sm">
                        <li>Marca temporal</li>
                        <li>Selecciones su dependencia</li>
                        <li>1. ¿Cómo califica su experiencia con el servicio de Almacén?</li>
                        <li>2. ¿Cómo califica los tiempos del área para el cumplimiento de sus requerimientos?</li>
                        <li>3. Cuando ha tenido algún requerimiento. ¿Éste ha sido resuelto de manera oportuna?</li>
                        <li>4. ¿Consideran que los materiales de uso frecuente para actividades académicas e institucionales, se encuentran disponibles cuando se requieren?</li>
                        <li>En caso de querer ampliar su respuesta, realice los comentarios en este espacio.</li>
                        <li>5. ¿Cómo califica el servicio y la atención de la persona que está a cargo de resolver su solicitud?</li>
                        <li>6. ¿Cómo califica la calidad de los materiales suministrados por parte del almacén?</li>
                        <li>En caso de querer ampliar su respuesta, realice los comentarios en este espacio.</li>
                        <li>7. ¿Considera que las opciones o propuestas entregadas en las cotizaciones van de acuerdo con su solicitud?</li>
                        <li>En caso de querer ampliar su respuesta, realice los comentarios en este espacio.</li>
                        <li>8. ¿Considera que los proveedores institucionales cumplen con los estándares de calidad, tiempos de entrega y precios de acuerdo con su solicitud?</li>
                        <li>En caso de querer ampliar su respuesta, realice los comentarios en este espacio.</li>
                        <li>9. ¿Qué aspectos del área de almacén consideras que se destacan?</li>
                        <li>10. ¿Qué oportunidades de mejora encuentra para el área de almacén?</li>
                    </ol>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Valores Esperados
                    </h3>
                </div>
                <div class="card-body">
                    <p><strong>Dependencia:</strong></p>
                    <ul class="text-sm">
                        <li>Docente</li>
                        <li>Administrativo</li>
                        <li>Directivo</li>
                    </ul>

                    <p><strong>Calificaciones:</strong></p>
                    <ul class="text-sm">
                        <li>Excelente</li>
                        <li>Bueno</li>
                        <li>Regular</li>
                        <li>Deficiente</li>
                    </ul>

                    <p><strong>Respuestas Sí/No:</strong></p>
                    <ul class="text-sm">
                        <li>Sí</li>
                        <li>No</li>
                    </ul>

                    <p class="text-muted">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            El sistema normalizará automáticamente las variaciones de estos valores.
                        </small>
                    </p>
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
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
    }
    
    .custom-file-label {
        border-radius: 5px;
    }
    
    .form-control {
        border-radius: 5px;
    }
    
    .alert {
        border-radius: 8px;
    }
    
    .text-sm {
        font-size: 0.875rem;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }
    
    .card-info .card-header {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    }
    
    .card-warning .card-header {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    
    .card-primary .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    
    ol li {
        margin-bottom: 4px;
    }
    
    .custom-control-label {
        font-weight: 500;
    }
    
    .input-group-text {
        border-radius: 0 5px 5px 0;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Actualizar label del archivo seleccionado
        $('#survey_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
        
        // Validación del formulario
        $('form').on('submit', function(e) {
            const file = $('#survey_file').val();
            const year = $('#survey_year').val();
            const month = $('#survey_month').val();
            const confirm = $('#confirm_format').is(':checked');
            
            if (!file) {
                e.preventDefault();
                alert('Por favor selecciona un archivo.');
                return false;
            }
            
            if (!year || !month) {
                e.preventDefault();
                alert('Por favor selecciona el año y mes de la encuesta.');
                return false;
            }
            
            if (!confirm) {
                e.preventDefault();
                alert('Por favor confirma que el archivo tiene el formato correcto.');
                return false;
            }
            
            // Mostrar indicador de carga
            $(this).find('button[type="submit"]').prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin"></i> Procesando...'
            );
        });
        
        // Validar tipo de archivo
        $('#survey_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                const fileType = file.type;
                const validTypes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv'
                ];
                
                if (!validTypes.includes(fileType)) {
                    alert('Por favor selecciona un archivo Excel (.xlsx, .xls) o CSV.');
                    $(this).val('');
                    $(this).next('.custom-file-label').html('Seleccionar archivo...');
                    return false;
                }
                
                // Validar tamaño (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. El tamaño máximo es 10MB.');
                    $(this).val('');
                    $(this).next('.custom-file-label').html('Seleccionar archivo...');
                    return false;
                }
            }
        });
        
        // Drag and drop
        const dropZone = $('.custom-file');
        
        dropZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('border-primary');
        });
        
        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
        });
        
        dropZone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#survey_file')[0].files = files;
                $('#survey_file').trigger('change');
            }
        });
    });
</script>
@stop
