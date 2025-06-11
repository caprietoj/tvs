<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #364e76;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
        }
        .status-box {
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            color: white;
        }
        .status-approved {
            background: #28a745;
        }
        .status-rejected {
            background: #dc3545;
        }
        .info-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .details {
            margin: 15px 0;
        }
        .details-row {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .details-label {
            font-weight: bold;
            color: #364e76;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #364e76;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/logo.png') }}" alt="TVS Logo">
            <h1>Resultado de Solicitud de Préstamo</h1>
        </div>
        
        <div class="content">
            <p>Cordial saludo,</p>
            
            @if($loanRequest->status === 'approved')
                <div class="alert-success">
                    <h2>¡Solicitud Aprobada!</h2>
                    <p>Nos complace informarle que su solicitud de préstamo ha sido aprobada.</p>
                </div>
            @else
                <div class="alert-danger">
                    <h2>Solicitud No Aprobada</h2>
                    <p>Lamentamos informarle que su solicitud de préstamo no ha sido aprobada.</p>
                    @if($loanRequest->rejection_reason)
                        <p><strong>Motivo:</strong> {{ $loanRequest->rejection_reason }}</p>
                    @endif
                </div>
            @endif

            <table class="info-table">
                <tr>
                    <th>Número de Solicitud:</th>
                    <td>{{ $loanRequest->id }}</td>
                </tr>
                <tr>
                    <th>Monto Solicitado:</th>
                    <td>${{ number_format($loanRequest->amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Fecha de Decisión:</th>
                    <td>{{ $loanRequest->decision_date->format('d/m/Y H:i') }}</td>
                </tr>
            </table>

            <div style="text-align: center;">
                <a href="{{ route('loan-requests.show', $loanRequest->id) }}" class="button">
                    Ver Detalles de la Solicitud
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
            <p>© {{ date('Y') }} The Victoria School | Departamento de Recursos Humanos</p>
        </div>
    </div>
</body>
</html>
