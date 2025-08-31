@extends('adminlte::page')

@section('title', 'Configurar Presupuesto Secciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-cogs"></i> Configurar Presupuesto de Secciones
        </h1>
        <a href="{{ route('presupuesto.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Presupuesto
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calculator mr-2"></i> Configuración de Presupuestos por Sección - Año {{ $year }}
                </h3>
            </div>
            
            <form action="{{ route('presupuesto.guardar-presupuesto-secciones') }}" method="POST" id="presupuesto-form">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                
                <div class="card-body">
                    <div class="alert alert-info border-left-info">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-info-circle fa-2x text-info"></i>
                            </div>
                            <div>
                                <h5 class="mb-2"><i class="fas fa-lightbulb"></i> Información Importante</h5>
                                <ul class="mb-0 small">
                                    <li><strong>Configure el presupuesto total aprobado</strong> para cada sección académica</li>
                                    <li><strong>Distribución automática:</strong> El monto se divide entre los conceptos de la sección</li>
                                    <li><strong>Actualización en tiempo real:</strong> Los cambios se reflejan inmediatamente en "Secciones Generales"</li>
                                    <li><strong>Flexibilidad:</strong> El presupuesto se puede modificar en cualquier momento del año</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Secciones Académicas -->
                    <div class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-graduation-cap text-primary"></i> Secciones Académicas
                        </h4>
                        <div class="row">
                            @php
                                $seccionesAcademicas = ['PREESCOLAR Y PRIMARIA', 'ESCUELA MEDIA', 'ALTA', 'PAI', 'PEP'];
                                $responsables = [
                                    'PREESCOLAR Y PRIMARIA' => 'Ana María Grisales',
                                    'ESCUELA MEDIA' => 'Lorena Hurtado', 
                                    'ALTA' => 'Constanza Bernal',
                                    'PAI' => 'Andrea Flórez',
                                    'PEP' => 'Helena Ortiz'
                                ];
                            @endphp
                            @foreach($seccionesAcademicas as $seccion)
                            @if(in_array($seccion, $secciones))
                            <div class="col-lg-4 col-md-6 mb-4">
                                @php
                                    $presupuestoActual = $presupuestosActuales[$seccion] ?? 0;
                                    $tienePresupuesto = $presupuestoActual > 0;
                                    $cardClass = $tienePresupuesto ? 'border-success' : 'border-warning';
                                @endphp
                                <div class="card {{ $cardClass }} h-100 shadow-sm section-card">
                                    <div class="card-header {{ $tienePresupuesto ? 'bg-success' : 'bg-warning' }} text-white">
                                        <h6 class="mb-0">
                                            @switch($seccion)
                                                @case('PREESCOLAR Y PRIMARIA')
                                                    <i class="fas fa-baby mr-2"></i>
                                                    @break
                                                @case('ESCUELA MEDIA')
                                                    <i class="fas fa-user-graduate mr-2"></i>
                                                    @break
                                                @case('ALTA')
                                                    <i class="fas fa-graduation-cap mr-2"></i>
                                                    @break
                                                @case('PAI')
                                                    <i class="fas fa-globe mr-2"></i>
                                                    @break
                                                @case('PEP')
                                                    <i class="fas fa-puzzle-piece mr-2"></i>
                                                    @break
                                            @endswitch
                                            {{ $seccion }}
                                        </h6>
                                        @if(isset($responsables[$seccion]))
                                        <small class="opacity-75">
                                            <i class="fas fa-user-tie mr-1"></i>{{ $responsables[$seccion] }}
                                        </small>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label for="presupuesto_{{ str_replace(' ', '_', $seccion) }}" class="form-label font-weight-bold">
                                                <i class="fas fa-dollar-sign text-success"></i> Presupuesto Total Aprobado
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="number" 
                                                       step="1" 
                                                       min="0"
                                                       class="form-control form-control-lg @error('presupuestos.' . $seccion) is-invalid @enderror" 
                                                       id="presupuesto_{{ str_replace(' ', '_', $seccion) }}"
                                                       name="presupuestos[{{ $seccion }}]" 
                                                       value="{{ old('presupuestos.' . $seccion, $presupuestoActual) }}"
                                                       placeholder="Ingrese el monto">
                                            </div>
                                            @error('presupuestos.' . $seccion)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                        @if($tienePresupuesto)
                                        <div class="alert alert-success py-2 mb-0">
                                            <small>
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <strong>Configurado:</strong> ${{ number_format($presupuestoActual, 0, ',', '.') }}
                                            </small>
                                        </div>
                                        @else
                                        <div class="alert alert-warning py-2 mb-0">
                                            <small>
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Pendiente:</strong> Sin presupuesto asignado
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Secciones de Apoyo -->
                    <div class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-hands-helping text-info"></i> Secciones de Apoyo
                        </h4>
                        <div class="row">
                            @php
                                $seccionesApoyo = ['BIBLIOTECA', 'DEPORTES', 'PSICOLOGÍA INSTITUCIONAL', 'CAS', 'CONSEJERÍA UNIVERSITARIA', 'DEPARTAMENTO DE APOYO'];
                                $responsablesApoyo = [
                                    'BIBLIOTECA' => 'Laura Rodríguez',
                                    'DEPORTES' => 'Miguel Bernal',
                                    'CAS' => 'María del Pilar Silva',
                                    'CONSEJERÍA UNIVERSITARIA' => 'Diana Torres',
                                    'DEPARTAMENTO DE APOYO' => 'Johana Gavidia'
                                ];
                            @endphp
                            @foreach($seccionesApoyo as $seccion)
                            @if(in_array($seccion, $secciones))
                            <div class="col-lg-4 col-md-6 mb-4">
                                @php
                                    $presupuestoActual = $presupuestosActuales[$seccion] ?? 0;
                                    $tienePresupuesto = $presupuestoActual > 0;
                                    $cardClass = $tienePresupuesto ? 'border-info' : 'border-secondary';
                                @endphp
                                <div class="card {{ $cardClass }} h-100 shadow-sm section-card">
                                    <div class="card-header {{ $tienePresupuesto ? 'bg-info' : 'bg-secondary' }} text-white">
                                        <h6 class="mb-0">
                                            @switch($seccion)
                                                @case('BIBLIOTECA')
                                                    <i class="fas fa-book mr-2"></i>
                                                    @break
                                                @case('DEPORTES')
                                                    <i class="fas fa-running mr-2"></i>
                                                    @break
                                                @case('PSICOLOGÍA INSTITUCIONAL')
                                                    <i class="fas fa-brain mr-2"></i>
                                                    @break
                                                @case('CAS')
                                                    <i class="fas fa-heart mr-2"></i>
                                                    @break
                                                @case('CONSEJERÍA UNIVERSITARIA')
                                                    <i class="fas fa-user-tie mr-2"></i>
                                                    @break
                                                @case('DEPARTAMENTO DE APOYO')
                                                    <i class="fas fa-hands-helping mr-2"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-cog mr-2"></i>
                                            @endswitch
                                            {{ $seccion }}
                                        </h6>
                                        @if(isset($responsablesApoyo[$seccion]))
                                        <small class="opacity-75">
                                            <i class="fas fa-user-tie mr-1"></i>{{ $responsablesApoyo[$seccion] }}
                                        </small>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label for="presupuesto_{{ str_replace(' ', '_', $seccion) }}" class="form-label font-weight-bold">
                                                <i class="fas fa-dollar-sign text-info"></i> Presupuesto Total Aprobado
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="number" 
                                                       step="1" 
                                                       min="0"
                                                       class="form-control form-control-lg @error('presupuestos.' . $seccion) is-invalid @enderror" 
                                                       id="presupuesto_{{ str_replace(' ', '_', $seccion) }}"
                                                       name="presupuestos[{{ $seccion }}]" 
                                                       value="{{ old('presupuestos.' . $seccion, $presupuestoActual) }}"
                                                       placeholder="Ingrese el monto">
                                            </div>
                                            @error('presupuestos.' . $seccion)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                        @if($tienePresupuesto)
                                        <div class="alert alert-info py-2 mb-0">
                                            <small>
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <strong>Configurado:</strong> ${{ number_format($presupuestoActual, 0, ',', '.') }}
                                            </small>
                                        </div>
                                        @else
                                        <div class="alert alert-secondary py-2 mb-0">
                                            <small>
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Pendiente:</strong> Sin presupuesto asignado
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Resumen y Total -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-gradient-dark text-white">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie mr-2"></i> Resumen General del Presupuesto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="text-success">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <span id="total-academicas">$0</span>
                                                </h4>
                                                <p class="mb-0">Secciones Académicas</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="text-info">
                                                    <i class="fas fa-hands-helping"></i>
                                                    <span id="total-apoyo">$0</span>
                                                </h4>
                                                <p class="mb-0">Secciones de Apoyo</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="text-warning">
                                                    <i class="fas fa-calculator"></i>
                                                    <span id="total-general">$0</span>
                                                </h4>
                                                <p class="mb-0">Total General</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress mt-3" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" id="progress-academicas" style="width: 0%"></div>
                                        <div class="progress-bar bg-info" role="progressbar" id="progress-apoyo" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small><span class="badge badge-success">Académicas</span></small>
                                        <small><span class="badge badge-info">Apoyo</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-info-circle text-info fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Estado de la Configuración</h6>
                                    <p class="mb-0 text-muted small">
                                        Los cambios se guardarán y distribuirán automáticamente entre los conceptos de cada sección.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-secondary mr-2" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Restablecer
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="btn-guardar">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
/* Estilos personalizados para mejorar la visualización */
.section-card {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.section-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.section-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007bff, #28a745, #ffc107);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.section-card:hover::before {
    opacity: 1;
}

.card-header h6 {
    font-weight: 600;
    letter-spacing: 0.5px;
}

.input-group-lg .form-control {
    font-size: 1.1rem;
    font-weight: 500;
}

.alert {
    border-left: 4px solid;
    border-radius: 0.375rem;
}

.border-left-info {
    border-left-color: #17a2b8 !important;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.bg-gradient-dark {
    background: linear-gradient(45deg, #343a40, #495057);
}

.opacity-75 {
    opacity: 0.75;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Animación para los números */
@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.count-animation {
    animation: countUp 0.6s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .section-card {
        margin-bottom: 1rem;
    }
    
    .input-group-lg .form-control {
        font-size: 1rem;
    }
    
    .card-header h6 {
        font-size: 0.9rem;
    }
}

/* Loading state */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    margin: -8px 0 0 -8px;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Cards shadow enhancement */
.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Calcular totales iniciales
    calcularTotales();
    
    // Escuchar cambios en los inputs
    $('input[name^="presupuestos"]').on('input', function() {
        calcularTotales();
        actualizarEstadoInput($(this));
    });
    
    // Formatear números mientras se escriben
    $('input[name^="presupuestos"]').on('input', function() {
        const value = $(this).val();
        if (value && !isNaN(value)) {
            // Agregar animación al cambio
            $(this).closest('.section-card').addClass('pulse');
            setTimeout(() => {
                $(this).closest('.section-card').removeClass('pulse');
            }, 300);
        }
    });
    
    // Validación en tiempo real
    $('input[name^="presupuestos"]').on('blur', function() {
        const value = parseFloat($(this).val()) || 0;
        const card = $(this).closest('.section-card');
        const alert = card.find('.alert');
        
        if (value > 0) {
            card.removeClass('border-warning border-secondary').addClass('border-success');
            card.find('.card-header').removeClass('bg-warning bg-secondary').addClass('bg-success');
            alert.removeClass('alert-warning alert-secondary').addClass('alert-success');
            alert.find('strong').text('Configurado:');
            alert.find('i').removeClass('fa-exclamation-triangle').addClass('fa-check-circle');
        } else {
            card.removeClass('border-success').addClass('border-warning');
            card.find('.card-header').removeClass('bg-success').addClass('bg-warning');
            alert.removeClass('alert-success').addClass('alert-warning');
            alert.find('strong').text('Pendiente:');
            alert.find('i').removeClass('fa-check-circle').addClass('fa-exclamation-triangle');
        }
    });

    // Validación del formulario
    $('#presupuesto-form').on('submit', function(e) {
        let hayPresupuesto = false;
        
        $('input[name^="presupuestos"]').each(function() {
            if (parseFloat($(this).val()) > 0) {
                hayPresupuesto = true;
                return false; // break
            }
        });
        
        if (!hayPresupuesto) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe configurar al menos un presupuesto mayor a cero.',
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        // Efecto de carga en el botón
        const btn = $('#btn-guardar');
        btn.addClass('btn-loading').prop('disabled', true);
        
        // Restaurar botón después de un tiempo (el formulario se enviará)
        setTimeout(() => {
            btn.removeClass('btn-loading').prop('disabled', false);
        }, 3000);
    });
});

function calcularTotales() {
    const seccionesAcademicas = ['PREESCOLAR Y PRIMARIA', 'ESCUELA MEDIA', 'ALTA', 'PAI', 'PEP'];
    const seccionesApoyo = ['BIBLIOTECA', 'DEPORTES', 'PSICOLOGÍA INSTITUCIONAL', 'CAS', 'CONSEJERÍA UNIVERSITARIA', 'DEPARTAMENTO DE APOYO'];
    
    let totalAcademicas = 0;
    let totalApoyo = 0;
    
    // Sumar secciones académicas
    seccionesAcademicas.forEach(seccion => {
        const input = $(`input[name="presupuestos[${seccion}]"]`);
        if (input.length) {
            const valor = parseFloat(input.val()) || 0;
            totalAcademicas += valor;
        }
    });
    
    // Sumar secciones de apoyo
    seccionesApoyo.forEach(seccion => {
        const input = $(`input[name="presupuestos[${seccion}]"]`);
        if (input.length) {
            const valor = parseFloat(input.val()) || 0;
            totalApoyo += valor;
        }
    });
    
    const totalGeneral = totalAcademicas + totalApoyo;
    
    // Actualizar displays con animación
    $('#total-academicas').addClass('count-animation').text(formatearNumero(totalAcademicas));
    $('#total-apoyo').addClass('count-animation').text(formatearNumero(totalApoyo));
    $('#total-general').addClass('count-animation').text(formatearNumero(totalGeneral));
    
    // Actualizar barras de progreso
    if (totalGeneral > 0) {
        const porcentajeAcademicas = (totalAcademicas / totalGeneral) * 100;
        const porcentajeApoyo = (totalApoyo / totalGeneral) * 100;
        
        $('#progress-academicas').css('width', porcentajeAcademicas + '%');
        $('#progress-apoyo').css('width', porcentajeApoyo + '%');
    } else {
        $('#progress-academicas').css('width', '0%');
        $('#progress-apoyo').css('width', '0%');
    }
    
    // Remover animación después de un tiempo
    setTimeout(() => {
        $('.count-animation').removeClass('count-animation');
    }, 600);
}

function formatearNumero(numero) {
    return '$' + numero.toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

function actualizarEstadoInput(input) {
    const valor = parseFloat(input.val()) || 0;
    const tarjeta = input.closest('.section-card');
    
    if (valor > 0) {
        tarjeta.find('.alert').html(`
            <small>
                <i class="fas fa-check-circle mr-1"></i>
                <strong>Configurado:</strong> ${formatearNumero(valor)}
            </small>
        `);
    }
}

function resetForm() {
    if (confirm('¿Está seguro de que desea restablecer todos los valores?')) {
        $('input[name^="presupuestos"]').val('');
        calcularTotales();
        
        // Restablecer estados visuales
        $('.section-card').removeClass('border-success border-warning').addClass('border-secondary');
        $('.card-header').removeClass('bg-success bg-warning').addClass('bg-secondary');
        $('.alert').removeClass('alert-success alert-warning').addClass('alert-secondary');
        $('.alert strong').text('Pendiente:');
        $('.alert i').removeClass('fa-check-circle').addClass('fa-exclamation-triangle');
        
        // Feedback visual
        Swal.fire({
            icon: 'success',
            title: 'Formulario Restablecido',
            text: 'Todos los valores han sido limpiados.',
            timer: 2000,
            showConfirmButton: false
        });
    }
}
</script>
@stop
