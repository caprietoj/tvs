@extends('adminlte::page')

@section('title', 'Dashboard - Consolidado Previsitas')

@section('content_header')
    <h1>Dashboard - Consolidado Previsitas</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Tarjetas de estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPrevisitas }}</h3>
                    <p>Total Previsitas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="{{ route('previsitas.index') }}" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $aprobadas }}</h3>
                    <p>Aprobadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('previsitas.index', ['aprobacion_sitio' => '1']) }}" class="small-box-footer">
                    Ver aprobadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendientes }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('previsitas.index', ['aprobacion_sitio' => '0']) }}" class="small-box-footer">
                    Ver pendientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $vencidas }}</h3>
                    <p>Vencidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Ver vencidas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Próximas a vencer -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-circle text-warning"></i>
                        Próximas a Vencer (7 días)
                    </h3>
                </div>
                <div class="card-body">
                    @if($proximasVencer->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Lugar</th>
                                        <th>Responsable</th>
                                        <th>Fecha Visita</th>
                                        <th>Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Creado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proximasVencer as $previsita)
                                        @if($previsita->vencimiento)
                                            @php
                                                $diasRestantes = now()->diffInDays($previsita->vencimiento, false);
                                                $claseAlerta = $diasRestantes <= 3 ? 'table-danger' : ($diasRestantes <= 7 ? 'table-warning' : '');
                                            @endphp
                                            <tr class="{{ $claseAlerta }}">
                                                <td>{{ $previsita->lugar }}</td>
                                                <td>{{ $previsita->responsable }}</td>
                                                <td>{{ $previsita->fecha_visita->format('d/m/Y') }}</td>
                                                <td>{{ $previsita->vencimiento->format('d/m/Y') }}</td>
                                                <td>
                                                    @if($diasRestantes >= 0)
                                                        <span class="badge badge-warning">{{ $diasRestantes }} días</span>
                                                    @else
                                                        <span class="badge badge-danger">Vencida</span>
                                                    @endif
                                                </td>
                                                <td>{{ $previsita->user->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('previsitas.show', $previsita->id) }}" 
                                                       class="btn btn-info btn-sm" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('previsitas.edit', $previsita->id) }}" 
                                                       class="btn btn-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay previsitas próximas a vencer en los próximos 7 días.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de estado (opcional) -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Estado de Previsitas
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="estadoChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('previsitas.create') }}" class="btn btn-primary btn-lg mb-3">
                            <i class="fas fa-plus"></i> Nueva Previsita
                        </a>
                        <a href="{{ route('previsitas.index') }}" class="btn btn-secondary btn-lg mb-3">
                            <i class="fas fa-list"></i> Ver Todas las Previsitas
                        </a>
                        <a href="{{ route('previsitas.index', ['vencimiento' => 'proximas']) }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-clock"></i> Ver Próximas a Vencer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .small-box {
            border-radius: 10px;
        }
        .card {
            border-radius: 10px;
        }
        .table-responsive {
            border-radius: 10px;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de estado de previsitas
    const ctx = document.getElementById('estadoChart').getContext('2d');
    const estadoChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Aprobadas', 'Pendientes', 'Vencidas'],
            datasets: [{
                data: [{{ $aprobadas }}, {{ $pendientes }}, {{ $vencidas }}],
                backgroundColor: [
                    '#28a745',
                    '#ffc107', 
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>
@stop