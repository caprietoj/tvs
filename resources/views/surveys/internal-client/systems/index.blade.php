@extends('adminlte::page')

@section('title', 'Encuesta Cliente Interno - Sistemas')

@section('content_header')
    <h1>
        <i class="fas fa-desktop"></i>
        Encuesta Cliente Interno - Sistemas
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
                        Evaluación del Servicio de Sistemas - Personal Interno
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta evalúa la calidad del soporte técnico y servicios tecnológicos brindados por el área de Sistemas.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-tools"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Soporte Técnico</span>
                                    <span class="info-box-number">Resolución</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 89%"></div>
                                    </div>
                                    <span class="progress-description">Efectividad</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tiempo Respuesta</span>
                                    <span class="info-box-number">Rapidez</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                    </div>
                                    <span class="progress-description">Atención oportuna</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-wifi"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Conectividad</span>
                                    <span class="info-box-number">Estabilidad</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 92%"></div>
                                    </div>
                                    <span class="progress-description">Red y servicios</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-graduation-cap"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Capacitación</span>
                                    <span class="info-box-number">Conocimiento</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 78%"></div>
                                    </div>
                                    <span class="progress-description">Transferencia</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-list-check"></i>
                                        Servicios Evaluados
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-laptop text-primary"></i> Soporte técnico de equipos</span>
                                                    <span class="badge badge-primary badge-pill">Alta prioridad</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-network-wired text-success"></i> Mantenimiento de red</span>
                                                    <span class="badge badge-success badge-pill">Crítico</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-shield-alt text-warning"></i> Seguridad informática</span>
                                                    <span class="badge badge-warning badge-pill">Importante</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-database text-info"></i> Respaldo de información</span>
                                                    <span class="badge badge-info badge-pill">Esencial</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-mobile-alt text-secondary"></i> Aplicaciones móviles</span>
                                                    <span class="badge badge-secondary badge-pill">Relevante</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-chalkboard-teacher text-primary"></i> Tecnología educativa</span>
                                                    <span class="badge badge-primary badge-pill">Importante</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-cog text-dark"></i> Mantenimiento software</span>
                                                    <span class="badge badge-dark badge-pill">Rutinario</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-question-circle text-success"></i> Atención Help-Desk</span>
                                                    <span class="badge badge-success badge-pill">Diario</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Estadísticas del Servicio
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="callout callout-info">
                                        <h5><i class="fas fa-ticket-alt"></i> Tickets Resueltos</h5>
                                        <p><strong>95%</strong> de los tickets fueron resueltos exitosamente este mes.</p>
                                    </div>
                                    
                                    <div class="callout callout-success">
                                        <h5><i class="fas fa-clock"></i> Tiempo Promedio</h5>
                                        <p><strong>2.5 horas</strong> tiempo promedio de resolución de incidencias.</p>
                                    </div>
                                    
                                    <div class="callout callout-warning">
                                        <h5><i class="fas fa-users"></i> Satisfacción</h5>
                                        <p><strong>4.3/5.0</strong> puntuación promedio de satisfacción del usuario.</p>
                                    </div>
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
                            <button type="button" class="btn btn-outline-secondary btn-lg ml-2">
                                <i class="fas fa-ticket-alt"></i>
                                Crear Ticket
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
        margin-bottom: 15px;
    }
    .badge {
        font-size: 10px;
    }
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Cliente Interno - Sistemas cargada correctamente');
</script>
@stop
