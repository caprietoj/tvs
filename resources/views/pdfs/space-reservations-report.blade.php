<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Reservas de Espacios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            background: linear-gradient(135deg, #3c8dbc 0%, #367fa9 100%);
            color: white;
            padding: 25px 30px;
            margin-bottom: 25px;
            border-radius: 5px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header .subtitle {
            font-size: 13px;
            opacity: 0.95;
        }

        .info-section {
            background-color: #f8f9fa;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3c8dbc;
            border-radius: 3px;
        }

        .info-section .label {
            font-weight: 600;
            color: #495057;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-section .value {
            font-size: 12px;
            color: #212529;
            margin-top: 3px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            vertical-align: top;
        }

        .stat-card .stat-box {
            background: white;
            border-radius: 5px;
            padding: 15px 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            border-top: 3px solid;
        }

        .stat-card:nth-child(1) .stat-box { border-top-color: #3c8dbc; }
        .stat-card:nth-child(2) .stat-box { border-top-color: #00a65a; }
        .stat-card:nth-child(3) .stat-box { border-top-color: #f39c12; }
        .stat-card:nth-child(4) .stat-box { border-top-color: #dd4b39; }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card:nth-child(1) .stat-number { color: #3c8dbc; }
        .stat-card:nth-child(2) .stat-number { color: #00a65a; }
        .stat-card:nth-child(3) .stat-number { color: #f39c12; }
        .stat-card:nth-child(4) .stat-number { color: #dd4b39; }

        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #3c8dbc;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #3c8dbc;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .table thead {
            background: linear-gradient(to bottom, #3c8dbc 0%, #367fa9 100%);
            color: white;
        }

        .table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 10px;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e9ecef;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background-color: #00a65a;
            color: white;
        }

        .badge-warning {
            background-color: #f39c12;
            color: white;
        }

        .badge-danger {
            background-color: #dd4b39;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        .footer .institution {
            font-weight: 600;
            color: #3c8dbc;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .space-group {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .space-group-header {
            background: linear-gradient(to right, #ecf0f5 0%, #ffffff 100%);
            padding: 10px 15px;
            margin-bottom: 10px;
            border-left: 4px solid #3c8dbc;
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }

        @page {
            margin: 20mm 15mm;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Reporte de Reservas de Espacios</h1>
        <div class="subtitle">
            Sistema de Gestión Institucional - The Victoria School
        </div>
    </div>

    <div class="info-section">
        <table width="100%">
            <tr>
                <td width="50%">
                    <div class="label">Fecha de Generación</div>
                    <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
                </td>
                <td width="50%">
                    <div class="label">Generado por</div>
                    <div class="value">{{ auth()->user()->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-box">
                <div class="stat-number">{{ $totalReservations }}</div>
                <div class="stat-label">Total Reservas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-box">
                <div class="stat-number">{{ $approvedReservations }}</div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-box">
                <div class="stat-number">{{ $pendingReservations }}</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-box">
                <div class="stat-number">{{ $rejectedReservations }}</div>
                <div class="stat-label">Rechazadas</div>
            </div>
        </div>
    </div>

    @if($reservations->isEmpty())
        <div class="no-data">
            <p>No se encontraron reservas con los criterios especificados.</p>
        </div>
    @else
        <div class="section-title">Detalle de Reservas</div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 20%">Espacio</th>
                    <th style="width: 12%">Fecha</th>
                    <th style="width: 15%">Horario</th>
                    <th style="width: 18%">Solicitante</th>
                    <th style="width: 25%">Propósito</th>
                    <th style="width: 10%">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                <tr>
                    <td><strong>{{ $reservation->space->name }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</td>
                    <td>{{ $reservation->user->name }}</td>
                    <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($reservation->purpose, 60) }}</td>
                    <td>
                        @if($reservation->status == 'approved')
                            <span class="badge badge-success">Aprobada</span>
                        @elseif($reservation->status == 'pending')
                            <span class="badge badge-warning">Pendiente</span>
                        @elseif($reservation->status == 'rejected')
                            <span class="badge badge-danger">Rechazada</span>
                        @else
                            <span class="badge badge-secondary">Cancelada</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($reservationsBySpace->count() > 1)
        <div class="page-break"></div>
        <div class="section-title">Resumen por Espacio</div>

        @foreach($reservationsBySpace as $spaceName => $spaceReservations)
        <div class="space-group">
            <div class="space-group-header">
                📍 {{ $spaceName }} ({{ $spaceReservations->count() }} reserva{{ $spaceReservations->count() != 1 ? 's' : '' }})
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 20%">Horario</th>
                        <th style="width: 25%">Solicitante</th>
                        <th style="width: 30%">Propósito</th>
                        <th style="width: 10%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($spaceReservations as $reservation)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</td>
                        <td>{{ $reservation->user->name }}</td>
                        <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($reservation->purpose, 50) }}</td>
                        <td>
                            @if($reservation->status == 'approved')
                                <span class="badge badge-success">Aprobada</span>
                            @elseif($reservation->status == 'pending')
                                <span class="badge badge-warning">Pendiente</span>
                            @elseif($reservation->status == 'rejected')
                                <span class="badge badge-danger">Rechazada</span>
                            @else
                                <span class="badge badge-secondary">Cancelada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @endif
    @endif

    <div class="footer">
        <div class="institution">The Victoria School - Sistema de Gestión Institucional</div>
        <div>Este documento fue generado automáticamente el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}</div>
        <div style="margin-top: 5px;">Reporte confidencial - Para uso interno únicamente</div>
    </div>
</body>
</html>
