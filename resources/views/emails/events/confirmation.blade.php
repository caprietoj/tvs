@component('mail::message')
# Evento Creado Exitosamente

Su evento ha sido creado y las notificaciones han sido enviadas a las áreas correspondientes.

**Detalles del evento:**

**Consecutivo:** {{ $event->consecutive }}  
**Nombre del evento:** {{ $event->event_name }}  
**Fecha:** {{ $event->service_date->format('d/m/Y') }}  
**Hora:** {{ $event->event_time }}  
**Lugar:** {{ $event->location }}

@if($event->metro_junior_required)
**Metro Junior:** Solicitado  
@endif

@if($event->aldimark_required)
**Aldimark:** Solicitado  
@endif

@if($event->maintenance_required)
**Mantenimiento:** Solicitado  
@endif

@if($event->general_services_required)
**Servicios Generales:** Solicitado  
@endif

@if($event->systems_required)
**Sistemas:** Solicitado  
@endif

@if($event->purchases_required)
**Compras:** Solicitado  
@endif

@if($event->communications_required)
**Comunicaciones:** Solicitado  
@endif

@component('mail::button', ['url' => route('events.index')])
Ver Todos los Eventos
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
