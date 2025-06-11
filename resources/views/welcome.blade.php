@extends('adminlte::page')

@section('title', 'Dashboard TVS')

@section('content')
<div class="container-fluid welcome-container">
    <!-- Ciclo Escolar Indicator -->
    @if($activeCycle && $currentCycleDay)
    <div class="cycle-day-indicator">
        <div class="cycle-day-label">Día actual del ciclo escolar</div>
        <div class="cycle-day-number">{{ $currentCycleDay->cycle_day }}</div>
    </div>
    @endif
    
    <!-- Header Section with Logo -->
    <div class="welcome-header">
        <img src="{{ asset('img/the_victoria.png') }}" alt="Logo Victoria School" class="welcome-logo">
        <div class="welcome-title">
            <h1>Portal Institucional</h1>
            <p class="welcome-subtitle">Bienvenido(a), {{ Auth::user()->name }}</p>
        </div>
    </div>
    @can('view-admin-dashboard')
    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon"><i class="fas fa-ticket-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tickets Activos</span>
                    <span class="info-box-number">{{ App\Models\Ticket::where('estado', 'Abierto')->count() }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Eventos Programados</span>
                    <span class="info-box-number">{{ App\Models\Event::whereDate('service_date', '>=', now())->count() }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Solicitudes Pendientes</span>
                    <span class="info-box-number">{{ App\Models\DocumentRequest::where('status', 'pending')->count() }}</span>
                </div>
            </div>
        </div>
        @endcan
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Hora Local</span>
                    <span class="info-box-number" id="current-time">00:00:00</span>
                    <span class="info-box-text" id="current-date">Cargando fecha...</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de Accesos Rápidos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #364E76 0%, #4a6494 100%); color: white;">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-rocket mr-2"></i>Accesos Rápidos
                    </h3>
                </div>
                <div class="card-body" style="background: #f8f9fa;">
                    <!-- Gestión Académica - Accesible para todos los roles -->
                    @can('view.space-reservations')
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-graduation-cap mr-2" style="color: #364E76;"></i>Gestión Académica
                            </h5>
                        </div>
                        <!-- Reservas de Espacios -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('space-reservations.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Reservas de Espacios</h6>
                                        <p>Gestionar reservas de aulas y espacios</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <!-- Préstamo de Equipos -->
                        @can('view.reservas')
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('equipment.loans') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Préstamo de Equipos</h6>
                                        <p>Solicitar y gestionar equipos</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        <!-- Eventos -->
                        @can('view.events')
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('events.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Eventos</h6>
                                        <p>Ver y gestionar eventos institucionales</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        <!-- Salidas Pedagógicas -->
                        @can('view.salidas')
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('salidas.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Salidas Pedagógicas</h6>
                                        <p>Gestionar salidas educativas</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endcan
                    </div>
                    @endcan

                    <!-- Proceso Administrativo -->
                    @if(auth()->user()->hasRole('admin'))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-cogs mr-2" style="color: #364E76;"></i>Proceso Administrativo
                            </h5>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('tickets.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Help Desk</h6>
                                        <p>Crear y gestionar tickets de soporte</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('copies-requests.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-copy"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Fotocopias</h6>
                                        <p>Gestionar solicitudes de fotocopias</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('maintenance.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Mantenimiento</h6>
                                        <p>Solicitudes de mantenimiento</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Solicitudes de Compra - Visible para admin y profesor -->
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('profesor'))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-shopping-cart mr-2" style="color: #364E76;"></i>Solicitudes
                            </h5>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('purchase-requests.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Solicitudes de Compra</h6>
                                        <p>Realizar solicitudes de materiales</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Análisis y Reportes - Solo Admin -->
                    @if(auth()->user()->hasRole('admin'))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-chart-line mr-2" style="color: #364E76;"></i>Análisis y Reportes
                            </h5>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('kpi-report.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Reportes KPI</h6>
                                        <p>Indicadores de rendimiento</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('attendance.dashboard') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Dashboard Asistencia</h6>
                                        <p>Control de asistencia del personal</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('weekly-biometric.dashboard') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Biométrico Semanal</h6>
                                        <p>Análisis biométrico semanal</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('photocopies.dashboard') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-print"></i>
                                    </div>
                                    <div class="content">
                                        <h6>Dashboard Fotocopias</h6>
                                        <p>Estadísticas de fotocopias</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Gestión por Áreas - Solo Admin -->
                    @if(auth()->user()->hasRole('admin'))
                    <div class="row">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-building mr-2" style="color: #364E76;"></i>Gestión por Áreas
                            </h5>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('kpis.enfermeria.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <div class="content">
                                        <h6>KPIs Enfermería</h6>
                                        <p>Indicadores del área de enfermería</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('kpis.compras.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="content">
                                        <h6>KPIs Compras</h6>
                                        <p>Indicadores del área de compras</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('kpis.rrhh.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="content">
                                        <h6>KPIs RRHH</h6>
                                        <p>Indicadores de recursos humanos</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('kpis.sistemas.index') }}" class="quick-access-card">
                                <div class="quick-access-item">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <div class="content">
                                        <h6>KPIs Sistemas</h6>
                                        <p>Indicadores del área de sistemas</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Announcements Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card announcement-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn mr-2"></i>Avisos Importantes
                    </h3>
                </div>
                <div class="card-body">
                    @if($announcements->count() > 0)
                        <div id="announcements-carousel" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                @foreach($announcements as $index => $announcement)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <div class="announcement-content">
                                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                                            <div class="announcement-body">
                                                {!! $announcement->content !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($announcements->count() > 1)
                                <button class="carousel-control-prev" type="button" data-target="#announcements-carousel" data-slide="prev">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="carousel-control-next" type="button" data-target="#announcements-carousel" data-slide="next">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle mr-2"></i>No hay avisos importantes en este momento.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --secondary: #ED3236;
        --light: #FEFEFE;
    }

    .welcome-container {
        padding: 2rem;
    }

    .welcome-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        background: linear-gradient(135deg, var(--primary) 0%, #1a2036 100%);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .welcome-logo {
        height: 100px;
        object-fit: contain;
    }

    .welcome-title {
        color: var(--light);
    }

    .welcome-title h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .welcome-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin: 0;
    }

    .info-box {
        display: flex;
        background: var(--light);
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-5px);
    }

    .info-box-icon {
        background: var(--primary);
        color: var(--light);
        width: 70px;
        height: 70px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-right: 1rem;
    }

    .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .info-box-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary);
    }

    .info-box-text {
        color: #666;
        font-size: 1rem;
    }

    .announcement-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .announcement-card .card-header {
        background: var(--primary);
        color: var(--light);
        padding: 1rem 1.5rem;
    }

    .announcement-content {
        padding: 2rem;
    }

    .announcement-title {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--secondary);
    }

    .announcement-body {
        color: #444;
        line-height: 1.6;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 40px;
        height: 40px;
        background: var(--primary);
        border-radius: 50%;
        opacity: 0.8;
        top: 50%;
        transform: translateY(-50%);
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1;
        background: var(--secondary);
    }

    .cycle-day-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(54, 78, 118, 0.9);
        color: #fff;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 1000;
    }

    .cycle-day-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .cycle-day-number {
        font-size: 1.2rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .welcome-header {
            flex-direction: column;
            text-align: center;
        }

        .welcome-logo {
            height: 80px;
        }

        .welcome-title h1 {
            font-size: 2rem;
        }

        .cycle-day-indicator {
            position: static;
            margin-top: 1rem;
            border-radius: 10px;
        }
    }

    /* Estilos para los accesos rápidos */
    .quick-access-card {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .quick-access-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        min-height: 100px;
        position: relative;
        overflow: hidden;
    }

    .quick-access-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(54, 78, 118, 0.15);
        border-color: var(--primary);
    }

    .quick-access-item .icon-wrapper {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary), #4a6494);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .quick-access-item .icon-wrapper i {
        font-size: 24px;
        color: white;
    }

    .quick-access-item .content {
        flex: 1;
    }

    .quick-access-item .content h6 {
        margin: 0 0 5px 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        line-height: 1.2;
    }

    .quick-access-item .content p {
        margin: 0;
        font-size: 13px;
        color: #6c757d;
        line-height: 1.3;
    }

    .quick-access-card:hover {
        text-decoration: none;
        color: inherit;
    }

    .quick-access-card:hover .quick-access-item .content h6 {
        color: var(--primary);
    }

    .quick-access-item:hover .icon-wrapper {
        transform: scale(1.1);
        background: linear-gradient(135deg, #4a6494, var(--primary));
    }

    .quick-access-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(54, 78, 118, 0.03) 100%);
        border-radius: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .quick-access-item:hover::before {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .quick-access-item {
            padding: 15px;
            min-height: 85px;
        }
        
        .quick-access-item .icon-wrapper {
            width: 40px;
            height: 40px;
            margin-right: 12px;
        }
        
        .quick-access-item .icon-wrapper i {
            font-size: 20px;
        }
        
        .quick-access-item .content h6 {
            font-size: 14px;
        }
        
        .quick-access-item .content p {
            font-size: 12px;
        }
    }
</style>
@stop

@section('js')
<script>
function updateDateTime() {
    const now = new Date();
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: false 
    };
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    
    document.getElementById('current-time').textContent = 
        now.toLocaleTimeString('es-CO', timeOptions);
    document.getElementById('current-date').textContent = 
        now.toLocaleDateString('es-CO', dateOptions).replace(/^\w/, (c) => c.toUpperCase());
}

setInterval(updateDateTime, 1000);
updateDateTime();

$(document).ready(function() {
    // Initialize carousel with custom options
    $('#announcements-carousel').carousel({
        interval: 8000,
        pause: "hover",
        keyboard: true
    });
});
</script>
@stop
