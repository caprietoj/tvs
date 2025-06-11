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
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            background: white;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            color: #666;
            font-weight: 600;
        }
        .info-value {
            color: #333;
            font-weight: 500;
        }
        .notice {
            background: #e8f5e9;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
                <h1>Solicitud de Préstamo Recibida</h1>
            </div>
            
            <div class="content">
                <p>Estimado(a) {{ $loanRequest->full_name }},</p>
                
                <p>Su solicitud de préstamo ha sido recibida exitosamente. A continuación, encontrará los detalles:</p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Número de Solicitud:</span>
                        <span class="info-value">#{{ $loanRequest->id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Monto Solicitado:</span>
                        <span class="info-value">${{ number_format($loanRequest->amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Plan de Pago:</span>
                        <span class="info-value">{{ $loanRequest->installments }} cuotas</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Valor por Cuota:</span>
                        <span class="info-value">${{ number_format($loanRequest->installment_value, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="notice">
                    <p><strong>Siguiente paso:</strong></p>
                    <p>El departamento de Recursos Humanos revisará su solicitud y posteriormente será evaluada por el área Financiera. Le notificaremos cuando haya una actualización sobre el estado de su solicitud.</p>
                </div>
            </div>

            <div class="footer">
                <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
                <p>© {{ date('Y') }} The Victoria School</p>
            </div>
        </div>
    </div>
</body>
</html>
