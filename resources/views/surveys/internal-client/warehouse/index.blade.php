@extends('adminlte::page')

@section('title', 'Encuesta Cliente Interno - Almacén')

@section('content_header')
    <h1>
        <i class="fas fa-warehouse"></i>
        Encuesta Cliente Interno - Almacén
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
                        Evaluación del Servicio de Almacén
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta tiene como objetivo evaluar la calidad del servicio prestado por el área de Almacén para mejorar continuamente nuestros procesos internos.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-clipboard-list"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Gestión de Inventario</span>
                                    <span class="info-box-number">Control y seguimiento</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 85%"></div>
                                    </div>
                                    <span class="progress-description">Disponibilidad de productos</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tiempo de Respuesta</span>
                                    <span class="info-box-number">Entrega oportuna</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 90%"></div>
                                    </div>
                                    <span class="progress-description">Cumplimiento de tiempos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Aspectos a Evaluar
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-check-circle text-success"></i> Calidad del servicio de entrega</span>
                                            <span class="badge badge-primary badge-pill">Importante</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-check-circle text-success"></i> Tiempo de respuesta a solicitudes</span>
                                            <span class="badge badge-primary badge-pill">Importante</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-check-circle text-success"></i> Atención al cliente interno</span>
                                            <span class="badge badge-primary badge-pill">Importante</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-check-circle text-success"></i> Disponibilidad de productos</span>
                                            <span class="badge badge-primary badge-pill">Importante</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-check-circle text-success"></i> Organización del almacén</span>
                                            <span class="badge badge-secondary badge-pill">Relevante</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-primary btn-lg">
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
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Cliente Interno - Almacén cargada correctamente');
</script>
@stop
