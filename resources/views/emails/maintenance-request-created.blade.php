@component('mail::message')
# Nueva Solicitud de Mantenimiento

Se ha creado una nueva solicitud de mantenimiento:

**Tipo de Solicitud:** {{ ucfirst(str_replace('_', ' ', $maintenanceRequest->request_type)) }}  
**Ubicación:** {{ $maintenanceRequest->location }}  
**Prioridad:** {{ ucfirst($maintenanceRequest->priority) }}  
**Descripción:** {{ $maintenanceRequest->description }}  

@component('mail::button', ['url' => route('maintenance.index')])
Ver Solicitud
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
