@extends('adminlte::page')

@section('title', 'Programar Sesión de Retroalimentación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-plus text-primary"></i> Programar Sesión de Retroalimentación</h1>
        <a href="{{ route('performance-evaluations.show', $evaluation) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Evaluación
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-clock"></i> 
                    Programar Sesión para {{ $employee->name }}
                </h3>
            </div>
            <form action="{{ route('feedback-sessions.store', $evaluation) }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Información de la Evaluación -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Información de la Evaluación</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Colaborador:</strong> {{ $employee->name }}<br>
                                <strong>Supervisor:</strong> {{ $supervisor->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Período:</strong> 
                                @if($evaluation->evaluation_period_start && $evaluation->evaluation_period_end)
                                    {{ $evaluation->evaluation_period_start->format('d/m/Y') }} - {{ $evaluation->evaluation_period_end->format('d/m/Y') }}
                                @else
                                    Período no definido
                                @endif<br>
                                <strong>Tipo:</strong> {{ $evaluation->evaluation_type }}
                            </div>
                        </div>
                    </div>

                    <!-- Fecha y Hora -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="scheduled_date">
                                    <i class="fas fa-calendar"></i> Fecha de la Sesión *
                                </label>
                                <input type="date" 
                                       class="form-control @error('scheduled_date') is-invalid @enderror" 
                                       id="scheduled_date" 
                                       name="scheduled_date" 
                                       value="{{ old('scheduled_date', now()->addDays(1)->format('Y-m-d')) }}"
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
                                    <i class="fas fa-clock"></i> Hora *
                                </label>
                                <input type="time" 
                                       class="form-control @error('scheduled_time') is-invalid @enderror" 
                                       id="scheduled_time" 
                                       name="scheduled_time" 
                                       value="{{ old('scheduled_time', '09:00') }}"
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
                                <select class="form-control @error('duration') is-invalid @enderror" 
                                        id="duration" 
                                        name="duration" 
                                        required>
                                    <option value="30" {{ old('duration') == '30' ? 'selected' : '' }}>30 minutos</option>
                                    <option value="45" {{ old('duration') == '45' ? 'selected' : '' }}>45 minutos</option>
                                    <option value="60" {{ old('duration', '60') == '60' ? 'selected' : '' }}>1 hora</option>
                                    <option value="90" {{ old('duration') == '90' ? 'selected' : '' }}>1 hora 30 min</option>
                                    <option value="120" {{ old('duration') == '120' ? 'selected' : '' }}>2 horas</option>
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
                               value="{{ old('location') }}"
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
                                  placeholder="Descripción opcional de los temas a tratar en la sesión de retroalimentación...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Máximo 1000 caracteres</small>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Importante:</h6>
                        <ul class="mb-0">
                            <li>Se enviarán notificaciones automáticas por email al colaborador y supervisor</li>
                            <li>Si está configurado, se creará un evento en Google Calendar</li>
                            <li>La sesión se puede reprogramar posteriormente si es necesario</li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Programar Sesión
                    </button>
                    <a href="{{ route('performance-evaluations.show', $evaluation) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Información adicional y consejos -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb"></i> Consejos para la Sesión
                </h3>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-target text-primary"></i> Objetivos de la Sesión:</h6>
                <ul class="list-unstyled">
                    <li>✓ Revisar resultados de evaluación</li>
                    <li>✓ Discutir fortalezas identificadas</li>
                    <li>✓ Abordar áreas de mejora</li>
                    <li>✓ Establecer metas futuras</li>
                    <li>✓ Planificar desarrollo profesional</li>
                </ul>

                <hr>

                <h6><i class="fas fa-clock text-warning"></i> Duración Recomendada:</h6>
                <p class="text-sm">
                    Se recomienda entre 45 minutos y 1 hora para permitir una discusión completa y constructiva.
                </p>

                <hr>

                <h6><i class="fas fa-envelope text-success"></i> Notificaciones:</h6>
                <p class="text-sm">
                    Ambas partes recibirán un email formal con todos los detalles de la sesión programada.
                </p>
            </div>
        </div>

        @if(config('google.calendar.calendar_id'))
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-google"></i> Google Calendar
                </h3>
            </div>
            <div class="card-body">
                <p class="text-sm">
                    <i class="fas fa-check-circle text-success"></i>
                    La sesión se agregará automáticamente a Google Calendar con recordatorios.
                </p>
            </div>
        </div>
        @endif
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
            $(this).val('{{ now()->addDay()->format("Y-m-d") }}');
        }
    });

    // Confirmación antes de enviar
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
            title: 'Confirmar Programación',
            html: `
                <p>¿Está seguro de programar la sesión de retroalimentación?</p>
                <div class="alert alert-info mt-3">
                    <strong>Fecha:</strong> ${formattedDate}<br>
                    <strong>Hora:</strong> ${formattedTime}<br>
                    <strong>Colaborador:</strong> {{ $employee->name }}
                </div>
                <p><small>Se enviarán notificaciones automáticas por email.</small></p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, Programar',
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
