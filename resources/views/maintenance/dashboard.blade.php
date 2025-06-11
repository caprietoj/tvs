@extends('adminlte::page')

@section('title', 'Dashboard Mantenimiento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Dashboard de Mantenimiento</h1>
        <div class="btn-group">
            <a href="{{ route('maintenance.dashboard', ['date_range' => 'week']) }}" 
               class="btn btn-outline-primary {{ $dateRange === 'week' ? 'active' : '' }}">Semana</a>
            <a href="{{ route('maintenance.dashboard', ['date_range' => 'month']) }}" 
               class="btn btn-outline-primary {{ $dateRange === 'month' ? 'active' : '' }}">Mes</a>
            <a href="{{ route('maintenance.dashboard', ['date_range' => 'quarter']) }}" 
               class="btn btn-outline-primary {{ $dateRange === 'quarter' ? 'active' : '' }}">Trimestre</a>
            <a href="{{ route('maintenance.dashboard', ['date_range' => 'year']) }}" 
               class="btn btn-outline-primary {{ $dateRange === 'year' ? 'active' : '' }}">Año</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Tarjetas de resumen -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalRequests }}</h3>
                <p>Total Solicitudes</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pendingRequests }}</h3>
                <p>Solicitudes Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $completedRequests }}</h3>
                <p>Solicitudes Completadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($averageCompletionTime ?? 0, 1) }}</h3>
                <p>Tiempo Promedio (Horas)</p>
            </div>
            <div class="icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>

    <!-- Gráficos principales -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Solicitudes por Tipo</h3>
            </div>
            <div class="card-body">
                <canvas id="requestTypeChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado de Solicitudes</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de línea temporal -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tendencia de Solicitudes</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Tiempo promedio por tipo -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tiempo Promedio por Tipo</h3>
            </div>
            <div class="card-body">
                <canvas id="avgTimeChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabla de solicitudes recientes -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Solicitudes Recientes</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Solicitante</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Tiempo Transcurrido</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</td>
                            <td>{{ $request->user->name }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'in_progress' ? 'info' : ($request->status == 'completed' ? 'success' : 'danger')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $request->priority == 'high' ? 'danger' : ($request->priority == 'medium' ? 'warning' : 'success') }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td>{{ $request->created_at->diffForHumans() }}</td>
                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $recentRequests->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
    }
    .text-primary {
        color: var(--primary) !important;
    }
    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
    }
    .btn-outline-primary:hover, .btn-outline-primary.active {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .small-box {
        border-radius: 8px;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const colors = ['#364E76', '#4682B4', '#5F9EA0', '#6495ED', '#7B68EE', '#483D8B'];
    
    // Gráfico de tipos de solicitud
    new Chart(document.getElementById('requestTypeChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($requestsByType->pluck('request_type')->map(function($type) {
                return ucfirst(str_replace('_', ' ', $type));
            })) !!},
            datasets: [{
                data: {!! json_encode($requestsByType->pluck('total')) !!},
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de estados
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($requestsByStatus->pluck('status')->map(function($status) {
                return ucfirst($status);
            })) !!},
            datasets: [{
                data: {!! json_encode($requestsByStatus->pluck('total')) !!},
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de tendencia mensual
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($requestsByMonth->pluck('month')) !!},
            datasets: [{
                label: 'Solicitudes',
                data: {!! json_encode($requestsByMonth->pluck('total')) !!},
                borderColor: '#364E76',
                tension: 0.1,
                fill: true,
                backgroundColor: 'rgba(54, 78, 118, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de tiempo promedio por tipo
    new Chart(document.getElementById('avgTimeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($avgTimeByType->pluck('request_type')->map(function($type) {
                return ucfirst(str_replace('_', ' ', $type));
            })) !!},
            datasets: [{
                label: 'Horas promedio',
                data: {!! json_encode($avgTimeByType->pluck('avg_time')) !!},
                backgroundColor: '#364E76'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@stop
