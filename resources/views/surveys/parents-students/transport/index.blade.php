@extends('adminlte::page')

@section('title', 'Encuesta Padres de Familia - Cafetería y Transporte')

@section('content_header')
    <h1>
        <i class="fas fa-utensils"></i>
        <i class="fas fa-school"></i>
        Encuesta Padres de Familia y/o Estudiantes - Cafetería y Transporte
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
                        Evaluación de los Servicios de Cafetería y Transporte Escolar - Comunidad Educativa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <i class="fas fa-info-circle"></i>
                        <strong>Propósito:</strong> Esta encuesta evalúa la satisfacción de padres de familia y estudiantes con los servicios de cafetería y transporte escolar institucional.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header bg-primary">
                                    <div class="widget-user-image">
                                        <i class="fas fa-shield-alt fa-2x"></i>
                                    </div>
                                    <h3 class="widget-user-username">Seguridad y Confianza</h3>
                                    <h5 class="widget-user-desc">Prioridad #1 para las familias</h5>
                                </div>
                                <div class="card-footer p-0">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Conductores capacitados <span class="float-right badge bg-success">100%</span>
                                            </span>
                                        </li>
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Vehículos con seguro <span class="float-right badge bg-info">100%</span>
                                            </span>
                                        </li>
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Monitores a bordo <span class="float-right badge bg-warning">95%</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header bg-success">
                                    <div class="widget-user-image">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                    <h3 class="widget-user-username">Puntualidad y Eficiencia</h3>
                                    <h5 class="widget-user-desc">Cumplimiento de horarios</h5>
                                </div>
                                <div class="card-footer p-0">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Llegada a tiempo <span class="float-right badge bg-success">93%</span>
                                            </span>
                                        </li>
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Comunicación oportuna <span class="float-right badge bg-info">88%</span>
                                            </span>
                                        </li>
                                        <li class="nav-item">
                                            <span class="nav-link">
                                                Rutas optimizadas <span class="float-right badge bg-warning">90%</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>4.4<sup style="font-size: 20px">/5</sup></h3>
                                    <p>Calificación General</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    Ver detalles <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>450<sup style="font-size: 20px">+</sup></h3>
                                    <p>Estudiantes Atendidos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    Ver estadísticas <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>12</h3>
                                    <p>Rutas Activas</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-map"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    Ver rutas <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Aspectos Más Valorados
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="progress-group">
                                        <strong>Seguridad del conductor</strong>
                                        <span class="float-right"><b>96</b>/100</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: 96%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-group">
                                        <strong>Estado del vehículo</strong>
                                        <span class="float-right"><b>94</b>/100</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" style="width: 94%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-group">
                                        <strong>Puntualidad</strong>
                                        <span class="float-right"><b>88</b>/100</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-warning" style="width: 88%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-group">
                                        <strong>Comunicación</strong>
                                        <span class="float-right"><b>85</b>/100</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-info" style="width: 85%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-group">
                                        <strong>Precio del servicio</strong>
                                        <span class="float-right"><b>78</b>/100</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-danger" style="width: 78%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-comments"></i>
                                        Comentarios Destacados
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="direct-chat-messages" style="height: 300px;">
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-left">Madre de familia</span>
                                                <span class="direct-chat-timestamp float-right">5 días</span>
                                            </div>
                                            <div class="direct-chat-text">
                                                "Excelente servicio, mi hija se siente muy segura. El conductor es muy responsable y amable."
                                            </div>
                                        </div>
                                        
                                        <div class="direct-chat-msg right">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-right">Estudiante</span>
                                                <span class="direct-chat-timestamp float-left">3 días</span>
                                            </div>
                                            <div class="direct-chat-text">
                                                "Me gusta que el bus esté limpio y que el monitor siempre nos cuide."
                                            </div>
                                        </div>
                                        
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-left">Padre de familia</span>
                                                <span class="direct-chat-timestamp float-right">1 día</span>
                                            </div>
                                            <div class="direct-chat-text">
                                                "Sería bueno tener una aplicación para rastrear el bus en tiempo real."
                                            </div>
                                        </div>
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
                            <button type="button" class="btn btn-outline-success btn-lg ml-2">
                                <i class="fas fa-chart-line"></i>
                                Ver Resultados
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg ml-2">
                                <i class="fas fa-map-marked-alt"></i>
                                Consultar Ruta
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-lg ml-2">
                                <i class="fas fa-phone"></i>
                                Contactar
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
    .widget-user-2 .widget-user-header {
        border-radius: 10px 10px 0 0;
    }
    .progress-group {
        margin-bottom: 15px;
    }
    .progress {
        border-radius: 10px;
    }
    .direct-chat-messages {
        border-radius: 10px;
        background: #f8f9fa;
        padding: 15px;
    }
    .direct-chat-text {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('js')
<script>
    console.log('Encuesta Padres de Familia - Transporte cargada correctamente');
</script>
@stop
