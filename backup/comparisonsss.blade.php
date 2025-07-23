@extends('adminlte::page')

@section('title', 'Comparación de Períodos - Servicios Complementarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line text-primary"></i> Comparación de Períodos</h1>
        <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && !empty($comparisonData))
    <div class="row">
        <!-- Resumen Ejecutivo -->
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-analytics"></i> Resumen Ejecutivo de Comparación
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Respuestas {{ $comparisonData['period1'] }}</span>
                                    <span class="info-box-number">{{ $comparisonData['responses_period1'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Respuestas {{ $comparisonData['period2'] }}</span>
                                    <span class="info-box-number">{{ $comparisonData['responses_period2'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Variación</span>
                                    <span class="info-box-number">
                                        @php
                                            $variation = $comparisonData['responses_period1'] > 0 
                                                ? (($comparisonData['responses_period2'] - $comparisonData['responses_period1']) / $comparisonData['responses_period1']) * 100 
                                                : 0;
                                        @endphp
                                        {{ number_format($variation, 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-secondary">
                                <span class="info-box-icon"><i class="fas fa-trend-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tendencia</span>
                                    <span class="info-box-number">
                                        @if($variation > 5)
                                            <i class="fas fa-arrow-up text-success"></i> Crecimiento
                                        @elseif($variation < -5)
                                            <i class="fas fa-arrow-down text-danger"></i> Disminución
                                        @else
                                            <i class="fas fa-minus text-warning"></i> Estable
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparación de Servicios -->
    <div class="row">
        <!-- Cafetería -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-utensils"></i> Comparación Cafetería</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="cafeteriaComparisonChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            @php 
                                $cafeteria1 = $comparisonData['cafeteria_period1'];
                                $cafeteria2 = $comparisonData['cafeteria_period2'];
                            @endphp
                            <div class="col-6">
                                <strong>{{ $comparisonData['period1'] }}</strong>
                                <ul class="list-unstyled text-sm">
                                    <li>Calidad/Sabor: <span class="float-right">{{ number_format($cafeteria1['calidad_sabor'] ?? 0, 1) }}%</span></li>
                                    <li>Porciones: <span class="float-right">{{ number_format($cafeteria1['porcion_satisfaccion'] ?? 0, 1) }}%</span></li>
                                    <li>Menú: <span class="float-right">{{ number_format($cafeteria1['menu_calidad'] ?? 0, 1) }}%</span></li>
                                    <li>Variedad: <span class="float-right">{{ number_format($cafeteria1['variedad_menu'] ?? 0, 1) }}%</span></li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <strong>{{ $comparisonData['period2'] }}</strong>
                                <ul class="list-unstyled text-sm">
                                    <li>Calidad/Sabor: 
                                        <span class="float-right">
                                            {{ number_format($cafeteria2['calidad_sabor'] ?? 0, 1) }}%
                                            @php $diff = ($cafeteria2['calidad_sabor'] ?? 0) - ($cafeteria1['calidad_sabor'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Porciones: 
                                        <span class="float-right">
                                            {{ number_format($cafeteria2['porcion_satisfaccion'] ?? 0, 1) }}%
                                            @php $diff = ($cafeteria2['porcion_satisfaccion'] ?? 0) - ($cafeteria1['porcion_satisfaccion'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Menú: 
                                        <span class="float-right">
                                            {{ number_format($cafeteria2['menu_calidad'] ?? 0, 1) }}%
                                            @php $diff = ($cafeteria2['menu_calidad'] ?? 0) - ($cafeteria1['menu_calidad'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Variedad: 
                                        <span class="float-right">
                                            {{ number_format($cafeteria2['variedad_menu'] ?? 0, 1) }}%
                                            @php $diff = ($cafeteria2['variedad_menu'] ?? 0) - ($cafeteria1['variedad_menu'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transporte -->
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bus"></i> Comparación Transporte</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="transportComparisonChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            @php 
                                $transport1 = $comparisonData['transport_period1'];
                                $transport2 = $comparisonData['transport_period2'];
                            @endphp
                            <div class="col-6">
                                <strong>{{ $comparisonData['period1'] }}</strong>
                                <ul class="list-unstyled text-sm">
                                    <li>Puntualidad: <span class="float-right">{{ number_format($transport1['puntualidad'] ?? 0, 1) }}%</span></li>
                                    <li>Limpieza: <span class="float-right">{{ number_format($transport1['limpieza_vehiculo'] ?? 0, 1) }}%</span></li>
                                    <li>Trato Personal: <span class="float-right">{{ number_format($transport1['trato_personal'] ?? 0, 1) }}%</span></li>
                                    <li>Comunicación: <span class="float-right">{{ number_format($transport1['comunicacion'] ?? 0, 1) }}%</span></li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <strong>{{ $comparisonData['period2'] }}</strong>
                                <ul class="list-unstyled text-sm">
                                    <li>Puntualidad: 
                                        <span class="float-right">
                                            {{ number_format($transport2['puntualidad'] ?? 0, 1) }}%
                                            @php $diff = ($transport2['puntualidad'] ?? 0) - ($transport1['puntualidad'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Limpieza: 
                                        <span class="float-right">
                                            {{ number_format($transport2['limpieza_vehiculo'] ?? 0, 1) }}%
                                            @php $diff = ($transport2['limpieza_vehiculo'] ?? 0) - ($transport1['limpieza_vehiculo'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Trato Personal: 
                                        <span class="float-right">
                                            {{ number_format($transport2['trato_personal'] ?? 0, 1) }}%
                                            @php $diff = ($transport2['trato_personal'] ?? 0) - ($transport1['trato_personal'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                    <li>Comunicación: 
                                        <span class="float-right">
                                            {{ number_format($transport2['comunicacion'] ?? 0, 1) }}%
                                            @php $diff = ($transport2['comunicacion'] ?? 0) - ($transport1['comunicacion'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <i class="fas fa-arrow-up text-success ml-1"></i>
                                            @elseif($diff < 0)
                                                <i class="fas fa-arrow-down text-danger ml-1"></i>
                                            @else
                                                <i class="fas fa-minus text-muted ml-1"></i>
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Detallado -->
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Análisis Detallado de Tendencias</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="detailedComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conclusiones y Recomendaciones -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-thumbs-up"></i> Mejoras Identificadas</h3>
                </div>
                <div class="card-body">
                    @php
                        $improvements = [];
                        
                        // Verificar mejoras en cafetería
                        foreach(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu'] as $metric) {
                            $diff = ($cafeteria2[$metric] ?? 0) - ($cafeteria1[$metric] ?? 0);
                            if ($diff > 2) {
                                $improvements[] = [
                                    'service' => 'Cafetería',
                                    'metric' => $metric,
                                    'improvement' => $diff
                                ];
                            }
                        }
                        
                        // Verificar mejoras en transporte
                        foreach(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'] as $metric) {
                            $diff = ($transport2[$metric] ?? 0) - ($transport1[$metric] ?? 0);
                            if ($diff > 2) {
                                $improvements[] = [
                                    'service' => 'Transporte',
                                    'metric' => $metric,
                                    'improvement' => $diff
                                ];
                            }
                        }
                    @endphp
                    
                    @if(count($improvements) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($improvements as $improvement)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-{{ $improvement['service'] === 'Cafetería' ? 'success' : 'info' }}">
                                                {{ $improvement['service'] }}
                                            </span>
                                            <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $improvement['metric'])) }}</span>
                                        </div>
                                        <span class="text-success font-weight-bold">
                                            +{{ number_format($improvement['improvement'], 1) }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No se identificaron mejoras significativas (>2%) entre los períodos comparados.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Áreas de Atención</h3>
                </div>
                <div class="card-body">
                    @php
                        $concerns = [];
                        
                        // Verificar deterioros en cafetería
                        foreach(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu'] as $metric) {
                            $diff = ($cafeteria2[$metric] ?? 0) - ($cafeteria1[$metric] ?? 0);
                            if ($diff < -2) {
                                $concerns[] = [
                                    'service' => 'Cafetería',
                                    'metric' => $metric,
                                    'decline' => abs($diff)
                                ];
                            }
                        }
                        
                        // Verificar deterioros en transporte
                        foreach(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'] as $metric) {
                            $diff = ($transport2[$metric] ?? 0) - ($transport1[$metric] ?? 0);
                            if ($diff < -2) {
                                $concerns[] = [
                                    'service' => 'Transporte',
                                    'metric' => $metric,
                                    'decline' => abs($diff)
                                ];
                            }
                        }
                    @endphp
                    
                    @if(count($concerns) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($concerns as $concern)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-{{ $concern['service'] === 'Cafetería' ? 'success' : 'info' }}">
                                                {{ $concern['service'] }}
                                            </span>
                                            <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $concern['metric'])) }}</span>
                                        </div>
                                        <span class="text-danger font-weight-bold">
                                            -{{ number_format($concern['decline'], 1) }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            No se identificaron deterioros significativos (>2%) entre los períodos comparados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- No data state -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay datos de comparación disponibles</h4>
                    <p class="text-muted">
                        Para realizar una comparación necesitas al menos dos períodos con datos de encuestas.
                    </p>
                    <a href="{{ route('surveys.complementary-services.transport.upload') }}" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Cargar Encuestas
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
<style>
    .card {
        border-radius: 15px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 30px rgba(0,0,0,0.15);
    }
    
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-1px);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        max-height: 300px;
    }
    
    .chart-container canvas {
        max-height: 100% !important;
    }
    
    .list-group-item {
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .comparison-metric {
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    
    .metric-improved {
        background-color: rgba(40, 167, 69, 0.1);
        border-left: 3px solid #28a745;
    }
    
    .metric-declined {
        background-color: rgba(220, 53, 69, 0.1);
        border-left: 3px solid #dc3545;
    }
    
    .metric-stable {
        background-color: rgba(108, 117, 125, 0.1);
        border-left: 3px solid #6c757d;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(isset($comparisonData) && !empty($comparisonData))
// Comparison data
const comparisonData = @json($comparisonData);

document.addEventListener('DOMContentLoaded', function() {
    initializeCafeteriaComparison();
    initializeTransportComparison();
    initializeDetailedComparison();
});

function initializeCafeteriaComparison() {
    const ctx = document.getElementById('cafeteriaComparisonChart');
    if (!ctx) return;
    
    const cafeteria1 = comparisonData.cafeteria_period1;
    const cafeteria2 = comparisonData.cafeteria_period2;
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Calidad/Sabor', 'Porciones', 'Menú', 'Variedad', 'Temperatura', 'Limpieza', 'Trato'],
            datasets: [{
                label: comparisonData.period1,
                data: [
                    cafeteria1.calidad_sabor || 0,
                    cafeteria1.porcion_satisfaccion || 0,
                    cafeteria1.menu_calidad || 0,
                    cafeteria1.variedad_menu || 0,
                    cafeteria1.temperatura_adecuada || 0,
                    cafeteria1.limpieza_comedor || 0,
                    cafeteria1.trato_personal || 0
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)'
            }, {
                label: comparisonData.period2,
                data: [
                    cafeteria2.calidad_sabor || 0,
                    cafeteria2.porcion_satisfaccion || 0,
                    cafeteria2.menu_calidad || 0,
                    cafeteria2.variedad_menu || 0,
                    cafeteria2.temperatura_adecuada || 0,
                    cafeteria2.limpieza_comedor || 0,
                    cafeteria2.trato_personal || 0
                ],
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(40, 167, 69, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        }
    });
}

function initializeTransportComparison() {
    const ctx = document.getElementById('transportComparisonChart');
    if (!ctx) return;
    
    const transport1 = comparisonData.transport_period1;
    const transport2 = comparisonData.transport_period2;
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Puntualidad', 'Limpieza', 'Trato Personal', 'Comunicación'],
            datasets: [{
                label: comparisonData.period1,
                data: [
                    transport1.puntualidad || 0,
                    transport1.limpieza_vehiculo || 0,
                    transport1.trato_personal || 0,
                    transport1.comunicacion || 0
                ],
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(255, 193, 7, 1)'
            }, {
                label: comparisonData.period2,
                data: [
                    transport2.puntualidad || 0,
                    transport2.limpieza_vehiculo || 0,
                    transport2.trato_personal || 0,
                    transport2.comunicacion || 0
                ],
                backgroundColor: 'rgba(23, 162, 184, 0.2)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(23, 162, 184, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        }
    });
}

function initializeDetailedComparison() {
    const ctx = document.getElementById('detailedComparisonChart');
    if (!ctx) return;
    
    const cafeteria1 = comparisonData.cafeteria_period1;
    const cafeteria2 = comparisonData.cafeteria_period2;
    const transport1 = comparisonData.transport_period1;
    const transport2 = comparisonData.transport_period2;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                'Calidad/Sabor', 'Porciones', 'Menú', 'Variedad', 
                'Temperatura', 'Limpieza', 'Trato Cafetería',
                'Puntualidad', 'Limpieza Vehículo', 'Trato Transporte', 'Comunicación'
            ],
            datasets: [{
                label: comparisonData.period1,
                data: [
                    cafeteria1.calidad_sabor || 0,
                    cafeteria1.porcion_satisfaccion || 0,
                    cafeteria1.menu_calidad || 0,
                    cafeteria1.variedad_menu || 0,
                    cafeteria1.temperatura_adecuada || 0,
                    cafeteria1.limpieza_comedor || 0,
                    cafeteria1.trato_personal || 0,
                    transport1.puntualidad || 0,
                    transport1.limpieza_vehiculo || 0,
                    transport1.trato_personal || 0,
                    transport1.comunicacion || 0
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: comparisonData.period2,
                data: [
                    cafeteria2.calidad_sabor || 0,
                    cafeteria2.porcion_satisfaccion || 0,
                    cafeteria2.menu_calidad || 0,
                    cafeteria2.variedad_menu || 0,
                    cafeteria2.temperatura_adecuada || 0,
                    cafeteria2.limpieza_comedor || 0,
                    cafeteria2.trato_personal || 0,
                    transport2.puntualidad || 0,
                    transport2.limpieza_vehiculo || 0,
                    transport2.trato_personal || 0,
                    transport2.comunicacion || 0
                ],
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
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
            }
        }
    });
}
@endif

console.log('Comparación de Períodos - Servicios Complementarios cargada correctamente');
</script>
@stop
