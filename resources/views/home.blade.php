{{-- resources/views/home.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <!-- Tarjeta Total Tickets -->
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
    <!-- Tarjeta Abiertos -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $abiertos }}</h3>
                <p>Abiertos</p>
            </div>
            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
    </div>
    <!-- Tarjeta En Proceso -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $enProceso }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
    </div>
    <!-- Tarjeta Cerrados -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $cerrados }}</h3>
                <p>Cerrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico Lineal para Tickets por Estado -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tickets por Estado</h3>
            </div>
            <div class="card-body">
                <canvas id="estadoChart" style="height:250px"></canvas>
            </div>
        </div>
    </div>
    <!-- Gráfico Lineal para Tickets por Prioridad -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Tickets por Prioridad</h3>
            </div>
            <div class="card-body">
                <canvas id="prioridadChart" style="height:250px"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico Lineal para Tickets por Estado
    var ctxEstado = document.getElementById('estadoChart').getContext('2d');
    var estadoChart = new Chart(ctxEstado, {
        type: 'line',
        data: {
            labels: ['Abierto', 'En Proceso', 'Cerrado'],
            datasets: [{
                label: 'Tickets por Estado',
                data: [{{ $abiertos }}, {{ $enProceso }}, {{ $cerrados }}],
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico Lineal para Tickets por Prioridad
    var ctxPrioridad = document.getElementById('prioridadChart').getContext('2d');
    var prioridadChart = new Chart(ctxPrioridad, {
        type: 'line',
        data: {
            labels: ['Baja', 'Media', 'Alta'],
            datasets: [{
                label: 'Tickets por Prioridad',
                data: [{{ $baja }}, {{ $media }}, {{ $alta }}],
                backgroundColor: 'rgba(23, 162, 184, 0.2)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@stop
