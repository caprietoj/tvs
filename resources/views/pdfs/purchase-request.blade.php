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
@php
function convertirNumeroALetras($numero) {
    $numero = round($numero);
    if ($numero == 0) return 'cero pesos';
    
    $unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    $especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    $decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
    
    function convertirGrupo($n, $unidades, $especiales, $decenas, $centenas) {
        if ($n == 0) return '';
        if ($n == 100) return 'cien';
        
        $resultado = '';
        
        if ($n >= 100) {
            $c = intval($n / 100);
            $resultado .= $centenas[$c];
            $n %= 100;
            if ($n > 0) $resultado .= ' ';
        }
        
        if ($n >= 20) {
            $d = intval($n / 10);
            $resultado .= $decenas[$d];
            $n %= 10;
            if ($n > 0) {
                $resultado .= ' y ' . $unidades[$n];
            }
        } elseif ($n >= 10) {
            $resultado .= $especiales[$n - 10];
        } elseif ($n > 0) {
            $resultado .= $unidades[$n];
        }
        
        return $resultado;
    }
    
    $resultado = '';
    
    if ($numero >= 1000000) {
        $millones = intval($numero / 1000000);
        if ($millones == 1) {
            $resultado .= 'un millón';
        } else {
            $resultado .= convertirGrupo($millones, $unidades, $especiales, $decenas, $centenas) . ' millones';
        }
        $numero %= 1000000;
        if ($numero > 0) $resultado .= ' ';
    }
    
    if ($numero >= 1000) {
        $miles = intval($numero / 1000);
        if ($miles == 1) {
            $resultado .= 'mil';
        } else {
            $resultado .= convertirGrupo($miles, $unidades, $especiales, $decenas, $centenas) . ' mil';
        }
        $numero %= 1000;
        if ($numero > 0) $resultado .= ' ';
    }
    
    if ($numero > 0) {
        $resultado .= convertirGrupo($numero, $unidades, $especiales, $decenas, $centenas);
    }
    
    if ($resultado == '') {
        $resultado = 'cero';
    }
    
    if (round($numero) == 1) {
        $resultado .= ' peso';
    } else {
        $resultado .= ' pesos';
    }
    
    return ucfirst($resultado);
}
@endphp

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

    <!-- Información de Impuestos y Totales -->
    @if($purchaseRequest->service_type === 'no_quotation' && ($purchaseRequest->applied_taxes || $purchaseRequest->subtotal_amount || $purchaseRequest->tax_amount || $purchaseRequest->total_amount))
    <div class="section">
        <div class="section-title">Información de Impuestos y Totales</div>
        
        @if($purchaseRequest->subtotal_amount)
        <div class="info-block">
            <span class="label">Subtotal:</span>
            ${{ number_format($purchaseRequest->subtotal_amount, 2, ',', '.') }}
        </div>
        @endif

        @if($purchaseRequest->applied_taxes && count($purchaseRequest->applied_taxes) > 0)
        <div class="info-block">
            <span class="label">Impuestos Aplicados:</span>
            <div style="margin-left: 150px;">
                @foreach($purchaseRequest->applied_taxes as $tax)
                    <div style="margin-bottom: 5px;">
                        @if($tax === 'iva_19')
                            IVA 19%
                        @elseif($tax === 'iva_5')
                            IVA 5%
                        @elseif($tax === 'consumo_8')
                            Impuesto al Consumo 8%
                        @elseif($tax === 'consumo_4')
                            Impuesto al Consumo 4%
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($purchaseRequest->tax_amount)
        <div class="info-block">
            <span class="label">Total Impuestos:</span>
            ${{ number_format($purchaseRequest->tax_amount, 2, ',', '.') }}
        </div>
        @endif

        @if($purchaseRequest->total_amount)
        <div class="info-block" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
            <span class="label" style="font-size: 14px; color: #2c5282;">TOTAL GENERAL:</span>
            <span style="font-size: 14px; font-weight: bold; color: #2c5282;">${{ number_format($purchaseRequest->total_amount, 2, ',', '.') }}</span>
        </div>
        <div class="info-block" style="margin-top: 10px;">
            <span class="label" style="font-size: 14px; color: #2c5282;">Valor Total en Letras:</span>
            <span style="font-size: 12px; font-weight: bold; color: #2c5282; text-transform: uppercase;">{{ convertirNumeroALetras($purchaseRequest->total_amount) }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Información de Presupuesto (para servicios regulares sin cálculo de impuestos) -->
    @if($purchaseRequest->type === 'services' && $purchaseRequest->service_type !== 'no_quotation' && $purchaseRequest->service_budget)
    <div class="section">
        <div class="section-title">Información de Presupuesto</div>
        
        <div class="info-block" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
            <span class="label" style="font-size: 14px; color: #2c5282;">VALOR PRESUPUESTADO:</span>
            <span style="font-size: 14px; font-weight: bold; color: #2c5282;">${{ number_format($purchaseRequest->service_budget, 2, ',', '.') }}</span>
        </div>
        <div class="info-block" style="margin-top: 10px;">
            <span class="label" style="font-size: 14px; color: #2c5282;">Valor Total en Letras:</span>
            <span style="font-size: 12px; font-weight: bold; color: #2c5282; text-transform: uppercase;">{{ convertirNumeroALetras($purchaseRequest->service_budget) }}</span>
        </div>
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

        @if($purchaseRequest->approved_at && $purchaseRequest->approvedBy)
        <div class="info-block">
            <span class="label">Aprobado por:</span>
            {{ $purchaseRequest->approvedBy->name }}
        </div>
        @endif

        @if($purchaseRequest->service_type === 'no_quotation' && $purchaseRequest->budget && $purchaseRequest->approved_at)
        <div class="info-block">
            <span class="label">Presupuesto Asignado:</span>
            <span style="font-weight: bold; color: #2c5282;">{{ $purchaseRequest->budget }}</span>
        </div>
        @endif

        @if($purchaseRequest->rejected_at)
        <div class="info-block">
            <span class="label">Fecha de Rechazo:</span>
            {{ \Carbon\Carbon::parse($purchaseRequest->rejected_at)->format('d/m/Y H:i:s') }}
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Compras de TVS</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Solicitud #{{ $purchaseRequest->id }} - {{ $purchaseRequest->status === 'approved' ? 'DOCUMENTO OFICIAL' : 'DOCUMENTO PRELIMINAR' }}</p>
    </div>
</body>
</html>
