@extends('adminlte::page')

@section('title', 'Editar Sesión de Retroalimentación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit text-warning"></i> Editar Sesión de Retroalimentación</h1>
        <a href="{{ route('feedback-sessions.show', $feedbackSession) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-edit"></i> 
                    Editar Sesión - {{ $feedbackSession->employee->name }}
                </h3>
            </div>
            <form action="{{ route('feedback-sessions.update', $feedbackSession) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <!-- Información de la Sesión Actual -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Información Actual</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Fecha actual:</strong> {{ $feedbackSession->scheduled_datetime->format('d/m/Y') }}<br>
                                <strong>Hora actual:</strong> {{ $feedbackSession->scheduled_datetime->format('H:i') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong> {{ ucfirst($feedbackSession->status) }}<br>
                                <strong>Colaborador:</strong> {{ $feedbackSession->employee->name }}
                            </div>
                        </div>
                    </div>

                    <!-- Nueva Fecha y Hora -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="scheduled_date">
                                    <i class="fas fa-calendar"></i> Nueva Fecha *
                                </label>
                                <input type="date" 
                                       class="form-control @error('scheduled_date') is-invalid @enderror" 
                                       id="scheduled_date" 
                                       name="scheduled_date" 
                                       value="{{ old('scheduled_date', $feedbackSession->scheduled_datetime->format('Y-m-d')) }}"
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       required>
                                @error('scheduled_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Debe ser una fecha futura</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="scheduled_time">
                                    <i class="fas fa-clock"></i> Nueva Hora *
                                </label>
                                <input type="time" 
                                       class="form-control @error('scheduled_time') is-invalid @enderror" 
                                       id="scheduled_time" 
                                       name="scheduled_time" 
                                       value="{{ old('scheduled_time', $feedbackSession->scheduled_datetime->format('H:i')) }}"
                                       required>
                                @error('scheduled_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="duration">
                                    <i class="fas fa-hourglass-half"></i> Duración (minutos) *
                                </label>
                                @php
                                    $currentDuration = 60; // Default
                                    if($feedbackSession->scheduled_datetime && $feedbackSession->scheduled_datetime->copy()->addHour()->format('H:i') !== $feedbackSession->scheduled_datetime->format('H:i')) {
                                        // Intentar calcular duración actual, por defecto 60 min
                                        $currentDuration = 60;
                                    }
                                @endphp
                                <select class="form-control @error('duration') is-invalid @enderror" 
                                        id="duration" 
                                        name="duration" 
                                        required>
                                    <option value="30" {{ old('duration', $currentDuration) == '30' ? 'selected' : '' }}>30 minutos</option>
                                    <option value="45" {{ old('duration', $currentDuration) == '45' ? 'selected' : '' }}>45 minutos</option>
                                    <option value="60" {{ old('duration', $currentDuration) == '60' ? 'selected' : '' }}>1 hora</option>
                                    <option value="90" {{ old('duration', $currentDuration) == '90' ? 'selected' : '' }}>1 hora 30 min</option>
                                    <option value="120" {{ old('duration', $currentDuration) == '120' ? 'selected' : '' }}>2 horas</option>
                                </select>
                                @error('duration')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="form-group">
                        <label for="location">
                            <i class="fas fa-map-marker-alt"></i> Ubicación
                        </label>
                        <input type="text" 
                               class="form-control @error('location') is-invalid @enderror" 
                               id="location" 
                               name="location" 
                               value="{{ old('location', $feedbackSession->location) }}"
                               placeholder="Ej: Oficina del supervisor, Sala de reuniones A, Virtual (Google Meet), etc.">
                        @error('location')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Especifique dónde se realizará la sesión</small>
                    </div>

                    <!-- Descripción/Agenda -->
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-clipboard-list"></i> Descripción o Agenda
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Descripción opcional de los temas a tratar en la sesión de retroalimentación...">{{ old('description', $feedbackSession->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Máximo 1000 caracteres</small>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Importante:</h6>
                        <ul class="mb-0">
                            <li>Se enviarán notificaciones automáticas sobre los cambios</li>
                            <li>Si existe un evento en Google Calendar, también se actualizará</li>
                            <li>Los participantes recibirán la información actualizada por email</li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('feedback-sessions.show', $feedbackSession) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Información adicional -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb"></i> Consideraciones
                </h3>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-clock text-warning"></i> Cambios de Horario:</h6>
                <p class="text-sm">
                    Procure reprogramar con suficiente anticipación para que los participantes puedan ajustar sus horarios.
                </p>

                <hr>

                <h6><i class="fas fa-envelope text-success"></i> Notificaciones:</h6>
                <p class="text-sm">
                    Los participantes recibirán automáticamente un email con la información actualizada de la sesión.
                </p>

                <hr>

                <h6><i class="fab fa-google text-info"></i> Google Calendar:</h6>
                <p class="text-sm">
                    Si la sesión tiene un evento asociado en Google Calendar, también se actualizará automáticamente.
                </p>
            </div>
        </div>

        <!-- Información de la Evaluación -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Evaluación Relacionada
                </h3>
            </div>
            <div class="card-body">
                <p><strong>Colaborador:</strong> {{ $feedbackSession->employee->name }}</p>
                <p><strong>Período:</strong> {{ $feedbackSession->evaluation_period }}</p>
                <p><strong>Tipo:</strong> {{ $feedbackSession->performanceEvaluation->evaluation_type }}</p>
                
                <a href="{{ route('performance-evaluations.show', $feedbackSession->performanceEvaluation) }}" 
                   class="btn btn-outline-primary btn-block">
                    <i class="fas fa-eye"></i> Ver Evaluación
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .alert ul {
        padding-left: 20px;
    }
    .form-text {
        font-size: 0.875rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Validación de fecha mínima
    $('#scheduled_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        if (selectedDate < tomorrow) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha Inválida',
                text: 'Debe seleccionar una fecha futura (mínimo mañana).',
                confirmButtonColor: '#007bff'
            });
            $(this).val('{{ $feedbackSession->scheduled_datetime->addDay()->format("Y-m-d") }}');
        }
    });

    // Confirmación antes de enviar cambios
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const date = $('#scheduled_date').val();
        const time = $('#scheduled_time').val();
        const dateObj = new Date(date + ' ' + time);
        const formattedDate = dateObj.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        const formattedTime = dateObj.toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        Swal.fire({
            title: 'Confirmar Cambios',
            html: `
                <p>¿Está seguro de actualizar la sesión de retroalimentación?</p>
                <div class="alert alert-warning mt-3">
                    <strong>Nueva fecha:</strong> ${formattedDate}<br>
                    <strong>Nueva hora:</strong> ${formattedTime}<br>
                    <strong>Colaborador:</strong> {{ $feedbackSession->employee->name }}
                </div>
                <p><small>Se enviarán notificaciones automáticas sobre los cambios.</small></p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, Actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@stop
