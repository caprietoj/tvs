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
                id: '{{ $event->id }}',
                title: '{{ $event->event_name }}',
                start: '{{ $event->service_date->format("Y-m-d") }}T{{ date("H:i:s", strtotime($event->event_time)) }}',
                url: '{{ route("events.show", $event->id) }}',
                backgroundColor: '{{ $event->getStatusColor() }}',
                borderColor: '{{ $event->getStatusColor() }}',
                allDay: false
            },
            @endforeach
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
