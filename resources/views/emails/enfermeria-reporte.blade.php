<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Enfermería</title>
    <style>
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #314569 0%, #4a6491 100%);
            color: #FEFEFE;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        
        .header-subtitle {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
            background-color: #ffffff;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #314569;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #314569;
            flex: 0 0 45%;
        }
        
        .info-value {
            color: #555;
            flex: 1;
            text-align: right;
        }
        
        .message {
            margin: 20px 0;
            line-height: 1.8;
            color: #555;
        }
        
        .attachment-notice {
            background-color: #e8f4f8;
            border: 2px dashed #314569;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        
        .attachment-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .attachment-text {
            color: #314569;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .attachment-subtext {
            color: #666;
            font-size: 13px;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #dee2e6;
        }
        
        .footer-logo {
            margin-bottom: 10px;
            font-weight: 700;
            color: #314569;
            font-size: 16px;
        }
        
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 25px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📋 Reporte de Enfermería</h2>
            <div class="header-subtitle">The Victoria School (TVS)</div>
        </div>
        
        <div class="content">
            <div class="greeting">
                Estimado/a <strong>{{ $recipientName }}</strong>,
            </div>
            
            <p class="message">
                Se adjunta el reporte de enfermería solicitado con la siguiente información:
            </p>
            
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">📊 Tipo de Reporte:</span>
                    <span class="info-value">{{ $reportType }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">📅 Período:</span>
                    <span class="info-value">{{ $dateRange }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">📈 Total de Registros:</span>
                    <span class="info-value"><strong>{{ $totalRecords }}</strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">🕐 Fecha de Generación:</span>
                    <span class="info-value">{{ date('d/m/Y H:i') }}</span>
                </div>
            </div>
            
            <div class="attachment-notice">
                <div class="attachment-icon">📎</div>
                <div class="attachment-text">Archivo Excel Adjunto</div>
                <div class="attachment-subtext">
                    El reporte completo está disponible en el archivo adjunto en formato Excel
                </div>
            </div>
            
            <div class="divider"></div>
            
            <p class="message" style="font-size: 14px; color: #6c757d;">
                <strong>Nota:</strong> Este reporte ha sido generado automáticamente desde el Sistema de Gestión de Enfermería. 
                Si tiene alguna pregunta o necesita información adicional, por favor contacte al área de enfermería.
            </p>
        </div>
        
        <div class="footer">
            <div class="footer-logo">The Victoria School (TVS)</div>
            <p style="margin: 5px 0;">Sistema de Gestión de Enfermería</p>
            <p style="margin: 5px 0; font-size: 12px;">
                Este es un correo automático, por favor no responder.
            </p>
        </div>
    </div>
</body>
</html>
