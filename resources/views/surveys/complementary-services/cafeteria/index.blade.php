@extends('adminlte::page')

@section('title', 'Encuesta Servicios Complementarios - Cafetería')

@section('content_header')
    <h1>
        <i class="fas fa-utensils"></i>
        Encuesta Servicios Complementarios - Cafetería
        <small class="text-muted">(Panel Administrativo)</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-poll"></i>
                        Evaluación del Servicio de Cafetería - Servicios Complementarios
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-shield-alt"></i> Panel Administrativo</h5>
                        Esta sección está disponible únicamente para administradores del sistema. Aquí puedes gestionar y revisar las encuestas institucionales.
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta evalúa la calidad de los servicios complementarios de cafetería para toda la comunidad educativa.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-hamburger"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Menú Variado</span>
                                    <span class="info-box-number">Opciones</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 86%"></div>
                                    </div>
                                    <span class="progress-description">Diversidad alimentaria</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-heart"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Alimentación Saludable</span>
                                    <span class="info-box-number">Nutrición</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 91%"></div>
                                    </div>
                                    <span class="progress-description">Opciones nutritivas</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Precio Justo</span>
                                    <span class="info-box-number">Accesibilidad</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 82%"></div>
                                    </div>
                                    <span class="progress-description">Relación precio-calidad</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Higiene</span>
                                    <span class="info-box-number">Seguridad</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 94%"></div>
                                    </div>
                                    <span class="progress-description">Protocolos sanitarios</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-list"></i>
                                        Servicios Ofrecidos
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <i class="fas fa-bread-slice text-warning"></i> Desayunos
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-drumstick-bite text-danger"></i> Almuerzos
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-cookie text-info"></i> Meriendas
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-coffee text-dark"></i> Bebidas calientes
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <i class="fas fa-glass-whiskey text-primary"></i> Bebidas frías
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-apple-alt text-success"></i> Frutas frescas
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-carrot text-orange"></i> Ensaladas
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-ice-cream text-secondary"></i> Postres
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-star"></i>
                                        Aspectos a Evaluar
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                                            Calidad de alimentos - 90%
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                                            Tiempo de atención - 85%
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 88%" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100">
                                            Amabilidad del personal - 88%
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                            Variedad del menú - 75%
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 92%" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100">
                                            Higiene y limpieza - 92%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-comments"></i>
                                        Comentarios Destacados
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <blockquote class="quote-warning">
                                                <p>"Excelente calidad en los alimentos, siempre frescos y deliciosos."</p>
                                                <small>Estudiante de Bachillerato</small>
                                            </blockquote>
                                        </div>
                                        <div class="col-md-4">
                                            <blockquote class="quote-success">
                                                <p>"El personal es muy amable y el servicio es rápido."</p>
                                                <small>Padre de Familia</small>
                                            </blockquote>
                                        </div>
                                        <div class="col-md-4">
                                            <blockquote class="quote-info">
                                                <p>"Me gustaría más variedad en opciones vegetarianas."</p>
                                                <small>Docente</small>
                                            </blockquote>
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
                            <button type="button" class="btn btn-outline-success btn-lg ml-2">
                                <i class="fas fa-utensils"></i>
                                Ver Menú
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
    .progress {
        border-radius: 10px;
    }
    .quote-warning {
        border-left: 4px solid #ffc107;
        background: rgba(255, 193, 7, 0.1);
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .quote-success {
        border-left: 4px solid #28a745;
        background: rgba(40, 167, 69, 0.1);
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .quote-info {
        border-left: 4px solid #17a2b8;
        background: rgba(23, 162, 184, 0.1);
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .text-orange {
        color: #fd7e14 !important;
    }
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Servicios Complementarios - Cafetería cargada correctamente');
</script>
@stop
