@extends('adminlte::page')

@section('title', 'Análisis Comparativo - Encuestas Padres de Familia')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line text-primary"></i> Análisis Comparativo de Encuestas de Padres de Familia</h1>
        <a href="{{ route('surveys.parent-student.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && !empty($comparisonData))
    
    <!-- Resumen Ejecutivo -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Informe Ejecutivo de Análisis Comparativo
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-clipboard-list"></i> Resumen Ejecutivo</h5>
                        <p class="mb-2">
                            El presente informe presenta el análisis comparativo de satisfacción de los servicios de 
                            cafetería y transporte según las encuestas de padres de familia entre los períodos 
                            <strong>{{ $comparisonData['period1'] }}</strong> y <strong>{{ $comparisonData['period2'] }}</strong>, 
                            con el objetivo de evaluar la evolución de la calidad de los servicios y el impacto de las 
                            acciones de mejora implementadas.
                        </p>
                        <p class="mb-2">
                            <strong>Metodología:</strong> Se aplicó la misma encuesta de satisfacción en ambos períodos para 
                            garantizar la comparabilidad de los resultados. Los indicadores se miden en escala de 1 a 5, 
                            donde 1 es muy insatisfecho y 5 es muy satisfecho.
                        </p>
                        <p class="mb-0">
                            <strong>Alcance de la muestra:</strong> {{ $comparisonData['responses_period1'] }} respuestas 
                            en {{ $comparisonData['period1'] }} vs {{ $comparisonData['responses_period2'] }} respuestas 
                            en {{ $comparisonData['period2'] }}.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period1'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period1'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period2'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period2'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación de Respuestas</span>
                            <span class="info-box-number">
                                @php
                                    $responseChange = $comparisonData['responses_period1'] > 0 
                                        ? round((($comparisonData['responses_period2'] - $comparisonData['responses_period1']) / $comparisonData['responses_period1']) * 100, 1)
                                        : 0;
                                @endphp
                                {{ $responseChange > 0 ? '+' : '' }}{{ $responseChange }}%
                            </span>
                            <div class="progress">
                                <div class="progress-bar {{ $responseChange > 0 ? 'bg-success' : ($responseChange < 0 ? 'bg-danger' : 'bg-warning') }}" 
                                     style="width: {{ abs($responseChange) > 100 ? 100 : abs($responseChange) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $comparisonData['responses_period1'] }} → {{ $comparisonData['responses_period2'] }} respuestas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Cafetería -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-utensils"></i> Análisis Comparativo - Servicio de Cafetería
            </h3>
        </div>
        <div class="card-body">
            @if(!empty($comparisonData['cafeteria_differences']))
                <div class="row">
                    @foreach($comparisonData['cafeteria_differences'] as $metric => $diff)
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ ucwords(str_replace('_', ' ', $metric)) }}</h6>
                                        <span class="badge badge-{{ $diff['trend'] == 'mejora' ? 'success' : ($diff['trend'] == 'disminucion' ? 'danger' : 'secondary') }}">
                                            {{ $diff['trend'] == 'mejora' ? 'Mejora' : ($diff['trend'] == 'disminucion' ? 'Disminución' : 'Estable') }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-sm mb-2">
                                        <span>{{ $comparisonData['period1'] }}: <strong>{{ number_format($diff['period1'], 1) }}%</strong></span>
                                        <span>{{ $comparisonData['period2'] }}: <strong>{{ number_format($diff['period2'], 1) }}%</strong></span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ $diff['period1'] }}%"></div>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $diff['period2'] }}%"></div>
                                    </div>
                                    <div class="text-center">
                                        <span class="{{ $diff['trend_class'] }}">
                                            <i class="fas fa-{{ $diff['trend'] == 'mejora' ? 'arrow-up' : ($diff['trend'] == 'disminucion' ? 'arrow-down' : 'minus') }}"></i>
                                            {{ $diff['difference'] > 0 ? '+' : '' }}{{ number_format($diff['difference'], 1) }}% 
                                            ({{ $diff['percentage_change'] > 0 ? '+' : '' }}{{ $diff['percentage_change'] }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No hay datos suficientes de cafetería para comparar.
                </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Transporte -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bus"></i> Análisis Comparativo - Servicio de Transporte
            </h3>
        </div>
        <div class="card-body">
            @if(!empty($comparisonData['transport_differences']))
                <div class="row">
                    @foreach($comparisonData['transport_differences'] as $metric => $diff)
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ ucwords(str_replace('_', ' ', $metric)) }}</h6>
                                        <span class="badge badge-{{ $diff['trend'] == 'mejora' ? 'success' : ($diff['trend'] == 'disminucion' ? 'danger' : 'secondary') }}">
                                            {{ $diff['trend'] == 'mejora' ? 'Mejora' : ($diff['trend'] == 'disminucion' ? 'Disminución' : 'Estable') }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-sm mb-2">
                                        <span>{{ $comparisonData['period1'] }}: <strong>{{ number_format($diff['period1'], 1) }}%</strong></span>
                                        <span>{{ $comparisonData['period2'] }}: <strong>{{ number_format($diff['period2'], 1) }}%</strong></span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $diff['period1'] }}%"></div>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ $diff['period2'] }}%"></div>
                                    </div>
                                    <div class="text-center">
                                        <span class="{{ $diff['trend_class'] }}">
                                            <i class="fas fa-{{ $diff['trend'] == 'mejora' ? 'arrow-up' : ($diff['trend'] == 'disminucion' ? 'arrow-down' : 'minus') }}"></i>
                                            {{ $diff['difference'] > 0 ? '+' : '' }}{{ number_format($diff['difference'], 1) }}% 
                                            ({{ $diff['percentage_change'] > 0 ? '+' : '' }}{{ $diff['percentage_change'] }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No hay datos suficientes de transporte para comparar.
                </div>
            @endif
        </div>
    </div>

    <!-- Gráfico Comparativo -->
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Comparación Visual de Métricas
            </h3>
        </div>
        <div class="card-body">
            <canvas id="comparisonChart" style="height: 400px;"></canvas>
        </div>
    </div>

    @else
    
    <!-- Formulario de Selección -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cog"></i> Configurar Análisis Comparativo
            </h3>
        </div>
        <div class="card-body">
            @if(isset($error))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> {{ $error }}
                </div>
            @endif
            
            <form method="GET" action="{{ route('surveys.parent-student.comparison') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period1">Período Base (Anterior) *</label>
                            <select name="period1" id="period1" class="form-control" required>
                                <option value="">Seleccionar período...</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->period }}">{{ $period->period }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period2">Período Comparación (Actual) *</label>
                            <select name="period2" id="period2" class="form-control" required>
                                <option value="">Seleccionar período...</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->period }}">{{ $period->period }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="grade">Filtrar por Grado</label>
                            <select name="grade" id="grade" class="form-control">
                                <option value="all">Todos los grados</option>
                                <option value="Preescolar">Preescolar</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Secundaria">Secundaria</option>
                                <option value="Bachillerato">Bachillerato</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="service">Servicios a Comparar</label>
                            <select name="service" id="service" class="form-control">
                                <option value="both">Ambos servicios (Cafetería y Transporte)</option>
                                <option value="cafeteria">Solo Cafetería</option>
                                <option value="transport">Solo Transporte</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-chart-line"></i> Generar Análisis Comparativo
                    </button>
                    <button type="button" class="btn btn-info ml-2" onclick="fillMostRecentPeriods()">
                        <i class="fas fa-magic"></i> Usar Períodos Más Recientes
                    </button>
                </div>
            </form>
            
            <div class="mt-4">
                <div class="alert alert-info">
                    <h5><i class="fas fa-lightbulb"></i> Sugerencias para un Análisis Efectivo</h5>
                    <ul class="mb-0">
                        <li><strong>Períodos consecutivos:</strong> Para mejor interpretación, compare períodos consecutivos o con intervalos regulares.</li>
                        <li><strong>Contexto temporal:</strong> Considere eventos importantes (cambios de proveedor, mejoras implementadas) entre los períodos.</li>
                        <li><strong>Tamaño de muestra:</strong> Asegúrese de que ambos períodos tengan suficientes respuestas para conclusiones confiables.</li>
                        <li><strong>Filtros específicos:</strong> Use filtros por grado para análisis más detallados según grupos de edad.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    @endif
