@extends('adminlte::page')

@section('title', 'Ver KPI - Sistemas')

@section('content_header')
    <h1>KPIs - Sistemas</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="float-right">
            <form method="GET" action="{{ route('kpis.sistemas.index') }}" class="form-inline">
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
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">KPIs de Medición</h3>
                <!-- <div class="card-abilities">
                    <a href="{{ route('kpis.sistemas.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus mr-1"></i> Nuevo KPI
                    </a>
                </div> -->
            </div>
            <div class="card-body">
                <table id="measurementTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del KPI</th>
                            <th>Metodología</th>
                            <th>Frecuencia</th>
                            <th>Fecha de Medición</th>
                            <th>Porcentaje</th>
                            <th>URL</th>
                            <th>Estado</th>
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
                            <td>{{ $kpi->percentage }}%</td>
                            <td>
                                @if($kpi->url)
                                    <a href="{{ $kpi->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-link mr-1"></i> Ver
                                    </a>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $kpi->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('kpis.sistemas.show', $kpi->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kpis.sistemas.edit', $kpi->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}">
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
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
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
                            <th>Fecha de Medición</th>
                            <th>Porcentaje</th>
                            <th>URL</th>
                            <th>Estado</th>
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
                            <td>{{ $kpi->percentage }}%</td>
                            <td>
                                @if($kpi->url)
                                    <a href="{{ $kpi->url }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-link mr-1"></i> Ver
                                    </a>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $kpi->status == 'Favorable' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $kpi->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('kpis.sistemas.show', $kpi->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('kpis.sistemas.edit', $kpi->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}">
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
</div>

<!-- <div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Análisis Estadístico</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>KPIs de Medición</h4>
                        <p><strong>Media:</strong> {{ number_format($measurementStats['average'], 2) }}%</p>
                        <p><strong>Mediana:</strong> {{ number_format($measurementStats['median'], 2) }}%</p>
                        <p><strong>Desviación Estándar:</strong> {{ number_format($measurementStats['stdDev'], 2) }}</p>
                        <p><strong>KPIs por debajo del umbral:</strong> {{ $measurementStats['countUnder'] }}</p>
                    </div>
                    <div class="col-md-6">
                        <h4>KPIs Informativos</h4>
                        <p><strong>Media:</strong> {{ number_format($informativeStats['average'], 2) }}%</p>
                        <p><strong>Mediana:</strong> {{ number_format($informativeStats['median'], 2) }}%</p>
                        <p><strong>Desviación Estándar:</strong> {{ number_format($informativeStats['stdDev'], 2) }}</p>
                        <p><strong>KPIs Desfavorables:</strong> {{ $informativeStats['countUnder'] }}</p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <canvas id="kpiChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>
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
        color: white !important;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header .card-abilities {
        margin-left: auto;
    }

    .table thead th {
        background: rgba(26, 72, 132, 0.05);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary);
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
        margin: 0 0.2rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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

    .btn-group {
        display: flex;
        justify-content: center;
    }

    .select2-container--bootstrap4 .select2-selection {
        border: 2px solid #e9ecef;
        border-radius: var(--border-radius);
        min-height: 45px;
        padding: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: white !important;
        border-radius: var(--border-radius);
    }

    .chart-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .btn {
            width: 100%;
            margin: 0.25rem 0;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
        }
        
        .btn-group .btn {
            margin: 0.25rem 0;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    $('#measurementTable, #informativeTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });

    // Configuración del gráfico
    var ctx = document.getElementById('kpiChart');
    if (ctx) {
        var kpiChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'KPIs de Medición',
                    data: {!! json_encode($chartData['measurementData'] ?? []) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'KPIs Informativos',
                    data: {!! json_encode($chartData['informativeData'] ?? []) !!},
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
                        max: 100
                    }
                }
            }
        });
    }

    // Manejo de eliminación
    $('.delete-kpi').click(function(){
        var kpiId = $(this).data('id');
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
                    url: '/sistemas/kpis/' + kpiId,
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