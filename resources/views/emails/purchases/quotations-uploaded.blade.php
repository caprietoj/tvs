@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('img/logo.png') }}" alt="The Victoria School" style="max-width: 200px; height: auto;">
</div>

<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #4a92e6; margin-bottom: 20px;">
    <h1 style="color: #3490dc; margin-top: 0; font-size: 22px; text-align: center;">
        <span style="margin-right: 8px;">📢</span> SOLICITUD REQUIERE PRE-APROBACIÓN
    </h1>
    <p style="font-size: 16px; color: #555; text-align: center;">
        Se han completado las cotizaciones para la solicitud <strong>{{ $request->request_number }}</strong> que requiere su revisión y pre-aprobación inmediata.
    </p>
</div>

<div style="background-color: #f0f7ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <h2 style="color: #2779bd; margin-top: 0; font-size: 18px;">
        <span style="margin-right: 8px;">🔍</span> Resumen de la Solicitud
    </h2>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; width: 40%;"><strong>Número de Solicitud:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->request_number }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Solicitante:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->requester }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Sección/Área:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->section_area }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Fecha de Solicitud:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Título/Descripción:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->title ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Cotizaciones:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>{{ $request->quotations->count() }}</strong> adjuntas a este correo</td>
        </tr>
        @if($request->delivery_date)
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>Fecha requerida:</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $request->delivery_date->format('d/m/Y') }}</td>
        </tr>
        @endif
    </table>
</div>

<div style="background-color: #fdf8e2; padding: 18px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f0ad4e;">
    <h2 style="color: #8a6d3b; margin-top: 0; font-size: 18px; text-align: center;">
        <span style="margin-right: 8px;">⚠️</span> ACCIÓN REQUERIDA
    </h2>
    <p style="text-align: center; font-size: 15px;">Se requiere su <strong>pre-aprobación</strong> para continuar el proceso de compra:</p>
    <ol style="margin-left: 20px; margin-bottom: 15px;">
        <li>Revise las cotizaciones adjuntas a este correo</li>
        <li>Seleccione la oferta que considere más adecuada</li>
        <li>Apruebe la solicitud a través del botón de abajo</li>
    </ol>
    <p style="text-align: center; font-size: 13px; color: #856404; margin-top: 10px; margin-bottom: 0;">Sin su pre-aprobación, no se podrá proceder con la orden de compra.</p>
</div>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
REVISAR Y PRE-APROBAR SOLICITUD
@endcomponent

<div style="font-size: 12px; color: #718096; text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
    <p><strong>Sistema de Compras de The Victoria School</strong></p>
    <p>Este es un correo automático. Por favor no responda a este mensaje.</p>
</div>

Cordialmente,<br>
<strong>Departamento de Compras</strong><br>
{{ config('app.name') }}
@endcomponent
