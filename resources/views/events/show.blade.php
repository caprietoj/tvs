@extends('adminlte::page')

@section('title', 'Detalles del Evento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-alt text-primary"></i> Detalles del Evento</h1>
        <div>
            <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'edicion-eliminacion-evento']))
                <a href="{{ route('events.edit', $event) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('events.destroy', $event) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger delete-event">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Información General -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información General</h3>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Consecutivo</th>
                        <td><span class="badge badge-info">{{ $event->consecutive }}</span></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-signature"></i> Nombre</th>
                        <td>{{ $event->event_name }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-calendar"></i> Fecha Solicitud</th>
                        <td>{{ is_object($event->request_date) ? $event->request_date->format('d/m/Y') : $event->request_date }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-calendar-check"></i> Fecha Servicio</th>
                        <td>
                            {{ is_object($event->service_date) ? $event->service_date->format('d/m/Y') : $event->service_date }}
                            
                            @if(isset($event->service_dates) && is_array($event->service_dates) && count($event->service_dates) > 1)
                                <button class="btn btn-sm btn-info ml-2" type="button" data-toggle="collapse" data-target="#collapseDates" aria-expanded="false" aria-controls="collapseDates">
                                    Ver todas ({{ count($event->service_dates) }})
                                </button>
                                <div class="collapse mt-2" id="collapseDates">
                                    <div class="card card-body py-1 px-2 bg-light">
                                        <ul class="mb-0 pl-3">
                                            @foreach($event->service_dates as $date)
                                                <li>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th><i class="far fa-clock"></i> Hora Inicio</th>
                        <td>{{ is_object($event->event_time) ? $event->event_time->format('H:i') : $event->event_time }}</td>
                    </tr>
                    <tr>
                        <th><i class="far fa-clock"></i> Hora Final</th>
                        <td>{{ is_object($event->end_time) ? $event->end_time->format('H:i') : $event->end_time }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-map-marker-alt"></i> Lugar</th>
                        <td>
                            {{ $event->location }}
                            
                            @if(isset($event->locations) && is_array($event->locations) && count($event->locations) > 1)
                                <button class="btn btn-sm btn-info ml-2" type="button" data-toggle="collapse" data-target="#collapseLocations" aria-expanded="false" aria-controls="collapseLocations">
                                    Ver todos ({{ count($event->locations) }})
                                </button>
                                <div class="collapse mt-2" id="collapseLocations">
                                    <div class="card card-body py-1 px-2 bg-light">
                                        <ul class="mb-0 pl-3">
                                            @foreach($event->locations as $location)
                                                <li>{{ $location }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Responsable</th>
                        <td>{{ $event->responsible }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-parking"></i> Parqueadero CAFAM</th>
                        <td>
                            <span class="badge badge-{{ $event->cafam_parking ? 'success' : 'secondary' }}">
                                {{ $event->cafam_parking ? 'Sí' : 'No' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Estado de Servicios -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Estado de Servicios</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        @if($event->metro_junior_required)
                        <tr>
                            <td class="border-left border-primary" style="width: 40%">
                                <strong><i class="fas fa-bus"></i> Metro Junior</strong>
                                <div class="text-muted small">
                                    @if($event->route)
                                        <div><i class="fas fa-route"></i> Ruta: {{ $event->route }}</div>
                                    @endif
                                    @if($event->passengers)
                                        <div><i class="fas fa-users"></i> Pasajeros: {{ $event->passengers }}</div>
                                    @endif
                                    @if($event->departure_time)
                                        <div><i class="fas fa-clock"></i> Salida: {{ $event->service_date->format('d/m/Y') }} {{ $event->departure_time->format('H:i') }}</div>
                                    @endif
                                    @if($event->return_time)
                                        <div><i class="fas fa-clock"></i> Regreso: {{ $event->service_date->format('d/m/Y') }} {{ $event->return_time->format('H:i') }}</div>
                                    @endif
                                    @if($event->metro_junior_observations)
                                        <div><i class="fas fa-comment"></i> Observaciones: {{ $event->metro_junior_observations }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->metro_junior_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-metro-junior']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="metro_junior" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->maintenance_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-tools"></i> Mantenimiento</strong>
                                <div class="text-muted small">
                                    @if($event->maintenance_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->maintenance_requirement }}</div>
                                    @endif
                                    @if($event->maintenance_setup_date)
                                        <div><i class="fas fa-calendar-day"></i> Fecha montaje: {{ is_object($event->maintenance_setup_date) ? $event->maintenance_setup_date->format('d/m/Y') : $event->maintenance_setup_date }}</div>
                                    @endif
                                    @if($event->maintenance_setup_time)
                                        <div><i class="fas fa-clock"></i> Hora montaje: {{ $event->maintenance_setup_time }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->maintenance_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-mantenimiento']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="maintenance" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->systems_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-laptop"></i> Sistemas</strong>
                                <div class="text-muted small">
                                    @if($event->systems_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->systems_requirement }}</div>
                                    @endif
                                    @if($event->systems_setup_date)
                                        <div><i class="fas fa-calendar-day"></i> Fecha montaje: {{ is_object($event->systems_setup_date) ? $event->systems_setup_date->format('d/m/Y') : $event->systems_setup_date }}</div>
                                    @endif
                                    @if($event->systems_setup_time)
                                        <div><i class="fas fa-clock"></i> Hora montaje: {{ $event->systems_setup_time }}</div>
                                    @endif
                                    @if($event->systems_observations)
                                        <div><i class="fas fa-comment"></i> Observaciones: {{ $event->systems_observations }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->systems_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-sistemas']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="systems" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->general_services_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-concierge-bell"></i> Servicios Generales</strong>
                                <div class="text-muted small">
                                    @if($event->general_services_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->general_services_requirement }}</div>
                                    @endif
                                    @if($event->general_services_setup_date)
                                        <div><i class="fas fa-calendar-day"></i> Fecha montaje: {{ is_object($event->general_services_setup_date) ? $event->general_services_setup_date->format('d/m/Y') : $event->general_services_setup_date }}</div>
                                    @endif
                                    @if($event->general_services_setup_time)
                                        <div><i class="fas fa-clock"></i> Hora montaje: {{ $event->general_services_setup_time }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->general_services_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-servicios-generales']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="general_services" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->aldimark_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-utensils"></i> Aldimark</strong>
                                <div class="text-muted small">
                                    @if($event->aldimark_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->aldimark_requirement }}</div>
                                    @endif
                                    @if($event->aldimark_time)
                                        <div><i class="fas fa-clock"></i> Hora: {{ $event->aldimark_time }}</div>
                                    @endif
                                    @if($event->aldimark_details)
                                        <div><i class="fas fa-info-circle"></i> Detalles: {{ $event->aldimark_details }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->aldimark_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-aldimark']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="aldimark" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->purchases_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-shopping-cart"></i> Compras</strong>
                                <div class="text-muted small">
                                    @if($event->purchases_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->purchases_requirement }}</div>
                                    @endif
                                    @if($event->purchases_observations)
                                        <div><i class="fas fa-comment"></i> Observaciones: {{ $event->purchases_observations }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->purchases_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-compras']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="purchases" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->communications_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-bullhorn"></i> Comunicaciones</strong>
                                <div class="text-muted small">
                                    @if($event->communications_coverage)
                                        <div><i class="fas fa-camera"></i> Cubrimiento: {{ $event->communications_coverage }}</div>
                                    @endif
                                    @if($event->communications_observations)
                                        <div><i class="fas fa-comment"></i> Observaciones: {{ $event->communications_observations }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->communications_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-comunicaciones']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="communications" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if($event->nursing_required)
                        <tr>
                            <td class="border-left border-primary">
                                <strong><i class="fas fa-briefcase-medical"></i> Enfermería</strong>
                                <div class="text-muted small">
                                    @if($event->nursing_requirement)
                                        <div><i class="fas fa-clipboard-list"></i> Requerimiento: {{ $event->nursing_requirement }}</div>
                                    @endif
                                    @if($event->nursing_observations)
                                        <div><i class="fas fa-comment"></i> Observaciones: {{ $event->nursing_observations }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                @if($event->nursing_confirmed)
                                    <span class="badge badge-success badge-lg">Confirmado</span>
                                @else
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-enfermeria']) || auth()->user()->can('confirm.events'))
                                        <button type="button" class="btn btn-warning btn-sm confirm-service" 
                                                data-service="nursing" 
                                                data-event="{{ $event->id }}"
                                                data-url="{{ route('events.confirm-service', ['event' => $event->id]) }}">
                                            <i class="fas fa-check"></i> Confirmar
                                        </button>
                                    @else
                                        <span class="badge badge-warning badge-lg">Pendiente</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Progreso General -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Progreso General</h3>
            </div>
            <div class="card-body">
                @php
                    $totalServices = 0;
                    $confirmedServices = 0;
                    $services = ['metro_junior', 'aldimark', 'maintenance', 'general_services', 'systems', 'purchases', 'communications', 'nursing'];
                    
                    foreach ($services as $service) {
                        $requiredField = $service . '_required';
                        $confirmedField = $service . '_confirmed';
                        
                        if ($event->$requiredField) {
                            $totalServices++;
                            if ($event->$confirmedField) {
                                $confirmedServices++;
                            }
                        }
                    }
                    
                    $progress = $totalServices > 0 ? ($confirmedServices / $totalServices) * 100 : 0;
                @endphp
                
                <div class="progress-group">
                    Servicios Confirmados
                    <span class="float-right"><b>{{ $confirmedServices }}</b>/{{ $totalServices }}</span>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                <p class="text-center mt-3 mb-0">
                    <span class="badge badge-{{ $progress == 100 ? 'success' : ($progress > 0 ? 'warning' : 'danger') }}">
                        {{ $progress }}% Completado
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     NOVEDADES DEL EVENTO
     ============================================================ -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-clipboard-list"></i> Novedades
                    @if($event->novelties->count() > 0)
                        <span class="badge badge-primary ml-1">{{ $event->novelties->count() }}</span>
                    @endif
                </h3>
                <button class="btn btn-sm btn-primary" type="button"
                        data-toggle="collapse" data-target="#collapseNovedad"
                        aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <i class="fas fa-plus"></i> Agregar novedad
                </button>
            </div>

            {{-- Formulario inline colapsable --}}
            <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="collapseNovedad">
                <div class="card-body border-bottom bg-light">
                    <form action="{{ route('event.novelties.store', $event) }}" method="POST">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="observation" class="font-weight-bold">
                                <i class="fas fa-pen"></i> Nueva novedad / observación
                            </label>
                            <textarea name="observation" id="observation" rows="3"
                                class="form-control @error('observation') is-invalid @enderror"
                                placeholder="Ingrese la novedad u observación…" maxlength="1000">{{ old('observation') }}</textarea>
                            @error('observation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Máximo 1000 caracteres.</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Guardar novedad
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-toggle="collapse" data-target="#collapseNovedad">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if($event->novelties->count() > 0)
                    <div class="timeline">
                        @foreach($event->novelties->sortByDesc('created_at') as $novelty)
                        <div class="time-label">
                            <span class="bg-primary">{{ $novelty->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-clipboard-list bg-info"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> {{ $novelty->created_at->format('h:i A') }}
                                </span>
                                <h3 class="timeline-header">
                                    <strong>{{ $novelty->user->name }}</strong> registró una novedad
                                    @if($novelty->created_at->ne($novelty->updated_at))
                                        <small class="text-muted ml-1">(editada {{ $novelty->updated_at->format('d/m/Y H:i') }})</small>
                                    @endif
                                </h3>
                                <div class="timeline-body">
                                    {{ $novelty->observation }}
                                </div>
                                @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad']))
                                <div class="timeline-footer">
                                    <a href="{{ route('event.novelties.edit', ['event' => $event, 'novelty' => $novelty]) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form action="{{ route('event.novelties.destroy', ['event' => $event, 'novelty' => $novelty]) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs delete-novelty-show">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        No hay novedades registradas para este evento.
                        Use el botón <strong>Agregar novedad</strong> para registrar la primera.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #1a4884;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --info: #17a2b8;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 1rem;
    }

    .card-outline {
        border-top: 3px solid var(--primary);
    }

    .table td, .table th {
        padding: 1rem;
        vertical-align: middle;
    }

    .table tr:hover {
        background-color: rgba(0,0,0,.01);
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 1.2rem;
    }

    .badge-info {
        background-color: var(--info);
        color: white;
    }

    .border-left {
        border-left: 4px solid !important;
    }

    .border-primary {
        border-color: var(--primary) !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .small {
        font-size: 0.875rem;
    }

    .fas, .far {
        margin-right: 5px;
    }

    .progress {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
        background-color: rgba(0,0,0,.1);
    }

    .progress-bar {
        background-color: var(--primary);
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        margin-left: 0.5rem;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .btn {
            margin-bottom: 0.5rem;
            width: 100%;
            margin-left: 0;
        }
    }

    /* ── Novedades timeline ─────────────────────────── */
    .timeline {
        position: relative;
        margin: 0 0 20px 0;
        padding: 0;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    .timeline > div {
        position: relative;
        margin-right: 10px;
        margin-bottom: 12px;
    }
    .time-label {
        background-color: #fff;
        display: inline-block;
        margin-left: 45px;
        padding: 4px 6px;
        font-weight: 600;
    }
    .time-label > span {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        color: #fff;
    }
    .timeline > div > i {
        width: 30px;
        height: 30px;
        font-size: 14px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        background-color: #6c757d;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
    .timeline-item {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 4px;
        background-color: #fff;
        color: #495057;
        margin-left: 60px;
        margin-right: 10px;
        margin-bottom: 8px;
        padding: 0;
        position: relative;
    }
    .timeline-header {
        border-radius: 4px 4px 0 0;
        padding: 8px 12px;
        border-bottom: 1px solid rgba(0,0,0,.1);
        font-size: 0.9rem;
    }
    .timeline-body {
        padding: 10px 12px;
        white-space: pre-wrap;
    }
    .timeline-footer {
        padding: 6px 12px;
        background-color: rgba(0,0,0,.04);
        border-radius: 0 0 4px 4px;
    }
    .timeline-item .time {
        color: #999;
        float: right;
        padding: 8px 12px 0 0;
        font-size: 0.8rem;
    }
    .btn-xs {
        padding: 2px 8px;
        font-size: 0.78rem;
        border-radius: 4px;
    }
</style>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejador para confirmar servicios
    $(document).on('click', '.confirm-service', function() {
        const button = $(this);
        const service = button.data('service');
        const eventId = button.data('event');
        const url = button.data('url');

        Swal.fire({
            title: '¿Confirmar servicio?',
            text: "¿Estás seguro de que deseas confirmar este servicio?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Realizar la petición AJAX para confirmar el servicio
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        service: service
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                title: '¡Confirmado!',
                                text: 'El servicio ha sido confirmado exitosamente.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Hubo un error al confirmar el servicio.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al procesar la solicitud.', 'error');
                    }
                });
            }
        });
    });

    // SweetAlert para mensajes de sesión
    @if(session('swal'))
        Swal.fire({
            icon: '{{ session("swal.icon") }}',
            title: '{{ session("swal.title") }}',
            text: '{{ session("swal.text") }}',
            showConfirmButton: true,
            timer: 3000
        });
    @endif

    // SweetAlert para confirmar eliminación del evento
    $(document).on('click', '.delete-event', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // SweetAlert para confirmar eliminación de una novedad
    $(document).on('click', '.delete-novelty-show', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: '¿Eliminar novedad?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Mantener abierto el collapse si hay errores de validación
    @if($errors->any())
        $('#collapseNovedad').addClass('show');
    @endif
});
</script>
@stop
