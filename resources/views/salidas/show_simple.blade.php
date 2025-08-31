@extends('adminlte::page')

@section('title', 'Detalle de Salida')

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
    <!-- Información Básica -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h3 class="card-title text-sm font-weight-bold">
                        <i class="fas fa-info-circle mr-1"></i>Información General
                    </h3>
                </div>
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
            <div class="card">
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
</div>
@stop

@section('css')
<style>
.card {
    margin-bottom: 1rem;
}
.card-header {
    padding: 0.5rem 1rem;
}
.card-body {
    padding: 0.75rem;
}
.btn-xs {
    padding: 0.15rem 0.3rem;
    font-size: 0.7rem;
    border-radius: 0.15rem;
}
.badge-sm {
    font-size: 0.6rem;
}
.text-sm {
    font-size: 0.875rem;
}
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

        $.ajax({
            url: "{{ route('salidas.confirmar-servicio', $salida->id) }}",
            method: 'POST',
            data: {
                servicio: currentService,
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
