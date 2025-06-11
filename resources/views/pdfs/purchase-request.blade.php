<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Compra #{{ $purchaseRequest->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        
        .header {
            border-bottom: 2px solid #aaa;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo {
            max-width: 200px;
            max-height: 60px;
        }
        
        .request-title {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .request-number {
            font-size: 16px;
            font-weight: bold;
            color: #555;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            color: #2c5282;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .info-block {
            margin-bottom: 15px;
        }
        
        .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 50px auto 10px;
            text-align: center;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .priority-high {
            color: #dc2626;
            font-weight: bold;
        }

        .priority-medium {
            color: #f59e0b;
            font-weight: bold;
        }

        .priority-low {
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1 style="margin: 0; color: #2c5282;">TVS - Tecnológico del Valle del Sarchi</h1>
            <p style="margin: 5px 0; color: #666;">Sistema de Gestión de Compras</p>
        </div>
        <div class="request-title">SOLICITUD DE COMPRA</div>
        <div class="request-number">#{{ $purchaseRequest->id }}</div>
    </div>

    <!-- Información General -->
    <div class="section">
        <div class="section-title">Información General</div>
        
        <div class="info-block">
            <span class="label">Fecha de Solicitud:</span>
            {{ $purchaseRequest->created_at->format('d/m/Y H:i:s') }}
        </div>
        
        <div class="info-block">
            <span class="label">Solicitante:</span>
            {{ $user->name }} ({{ $user->email }})
        </div>
        
        <div class="info-block">
            <span class="label">Estado:</span>
            <span class="status-badge status-{{ $purchaseRequest->status }}">
                @switch($purchaseRequest->status)
                    @case('pending')
                        PENDIENTE
                        @break
                    @case('approved')
                        APROBADA
                        @break
                    @case('rejected')
                        RECHAZADA
                        @break
                    @default
                        {{ strtoupper($purchaseRequest->status) }}
                @endswitch
            </span>
        </div>
        
        @if($purchaseRequest->priority)
        <div class="info-block">
            <span class="label">Prioridad:</span>
            <span class="priority-{{ $purchaseRequest->priority }}">
                @switch($purchaseRequest->priority)
                    @case('high')
                        ALTA
                        @break
                    @case('medium')
                        MEDIA
                        @break
                    @case('low')
                        BAJA
                        @break
                    @default
                        {{ strtoupper($purchaseRequest->priority) }}
                @endswitch
            </span>
        </div>
        @endif

        @if($purchaseRequest->department)
        <div class="info-block">
            <span class="label">Departamento:</span>
            {{ $purchaseRequest->department }}
        </div>
        @endif

        @if($purchaseRequest->budget_code)
        <div class="info-block">
            <span class="label">Código Presupuestario:</span>
            {{ $purchaseRequest->budget_code }}
        </div>
        @endif
    </div>

    <!-- Descripción de la Solicitud -->
    @if($purchaseRequest->description)
    <div class="section">
        <div class="section-title">Descripción de la Solicitud</div>
        <p style="text-align: justify; line-height: 1.4;">{{ $purchaseRequest->description }}</p>
    </div>
    @endif

    <!-- Justificación -->
    @if($purchaseRequest->justification)
    <div class="section">
        <div class="section-title">Justificación</div>
        <p style="text-align: justify; line-height: 1.4;">{{ $purchaseRequest->justification }}</p>
    </div>
    @endif

    <!-- Artículos Solicitados -->
    @if(!empty($items) && count($items) > 0)
    <div class="section">
        <div class="section-title">Artículos Solicitados</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 15%; text-align: center;">Cantidad</th>
                    <th style="width: 15%; text-align: center;">Unidad</th>
                    <th style="width: 30%;">Especificaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item['quantity'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item['unit'] ?? 'N/A' }}</td>
                    <td>{{ $item['specifications'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Observaciones o Comentarios -->
    @if($purchaseRequest->comments)
    <div class="section">
        <div class="section-title">Observaciones</div>
        <p style="text-align: justify; line-height: 1.4;">{{ $purchaseRequest->comments }}</p>
    </div>
    @endif

    <!-- Fechas Importantes -->
    <div class="section">
        <div class="section-title">Fechas Importantes</div>
        
        @if($purchaseRequest->required_date)
        <div class="info-block">
            <span class="label">Fecha Requerida:</span>
            {{ \Carbon\Carbon::parse($purchaseRequest->required_date)->format('d/m/Y') }}
        </div>
        @endif

        @if($purchaseRequest->approved_at)
        <div class="info-block">
            <span class="label">Fecha de Aprobación:</span>
            {{ \Carbon\Carbon::parse($purchaseRequest->approved_at)->format('d/m/Y H:i:s') }}
        </div>
        @endif

        @if($purchaseRequest->rejected_at)
        <div class="info-block">
            <span class="label">Fecha de Rechazo:</span>
            {{ \Carbon\Carbon::parse($purchaseRequest->rejected_at)->format('d/m/Y H:i:s') }}
        </div>
        @endif
    </div>

    <!-- Sección de Firmas -->
    <div class="signature-section">
        <div class="section-title">Firmas y Autorizaciones</div>
        
        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 50%; text-align: center; border: none;">
                    <div class="signature-line"></div>
                    <strong>{{ $user->name }}</strong><br>
                    <small>Solicitante</small><br>
                    <small>Fecha: {{ $purchaseRequest->created_at->format('d/m/Y') }}</small>
                </td>
                <td style="width: 50%; text-align: center; border: none;">
                    <div class="signature-line"></div>
                    <strong>Autorizado por</strong><br>
                    <small>Jefe de Compras</small><br>
                    @if($purchaseRequest->approved_at)
                        <small>Fecha: {{ \Carbon\Carbon::parse($purchaseRequest->approved_at)->format('d/m/Y') }}</small>
                    @else
                        <small>Fecha: _______________</small>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Compras de TVS</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Solicitud #{{ $purchaseRequest->id }} - {{ $purchaseRequest->status === 'approved' ? 'DOCUMENTO OFICIAL' : 'DOCUMENTO PRELIMINAR' }}</p>
    </div>
</body>
</html>
