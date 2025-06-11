@extends('adminlte::page')

@section('title', 'Solicitar Equipo')

@section('content_header')
    <h1 class="text-primary">Solicitud de Préstamo de Equipo</h1>
    <p class="text-muted">Complete todos los campos para solicitar un préstamo de equipo. <strong>Las reservas solo pueden realizarse para días de mañana en adelante, y los viernes puede reservar para toda la próxima semana.</strong></p>
@stop

@section('content')
<div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-info"></i> Información importante</h5>
    <p>Los días viernes puede reservar equipos para toda la semana siguiente. Los demás días solo hasta el domingo de la semana actual.</p>
</div>
<div class="row">
    <div class="col-md-7">
        <div class="card custom-card">
            <div class="card-header institutional-bg text-white">
                <h3 class="card-title"><i class="fas fa-edit"></i> Formulario de Solicitud</h3>
            </div>
            <div class="card-body">
                <form id="loanRequestForm" action="{{ route('equipment.request.submit') }}" method="POST">
                    @csrf

                    <!-- Agregar mensaje de error si existe -->
                    @if(session('error'))
                        <div class="alert alert-danger mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Mostrar errores de validación -->
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Sección</label>
                                <select name="section" class="form-control @error('section') is-invalid @enderror" required id="section-select">
                                    <option value="">Seleccione una sección</option>
                                    <option value="bachillerato" {{ old('section') == 'bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                    <option value="preescolar_primaria" {{ old('section') == 'preescolar_primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                    <option value="administrativo" {{ old('section') == 'administrativo' ? 'selected' : '' }}>Administrativo</option>
                                </select>
                                @error('section')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-laptop"></i> Tipo de Equipo</label>
                                <select name="equipment_id" class="form-control @error('equipment_id') is-invalid @enderror" required id="equipment-select" disabled>
                                    <option value="">Primero seleccione una sección</option>
                                </select>
                                @error('equipment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-door-open"></i> Salón</label>
                                <input type="text" name="grade" class="form-control @error('grade') is-invalid @enderror" required placeholder="Ej: Aula 101, Laboratorio 3" value="{{ old('grade') }}">
                                @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calculator"></i> Cantidad de Equipos</label>
                                <div class="input-group">
                                    <input type="number" name="units_requested" class="form-control @error('units_requested') is-invalid @enderror" required min="1" id="units-input" value="{{ old('units_requested') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><span id="units-available-text">0</span> disponibles</span>
                                    </div>
                                </div>
                                @error('units_requested')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted" id="units-help-text">Seleccione un equipo para ver disponibilidad</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Fecha del Préstamo</label>
                                <input type="date" name="loan_date" class="form-control @error('loan_date') is-invalid @enderror" required 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                       max="{{ date('Y-m-d', date('w') == 5 ? strtotime('+9 day') : (date('w') == 0 ? strtotime('+7 day') : strtotime('next sunday'))) }}"
                                       value="{{ old('loan_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                       id="loan-date-input">
                                @error('loan_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Los viernes puede reservar para toda la próxima semana, otros días solo hasta el domingo actual</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Hora de Inicio</label>
                                <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" required id="start-time-input" value="{{ old('start_time') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-hourglass-end"></i> Hora de Finalización</label>
                                <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" required id="end-time-input" value="{{ old('end_time') }}">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selector de períodos de clase -->
                    <div class="row mt-3" id="class-periods-container" style="display: none;">
                        <div class="col-md-12">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-header institutional-bg text-white py-2 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-clock"></i> Seleccione horario de clase</h5>
                                    <span class="badge badge-light text-primary" id="selected-period-badge" style="display: none;">
                                        <i class="fas fa-check-circle"></i> Horario seleccionado
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-primary border-left border-primary mb-4" style="border-left-width: 4px !important;">
                                        <div class="d-flex">
                                            <div class="mr-3">
                                                <i class="fas fa-info-circle fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold mb-1">Selección de horarios</h6>
                                                <p class="mb-0 small">Al elegir un período de clase, el horario se establecerá automáticamente y el equipo se devolverá al finalizar la clase.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label class="btn btn-outline-primary active" id="period-type-single-label">
                                                <input type="radio" class="custom-control-input" id="period-type-single" name="period-type" value="single" checked>
                                                <i class="fas fa-calendar-day"></i> <span>Un período</span>
                                            </label>
                                            <label class="btn btn-outline-primary" id="period-type-block-label">
                                                <input type="radio" class="custom-control-input" id="period-type-block" name="period-type" value="block">
                                                <i class="fas fa-calendar-week"></i> <span>Bloque de períodos</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Selección de un solo período -->
                                    <div id="single-period-selector" class="mb-3">
                                        <div class="period-grid-container">
                                            <div id="periods-list" class="period-grid mb-2">
            <div class="alert alert-info mb-3">
                <i class="fas fa-calendar-day"></i> <strong>Tuesday</strong> 
            </div>
        
                <div class="period-item" data-period-id="period_0" data-start="8:00" data-end="8:45">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 8:00 - 8:45
                        </div>
                        <div>
                            <span class="period-label">Clase 1</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_1" data-start="8:45" data-end="9:30">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 8:45 - 9:30
                        </div>
                        <div>
                            <span class="period-label">Clase 2</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_2" data-start="10:00" data-end="10:45">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 10:00 - 10:45
                        </div>
                        <div>
                            <span class="period-label">Clase 3</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_3" data-start="10:45" data-end="11:30">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 10:45 - 11:30
                        </div>
                        <div>
                            <span class="period-label">Clase 4</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_4" data-start="11:30" data-end="12:15">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 11:30 - 12:15
                        </div>
                        <div>
                            <span class="period-label">Clase 5</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_5" data-start="13:00" data-end="13:45">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 13:00 - 13:45
                        </div>
                        <div>
                            <span class="period-label">Clase 6</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item" data-period-id="period_6" data-start="13:45" data-end="14:30">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> 13:45 - 14:30
                        </div>
                        <div>
                            <span class="period-label">Clase 7</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            
                <div class="period-item break" data-period-id="break_0" data-start="9:30" data-end="10:00">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-coffee mr-1"></i> 9:30 - 10:00
                        </div>
                        <div>
                            <span class="period-label">SNACK PRIMARIA</span>
                        </div>
                    </div>
                </div>
            
                <div class="period-item break" data-period-id="break_1" data-start="12:15" data-end="13:00">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-coffee mr-1"></i> 12:15 - 13:00
                        </div>
                        <div>
                            <span class="period-label">LUNCH PRIMARIA</span>
                        </div>
                    </div>
                </div>
            </div>
                                        </div>
                                    </div>

                                    <!-- Selección de bloque de períodos -->
                                    <div id="block-period-selector" class="mb-3" style="display: none;">
                                        <div class="card border border-light bg-white">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="period-start-select" class="font-weight-bold">
                                                                <i class="fas fa-hourglass-start text-primary"></i> Desde:
                                                            </label>
                                                            <select id="period-start-select" class="form-control">
                                                                <option value="">Seleccione período inicial</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="period-end-select" class="font-weight-bold">
                                                                <i class="fas fa-hourglass-end text-primary"></i> Hasta:
                                                            </label>
                                                            <select id="period-end-select" class="form-control" disabled>
                                                                <option value="">Seleccione período final</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="block-period-summary" class="alert alert-success mt-3" style="display: none;">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <span id="block-period-text">Bloque seleccionado: Período 1 a Período 3 (8:00 - 11:30)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Mensaje de no hay períodos -->
                                    <div id="no-periods-message" style="display: none;">
                                        <div class="alert alert-warning mb-0 d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                            <div>
                                                <h6 class="font-weight-bold mb-1">No hay horarios disponibles</h6>
                                                <p class="mb-0">No se encontraron horarios de clase para la sección y fecha seleccionadas.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="period_id" id="period-id-input">

                                    <!-- Opción horario manual -->
                                    <div class="custom-control custom-switch mt-4 d-flex justify-content-end">
                                        <input type="checkbox" class="custom-control-input" id="manual-hours-checkbox">
                                        <label class="custom-control-label" for="manual-hours-checkbox">
                                            Prefiero ingresar el horario manualmente
                                        </label>
                                    </div>

                                    <!-- Vista horario manual -->
                                    <div id="manual-hours-container" class="card border-dashed mt-3" style="display: none;">
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-pen"></i> Modo de horario manual activado. Puede establecer directamente las horas.
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="manual-start-time" class="font-weight-bold">
                                                            <i class="fas fa-hourglass-start"></i> Hora de inicio:
                                                        </label>
                                                        <input type="time" id="manual-start-time" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="manual-end-time" class="font-weight-bold">
                                                            <i class="fas fa-hourglass-end"></i> Hora de finalización:
                                                        </label>
                                                        <input type="time" id="manual-end-time" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm float-right" id="apply-manual-hours-btn">
                                                <i class="fas fa-check"></i> Aplicar horario
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-3 d-none" id="time-slot-warning">
                        <div class="d-flex">
                            <div class="mr-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold mb-1">Horario con reservas existentes</h6>
                                <p class="mb-0">El horario seleccionado coincide con horarios ya reservados. La disponibilidad de equipos puede variar según la hora.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-3" id="time-availability-tip">
                        <div class="d-flex">
                            <div class="mr-3">
                                <i class="fas fa-lightbulb fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold mb-1">Consejo de disponibilidad</h6>
                                <p class="mb-0">Aunque algunos equipos estén reservados a ciertas horas, podría haber disponibilidad en otros horarios del mismo día. Seleccione un horario específico para verificar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Una vez enviada la solicitud, recibirá un correo electrónico con la confirmación.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancelar</button>
                        <button type="button" id="preview-button" class="btn btn-info"><i class="fas fa-eye"></i> Vista Previa</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Solicitar Préstamo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card custom-card">
            <div class="card-header institutional-bg text-white">
                <h3 class="card-title"><i class="fas fa-calendar-check"></i> Disponibilidad de Equipos</h3>
            </div>
            <div class="card-body">
                <div id="help-info" class="alert alert-info mb-3">
                    <i class="fas fa-lightbulb"></i> Seleccione una sección, tipo de equipo y fecha para ver la disponibilidad
                </div>
                <div id="availability-info" class="d-none"></div>
                
                <div id="timeline-container" class="mt-4 d-none">
                    <h5 class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clock"></i> Disponibilidad por Horas</span>
                        <span class="badge badge-success small">Encuentre horarios disponibles</span>
                    </h5>
                    <div class="alert alert-light border-left border-info small mb-2 py-2" style="border-left-width: 4px !important;">
                        Puede reservar equipos en cualquier horario disponible, incluso si ya hay otras reservas en el día.
                    </div>
                    <div class="timeline-legend mb-2 d-flex align-items-center">
                        <div class="mr-3"><span class="timeline-legend-item bg-danger"></span> Horas reservadas</div>
                        <div class="mr-3"><span class="timeline-legend-item bg-success"></span> Su selección</div>
                        <div><span class="timeline-legend-item bg-warning"></span> Conflicto de horario</div>
                    </div>
                    <div id="timeline-chart" class="timeline-chart">
                        <div class="timeline-hours">
                            <div>7:00</div>
                            <div>8:00</div>
                            <div>9:00</div>
                            <div>10:00</div>
                            <div>11:00</div>
                            <div>12:00</div>
                            <div>13:00</div>
                            <div>14:00</div>
                            <div>15:00</div>
                            <div>16:00</div>
                            <div>17:00</div>
                        </div>
                        <div class="timeline-grid">
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                            <div class="timeline-unit"></div>
                        </div>
                        <div id="timeline-slots" class="timeline-slots">
                            <!-- Aquí se insertarán los slots ocupados -->
                        </div>
                        <div id="timeline-selection" class="timeline-selection d-none">
                            <!-- Aquí se mostrará la selección actual -->
                        </div>
                    </div>
                </div>
                
                <div id="units-summary" class="mt-4 d-none">
                    <h5><i class="fas fa-calculator"></i> Resumen de Unidades</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total de unidades:</span>
                        <span id="total-units-value" class="badge bg-primary">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Unidades ocupadas en la fecha:</span>
                        <span id="occupied-units-value" class="badge bg-warning">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Unidades disponibles:</span>
                        <span id="available-units-value" class="badge bg-success">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card mt-3">
            <div class="card-header institutional-bg text-white">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
            </div>
            <div class="card-body">
                <div class="accordion" id="infoAccordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-primary" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="fas fa-clock"></i> Horarios Disponibles
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#infoAccordion">
                            <div class="card-body">
                                <ul class="schedule-info">
                                    <li><i class="fas fa-sun text-warning"></i> Mañana: 7:00 - 12:00</li>
                                    <li><i class="fas fa-cloud-sun text-info"></i> Tarde: 12:00 - 16:00</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed text-primary" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="fas fa-question-circle"></i> Instrucciones
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#infoAccordion">
                            <div class="card-body">
                                <ol>
                                    <li>Seleccione una sección y el tipo de equipo que necesita</li>
                                    <li>Elija la fecha del préstamo (debe ser para mañana o después)</li>
                                    <li>Verifique la disponibilidad en el calendario</li>
                                    <li>Seleccione un horario disponible y la cantidad de equipos</li>
                                    <li>Complete la información del salón donde usará el equipo</li>
                                    <li>Envíe su solicitud</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header institutional-bg text-white">
                <h5 class="modal-title" id="previewModalLabel">Vista Previa de la Solicitud</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">Sección</th>
                                <td id="preview-section"></td>
                            </tr>
                            <tr>
                                <th>Equipo</th>
                                <td id="preview-equipment"></td>
                            </tr>
                            <tr>
                                <th>Salón</th>
                                <td id="preview-grade"></td>
                            </tr>
                            <tr>
                                <th>Cantidad</th>
                                <td id="preview-units"></td>
                            </tr>
                            <tr>
                                <th>Fecha</th>
                                <td id="preview-date"></td>
                            </tr>
                            <tr>
                                <th>Horario</th>
                                <td id="preview-time"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="confirm-submit-btn">Confirmar Solicitud</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const elements = {
        form: document.getElementById('loanRequestForm'),
        sectionSelect: document.getElementById('section-select'),
        equipmentSelect: document.getElementById('equipment-select'),
        unitsInput: document.getElementById('units-input'),
        availabilityInfo: document.getElementById('availability-info'),
        loanDateInput: document.getElementById('loan-date-input'),
        startTimeInput: document.getElementById('start-time-input'),
        endTimeInput: document.getElementById('end-time-input'),
        timelineContainer: document.getElementById('timeline-container'),
        timelineSlots: document.getElementById('timeline-slots'),
        timelineSelection: document.getElementById('timeline-selection'),
        unitsAvailableText: document.getElementById('units-available-text'),
        unitsHelpText: document.getElementById('units-help-text'),
        unitsSummary: document.getElementById('units-summary'),
        totalUnitsValue: document.getElementById('total-units-value'),
        occupiedUnitsValue: document.getElementById('occupied-units-value'),
        availableUnitsValue: document.getElementById('available-units-value'),
        previewButton: document.getElementById('preview-button'),
        timeSlotWarning: document.getElementById('time-slot-warning')
    };

    // Verify all required elements exist
    const missingElements = Object.entries(elements)
        .filter(([key, element]) => !element)
        .map(([key]) => key);

    if (missingElements.length > 0) {
        console.error('Missing elements:', missingElements.join(', '));
        return;
    }

    // Initialize equipment request module
    if (window.initializeEquipmentRequest) {
        window.initializeEquipmentRequest(elements);
    } else {
        console.error('Equipment request module not loaded');
    }

    // Manejador del cambio de sección
    document.getElementById('section-select').addEventListener('change', function() {
        const selectedSection = this.value;
        const equipmentSelect = document.getElementById('equipment-select');
        
        if (!selectedSection) {
            equipmentSelect.disabled = true;
            equipmentSelect.innerHTML = '<option value="">Primero seleccione una sección</option>';
            return;
        }

        // Realizar petición AJAX
        fetch(`/equipment/types/${selectedSection}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(equipment => {
                equipmentSelect.innerHTML = '<option value="">Seleccione un equipo</option>';
                
                equipment.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    
                    // Personalizar el texto según la disponibilidad
                    if (item.show_availability) {
                        option.textContent = `${item.type === 'laptop' ? 'Portátil' : 'iPad'} (${item.available_units} disponibles)`;
                        option.dataset.available = item.available_units;
                    } else {
                        option.textContent = item.type === 'laptop' ? 'Portátil' : 'iPad';
                        option.dataset.available = 0;
                    }
                    
                    option.dataset.totalUnits = item.total_units;
                    option.dataset.type = item.type;
                    equipmentSelect.appendChild(option);
                });
                
                equipmentSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los equipos disponibles'
                });
            });
    });
    
    // Manejo de la vista previa
    document.getElementById('preview-button').addEventListener('click', function() {
        // Verificar si todos los campos están completos
        if (!validateForm()) {
            return;
        }
        
        // Llenar los datos de la vista previa
        document.getElementById('preview-section').textContent = elements.sectionSelect.options[elements.sectionSelect.selectedIndex].text;
        document.getElementById('preview-equipment').textContent = elements.equipmentSelect.options[elements.equipmentSelect.selectedIndex].text;
        document.getElementById('preview-grade').textContent = document.querySelector('input[name="grade"]').value;
        document.getElementById('preview-units').textContent = elements.unitsInput.value;
        
        // Solución para garantizar la visualización correcta de la fecha seleccionada
        const loanDateValue = elements.loanDateInput.value; // YYYY-MM-DD
        const [year, month, day] = loanDateValue.split('-');
        
        // Crear fecha explícitamente usando los componentes para evitar problemas de zona horaria
        const dateObj = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        
        const formattedDate = dateObj.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        document.getElementById('preview-date').textContent = formattedDate;
        
        document.getElementById('preview-time').textContent = `${elements.startTimeInput.value} a ${elements.endTimeInput.value}`;
        
        // Mostrar el modal
        $('#previewModal').modal('show');
    });
    
    // Botón de confirmación en la vista previa
    document.getElementById('confirm-submit-btn').addEventListener('click', function() {
        $('#previewModal').modal('hide');
        elements.form.submit();
    });
    
    function validateForm() {
        const requiredFields = [
            { element: elements.sectionSelect, message: "Seleccione una sección" },
            { element: elements.equipmentSelect, message: "Seleccione un equipo" },
            { element: document.querySelector('input[name="grade"]'), message: "Ingrese el salón" },
            { element: elements.unitsInput, message: "Ingrese la cantidad de equipos" },
            { element: elements.startTimeInput, message: "Seleccione la hora de inicio" },
            { element: elements.endTimeInput, message: "Seleccione la hora de finalización" }
        ];
        
        for (const field of requiredFields) {
            if (!field.element.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: field.message
                });
                return false;
            }
        }
        
        // Validación de unidades disponibles
        const unitsAvailable = parseInt(elements.unitsAvailableText.textContent);
        const unitsRequested = parseInt(elements.unitsInput.value);
        
        console.log('Validando disponibilidad:', {
            unitsRequested: unitsRequested,
            unitsAvailable: unitsAvailable,
        });
        
        if (unitsRequested > unitsAvailable) {
            // Mostrar opciones más contextuales
            Swal.fire({
                icon: 'error',
                title: 'Unidades insuficientes',
                html: `<div class="text-center">
                    <p>Solo hay <strong>${unitsAvailable} unidades</strong> disponibles para el horario seleccionado.</p>
                    <p class="mt-3">Opciones:</p>
                    <ul class="text-left list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> Seleccionar un horario diferente</li>
                        <li><i class="fas fa-check-circle text-success"></i> Solicitar menos unidades</li>
                        <li><i class="fas fa-check-circle text-success"></i> Elegir otra fecha de préstamo</li>
                    </ul>
                </div>`,
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        
        // Validar que la hora de fin sea después de la hora de inicio
        if (elements.startTimeInput.value >= elements.endTimeInput.value) {
            Swal.fire({
                icon: 'error',
                title: 'Error de horario',
                text: 'La hora de finalización debe ser posterior a la hora de inicio'
            });
            return false;
        }
        
        // Si hay un aviso de horario, confirmarlo antes de continuar
        if (!elements.timeSlotWarning.classList.contains('d-none')) {
            const warningConfirm = confirm('Hay horarios ocupados que coinciden con su selección. ¿Está seguro de que desea continuar? La solicitud dependerá de la disponibilidad en ese horario específico.');
            if (!warningConfirm) {
                return false;
            }
        }
        
        return true;
    }
    
    // Actualizar cantidad máxima cuando cambia el equipo
    elements.equipmentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.available) {
            const availableUnits = parseInt(selectedOption.dataset.available);
            elements.unitsAvailableText.textContent = availableUnits;
            elements.unitsInput.max = availableUnits;
            elements.unitsInput.placeholder = `Máximo ${availableUnits}`;
            
            console.log('Equipo seleccionado:', {
                id: selectedOption.value,
                availableUnits: availableUnits,
                totalUnits: selectedOption.dataset.total || 'No disponible'
            });
            
            if (availableUnits > 0) {
                elements.unitsHelpText.textContent = `Puede solicitar hasta ${availableUnits} unidades`;
                elements.unitsHelpText.classList.remove('text-danger');
                elements.unitsHelpText.classList.add('text-muted');
            } else {
                elements.unitsHelpText.textContent = "Busque otro horario para encontrar disponibilidad";
                elements.unitsHelpText.classList.remove('text-muted');
                elements.unitsHelpText.classList.add('text-info');
            }
            
            // Verificar disponibilidad específica para el horario seleccionado
            if (elements.loanDateInput.value) {
                checkAvailability();
            }
        } else {
            elements.unitsAvailableText.textContent = "0";
            elements.unitsHelpText.textContent = "Seleccione un equipo para ver disponibilidad";
        }
    });
});
</script>
<script src="{{ asset('js/equipment-request.js') }}"></script>
<!-- Script de depuración para verificar disponibilidad -->
<script src="{{ asset('js/debug-equipment-availability.js') }}"></script>
<!-- Script de correcciones para disponibilidad -->
<script src="{{ asset('js/equipment-request-fixes.js') }}"></script></script>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --timeline-background: #f8f9fa;
        --timeline-hour-height: 20px;
    }

    .text-primary { color: var(--primary) !important; }

    .custom-card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }

    .form-group label {
        color: #495057;
        font-weight: 500;
    }

    .select2-container--default .select2-selection--single {
        border-radius: 4px;
        border: 1px solid #ddd;
        height: calc(2.25rem + 2px);
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: var(--primary);
    }

    .steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }

    .step {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        position: relative;
        font-weight: 500;
        color: #6c757d;
        border-radius: 4px;
        margin: 0 0.5rem;
    }

    .step.active {
        background: var(--primary);
        color: white;
    }

    .schedule-info {
        list-style: none;
        padding: 0;
    }

    .schedule-info li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }

    .schedule-info li:last-child {
        border-bottom: none;
    }

    .schedule-info i {
        margin-right: 0.5rem;
        width: 20px;
    }

    #help-info {
        font-size: 0.9rem;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .form-group label i {
        color: var(--primary);
    }

    .form-control:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .institutional-bg {
        background-color: #364E76 !important;
    }

    .card-header {
        padding: 1rem 1.25rem;
    }

    .card-header .card-title {
        margin: 0;
        font-size: 1.1rem;
    }

    /* Estilos mejorados para la sección de disponibilidad */
    .availability-section .card-body {
        padding: 1.25rem;
    }

    .availability-section .alert {
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .availability-section .list-group-item {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border: 1px solid rgba(0,0,0,.125);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
    }

    .availability-section .list-group-item i {
        margin-right: 0.5rem;
        color: #ffc107;
    }

    .availability-section .time-range {
        flex-grow: 1;
        margin: 0 0.5rem;
    }

    .availability-section .units-badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .availability-section .status-available {
        color: #28a745;
        background-color: #d4edda;
        padding: 0.5rem;
        border-radius: 4px;
        margin-top: 0.5rem;
    }

    /* Ajustes para los horarios */
    .schedule-info li {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
    }
    
    /* Estilos para el timeline */
    .timeline-chart {
        position: relative;
        margin-top: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px;
        background-color: var(--timeline-background);
    }
    
    .timeline-hours {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        padding: 0 10px;
    }
    
    .timeline-hours div {
        font-size: 11px;
        color: #666;
        flex: 1;
        text-align: center;
    }
    
    .timeline-grid {
        display: flex;
        height: 10px;
        margin-bottom: 10px;
    }
    
    .timeline-unit {
        flex: 1;
        border-right: 1px solid #ddd;
    }
    
    .timeline-unit:last-child {
        border-right: none;
    }
    
    .timeline-slots {
        position: relative;
        height: 60px;
        margin-bottom: 10px;
    }
    
    .timeline-slot {
        position: absolute;
        height: 30px;
        background-color: rgba(220, 53, 69, 0.7);
        border-radius: 4px;
        color: white;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .timeline-slot:hover {
        background-color: rgba(220, 53, 69, 0.9);
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .timeline-selection {
        position: absolute;
        height: 30px;
        background-color: rgba(25, 135, 84, 0.5);
        border: 2px solid #198754;
        border-radius: 4px;
        color: white;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        top: 40px;
    }
    
    /* Estilos para la leyenda del timeline */
    .timeline-legend {
        font-size: 0.85rem;
        color: #666;
    }
    
    .timeline-legend-item {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 5px;
    }
    
    .timeline-legend-item.bg-danger {
        background-color: rgba(220, 53, 69, 0.7);
    }
    
    .timeline-legend-item.bg-success {
        background-color: rgba(25, 135, 84, 0.5);
    }
    
    .timeline-legend-item.bg-warning {
        background-color: rgba(255, 193, 7, 0.5);
    }
    
    /* Estilos mejorados para los slots del timeline */
    .timeline-slot {
        border: 1px solid rgba(220, 53, 69, 0.9);
        transition: all 0.3s;
    }
    
    .timeline-slot:hover {
        transform: translateY(-3px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        z-index: 10;
    }
    
    .timeline-slot.conflict {
        border: 2px solid #ffc107;
        z-index: 5;
    }
    
    .modal-content {
        border-radius: 8px;
        border: none;
    }
    
    .modal-header {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    .modal-body .table {
        margin-bottom: 0;
    }
    
    .modal-body .table th {
        background-color: #f8f9fa;
    }

    /* Nuevos estilos para la sección de selección de horarios */
    .period-grid-container {
        max-height: 350px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: white;
        padding: 10px;
    }

    .period-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        padding: 5px;
    }

    .period-item {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        background-color: white;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }

    .period-item:hover {
        border-color: var(--primary);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .period-item.active {
        border-color: var(--primary);
        background-color: rgba(54, 78, 118, 0.1);
        box-shadow: 0 2px 6px rgba(54, 78, 118, 0.25);
    }

    .period-item .period-time {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--primary);
    }

    .period-item .period-label {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        background-color: var(--primary);
        color: white;
    }

    .period-item .period-status {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #28a745;
        border: 2px solid white;
        display: none;
    }

    .period-item.active .period-status {
        display: block;
    }

    .period-item.break {
        background-color: #fff9e6;
        border-color: #ffc107;
        opacity: 0.7;
        cursor: not-allowed;
    }

    .period-item.break .period-time {
        color: #856404;
    }

    .period-item.break .period-label {
        background-color: #ffc107;
    }

    /* Estilo para el borde discontinuo */
    .border-dashed {
        border: 1px dashed #dee2e6;
        border-radius: 6px;
    }

    /* Mejoras para el switch */
    .custom-switch {
        padding-left: 2.25rem;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    /* Mejoras para los selectores de período */
    #block-period-selector .card {
        transition: all 0.2s ease;
    }

    #block-period-selector .card:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    #block-period-summary {
        border-left: 4px solid #28a745;
    }

    /* Animación para los cambios de estado */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Estilos para la vista de horario manual */
    #manual-hours-container {
        transition: all 0.3s ease;
    }

    /* Cambio de color en la sección de selección de horarios */
    .alert-primary, .btn-outline-primary, .text-primary, .spinner-border.text-primary,
    .btn-primary, .badge.bg-primary, .btn-link.text-primary {
        /* color: #364E76 !important; */
        border-color: #364E76 !important;
    }
    
    .bg-primary, .btn-primary, .btn-outline-primary:hover, .btn-outline-primary:active, .btn-outline-primary.active {
        background-color: #364E76 !important;
        border-color: #364E76 !important;
    }
    
    .border-primary {
        border-color: #364E76 !important;
    }

    .alert-primary {
        background-color: rgba(54, 78, 118, 0.15) !important;
        border-color: rgba(54, 78, 118, 0.3) !important;
    }

    .fas.fa-info-circle.fa-2x.text-primary,
    .fas.fa-hourglass-start.text-primary,
    .fas.fa-hourglass-end.text-primary {
        color: #364E76 !important;
    }
    
    /* Estilos para controlar el color del texto en los botones según selección */
    .btn-outline-primary {
        color: #364E76 !important;
    }
    
    .btn-outline-primary.active {
        color: white !important;
    }
    
    .btn-outline-primary:hover {
        color: white !important;
    }
</style>
@stop