@extends('adminlte::page')

@section('title', 'Detalles de Salida Pedagógica')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="text-primary">
                <i class="fas fa-bus mr-2"></i>Salida Pedagógica {{ $salida->consecutivo }}
            </h1>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                @if(!auth()->user()->hasRole('profesor'))
                <a href="{{ route('salidas.edit', $salida) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Salida
                </a>
                @endif
                <a href="{{ route('salidas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Estado General -->
    <div class="row">
        <div class="col-md-12">
            <div class="info-box">
                <span class="info-box-icon bg-{{ $salida->estado === 'Programada' ? 'primary' : 
                    ($salida->estado === 'Realizada' ? 'success' : 'danger') }}">
                    <i class="fas fa-{{ $salida->estado === 'Programada' ? 'clock' : 
                        ($salida->estado === 'Realizada' ? 'check' : 'times') }}"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Estado de la Salida</span>
                    <span class="info-box-number">{{ $salida->estado }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-{{ $salida->estado === 'Programada' ? 'primary' : 
                            ($salida->estado === 'Realizada' ? 'success' : 'danger') }}" 
                            style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Detalles Principales -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información General</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Responsable</label>
                                <p class="h5">{{ $salida->responsable->name }}</p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Lugar</label>
                                <p class="h5">{{ $salida->lugar }}</p>
                            </div>
                            <div class="info-group">
                                <label class="text-muted">Grados</label>
                                <p class="h5">{{ $salida->grados }}</p>
                            </div>
                            <div class="info-group mt-3">
                                <label class="text-muted">Visita de Inspección</label>
                                <p class="h5">
                                    @if($salida->visita_inspeccion)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Requerida</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-times-circle"></i> No requerida</span>
                                    @endif
                                </p>
                            </div>
                            @if($salida->visita_inspeccion && $salida->detalles_inspeccion)
                            <div class="info-group mt-2">
                                <label class="text-muted">Detalles de la Inspección</label>
                                <p>{{ $salida->detalles_inspeccion }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Fecha de Salida</label>
                                <p class="h5">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $salida->fecha_salida->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Fecha de Regreso</label>
                                <p class="h5">
                                    <i class="far fa-calendar-check mr-1"></i>
                                    {{ $salida->fecha_regreso->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="info-group">
                                <label class="text-muted">Cantidad de Pasajeros</label>
                                <p class="h5">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $salida->cantidad_pasajeros }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline de Estados -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Línea de Tiempo</h3>
                </div>
                <div class="card-body p-0">
                    <div class="timeline timeline-inverse p-3">
                        <!-- Fecha de Solicitud -->
                        <div class="time-label">
                            <span class="bg-primary">{{ $salida->fecha_solicitud->format('d M Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-file bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> Creación</span>
                                <h3 class="timeline-header">Solicitud Registrada</h3>
                            </div>
                        </div>

                        <!-- Estados de Confirmación -->
                        @if($salida->transporte_confirmado)
                        <div>
                            <i class="fas fa-bus bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Transporte Confirmado</h3>
                            </div>
                        </div>
                        @endif

                        @if($salida->requiere_alimentacion)
                        <div>
                            <i class="fas fa-utensils bg-{{ $salida->alimentacion_confirmada ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Alimentación {{ $salida->alimentacion_confirmada ? 'Confirmada' : 'Pendiente' }}</h3>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Servicios Requeridos -->
    <div class="row">
        @include('salidas.partials.service-card', [
            'icon' => 'bus',
            'title' => 'Transporte',
            'status' => $salida->transporte_confirmado,
            'details' => [
                'Hora Salida' => $salida->hora_salida_bus,
                'Hora Regreso' => $salida->hora_regreso_bus
            ]
        ])

        @if($salida->requiere_alimentacion)
            @include('salidas.partials.service-card', [
                'icon' => 'utensils',
                'title' => 'Alimentación',
                'status' => $salida->alimentacion_confirmada,
                'details' => [
                    'Snacks' => $salida->cantidad_snacks,
                    'Almuerzos' => $salida->cantidad_almuerzos,
                    'Menú' => $salida->menu_sugerido
                ]
            ])
        @endif

        @if($salida->requiere_enfermeria)
            @include('salidas.partials.service-card', [
                'icon' => 'heartbeat',
                'title' => 'Enfermería',
                'status' => $salida->enfermeria_confirmada,
                'details' => []
            ])
        @endif
        
        @if($salida->requiere_comunicaciones)
            @include('salidas.partials.service-card', [
                'icon' => 'bullhorn',
                'title' => 'Comunicaciones',
                'status' => false,
                'details' => [
                    'Observaciones' => $salida->observaciones_comunicaciones
                ]
            ])
        @endif
        
        @if($salida->requiere_arl)
            @include('salidas.partials.service-card', [
                'icon' => 'medkit',
                'title' => 'Reporte ARL',
                'status' => false,
                'details' => [
                    'Estado' => 'Reportado a Gestión Humana'
                ]
            ])
        @endif

        @if($salida->visita_inspeccion)
            @include('salidas.partials.service-card', [
                'icon' => 'search',
                'title' => 'Visita de Inspección',
                'status' => true,
                'details' => [
                    'Detalles' => $salida->detalles_inspeccion
                ]
            ])
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .info-box-icon { font-size: 1.5rem; }
    .info-group label { font-size: 0.9rem; margin-bottom: 0.2rem; }
    .info-group p { margin-bottom: 0; }
    .timeline { margin: 0; padding: 0; position: relative; }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    .time-label { margin-bottom: 1rem; }
    .service-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .service-card:hover {
        transform: translateY(-5px);
    }
    .card-header { 
        background-color: #364E76 !important; 
        color: white;
    }
    .btn-primary { 
        background-color: #364E76; 
        border-color: #364E76; 
    }
    .btn-primary:hover { 
        background-color: #2B3E5F; 
        border-color: #2B3E5F; 
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.service-card').click(function() {
        $(this).find('.collapse').collapse('toggle');
    });
});
</script>
@stop
