@extends('adminlte::page')

@section('title', 'Encuesta Cliente Interno - Cafetería')

@section('content_header')
    <h1>
        <i class="fas fa-coffee"></i>
        Encuesta Cliente Interno - Cafetería
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-poll"></i>
                        Evaluación del Servicio de Cafetería - Personal Interno
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta evalúa la satisfacción del personal interno con el servicio de cafetería institucional.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-utensils"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Calidad de Alimentos</span>
                                    <span class="info-box-number">Frescura y sabor</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 88%"></div>
                                    </div>
                                    <span class="progress-description">Satisfacción general</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-smile"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Atención al Cliente</span>
                                    <span class="info-box-number">Servicio cordial</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 92%"></div>
                                    </div>
                                    <span class="progress-description">Amabilidad del personal</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Precios</span>
                                    <span class="info-box-number">Accesibilidad</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 78%"></div>
                                    </div>
                                    <span class="progress-description">Relación calidad-precio</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-check"></i>
                                        Aspectos del Servicio
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-leaf text-success"></i> Calidad e higiene de los alimentos</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-clock text-primary"></i> Tiempo de atención</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-list text-info"></i> Variedad del menú</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-hand-holding-heart text-warning"></i> Atención del personal</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Evaluación General
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 text-center">
                                            <canvas id="satisfactionChart" width="150" height="150"></canvas>
                                            <h5 class="mt-2">Satisfacción General</h5>
                                        </div>
                                        <div class="col-6">
                                            <div class="callout callout-success">
                                                <h5><i class="fas fa-star"></i> Puntuación Actual</h5>
                                                <p>4.2/5.0 estrellas basado en las evaluaciones del personal interno.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-warning btn-lg">
                                <i class="fas fa-edit"></i>
                                Responder Encuesta
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg ml-2">
                                <i class="fas fa-chart-line"></i>
                                Ver Resultados
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .list-group-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
    }
    .callout {
        border-radius: 10px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de satisfacción
    const ctx = document.getElementById('satisfactionChart').getContext('2d');
    const satisfactionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Excelente', 'Bueno', 'Regular', 'Malo'],
            datasets: [{
                data: [45, 35, 15, 5],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#fd7e14',
                    '#dc3545'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    fontSize: 10
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.labels[tooltipItem.index] + ': ' + data.datasets[0].data[tooltipItem.index] + '%';
                    }
                }
            }
        }
    });
    
    console.log('Encuesta Cliente Interno - Cafetería cargada correctamente');
</script>
@stop
