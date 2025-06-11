@extends('adminlte::page')

@section('title', 'Gestión de Equipos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-laptop-house mr-2"></i>
            Gestión de Equipos
        </h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Cards de Bachillerato -->
    <div class="card card-outline card-primary mb-4 shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-graduation-cap mr-2"></i>
                Bachillerato
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Portátiles -->
                <div class="col-md-6">
                    <div class="info-box bg-gradient-primary h-100">
                        <span class="info-box-icon elevation-2">
                            <i class="fas fa-laptop"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold mb-2">Portátiles</span>
                            <div class="progress-description mb-2">
                                @php
                                    $laptops = $equipment->where('section', 'bachillerato')->where('type', 'laptop')->first();
                                    $available = $equipment->where('section', 'bachillerato')->where('type', 'laptop')->sum('available_units');
                                    $total = $equipment->where('section', 'bachillerato')->where('type', 'laptop')->sum('total_units');
                                    $percentage = $laptops ? ($laptops->available_units / $laptops->total_units) * 100 : 0;
                                @endphp
                                <span class="text-lg">{{ $available }} / {{ $total }}</span>
                                <span class="text-sm ml-2">unidades disponibles</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <span class="progress-description mt-2">
                                {{ number_format($percentage, 1) }}% disponible
                            </span>
                        </div>
                    </div>
                </div>
                <!-- iPads -->
                <div class="col-md-6">
                    <div class="info-box bg-gradient-info h-100">
                        <span class="info-box-icon elevation-2">
                            <i class="fas fa-tablet-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold mb-2">iPads</span>
                            <div class="progress-description mb-2">
                                @php
                                    $ipads = $equipment->where('section', 'bachillerato')->where('type', 'ipad')->first();
                                    $available = $equipment->where('section', 'bachillerato')->where('type', 'ipad')->sum('available_units');
                                    $total = $equipment->where('section', 'bachillerato')->where('type', 'ipad')->sum('total_units');
                                    $percentage = $ipads ? ($ipads->available_units / $ipads->total_units) * 100 : 0;
                                @endphp
                                <span class="text-lg">{{ $available }} / {{ $total }}</span>
                                <span class="text-sm ml-2">unidades disponibles</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <span class="progress-description mt-2">
                                {{ number_format($percentage, 1) }}% disponible
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Preescolar y Primaria -->
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-child mr-2"></i>
                Preescolar y Primaria
            </h3>
        </div>
        <div class="card-body">
            <div class="info-box bg-gradient-success h-100">
                <span class="info-box-icon elevation-2">
                    <i class="fas fa-tablet-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-bold mb-2">iPads</span>
                    <div class="progress-description mb-2">
                        @php
                            $ipads = $equipment->where('section', 'preescolar_primaria')->where('type', 'ipad')->first();
                            $available = $equipment->where('section', 'preescolar_primaria')->where('type', 'ipad')->sum('available_units');
                            $total = $equipment->where('section', 'preescolar_primaria')->where('type', 'ipad')->sum('total_units');
                            $percentage = $ipads ? ($ipads->available_units / $ipads->total_units) * 100 : 0;
                        @endphp
                        <span class="text-lg">{{ $available }} / {{ $total }}</span>
                        <span class="text-sm ml-2">unidades disponibles</span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $percentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <span class="progress-description mt-2">
                        {{ number_format($percentage, 1) }}% disponible
                    </span>
                </div>
            </div>
        </div>
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

    /* Card Styles */
    .card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        padding: 1.25rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Info Box Customization */
    .info-box {
        border-radius: 10px;
        min-height: 180px;
    }

    .info-box-icon {
        border-radius: 10px;
        font-size: 2rem;
    }

    .info-box .progress {
        height: 8px;
        border-radius: 4px;
        background-color: rgba(255,255,255,0.3);
    }

    .info-box .progress-bar {
        border-radius: 4px;
        background-color: rgba(255,255,255,0.8);
    }

    .info-box-content {
        padding: 1.25rem 0;
    }

    .text-lg {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .progress-description {
        color: rgba(255,255,255,0.9);
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .container-fluid > * {
        animation: fadeIn 0.6s ease-out forwards;
    }

    .card:nth-child(2) {
        animation-delay: 0.2s;
    }

    /* Status Colors */
    .bg-gradient-primary {
        background: linear-gradient(45deg, var(--primary), #4a6fad);
    }

    .bg-gradient-info {
        background: linear-gradient(45deg, var(--info), #3bc9db);
    }

    .bg-gradient-success {
        background: linear-gradient(45deg, var(--success), #34ce57);
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mensaje de éxito
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    @endif
});
</script>
@stop