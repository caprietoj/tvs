@extends('adminlte::page')

@section('title', 'Encuesta Padres de Familia - Cafetería')

@section('content_header')
    <h1>
        <i class="fas fa-apple-alt"></i>
        Encuesta Padres de Familia y/o Estudiantes - Cafetería
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-poll"></i>
                        Evaluación del Servicio de Cafetería - Comunidad Educativa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta busca conocer la opinión de padres de familia y estudiantes sobre el servicio de cafetería escolar.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-heart"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Alimentación Saludable</span>
                                    <span class="info-box-number">Nutrición Balanceada</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 89%"></div>
                                    </div>
                                    <span class="progress-description">Calidad nutricional</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-smile"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Satisfacción Estudiantil</span>
                                    <span class="info-box-number">Aceptación</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                    </div>
                                    <span class="progress-description">Gustos de los estudiantes</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Precio Accesible</span>
                                    <span class="info-box-number">Economía Familiar</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 76%"></div>
                                    </div>
                                    <span class="progress-description">Costo beneficio</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users"></i>
                                        Opiniones por Grupo
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5><i class="fas fa-child text-primary"></i> Estudiantes</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Sabor de los alimentos</span>
                                                    <strong class="text-success">4.2/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Variedad del menú</span>
                                                    <strong class="text-warning">3.8/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Tamaño de las porciones</span>
                                                    <strong class="text-info">4.0/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Tiempo de entrega</span>
                                                    <strong class="text-success">4.3/5</strong>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5><i class="fas fa-users text-info"></i> Padres de Familia</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Valor nutricional</span>
                                                    <strong class="text-success">4.5/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Higiene y seguridad</span>
                                                    <strong class="text-success">4.6/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Precio accesible</span>
                                                    <strong class="text-warning">3.9/5</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Comunicación</span>
                                                    <strong class="text-info">4.1/5</strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-lightbulb"></i>
                                        Sugerencias Frecuentes
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="callout callout-info">
                                        <h6><i class="fas fa-plus-circle"></i> Más Variedad</h6>
                                        <p>Incluir más opciones vegetarianas y sin gluten.</p>
                                    </div>
                                    
                                    <div class="callout callout-success">
                                        <h6><i class="fas fa-clock"></i> Horarios</h6>
                                        <p>Ampliar horarios de atención en recreos.</p>
                                    </div>
                                    
                                    <div class="callout callout-warning">
                                        <h6><i class="fas fa-dollar-sign"></i> Precios</h6>
                                        <p>Considerar combos familiares económicos.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-trophy"></i>
                                        Fortalezas Destacadas
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <i class="fas fa-shield-alt fa-3x text-success"></i>
                                            <h5>Higiene Impecable</h5>
                                            <p class="text-muted">Protocolos de seguridad alimentaria</p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <i class="fas fa-leaf fa-3x text-success"></i>
                                            <h5>Ingredientes Frescos</h5>
                                            <p class="text-muted">Productos naturales y de calidad</p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <i class="fas fa-user-friends fa-3x text-info"></i>
                                            <h5>Personal Amable</h5>
                                            <p class="text-muted">Atención cálida y profesional</p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <i class="fas fa-home fa-3x text-warning"></i>
                                            <h5>Ambiente Familiar</h5>
                                            <p class="text-muted">Espacio acogedor para la comunidad</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-info btn-lg">
                                <i class="fas fa-edit"></i>
                                Responder Encuesta
                            </button>
                            <button type="button" class="btn btn-outline-success btn-lg ml-2">
                                <i class="fas fa-chart-pie"></i>
                                Ver Resultados
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-lg ml-2">
                                <i class="fas fa-utensils"></i>
                                Menú Semanal
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
    .info-box {
        border-radius: 10px;
    }
    .callout {
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .list-group-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Padres de Familia - Cafetería cargada correctamente');
</script>
@stop
