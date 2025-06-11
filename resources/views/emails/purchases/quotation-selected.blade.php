@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('img/logo.png') }}" alt="The Victoria School" style="max-width: 200px; height: auto;">
</div>

<div style="background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-bottom: 20px;">
    <h1 style="color: #155724; margin-top: 0; font-size: 22px; text-align: center;">
        <span style="margin-right: 8px;">✅</span> COTIZACIÓN PRE-APROBADA
    </h1>
    <p style="font-size: 16px; color: #555; text-align: center;">
        La solicitud <strong>#{{ $request->request_number }}</strong> ha sido pre-aprobada con la siguiente cotización seleccionada.
    </p>
</div>

<div style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
    <h2 style="color: #1a1a1a; font-size: 18px; margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
        Detalles de la solicitud
    </h2>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="padding: 8px 0; color: #666; width: 40%;">Número:</td>
            <td style="padding: 8px 0;"><strong>#{{ $request->request_number }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #666;">Solicitante:</td>
            <td style="padding: 8px 0;"><strong>{{ $request->user->name ?? 'No disponible' }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #666;">Área/Sección:</td>
            <td style="padding: 8px 0;"><strong>{{ $request->section_area }}</strong></td>
        </tr>
    </table>
</div>

<div style="background-color: #e9f7f2; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #20c997;">
    <h2 style="color: #1e7e5b; font-size: 18px; margin-top: 0; border-bottom: 1px solid #b4e3d5; padding-bottom: 8px;">
        Cotización Seleccionada
    </h2>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="padding: 8px 0; color: #666; width: 40%;">Proveedor:</td>
            <td style="padding: 8px 0;"><strong>{{ $quotation->provider_name }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #666;">Monto Total:</td>
            <td style="padding: 8px 0;"><strong>$ {{ number_format($quotation->total_amount, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
</div>

@component('mail::button', ['url' => $url, 'color' => 'success'])
VER DETALLES DE LA SOLICITUD
@endcomponent

<div style="font-size: 12px; color: #718096; text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
    <p><strong>Sistema de Compras de The Victoria School</strong></p>
    <p>Este es un correo automático. Por favor no responda a este mensaje.</p>
</div>

Cordialmente,<br>
<strong>Departamento de Compras</strong><br>
{{ config('app.name') }}
@endcomponent
