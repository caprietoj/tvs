@component('mail::message')
# Nueva novedad registrada en un evento

**{{ $novelty->user->name }}** ha registrado una nueva novedad en el evento que requiere su atención:

---

**Evento:** {{ $event->event_name }}  
**Consecutivo:** {{ $event->consecutive }}  
**Fecha del evento:** {{ $event->service_date->format('d/m/Y') }}  
**Hora:** {{ is_object($event->event_time) ? $event->event_time->format('H:i') : $event->event_time }}  
**Lugar:** {{ $event->location }}  
**Responsable:** {{ $event->responsible }}

---

**Novedad registrada:**

> {{ $novelty->observation }}

*Registrada el {{ $novelty->created_at->format('d/m/Y') }} a las {{ $novelty->created_at->format('H:i') }}*

---

**Servicios involucrados en este evento:**

@php
    $services = [
        'metro_junior'    => 'Metro Junior',
        'aldimark'        => 'Aldimark',
        'maintenance'     => 'Mantenimiento',
        'general_services'=> 'Servicios Generales',
        'systems'         => 'Sistemas',
        'purchases'       => 'Compras',
        'communications'  => 'Comunicaciones',
        'nursing'         => 'Enfermería',
    ];
@endphp

@foreach($services as $key => $label)
    @if($event->{$key . '_required'})
- {{ $label }} — {{ $event->{$key . '_confirmed'} ? '✅ Confirmado' : '⏳ Pendiente de confirmación' }}
    @endif
@endforeach

@component('mail::button', ['url' => url('/events/' . $event->id)])
Ver el evento completo
@endcomponent

Este correo fue enviado automáticamente porque su área está vinculada a este evento.

Gracias,  
{{ config('app.name') }}
@endcomponent
