<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Ejecutivo - Sistema de Asistencia | {{ $mesTexto }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.5;
            color: #333333;
            background-color: #f5f5f5;
        }
        
        .document {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #FFFFFF;
            border: 1px solid #e0e0e0;
        }
        
        .header {
            background-color: #304468;
            color: #FFFFFF;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .header h2 {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        .info-item {
            text-align: center;
            flex: 1;
        }
        
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 18px;
            color: #304468;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #304468;
            font-weight: 600;
        }
        
        .executive-summary {
            background-color: #f8f9fa;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #304468;
        }
        
        .summary-text {
            font-size: 14px;
            line-height: 1.7;
            color: #555555;
            text-align: justify;
        }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .kpi-card {
            background-color: #FFFFFF;
            border: 1px solid #e0e0e0;
            padding: 20px;
        }
        
        .kpi-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .kpi-icon {
            font-size: 20px;
            margin-right: 10px;
            color: #304468;
        }
        
        .kpi-title {
            font-size: 12px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .kpi-value {
            font-size: 32px;
            font-weight: 700;
            color: #304468;
            margin-bottom: 5px;
        }
        
        .kpi-description {
            font-size: 11px;
            color: #888888;
        }
        
        .insights-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .insight-card {
            background-color: #FFFFFF;
            border: 1px solid #e0e0e0;
            padding: 20px;
        }
        
        .insight-title {
            font-size: 14px;
            color: #304468;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .insight-text {
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
        }
        
        .table-section {
            margin-bottom: 30px;
        }
        
        .table-header {
            background-color: #304468;
            color: #FFFFFF;
            padding: 15px 20px;
            text-align: center;
        }
        
        .table-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .table-subtitle {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .table-container {
            border: 1px solid #e0e0e0;
            background-color: #FFFFFF;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #304468;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .badge {
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 2px;
        }
        
        .badge-success {
            background-color: #e8f5e8;
            color: #2d5016;
            border: 1px solid #c3e6c3;
        }
        
        .badge-warning {
            background-color: #fff8e1;
            color: #e65100;
            border: 1px solid #ffcc02;
        }
        
        .badge-danger {
            background-color: #fdeaea;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge-info {
            background-color: #e1f5fe;
            color: #01579b;
            border: 1px solid #b3e5fc;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }
        
        .pagination-button {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            background-color: #FFFFFF;
            color: #304468;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }
        
        .pagination-button:hover {
            background-color: #f5f5f5;
        }
        
        .pagination-button.active {
            background-color: #304468;
            color: #FFFFFF;
            border-color: #304468;
        }
        
        .pagination-info {
            font-size: 12px;
            color: #666666;
            margin: 0 15px;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .footer-logo {
            font-size: 16px;
            color: #304468;
            font-weight: 700;
        }
        
        .footer-info {
            text-align: right;
            font-size: 12px;
            color: #666666;
        }
        
        .footer-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .footer-stat {
            text-align: center;
            padding: 15px;
            background-color: #FFFFFF;
            border: 1px solid #e0e0e0;
        }
        
        .footer-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #304468;
            margin-bottom: 5px;
        }
        
        .footer-stat-label {
            font-size: 11px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .signature {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666666;
            font-size: 11px;
        }
        
        /* Paginación para tabla */
        .table-pagination {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .document {
                margin: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .header-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .insights-section {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .footer-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .kpi-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media print {
            body {
                background-color: #FFFFFF;
            }
            
            .document {
                margin: 0;
                border: none;
            }
            
            .pagination, .table-pagination {
                display: none;
            }
            
            .header {
                background-color: #304468 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="document">
        <!-- Header institucional -->
        <div class="header">
            <h1>INFORME EJECUTIVO</h1>
            <h2>Sistema de Control de Asistencia Biométrica</h2>
            
            <div class="header-info">
                <div class="info-item">
                    <div class="info-label">Período de Análisis</div>
                    <div class="info-value">{{ $mesTexto }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total de Registros</div>
                    <div class="info-value">{{ number_format($attendanceStats['total_registros']) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Generación</div>
                    <div class="info-value">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Resumen Ejecutivo -->
            <div class="section">
                <h2 class="section-title">RESUMEN EJECUTIVO</h2>
                <div class="executive-summary">
                    <p class="summary-text">
                        Durante el mes de <strong>{{ $mesTexto }}</strong>, el sistema de control biométrico registró 
                        un total de <strong>{{ number_format($attendanceStats['total_registros']) }} marcaciones</strong> 
                        correspondientes a <strong>{{ number_format($attendanceStats['total_empleados']) }} empleados únicos</strong> 
                        a lo largo de <strong>{{ number_format($attendanceStats['total_dias']) }} días laborales</strong>.
                        
                        El promedio de asistencia alcanzó un <strong>{{ $attendanceStats['promedio_asistencia'] }}%</strong>, 
                        con <strong>{{ number_format($attendanceStats['registros_puntuales']) }} marcaciones puntuales</strong> 
                        y <strong>{{ number_format($attendanceStats['llegadas_tarde']) }} llegadas tarde</strong>.
                        
                        El promedio diario de marcaciones fue de <strong>{{ $attendanceStats['promedio_diario'] }} registros</strong>, 
                        reflejando un comportamiento consistente en el cumplimiento de horarios laborales.
                    </p>
                </div>
            </div>

            <!-- Indicadores Clave -->
            <div class="section">
                <h2 class="section-title">INDICADORES CLAVE DE RENDIMIENTO</h2>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">📊</div>
                            <div class="kpi-title">Total de Registros</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['total_registros']) }}</div>
                        <div class="kpi-description">Marcaciones biométricas registradas</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">👥</div>
                            <div class="kpi-title">Empleados Únicos</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['total_empleados']) }}</div>
                        <div class="kpi-description">Personal registrado en el sistema</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">✅</div>
                            <div class="kpi-title">Asistencia Promedio</div>
                        </div>
                        <div class="kpi-value">{{ $attendanceStats['promedio_asistencia'] }}%</div>
                        <div class="kpi-description">Porcentaje de cumplimiento</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">⏰</div>
                            <div class="kpi-title">Llegadas Puntuales</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['registros_puntuales']) }}</div>
                        <div class="kpi-description">{{ $attendanceStats['total_registros'] > 0 ? round(($attendanceStats['registros_puntuales']/$attendanceStats['total_registros'])*100, 1) : 0 }}% del total</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">⚠️</div>
                            <div class="kpi-title">Llegadas Tarde</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['llegadas_tarde']) }}</div>
                        <div class="kpi-description">{{ $attendanceStats['total_registros'] > 0 ? round(($attendanceStats['llegadas_tarde']/$attendanceStats['total_registros'])*100, 1) : 0 }}% del total</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">📅</div>
                            <div class="kpi-title">Días Laborales</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['total_dias']) }}</div>
                        <div class="kpi-description">Días con registro de actividad</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">❌</div>
                            <div class="kpi-title">Ausencias</div>
                        </div>
                        <div class="kpi-value">{{ number_format($attendanceStats['ausencias_total']) }}</div>
                        <div class="kpi-description">Registros de inasistencia</div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon">📈</div>
                            <div class="kpi-title">Promedio Diario</div>
                        </div>
                        <div class="kpi-value">{{ $attendanceStats['promedio_diario'] }}</div>
                        <div class="kpi-description">Marcaciones por día</div>
                    </div>
                </div>
            </div>

            <!-- Análisis Estratégico -->
            <div class="section">
                <h2 class="section-title">ANÁLISIS ESTRATÉGICO</h2>
                <div class="insights-section">
                    <div class="insight-card">
                        <div class="insight-title">CUMPLIMIENTO HORARIO</div>
                        <div class="insight-text">
                            El análisis de puntualidad muestra que el 
                            {{ $attendanceStats['total_registros'] > 0 ? round(($attendanceStats['registros_puntuales']/$attendanceStats['total_registros'])*100, 1) : 0 }}% 
                            de las marcaciones fueron puntuales. Las llegadas tarde representan el 
                            {{ $attendanceStats['total_registros'] > 0 ? round(($attendanceStats['llegadas_tarde']/$attendanceStats['total_registros'])*100, 1) : 0 }}% 
                            del total, indicando un buen nivel de disciplina horaria en la organización.
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-title">PRODUCTIVIDAD ORGANIZACIONAL</div>
                        <div class="insight-text">
                            Con un promedio de asistencia del {{ $attendanceStats['promedio_asistencia'] }}% y 
                            {{ $attendanceStats['promedio_diario'] }} marcaciones diarias, la organización mantiene 
                            un ritmo constante de actividad. La relación entre empleados únicos 
                            ({{ number_format($attendanceStats['total_empleados']) }}) y registros totales 
                            ({{ number_format($attendanceStats['total_registros']) }}) refleja una participación activa del personal.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Registros -->
            <div class="section">
                <h2 class="section-title">REGISTRO DETALLADO DE ASISTENCIA</h2>
                <div class="table-section">
                    <div class="table-container">
                        <div class="table-header">
                            <div class="table-title">Marcaciones Biométricas</div>
                            <div class="table-subtitle">Detalle cronológico de entradas y salidas registradas</div>
                        </div>
                        
                        <table id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>ID Empleado</th>
                                    <th>Nombre Completo</th>
                                    <th>Fecha</th>
                                    <th>Hora Entrada</th>
                                    <th>Hora Salida</th>
                                    <th>Departamento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($records && count($records) > 0)
                                    @foreach($records as $record)
                                        <tr>
                                            <td><code>{{ $record->no_id }}</code></td>
                                            <td>{{ $record->nombre_apellidos }}</td>
                                            <td><strong>{{ \Carbon\Carbon::parse($record->fecha)->format('d/m/Y') }}</strong></td>
                                            <td>{{ $record->entrada ?: '-' }}</td>
                                            <td>{{ $record->salida ?: '-' }}</td>
                                            <td>{{ $record->departamento ?? 'No especificado' }}</td>
                                            <td>
                                                @php
                                                    $horaEntrada = null;
                                                    if ($record->entrada) {
                                                        try {
                                                            $horaEntrada = \Carbon\Carbon::parse($record->entrada);
                                                            $horaLimite = \Carbon\Carbon::parse('07:00:00');
                                                        } catch (\Exception $e) {
                                                            $horaEntrada = null;
                                                        }
                                                    }
                                                @endphp
                                                @if($horaEntrada && $horaEntrada->gt($horaLimite))
                                                    <span class="badge badge-warning">Tarde</span>
                                                @elseif($record->entrada)
                                                    <span class="badge badge-success">Puntual</span>
                                                @else
                                                    <span class="badge badge-danger">Sin registro</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #666666; padding: 30px;">
                                            No hay registros disponibles para el período seleccionado
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        
                        <div class="table-pagination">
                            <div class="pagination">
                                <button class="pagination-button">‹ Anterior</button>
                                <div class="pagination-info">Página 1 de 1</div>
                                <button class="pagination-button">Siguiente ›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer institucional -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">SISTEMA DE CONTROL BIOMÉTRICO</div>
                <div class="footer-info">
                    <div>Documento de uso interno</div>
                    <div>Información confidencial</div>
                </div>
            </div>
            
            <div class="footer-stats">
                <div class="footer-stat">
                    <div class="footer-stat-value">{{ count($records) }}</div>
                    <div class="footer-stat-label">Registros Mostrados</div>
                </div>
                <div class="footer-stat">
                    <div class="footer-stat-value">{{ $attendanceStats['promedio_diario'] }}</div>
                    <div class="footer-stat-label">Promedio Diario</div>
                </div>
                <div class="footer-stat">
                    <div class="footer-stat-value">{{ $attendanceStats['promedio_asistencia'] }}%</div>
                    <div class="footer-stat-label">Asistencia</div>
                </div>
                <div class="footer-stat">
                    <div class="footer-stat-value">{{ number_format($attendanceStats['total_empleados']) }}</div>
                    <div class="footer-stat-label">Empleados</div>
                </div>
            </div>
            
            <div class="signature">
                <p>Informe generado automáticamente el {{ now()->format('d \d\e F \d\e Y \a \l\a\s H:i') }} hrs</p>
                <p>Sistema de Control Biométrico - Datos en tiempo real - Documento confidencial</p>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad básica de paginación para el reporte (sin dependencias)
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('attendanceTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const rowsPerPage = 15;
            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / rowsPerPage);
            
            function showPage(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                
                rows.forEach((row, index) => {
                    row.style.display = (index >= start && index < end) ? '' : 'none';
                });
                
                // Actualizar info de paginación
                const paginationInfo = document.querySelector('.pagination-info');
                if (paginationInfo) {
                    paginationInfo.textContent = `Página ${page} de ${totalPages}`;
                }
                
                // Actualizar botones
                const prevBtn = document.querySelector('.pagination-button');
                const nextBtn = document.querySelectorAll('.pagination-button')[1];
                
                if (prevBtn) prevBtn.disabled = page === 1;
                if (nextBtn) nextBtn.disabled = page === totalPages;
            }
            
            // Eventos de paginación
            const prevBtn = document.querySelector('.pagination-button');
            const nextBtn = document.querySelectorAll('.pagination-button')[1];
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        showPage(currentPage);
                    }
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        showPage(currentPage);
                    }
                });
            }
            
            // Mostrar primera página
            if (rows.length > 0) {
                showPage(1);
            }
        });
    </script>
</body>
</html>