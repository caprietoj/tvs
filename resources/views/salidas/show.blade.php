@extends('adminlte::page')

@section('title', 'Detalle de Salida')

@section('content_header')
<div class="content-header" style="background: linear-gradient(135deg, #233e6c 0%, #1a2d56 100%); color: white; padding: 2.5rem 0; margin-bottom: 2rem; position: relative; overflow: hidden;">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.3;"></div>
    <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
    
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-7">
                <div class="header-content">
                    <!-- Title Section -->
                    <div class="title-section mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-wrapper mr-3" style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 12px; backdrop-filter: blur(10px);">
                                <i class="fas fa-route" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h1 class="h2 font-weight-bold mb-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                    {{ $salida->consecutivo }}
                                </h1>
                                <div class="subtitle-badge" style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; display: inline-block;">
                                    <small class="font-weight-medium">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <p class="lead mb-3" style="font-weight: 500; opacity: 0.95;">
                        <i class="fas fa-map-marker-alt mr-2"></i>{{ $salida->lugar }}
                    </p>
                    
                    <!-- Status and Progress -->
                    <div class="d-flex align-items-center flex-wrap">
                        @if($salida->estado === 'Programada')
                            <span class="status-badge badge-programada mr-3 mb-2" style="background: rgba(52, 168, 83, 0.9); padding: 8px 16px; border-radius: 25px; font-size: 0.9rem;">
                                <i class="fas fa-calendar-check mr-2"></i>{{ $salida->estado }}
                            </span>
                        @elseif($salida->estado === 'Realizada')
                            <span class="status-badge badge-realizada mr-3 mb-2" style="background: rgba(52, 168, 83, 0.9); padding: 8px 16px; border-radius: 25px; font-size: 0.9rem;">
                                <i class="fas fa-check-circle mr-2"></i>{{ $salida->estado }}
                            </span>
                        @else
                            <span class="status-badge badge-cancelada mr-3 mb-2" style="background: rgba(234, 67, 53, 0.9); padding: 8px 16px; border-radius: 25px; font-size: 0.9rem;">
                                <i class="fas fa-times-circle mr-2"></i>{{ $salida->estado }}
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
                        
                        <div class="progress-section mb-2" style="min-width: 280px;">
                            <div class="d-flex align-items-center mb-1">
                                <small class="text-white-50 mr-2">Servicios Confirmados:</small>
                                <strong>{{ $confirmados }}/{{ $total }}</strong>
                            </div>
                            <div class="progress-container" style="background: rgba(255,255,255,0.15); border-radius: 25px; padding: 3px; backdrop-filter: blur(10px);">
                                <div class="progress" style="height: 24px; background: transparent; border-radius: 22px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $porcentaje }}%; background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%); border-radius: 20px; transition: width 0.6s ease;" 
                                         aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                        <span class="font-weight-bold text-white" style="font-size: 0.85rem; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                            {{ round($porcentaje) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-5 text-right">
                <div class="action-buttons">
                    @if(!auth()->user()->hasRole('profesor'))
                    <a href="{{ route('salidas.edit', $salida) }}" class="btn btn-edit btn-lg mr-2 shadow-lg mb-2" 
                       style="background: rgba(255,255,255,0.9); color: #233e6c; border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; transition: all 0.3s ease; backdrop-filter: blur(10px);"
                       onmouseover="this.style.background='rgba(255,255,255,1)'; this.style.transform='translateY(-2px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='translateY(0)'">
                        <i class="fas fa-edit mr-2"></i>Editar Salida
                    </a>
                    @endif
                    <a href="{{ route('salidas.index') }}" class="btn btn-back btn-lg shadow-lg mb-2" 
                       style="background: rgba(255,255,255,0.2); color: white; border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; padding: 12px 24px; font-weight: 600; transition: all 0.3s ease; backdrop-filter: blur(10px);"
                       onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-2px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('content')
<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle mr-2"></i>Confirmar Servicio
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>¿Confirmar este servicio?</h5>
                    <p class="text-muted">Esta acción marcará el servicio como confirmado.</p>
                    <div class="mt-3">
                        <strong>Servicio: </strong><span id="serviceTitle"></span><br>
                        <strong>Usuario: </strong>{{ auth()->user()->name }}
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
<div class="modal fade" id="successModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i>Confirmación Exitosa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="text-success mb-3">¡Servicio Confirmado!</h4>
                    <p id="successMessage" class="lead"></p>
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

