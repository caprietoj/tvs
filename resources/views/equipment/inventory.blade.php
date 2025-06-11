@extends('adminlte::page')

@section('title', 'Inventario de Equipos')

@section('content_header')
    <h1 class="text-primary mb-4">
        <i class="fas fa-boxes mr-2"></i>
        Inventario de Equipos
        <small class="text-muted font-weight-light">Control de dispositivos</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Resumen del Inventario -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-primary card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Bachillerato
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-gradient-primary h-100">
                                <span class="info-box-icon">
                                    <i class="fas fa-laptop"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Portátiles</span>
                                    <span class="info-box-number display-4">
                                        {{ $equipment->where('section', 'bachillerato')->where('type', 'laptop')->sum('total_units') }}
                                    </span>
                                    <div class="mt-3">
                                        <small class="text-white">
                                            <i class="fas fa-info-circle"></i>
                                            Última actualización: 
                                            @php
                                                $lastUpdate = $equipment->where('section', 'bachillerato')
                                                    ->where('type', 'laptop')
                                                    ->max('updated_at');
                                            @endphp
                                            {{ $lastUpdate ? $lastUpdate->diffForHumans() : 'No disponible' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-gradient-info h-100">
                                <span class="info-box-icon">
                                    <i class="fas fa-tablet-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">iPads</span>
                                    <span class="info-box-number display-4">
                                        {{ $equipment->where('section', 'bachillerato')->where('type', 'ipad')->sum('total_units') }}
                                    </span>
                                    <div class="mt-3">
                                        <small class="text-white">
                                            <i class="fas fa-info-circle"></i>
                                            Última actualización: 
                                            @php
                                                $lastUpdate = $equipment->where('section', 'bachillerato')
                                                    ->where('type', 'ipad')
                                                    ->max('updated_at');
                                            @endphp
                                            {{ $lastUpdate ? $lastUpdate->diffForHumans() : 'No disponible' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-success card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-child mr-2"></i>
                        Preescolar y Primaria
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-gradient-success h-100">
                        <span class="info-box-icon">
                            <i class="fas fa-tablet-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">iPads</span>
                            <span class="info-box-number display-4">
                                {{ $equipment->where('section', 'preescolar_primaria')->where('type', 'ipad')->sum('total_units') }}
                            </span>
                            <div class="mt-3">
                                <small class="text-white">
                                    <i class="fas fa-info-circle"></i>
                                    Última actualización: 
                                    @php
                                        $lastUpdate = $equipment->where('section', 'preescolar_primaria')
                                            ->where('type', 'ipad')
                                            ->max('updated_at');
                                    @endphp
                                    {{ $lastUpdate ? $lastUpdate->diffForHumans() : 'No disponible' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulario para Bachillerato -->
        <div class="col-md-6">
            <div class="card card-primary card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Registrar Equipos - Bachillerato
                    </h3>
                </div>
                <div class="card-body">
                    <form id="laptopForm" action="{{ route('equipment.store') }}" method="POST" class="mb-4 equipment-form">
                        @csrf
                        <input type="hidden" name="section" value="bachillerato">
                        <div class="form-group">
                            <label class="font-weight-bold d-block">
                                <i class="fas fa-laptop mr-1"></i> Portátiles
                                <small class="text-muted ml-2">Ingrese la cantidad a registrar</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="number" 
                                       name="total_units" 
                                       class="form-control units-input" 
                                       required 
                                       min="1" 
                                       placeholder="Cantidad"
                                       data-type="laptop">
                                <input type="hidden" name="type" value="laptop">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-register">
                                        <i class="fas fa-plus mr-1"></i> Registrar
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle"></i>
                                Los equipos registrados se agregarán al inventario actual
                            </small>
                        </div>
                    </form>

                    <form id="ipadBachForm" action="{{ route('equipment.store') }}" method="POST" class="equipment-form">
                        @csrf
                        <input type="hidden" name="section" value="bachillerato">
                        <div class="form-group">
                            <label class="font-weight-bold d-block">
                                <i class="fas fa-tablet-alt mr-1"></i> iPads
                                <small class="text-muted ml-2">Ingrese la cantidad a registrar</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="number" 
                                       name="total_units" 
                                       class="form-control units-input" 
                                       required 
                                       min="1" 
                                       placeholder="Cantidad"
                                       data-type="ipad">
                                <input type="hidden" name="type" value="ipad">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info btn-register">
                                        <i class="fas fa-plus mr-1"></i> Registrar
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle"></i>
                                Los equipos registrados se agregarán al inventario actual
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Formulario para Preescolar y Primaria -->
        <div class="col-md-6">
            <div class="card card-success card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Registrar Equipos - Preescolar y Primaria
                    </h3>
                </div>
                <div class="card-body">
                    <form id="ipadPrimariaForm" action="{{ route('equipment.store') }}" method="POST" class="equipment-form">
                        @csrf
                        <input type="hidden" name="section" value="preescolar_primaria">
                        <input type="hidden" name="type" value="ipad">
                        <div class="form-group">
                            <label class="font-weight-bold d-block">
                                <i class="fas fa-tablet-alt mr-1"></i> iPads
                                <small class="text-muted ml-2">Ingrese la cantidad a registrar</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="number" 
                                       name="total_units" 
                                       class="form-control units-input" 
                                       required 
                                       min="1" 
                                       placeholder="Cantidad"
                                       data-type="ipad">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success btn-register">
                                        <i class="fas fa-plus mr-1"></i> Registrar
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle"></i>
                                Los equipos registrados se agregarán al inventario actual
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Procesando...</span>
        </div>
        <div class="mt-3 text-primary">Registrando equipos...</div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --info: #17a2b8;
    }

    /* Headers and Text */
    .text-primary { color: var(--primary) !important; }
    h1 { 
        font-weight: 600;
        font-size: 1.75rem;
    }
    h1 small {
        font-size: 1rem;
        opacity: 0.7;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .card-outline {
        border-top: 3px solid var(--primary);
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.125);
        padding: 1.25rem;
    }

    /* Info Box Styles */
    .info-box {
        border-radius: 12px;
        min-height: 140px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .info-box-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        border-radius: 12px;
        margin: 1rem;
        background: rgba(255, 255, 255, 0.2);
    }

    .display-4 {
        font-size: 2.5rem;
        font-weight: 600;
        line-height: 1.2;
    }

    /* Form Controls */
    .form-control {
        border-radius: 8px;
        border: 2px solid #dee2e6;
        padding: 0.75rem 1rem;
        height: auto;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
        border: 2px solid #dee2e6;
        border-right: none;
        background-color: #f8f9fa;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-register {
        min-width: 120px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Loading Overlay */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .spinner-container {
        text-align: center;
        padding: 2rem;
        border-radius: 12px;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .container-fluid > * {
        animation: fadeIn 0.6s ease-out forwards;
    }

    .row > div:nth-child(2) {
        animation-delay: 0.2s;
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de campos numéricos
    const unitsInputs = document.querySelectorAll('.units-input');
    unitsInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value < 1) {
                this.value = 1;
            }
        });
    });

    // Manejo de formularios
    const forms = document.querySelectorAll('.equipment-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const type = this.querySelector('input[name="type"]').value;
            const units = this.querySelector('input[name="total_units"]').value;
            const section = this.querySelector('input[name="section"]').value;

            Swal.fire({
                title: '¿Confirmar registro?',
                html: `
                    <div class="text-left">
                        <p><strong>Tipo:</strong> ${type === 'laptop' ? 'Portátiles' : 'iPads'}</p>
                        <p><strong>Cantidad:</strong> ${units} unidad(es)</p>
                        <p><strong>Sección:</strong> ${section === 'bachillerato' ? 'Bachillerato' : 'Preescolar y Primaria'}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#364E76',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    this.submit();
                }
            });
        });
    });

    function showLoading() {
        const overlay = document.querySelector('.loading-overlay');
        overlay.style.display = 'flex';
    }

    // Mensajes de éxito o error
    @if(session('success'))
        Swal.fire({
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Error',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#364E76'
        });
    @endif
});
</script>
@stop