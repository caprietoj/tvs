@extends('adminlte::page')

@section('title', 'Encuesta Servicios Complementarios - Transporte')

@section('content_header')
    <h1>
        <i class="fas fa-bus"></i>
        Encuesta Servicios Complementarios - Transporte
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-poll"></i>
                        Evaluación del Servicio de Transporte Escolar
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta evalúa la calidad y seguridad del servicio de transporte escolar institucional.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>95%</h3>
                                    <p>Puntualidad</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>98%</h3>
                                    <p>Seguridad</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>92%</h3>
                                    <p>Comodidad</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-couch"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>88%</h3>
                                    <p>Atención</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-check-circle"></i>
                                        Aspectos Evaluados
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-clock text-success"></i> Puntualidad en rutas</span>
                                            <span class="badge badge-success badge-pill">Excelente</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-shield-alt text-primary"></i> Seguridad vehicular</span>
                                            <span class="badge badge-primary badge-pill">Muy bueno</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-user-tie text-info"></i> Comportamiento del conductor</span>
                                            <span class="badge badge-info badge-pill">Bueno</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-bus text-warning"></i> Estado del vehículo</span>
                                            <span class="badge badge-warning badge-pill">Satisfactorio</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-route text-secondary"></i> Eficiencia de rutas</span>
                                            <span class="badge badge-secondary badge-pill">Regular</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Estadísticas del Servicio
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="callout callout-success">
                                        <h5><i class="fas fa-users"></i> Estudiantes Atendidos</h5>
                                        <p><strong>450+</strong> estudiantes utilizan el servicio de transporte diariamente.</p>
                                    </div>
                                    
                                    <div class="callout callout-info">
                                        <h5><i class="fas fa-map-marked-alt"></i> Rutas Activas</h5>
                                        <p><strong>12 rutas</strong> cubren diferentes sectores de la ciudad.</p>
                                    </div>
                                    
                                    <div class="callout callout-warning">
                                        <h5><i class="fas fa-star"></i> Calificación General</h5>
                                        <p><strong>4.1/5.0</strong> estrellas de satisfacción promedio.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-success btn-lg">
                                <i class="fas fa-edit"></i>
                                Responder Encuesta
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg ml-2">
                                <i class="fas fa-chart-line"></i>
                                Ver Resultados
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-lg ml-2">
                                <i class="fas fa-map"></i>
                                Ver Rutas
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
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
    }
    .small-box {
        border-radius: 10px;
    }
    .callout {
        border-radius: 10px;
    }
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Servicios Complementarios - Transporte cargada correctamente');
</script>
@stop
