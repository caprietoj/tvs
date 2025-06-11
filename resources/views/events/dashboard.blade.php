@extends('adminlte::page')

@section('title', 'Dashboard de Eventos')

@section('content_header')
    <h1>Dashboard de Eventos</h1>
@stop

@section('content')
<!-- Métricas generales -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalEvents }}</h3>
                <p>Total de Eventos</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pendingEvents }}</h3>
                <p>Eventos Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $confirmedEvents }}</h3>
                <p>Eventos Confirmados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Servicio más solicitado del mes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
            @if (empty($mostRequestedService) || empty($eventsThisMonth[$mostRequestedService] ?? null))
                <h5>No hay servicios solicitados este mes</h5>
                <p>Total de solicitudes este mes: 0</p>
            @else
                <h5>Servicio más solicitado del mes: <strong>{{ $mostRequestedService }}</strong></h5>
                <p>Total de solicitudes este mes: {{ $eventsThisMonth[$mostRequestedService] }}</p>
            @endif
            </div>
        </div>
    </div>
</div>

<!-- Filtros y Tabla de Eventos -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Lista de Eventos</h3>
    </div>
    <div class="card-body">
        <form id="filterForm" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="period" class="form-control" onchange="this.form.submit()">
                        <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>Todos los períodos</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hoy</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Este mes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="service" class="form-control" onchange="this.form.submit()">
                        <option value="all" {{ request('service') == 'all' ? 'selected' : '' }}>Todos los servicios</option>
                        @foreach($services as $key => $name)
                            <option value="{{ $key }}" {{ request('service') == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered" id="events-table">
                <thead>
                    <tr>
                        <th>Consecutivo</th>
                        <th>Evento</th>
                        <th>Fecha</th>
                        <th>Lugar</th>
                        <th>Servicios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td>{{ $event->consecutive }}</td>
                        <td>{{ $event->event_name }}</td>
                        <td>{{ $event->service_date->format('d/m/Y') }}</td>
                        <td>{{ $event->location }}</td>
                        <td>
                            @foreach($services as $key => $name)
                                @if($event->{$key . '_required'})
                                    <span class="badge badge-{{ $event->{$key . '_confirmed'} ? 'success' : 'warning' }}">
                                        {{ $name }}
                                    </span>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @php
                                $confirmedCount = 0;
                                $totalRequired = 0;
                                foreach($services as $key => $name) {
                                    if($event->{$key . '_required'}) {
                                        $totalRequired++;
                                        if($event->{$key . '_confirmed'}) $confirmedCount++;
                                    }
                                }
                                $percentage = $totalRequired > 0 ? ($confirmedCount / $totalRequired) * 100 : 0;
                            @endphp
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                                    {{ number_format($percentage) }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{ $event['N/A'] ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay eventos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Eventos por Ubicación</h3>
            </div>
            <div class="card-body">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Eventos por Servicio</h3>
            </div>
            <div class="card-body">
                <canvas id="serviceChart"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.small-box {
    margin-bottom: 20px;
}
.progress { height: 20px; }
.badge { margin-right: 5px; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationData = {!! json_encode($eventsByLocation->pluck('location')) !!};
    const serviceData = {!! json_encode(array_keys($eventsByService)) !!};

    if (locationData.length > 0) {
        new Chart(document.getElementById('locationChart'), {
            type: 'pie',
            data: {
                labels: locationData,
                datasets: [{
                    data: {!! json_encode($eventsByLocation->pluck('total')) !!},
                    backgroundColor: [
                        '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc',
                        '#d2d6de', '#932ab6', '#7c8184', '#4CAF50', '#FF5722'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else {
        document.getElementById('locationChart').insertAdjacentHTML('beforebegin', 
            '<div class="text-center text-muted">No hay datos disponibles</div>');
    }

    if (serviceData.length > 0) {
        new Chart(document.getElementById('serviceChart'), {
            type: 'bar',
            data: {
                labels: serviceData,
                datasets: [{
                    label: 'Cantidad de Eventos',
                    data: {!! json_encode(array_values($eventsByService)) !!},
                    backgroundColor: '#3c8dbc'
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
                }
            }
        });
    } else {
        document.getElementById('serviceChart').insertAdjacentHTML('beforebegin', 
            '<div class="text-center text-muted">No hay datos disponibles</div>');
    }
});

$(document).ready(function() {
    $('#events-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[2, "desc"]]
    });
});
</script>
@stop
