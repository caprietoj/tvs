@extends('adminlte::page')

@section('title', 'Ejecución Presupuestal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ejecución Presupuestal</h1>
        <div>
            <select id="monthFilter" class="form-control d-inline-block mr-2" style="width: 200px;">
                <option value="">Todos los meses</option>
                @foreach($months as $month)
                    <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                        {{ $month }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('budget.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Registrar Ejecución
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Tarjetas de Resumen -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['promedio_ejecucion'], 1) }}%</h3>
                <p>Promedio de Ejecución</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>
                    @php
                        $presupuestoMostrar = request('month') 
                            ? $stats['total_presupuesto'] 
                            : $stats['total_presupuesto_unico'];
                    @endphp
                    COP ${{ number_format($presupuestoMostrar, 0, ',', '.') }}
                    <small style="font-size: 16px">COP</small>
                </h3>
                <p>Presupuesto Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['departamentos_riesgo'] }}</h3>
                <p>Dptos. en Riesgo (>95%)</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['departamentos_bajo'] }}</h3>
                <p>Dptos. Baja Ejecución (<50%)</p>
            </div>
            <div class="icon">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
    </div>

    <!-- Tabla de Ejecución -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th>Presupuesto</th>
                            <th>Ejecutado</th>
                            <th>% Ejecución</th>
                            <th>Estado</th>
                            <th>Análisis</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($budgets as $budget)
                        <tr>
                            <td>{{ $budget->department }}</td>
                            <td>${{ number_format($budget->budget_amount, 2, ',', '.') }}</td>
                            <td>${{ number_format($budget->executed_amount, 2, ',', '.') }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-{{ getProgressColor($budget->execution_percentage) }}" 
                                         role="progressbar" 
                                         style="width: {{ $budget->execution_percentage }}%">
                                        {{ number_format($budget->execution_percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ getStatusColor($budget->execution_percentage) }}">
                                    {{ getStatusText($budget->execution_percentage) }}
                                </span>
                            </td>
                            <td>{{ $budget->analysis }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Análisis Estadístico -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Análisis Estadístico</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Resumen General</h5>
                        <ul>
                            <li>Ejecución promedio: {{ number_format($stats['promedio_ejecucion'], 1) }}%</li>
                            <li>Desviación estándar: {{ number_format($stats['desviacion_estandar'], 1) }}%</li>
                            <li>Departamento con mayor ejecución: 
                                @if($stats['max_ejecucion'])
                                    {{ $stats['max_ejecucion']->department }} 
                                    ({{ number_format($stats['max_ejecucion']->execution_percentage, 1) }}%)
                                @else
                                    No hay datos
                                @endif
                            </li>
                            <li>Departamento con menor ejecución: 
                                @if($stats['min_ejecucion'])
                                    {{ $stats['min_ejecucion']->department }} 
                                    ({{ number_format($stats['min_ejecucion']->execution_percentage, 1) }}%)
                                @else
                                    No hay datos
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Distribución de Ejecución</h5>
                        <ul>
                            <li>Crítico (>95%): {{ $stats['departamentos_riesgo'] }} departamentos</li>
                            <li>Alto (85-95%): {{ $stats['departamentos_alto'] }} departamentos</li>
                            <li>Normal (50-85%): {{ $stats['departamentos_normal'] }} departamentos</li>
                            <li>Bajo (<50%): {{ $stats['departamentos_bajo'] }} departamentos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Visualización de Ejecución Presupuestal</h3>
            </div>
            <div class="card-body">
                <canvas id="budgetChart"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.progress { height: 20px; }
.progress-bar { line-height: 20px; }
.small-box { transition: transform .3s; }
.small-box:hover { transform: translateY(-3px); }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('budgetChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Presupuestado',
            data: @json($chartData['budget']),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        },
        {
            label: 'Ejecutado',
            data: @json($chartData['executed']),
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Monto (COP)'
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Comparación Presupuesto vs Ejecución por Departamento'
            }
        }
    }
});

$('#monthFilter').change(function() {
    window.location.href = '{{ route("budget.index") }}?month=' + $(this).val();
});
</script>
@stop

@php
function getProgressColor($percentage) {
    if ($percentage > 95) return 'danger';
    if ($percentage > 85) return 'warning';
    if ($percentage > 50) return 'info';
    return 'secondary';
}

function getStatusColor($percentage) {
    if ($percentage > 95) return 'danger';
    if ($percentage > 85) return 'warning';
    if ($percentage > 50) return 'success';
    return 'secondary';
}

function getStatusText($percentage) {
    if ($percentage > 95) return 'CRÍTICO';
    if ($percentage > 85) return 'ALTO';
    if ($percentage > 50) return 'NORMAL';
    return 'BAJO';
}
@endphp