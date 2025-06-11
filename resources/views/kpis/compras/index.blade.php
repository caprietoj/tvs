@extends('adminlte::page')

@section('title', 'KPIs Compras')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>KPIs - Compras</h1>
        <!-- <a href="{{ route('kpis.compras.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo KPI
        </a> -->
    </div>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="float-right">
            <form method="GET" action="{{ route('kpis.compras.index') }}" class="form-inline">
                <label for="month" class="mr-2">Filtrar por Mes:</label>
                <select name="month" id="month" class="form-control select2bs4" onchange="this.form.submit()">
                    <option value="">Todos los meses</option>
                    @php
                        $meses = [
                            1 => 'Enero',
                            2 => 'Febrero',
                            3 => 'Marzo',
                            4 => 'Abril',
                            5 => 'Mayo',
                            6 => 'Junio',
                            7 => 'Julio',
                            8 => 'Agosto',
                            9 => 'Septiembre',
                            10 => 'Octubre',
                            11 => 'Noviembre',
                            12 => 'Diciembre'
                        ];
                    @endphp
                    @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

<!-- KPIs de Medición -->
<div class="card custom-card">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">KPIs de Medición</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="measurementKpiTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del KPI</th>
                        <th>Metodología</th>
                        <th>Frecuencia</th>
                        <th>Fecha de Medición</th>
                        <th>Porcentaje</th>
                        <th>Análisis</th>
                        <th>URL</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpis->where('type', 'measurement') as $kpi)
                    <tr>
                        <td>{{ $kpi->id }}</td>
                        <td>{{ $kpi->threshold->kpi_name }}</td>
                        <td>{{ $kpi->methodology }}</td>
                        <td>{{ $kpi->frequency }}</td>
                        <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                        <td class="percentage-cell" 
                            data-toggle="tooltip" 
                            data-html="true" 
                            title="<div class='text-left'>
                                <strong>Análisis:</strong><br>
                                Estado: {{ $kpi->percentage >= $kpi->threshold->value ? 'Alcanzado' : 'No Alcanzado' }}<br>
                                Umbral: {{ $kpi->threshold->value }}%<br>
                                Variación: {{ $kpi->percentage - $kpi->threshold->value }}%<br>
                                {{ $kpi->analysis ?? 'No hay análisis disponible' }}
                            </div>"
                            data-kpi-id="{{ $kpi->id }}"
                            data-kpi-type="measurement"
                            style="cursor: pointer;">
                            {{ $kpi->percentage }}%
                        </td>
                        <td class="analysis-cell">
                            <div class="analysis-content">
                                @if($kpi->analysis)
                                    {{ Str::limit($kpi->analysis, 50) }}
                                    @if(strlen($kpi->analysis) > 50)
                                        <a href="#" class="view-more" data-analysis="{{ $kpi->analysis }}">ver más</a>
                                    @endif
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(isset($kpi->url) && !empty($kpi->url))
                                <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                    <i class="fas fa-external-link-alt"></i> Ver enlace
                                </a>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $thresholdValue = $kpi->threshold ? $kpi->threshold->value : 80;
                                $status = $kpi->percentage >= $thresholdValue ? 'Alcanzado' : 'No Alcanzado';
                            @endphp
                            <span class="badge {{ $status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('kpis.compras.show', $kpi->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kpis.compras.edit', $kpi->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- KPIs Informativos -->
<div class="card custom-card mt-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">KPIs Informativos</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="informativeKpiTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del KPI</th>
                        <th>Metodología</th>
                        <th>Frecuencia</th>
                        <th>Fecha de Medición</th>
                        <th>Porcentaje</th>
                        <th>Análisis</th>
                        <th>URL</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpis->where('type', 'informative') as $kpi)
                    <tr>
                        <td>{{ $kpi->id }}</td>
                        <td>{{ $kpi->threshold->kpi_name }}</td>
                        <td>{{ $kpi->methodology }}</td>
                        <td>{{ $kpi->frequency }}</td>
                        <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                        <td class="percentage-cell" 
                            data-toggle="tooltip" 
                            data-html="true" 
                            title="<div class='text-left'>
                                <strong>Análisis:</strong><br>
                                Estado: {{ $kpi->percentage >= $kpi->threshold->value ? 'Alcanzado' : 'No Alcanzado' }}<br>
                                Umbral: {{ $kpi->threshold->value }}%<br>
                                Variación: {{ $kpi->percentage - $kpi->threshold->value }}%<br>
                                {{ $kpi->analysis ?? 'No hay análisis disponible' }}
                            </div>"
                            data-kpi-id="{{ $kpi->id }}"
                            data-kpi-type="informative"
                            style="cursor: pointer;">
                            {{ $kpi->percentage }}%
                        </td>
                        <td class="analysis-cell">
                            <div class="analysis-content">
                                @if($kpi->analysis)
                                    {{ Str::limit($kpi->analysis, 50) }}
                                    @if(strlen($kpi->analysis) > 50)
                                        <a href="#" class="view-more" data-analysis="{{ $kpi->analysis }}">ver más</a>
                                    @endif
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(isset($kpi->url) && !empty($kpi->url))
                                <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                    <i class="fas fa-external-link-alt"></i> Ver enlace
                                </a>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $thresholdValue = $kpi->threshold ? $kpi->threshold->value : 80;
                                $status = $kpi->percentage >= $thresholdValue ? 'Alcanzado' : 'No Alcanzado';
                            @endphp
                            <span class="badge {{ $status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('kpis.compras.show', $kpi->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kpis.compras.edit', $kpi->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Análisis Estadístico -->
