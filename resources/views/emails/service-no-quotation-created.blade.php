<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailType == 'user' ? 'Confirmación' : 'Nueva' }} Solicitud de Servicio sin Cotización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #364E76;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .request-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #364E76;
        }
        .no-quotation-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .provider-info {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .services-table th,
        .services-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .services-table th {
            background-color: #364E76;
            color: white;
        }
        .action-button {
            display: inline-block;
            background-color: #364E76;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .action-button:hover {
            background-color: #2a3d5f;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                @if($emailType == 'user')
                    <i>✓</i> Confirmación de Solicitud
                @elseif($emailType == 'pre_approval')
                    <i>⚠</i> Pre-aprobación Requerida
                @else
                    <i>📋</i> Nueva Solicitud de Servicio
                @endif
            </h1>
            <p style="margin: 5px 0 0 0;">Servicio sin Cotización #{{ $purchaseRequest->request_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            @if($emailType == 'user')
                <h2>¡Solicitud Enviada Exitosamente!</h2>
                <p>Estimado/a <strong>{{ $purchaseRequest->requester }}</strong>,</p>
                <p>Su solicitud de servicio sin cotización ha sido recibida y está siendo procesada. Al tratarse de un servicio que no requiere cotización, será enviada directamente para pre-aprobación.</p>
            @elseif($emailType == 'pre_approval')
                <h2>Solicitud Pendiente de Pre-aprobación</h2>
                <p>Se ha recibido una nueva solicitud de servicio sin cotización que requiere su pre-aprobación.</p>
                <div class="alert alert-warning">
                    <strong>Acción Requerida:</strong> Esta solicitud requiere su autorización antes de proceder con la orden de compra.
                </div>
            @else
                <h2>Nueva Solicitud Recibida</h2>
                <p>Se ha recibido una nueva solicitud de servicio sin cotización.</p>
                <div class="alert alert-info">
                    <strong>Información:</strong> Esta solicitud no requiere proceso de cotización y puede proceder directamente tras la aprobación.
                </div>
            @endif

            <!-- Información de la solicitud -->
            <div class="request-info">
                <h3>Detalles de la Solicitud</h3>
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; font-weight: bold; width: 30%;">Número:</td>
                        <td style="border: none;">#{{ $purchaseRequest->request_number }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; font-weight: bold;">Solicitante:</td>
                        <td style="border: none;">{{ $purchaseRequest->requester }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; font-weight: bold;">Sección/Área:</td>
                        <td style="border: none;">{{ $purchaseRequest->section_area }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; font-weight: bold;">Fecha:</td>
                        <td style="border: none;">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; font-weight: bold;">Tipo:</td>
                        <td style="border: none;">
                            Servicio sin Cotización <span class="status-badge badge-warning">SIN COTIZACIÓN</span>
                        </td>
                    </tr>
                    @if($purchaseRequest->coordinator)
                    <tr>
                        <td style="border: none; font-weight: bold;">Coordinador:</td>
                        <td style="border: none;">{{ $purchaseRequest->coordinator }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Información del proveedor -->
            <div class="provider-info">
                <h3><i>🏢</i> Información del Proveedor</h3>
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; font-weight: bold; width: 30%;">Proveedor:</td>
                        <td style="border: none;">{{ $purchaseRequest->provider_name }}</td>
                    </tr>
                    @if($purchaseRequest->provider_nit)
                    <tr>
                        <td style="border: none; font-weight: bold;">NIT:</td>
                        <td style="border: none;">{{ $purchaseRequest->provider_nit }}</td>
                    </tr>
                    @endif
                    @if($purchaseRequest->provider_contact)
                    <tr>
                        <td style="border: none; font-weight: bold;">Contacto:</td>
                        <td style="border: none;">{{ $purchaseRequest->provider_contact }}</td>
                    </tr>
                    @endif
                    @if($purchaseRequest->provider_email)
                    <tr>
                        <td style="border: none; font-weight: bold;">Email:</td>
                        <td style="border: none;">{{ $purchaseRequest->provider_email }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Justificación para no cotizar -->
            <div class="no-quotation-info">
                <h3><i>⚠</i> Justificación para no Cotizar</h3>
                <p><em>{{ $purchaseRequest->no_quotation_reason }}</em></p>
            </div>

            <!-- Servicios solicitados -->
            @if($purchaseRequest->service_items && count($purchaseRequest->service_items) > 0)
            <h3>Servicios Solicitados</h3>
            <table class="services-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Item</th>
                        <th style="width: 10%;">Cant.</th>
                        <th style="width: 60%;">Descripción</th>
                        <th style="width: 20%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequest->service_items as $service)
                        @if(!empty($service['description']))
                        <tr>
                            <td>{{ $service['item'] ?? '' }}</td>
                            <td>{{ $service['quantity'] ?? '' }}</td>
                            <td>{{ $service['description'] ?? '' }}</td>
                            <td>{{ $service['observations'] ?? '' }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            @endif

            <!-- Información presupuestaria -->
            @if($purchaseRequest->service_budget)
            <div class="request-info">
                <h3>Información Presupuestaria</h3>
                <p><strong>Valor Presupuestado:</strong> ${{ number_format($purchaseRequest->service_budget, 0, ',', '.') }}</p>
                @if($purchaseRequest->service_budget_text)
                <p><strong>En letras:</strong> {{ $purchaseRequest->service_budget_text }}</p>
                @endif
            </div>
            @endif

            <!-- Justificación del servicio -->
            <div class="request-info">
                <h3>Justificación del Servicio</h3>
                <p>{{ $purchaseRequest->service_justification }}</p>
            </div>

            <!-- Observaciones generales -->
            @if($purchaseRequest->general_observations)
            <div class="request-info">
                <h3>Observaciones Generales</h3>
                <p>{{ $purchaseRequest->general_observations }}</p>
            </div>
            @endif

            <!-- Botón de acción (solo para emails que no son de usuario) -->
            @if($emailType != 'user')
            <div style="text-align: center; margin: 30px 0;">
                @if($emailType == 'pre_approval')
                    <a href="{{ url('/quotation-approvals/' . $purchaseRequest->id) }}" class="action-button">
                        Revisar y Pre-aprobar
                    </a>
                @else
                    <a href="{{ url('/purchase-requests/' . $purchaseRequest->id) }}" class="action-button">
                        Ver Solicitud Completa
                    </a>
                @endif
            </div>
            @endif

            <!-- Mensaje final -->
            @if($emailType == 'user')
                <p><strong>¿Qué sigue?</strong></p>
                <p>Su solicitud será revisada por el área correspondiente y, al no requerir cotización, procederá directamente a la generación de la orden de compra tras la aprobación.</p>
                <p>Recibirá una notificación cuando su solicitud sea procesada.</p>
            @elseif($emailType == 'pre_approval')
                <p><strong>Próximos pasos:</strong></p>
                <ul>
                    <li>Revisar la justificación para no cotizar</li>
                    <li>Validar la información del proveedor</li>
                    <li>Aprobar o rechazar la solicitud</li>
                    <li>Una vez aprobada, se generará automáticamente la orden de compra</li>
                </ul>
            @else
                <p><strong>Información importante:</strong></p>
                <p>Esta solicitud no requiere proceso de cotización y procederá directamente a la aprobación final tras la pre-aprobación correspondiente.</p>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Colegio Victoria S.A.S.</strong></p>
            <p>Sistema de Gestión de Solicitudes</p>
            <p>Este es un mensaje automático, por favor no responder a este correo.</p>
        </div>
    </div>
</body>
</html>
