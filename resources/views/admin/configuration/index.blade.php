@extends('adminlte::page')

@section('title', 'Configuración')

@section('content_header')
    <h1 class="text-primary mb-4">Configuración del Sistema</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.configuration.update-emails') }}" method="POST" id="emailForm">
            @csrf
            @foreach($configurations as $key => $config)
                @if($key === 'events')
                    @php
                        $areaIcons = [
                            'sistemas' => 'fas fa-laptop-code',
                            'compras' => 'fas fa-shopping-cart',
                            'mantenimiento' => 'fas fa-tools',
                            'servicios_generales' => 'fas fa-cogs',
                            'comunicaciones' => 'fas fa-comments',
                            'aldimark' => 'fas fa-store',
                            'metro_junior' => 'fas fa-school'
                        ];
                    @endphp
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title text-white mb-0">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        Notificaciones de {{ $config['name'] }}
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($config['areas'] as $areaKey => $area)
                                            <div class="col-md-4 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-header bg-primary">
                                                        <h5 class="mb-0 text-white">
                                                            <i class="{{ $areaIcons[$areaKey] ?? 'fas fa-building' }} mr-1"></i>
                                                            {{ $area['name'] }}
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="emailInputs_events_{{ $areaKey }}">
                                                            @forelse($area['emails'] as $email)
                                                                <div class="input-group mb-2">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                                                    </div>
                                                                    <input type="email" name="emails[events_{{ $areaKey }}][]" 
                                                                           class="form-control" value="{{ $email }}" required 
                                                                           placeholder="correo@ejemplo.com">
                                                                    <div class="input-group-append">
                                                                        <button type="button" class="btn btn-outline-danger remove-email">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="input-group mb-2">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                                                    </div>
                                                                    <input type="email" name="emails[events_{{ $areaKey }}][]" 
                                                                           class="form-control" required 
                                                                           placeholder="correo@ejemplo.com">
                                                                    <div class="input-group-append">
                                                                        <button type="button" class="btn btn-outline-danger remove-email">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                        <button type="button" class="btn btn-outline-secondary w-100 mt-2 add-email" 
                                                                data-container="events_{{ $areaKey }}">
                                                            <i class="fas fa-plus mr-1"></i> Agregar Correo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-envelope mr-2"></i>
                                Notificaciones de {{ $config['name'] }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">
                                    <i class="fas fa-mail-bulk mr-1"></i>
                                    Correos para {{ $config['name'] }}
                                </label>
                                <p class="text-muted mb-3">
                                    <small><i class="fas fa-info-circle mr-1"></i>Agregue los correos electrónicos que recibirán las notificaciones de {{ strtolower($config['name']) }}.</small>
                                </p>
                                <div id="emailInputs_{{ $key }}">
                                    @forelse($config['emails'] as $email)
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            </div>
                                            <input type="email" name="emails[{{ $key }}][]" class="form-control" value="{{ $email }}" required 
                                                   placeholder="correo@ejemplo.com">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-danger remove-email" title="Eliminar correo">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            </div>
                                            <input type="email" name="emails[{{ $key }}][]" class="form-control" required 
                                                   placeholder="correo@ejemplo.com">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-danger remove-email" title="Eliminar correo">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                <button type="button" class="btn btn-outline-secondary mt-3 add-email" data-container="{{ $key }}">
                                    <i class="fas fa-plus mr-1"></i> Agregar Correo
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="card shadow">
                <div class="card-body text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Información
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-lightbulb mr-2"></i>Consejos:</h5>
                    <ul class="pl-3 mt-2">
                        <li>Configure los correos para cada módulo por separado</li>
                        <li>Puede agregar múltiples correos por módulo</li>
                        <li>Las notificaciones se enviarán a todos los correos configurados en cada módulo</li>
                        <li>Asegúrese de que los correos sean válidos</li>
                        <li>Los cambios se aplican inmediatamente al guardar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;  /* Color institucional */
        --secondary: #6c757d;
    }

    /* Estilos para texto primario */
    .text-primary, 
    .font-weight-bold.text-primary,
    .card-title {
        color: var(--primary) !important;
    }

    /* Estilos para fondos */
    .bg-primary,
    .card-header {
        background-color: var(--primary) !important;
    }

    /* Estilos para botones */
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }

    .btn-outline-secondary:hover {
        background-color: var(--secondary);
        border-color: var(--secondary);
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
        cursor: pointer;
    }

    .card-header[role="button"] {
        transition: background-color 0.3s ease;
    }

    .card-header[role="button"]:hover {
        background-color: #2a3d5d !important;
    }

    .card-header .fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .collapse:not(.show) + .card-header .fa-chevron-down {
        transform: rotate(-90deg);
    }

    .text-white {
        color: #ffffff !important;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .alert-info {
        border-left: 4px solid var(--primary);
    }

    .shadow {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    /* Estilos para modales y SweetAlert */
    .swal2-styled.swal2-confirm {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }

    .accordion .card-header {
        cursor: pointer;
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .accordion .card-header:hover {
        background-color: #e9ecef;
    }

    .accordion .card {
        border: 1px solid rgba(0,0,0,.125);
        margin-bottom: 5px;
    }

    .accordion .fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .accordion .collapse:not(.show) + .card-header .fa-chevron-down,
    .accordion .collapsed .fa-chevron-down {
        transform: rotate(-90deg);
    }

    /* Estilos específicos para los headers del acordeón de eventos */
    .accordion .card-header {
        cursor: pointer;
        background-color: var(--primary);
        border-bottom: 1px solid rgba(255,255,255,.125);
    }

    .accordion .card-header h5,
    .accordion .card-header .text-primary {
        color: #ffffff !important;
    }

    .accordion .card-header:hover {
        background-color: #2a3d5d;
    }

    .accordion .card {
        border: 1px solid rgba(0,0,0,.125);
        margin-bottom: 5px;
        overflow: hidden;
    }

    .accordion .fa-chevron-down,
    .accordion .fa-building {
        color: #ffffff;
        transition: transform 0.3s ease;
    }

    /* Asegurar que todos los card-headers principales y del acordeón tengan texto blanco */
    .card-header.bg-primary .card-title,
    .card-header.bg-primary h3,
    .accordion .card-header span {
        color: #ffffff !important;
    }

    /* Estilos específicos para las tarjetas de eventos */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        height: 100%;
    }

    .card-header {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .card-body {
        padding: 1.25rem;
    }

    .input-group {
        margin-bottom: 0.75rem;
    }

    .input-group:last-child {
        margin-bottom: 0;
    }

    .btn-outline-secondary {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-outline-secondary:hover {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }
</style>
@stop

@section('js')
<script>
document.querySelectorAll('.add-email').forEach(button => {
    button.addEventListener('click', function() {
        const containerId = this.dataset.container;
        const container = document.getElementById('emailInputs_' + containerId);
        const newInput = document.createElement('div');
        newInput.className = 'input-group mb-2';
        newInput.innerHTML = `
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-at"></i></span>
            </div>
            <input type="email" name="emails[${containerId}][]" class="form-control" required placeholder="correo@ejemplo.com">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger remove-email" title="Eliminar correo">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newInput);
    });
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-email') || e.target.closest('.remove-email')) {
        const inputGroup = e.target.closest('.input-group');
        const container = inputGroup.closest('[id^="emailInputs_"]');
        if (container.querySelectorAll('.input-group').length > 1) {
            inputGroup.remove();
        }
    }
});

document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    
    // Expandir todos los acordeones antes de validar
    document.querySelectorAll('.collapse').forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            new bootstrap.Collapse(collapse).show();
        }
    });

    // Esperar a que se expandan los acordeones
    setTimeout(() => {
        // Verificar si hay campos vacíos
        let hasEmptyFields = false;
        form.querySelectorAll('input[type="email"]').forEach(input => {
            if (input.required && !input.value.trim()) {
                hasEmptyFields = true;
                input.closest('.card').querySelector('.card-header').click();
                input.focus();
            }
        });

        if (hasEmptyFields) {
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor, complete todos los campos de correo electrónico requeridos.',
                confirmButtonColor: '#364E76'
            });
            return;
        }

        // Si todo está correcto, mostrar confirmación
        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizará la configuración de todos los módulos',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#364E76',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save mr-2"></i>Sí, guardar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }, 300);
});

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#364E76'  // Color institucional
    });
@endif

// Manejar la rotación del ícono en el acordeón
$('#eventsCollapse').on('show.bs.collapse hide.bs.collapse', function() {
    const icon = $(this).siblings('.card-header').find('.fa-chevron-down');
    icon.toggleClass('rotate-icon');
});

// Eliminar el código anterior del acordeón general y añadir esto:
document.querySelectorAll('.accordion .card-header').forEach(header => {
    header.addEventListener('click', function() {
        const icon = this.querySelector('.fa-chevron-down');
        document.querySelectorAll('.accordion .fa-chevron-down').forEach(otherIcon => {
            if (otherIcon !== icon) {
                otherIcon.style.transform = 'rotate(-90deg)';
            }
        });
        icon.style.transform = this.getAttribute('aria-expanded') === 'true' ? 
            'rotate(-90deg)' : 'rotate(0deg)';
    });
});
</script>
@stop
