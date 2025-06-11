@extends('adminlte::page')

@section('title', 'Dashboard Help-Desk')

@section('content_header')
    <h1>Dashboard Help-Desk</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalTickets }}</h3>
                <p>Total Tickets</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $abiertos }}</h3>
                <p>Tickets Abiertos</p>
            </div>
            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $enProceso }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $cerrados }}</h3>
                <p>Tickets Cerrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tickets por Prioridad</h3>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" style="min-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tickets por Estado</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="min-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Tickets</h3>
            </div>
            <div class="card-body table-responsive">
                <table id="tickets-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Creado</th>
                            <th>Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}">
                                    {{ Str::limit($ticket->asunto, 50) }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ticket->getStatusColor() }}">
                                    {{ $ticket->estado }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ticket->getPriorityColor() }}">
                                    {{ $ticket->prioridad }}
                                </span>
                            </td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $ticket->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.small-box { margin-bottom: 20px; }
.badge { font-size: 0.9em; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Prioridades (Dispersión)
    new Chart(document.getElementById('priorityChart'), {
        type: 'scatter',
        data: {
            datasets: [
                {
                    label: 'Alta',
                    data: [{ x: 1, y: {{ $alta }} }],
                    backgroundColor: '#dc3545',
                },
                {
                    label: 'Media',
                    data: [{ x: 2, y: {{ $media }} }],
                    backgroundColor: '#ffc107',
                },
                {
                    label: 'Baja',
                    data: [{ x: 3, y: {{ $baja }} }],
                    backgroundColor: '#28a745',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'linear',
                    position: 'bottom',
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return ['', 'Alta', 'Media', 'Baja'][value] || '';
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y} tickets`;
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 10,
                    hoverRadius: 12
                }
            }
        }
    });

    // Gráfico de Estados
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: ['Abiertos', 'En Proceso', 'Cerrados'],
            datasets: [{
                label: 'Cantidad de Tickets',
                data: [{{ $abiertos }}, {{ $enProceso }}, {{ $cerrados }}],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // DataTable
    $('#tickets-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[4, "desc"]],
        "pageLength": 5
    });
});
</script>
@stop
