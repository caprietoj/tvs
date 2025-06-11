@component('mail::message')
# Servicio Confirmado

Se ha confirmado un servicio para el siguiente evento:

**Consecutivo:** {{ $event->consecutive }}  
**Nombre del evento:** {{ $event->event_name }}  
**Fecha:** {{ $event->service_date->format('d/m/Y') }}  
**Hora:** {{ $event->event_time->format('H:i') }}  
**Lugar:** {{ $event->location }}

@if($event->metro_junior_required && $event->metro_junior_confirmed)
**Metro Junior:** Confirmado  
@endif

@if($event->aldimark_required && $event->aldimark_confirmed)
**Aldimark:** Confirmado  
@endif

@if($event->maintenance_required && $event->maintenance_confirmed)
**Mantenimiento:** Confirmado  
@endif

@if($event->general_services_required && $event->general_services_confirmed)
**Servicios Generales:** Confirmado  
@endif

@if($event->systems_required && $event->systems_confirmed)
**Sistemas:** Confirmado  
@endif

@if($event->purchases_required && $event->purchases_confirmed)
**Compras:** Confirmado  
@endif

@if($event->communications_required && $event->communications_confirmed)
**Comunicaciones:** Confirmado  
@endif

@component('mail::button', ['url' => route('events.show', $event->id)])
Ver Detalles del Evento
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
