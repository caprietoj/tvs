@component('mail::message')
# 🎫 Nuevo Ticket Help-Desk TVS #{{ $ticket->id }}

Se ha registrado un nuevo ticket en el sistema de Help-Desk que requiere su atención.

@php
    $prioridadColor = match($ticket->prioridad) {
        'Alta' => '#dc3545',
        'Media' => '#ffc107',
        'Baja' => '#28a745',
        default => '#6c757d'
    };
    
    $prioridadIcon = match($ticket->prioridad) {
        'Alta' => '🔴',
        'Media' => '🟡',
        'Baja' => '🟢',
        default => '⚪'
    };
    
    $tipoIcon = match($ticket->tipo_requerimiento) {
        'Hardware' => '🖥️',
        'Software' => '💿',
        'Mantenimiento' => '🔧',
        'Instalación' => '📥',
        'Conectividad' => '🌐',
        default => '❔'
    };
@endphp

@component('mail::panel')
## 📋 Detalles del Ticket

<div style="margin-bottom: 20px;">
<strong>Asunto:</strong> {{ $ticket->titulo }}<br>
<strong>{{ $tipoIcon }} Categoría:</strong> {{ $ticket->tipo_requerimiento }}<br>
<strong>{{ $prioridadIcon }} Prioridad:</strong> <span style="color: {{ $prioridadColor }}"><strong>{{ $ticket->prioridad }}</strong></span><br>
<strong>👤 Solicitante:</strong> {{ $ticket->user->name }}<br>
<strong>📅 Fecha:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}
</div>

<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px;">
<strong>📝 Descripción:</strong><br>
{{ $ticket->descripcion }}
</div>

@if(isset($ticket->ubicacion) && $ticket->ubicacion)
<div style="margin-top: 15px;">
<strong>📍 Ubicación:</strong> {{ $ticket->ubicacion }}
</div>
@endif

@if($ticket->tecnico_id)
<div style="margin-top: 15px;">
<strong>👨‍💻 Asignado a:</strong> {{ $ticket->tecnico->name }}
</div>
@endif
@endcomponent

@component('mail::button', ['url' => route('tickets.show', $ticket->id), 'color' => 'primary'])
Ver Detalles del Ticket
@endcomponent

<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
<strong>ℹ️ Información Importante:</strong>
<ul style="margin-top: 10px;">
    <li>Este ticket ha sido asignado automáticamente con prioridad <strong style="color: {{ $prioridadColor }}">{{ $ticket->prioridad }}</strong> basada en su tipo y descripción.</li>
    <li>Para cualquier actualización o seguimiento, por favor utilice el sistema de Help-Desk haciendo clic en el botón superior.</li>
</ul>
</div>

Saludos cordiales,<br>
{{ config('app.name') }}

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #718096;">
Este es un mensaje automático del sistema Help-Desk TVS.<br>Por favor no responda a este correo.
</div>
@endcomponent
