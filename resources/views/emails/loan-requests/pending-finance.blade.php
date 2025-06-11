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
            background: linear-gradient(135deg, #1a4f98 0%, #0d2b50 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
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
            color: #1a4f98;
            font-weight: 600;
            width: 40%;
        }
        .action-button {
            display: inline-block;
            background: #1a4f98;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 style="color: white !important; text-align: center !important;">Solicitud de préstamo lista para revisión financiera</h1>
            </div>
            
            <div class="content">
                <p>Una solicitud de préstamo ha sido revisada por RRHH y requiere su autorización:</p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Solicitante:</span>
                        <span>{{ $loanRequest->full_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Documento:</span>
                        <span>{{ $loanRequest->document_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Monto:</span>
                        <span>${{ number_format($loanRequest->amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Salario Actual:</span>
                        <span>${{ number_format($loanRequest->current_salary, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Revisado por:</span>
                        <span>{{ $loanRequest->hr_signature }}</span>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="{{ route('loan-requests.show', $loanRequest->id) }}" class="action-button">
                        Revisar y Autorizar
                    </a>
                </div>
            </div>

            <div class="footer">
                <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
                <p>© {{ date('Y') }} The Victoria School | Departamento Financiero</p>
            </div>
        </div>
    </div>
</body>
</html>
