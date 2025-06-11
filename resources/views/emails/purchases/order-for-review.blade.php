@component('mail::message')
# Orden de Compra Lista para Revisión

Se ha creado una orden de compra que requiere su revisión y aprobación.

**Detalles de la Orden:**
- **Número de Orden:** {{ $order->order_number }}
- **Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}
- **Solicitante:** {{ $request->user->name }}
- **Sección:** {{ $request->section }}
- **Proveedor:** {{ $order->provider_name }}
- **Total:** ${{ number_format($order->total, 2) }}

Por favor, revise la orden de compra, las cotizaciones y toda la información relacionada para determinar si se aprueba o rechaza.

@component('mail::button', ['url' => $url])
Revisar Orden
@endcomponent

Su aprobación es necesaria para proceder con el pago.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
