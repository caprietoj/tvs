@extends('adminlte::page')

@section('title', 'Calendario de Eventos')

@section('content_header')
    <h1>Calendario de Eventos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@stop

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
.fc-event {
    cursor: pointer;
}

/* Estilos para salidas pedagógicas */
.salida-pedagogica-item {
    border-left: 4px solid #17a2b8 !important;
}

.salida-pedagogica-item .fc-event-title {
    font-style: italic;
}

/* Estilos para eventos regulares */
.event-item {
    border-left: 4px solid #007bff !important;
}

/* Tooltip personalizado */
.fc-event:hover {
    opacity: 0.8;
    transform: scale(1.02);
    transition: all 0.2s ease;
}
</style>
@stop

@section('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            @foreach($events as $event)
            {
                id: 'event_{{ $event->id }}',
                title: {!! json_encode($event->event_name) !!},
                start: '{{ $event->service_date->format("Y-m-d") }}T{{ date("H:i:s", strtotime($event->event_time)) }}',
                url: '{{ route("events.show", $event->id) }}',
                backgroundColor: '{{ $event->getStatusColor() }}',
                borderColor: '{{ $event->getStatusColor() }}',
                allDay: false,
                className: 'event-item'
            },
            @endforeach
            @if(isset($salidasPedagogicas))
            @foreach($salidasPedagogicas as $salida)
            {
                id: 'salida_{{ $salida->id }}',
                title: {!! json_encode('[SP] ' . $salida->lugar . ' - ' . $salida->grados) !!},
                start: '{{ $salida->fecha_salida->format("Y-m-d") }}T{{ $salida->fecha_salida->format("H:i:s") }}',
                @if($salida->fecha_regreso)
                end: '{{ $salida->fecha_regreso->format("Y-m-d") }}T{{ $salida->fecha_regreso->format("H:i:s") }}',
                @endif
                url: '{{ route("salidas.show", $salida->id) }}',
                backgroundColor: '{{ $salida->getStatusColor() }}',
                borderColor: '{{ $salida->getStatusColor() }}',
                allDay: {{ $salida->fecha_regreso && $salida->fecha_regreso->format('Y-m-d') != $salida->fecha_salida->format('Y-m-d') ? 'true' : 'false' }},
                className: 'salida-pedagogica-item'
            },
            @endforeach
            @endif
        ],
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                return false;
            }
        }
    });
    calendar.render();
});
</script>
@stop
