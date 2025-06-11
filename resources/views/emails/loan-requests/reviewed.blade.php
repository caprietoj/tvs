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
        .info-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .review-box {
            background: #17a2b8;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
        .hr-signature {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 15px;
            border-radius: 4px;
            font-style: italic;
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
            <h1>Solicitud de Préstamo Revisada por RRHH</h1>
        </div>
        
        <div class="content">
            <p>Cordial saludo,</p>
            <p>Le informamos que la solicitud de préstamo ha sido revisada por el Departamento de Recursos Humanos y está lista para aprobación final.</p>

            <table class="info-table">
                <tr>
                    <th>Número de Solicitud:</th>
                    <td>{{ $loanRequest->id }}</td>
                </tr>
                <tr>
                    <th>Solicitante:</th>
                    <td>{{ $loanRequest->full_name }}</td>
                </tr>
                <tr>
                    <th>Salario Actual:</th>
                    <td>${{ number_format($loanRequest->current_salary, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Préstamos Activos:</th>
                    <td>{{ $loanRequest->has_active_loans ? 'Sí' : 'No' }}</td>
                </tr>
                @if($loanRequest->has_active_loans)
                <tr>
                    <th>Saldo Préstamos:</th>
                    <td>${{ number_format($loanRequest->current_loan_balance, 2, ',', '.') }}</td>
                </tr>
                @endif
            </table>

            <div class="review-info">
                <p><strong>Revisado por:</strong> {{ $loanRequest->hr_signature }}</p>
                <p><strong>Fecha de Revisión:</strong> {{ $loanRequest->review_date->format('d/m/Y H:i') }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('loan-requests.show', $loanRequest->id) }}" class="button">
                    Ver Solicitud Completa
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
