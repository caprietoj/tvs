@extends('adminlte::page')

@section('title', 'Detalle de Atención de Estudiante - Enfermería')

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-info">
            <i class="fas fa-eye mr-2"></i>Detalle de Atención de Estudiante
        </h1>
        <div>
            <a href="{{ route('enfermeria.ingreso_estudiantes.edit', $ingreso->id) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            <a href="{{ route('enfermeria.ingreso_estudiantes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Card 1: Información Básica -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>Información Básica
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-calendar mr-1"></i>Fecha
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->fecha ? $ingreso->fecha->format('d/m/Y') : 'No registrada' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-clock mr-1"></i>Hora
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->hora ? substr($ingreso->hora, 0, 5) : 'No registrada' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Información del Estudiante -->
            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-graduate mr-2"></i>Información del Estudiante
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-user mr-1"></i>Nombre
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->estudiante ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-user mr-1"></i>Apellidos
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->apellidos_estudiante ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-id-badge mr-1"></i>Código
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->codigo_estudiante ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-id-card mr-1"></i>Documento
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->documento_estudiante ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-graduation-cap mr-1"></i>Curso
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->curso ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-briefcase-medical mr-1"></i>EPS
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->eps_estudiante ?: 'No registrada' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-venus-mars mr-1"></i>Sexo
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    @if($ingreso->sexo_estudiante == 'M')
                                        Masculino
                                    @elseif($ingreso->sexo_estudiante == 'F')
                                        Femenino
                                    @else
                                        No registrado
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-tint mr-1"></i>Tipo de Sangre
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->tipo_sangre_estudiante ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Motivo de la Consulta -->
            <div class="card card-outline card-warning mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-medical mr-2"></i>Motivo de la Consulta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-stethoscope mr-1"></i>Motivo
                        </label>
                        <p class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $ingreso->motivo ?: 'No registrado' }}
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-file-alt mr-1"></i>Descripción del Evento
                        </label>
                        <p class="form-control-plaintext border rounded p-2 bg-light" style="min-height: 100px; white-space: pre-wrap;">{{ $ingreso->descripcion_evento ?: 'No registrada' }}</p>
                    </div>
                </div>
            </div>

            <!-- Card 4: Atención de Enfermería -->
            <div class="card card-outline card-success mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-nurse mr-2"></i>Atención de Enfermería
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-notes-medical mr-1"></i>Acción de Enfermería
                        </label>
                        <p class="form-control-plaintext border rounded p-2 bg-light" style="min-height: 100px; white-space: pre-wrap;">{{ $ingreso->accion_enfermeria ?: 'No registrada' }}</p>
                    </div>
                </div>
            </div>

            <!-- Card 5: Seguimiento y Derivación -->
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-check mr-2"></i>Seguimiento y Derivación
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-eye mr-1"></i>Seguimiento
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->seguimiento ?: 'No registrado' }}
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-poll mr-1"></i>Encuesta
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->encuesta ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-directions mr-1"></i>Derivación del Estudiante
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->derivacion_estudiante ?: 'No registrada' }}
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-file-alt mr-1"></i>Reporte de caso a dirección local de educación
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->reporte_direccion_educacion ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($ingreso->encuesta_observaciones)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-comment mr-1"></i>Observaciones de la Encuesta
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" style="min-height: 80px; white-space: pre-wrap;">{{ $ingreso->encuesta_observaciones }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card 6: Encuesta -->
            @if($ingreso->encuesta || $ingreso->encuesta_observaciones)
            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-poll mr-2"></i>Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    @if($ingreso->encuesta)
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i>Encuesta
                        </label>
                        <p class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $ingreso->encuesta }}
                        </p>
                    </div>
                    @endif
                    @if($ingreso->encuesta_observaciones)
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-comment mr-1"></i>Observaciones de la Encuesta
                        </label>
                        <p class="form-control-plaintext border rounded p-2 bg-light" style="min-height: 80px; white-space: pre-wrap;">{{ $ingreso->encuesta_observaciones }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Card 7: Información del Registro -->
            <div class="card card-outline card-dark mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Información del Registro
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-user-nurse mr-1"></i>Registrado por
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->user ? $ingreso->user->name : 'No disponible' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-clock mr-1"></i>Fecha de Registro
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->created_at ? $ingreso->created_at->format('d/m/Y H:i') : 'No disponible' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @if($ingreso->updated_at && $ingreso->updated_at != $ingreso->created_at)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-edit mr-1"></i>Última modificación
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $ingreso->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-control-plaintext {
            color: #495057;
            font-size: 1rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .card-outline.card-primary .card-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .card-outline.card-info .card-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .card-outline.card-warning .card-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }
        
        .card-outline.card-success .card-header {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        }
        
        .card-outline.card-secondary .card-header {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }
        
        .card-outline.card-dark .card-header {
            background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Vista de detalle de ingreso de estudiante cargada.');
    </script>
@stop
