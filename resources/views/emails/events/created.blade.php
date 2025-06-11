@component('mail::message')
# Nuevo Evento Requiere su Atención

Se ha creado un nuevo evento que requiere su participación:

**Nombre del evento:** {{ $event->event_name }}  
**Fecha:** {{ $event->service_date->format('d/m/Y') }}  
**Hora:** {{ $event->event_time }}  
**Lugar:** {{ $event->location }}  
**Responsable:** {{ $event->responsible }}

@component('mail::button', ['url' => url('/events/' . $event->id)])
Ver Detalles del Evento
@endcomponent

Por favor, revise los detalles del evento en el sistema para confirmar su participación.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
