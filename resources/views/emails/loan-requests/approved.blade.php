<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #28a745;
            display: block;
            margin-bottom: 5px;
        }
        .next-steps {
            background: #e8f5e9;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: white !important; text-align: center !important;">¡Su Préstamo ha sido Aprobado!</h1>
        </div>
        
        <div class="content">
            <p>Estimado(a) {{ $loanRequest->full_name }},</p>
            
            <p>Nos complace informarle que su solicitud de préstamo ha sido aprobada. A continuación encontrará los detalles:</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Monto Aprobado:</span>
                    ${{ number_format($loanRequest->amount, 2, ',', '.') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Plan de Pago:</span>
                    {{ $loanRequest->installments }} cuotas de ${{ number_format($loanRequest->installment_value, 2, ',', '.') }}
                </div>
                <div class="info-row">
                    <span class="info-label">Inicio de Descuentos:</span>
                    {{ $loanRequest->deduction_start_date->format('d/m/Y') }}
                </div>
            </div>

            <div class="next-steps">
                <h4 style="margin-top: 0; color: #28a745;">Próximos Pasos:</h4>
                <p>El departamento de Contabilidad procederá con el desembolso en los próximos días hábiles.</p>
                <p>Los descuentos comenzarán a partir de la fecha indicada anteriormente.</p>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
            <p>© {{ date('Y') }} The Victoria School</p>
        </div>
    </div>
</body>
</html>
