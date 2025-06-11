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
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #28a745;
        }
        .important-note {
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
            <h1 style="color: white !important; text-align: center !important;">Préstamo aprobado para desembolso</h1>
        </div>
        
        <div class="content">
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Beneficiario:</span>
                    <span>{{ $loanRequest->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Documento:</span>
                    <span>{{ $loanRequest->document_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cargo:</span>
                    <span>{{ $loanRequest->position }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Monto Aprobado:</span>
                    <span>${{ number_format($loanRequest->amount, 2, ',', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Plan de Descuentos:</span>
                    <span>{{ $loanRequest->installments }} cuotas de ${{ number_format($loanRequest->installment_value, 2, ',', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Inicio de Descuentos:</span>
                    <span>{{ $loanRequest->deduction_start_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="important-note">
                <p><strong>Información Importante:</strong></p>
                <p>Se adjunta el PDF con todos los detalles del préstamo y la revisión realizada por RRHH.</p>
                <p>Por favor proceder con el desembolso según las políticas establecidas.</p>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
            <p>© {{ date('Y') }} The Victoria School | Departamento de Contabilidad</p>
        </div>
    </div>
</body>
</html>