@endsection

@section('css')
<style>
    .card-title {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .progress {
        background-color: #f4f4f4;
    }
    
    .info-box {
        min-height: 90px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .alert h5 {
        color: inherit;
        font-weight: 600;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(isset($comparisonData) && !empty($comparisonData))
// Preparar datos para el gráfico
const cafeteriaData = @json($comparisonData['cafeteria_differences'] ?? []);
const transportData = @json($comparisonData['transport_differences'] ?? []);

const labels = [
    ...Object.keys(cafeteriaData).map(key => 'Cafetería: ' + key.replace(/_/g, ' ')),
    ...Object.keys(transportData).map(key => 'Transporte: ' + key.replace(/_/g, ' '))
];

const period1Data = [
    ...Object.values(cafeteriaData).map(item => item.period1),
    ...Object.values(transportData).map(item => item.period1)
];

const period2Data = [
    ...Object.values(cafeteriaData).map(item => item.period2),
    ...Object.values(transportData).map(item => item.period2)
];

const ctx = document.getElementById('comparisonChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: '{{ $comparisonData["period1"] }}',
            data: period1Data,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: '{{ $comparisonData["period2"] }}',
            data: period2Data,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + '%';
                    }
                }
            }
        }
    }
});
@endif

function fillMostRecentPeriods() {
    const period1Select = document.getElementById('period1');
    const period2Select = document.getElementById('period2');
    
    if (period1Select.options.length > 2 && period2Select.options.length > 2) {
        period2Select.selectedIndex = 1; // Más reciente
        period1Select.selectedIndex = 2; // Segundo más reciente
    }
}

// Auto-seleccionar períodos más recientes al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    @if(!isset($comparisonData))
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('period1') && !urlParams.has('period2')) {
        fillMostRecentPeriods();
    }
    @endif
});
</script>
@endsection
