@component('mail::message')
# Orden de Compra Aprobada

La orden de compra {{ $order->order_number }} ha sido aprobada y está lista para procesar el pago.

**Detalles de la Orden:**
- **Número de Orden:** {{ $order->order_number }}
- **Solicitud:** #{{ $request->id }}
- **Título:** {{ $request->title }}
- **Proveedor:** {{ $order->provider_name }}
- **Subtotal:** ${{ number_format($order->subtotal, 2) }}
- **IVA (19%):** ${{ number_format($order->tax, 2) }}
- **Total:** ${{ number_format($order->total, 2) }}

Se adjunta el PDF de la orden de compra aprobada y las cotizaciones correspondientes.

@component('mail::button', ['url' => $url])
Ver Detalles
@endcomponent

Por favor, proceda a gestionar el pago según los datos del proveedor y registre la información del pago en el sistema una vez completado.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
