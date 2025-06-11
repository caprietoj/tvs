@extends('adminlte::page')

@section('title', 'Editar Evento')

@section('content_header')
    <h1>Editar Evento #{{ $event->consecutive }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('events.update', $event) }}" method="POST" id="eventForm">
            @csrf
            @method('PUT')
            
            <!-- Campos básicos -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fecha de solicitud</label>
                        <input type="date" name="request_date" class="form-control" required value="{{ $event->request_date->format('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <!-- Información del Evento -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Nombre del evento</label>
                        <input type="text" name="event_name" class="form-control" required value="{{ $event->event_name }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sección</label>
                        <input type="text" name="section" class="form-control" required value="{{ $event->section }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Responsable</label>
                        <input type="text" name="responsible" class="form-control" required value="{{ $event->responsible }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha del servicio</label>
                        <input type="date" name="service_date" class="form-control" required value="{{ $event->service_date->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Hora inicio</label>
                        <input type="time" name="event_time" class="form-control" required value="{{ $event->event_time->format('H:i') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Hora final</label>
                        <input type="time" name="end_time" class="form-control" required value="{{ $event->end_time->format('H:i') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Lugar</label>
                        <select name="location" class="form-control" required>
                            @php
                                $locations = [
                                    'Plaza Colibri' => 'Plaza Colibrí',
                                    'Cancha football' => 'Cancha Fútbol',
                                    'Cancha Baloncesto' => 'Cancha Baloncesto',
                                    'Tienda' => 'Tienda',
                                    'Biblioteca Primer Piso' => 'Biblioteca Primer Piso',
                                    'Biblioteca Segundo Piso' => 'Biblioteca Segundo Piso',
                                    'Auditorio Biblioteca' => 'Auditorio Biblioteca',
                                    'Teatro' => 'Teatro',
                                    'Retiro San Juan' => 'Retiro San Juan',
                                    'Aula Multiple' => 'Aula Multiple'
                                ];
                            @endphp
                            @foreach($locations as $value => $label)
                                <option value="{{ $value }}" {{ $event->location == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Solicitud de parqueadero CAFAM</label>
                <select name="cafam_parking" class="form-control">
                    <option value="0" {{ !$event->cafam_parking ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $event->cafam_parking ? 'selected' : '' }}>Sí</option>
                </select>
            </div>

            <!-- Servicios -->
            <div class="card mt-4">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Servicios Requeridos</h3>
                </div>
                <div class="card-body">
                    <!-- Metro Junior -->
                    <div class="service-section mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="metro_junior_required" 
                                   name="metro_junior_required" value="1" {{ $event->metro_junior_required ? 'checked' : '' }}>
                            <label class="custom-control-label" for="metro_junior_required">Metro Junior</label>
                        </div>
                        <div id="metro_junior_fields" class="service-fields" style="display: none;">
                            <div class="form-group">
                                <label>Ruta</label>
                                <input type="text" name="route" class="form-control" value="{{ $event->route }}">
                            </div>
                            <div class="form-group">
                                <label>Cantidad de pasajeros</label>
                                <input type="number" name="passengers" class="form-control" value="{{ $event->passengers }}">
                            </div>
                            <div class="form-group">
                                <label>Hora de salida</label>
                                <input type="time" name="departure_time" class="form-control" 
                                       value="{{ $event->departure_time ? $event->departure_time->format('H:i') : '' }}">
                            </div>
                            <div class="form-group">
                                <label>Hora de regreso</label>
                                <input type="time" name="return_time" class="form-control"
                                       value="{{ $event->return_time ? $event->return_time->format('H:i') : '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Otros servicios (similar structure) -->
                    @php
                        $services = [
                            'general_services' => [
                                'label' => 'Servicios Generales',
                                'fields' => [
                                    'requirement' => 'Requerimiento',
                                    'setup_date' => 'Fecha de montaje',
                                    'setup_time' => 'Hora de montaje'
                                ]
                            ],
                            'maintenance' => [
                                'label' => 'Mantenimiento',
                                'fields' => [
                                    'requirement' => 'Requerimiento',
                                    'setup_date' => 'Fecha de montaje',
                                    'setup_time' => 'Hora de montaje'
                                ]
                            ],
                            'systems' => [
                                'label' => 'Sistemas',
                                'fields' => [
                                    'requirement' => 'Requerimiento',
                                    'setup_date' => 'Fecha de montaje',
                                    'setup_time' => 'Hora de montaje',
                                    'observations' => 'Observaciones'
                                ]
                            ],
                            'purchases' => [
                                'label' => 'Compras',
                                'fields' => [
                                    'requirement' => 'Requerimiento',
                                    'observations' => 'Observaciones'
                                ]
                            ],
                            'communications' => [
                                'label' => 'Comunicaciones',
                                'fields' => [
                                    'coverage' => 'Cubrimiento',
                                    'observations' => 'Observaciones'
                                ]
                            ],
                            'aldimark' => [
                                'label' => 'Aldimark',
                                'fields' => [
                                    'requirement' => 'Requerimiento',
                                    'time' => 'Hora',
                                    'details' => 'Detalles'
                                ]
                            ]
                        ];
                    @endphp

                    @foreach($services as $service => $config)
                        <div class="service-section mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="{{ $service }}_required" 
                                       name="{{ $service }}_required" 
                                       value="1" 
                                       {{ $event->{$service.'_required'} ? 'checked' : '' }}>
                                <label class="custom-control-label" for="{{ $service }}_required">{{ $config['label'] }}</label>
                            </div>
                            <div id="{{ $service }}_fields" class="service-fields" style="{{ $event->{$service.'_required'} ? 'display: block;' : 'display: none;' }}">
                                @foreach($config['fields'] as $field => $label)
                                    <div class="form-group">
                                        <label>{{ $label }}</label>
                                        @if(strpos($field, 'date') !== false)
                                            @php
                                                $dateField = $service.'_'.$field;
                                                $dateValue = $event->$dateField;
                                            @endphp
                                            <input type="date" 
                                                   name="{{ $service }}_{{ $field }}" 
                                                   class="form-control"
                                                   value="{{ $dateValue instanceof \Carbon\Carbon ? $dateValue->format('Y-m-d') : '' }}"
                                                   {{ $event->{$service.'_required'} ? 'required' : '' }}>
                                        @elseif(strpos($field, 'time') !== false)
                                            <input type="time" 
                                                   name="{{ $service }}_{{ $field }}" 
                                                   class="form-control"
                                                   value="{{ $event->{$service.'_'.$field} instanceof \Carbon\Carbon ? $event->{$service.'_'.$field}->format('H:i') : '' }}">
                                        @elseif(in_array($field, ['observations', 'details']))
                                            <textarea name="{{ $service }}_{{ $field }}" 
                                                      class="form-control">{{ $event->{$service.'_'.$field} }}</textarea>
                                        @else
                                            <input type="text" 
                                                   name="{{ $service }}_{{ $field }}" 
                                                   class="form-control"
                                                   value="{{ $event->{$service.'_'.$field} }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Evento</button>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border-bottom: none;
    }

    .service-group {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .service-group:last-child {
        border-bottom: none;
    }

    .service-fields {
        margin-left: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: var(--border-radius);
        margin-top: 1rem;
        border: 1px solid #e9ecef;
    }

    .form-group label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
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
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    textarea.form-control {
        height: auto;
        min-height: 100px;
    }

    select.form-control {
        padding-right: 2rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23666' d='M0 2l4 4 4-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 8px;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }

    @media (max-width: 768px) {
        .service-fields {
            margin-left: 0;
        }
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupServiceToggle(serviceId) {
        const checkbox = document.getElementById(serviceId);
        const fieldsDiv = document.getElementById(serviceId.replace('_required', '_fields'));
        
        if (!checkbox || !fieldsDiv) return;

        function toggleFields() {
            // Mostrar u ocultar los campos
            fieldsDiv.style.display = checkbox.checked ? 'block' : 'none';
            
            const inputs = fieldsDiv.querySelectorAll('input[type="text"], input[type="number"], input[type="time"], input[type="date"]');
            const textareas = fieldsDiv.querySelectorAll('textarea');
            
            if (!checkbox.checked) {
                // Solo quitamos el atributo required cuando se desmarca
                inputs.forEach(input => input.removeAttribute('required'));
                textareas.forEach(textarea => textarea.removeAttribute('required'));
            } else {
                // Cuando se marca el checkbox, agregamos required a los campos según el servicio
                inputs.forEach(input => {
                    const fieldName = input.getAttribute('name');
                    if (fieldName.includes('requirement') || fieldName.includes('setup_')) {
                        input.setAttribute('required', '');
                    }
                });
            }
        }

        checkbox.addEventListener('change', toggleFields);
        
        // Configuración inicial
        toggleFields();
        
        // Asegurar que los textareas nunca sean requeridos, incluso al cargar la página
        const textareas = fieldsDiv.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.removeAttribute('required');
        });
    }

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
});
</script>
@stop
