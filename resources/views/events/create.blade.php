@extends('adminlte::page')

@section('title', 'Crear Evento')

@section('content_header')
    <h1>Crear Nuevo Evento</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('events.store') }}" method="POST" id="eventForm">
            @csrf
            <!-- Sección de información básica -->
            <div class="form-section">
                <h3 class="form-section-title">Información Básica</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha de solicitud</label>
                            <input type="date" name="request_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre del evento</label>
                            <input type="text" name="event_name" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sección</label>
                            <input type="text" name="section" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Responsable</label>
                            <input type="text" name="responsible" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de fechas y horas -->
            <div class="form-section">
                <h3 class="form-section-title">Fecha y Hora</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha del servicio</label>
                            <div class="date-type-selector mb-2">
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" id="single_date_option" name="date_type" value="single" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="single_date_option">Fecha única</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="multiple_dates_option" name="date_type" value="multiple" class="custom-control-input">
                                    <label class="custom-control-label" for="multiple_dates_option">Múltiples fechas</label>
                                </div>
                            </div>
                            <div id="single_date_container">
                                <input type="date" name="service_date" id="service_date" class="form-control" required>
                            </div>
                            <div id="multiple_dates_container" style="display: none;">
                                <div class="date-inputs">
                                    <div class="d-flex mb-2">
                                        <input type="date" name="service_dates[]" class="form-control mr-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-date" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="add_date">
                                    <i class="fas fa-plus"></i> Agregar otra fecha
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Horario</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted">Hora inicio</label>
                                        <input type="time" name="event_time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted">Hora final</label>
                                        <input type="time" name="end_time" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de lugares y parqueadero -->
            <div class="form-section">
                <h3 class="form-section-title">Ubicación</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Lugar</label>
                            <div class="location-type-selector">
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" id="single_location_option" name="location_type" value="single" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="single_location_option">Lugar único</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="multiple_locations_option" name="location_type" value="multiple" class="custom-control-input">
                                    <label class="custom-control-label" for="multiple_locations_option">Múltiples lugares</label>
                                </div>
                            </div>
                            <div id="single_location_container">
                                <select name="location" id="location" class="form-control" required>
                                    <option value="">Seleccione un lugar</option>
                                    <option value="Plaza Colibri">Plaza Colibrí</option>
                                    <option value="Cancha football">Cancha Fútbol</option>
                                    <option value="Cancha Baloncesto">Cancha Baloncesto</option>
                                    <option value="Tienda">Tienda</option>
                                    <option value="Biblioteca Primer Piso">Biblioteca Primer Piso</option>
                                    <option value="Biblioteca Segundo Piso">Biblioteca Segundo Piso</option>
                                    <option value="Auditorio Biblioteca">Auditorio Biblioteca</option>
                                    <option value="Teatro">Teatro</option>
                                    <option value="Retiro San Juan">Retiro San Juan</option>
                                    <option value="Aula Multiple">Aula Multiple</option>
                                    <option value="Sala de Cine">Sala de Cine</option>
                                </select>
                            </div>
                            <div id="multiple_locations_container" style="display: none;">
                                <select name="locations[]" id="locations" class="form-control" multiple size="5">
                                    <option value="Plaza Colibri">Plaza Colibrí</option>
                                    <option value="Cancha football">Cancha Fútbol</option>
                                    <option value="Cancha Baloncesto">Cancha Baloncesto</option>
                                    <option value="Tienda">Tienda</option>
                                    <option value="Biblioteca Primer Piso">Biblioteca Primer Piso</option>
                                    <option value="Biblioteca Segundo Piso">Biblioteca Segundo Piso</option>
                                    <option value="Auditorio Biblioteca">Auditorio Biblioteca</option>
                                    <option value="Teatro">Teatro</option>
                                    <option value="Retiro San Juan">Retiro San Juan</option>
                                    <option value="Aula Multiple">Aula Multiple</option>
                                    <option value="Sala de Cine">Sala de Cine</option>
                                </select>
                                <small class="form-text text-muted">Mantenga presionada la tecla Ctrl para seleccionar varios lugares.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Solicitud de parqueadero CAFAM</label>
                            <select name="cafam_parking" class="form-control" required>
                                <option value="">Seleccione una opción</option>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de servicios requeridos -->
            <div class="card mt-4">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Servicios Requeridos</h3>
                </div>
                <div class="card-body">
                    <!-- Transporte -->
                    <div class="service-group">
                        <h4 class="text-primary"><i class="fas fa-bus mr-2"></i>Transporte</h4>
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="metro_junior_required" name="metro_junior_required" value="1">
                                <label class="custom-control-label" for="metro_junior_required">Metro Junior</label>
                            </div>
                            <div id="metro_junior_fields" class="service-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Ruta</label>
                                            <input type="text" name="route" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cantidad de pasajeros</label>
                                            <input type="number" name="passengers" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora de salida</label>
                                            <input type="time" name="departure_time" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora de regreso</label>
                                            <input type="time" name="return_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="metro_junior_observations" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios de Montaje -->
                    <div class="service-group">
                        <h4 class="text-primary"><i class="fas fa-tools mr-2"></i>Servicios de Montaje</h4>
                        
                        <!-- Servicios Generales -->
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="general_services_required" name="general_services_required" value="1">
                                <label class="custom-control-label" for="general_services_required">Servicios Generales</label>
                            </div>
                            <div id="general_services_fields" class="service-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Requerimiento</label>
                                            <input type="text" name="general_services_requirement" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de montaje</label>
                                            <input type="date" name="general_services_setup_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora de montaje</label>
                                            <input type="time" name="general_services_setup_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mantenimiento -->
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="maintenance_required" name="maintenance_required" value="1">
                                <label class="custom-control-label" for="maintenance_required">Mantenimiento</label>
                            </div>
                            <div id="maintenance_fields" class="service-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Requerimiento</label>
                                            <input type="text" name="maintenance_requirement" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de montaje</label>
                                            <input type="date" name="maintenance_setup_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora de montaje</label>
                                            <input type="time" name="maintenance_setup_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sistemas -->
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="systems_required" name="systems_required" value="1">
                                <label class="custom-control-label" for="systems_required">Sistemas</label>
                            </div>
                            <div id="systems_fields" class="service-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Requerimiento</label>
                                            <input type="text" name="systems_requirement" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de montaje</label>
                                            <input type="date" name="systems_setup_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora de montaje</label>
                                            <input type="time" name="systems_setup_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="systems_observations" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios de Alimentación -->
                    <div class="service-group">
                        <h4 class="text-primary"><i class="fas fa-utensils mr-2"></i>Alimentación</h4>
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="aldimark_required" name="aldimark_required" value="1">
                                <label class="custom-control-label" for="aldimark_required">Aldimark</label>
                            </div>
                            <div id="aldimark_fields" class="service-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Requerimiento</label>
                                            <input type="text" name="aldimark_requirement" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hora</label>
                                            <input type="time" name="aldimark_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Detalles</label>
                                    <textarea name="aldimark_details" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Otros Servicios -->
                    <div class="service-group">
                        <h4 class="text-primary"><i class="fas fa-cogs mr-2"></i>Otros Servicios</h4>
                        
                        <!-- Compras -->
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="purchases_required" name="purchases_required" value="1">
                                <label class="custom-control-label" for="purchases_required">Compras</label>
                            </div>
                            <div id="purchases_fields" class="service-fields" style="display: none;">
                                <div class="form-group">
                                    <label>Requerimiento</label>
                                    <input type="text" name="purchases_requirement" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="purchases_observations" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Comunicaciones -->
                        <div class="service-section">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="communications_required" name="communications_required" value="1">
                                <label class="custom-control-label" for="communications_required">Comunicaciones</label>
                            </div>
                            <div id="communications_fields" class="service-fields" style="display: none;">
                                <div class="form-group">
                                    <label>Cubrimiento</label>
                                    <input type="text" name="communications_coverage" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="communications_observations" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Crear Evento
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('css')
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
    }

    /* Base de tarjeta y formulario */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: var(--spacing-xl);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border-bottom: none;
        padding: var(--spacing-md) var(--spacing-lg);
    }

    .card-body {
        padding: var(--spacing-lg);
    }

    /* Estructura y secciones del formulario */
    .form-section {
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px solid #eaeaea;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .form-section-title {
        color: var(--primary);
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-sm);
        border-bottom: 2px solid var(--primary);
        display: inline-block;
    }

    /* Grupos de servicios */
    .service-group {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }

    .service-group:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .service-section {
        margin-bottom: var(--spacing-md);
    }

    .service-fields {
        margin-left: var(--spacing-lg);
        padding: var(--spacing-lg);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: var(--border-radius);
        margin-top: var(--spacing-md);
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Campos del formulario */
    .form-group {
        margin-bottom: var(--spacing-lg);
    }

    .form-group label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: var(--spacing-sm);
        display: block;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.5rem 1rem;
        height: calc(2.25rem + 8px);
        font-size: 1rem;
        line-height: 1.5;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.15);
        outline: none;
    }

    textarea.form-control {
        height: auto;
        min-height: 100px;
        resize: vertical;
    }

    select.form-control {
        padding-right: 2rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23666' d='M0 2l4 4 4-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 8px;
    }
    
    select.form-control[multiple] {
        height: auto;
        min-height: 150px;
        background-image: none;
        padding: var(--spacing-sm);
    }

    /* Radio buttons y checkboxes */
    .custom-control-label {
        cursor: pointer;
        padding-top: 2px;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .custom-radio,
    .custom-checkbox {
        margin-right: var(--spacing-md);
    }

    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.15);
    }

    /* Ajustes específicos para alineación de fecha/hora */
    .date-type-selector,
    .location-type-selector {
        display: flex;
        align-items: center;
        margin-bottom: var(--spacing-md);
    }
    
    .small.text-muted {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        color: #6c757d;
    }
    
    /* Alineación de contenedores de fecha */
    #single_date_container,
    #multiple_dates_container {
        margin-top: 0.5rem;
    }
    
    /* Botones de agregar/eliminar fecha */
    #add_date {
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
        margin-top: 0.5rem;
    }
    
    .remove-date {
        align-self: center;
    }
    
    /* Espaciado entre grupos de campos relacionados */
    .form-group .row .form-group {
        margin-bottom: 0.5rem;
    }
    
    /* Ajustes para campos de tiempo */
    input[type="time"].form-control {
        padding: 0.4rem 0.75rem;
    }

    /* Botones */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }
    
    .btn-outline-primary {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: #fff;
    }
    
    .btn-outline-danger {
        border-color: var(--danger);
        color: var(--danger);
    }
    
    .btn-outline-danger:hover {
        background-color: var(--danger);
        color: #fff;
    }

    /* Colores y decoraciones */
    .text-primary {
        color: var(--primary) !important;
    }

    /* Espaciadores */
    .mb-form {
        margin-bottom: var(--spacing-lg);
    }

    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, rgba(234,234,234,0) 0%, rgba(234,234,234,1) 50%, rgba(234,234,234,0) 100%);
        margin: var(--spacing-xl) 0;
    }

    /* Ajustes responsive */
    @media (max-width: 767.98px) {
        .card-body {
            padding: var(--spacing-md);
        }

        .form-row [class*="col-"] {
            margin-bottom: var(--spacing-md);
        }
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controles para selección de fecha única o múltiple
    const singleDateOption = document.getElementById('single_date_option');
    const multipleDatesOption = document.getElementById('multiple_dates_option');
    const singleDateContainer = document.getElementById('single_date_container');
    const multipleDatesContainer = document.getElementById('multiple_dates_container');
    const serviceDateInput = document.getElementById('service_date');
    
    // Controles para selección de lugar único o múltiple
    const singleLocationOption = document.getElementById('single_location_option');
    const multipleLocationsOption = document.getElementById('multiple_locations_option');
    const singleLocationContainer = document.getElementById('single_location_container');
    const multipleLocationsContainer = document.getElementById('multiple_locations_container');
    const locationInput = document.getElementById('location');
    const locationsInput = document.getElementById('locations');
    
    // Botón para agregar más fechas
    const addDateBtn = document.getElementById('add_date');
    
    // Control de fechas
    singleDateOption.addEventListener('change', function() {
        if (this.checked) {
            singleDateContainer.style.display = 'block';
            multipleDatesContainer.style.display = 'none';
            serviceDateInput.setAttribute('required', '');
            
            // Quitar required de los campos múltiples
            const multipleInputs = multipleDatesContainer.querySelectorAll('input[name="service_dates[]"]');
            multipleInputs.forEach(input => {
                input.removeAttribute('required');
            });
        }
    });
    
    multipleDatesOption.addEventListener('change', function() {
        if (this.checked) {
            singleDateContainer.style.display = 'none';
            multipleDatesContainer.style.display = 'block';
            serviceDateInput.removeAttribute('required');
            
            // Hacer required al menos el primer campo de fechas múltiples
            const firstMultipleInput = multipleDatesContainer.querySelector('input[name="service_dates[]"]');
            if (firstMultipleInput) {
                firstMultipleInput.setAttribute('required', '');
            }
        }
    });
    
    // Control de lugares
    singleLocationOption.addEventListener('change', function() {
        if (this.checked) {
            singleLocationContainer.style.display = 'block';
            multipleLocationsContainer.style.display = 'none';
            locationInput.setAttribute('required', '');
            locationsInput.removeAttribute('required');
        }
    });
    
    multipleLocationsOption.addEventListener('change', function() {
        if (this.checked) {
            singleLocationContainer.style.display = 'none';
            multipleLocationsContainer.style.display = 'block';
            locationInput.removeAttribute('required');
            locationsInput.setAttribute('required', '');
        }
    });
    
    // Funcionalidad para agregar más fechas
    addDateBtn.addEventListener('click', function() {
        const dateInputs = multipleDatesContainer.querySelector('.date-inputs');
        const newInput = document.createElement('div');
        newInput.className = 'd-flex mb-2';
        newInput.innerHTML = `
            <input type="date" name="service_dates[]" class="form-control mr-2" required>
            <button type="button" class="btn btn-sm btn-outline-danger remove-date">
                <i class="fas fa-times"></i>
            </button>
        `;
        dateInputs.appendChild(newInput);
        
        // Mostrar botones de eliminar
        const removeButtons = multipleDatesContainer.querySelectorAll('.remove-date');
        removeButtons.forEach(button => {
            button.style.display = 'block';
        });
    });
    
    // Delegación de eventos para eliminar fechas
    multipleDatesContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-date') || event.target.closest('.remove-date')) {
            const button = event.target.classList.contains('remove-date') ? event.target : event.target.closest('.remove-date');
            const inputContainer = button.parentElement;
            inputContainer.remove();
            
            // Si solo queda un input, ocultar los botones de eliminar
            const dateInputs = multipleDatesContainer.querySelectorAll('.d-flex');
            if (dateInputs.length === 1) {
                const removeButton = dateInputs[0].querySelector('.remove-date');
                if (removeButton) {
                    removeButton.style.display = 'none';
                }
            }
        }
    });
    
    // Función mejorada para manejar toggles de servicios
    function setupServiceToggle(serviceId) {
        const checkbox = document.getElementById(serviceId);
        const fieldsDiv = document.getElementById(serviceId.replace('_required', '_fields'));
        
        if (!checkbox || !fieldsDiv) {
            console.error(`Elementos no encontrados para ${serviceId}`);
            return;
        }

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                // Mostrar los campos con una animación suave
                fieldsDiv.style.display = 'block';
                fieldsDiv.style.opacity = '0';
                setTimeout(() => {
                    fieldsDiv.style.opacity = '1';
                }, 10);
                
                // Hacer requeridos los campos relevantes
                const inputs = fieldsDiv.querySelectorAll('input[type="text"], input[type="number"], input[type="time"], input[type="date"]');
                inputs.forEach(input => {
                    input.setAttribute('required', '');
                });
            } else {
                // Ocultar los campos con una animación suave
                fieldsDiv.style.opacity = '0';
                setTimeout(() => {
                    fieldsDiv.style.display = 'none';
                    
                    // Quitar requeridos y limpiar valores
                    const inputs = fieldsDiv.querySelectorAll('input[type="text"], input[type="number"], input[type="time"], input[type="date"], textarea');
                    inputs.forEach(input => {
                        input.removeAttribute('required');
                        input.value = '';
                    });
                }, 200);
            }
        });

        // Estado inicial
        fieldsDiv.style.display = checkbox.checked ? 'block' : 'none';
        
        // Asegurar que los textareas nunca sean requeridos
        const textareas = fieldsDiv.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.removeAttribute('required');
        });
    }

    // Inicializar todos los toggles de servicios
    const services = [
        'metro_junior_required',
        'general_services_required',
        'maintenance_required',
        'systems_required',
        'aldimark_required',
        'purchases_required',
        'communications_required'
    ];

    services.forEach(setupServiceToggle);
    
    // Mejorar la experiencia del formulario
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        // Corrección para fechas únicas
        if (document.getElementById('single_date_option').checked) {
            // Si es fecha única, tomamos el valor de service_date y lo asignamos al primer elemento de service_dates[]
            const singleDateValue = document.getElementById('service_date').value;
            
            // Eliminamos cualquier campo de service_dates[] existente
            const existingDateInputs = document.querySelectorAll('input[name="service_dates[]"]');
            existingDateInputs.forEach(input => {
                input.parentElement.remove();
            });
            
            // Creamos un campo oculto con el valor de la fecha única
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'service_dates[]';
            hiddenInput.value = singleDateValue;
            this.appendChild(hiddenInput);
        }
        
        let submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
        
        // Reactivar el botón después de 5 segundos en caso de error
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Crear Evento';
        }, 5000);
    });
});
</script>
@stop