<!-- <div class="card custom-card mt-4">
    <div class="card-header bg-success">
        <h3 class="card-title text-white">Análisis Estadístico</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estadísticas Generales</span>
                        <span class="info-box-number">Media: {{ number_format($average, 2) }}%</span>
                        <span class="info-box-number">Mediana: {{ number_format($median, 2) }}%</span>
                        <span class="info-box-number">Desviación Estándar: {{ number_format($stdDev, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-chart-bar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Valores Extremos</span>
                        <span class="info-box-number">Máximo: {{ $max }}%</span>
                        <span class="info-box-number">Mínimo: {{ $min }}%</span>
                        <span class="info-box-number">KPIs bajo umbral: {{ $countUnder }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Comparativa de KPIs</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="kpiChart" style="min-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <h5><i class="icon fas fa-info"></i> Conclusión del Análisis</h5>
            <p>{{ $conclusion }}</p>
        </div>
    </div>
</div> -->
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #364E76; /* Updated to institutional blue */
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --info: #17a2b8;
        --warning: #ffc107;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .custom-card {
        background: #ffffff;
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .custom-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,.12);
    }

    .card-header {
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 1.5rem;
    }

    .bg-primary {
        background: #364E76 !important; /* Changed from gradient to solid institutional blue */
    }

    .bg-info {
        background: linear-gradient(135deg, var(--info) 0%, #36b9cc 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, var (--success) 0%, #2dce89 100%) !important;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border-top: none;
        background: rgba(0,0,0,.05);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem;
    }

    .badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
        border-radius: 20px;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #f86384 100%);
    }

    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-group .btn {
        margin: 0 0.2rem;
    }
    
    .btn-group .btn-sm {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 3px;
        border-radius: 4px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(26, 72, 132, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5298 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }
    
    .btn-link {
        color: var(--primary);
        text-decoration: none;
    }
    
    .btn-link:hover {
        text-decoration: underline;
    }

    .tooltip .tooltip-inner {
        max-width: 300px;
        padding: 15px;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .kpi-modal {
        max-width: 400px !important;
    }

    .kpi-header {
        text-align: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .kpi-body {
        padding: 1.5rem;
    }

    .kpi-progress {
        position: relative;
        width: 180px;
        height: 180px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #f3f3f3, #ffffff);
        border-radius: 50%;
        box-shadow: 
            8px 8px 16px rgba(0,0,0,0.1),
            -8px -8px 16px rgba(255,255,255,0.8);
    }

    .kpi-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem;
        font-weight: 700;
        color: #364E76;
        text-align: center;
        line-height: 1;
        z-index: 2;
    }

    .kpi-percentage::after {
        content: '';
        display: block;
        width: 40px;
        height: 3px;
        background: currentColor;
        opacity: 0.1;
        margin: 8px auto 0;
        border-radius: 2px;
    }

    .kpi-circle {
        transform: rotate(-90deg);
        position: relative;
        z-index: 1;
    }

    .kpi-circle-bg {
        fill: none;
        stroke: rgba(0,0,0,0.05);
        stroke-width: 8;
    }

    .kpi-circle-progress {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        filter: drop-shadow(0 0 4px rgba(0,0,0,0.2));
        transition: stroke-dashoffset 1.5s ease-in-out, stroke 0.5s ease;
    }

    .kpi-status {
        text-align: center;
        margin-top: 1rem;
    }

    .kpi-details {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
        font-size: 0.9rem;
        color: #666;
    }

    .swal-wide {
        width: 850px !important;
    }
    .swal-tall {
        min-height: 300px;
    }
    .swal2-popup *:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    .swal2-close:focus {
        box-shadow: none !important;
        outline: 2px solid var(--primary) !important;
        outline-offset: 2px;
    }
    
    [role="dialog"] {
        animation: none !important;
    }
    
    .kpi-modal-container {
        padding: 0;
    }

    .analysis-cell {
        max-width: 250px;
        min-width: 200px;
    }
    
    .analysis-content {
        position: relative;
        display: inline-block;
        color: #333;
        word-break: break-word;
    }
    
    .view-more {
        display: inline-block;
        color: #364E76;
        margin-left: 5px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    
    .view-more:hover {
        text-decoration: underline;
        color: #2a3f5f;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTables
    $('#measurementKpiTable, #informativeKpiTable').DataTable({
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        }
    });

    // Initialize tooltip
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        placement: 'right',
        boundary: 'window',
        template: `
            <div class="tooltip" role="tooltip">
                <div class="arrow"></div>
                <div class="tooltip-inner bg-white text-dark"></div>
            </div>
        `
    });

    // Handle percentage cell click
    $('.percentage-cell').click(function() {
        const cell = $(this); // Store reference to clicked cell
        const percentage = parseFloat($(this).text());
        const status = $(this).closest('tr').find('.badge').text().trim();
        const frequency = $(this).closest('tr').find('td:eq(3)').text().trim();
        const date = $(this).closest('tr').find('td:eq(4)').text().trim();
        const kpiType = $(this).data('kpi-type');
        
        const radius = 60;
        const circumference = 2 * Math.PI * radius;
        const progress = percentage / 100;
        const dashoffset = circumference * (1 - progress);
        let strokeColor;
        
        // Enhanced color based on percentage
        if (percentage >= 90) {
            strokeColor = '#28a745';
        } else if (percentage >= 70) {
            strokeColor = '#17a2b8';
        } else if (percentage >= 50) {
            strokeColor = '#ffc107';
        } else {
            strokeColor = '#dc3545';
        }

        Swal.fire({
            html: `
                <div class="kpi-header" role="region" aria-label="Detalles del KPI">
                    <div class="kpi-progress" role="progressbar" aria-valuenow="${percentage}" 
                         aria-valuemin="0" aria-valuemax="100">
                        <svg height="180" width="180" class="kpi-circle">
                            <circle class="kpi-circle-bg" 
                                cx="90" 
                                cy="90" 
                                r="${radius}"/>
                            <circle class="kpi-circle-progress" 
                                cx="90" 
                                cy="90" 
                                r="${radius}"
                                stroke="${strokeColor}"
                                stroke-dasharray="${circumference}"
                                stroke-dashoffset="${circumference}"
                                style="transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1)"/>
                        </svg>
                        <div class="kpi-percentage" aria-live="polite">
                            <span>0%</span>
                        </div>
                    </div>
                    <div class="kpi-status" role="status">
                        <span class="badge badge-${status === 'Alcanzado' ? 'success' : 'danger'} px-4 py-2">
                            ${status}
                        </span>
                    </div>
                </div>
                <div class="kpi-details" role="complementary">
                    <small>Última medición: ${date} (${frequency})</small>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            allowEscapeKey: true,
            allowOutsideClick: true,
            focusConfirm: false,
            customClass: {
                popup: 'kpi-modal',
                container: 'kpi-modal-container'
            },
            didOpen: (modal) => {
                modal.querySelector('.swal2-close').focus();
                
                requestAnimationFrame(() => {
                    const progressCircle = modal.querySelector('.kpi-circle-progress');
                    const percentageSpan = modal.querySelector('.kpi-percentage span');
                    progressCircle.style.strokeDashoffset = dashoffset;
                    
                    const duration = 1500;
                    const start = performance.now();
                    
                    const updateCounter = (currentTime) => {
                        const elapsed = currentTime - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        const currentPercentage = progress * percentage;
                        percentageSpan.textContent = `${Math.round(currentPercentage)}%`;
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        }
                    };
                    
                    requestAnimationFrame(updateCounter);
                });
            },
            willClose: () => {
                cell.focus(); // Return focus to the clicked cell
            }
        });
    });

    // Handle view more links for analysis
    $('.view-more').click(function(e) {
        e.preventDefault();
        const analysis = $(this).data('analysis');
        
        Swal.fire({
            title: 'Análisis Detallado',
            html: `<div class="text-left p-3">${analysis}</div>`,
            width: 600,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#364E76'
        });
    });

    // Manejo de eliminación de KPIs
    $('.delete-kpi').click(function() {
        const kpiId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/compras/kpis/${kpiId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            '¡Eliminado!',
                            'El KPI ha sido eliminado.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el KPI.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@stop