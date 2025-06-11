<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #364e76 0%, #293d5f 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            background: white;
        }
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin: 20px 0;
        }
        .info-table td {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-table th {
            padding: 12px;
            text-align: left;
            color: #364e76;
            font-weight: 600;
            width: 40%;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #364e76;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9em;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Nueva solicitud de préstamo para revisión</h1>
            </div>
            
            <div class="content">
                <p>Se ha registrado una nueva solicitud de préstamo que requiere su revisión:</p>

                <table class="info-table">
                    <tr>
                        <th>Solicitante:</th>
                        <td>{{ $loanRequest->full_name }}</td>
                    </tr>
                    <tr>
                        <th>Documento:</th>
                        <td>{{ $loanRequest->document_number }}</td>
                    </tr>
                    <tr>
                        <th>Cargo:</th>
                        <td>{{ $loanRequest->position }}</td>
                    </tr>
                    <tr>
                        <th>Monto Solicitado:</th>
                        <td>${{ number_format($loanRequest->amount, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Plan de Pago:</th>
                        <td>{{ $loanRequest->installments }} cuotas de ${{ number_format($loanRequest->installment_value, 2, ',', '.') }}</td>
                    </tr>
                </table>

                <div style="text-align: center;">
                    <a href="{{ route('loan-requests.edit', $loanRequest->id) }}" class="button" style="color: white !important;">
                        Revisar Solicitud
                    </a>
                </div>
            </div>

            <div class="footer">
                <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
                <p>© {{ date('Y') }} The Victoria School | Departamento de Recursos Humanos</p>
            </div>
        </div>
    </div>
</body>
</html>
