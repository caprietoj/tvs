@extends('adminlte::page')

@section('title', 'KPIs - Recursos Humanos')

@section('content_header')
    <h1>KPIs - Recursos Humanos</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="float-right">
            <form method="GET" action="{{ route('kpis.rrhh.index') }}" class="form-inline">
                <label for="month" class="mr-2">Filtrar por Mes:</label>
                <select name="month" id="month" class="form-control select2bs4" onchange="this.form.submit()">
                    <option value="">Todos los meses</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </form>
        </div>
    </div>
</div>

<!-- KPIs de Medición -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">KPIs de Medición</h3>
    </div>
    <div class="card-body">
        <table id="measurementTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del KPI</th>
                    <th>Metodología</th>
                    <th>Frecuencia</th>
                    <th>Fecha</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                    <th>URL</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($measurementKpis as $kpi)
                <tr>
                    <td>{{ $kpi->id }}</td>
                    <td>{{ $kpi->name }}</td>
                    <td>{{ $kpi->methodology }}</td>
                    <td>{{ $kpi->frequency }}</td>
                    <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                    <td>{{ number_format($kpi->percentage, 2) }}%</td>
                    <td>
                        <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                            {{ $kpi->status }}
                        </span>
                    </td>
                    <td>
                        @if(isset($kpi->url) && !empty($kpi->url))
                            <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                <i class="fas fa-external-link-alt"></i> Ver
                            </a>
                        @else
                            <span class="text-muted">No disponible</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('kpis.rrhh.show', $kpi->id) }}" class="btn btn-sm btn-info mx-1" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('kpis.rrhh.edit', $kpi->id) }}" class="btn btn-sm btn-primary mx-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger mx-1 delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
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

<!-- KPIs Informativos -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">KPIs Informativos</h3>
    </div>
    <div class="card-body">
        <table id="informativeTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del KPI</th>
                    <th>Metodología</th>
                    <th>Frecuencia</th>
                    <th>Fecha</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                    <th>URL</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($informativeKpis as $kpi)
                <tr>
                    <td>{{ $kpi->id }}</td>
                    <td>{{ $kpi->name }}</td>
                    <td>{{ $kpi->methodology }}</td>
                    <td>{{ $kpi->frequency }}</td>
                    <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                    <td>{{ number_format($kpi->percentage, 2) }}%</td>
                    <td>
                        <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                            {{ $kpi->status }}
                        </span>
                    </td>
                    <td>
                        @if(isset($kpi->url) && !empty($kpi->url))
                            <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                <i class="fas fa-external-link-alt"></i> Ver
                            </a>
                        @else
                            <span class="text-muted">No disponible</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('kpis.rrhh.show', $kpi->id) }}" class="btn btn-sm btn-info mx-1" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('kpis.rrhh.edit', $kpi->id) }}" class="btn btn-sm btn-primary mx-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger mx-1 delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
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

<!-- Análisis Estadístico -->
<!-- <div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h3 class="card-title">Análisis Estadístico</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Media</span>
                        <span class="info-box-number">{{ number_format($average, 2) }}%</span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-sort-numeric-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mediana</span>
                        <span class="info-box-number">{{ number_format($median, 2) }}%</span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-chart-bar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Desviación Estándar</span>
                        <span class="info-box-number">{{ number_format($stdDev, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Valor Máximo</span>
                        <span class="info-box-number">{{ number_format($max, 2) }}%</span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Valor Mínimo</span>
                        <span class="info-box-number">{{ number_format($min, 2) }}%</span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">KPIs bajo el umbral</span>
                        <span class="info-box-number">{{ $countUnder }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <h5><i class="icon fas fa-info"></i> Conclusión del Análisis</h5>
            {{ $conclusion }}
        </div>

        <div class="chart-container mt-4" style="position: relative; height:400px;">
            <canvas id="kpiChart"></canvas>
        </div>
    </div>
</div> -->
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --info: #17a2b8;
        --warning: #ffc107;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%) !important;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
    }

    .table thead th {
        background: rgba(26, 72, 132, 0.05);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border-bottom: 2px solid var(--primary);
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

    .info-box {
        background: #ffffff;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-5px);
    }

    .info-box-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        color: white;
        margin-bottom: 1rem;
    }

    .select2-container--bootstrap4 .select2-selection {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        min-height: 45px;
        padding: 0.5rem;
    }

    .chart-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        margin-top: 2rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #17a2b8 0%, #36b9cc 100%);
        color: white;
        border: none;
        border-radius: var(--border-radius);
    }

    @media (max-width: 768px) {
        .btn {
            width: 100%;
            margin: 0.25rem 0;
        }
        
        .info-box {
            margin-bottom: 1rem;
        }
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
    $('#measurementTable, #informativeTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });

    // Inicializar Select2
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Configuración del gráfico
    const ctx = document.getElementById('kpiChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_merge(
                    $measurementKpis->pluck('name')->toArray(),
                    $informativeKpis->pluck('name')->toArray()
                )) !!},
                datasets: [{
                    label: 'KPIs de Medición',
                    data: {!! json_encode($measurementKpis->pluck('percentage')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'KPIs Informativos',
                    data: {!! json_encode($informativeKpis->pluck('percentage')) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Porcentaje (%)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Comparativa de KPIs por Tipo'
                    }
                }
            }
        });
    }

    // Manejo de eliminación
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
                    url: `/rrhh/kpis/${kpiId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            '¡Eliminado!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el KPI',
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