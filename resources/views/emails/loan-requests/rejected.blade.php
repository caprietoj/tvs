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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .rejection-box {
            background: #fff5f5;
            border: 1px solid #dc3545;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .rejection-title {
            color: #dc3545;
            font-weight: 600;
            margin-bottom: 10px;
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
            <h1 style="color: white !important; text-align: center !important;">Solicitud de Préstamo No Aprobada</h1>
        </div>
        
        <div class="content">
            <p>Estimado(a) {{ $loanRequest->full_name }},</p>
            
            <p>Lamentamos informarle que su solicitud de préstamo no ha sido aprobada.</p>

            <div class="rejection-box">
                <div class="rejection-title">Motivo del rechazo:</div>
                <p>{{ $loanRequest->rejection_reason }}</p>
            </div>

            <p>Si tiene alguna duda o requiere más información, por favor comuníquese con el departamento de Recursos Humanos.</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de préstamos.</p>
            <p>© {{ date('Y') }} The Victoria School</p>
        </div>
    </div>
</body>
</html>
