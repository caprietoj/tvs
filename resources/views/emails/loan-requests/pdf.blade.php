<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            size: letter;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 10px;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .header-text {
            text-transform: uppercase;
            font-weight: bold;
            margin: 2px 0;
            font-size: 12px;
        }

        .address-section {
            margin: 10px 0;
            font-size: 13px;
            line-height: 1.5;
        }

        .address-section p {
            margin: 2px 0;
        }

        .letter-content {
            margin: 10px 0;
            text-align: justify;
            font-size: 13px;
            line-height: 1.5;
        }

        .letter-content p {
            margin: 5px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .main-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .signature-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .signature-cell {
            height: 40px;
            position: relative;
        }

        .signature-line {
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            border-bottom: 1px solid #000;
        }

        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .approval-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .checkbox-group {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
            margin: 0 3px;
            position: relative;
        }

        .checkbox.checked::after {
            content: 'X';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px;
            font-weight: bold;
        }

        .date-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .date-table td, .date-table th {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            width: 25%;
        }

        .date-table th {
            background-color: #f0f0f0;
            font-size: 11px;
        }

        .authorization-section {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .signature-box {
            margin: 10px 0;
            padding: 5px;
            border: 1px solid #000;
        }

        .signature-box p {
            margin: 2px 0;
        }

        .signature-box img {
            max-width: 150px;
            max-height: 40px;
            margin: 5px 0;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .footer-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }

        .section-title {
            background-color: #f0f0f0;
            padding: 5px;
            margin: 10px 0;
            font-weight: bold;
            font-size: 11px;
        }

        .money {
            font-weight: bold;
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-before: always;
            }

            img {
                max-height: 40px !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="header-text">GESTIÓN ADMINISTRATIVA</p>
        <p class="header-text">FORMATO SOLICITUD DE PRÉSTAMOS / ANTICIPOS</p>
        <p class="header-text">COLEGIO VICTORIA SAS</p>
        <p class="header-text">RECURSOS HUMANOS</p>
    </div>

    <div class="address-section">
        <p>Señores</p>
        <p>COLEGIO VICTORIA S.A.S</p>
        <p>Atn: Juliana Pérez López</p>
        <p>Ciudad</p>
    </div>

    <div class="letter-content">
        <p>Por medio de la presente les solicito se sirvan concederme un: <strong>Préstamo</strong> por la suma de:</p>
        <p class="money">${{ number_format($loanRequest->amount, 2, ',', '.') }} pesos m/cte.</p>

        <p>De igual manera <strong>autorizo me sean descontados, en {{ $loanRequest->installments }} cuotas 
        {{ $loanRequest->installment_type == 'monthly' ? 'mensuales' : 'quincenales' }} 
        de ${{ number_format($loanRequest->installment_value, 2, ',', '.') }} 
        a partir del {{ $loanRequest->deduction_start_date->format('d/m/Y') }}</strong> y en caso de que mi contrato 
        se dé por terminado estando vigente este préstamo, autorizo me sea descontado el saldo pendiente 
        de esta obligación adquirida con la institución de mi liquidación del contrato de trabajo, es decir 
        de cesantías, intereses, vacaciones, prima de servicios y/o cualquier otro pago a que tenga derecho.</p>
    </div>

    <table class="signature-table">
        <tr>
            <td colspan="2"><strong>Cordialmente,</strong></td>
        </tr>
        <tr>
            <td width="50%">
                <strong>Nombre:</strong> {{ $loanRequest->full_name }}
            </td>
            <td width="50%">
                <strong>C.C. Nº:</strong> {{ $loanRequest->document_number }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Cargo:</strong> {{ $loanRequest->position }}
            </td>
            <td class="signature-cell">
                <strong>Firma:</strong>
                @if($loanRequest->signature)
                    <img src="{{ storage_path('app/public/' . $loanRequest->signature) }}" 
                         alt="Firma del solicitante" 
                         style="max-width: 150px; max-height: 40px;">
                @else
                    <div class="signature-line"></div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Uso exclusivo para aprobación</div>

    <table class="approval-table">
        <tr>
            <td width="50%"><strong>Sueldo actual:</strong></td>
            <td>${{ number_format($loanRequest->current_salary ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>
                <strong>¿Tiene préstamos actualmente?</strong>
                <div class="checkbox-group">
                    <span>SI</span>
                    <div class="checkbox {{ $loanRequest->has_active_loans ? 'checked' : '' }}"></div>
                    <span>NO</span>
                    <div class="checkbox {{ !$loanRequest->has_active_loans ? 'checked' : '' }}"></div>
                </div>
            </td>
            <td><strong>Saldo a la fecha:</strong> ${{ number_format($loanRequest->current_loan_balance ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>
                <strong>¿Tiene anticipos en el último año?</strong>
                <div class="checkbox-group">
                    <span>SI</span>
                    <div class="checkbox {{ $loanRequest->has_advances ? 'checked' : '' }}"></div>
                    <span>NO</span>
                    <div class="checkbox {{ !$loanRequest->has_advances ? 'checked' : '' }}"></div>
                </div>
            </td>
            <td><strong>Valor:</strong> ${{ number_format($loanRequest->advances_amount ?? 0, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="date-table">
        <tr>
            <th>Año</th>
            <th>Mes</th>
            <th>Día</th>
            <th>Se aprueba</th>
        </tr>
        <tr>
            <td>{{ $loanRequest->review_date ? $loanRequest->review_date->format('Y') : now()->format('Y') }}</td>
            <td>{{ $loanRequest->review_date ? $loanRequest->review_date->format('m') : now()->format('m') }}</td>
            <td>{{ $loanRequest->review_date ? $loanRequest->review_date->format('d') : now()->format('d') }}</td>
            <td>
                <div class="checkbox-group">
                    <span>SI</span>
                    <div class="checkbox {{ $loanRequest->status == 'approved' ? 'checked' : '' }}"></div>
                    <span>NO</span>
                    <div class="checkbox {{ $loanRequest->status == 'rejected' ? 'checked' : '' }}"></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="authorization-section">
        <div class="signature-box">
            <p><strong>Vo Bo Recursos Humanos:</strong> Luz Vargas Sáenz</p>
            @if($loanRequest->hr_signature)
                <img src="{{ storage_path('app/public/' . $loanRequest->hr_signature) }}" 
                     alt="Firma RRHH" 
                     style="max-width: 150px; max-height: 40px;">
            @else
                <div class="signature-line"></div>
            @endif
        </div>

        <div class="signature-box">
            <p><strong>Firma autorizada:</strong> Juliana Pérez López</p>
            @if($loanRequest->admin_signature)
                <img src="{{ storage_path('app/public/' . $loanRequest->admin_signature) }}" 
                     alt="Firma Autorización" 
                     style="max-width: 150px; max-height: 40px;">
            @else
                <div class="signature-line"></div>
            @endif
        </div>
    </div>

    <table class="footer-table">
        <tr>
            <td>Estado del documento</td>
            <td>Instancia aprobatoria</td>
            <td>Fecha de control de cambios</td>
            <td>Versión del documento</td>
        </tr>
        <tr>
            <td>Documento aprobado.</td>
            <td>Vicerrectoría administrativa</td>
            <td>Agosto 2024.</td>
            <td>V1.</td>
        </tr>
    </table>
</body>
</html>