@component('mail::message')
# Pago Registrado - Solicitud Completada

Nos complace informarle que el pago correspondiente a su solicitud de compra #{{ $request->id }} ha sido registrado y la solicitud ha sido completada.

**Detalles de la Solicitud:**
- **Número de Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}

**Detalles del Pago:**
- **Número de Orden:** {{ $order->order_number }}
- **Fecha de Pago:** {{ \Carbon\Carbon::parse($order->payment_date)->format('d/m/Y') }}
- **Referencia de Pago:** {{ $order->payment_reference }}
- **Total Pagado:** ${{ number_format($order->total, 2) }}

@component('mail::button', ['url' => $url])
Ver Detalles Completos
@endcomponent

Gracias por utilizar nuestro sistema de gestión de compras.

Atentamente,<br>
{{ config('app.name') }}
@endcomponent
