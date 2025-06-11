@component('mail::message')
# Nueva Solicitud de Compra

Se ha creado una nueva solicitud de compra que requiere su atención.

**Detalles de la Solicitud:**
- **Número de Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}
- **Solicitante:** {{ $request->user->name }}
- **Sección:** {{ $request->section }}
- **Fecha:** {{ $request->created_at->format('d/m/Y H:i') }}

@component('mail::button', ['url' => $url])
Ver Solicitud
@endcomponent

Por favor, revise esta solicitud lo antes posible para iniciar el proceso de cotización.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
