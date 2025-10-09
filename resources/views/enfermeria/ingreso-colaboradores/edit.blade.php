@extends('adminlte::page')

@section('title', 'Editar Atención de Colaborador - Enfermería')

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-warning">
            <i class="fas fa-edit mr-2"></i>Editar Atención de Colaborador
        </h1>
        <div>
            <a href="{{ route('enfermeria.ingreso_colaboradores.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Mostrar errores de validación -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error en la validación</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('enfermeria.ingreso_colaboradores.update', $ingreso->id) }}" method="POST" class="enfermeria-form">
                @csrf
                @method('PUT')
                
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
                                    <label for="fecha" class="font-weight-bold">
                                        <i class="fas fa-calendar mr-1"></i>Fecha <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control form-control-lg @error('fecha') is-invalid @enderror" 
                                           id="fecha" 
                                           name="fecha" 
                                           value="{{ old('fecha', $ingreso->fecha ? \Carbon\Carbon::parse($ingreso->fecha)->format('Y-m-d') : '') }}" 
                                           required>
                                    @error('fecha')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hora" class="font-weight-bold">
                                        <i class="fas fa-clock mr-1"></i>Hora <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control form-control-lg @error('hora') is-invalid @enderror" 
                                           id="hora" 
                                           name="hora" 
                                           value="{{ old('hora', $ingreso->hora ? \Carbon\Carbon::parse($ingreso->hora)->format('H:i') : '') }}" 
                                           required>
                                    @error('hora')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Información del Colaborador -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-tie mr-2"></i>Información del Colaborador
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Campos del empleado -->
                        <div id="empleado-seleccionado">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nombre_completo" class="font-weight-bold">
                                            <i class="fas fa-user-tie mr-1"></i>Nombre Completo del Colaborador <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg @error('nombre_completo') is-invalid @enderror" 
                                               id="nombre_completo" 
                                               name="nombre_completo" 
                                               value="{{ old('nombre_completo', $ingreso->nombre_completo) }}" 
                                               placeholder="Se llenará automáticamente"
                                               readonly
                                               required>
                                        @error('nombre_completo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="documento_colaborador" class="font-weight-bold">
                                            <i class="fas fa-id-card mr-1"></i>Documento <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg @error('documento_colaborador') is-invalid @enderror" 
                                               id="documento_colaborador" 
                                               name="documento_colaborador" 
                                               value="{{ old('documento_colaborador', $ingreso->documento_colaborador) }}" 
                                               placeholder="Número de documento"
                                               readonly
                                               required>
                                        @error('documento_colaborador')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="font-weight-bold">
                                            <i class="fas fa-envelope mr-1"></i>Email
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $ingreso->email) }}" 
                                               placeholder="Correo electrónico"
                                               readonly>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="area_colaborador" class="font-weight-bold">
                                            <i class="fas fa-briefcase mr-1"></i>Área <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-lg @error('area_colaborador') is-invalid @enderror" 
                                                id="area_colaborador" 
                                                name="area_colaborador"
                                                required>
                                            <option value="">-- Seleccione un área --</option>
                                            <option value="DOCENTE" {{ old('area_colaborador', $ingreso->area_colaborador) == 'DOCENTE' ? 'selected' : '' }}>DOCENTE</option>
                                            <option value="ADMINISTRATIVO" {{ old('area_colaborador', $ingreso->area_colaborador) == 'ADMINISTRATIVO' ? 'selected' : '' }}>ADMINISTRATIVO</option>
                                            <option value="SERV. GENS. Y MTO." {{ old('area_colaborador', $ingreso->area_colaborador) == 'SERV. GENS. Y MTO.' ? 'selected' : '' }}>SERV. GENS. Y MTO.</option>
                                            <option value="TRANSPORTE" {{ old('area_colaborador', $ingreso->area_colaborador) == 'TRANSPORTE' ? 'selected' : '' }}>TRANSPORTE</option>
                                            <option value="OTRO" {{ old('area_colaborador', $ingreso->area_colaborador) == 'OTRO' ? 'selected' : '' }}>OTRO</option>
                                        </select>
                                        @error('area_colaborador')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Seleccione el área correspondiente al colaborador
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sexo_colaborador_display" class="font-weight-bold">
                                            <i class="fas fa-venus-mars mr-1"></i>Sexo
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="sexo_colaborador_display" 
                                               value="{{ old('sexo_colaborador', $ingreso->sexo_colaborador) == 'M' ? 'Masculino' : (old('sexo_colaborador', $ingreso->sexo_colaborador) == 'F' ? 'Femenino' : '') }}"
                                               placeholder="Sexo del colaborador"
                                               readonly>
                                        <input type="hidden" 
                                               id="sexo_colaborador" 
                                               name="sexo_colaborador" 
                                               value="{{ old('sexo_colaborador', $ingreso->sexo_colaborador) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_sangre_colaborador" class="font-weight-bold">
                                            <i class="fas fa-tint mr-1"></i>Tipo de Sangre
                                        </label>
                                        <select class="form-control form-control-lg @error('tipo_sangre_colaborador') is-invalid @enderror" 
                                                id="tipo_sangre_colaborador" 
                                                name="tipo_sangre_colaborador">
                                            <option value="">-- Seleccione tipo de sangre --</option>
                                            <option value="A+" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'A+' ? 'selected' : '' }}>A+</option>
                                            <option value="A-" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'A-' ? 'selected' : '' }}>A-</option>
                                            <option value="B+" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'B+' ? 'selected' : '' }}>B+</option>
                                            <option value="B-" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'B-' ? 'selected' : '' }}>B-</option>
                                            <option value="AB+" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                            <option value="AB-" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                            <option value="O+" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'O+' ? 'selected' : '' }}>O+</option>
                                            <option value="O-" {{ old('tipo_sangre_colaborador', $ingreso->tipo_sangre_colaborador) == 'O-' ? 'selected' : '' }}>O-</option>
                                        </select>
                                        @error('tipo_sangre_colaborador')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campo oculto para el ID del empleado -->
                        <input type="hidden" id="empleado_id" name="empleado_id" value="{{ old('empleado_id', $ingreso->empleado_id) }}">
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
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="motivo" class="font-weight-bold">
                                        <i class="fas fa-stethoscope mr-1"></i>Motivo <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg @error('motivo') is-invalid @enderror" 
                                            id="motivo" 
                                            name="motivo" 
                                            required>
                                        <option value="">-- Seleccionar motivo --</option>
                                        @if(isset($motivos) && count($motivos) > 0)
                                            @foreach($motivos as $valor => $nombreCompleto)
                                                <option value="{{ $valor }}" {{ old('motivo', $ingreso->motivo) == $valor ? 'selected' : '' }}>
                                                    {{ $nombreCompleto }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="Enfermedad" {{ old('motivo', $ingreso->motivo) == 'Enfermedad' ? 'selected' : '' }}>🤒 Enfermedad</option>
                                            <option value="Accidente" {{ old('motivo', $ingreso->motivo) == 'Accidente' ? 'selected' : '' }}>🚑 Accidente</option>
                                            <option value="Dolor" {{ old('motivo', $ingreso->motivo) == 'Dolor' ? 'selected' : '' }}>😣 Dolor</option>
                                            <option value="Malestar general" {{ old('motivo', $ingreso->motivo) == 'Malestar general' ? 'selected' : '' }}>😷 Malestar general</option>
                                            <option value="Emergencia" {{ old('motivo', $ingreso->motivo) == 'Emergencia' ? 'selected' : '' }}>🚨 Emergencia</option>
                                            <option value="Control rutinario" {{ old('motivo', $ingreso->motivo) == 'Control rutinario' ? 'selected' : '' }}>✅ Control rutinario</option>
                                            <option value="Otro" {{ old('motivo', $ingreso->motivo) == 'Otro' ? 'selected' : '' }}>❓ Otro</option>
                                        @endif
                                    </select>
                                    @error('motivo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    @if(isset($motivos) && count($motivos) == 0)
                                        <small class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            No hay motivos configurados. 
                                            <a href="{{ route('motivos-enfermeria.index') }}" target="_blank">
                                                Configurar motivos
                                            </a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_evento" class="font-weight-bold">
                                        <i class="fas fa-file-alt mr-1"></i>Descripción del Evento <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('descripcion_evento') is-invalid @enderror" 
                                              id="descripcion_evento" 
                                              name="descripcion_evento" 
                                              rows="5" 
                                              placeholder="Describa detalladamente lo que sucedió, síntomas presentados, circunstancias del evento..."
                                              required>{{ old('descripcion_evento', $ingreso->descripcion_evento) }}</textarea>
                                    @error('descripcion_evento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
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
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="accion_enfermeria" class="font-weight-bold">
                                        <i class="fas fa-notes-medical mr-1"></i>Acción de Enfermería <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('accion_enfermeria') is-invalid @enderror" 
                                              id="accion_enfermeria" 
                                              name="accion_enfermeria" 
                                              rows="6" 
                                              placeholder="Describa las acciones realizadas: medicamentos administrados, procedimientos aplicados, primeros auxilios, observaciones clínicas, etc..."
                                              required>{{ old('accion_enfermeria', $ingreso->accion_enfermeria) }}</textarea>
                                    @error('accion_enfermeria')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Seguimiento y Encuesta -->
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-check mr-2"></i>Seguimiento y Encuesta
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="seguimiento" class="font-weight-bold">
                                        <i class="fas fa-eye mr-1"></i>Seguimiento
                                    </label>
                                    <select class="form-control form-control-lg @error('seguimiento') is-invalid @enderror" 
                                            id="seguimiento" 
                                            name="seguimiento">
                                        <option value="" {{ old('seguimiento', $ingreso->seguimiento) == '' ? 'selected' : '' }}>Seleccione una opción</option>
                                        <option value="En Observación" {{ old('seguimiento', $ingreso->seguimiento) == 'En Observación' ? 'selected' : '' }}>En Observación</option>
                                        <option value="Alta" {{ old('seguimiento', $ingreso->seguimiento) == 'Alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="Requiere Seguimiento" {{ old('seguimiento', $ingreso->seguimiento) == 'Requiere Seguimiento' ? 'selected' : '' }}>Requiere Seguimiento</option>
                                    </select>
                                    @error('seguimiento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="encuesta" class="font-weight-bold">
                                        <i class="fas fa-poll mr-1"></i>Encuesta
                                    </label>
                                    <select class="form-control form-control-lg @error('encuesta') is-invalid @enderror" 
                                            id="encuesta" 
                                            name="encuesta"
                                            onchange="toggleEncuestaObservaciones()">
                                        <option value="">Seleccione una opción</option>
                                        <option value="Si" {{ old('encuesta', $ingreso->encuesta) == 'Si' ? 'selected' : '' }}>Sí</option>
                                        <option value="No" {{ old('encuesta', $ingreso->encuesta) == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('encuesta')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group" id="encuesta-observaciones" style="display: none;">
                                    <label for="encuesta_observaciones" class="font-weight-bold">
                                        <i class="fas fa-comment mr-1"></i>¿Por qué no se realiza la encuesta?
                                    </label>
                                    <textarea class="form-control @error('encuesta_observaciones') is-invalid @enderror" 
                                              id="encuesta_observaciones" 
                                              name="encuesta_observaciones" 
                                              rows="4" 
                                              placeholder="Explique el motivo por el cual no se puede realizar la encuesta...">{{ old('encuesta_observaciones', $ingreso->encuesta_observaciones) }}</textarea>
                                    @error('encuesta_observaciones')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('enfermeria.ingreso_estudiantes.index') }}" 
                                   class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-save mr-2"></i>Guardar Registro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Colores institucionales TVS */
        :root {
            --tvs-primary: #364E76;
            --tvs-primary-light: #4a6491;
            --tvs-primary-dark: #2c3e61;
            --tvs-secondary: #5a6c8a;
            --tvs-accent: #3498db;
            --tvs-success: #27ae60;
            --tvs-warning: #f39c12;
            --tvs-danger: #e74c3c;
            --tvs-info: #3498db;
            --tvs-light: #ecf0f1;
            --tvs-white: #ffffff;
        }

        /* Estilos generales del formulario */
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(54, 78, 118, 0.15);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
            border: 1px solid rgba(54, 78, 118, 0.1);
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(54, 78, 118, 0.25);
        }
        
        .card-body {
            padding: 25px;
            background: linear-gradient(to bottom, var(--tvs-white) 0%, rgba(54, 78, 118, 0.01) 100%);
        }
        
        .card-header {
            border-radius: 12px 12px 0 0 !important;
            color: var(--tvs-white) !important;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 18px 25px;
        }
        
        /* Espaciado mejorado para form-groups */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        /* Espaciado entre rows */
        .row + .row {
            margin-top: 15px;
        }
        
        /* Espaciado específico para columnas */
        .col-12.mb-3 {
            margin-bottom: 20px !important;
        }
        
        /* Headers específicos por sección con colores institucionales */
        .card-outline.card-primary .card-header {
            background: linear-gradient(135deg, var(--tvs-primary) 0%, var(--tvs-primary-light) 100%);
            box-shadow: 0 2px 10px rgba(54, 78, 118, 0.3);
        }
        
        .card-outline.card-info .card-header {
            background: linear-gradient(135deg, var(--tvs-accent) 0%, var(--tvs-info) 100%);
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
        }
        
        .card-outline.card-warning .card-header {
            background: linear-gradient(135deg, var(--tvs-warning) 0%, #e67e22 100%);
            color: var(--tvs-white) !important;
            box-shadow: 0 2px 10px rgba(243, 156, 18, 0.3);
        }
        
        .card-outline.card-success .card-header {
            background: linear-gradient(135deg, var(--tvs-success) 0%, #2ecc71 100%);
            box-shadow: 0 2px 10px rgba(39, 174, 96, 0.3);
        }
        
        .card-outline.card-secondary .card-header {
            background: linear-gradient(135deg, var(--tvs-secondary) 0%, var(--tvs-primary-dark) 100%);
            box-shadow: 0 2px 10px rgba(90, 108, 138, 0.3);
        }
        
        /* Estilos de los campos de formulario con colores TVS */
        .form-control {
            border-radius: 8px;
            border: 2px solid rgba(54, 78, 118, 0.2);
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
            background-color: var(--tvs-white);
        }
        
        .form-control:focus {
            border-color: var(--tvs-primary);
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
            transform: translateY(-1px);
            background-color: rgba(54, 78, 118, 0.02);
        }
        
        .form-control-lg {
            padding: 15px 20px;
            font-size: 16px;
            font-weight: 500;
            border: 2px solid rgba(54, 78, 118, 0.25);
        }
        
        .form-control-lg:focus {
            border-color: var(--tvs-primary-light);
            box-shadow: 0 0 0 0.3rem rgba(54, 78, 118, 0.15);
        }
        
        /* Estilos de las etiquetas con colores institucionales */
        .font-weight-bold {
            color: var(--tvs-primary-dark);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .font-weight-bold i {
            color: var(--tvs-primary);
            margin-right: 8px;
            font-size: 16px;
        }
        
        /* Estilos para los textareas */
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            border: 2px solid rgba(54, 78, 118, 0.2);
        }
        
        textarea.form-control:focus {
            border-color: var(--tvs-primary);
            background-color: rgba(54, 78, 118, 0.02);
        }
        
        /* Estilos para los selects con colores TVS - MEJORADOS */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23364E76' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 15px center;
            background-repeat: no-repeat;
            background-size: 20px 16px;
            padding-right: 50px;
            border: 2px solid rgba(54, 78, 118, 0.3);
            background-color: var(--tvs-white);
            color: var(--tvs-primary-dark);
            font-weight: 500;
            cursor: pointer;
            height: auto;
            min-height: 48px;
            line-height: 1.5;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        select.form-control:focus {
            border-color: var(--tvs-primary);
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
            background-color: rgba(54, 78, 118, 0.02);
            outline: none;
        }
        
        select.form-control:hover {
            border-color: var(--tvs-primary-light);
            background-color: rgba(54, 78, 118, 0.01);
            box-shadow: 0 2px 8px rgba(54, 78, 118, 0.1);
        }
        
        select.form-control-lg {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23364E76' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 18px center;
            background-repeat: no-repeat;
            background-size: 22px 18px;
            padding-right: 55px;
            min-height: 52px;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Estilos para opciones de select - MEJORADOS */
        select.form-control option {
            padding: 15px 20px;
            background-color: var(--tvs-white);
            color: var(--tvs-primary-dark);
            font-weight: 500;
            border: none;
            font-size: 15px;
            line-height: 1.4;
        }
        
        select.form-control option:hover,
        select.form-control option:focus {
            background-color: rgba(54, 78, 118, 0.1);
            color: var(--tvs-primary);
        }
        
        select.form-control option:checked,
        select.form-control option:selected {
            background-color: var(--tvs-primary);
            color: var(--tvs-white);
            font-weight: 600;
        }
        
        select.form-control option:disabled {
            color: rgba(54, 78, 118, 0.5);
            background-color: rgba(54, 78, 118, 0.05);
            cursor: not-allowed;
        }
        
        /* Estilo especial para la opción por defecto */
        select.form-control option[value=""] {
            color: rgba(54, 78, 118, 0.6);
            font-style: italic;
            font-weight: 400;
        }
        
        /* Mejoras para select inválido */
        select.form-control.is-invalid {
            border-color: var(--tvs-danger);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        select.form-control.is-invalid:focus {
            border-color: var(--tvs-danger);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        /* Estilos para los botones con colores institucionales */
        .btn {
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            font-size: 14px;
        }
        
        .btn-lg {
            padding: 12px 30px;
            font-size: 15px;
            border-radius: 8px;
        }
        
        .btn-block {
            padding: 12px 20px;
            font-size: 15px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--tvs-success) 0%, #2ecc71 100%);
            color: var(--tvs-white);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            color: var(--tvs-white);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--tvs-secondary) 0%, var(--tvs-primary) 100%);
            color: var(--tvs-white);
            box-shadow: 0 4px 15px rgba(90, 108, 138, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, var(--tvs-primary-dark) 0%, var(--tvs-secondary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(90, 108, 138, 0.4);
            color: var(--tvs-white);
        }
        
        /* Estilos para los campos requeridos */
        .text-danger {
            color: var(--tvs-danger) !important;
            font-weight: bold;
        }
        
        /* Estilos para los placeholders */
        .form-control::placeholder {
            color: rgba(54, 78, 118, 0.6);
            font-style: italic;
        }
        
        /* Estilos para mensajes de error */
        .invalid-feedback {
            font-size: 13px;
            font-weight: 500;
            color: var(--tvs-danger);
        }
        
        .form-control.is-invalid {
            border-color: var(--tvs-danger);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        /* Animaciones sutiles */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Efectos hover en las cards */
        .card-body {
            background: linear-gradient(to bottom, var(--tvs-white) 0%, rgba(54, 78, 118, 0.02) 100%);
        }
        
        /* Estilos para iconos en headers */
        .card-title i {
            margin-right: 10px;
            font-size: 18px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card {
                margin-bottom: 15px;
                border-radius: 8px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .card-header {
                padding: 15px 20px;
                border-radius: 8px 8px 0 0 !important;
            }
            
            .btn,
            .btn-lg,
            .btn-block {
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .form-control-lg {
                padding: 12px 15px;
                font-size: 15px;
                min-height: 48px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .row + .row {
                margin-top: 10px;
            }
            
            /* Select responsive mejorado */
            select.form-control {
                background-size: 18px 14px;
                background-position: right 12px center;
                padding-right: 40px;
                min-height: 45px;
                font-size: 14px;
            }
            
            select.form-control-lg {
                background-size: 20px 16px;
                background-position: right 15px center;
                padding-right: 45px;
                min-height: 48px;
                font-size: 15px;
            }
            
            select.form-control option {
                padding: 12px 15px;
                font-size: 14px;
            }
        }
        
        /* Espaciado adicional para tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .card-body {
                padding: 22px;
            }
            
            .form-group {
                margin-bottom: 22px;
            }
        }
        
        /* Mejoras adicionales para los selects */
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            transition: all 0.3s ease;
        }
        
        /* Estilo especial para select con form-control-lg */
        select.form-control-lg {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23364E76' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 18px center;
            background-repeat: no-repeat;
            background-size: 22px 18px;
            padding-right: 55px;
        }
        
        /* Animación sutil para select al hacer clic */
        select.form-control:active {
            transform: translateY(1px);
        }
        
        /* Estilo para cuando el select está abierto (algunos navegadores) */
        select.form-control:focus::-ms-expand {
            display: none;
        }
        
        /* Mejora para Firefox */
        @-moz-document url-prefix() {
            select.form-control {
                text-indent: 0.01px;
                text-overflow: '';
            }
        }
        
        /* Estilo mejorado para las opciones con emojis */
        select.form-control option {
            padding: 15px 20px;
            background-color: var(--tvs-white);
            color: var(--tvs-primary-dark);
            font-weight: 500;
            line-height: 1.6;
        }
        
        /* Hover effect para el select completo */
        select.form-control:not(:disabled):hover {
            box-shadow: 0 4px 12px rgba(54, 78, 118, 0.15);
            transform: translateY(-1px);
        }
        
        /* Mejoras adicionales para accesibilidad */
        .form-control:focus,
        .btn:focus {
            outline: 2px solid var(--tvs-primary);
            outline-offset: 2px;
        }
        
        /* Estilo para el título de la página */
        .content-header h1 {
            color: var(--tvs-primary-dark);
            font-weight: 700;
        }
        
        .content-header h1 i {
            color: var(--tvs-primary);
        }
        
        /* Separadores visuales */
        .card + .card {
            margin-top: 20px;
        }
        
        /* Estilos para contenedores principales */
        .content {
            padding: 20px 0;
        }
        
        /* Mejoras visuales para las secciones */
        .card-outline {
            border-width: 2px;
        }
        
        .card-outline.card-primary {
            border-color: var(--tvs-primary);
        }
        
        .card-outline.card-info {
            border-color: var(--tvs-info);
        }
        
        .card-outline.card-warning {
            border-color: var(--tvs-warning);
        }
        
        .card-outline.card-success {
            border-color: var(--tvs-success);
        }
        
        .card-outline.card-secondary {
            border-color: var(--tvs-secondary);
        }
        
        /* Animación para campos con focus */
        .form-control:focus {
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
        
        /* Layout específico para el formulario de enfermería */
        .enfermeria-form .card-body {
            min-height: auto;
        }
        
        .enfermeria-form .form-group label {
            margin-bottom: 10px;
            display: block;
        }
        
        .enfermeria-form textarea {
            transition: height 0.3s ease;
        }
        
        .enfermeria-form textarea:focus {
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        
        /* Estilos para el autocompletado de estudiantes */
        .estudiantes-dropdown {
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid rgba(54, 78, 118, 0.3);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(54, 78, 118, 0.2);
            margin-top: 2px;
        }
        
        .estudiante-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid rgba(54, 78, 118, 0.1);
            transition: all 0.3s ease;
        }
        
        .estudiante-item:last-child {
            border-bottom: none;
        }
        
        .estudiante-item:hover {
            background-color: rgba(54, 78, 118, 0.1);
            transform: translateX(3px);
        }
        
        .estudiante-item.selected {
            background-color: var(--tvs-primary);
            color: white;
        }
        
        .estudiante-nombre {
            font-weight: 600;
            color: var(--tvs-primary-dark);
            font-size: 14px;
        }
        
        .estudiante-item:hover .estudiante-nombre,
        .estudiante-item.selected .estudiante-nombre {
            color: inherit;
        }
        
        .estudiante-info {
            font-size: 12px;
            color: var(--tvs-secondary);
            margin-top: 2px;
        }
        
        .estudiante-item:hover .estudiante-info,
        .estudiante-item.selected .estudiante-info {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .estudiante-curso {
            display: inline-block;
            background-color: var(--tvs-accent);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .form-group {
            position: relative;
        }
        
        /* Indicador de campo rellenado automáticamente */
        .auto-filled {
            background-color: rgba(39, 174, 96, 0.1) !important;
            border-color: var(--tvs-success) !important;
        }
        
        .auto-filled:focus {
            border-color: var(--tvs-success) !important;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25) !important;
        }
        
        /* Estilos específicos para campos readonly */
        input[readonly] {
            background-color: rgba(248, 249, 250, 0.8) !important;
            cursor: not-allowed;
        }
        
        input[readonly].auto-filled {
            background-color: rgba(39, 174, 96, 0.1) !important;
            border-color: var(--tvs-success) !important;
        }
        
        /* Mensaje de ayuda */
        #mensaje-seleccionar {
            border-left: 4px solid var(--tvs-primary);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            console.log('%c=========================', 'color: blue; font-size: 20px');
            console.log('%cPÁGINA CARGADA', 'color: blue; font-size: 20px');
            console.log('%c=========================', 'color: blue; font-size: 20px');
            
            // Test inmediato del campo de búsqueda
            const testField = document.getElementById('buscar_estudiante');
            console.log('Campo buscar_estudiante existe?', testField ? 'SÍ ✅' : 'NO ❌');
            if (testField) {
                console.log('Valor actual:', testField.value);
                testField.addEventListener('input', function() {
                    console.log('%c¡INPUT DETECTADO!', 'background: yellow; color: black; font-size: 16px; padding: 5px;');
                    console.log('Valor:', this.value);
                });
            }
            
            // Verificar el estado inicial del select de encuesta
            toggleEncuestaObservaciones();
            
            // Validación del formulario
            $('form').on('submit', function(e) {
                console.log('🚀 Intentando enviar formulario...');
                let isValid = true;
                let camposInvalidos = [];
                
                // Mostrar todos los datos del formulario
                console.log('📋 DATOS DEL FORMULARIO:');
                const formData = new FormData(this);
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Validar campos requeridos
                $('[required]').each(function() {
                    // Verificar si el campo o su contenedor padre está oculto
                    if ($(this).is(':hidden') || $(this).closest('#empleado-seleccionado').is(':hidden')) {
                        console.log('⏭️ Saltando campo oculto:', $(this).attr('name') || $(this).attr('id'));
                        return true; // Skip este campo
                    }
                    
                    const valor = $(this).val();
                    const nombre = $(this).attr('name') || $(this).attr('id');
                    console.log('✅ Validando campo:', nombre, '=', valor);
                    
                    if (valor === '' || valor === null) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        camposInvalidos.push(nombre);
                        console.error('❌ Campo inválido:', nombre);
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    console.error('❌ Formulario NO válido. Campos inválidos:', camposInvalidos);
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor complete todos los campos requeridos: ' + camposInvalidos.join(', '),
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    console.log('✅ Formulario válido! Enviando...');
                }
            });
            
            // Limpiar validación cuando el usuario escribe
            $('[required]').on('input change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // ===== AUTOCOMPLETADO DE ESTUDIANTES =====
            
            console.log('🎯 Iniciando autocompletado de estudiantes...');
        
            // Configurar token CSRF para todas las peticiones AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Elementos del DOM
            const $searchInput = $('#buscar_empleado');
            const $dropdown = $('#empleados-dropdown');
            const $hiddenEmpleadoId = $('#empleado_id');
            
            console.log('='.repeat(50));
            console.log('🚀 AUTOCOMPLETADO DE EMPLEADOS INICIADO');
            console.log('='.repeat(50));
            console.log('Elementos encontrados:');
            console.log('Search input:', $searchInput.length, $searchInput.length > 0 ? '✅' : '❌');
            console.log('Dropdown:', $dropdown.length, $dropdown.length > 0 ? '✅' : '❌');
            console.log('Hidden empleado ID:', $hiddenEmpleadoId.length, $hiddenEmpleadoId.length > 0 ? '✅' : '❌');
            
            if ($searchInput.length === 0) {
                console.error('❌ ERROR CRÍTICO: Campo de búsqueda no encontrado!');
                console.error('No se puede inicializar el autocompletado');
            }
            
            // Campos que se rellenan automáticamente
            const $nombreCompleto = $('#nombre_completo');
            const $documentoColaborador = $('#documento_colaborador');
            const $emailColaborador = $('#email');
            const $areaColaborador = $('#area_colaborador');
            const $sexoColaborador = $('#sexo_colaborador');
            const $sexoColaboradorDisplay = $('#sexo_colaborador_display');
            const $tipoSangreColaborador = $('#tipo_sangre_colaborador');
            
            console.log('📋 Campos encontrados:');
            console.log('nombre_completo:', $nombreCompleto.length, '✅');
            console.log('documento_colaborador:', $documentoColaborador.length, '✅');
            console.log('email:', $emailColaborador.length, '✅');
            console.log('sexo_colaborador (hidden):', $sexoColaborador.length, '✅');
            console.log('sexo_colaborador_display:', $sexoColaboradorDisplay.length, '✅');
            console.log('tipo_sangre_colaborador:', $tipoSangreColaborador.length, '✅');
            
            let searchTimeout;
            let selectedIndex = -1;
            let empleados = [];
            
            // Función para buscar empleados
            function buscarEmpleados(query) {
                console.log('🔍 buscarEmpleados llamada con:', query);
                
                if (query.length < 2) {
                    console.log('❌ Query muy corta, cancelando búsqueda');
                    ocultarDropdown();
                    return;
                }
                
                console.log('✅ Query válida, iniciando búsqueda...');
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    console.log('⏱️ Timeout ejecutado, haciendo petición AJAX...');
                    
                    // Mostrar indicador de carga
                    $dropdown.html('<div class="empleado-item" style="text-align: center; padding: 15px;"><div class="empleado-nombre">🔄 Buscando...</div></div>').show();
                    
                    $.ajax({
                        url: '/api/empleados/buscar',
                        method: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function(data) {
                            console.log('✅ AJAX exitoso! Datos recibidos:', data);
                            console.log('Tipo de datos:', typeof data, 'Es array?', Array.isArray(data));
                            console.log('Cantidad de resultados:', data ? data.length : 0);
                            
                            if (Array.isArray(data) && data.length > 0) {
                                empleados = data;
                                mostrarDropdown(data);
                            } else if (Array.isArray(data) && data.length === 0) {
                                $dropdown.html('<div class="empleado-item" style="text-align: center; padding: 15px;"><div class="empleado-nombre">No se encontraron empleados</div></div>').show();
                            } else {
                                console.error('❌ La respuesta no es un array válido:', data);
                                mostrarError('Error en formato de respuesta');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ ERROR AJAX:');
                            console.error('Status:', xhr.status);
                            console.error('StatusText:', xhr.statusText);
                            console.error('Error:', error);
                            console.error('Response:', xhr.responseText);
                            
                            let errorMsg = 'Error de conexión';
                            if (xhr.status === 401) {
                                errorMsg = 'Error de autenticación';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Error del servidor';
                            } else if (xhr.status === 404) {
                                errorMsg = 'API no encontrada';
                            }
                            
                            mostrarError(errorMsg + ' (Status: ' + xhr.status + ')');
                        }
                    });
                }, 300);
            }
            
            // Función para mostrar errores
            function mostrarError(mensaje) {
                $dropdown.html(`
                    <div class="empleado-item" style="text-align: center; color: #dc3545;">
                        <div class="empleado-nombre">${mensaje}</div>
                    </div>
                `).show();
            }
            
            // Función para mostrar el dropdown
            function mostrarDropdown(data) {
                $dropdown.empty();
                selectedIndex = -1;
                
                if (data.length === 0) {
                    $dropdown.append(`
                        <div class="empleado-item" style="text-align: center; color: #999;">
                            <div class="empleado-nombre">No se encontraron empleados</div>
                        </div>
                    `);
                } else {
                    data.forEach(function(empleado, index) {
                        const item = `
                            <div class="empleado-item" data-index="${index}" data-id="${empleado.id}">
                                <div class="empleado-nombre">
                                    ${empleado.nombre_completo}
                                    ${empleado.email ? `<span class="empleado-email">${empleado.email}</span>` : ''}
                                </div>
                                <div class="empleado-info">
                                    ${empleado.documento ? `Doc: ${empleado.documento}` : ''}
                                    ${empleado.eps ? ` | EPS: ${empleado.eps}` : ''}
                                </div>
                            </div>
                        `;
                        $dropdown.append(item);
                    });
                }
                
                $dropdown.show();
            }
            
            // Función para ocultar el dropdown
            function ocultarDropdown() {
                $dropdown.hide();
                selectedIndex = -1;
            }
            
            // Función para seleccionar un empleado
            function seleccionarEmpleado(empleado) {
                console.log('🎯 Seleccionando empleado:', empleado);
                console.log('Datos recibidos:', {
                    id: empleado.id,
                    nombre_completo: empleado.nombre_completo,
                    documento: empleado.documento,
                    email: empleado.email,
                    area: empleado.area,
                    sexo: empleado.sexo,
                    tipo_sangre: empleado.tipo_sangre
                });
                
                // Llenar campos automáticamente
                console.log('📝 Llenando campos...');
                console.log('empleado_id:', empleado.id);
                $hiddenEmpleadoId.val(empleado.id);
                
                console.log('nombre_completo:', empleado.nombre_completo, '- Campo encontrado:', $nombreCompleto.length);
                $nombreCompleto.val(empleado.nombre_completo || '').addClass('auto-filled');
                
                console.log('documento:', empleado.documento, '- Campo encontrado:', $documentoColaborador.length);
                $documentoColaborador.val(empleado.documento || '').addClass('auto-filled');
                
                console.log('email:', empleado.email, '- Campo encontrado:', $emailColaborador.length);
                $emailColaborador.val(empleado.email || 'No registrado').addClass('auto-filled');
                console.log('Valor asignado al email:', $emailColaborador.val());
                
                // Preseleccionar el área si el empleado la tiene registrada
                console.log('area:', empleado.area, '- Campo encontrado:', $areaColaborador.length);
                if (empleado.area && empleado.area !== 'No registrado') {
                    $areaColaborador.val(empleado.area).addClass('auto-filled');
                } else {
                    $areaColaborador.val(''); // Dejar en blanco para que el usuario seleccione
                }
                
                // Llenar el campo de sexo (tanto el hidden como el display)
                const sexoValor = empleado.sexo || 'No Registra';
                console.log('Asignando sexo:', sexoValor);
                $sexoColaborador.val(empleado.sexo || '').addClass('auto-filled');
                $sexoColaboradorDisplay.val(sexoValor).addClass('auto-filled');
                
                // Llenar tipo de sangre - dejar vacío si no tiene para que el servidor lo guarde como NULL
                console.log('Asignando tipo sangre:', empleado.tipo_sangre);
                if (empleado.tipo_sangre) {
                    $tipoSangreColaborador.val(empleado.tipo_sangre).addClass('auto-filled');
                } else {
                    $tipoSangreColaborador.val('').attr('placeholder', 'No registrado').addClass('auto-filled');
                }
                
                // Mostrar la sección de empleado seleccionado y ocultar mensaje de ayuda
                $('#empleado-seleccionado').show();
                $('#mensaje-seleccionar').hide();
                
                // Actualizar el campo de búsqueda
                $searchInput.val(empleado.nombre_completo);
                
                // Ocultar dropdown
                ocultarDropdown();
                
                console.log('Empleado seleccionado:', empleado);
            }
            
            // Función para limpiar selección
            function limpiarSeleccion() {
                $hiddenEmpleadoId.val('');
                $('.auto-filled').removeClass('auto-filled').val('');
                // Ocultar la sección de empleado seleccionado y mostrar mensaje de ayuda
                $('#empleado-seleccionado').hide();
                $('#mensaje-seleccionar').show();
            }
            
            // Navegación con teclado
            function manejarNavegacion(e) {
                const items = $('.empleado-item');
                
                if (items.length === 0) return;
                
                switch(e.keyCode) {
                    case 38: // Flecha arriba
                        e.preventDefault();
                        selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
                        break;
                    case 40: // Flecha abajo
                        e.preventDefault();
                        selectedIndex = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
                        break;
                    case 13: // Enter
                        e.preventDefault();
                        if (selectedIndex >= 0) {
                            const empleadoIndex = $(items[selectedIndex]).data('index');
                            seleccionarEmpleado(empleados[empleadoIndex]);
                        }
                        return;
                    case 27: // Escape
                        ocultarDropdown();
                        return;
                }
                
                // Actualizar selección visual
                items.removeClass('selected');
                if (selectedIndex >= 0) {
                    $(items[selectedIndex]).addClass('selected');
                }
            }
            
            // Event listeners para el autocompletado
            $searchInput.on('input', function() {
                const query = $(this).val().trim();
                console.log('Input detectado:', query, 'Longitud:', query.length);
                
                // Alert temporal para debugging
                if (query.length === 2) {
                    console.log('🔍 Iniciando búsqueda para:', query);
                }
                
                if (query === '') {
                    limpiarSeleccion();
                    ocultarDropdown();
                } else {
                    buscarEmpleados(query);
                }
            });
            
            $searchInput.on('keydown', manejarNavegacion);
            
            $searchInput.on('focus', function() {
                const query = $(this).val().trim();
                if (query.length >= 2) {
                    buscarEmpleados(query);
                }
            });
            
            // Click en elementos del dropdown
            $(document).on('click', '.empleado-item', function() {
                const index = $(this).data('index');
                if (index !== undefined && empleados[index]) {
                    seleccionarEmpleado(empleados[index]);
                }
            });
            
            // Ocultar dropdown al hacer click fuera
            $(document).on('click', function(e) {
                if (!$searchInput.is(e.target) && !$dropdown.is(e.target) && $dropdown.has(e.target).length === 0) {
                    ocultarDropdown();
                }
            });
            
            // Prevenir submit del formulario con Enter en el campo de búsqueda
            $searchInput.on('keypress', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });
        }); // Cierre de $(document).ready
        
        // Función para mostrar/ocultar el textarea de observaciones de encuesta
        function toggleEncuestaObservaciones() {
            const encuestaSelect = document.getElementById('encuesta');
            const observacionesDiv = document.getElementById('encuesta-observaciones');
            
            if (encuestaSelect.value === 'No') {
                observacionesDiv.style.display = 'block';
            } else {
                observacionesDiv.style.display = 'none';
                // Limpiar el textarea cuando se oculta
                document.getElementById('encuesta_observaciones').value = '';
            }
        }
    </script>
@stop