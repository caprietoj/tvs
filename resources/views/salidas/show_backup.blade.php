@extends('adminlte::page')

@section('title', 'Detalles de Salida Pedagógica')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-1">
        <div class="col-sm-8">
            <h1 class="h3 text-primary mb-1">
                <i class="fas fa-route mr-2"></i>{{ $salida->consecutivo }} - {{ Str::limit($salida->lugar, 30) }}
            </h1>
            <div>
                @if($salida->estado === 'Programada')
                    <span class="badge badge-primary">
                        <i class="fas fa-calendar-check mr-1"></i>{{ $salida->estado }}
                    </span>
                @elseif($salida->estado === 'Realizada')
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle mr-1"></i>{{ $salida->estado }}
                    </span>
                @else
                    <span class="badge badge-danger">
                        <i class="fas fa-times-circle mr-1"></i>{{ $salida->estado }}
                    </span>
                @endif
                
                @php
                    $servicios = [
                        'transporte' => $salida->transporte_confirmado,
                        'alimentacion' => $salida->requiere_alimentacion ? $salida->alimentacion_confirmada : true,
                        'enfermeria' => $salida->requiere_enfermeria ? $salida->enfermeria_confirmada : true,
                        'accesos' => $salida->accesos_confirmados,
                        'comunicaciones' => $salida->requiere_comunicaciones ? $salida->comunicaciones_confirmada : true,
                        'arl' => $salida->requiere_arl ? $salida->arl_confirmado : true
                    ];
                    $confirmados = collect($servicios)->filter()->count();
                    $total = count($servicios);
                    $porcentaje = ($confirmados / $total) * 100;
                @endphp
                
                <span class="badge badge-{{ $porcentaje == 100 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger') }} ml-2">
                    {{ round($porcentaje) }}% ({{ $confirmados }}/{{ $total }})
                </span>
            </div>
        </div>
        <div class="col-sm-4 text-right">
            @if(!auth()->user()->hasRole('profesor'))
            <a href="{{ route('salidas.edit', $salida) }}" class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit"></i> Editar
            </a>
            @endif
            <a href="{{ route('salidas.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmServiceModal" tabindex="-1" role="dialog" aria-labelledby="confirmServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="confirmServiceModalLabel">
                    <i class="fas fa-question-circle mr-2"></i>Confirmar Servicio
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>¿Estás seguro de que quieres confirmar este servicio?</h5>
                    <p class="text-muted">Esta acción marcará el servicio como confirmado y registrará tu usuario como responsable de la confirmación.</p>
                    <div class="mt-3">
                        <strong>Servicio: </strong><span id="serviceTitle"></span><br>
                        <strong>Usuario: </strong>{{ auth()->user()->name }}<br>
                        <strong>Fecha: </strong>{{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="confirmServiceBtn">
                    <i class="fas fa-check mr-1"></i>Confirmar Servicio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Éxito -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>Confirmación Exitosa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="text-success mb-3">¡Servicio Confirmado!</h4>
                    <p id="successMessage" class="lead"></p>
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>Confirmado por: </strong>{{ auth()->user()->name }}<br>
                        <strong>Fecha y hora: </strong><span id="confirmationTime"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">
                    <i class="fas fa-thumbs-up mr-1"></i>Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Error -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                    <h4 class="text-danger mb-3">Error en la Confirmación</h4>
                    <p id="errorMessage" class="lead"></p>
                    <p class="text-muted">Por favor, intenta nuevamente. Si el problema persiste, contacta al administrador del sistema.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
    <div class="container-fluid">
        <!-- Información Básica -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-body p-2">
                        <div class="row text-sm">
                            <div class="col-md-2">
                                <strong>Responsable:</strong><br>
                                <small>{{ $salida->responsable->name }}</small>
                            </div>
                            <div class="col-md-2">
                                <strong>Grados:</strong><br>
                                <span class="badge badge-info badge-sm">{{ $salida->grados }}</span>
                            </div>
                            <div class="col-md-2">
                                <strong>Fecha:</strong><br>
                                <small>{{ \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>Horario:</strong><br>
                                <small>{{ \Carbon\Carbon::parse($salida->fecha_salida)->format('H:i') }} - {{ \Carbon\Carbon::parse($salida->fecha_regreso)->format('H:i') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>Lugar:</strong><br>
                                <small>{{ Str::limit($salida->lugar, 30) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                        <strong>Fecha de Salida:</strong>
                                    </div>
                                    <p>{{ $salida->fecha_salida->format('d/m/Y H:i') }}</p>
                                </div>
                                
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check text-primary mr-2"></i>
                                        <strong>Fecha de Regreso:</strong>
                                    </div>
                                    <p>{{ $salida->fecha_regreso->format('d/m/Y H:i') }}</p>
                                </div>
                                
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                        <strong>Destino:</strong>
                                    </div>
                                    <p>{{ $salida->lugar }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($salida->observaciones)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong><i class="fas fa-comment-alt text-primary mr-1"></i>Observaciones:</strong>
                                <div class="bg-light p-2 rounded mt-1">
                                    <small>{{ $salida->observaciones }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios - Compacto -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold">
                            <i class="fas fa-clipboard-check mr-1"></i>Estado de Servicios
                        </h3>
                    </div>
                    <div class="card-body p-2">
                        <div class="row text-sm">
                            <!-- Transporte -->
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-bus mr-1"></i>Transporte</span>
                                    @if($salida->transporte_confirmado)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="transporte" 
                                                data-title="Transporte">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Alimentación -->
                            @if($salida->requiere_alimentacion)
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-utensils mr-1"></i>Alimentación</span>
                                    @if($salida->alimentacion_confirmada)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="alimentacion" 
                                                data-title="Alimentación">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Enfermería -->
                            @if($salida->requiere_enfermeria)
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-plus-circle mr-1"></i>Enfermería</span>
                                    @if($salida->enfermeria_confirmada)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="enfermeria" 
                                                data-title="Enfermería">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Accesos -->
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-key mr-1"></i>Accesos</span>
                                    @if($salida->accesos_confirmados)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="accesos" 
                                                data-title="Control de Accesos">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Comunicaciones -->
                            @if($salida->requiere_comunicaciones)
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-bullhorn mr-1"></i>Comunicaciones</span>
                                    @if($salida->comunicaciones_confirmada)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="comunicaciones" 
                                                data-title="Comunicaciones">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- ARL -->
                            @if($salida->requiere_arl)
                            <div class="col-md-2 mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-medkit mr-1"></i>ARL</span>
                                    @if($salida->arl_confirmado)
                                        <span class="badge badge-success badge-sm">OK</span>
                                    @else
                                        <button class="btn btn-warning btn-xs confirm-service-btn" 
                                                data-service="arl" 
                                                data-title="ARL">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
                        
                        <div class="text-center mb-2">
                            <h5 class="text-{{ $porcentaje == 100 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger') }}">
                                {{ $confirmados }} de {{ $total }} servicios confirmados
                            </h5>
                        </div>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-bus mr-1"></i>Transporte</span>
                                <span class="badge badge-{{ $salida->transporte_confirmado ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->transporte_confirmado ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @if($salida->requiere_alimentacion)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-utensils mr-1"></i>Alimentación</span>
                                <span class="badge badge-{{ $salida->alimentacion_confirmada ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->alimentacion_confirmada ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @endif
                            @if($salida->requiere_enfermeria)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-plus-circle mr-1"></i>Enfermería</span>
                                <span class="badge badge-{{ $salida->enfermeria_confirmada ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->enfermeria_confirmada ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @endif
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-key mr-1"></i>Accesos</span>
                                <span class="badge badge-{{ $salida->accesos_confirmados ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->accesos_confirmados ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @if($salida->requiere_comunicaciones)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-bullhorn mr-1"></i>Comunicaciones</span>
                                <span class="badge badge-{{ $salida->comunicaciones_confirmada ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->comunicaciones_confirmada ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @endif
                            @if($salida->requiere_arl)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-medkit mr-1"></i>ARL</span>
                                <span class="badge badge-{{ $salida->arl_confirmado ? 'success' : 'warning' }} badge-pill">
                                    {{ $salida->arl_confirmado ? 'Confirmado' : 'Pendiente' }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                    <div id="timeline-container" class="timeline timeline-inverse p-3">
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
                                <span class="time">
                                    @if($salida->transporte_confirmado_at)
                                        <i class="far fa-clock"></i> {{ $salida->transporte_confirmado_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">Transporte Confirmado</h3>
                                @if($salida->transporteConfirmadoPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->transporteConfirmadoPor->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($salida->requiere_alimentacion)
                        <div>
                            <i class="fas fa-utensils bg-{{ $salida->alimentacion_confirmada ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($salida->alimentacion_confirmada && $salida->alimentacion_confirmada_at)
                                        <i class="far fa-clock"></i> {{ $salida->alimentacion_confirmada_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">Alimentación {{ $salida->alimentacion_confirmada ? 'Confirmada' : 'Pendiente' }}</h3>
                                @if($salida->alimentacion_confirmada && $salida->alimentacionConfirmadaPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->alimentacionConfirmadaPor->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($salida->requiere_enfermeria)
                        <div>
                            <i class="fas fa-plus-circle bg-{{ $salida->enfermeria_confirmada ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($salida->enfermeria_confirmada && $salida->enfermeria_confirmada_at)
                                        <i class="far fa-clock"></i> {{ $salida->enfermeria_confirmada_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">Enfermería {{ $salida->enfermeria_confirmada ? 'Confirmada' : 'Pendiente' }}</h3>
                                @if($salida->enfermeria_confirmada && $salida->enfermeriaConfirmadaPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->enfermeriaConfirmadaPor->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div>
                            <i class="fas fa-key bg-{{ $salida->accesos_confirmados ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($salida->accesos_confirmados && $salida->accesos_confirmados_at)
                                        <i class="far fa-clock"></i> {{ $salida->accesos_confirmados_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">Accesos {{ $salida->accesos_confirmados ? 'Confirmados' : 'Pendientes' }}</h3>
                                @if($salida->accesos_confirmados && $salida->accesosConfirmadosPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->accesosConfirmadosPor->name }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($salida->requiere_comunicaciones)
                        <div>
                            <i class="fas fa-broadcast-tower bg-{{ $salida->comunicaciones_confirmada ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($salida->comunicaciones_confirmada && $salida->comunicaciones_confirmado_at)
                                        <i class="far fa-clock"></i> {{ $salida->comunicaciones_confirmado_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">Comunicaciones {{ $salida->comunicaciones_confirmada ? 'Confirmadas' : 'Pendientes' }}</h3>
                                @if($salida->comunicaciones_confirmada && $salida->comunicacionesConfirmadoPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->comunicacionesConfirmadoPor->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($salida->requiere_arl)
                        <div>
                            <i class="fas fa-shield-alt bg-{{ $salida->arl_confirmado ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($salida->arl_confirmado && $salida->arl_confirmado_at)
                                        <i class="far fa-clock"></i> {{ $salida->arl_confirmado_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">ARL {{ $salida->arl_confirmado ? 'Confirmado' : 'Pendiente' }}</h3>
                                @if($salida->arl_confirmado && $salida->arlConfirmadoPor)
                                    <div class="timeline-body">
                                        Confirmado por: {{ $salida->arlConfirmadoPor->name }}
                                    </div>
                                @endif
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
            'service' => 'transporte',
            'salidaId' => $salida->id,
            'details' => [
                'Hora Salida' => $salida->hora_salida_bus,
                'Hora Regreso' => $salida->hora_regreso_bus
            ],
            'confirmData' => $salida->transporte_confirmado ? [
                'user' => $salida->transporteConfirmadoPor->name ?? 'N/A',
                'date' => $salida->transporte_confirmado_at ? $salida->transporte_confirmado_at->format('d/m/Y H:i') : 'N/A'
            ] : null
        ])

        @if($salida->requiere_alimentacion)
            @include('salidas.partials.service-card', [
                'icon' => 'utensils',
                'title' => 'Alimentación',
                'status' => $salida->alimentacion_confirmada,
                'service' => 'alimentacion',
                'salidaId' => $salida->id,
                'details' => [
                    'Snacks' => $salida->cantidad_snacks,
                    'Almuerzos' => $salida->cantidad_almuerzos,
                    'Menú' => $salida->menu_sugerido
                ],
                'confirmData' => $salida->alimentacion_confirmada ? [
                    'user' => $salida->alimentacionConfirmadaPor->name ?? 'N/A',
                    'date' => $salida->alimentacion_confirmada_at ? $salida->alimentacion_confirmada_at->format('d/m/Y H:i') : 'N/A'
                ] : null
            ])
        @endif

        @if($salida->requiere_enfermeria)
            @include('salidas.partials.service-card', [
                'icon' => 'heartbeat',
                'title' => 'Enfermería',
                'status' => $salida->enfermeria_confirmada,
                'service' => 'enfermeria',
                'salidaId' => $salida->id,
                'details' => [],
                'confirmData' => $salida->enfermeria_confirmada ? [
                    'user' => $salida->enfermeriaConfirmadaPor->name ?? 'N/A',
                    'date' => $salida->enfermeria_confirmada_at ? $salida->enfermeria_confirmada_at->format('d/m/Y H:i') : 'N/A'
                ] : null
            ])
        @endif
        
        @if($salida->requiere_comunicaciones)
            @include('salidas.partials.service-card', [
                'icon' => 'bullhorn',
                'title' => 'Comunicaciones',
                'status' => $salida->comunicaciones_confirmada,
                'service' => 'comunicaciones',
                'salidaId' => $salida->id,
                'details' => [
                    'Observaciones' => $salida->observaciones_comunicaciones
                ],
                'confirmData' => $salida->comunicaciones_confirmada ? [
                    'user' => $salida->comunicacionesConfirmadoPor->name ?? 'N/A',
                    'date' => $salida->comunicaciones_confirmado_at ? $salida->comunicaciones_confirmado_at->format('d/m/Y H:i') : 'N/A'
                ] : null
            ])
        @endif
        
        @if($salida->requiere_arl)
            @include('salidas.partials.service-card', [
                'icon' => 'medkit',
                'title' => 'Reporte ARL',
                'status' => $salida->arl_confirmado,
                'service' => 'arl',
                'salidaId' => $salida->id,
                'details' => [
                    'Estado' => 'Reportado a Gestión Humana'
                ],
                'confirmData' => $salida->arl_confirmado ? [
                    'user' => $salida->arlConfirmadoPor->name ?? 'N/A',
                    'date' => $salida->arl_confirmado_at ? $salida->arl_confirmado_at->format('d/m/Y H:i') : 'N/A'
                ] : null
            ])
        @endif

        <!-- Tarjeta de Accesos -->
        @include('salidas.partials.service-card', [
            'icon' => 'key',
            'title' => 'Control de Accesos',
            'status' => $salida->accesos_confirmados,
            'service' => 'accesos',
            'salidaId' => $salida->id,
            'details' => [
                'Hora Apertura' => $salida->hora_apertura_puertas
            ],
            'confirmData' => $salida->accesos_confirmados ? [
                'user' => $salida->accesosConfirmadosPor->name ?? 'N/A',
                'date' => $salida->accesos_confirmados_at ? $salida->accesos_confirmados_at->format('d/m/Y H:i') : 'N/A'
            ] : null
        ])

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
    /* Estilos básicos mejorados */
    .content-header h1 {
        color: #364E76;
        font-size: 1.8rem;
    }
    
    .badge-lg {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
    
    /* Cards compactas */
    .card {
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .card-outline.card-primary {
        border-top: 3px solid #364E76;
    }
    
    .card-outline.card-info {
        border-top: 3px solid #17a2b8;
    }
    
    .card-header { 
        background-color: #364E76 !important; 
        color: white;
        padding: 0.75rem 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    /* Información compacta */
    .info-item {
        margin-bottom: 1rem;
    }
    
    .info-item .d-flex {
        margin-bottom: 0.5rem;
    }
    
    .info-item p {
        margin-bottom: 0;
        margin-left: 1.5rem;
    }
    
    /* Timeline compacto */
    .timeline { 
        margin: 0; 
        padding: 0; 
        position: relative; 
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #364E76;
        left: 31px;
        margin: 0;
    }
    
    .timeline-item {
        background: white;
        border-radius: 5px;
        padding: 10px;
        margin: 5px 0;
        border-left: 3px solid #364E76;
    }
    
    /* Service Cards */
    .service-card {
        transition: transform 0.2s ease;
        cursor: pointer;
        margin-bottom: 1rem;
    }
    
    .service-card:hover {
        transform: translateY(-2px);
    }
    
    .clickable-pending {
        cursor: pointer;
        border: 2px dashed #ffc107 !important;
    }
    
    .clickable-pending:hover {
        border-color: #28a745 !important;
    }
    
    .clickable-pending .card-header {
        background-color: #ffc107 !important;
    }
    
    .clickable-pending:hover .card-header {
        background-color: #28a745 !important;
    }
    
    /* Botones */
    .btn-primary { 
        background-color: #364E76; 
        border-color: #364E76; 
    }
    
    .btn-primary:hover { 
        background-color: #2B3E5F; 
        border-color: #2B3E5F; 
    }
    
    /* Progreso */
    .progress {
        height: 20px;
        border-radius: 10px;
    }
    
    .progress-bar {
        transition: width 0.6s ease;
    }
    
    /* Lista compacta */
    .list-group-item {
        padding: 0.5rem 0;
        border: none;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    /* Información de confirmación */
    .confirmation-info {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
        border-left: 3px solid #28a745;
    }
    
    /* Responsive - más compacto en móviles */
    @media (max-width: 768px) {
        .content-header h1 {
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .info-item {
            margin-bottom: 0.75rem;
        }
        
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
    }
    
    /* Modales simples */
    .modal-content {
        border-radius: 8px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let currentCard = null;
    let currentService = null;
    let currentSalidaId = null;
    
    // Toggle collapse para todas las tarjetas
    $('.service-card:not(.clickable-pending)').click(function() {
        $(this).find('.collapse').collapse('toggle');
    });
    
    // Click para confirmar servicios pendientes
    $('.clickable-pending').click(function(e) {
        e.preventDefault();
        
        currentCard = $(this);
        currentService = currentCard.data('service');
        currentSalidaId = currentCard.data('salida-id');
        
        if (!currentService || !currentSalidaId) {
            console.error('Faltan datos de servicio o salida ID');
            return;
        }
        
        // Establecer el título del servicio en el modal
        const serviceTitle = currentCard.find('.card-title').text().trim();
        $('#serviceTitle').text(serviceTitle);
        
        // Mostrar el modal
        $('#confirmServiceModal').modal('show');
    });
    
    // Función para actualizar la línea de tiempo
    function updateTimeline(service, confirmedBy, confirmedAt) {
        let icon, title;
        
        switch(service) {
            case 'transporte':
                icon = 'fas fa-bus';
                title = 'Transporte Confirmado';
                break;
            case 'alimentacion':
                icon = 'fas fa-utensils';
                title = 'Alimentación Confirmada';
                break;
            case 'enfermeria':
                icon = 'fas fa-plus-circle';
                title = 'Enfermería Confirmada';
                break;
            case 'accesos':
                icon = 'fas fa-key';
                title = 'Accesos Confirmados';
                break;
            case 'comunicaciones':
                icon = 'fas fa-broadcast-tower';
                title = 'Comunicaciones Confirmadas';
                break;
            case 'arl':
                icon = 'fas fa-shield-alt';
                title = 'ARL Confirmado';
                break;
        }
        
        // Buscar si el elemento ya existe en el timeline
        const timelineElement = $(`#timeline-container i.${icon.replace(' ', '.')}`).closest('div');
        
        if (timelineElement.length > 0) {
            // Actualizar elemento existente
            timelineElement.find('i').removeClass('bg-warning').addClass('bg-success');
            timelineElement.find('.timeline-header').text(title);
            
            // Agregar o actualizar la hora
            const timeSpan = timelineElement.find('.time');
            if (timeSpan.length > 0) {
                timeSpan.html(`<i class="far fa-clock"></i> ${confirmedAt.split(' ')[1]}`);
            } else {
                timelineElement.find('.timeline-item').prepend(`
                    <span class="time">
                        <i class="far fa-clock"></i> ${confirmedAt.split(' ')[1]}
                    </span>
                `);
            }
            
            // Agregar o actualizar información del usuario
            let timelineBody = timelineElement.find('.timeline-body');
            if (timelineBody.length > 0) {
                timelineBody.text(`Confirmado por: ${confirmedBy}`);
            } else {
                timelineElement.find('.timeline-item').append(`
                    <div class="timeline-body">
                        Confirmado por: ${confirmedBy}
                    </div>
                `);
            }
        }
    }
    
    // Función para actualizar el panel de progreso
    function updateProgressPanel(service) {
        // Mapear el servicio al selector del badge
        let badgeSelector = '';
        switch(service) {
            case 'transporte':
                badgeSelector = '.list-group-item:contains("Transporte") .badge';
                break;
            case 'alimentacion':
                badgeSelector = '.list-group-item:contains("Alimentación") .badge';
                break;
            case 'enfermeria':
                badgeSelector = '.list-group-item:contains("Enfermería") .badge';
                break;
            case 'accesos':
                badgeSelector = '.list-group-item:contains("Accesos") .badge';
                break;
            case 'comunicaciones':
                badgeSelector = '.list-group-item:contains("Comunicaciones") .badge';
                break;
            case 'arl':
                badgeSelector = '.list-group-item:contains("ARL") .badge';
                break;
        }
        
        if (badgeSelector) {
            $(badgeSelector).removeClass('badge-warning')
                           .addClass('badge-success')
                           .text('Confirmado');
        }
    }
    
    // Función para actualizar el progreso general en el header
    function updateHeaderProgress() {
        // Contar servicios confirmados
        let confirmados = 0;
        let total = 0;
        
        // Contar desde los badges del panel de progreso
        $('.list-group-item .badge').each(function() {
            total++;
            if ($(this).hasClass('badge-success')) {
                confirmados++;
            }
        });
        
        let porcentaje = (confirmados / total) * 100;
        
        // Actualizar la barra de progreso
        $('.progress-bar').css('width', porcentaje + '%')
                         .attr('aria-valuenow', porcentaje)
                         .text(Math.round(porcentaje) + '%');
        
        // Actualizar las clases de color
        $('.progress-bar').removeClass('bg-success bg-warning bg-danger');
        if (porcentaje == 100) {
            $('.progress-bar').addClass('bg-success');
        } else if (porcentaje >= 50) {
            $('.progress-bar').addClass('bg-warning');
        } else {
            $('.progress-bar').addClass('bg-danger');
        }
        
        // Actualizar el texto del contador
        $('.card-body h4').text(`${confirmados} de ${total} servicios confirmados`)
                         .removeClass('text-success text-warning text-danger');
        
        if (porcentaje == 100) {
            $('.card-body h4').addClass('text-success');
        } else if (porcentaje >= 50) {
            $('.card-body h4').addClass('text-warning');
        } else {
            $('.card-body h4').addClass('text-danger');
        }
        
        // Actualizar el badge en el header principal
        let headerBadge = $('.content-header .badge:contains("%")');
        if (headerBadge.length > 0) {
            headerBadge.removeClass('badge-success badge-warning badge-danger');
            if (porcentaje == 100) {
                headerBadge.addClass('badge-success');
            } else if (porcentaje >= 50) {
                headerBadge.addClass('badge-warning');
            } else {
                headerBadge.addClass('badge-danger');
            }
            headerBadge.html(`<i class="fas fa-tasks mr-1"></i>${Math.round(porcentaje)}% Completado (${confirmados}/${total})`);
        }
    }

    $('#confirmServiceBtn').click(function() {
        if (!currentCard || !currentService || !currentSalidaId) {
            console.error('No hay datos de confirmación disponibles');
            return;
        }
        
        // Cerrar el modal
        $('#confirmServiceModal').modal('hide');
        
        // Deshabilitar la tarjeta temporalmente
        currentCard.addClass('disabled').css('pointer-events', 'none');
        
        // Hacer la petición AJAX
        $.ajax({
            url: `/salidas/${currentSalidaId}/confirmar-${currentService}`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar la interfaz
                    currentCard.removeClass('clickable-pending disabled')
                             .css('pointer-events', 'auto')
                             .find('.badge')
                             .removeClass('badge-warning')
                             .addClass('badge-success')
                             .text('Confirmado');
                    
                    // Remover el texto de "Clic para confirmar"
                    currentCard.find('.card-tools small').remove();
                    
                    // Actualizar el header color
                    currentCard.find('.card-header').css('background-color', '#364E76');
                    
                    // Expandir el collapse para mostrar detalles
                    currentCard.find('.collapse').addClass('show');
                    
                    // Agregar información de confirmación
                    const confirmInfo = `
                        <div class="confirmation-info mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Confirmado por: <strong>${response.confirmed_by}</strong><br>
                                <i class="fas fa-clock"></i> Fecha: <strong>${response.confirmed_at}</strong>
                            </small>
                        </div>
                    `;
                    currentCard.find('.card-body').append(confirmInfo);
                    
                    // Actualizar la línea de tiempo
                    updateTimeline(currentService, response.confirmed_by, response.confirmed_at);
                    
                    // Actualizar el panel de progreso
                    updateProgressPanel(currentService);
                    
                    // Actualizar el badge de progreso en el header
                    updateHeaderProgress();
                    
                    // Mostrar modal de éxito
                    $('#successMessage').text(response.message);
                    $('#confirmationTime').text(response.confirmed_at);
                    $('#successModal').modal('show');
                    
                    // Reasignar el click handler para toggle
                    currentCard.off('click').click(function() {
                        $(this).find('.collapse').collapse('toggle');
                    });
                    
                    // Limpiar variables
                    currentCard = null;
                    currentService = null;
                    currentSalidaId = null;
                } else {
                    throw new Error(response.message || 'Error desconocido');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                let errorMessage = 'Error al confirmar el servicio';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Mostrar modal de error
                $('#errorMessage').text(errorMessage);
                $('#errorModal').modal('show');
                
                // Rehabilitar la tarjeta
                currentCard.removeClass('disabled').css('pointer-events', 'auto');
                
                // Limpiar variables
                currentCard = null;
                currentService = null;
                currentSalidaId = null;
            }
        });
    });
    
    // Limpiar variables cuando se cierra el modal sin confirmar
    $('#confirmServiceModal').on('hidden.bs.modal', function () {
        currentCard = null;
        currentService = null;
        currentSalidaId = null;
    });
});
</script>
@stop