<div class="container-fluid">
    <!-- Información General - Diseño Profesional -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-primary text-white" style="background: #233e6c !important; padding: 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #233e6c;">
                            <i class="fas fa-info-circle fa-lg"></i>
                        </div>
                        <h3 class="card-title mb-0 font-weight-bold">Información General de la Salida Pedagógica</h3>
                    </div>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 12px; border-left: 4px solid #3b82f6;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-tie text-primary mr-2 fa-lg"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">RESPONSABLE</h6>
                                </div>
                                <p class="h5 text-dark font-weight-600 mb-0">{{ $salida->responsable->name }}</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; border-left: 4px solid #10b981;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-graduation-cap text-success mr-2 fa-lg"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">GRADOS</h6>
                                </div>
                                <span class="badge badge-success badge-lg px-3 py-2" style="font-size: 1rem;">{{ $salida->grados }}</span>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border-radius: 12px; border-left: 4px solid #f59e0b;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-alt text-warning mr-2 fa-lg"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">FECHA</h6>
                                </div>
                                <p class="h5 text-dark font-weight-600 mb-0">{{ \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #fdf4ff 0%, #f3e8ff 100%); border-radius: 12px; border-left: 4px solid #8b5cf6;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-purple mr-2 fa-lg" style="color: #8b5cf6;"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">HORARIO</h6>
                                </div>
                                <p class="h5 text-dark font-weight-600 mb-0">
                                    {{ \Carbon\Carbon::parse($salida->fecha_salida)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($salida->fecha_regreso)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); border-radius: 12px; border-left: 4px solid #3b82f6;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-users text-info mr-2 fa-lg"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">PASAJEROS</h6>
                                </div>
                                <p class="h5 text-dark font-weight-600 mb-0">{{ $salida->cantidad_pasajeros }} personas</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="info-card h-100 p-3" style="background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%); border-radius: 12px; border-left: 4px solid #ef4444;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-danger mr-2 fa-lg"></i>
                                    <h6 class="text-muted font-weight-bold mb-0">DESTINO</h6>
                                </div>
                                <p class="h6 text-dark font-weight-500 mb-0 text-wrap">{{ $salida->lugar }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($salida->observaciones)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px;">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-comment-alt text-info mr-3 mt-1 fa-lg"></i>
                                    <div>
                                        <h6 class="font-weight-bold text-info mb-2">Observaciones Especiales</h6>
                                        <p class="mb-0 text-dark">{{ $salida->observaciones }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Servicios - Diseño Profesional -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white" style="background: #233e6c !important; padding: 1.5rem;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #233e6c;">
                                <i class="fas fa-clipboard-check fa-lg"></i>
                            </div>
                            <h3 class="card-title mb-0 font-weight-bold">Estado de Servicios Requeridos</h3>
                        </div>
                        <div class="progress-summary bg-white text-dark px-3 py-2 rounded-pill">
                            <strong>{{ $confirmados }}/{{ $total }} Confirmados</strong>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <div class="row">
                        <!-- Transporte -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->transporte_confirmado ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->transporte_confirmado ? '#233e6c' : '#6c757d' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bus fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Transporte</h5>
                                            <small>Servicio Obligatorio</small>
                                        </div>
                                    </div>
                                    @if($salida->transporte_confirmado)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    @if($salida->transporte_confirmado)
                                        <div class="alert alert-success border-0 mb-3" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->transporteConfirmadoPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->transporteConfirmadoPor->name }}</p>
                                        @endif
                                        @if($salida->transporte_confirmado_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->transporte_confirmado_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-sm confirm-service-btn shadow" 
                                                data-service="transporte" 
                                                data-title="Transporte"
                                                style="background: #233e6c; color: white; border: none; border-radius: 6px; font-weight: 500; padding: 8px 16px; width: 100%;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.875rem;">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alimentación -->
                        @if($salida->requiere_alimentacion)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->alimentacion_confirmada ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->alimentacion_confirmada ? '#233e6c' : '#6c757d' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-utensils fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Alimentación</h5>
                                            <small>Servicio Requerido</small>
                                        </div>
                                    </div>
                                    @if($salida->alimentacion_confirmada)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    <!-- Información de Alimentación -->
                                    <div class="mb-3">
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Cantidad Snacks</small>
                                                <strong>{{ $salida->cantidad_snacks ?? '--' }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Cantidad Almuerzos</small>
                                                <strong>{{ $salida->cantidad_almuerzos ?? '--' }}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Hora Entrega Alimentos</small>
                                            <strong>{{ $salida->hora_entrega_alimentos ? date('H:i', strtotime($salida->hora_entrega_alimentos)) : '--:--' }}</strong>
                                        </div>
                                        
                                        @if($salida->menu_sugerido)
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Menú Sugerido</small>
                                            <p class="mb-0" style="font-size: 0.9rem;">{{ $salida->menu_sugerido }}</p>
                                        </div>
                                        @endif
                                        
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Observaciones Dietéticas</small>
                                            <p class="mb-0" style="font-size: 0.9rem;">
                                                {{ $salida->observaciones_dieteticas ?? 'No hay observaciones dietéticas' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Estado de Confirmación -->
                                    @if($salida->alimentacion_confirmada)
                                        <div class="alert alert-success border-0 mb-3" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->alimentacionConfirmadaPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->alimentacionConfirmadaPor->name }}</p>
                                        @endif
                                        @if($salida->alimentacion_confirmada_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->alimentacion_confirmada_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-sm confirm-service-btn shadow" 
                                                data-service="alimentacion" 
                                                data-title="Alimentación"
                                                style="background: #233e6c; color: white; border: none; border-radius: 6px; font-weight: 500; padding: 8px 16px; width: 100%;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.875rem;">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Enfermería -->
                        @if($salida->requiere_enfermeria)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->enfermeria_confirmada ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->enfermeria_confirmada ? '#233e6c' : '#6c757d' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plus-circle fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Enfermería</h5>
                                            <small>Servicio Requerido</small>
                                        </div>
                                    </div>
                                    @if($salida->enfermeria_confirmada)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    @if($salida->enfermeria_confirmada)
                                        <div class="alert alert-success border-0 mb-3" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->enfermeriaConfirmadaPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->enfermeriaConfirmadaPor->name }}</p>
                                        @endif
                                        @if($salida->enfermeria_confirmada_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->enfermeria_confirmada_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-sm confirm-service-btn shadow" 
                                                data-service="enfermeria" 
                                                data-title="Enfermería"
                                                style="background: #233e6c; color: white; border: none; border-radius: 6px; font-weight: 500; padding: 8px 16px; width: 100%;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.875rem;">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Accesos -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->accesos_confirmados ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->accesos_confirmados ? '#233e6c' : '#6c757d' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-key fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Control de Accesos</h5>
                                            <small>Servicio Obligatorio</small>
                                        </div>
                                    </div>
                                    @if($salida->accesos_confirmados)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    @if($salida->accesos_confirmados)
                                        <div class="alert alert-success border-0 mb-3" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->accesosConfirmadosPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->accesosConfirmadosPor->name }}</p>
                                        @endif
                                        @if($salida->accesos_confirmados_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->accesos_confirmados_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-sm confirm-service-btn shadow" 
                                                data-service="accesos" 
                                                data-title="Control de Accesos"
                                                style="background: #233e6c; color: white; border: none; border-radius: 6px; font-weight: 500; padding: 8px 16px; width: 100%;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.875rem;">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comunicaciones -->
                        @if($salida->requiere_comunicaciones)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->comunicaciones_confirmada ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->comunicaciones_confirmada ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bullhorn fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Comunicaciones</h5>
                                            <small>Servicio Requerido</small>
                                        </div>
                                    </div>
                                    @if($salida->comunicaciones_confirmada)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    @if($salida->comunicaciones_confirmada)
                                        <div class="alert alert-success border-0 mb-3" style="background: #f0fdf4;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->comunicacionesConfirmadaPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->comunicacionesConfirmadaPor->name }}</p>
                                        @endif
                                        @if($salida->comunicaciones_confirmada_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->comunicaciones_confirmada_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-warning btn-block confirm-service-btn shadow" 
                                                data-service="comunicaciones" 
                                                data-title="Comunicaciones"
                                                style="border-radius: 8px; font-weight: bold;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- ARL -->
                        @if($salida->requiere_arl)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card h-100 {{ $salida->arl_confirmado ? 'confirmed' : 'pending' }}" 
                                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <div class="service-header p-3 text-white d-flex align-items-center justify-content-between" 
                                     style="background: {{ $salida->arl_confirmado ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' }};">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-medkit fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">ARL</h5>
                                            <small>Servicio Requerido</small>
                                        </div>
                                    </div>
                                    @if($salida->arl_confirmado)
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    @else
                                        <i class="fas fa-clock fa-2x"></i>
                                    @endif
                                </div>
                                <div class="service-body p-3">
                                    @if($salida->arl_confirmado)
                                        <div class="alert alert-success border-0 mb-3" style="background: #f0fdf4;">
                                            <i class="fas fa-check mr-2"></i>
                                            <strong>Confirmado</strong>
                                        </div>
                                        @if($salida->arlConfirmadoPor)
                                            <p class="text-muted mb-1"><strong>Confirmado por:</strong> {{ $salida->arlConfirmadoPor->name }}</p>
                                        @endif
                                        @if($salida->arl_confirmado_at)
                                            <p class="text-muted mb-0"><strong>Fecha:</strong> {{ $salida->arl_confirmado_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    @else
                                        <button class="btn btn-warning btn-block confirm-service-btn shadow" 
                                                data-service="arl" 
                                                data-title="ARL"
                                                style="border-radius: 8px; font-weight: bold;">
                                            <i class="fas fa-check mr-2"></i>Confirmar Servicio
                                        </button>
                                        <p class="text-muted text-center mt-2 mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>Pendiente de confirmación
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Cambios -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white" style="background: #233e6c !important; padding: 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #233e6c;">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <h3 class="card-title mb-0 font-weight-bold">Historial de Cambios</h3>
                    </div>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    @if($salida->history && $salida->history->count() > 0)
                        <div class="timeline timeline-inverse">
                            @foreach($salida->history as $entry)
                                <div class="time-label">
                                    <span class="bg-primary">
                                        {{ $entry->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div>
                                    @if($entry->action === 'created')
                                        <i class="fas fa-plus-circle bg-success"></i>
                                    @elseif($entry->action === 'updated' || $entry->action === 'manual_edit')
                                        <i class="fas fa-edit bg-warning"></i>
                                    @elseif($entry->action === 'deleted')
                                        <i class="fas fa-trash bg-danger"></i>
                                    @else
                                        <i class="fas fa-circle bg-info"></i>
                                    @endif
                                    
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="far fa-clock"></i> {{ $entry->created_at->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header">
                                            @if($entry->action === 'created')
                                                <span class="text-success">Salida Creada</span>
                                            @elseif($entry->action === 'updated')
                                                <span class="text-warning">Salida Actualizada</span>
                                            @elseif($entry->action === 'manual_edit')
                                                <span class="text-warning">Edición Manual</span>
                                            @elseif($entry->action === 'deleted')
                                                <span class="text-danger">Salida Eliminada</span>
                                            @else
                                                <span class="text-info">{{ ucfirst($entry->action) }}</span>
                                            @endif
                                        </h3>
                                        <div class="timeline-body">
                                            @if($entry->user)
                                                <p class="mb-2">
                                                    <strong>Realizado por:</strong> 
                                                    <span class="badge badge-secondary">{{ $entry->user->name }}</span>
                                                    @if($entry->ip_address)
                                                        <small class="text-muted ml-2">IP: {{ $entry->ip_address }}</small>
                                                    @endif
                                                </p>
                                            @endif
                                            
                                            @if($entry->notes)
                                                <p class="mb-2"><strong>Descripción:</strong> {{ $entry->notes }}</p>
                                            @endif
                                            
                                            @if($entry->changes && is_array($entry->changes) && !empty($entry->changes))
                                                <div class="changes-details">
                                                    <strong>Cambios realizados:</strong>
                                                    <div class="mt-2">
                                                        @foreach($entry->changes as $field => $change)
                                                            @if(isset($change['old']) && isset($change['new']))
                                                                @php
                                                                    $fieldLabels = [
                                                                        'grados' => 'Grados',
                                                                        'lugar' => 'Lugar',
                                                                        'responsable_id' => 'Responsable',
                                                                        'fecha_salida' => 'Fecha de salida',
                                                                        'fecha_regreso' => 'Fecha de regreso',
                                                                        'cantidad_pasajeros' => 'Cantidad de pasajeros',
                                                                        'observaciones' => 'Observaciones',
                                                                        'calendario_general' => 'Calendario general',
                                                                        'visita_inspeccion' => 'Visita de inspección',
                                                                        'detalles_inspeccion' => 'Detalles de inspección',
                                                                        'contacto_lugar' => 'Contacto del lugar',
                                                                        'requiere_alimentacion' => 'Requiere alimentación',
                                                                        'cantidad_snacks' => 'Cantidad de snacks',
                                                                        'cantidad_almuerzos' => 'Cantidad de almuerzos',
                                                                        'menu_sugerido' => 'Menú sugerido',
                                                                        'hora_apertura_puertas' => 'Hora de apertura de puertas',
                                                                        'requiere_enfermeria' => 'Requiere enfermería',
                                                                        'requiere_comunicaciones' => 'Requiere comunicaciones',
                                                                        'requiere_arl' => 'Requiere ARL',
                                                                        'observaciones_comunicaciones' => 'Observaciones de comunicaciones',
                                                                        'estado' => 'Estado',
                                                                    ];
                                                                    
                                                                    $fieldName = $fieldLabels[$field] ?? $field;
                                                                    
                                                                    // Formatear valores
                                                                    $oldValue = $change['old'];
                                                                    $newValue = $change['new'];
                                                                    
                                                                    // Campos booleanos
                                                                    if (in_array($field, ['calendario_general', 'visita_inspeccion', 'requiere_alimentacion', 'requiere_enfermeria', 'requiere_comunicaciones', 'requiere_arl'])) {
                                                                        $oldValue = $oldValue ? 'Sí' : 'No';
                                                                        $newValue = $newValue ? 'Sí' : 'No';
                                                                    }
                                                                    
                                                                    // Campos de fecha/hora
                                                                    if (in_array($field, ['fecha_salida', 'fecha_regreso']) && $oldValue) {
                                                                        try {
                                                                            $oldValue = \Carbon\Carbon::parse($oldValue)->format('d/m/Y H:i');
                                                                        } catch (\Exception $e) {
                                                                            // Mantener valor original si no se puede parsear
                                                                        }
                                                                    }
                                                                    
                                                                    if (in_array($field, ['fecha_salida', 'fecha_regreso']) && $newValue) {
                                                                        try {
                                                                            $newValue = \Carbon\Carbon::parse($newValue)->format('d/m/Y H:i');
                                                                        } catch (\Exception $e) {
                                                                            // Mantener valor original si no se puede parsear
                                                                        }
                                                                    }
                                                                    
                                                                    // Responsable (buscar nombre del usuario)
                                                                    if ($field === 'responsable_id') {
                                                                        if ($oldValue) {
                                                                            $oldUser = \App\Models\User::find($oldValue);
                                                                            $oldValue = $oldUser ? $oldUser->name : "Usuario ID: {$oldValue}";
                                                                        }
                                                                        if ($newValue) {
                                                                            $newUser = \App\Models\User::find($newValue);
                                                                            $newValue = $newUser ? $newUser->name : "Usuario ID: {$newValue}";
                                                                        }
                                                                    }
                                                                    
                                                                    // Valores nulos o vacíos
                                                                    if (is_null($oldValue) || $oldValue === '') {
                                                                        $oldValue = '(vacío)';
                                                                    }
                                                                    if (is_null($newValue) || $newValue === '') {
                                                                        $newValue = '(vacío)';
                                                                    }
                                                                @endphp
                                                                
                                                                @if($oldValue !== $newValue)
                                                                    <div class="alert alert-light border-left-info mb-2" style="border-left: 4px solid #17a2b8; font-size: 0.9rem;">
                                                                        <strong>{{ $fieldName }}:</strong><br>
                                                                        <span class="text-muted">Anterior:</span> <span class="badge badge-secondary">{{ $oldValue }}</span><br>
                                                                        <span class="text-success">Nuevo:</span> <span class="badge badge-success">{{ $newValue }}</span>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div>
                                <i class="far fa-clock bg-gray"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> Inicio</span>
                                    <h3 class="timeline-header no-border">Sistema de Auditoría Activado</h3>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay historial de cambios disponible para esta salida pedagógica.</p>
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
/* Estilos profesionales para la vista de salidas */
body {
    background: #f8fafc;
}

.content-header {
    border-radius: 0 0 25px 25px;
    position: relative;
    overflow: hidden;
}

.content-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
}

.info-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.service-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
}

.service-header {
    position: relative;
    overflow: hidden;
}

.service-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.service-card:hover .service-header::before {
    left: 100%;
}

.icon-circle {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.progress {
    border-radius: 50px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #10b981, #059669) !important;
    position: relative;
    transition: width 1s ease;
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
    );
    background-size: 30px 30px;
    animation: move 2s linear infinite;
}

@keyframes move {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 30px 30px;
    }
}

.btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    color: white;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1rem;
}

.card {
    border: none;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.alert {
    border: none;
    border-radius: 10px;
}

.text-wrap {
    word-wrap: break-word;
    word-break: break-word;
}

.font-weight-600 {
    font-weight: 600;
}

.font-weight-500 {
    font-weight: 500;
}

/* Modal mejoras */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    border-bottom: none;
    padding: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
    border-top: none;
    padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0 !important;
        margin-bottom: 1rem !important;
    }
    
    .content-header h1 {
        font-size: 1.5rem !important;
    }
    
    .content-header .lead {
        font-size: 1rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .info-card {
        margin-bottom: 1rem;
    }
    
    .service-card {
        margin-bottom: 1.5rem;
    }
}

/* Animaciones de carga */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

.service-card:nth-child(1) { animation-delay: 0.1s; }
.service-card:nth-child(2) { animation-delay: 0.2s; }
.service-card:nth-child(3) { animation-delay: 0.3s; }
.service-card:nth-child(4) { animation-delay: 0.4s; }
.service-card:nth-child(5) { animation-delay: 0.5s; }
.service-card:nth-child(6) { animation-delay: 0.6s; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let currentService = '';
    let currentSalidaId = {{ $salida->id }};

    // Función para mostrar modal de confirmación
    $('.confirm-service-btn').on('click', function() {
        currentService = $(this).data('service');
        const serviceTitle = $(this).data('title');
        
        $('#serviceTitle').text(serviceTitle);
        $('#confirmServiceModal').modal('show');
    });

    // Confirmar servicio
    $('#confirmServiceBtn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Confirmando...').prop('disabled', true);

        // Determinar la URL según el servicio
        let url = '';
        switch(currentService) {
            case 'transporte':
                url = "{{ route('salidas.confirmar-transporte', $salida->id) }}";
                break;
            case 'alimentacion':
                url = "{{ route('salidas.confirmar-alimentacion', $salida->id) }}";
                break;
            case 'enfermeria':
                url = "{{ route('salidas.confirmar-enfermeria', $salida->id) }}";
                break;
            case 'accesos':
                url = "{{ route('salidas.confirmar-accesos', $salida->id) }}";
                break;
            case 'comunicaciones':
                url = "{{ route('salidas.confirmar-comunicaciones', $salida->id) }}";
                break;
            case 'arl':
                url = "{{ route('salidas.confirmar-arl', $salida->id) }}";
                break;
            default:
                alert('Servicio no válido');
                $btn.html(originalText).prop('disabled', false);
                return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#confirmServiceModal').modal('hide');
                $('#successMessage').text('El servicio ha sido confirmado exitosamente.');
                $('#successModal').modal('show');
                
                // Recargar la página después de cerrar el modal de éxito
                $('#successModal').on('hidden.bs.modal', function() {
                    location.reload();
                });
            },
            error: function(xhr) {
                $('#confirmServiceModal').modal('hide');
                alert('Error: ' + (xhr.responseJSON?.message || 'Ha ocurrido un error inesperado.'));
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@stop
