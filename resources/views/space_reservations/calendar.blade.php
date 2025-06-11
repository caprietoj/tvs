@extends('adminlte::page')

@section('title', 'Calendario de Reservas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Calendario de Reservas de Espacios</h1>
        <div>
            <a href="{{ route('space-reservations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Reserva
            </a>
            <a href="{{ route('space-reservations.index') }}" class="btn btn-info">
                <i class="fas fa-list"></i> Mis Reservas
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="space-filter">Espacio:</label>
                        <select id="space-filter" class="form-control">
                            <option value="all">Todos los espacios</option>
                            @foreach($spaces as $space)
                                <option value="{{ $space->id }}">{{ $space->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Estado:</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="status-pending" checked>
                            <label class="custom-control-label" for="status-pending">
                                <span class="badge badge-warning">Pendientes</span>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="status-approved" checked>
                            <label class="custom-control-label" for="status-approved">
                                <span class="badge badge-success">Aprobadas</span>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="status-rejected" checked>
                            <label class="custom-control-label" for="status-rejected">
                                <span class="badge badge-danger">Rechazadas</span>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="status-cancelled" checked>
                            <label class="custom-control-label" for="status-cancelled">
                                <span class="badge badge-secondary">Canceladas</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button id="refresh-calendar" class="btn btn-primary btn-block">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Espacios Bloqueados</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        @forelse($blockedSpaces as $blockedSpace)
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-ban text-danger"></i> {{ $blockedSpace->space->name }}
                                    <span class="float-right text-muted text-sm">{{ $blockedSpace->cycle_day }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="nav-item">
                                <span class="nav-link text-muted">
                                    <i class="fas fa-info-circle"></i> No hay espacios bloqueados
                                </span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver detalle de reserva -->
    <div class="modal fade" id="event-modal" tabindex="-1" aria-labelledby="event-modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="event-modal-title">Detalle de Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="event-modal-body">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <a href="#" id="view-detail-link" class="btn btn-primary">Ver Detalles</a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-event-pending {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .fc-event-approved {
            background-color: #28a745;
            border-color: #28a745;
        }
        .fc-event-rejected {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .fc-event-cancelled {
            background-color: #6c757d;
            border-color: #6c757d;
            text-decoration: line-through;
        }
        .modal-body dl {
            margin-bottom: 0;
        }
        .modal-body dt {
            font-weight: bold;
        }
        .modal-body dd {
            margin-bottom: 0.5rem;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el calendario
            const calendarEl = document.getElementById('calendar');
            let calendar;
            let eventSources = [];
            
            // Función para obtener los filtros actuales
            function getCurrentFilters() {
                const spaceId = $('#space-filter').val();
                const statusPending = $('#status-pending').is(':checked');
                const statusApproved = $('#status-approved').is(':checked');
                const statusRejected = $('#status-rejected').is(':checked');
                const statusCancelled = $('#status-cancelled').is(':checked');
                
                // Array de estados a mostrar
                const statuses = [];
                if (statusPending) statuses.push('pending');
                if (statusApproved) statuses.push('approved');
                if (statusRejected) statuses.push('rejected');
                if (statusCancelled) statuses.push('cancelled');
                
                return {
                    space_id: spaceId,
                    statuses: statuses.join(',')
                };
            }
            
            // Función para inicializar el calendario con filtros
            function initCalendar() {
                const filters = getCurrentFilters();
                
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    navLinks: true,
                    selectable: true,
                    nowIndicator: true,
                    dayMaxEvents: true,
                    businessHours: {
                        daysOfWeek: [1, 2, 3, 4, 5], // Días de la semana: lunes-viernes
                        startTime: '07:00',
                        endTime: '18:00',
                    },
                    select: function(info) {
                        // Redirigir a página de creación con la fecha seleccionada
                        window.location.href = "{{ route('space-reservations.create') }}?date=" + info.startStr;
                    },
                    eventClick: function(info) {
                        // Abrir modal con información del evento
                        openEventModal(info.event);
                    },
                    events: {
                        url: "{{ route('space-reservations.events') }}",
                        extraParams: filters,
                        failure: function(error) {
                            console.error('Error al cargar eventos:', error);
                            alert('Hubo un error al cargar las reservas. Por favor intente de nuevo.');
                        },
                    },
                    eventClassNames: function(arg) {
                        // Añadir clase según el estado de la reserva
                        return ['fc-event-' + arg.event.extendedProps.status];
                    },
                    // Mostrar log cuando se carguen los eventos
                    loading: function(isLoading) {
                        if (!isLoading) {
                            console.log('Eventos cargados:', calendar.getEvents().length);
                        }
                    }
                });
                
                calendar.render();
                
                // Registrar la fuente de eventos para poder reemplazarla después
                eventSources = calendar.getEventSources();
                console.log("Calendario inicializado con filtros:", filters);
            }
            
            // Inicializar el calendario al cargar la página
            initCalendar();
            
            // Manejador del modal de eventos
            function openEventModal(event) {
                const modal = $('#event-modal');
                const modalBody = $('#event-modal-body');
                const modalTitle = $('#event-modal-title');
                const viewDetailLink = $('#view-detail-link');
                
                // Configurar el título y enlace del modal
                modalTitle.text('Reserva: ' + event.title);
                viewDetailLink.attr('href', "{{ route('space-reservations.index') }}/" + event.id);
                
                // Mostrar spinner mientras carga
                modalBody.html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></div>');
                
                // Cargar datos del evento
                $.ajax({
                    url: "{{ url('space-reservations') }}/" + event.id + "/modal",
                    success: function(data) {
                        modalBody.html(data);
                    },
                    error: function() {
                        modalBody.html('<div class="alert alert-danger">Error al cargar los datos de la reserva.</div>');
                    }
                });
                
                modal.modal('show');
            }
            
            // Filtros de calendario - Actualizado para mostrar feedback al usuario
            $('#refresh-calendar').click(function() {
                const filters = getCurrentFilters();
                
                // Eliminar todas las fuentes de eventos actuales
                if (eventSources && eventSources.length > 0) {
                    eventSources.forEach(source => source.remove());
                }
                
                // Agregar la nueva fuente de eventos con los filtros seleccionados
                calendar.addEventSource({
                    url: "{{ route('space-reservations.events') }}",
                    extraParams: filters
                });
                
                // Refrescar la vista del calendario
                calendar.refetchEvents();
                
                // Mostrar un mensaje de confirmación al usuario
                const statusLabels = {
                    'pending': 'pendientes',
                    'approved': 'aprobadas',
                    'rejected': 'rechazadas',
                    'cancelled': 'canceladas'
                };
                
                // Crear mensaje para el espacio seleccionado
                let spaceMsg = "todos los espacios";
                if (filters.space_id !== 'all') {
                    const spaceName = $('#space-filter option:selected').text();
                    spaceMsg = `"${spaceName}"`;
                }
                
                // Crear mensaje para los estados seleccionados
                let statusesArray = filters.statuses.split(',');
                let statusMsg = statusesArray.map(s => statusLabels[s]).join(', ');
                if (!statusMsg) statusMsg = "ningún estado";
                
                // Mostrar notificación
                const alertMsg = `<div class="alert alert-success alert-dismissible fade show mt-3">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> Calendario actualizado: mostrando reservas de ${spaceMsg} con estados: ${statusMsg}
                </div>`;
                
                // Agregar y luego quitar automáticamente el mensaje
                const alertDiv = $(alertMsg).appendTo($('.card:has(#calendar) .card-body')).hide().slideDown();
                setTimeout(() => alertDiv.fadeOut('slow', function() { $(this).remove(); }), 5000);
                
                console.log("Filtros aplicados:", filters);
            });
        });
    </script>
@stop