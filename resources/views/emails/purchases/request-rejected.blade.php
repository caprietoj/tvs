@component('mail::message')
# Solicitud de Compra Rechazada

Lamentamos informarle que la solicitud de compra #{{ $request->id }} ha sido rechazada.

**Detalles de la Solicitud:**
- **Número de Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}

**Motivo del Rechazo:**
{{ $reason }}

@component('mail::button', ['url' => $url])
Ver Solicitud
@endcomponent

Si tiene alguna duda sobre el motivo del rechazo o desea presentar una nueva solicitud, por favor contacte al departamento de compras.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
