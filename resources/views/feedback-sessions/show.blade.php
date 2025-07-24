@extends('adminlte::page')

@section('title', 'Sesión de Retroalimentación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-comments text-primary"></i> Sesión de Retroalimentación</h1>
        <div>
            @if($feedbackSession->status === 'programada' && auth()->id() === $feedbackSession->supervisor_id)
                <a href="{{ route('feedback-sessions.edit', $feedbackSession) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('performance-evaluations.show', $feedbackSession->performanceEvaluation) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Evaluación
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Información Principal -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> 
                    Detalles de la Sesión
                </h3>
                <div class="card-tools">
                    @switch($feedbackSession->status)
                        @case('programada')
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock"></i> Programada
                            </span>
                            @break
                        @case('realizada')
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check-circle"></i> Realizada
                            </span>
                            @break
                        @case('cancelada')
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-times-circle"></i> Cancelada
                            </span>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-calendar text-info"></i> Fecha y Hora</h5>
                        <p class="mb-3">
                            <strong>{{ $feedbackSession->formatted_datetime }}</strong>
                            @if($feedbackSession->isPast() && $feedbackSession->status === 'programada')
                                <span class="badge badge-warning ml-2">Sesión Pasada</span>
                            @elseif($feedbackSession->isUpcoming())
                                <span class="badge badge-info ml-2">Próxima</span>
                            @endif
                        </p>

                        <h5><i class="fas fa-users text-success"></i> Participantes</h5>
                        <div class="mb-3">
                            <p><strong>Colaborador:</strong> {{ $feedbackSession->employee->name }}</p>
                            <p><strong>Supervisor:</strong> {{ $feedbackSession->supervisor->name }}</p>
                        </div>

                        @if($feedbackSession->location)
                        <h5><i class="fas fa-map-marker-alt text-danger"></i> Ubicación</h5>
                        <p class="mb-3">{{ $feedbackSession->location }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-clipboard-list text-warning"></i> Evaluación Relacionada</h5>
                        <div class="mb-3">
                            <p><strong>Período:</strong> {{ $feedbackSession->evaluation_period }}</p>
                            <p><strong>Tipo:</strong> {{ $feedbackSession->performanceEvaluation->evaluation_type }}</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge badge-success">
                                    {{ ucfirst(str_replace('_', ' ', $feedbackSession->performanceEvaluation->status)) }}
                                </span>
                            </p>
                        </div>

                        @if($feedbackSession->google_event_id)
                        <h5><i class="fab fa-google text-info"></i> Google Calendar</h5>
                        <p class="mb-0">
                            <span class="badge badge-success">
                                <i class="fas fa-check"></i> Evento creado
                            </span>
                        </p>
                        @endif
                    </div>
                </div>

                @if($feedbackSession->description)
                <hr>
                <h5><i class="fas fa-clipboard-list text-primary"></i> Descripción</h5>
                <div class="alert alert-info">
                    {{ $feedbackSession->description }}
                </div>
                @endif
            </div>
        </div>

        <!-- Notas de la Reunión -->
        @if($feedbackSession->status === 'realizada' && $feedbackSession->meeting_notes)
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note"></i> 
                    Notas de la Reunión
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-light border-left-success">
                    <h6>Completada el: {{ $feedbackSession->completed_at->format('d/m/Y \a \l\a\s H:i') }}</h6>
                    <hr>
                    {!! nl2br(e($feedbackSession->meeting_notes)) !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Formulario para completar sesión -->
        @if($feedbackSession->status === 'programada' && auth()->id() === $feedbackSession->supervisor_id)
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-check-square"></i> 
                    Marcar Sesión como Completada
                </h3>
            </div>
            <form action="{{ route('feedback-sessions.complete', $feedbackSession) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="meeting_notes">
                            <i class="fas fa-edit"></i> Notas de la Reunión
                        </label>
                        <textarea class="form-control @error('meeting_notes') is-invalid @enderror" 
                                  id="meeting_notes" 
                                  name="meeting_notes" 
                                  rows="6"
                                  placeholder="Resumen de los temas tratados, acuerdos alcanzados, compromisos establecidos, etc.">{{ old('meeting_notes') }}</textarea>
                        @error('meeting_notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Máximo 2000 caracteres</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Marcar como Completada
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Acciones Disponibles -->
        @if($feedbackSession->status === 'programada' && auth()->id() === $feedbackSession->supervisor_id)
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Acciones
                </h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('feedback-sessions.edit', $feedbackSession) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Editar Sesión
                    </a>
                    
                    <button type="button" class="btn btn-danger btn-block" onclick="cancelSession()">
                        <i class="fas fa-times"></i> Cancelar Sesión
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Información Contextual -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información
                </h3>
            </div>
            <div class="card-body">
                @if($feedbackSession->status === 'programada')
                    <h6 class="text-primary">
                        <i class="fas fa-lightbulb"></i> Consejos para la Sesión
                    </h6>
                    <ul class="list-unstyled text-sm">
                        <li>✓ Revisar resultados de evaluación</li>
                        <li>✓ Preparar ejemplos específicos</li>
                        <li>✓ Enfocarse en desarrollo futuro</li>
                        <li>✓ Establecer metas claras</li>
                    </ul>
                @elseif($feedbackSession->status === 'realizada')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Sesión Completada</strong><br>
                        Esta sesión se realizó exitosamente el {{ $feedbackSession->completed_at->format('d/m/Y') }}.
                    </div>
                @else
                    <div class="alert alert-secondary">
                        <i class="fas fa-ban"></i>
                        <strong>Sesión Cancelada</strong><br>
                        Esta sesión fue cancelada.
                    </div>
                @endif

                <hr>

                <h6 class="text-info">
                    <i class="fas fa-clock"></i> Cronología
                </h6>
                <ul class="list-unstyled text-sm">
                    <li><strong>Programada:</strong> {{ $feedbackSession->created_at->format('d/m/Y H:i') }}</li>
                    @if($feedbackSession->completed_at)
                    <li><strong>Completada:</strong> {{ $feedbackSession->completed_at->format('d/m/Y H:i') }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Enlace a la Evaluación -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-link"></i> Enlaces Relacionados
                </h3>
            </div>
            <div class="card-body">
                <a href="{{ route('performance-evaluations.show', $feedbackSession->performanceEvaluation) }}" 
                   class="btn btn-outline-primary btn-block">
                    <i class="fas fa-chart-line"></i> Ver Evaluación Completa
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .border-left-success {
        border-left: 4px solid #28a745;
    }
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    .d-grid {
        display: grid;
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>
@stop

@section('js')
<script>
function cancelSession() {
    Swal.fire({
        title: '¿Cancelar Sesión?',
        text: '¿Está seguro de que desea cancelar esta sesión de retroalimentación?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, Cancelar',
        cancelButtonText: 'No Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear un formulario temporal para enviar la solicitud DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("feedback-sessions.cancel", $feedbackSession) }}';
            
            // Agregar token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Agregar método DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Contador de caracteres para notas
$(document).ready(function() {
    $('#meeting_notes').on('input', function() {
        let length = $(this).val().length;
        let max = 2000;
        let remaining = max - length;
        
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, max));
            remaining = 0;
        }
        
        $(this).siblings('.form-text').text(`Caracteres restantes: ${remaining}`);
    });
});
</script>
@stop
