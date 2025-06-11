@component('mail::message')
# Actualización de Solicitud de Compra

{{ $message }}

**Detalles de la Solicitud:**
- **Número de Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}
- **Estado Actual:** {{ $request->status }}
- **Actualizado:** {{ $request->updated_at->format('d/m/Y H:i') }}

@component('mail::button', ['url' => $url])
Ver Solicitud
@endcomponent

Si tiene alguna pregunta, no dude en contactar al departamento de compras.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
