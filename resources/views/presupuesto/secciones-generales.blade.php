@extends('adminlte::page')

@section('title', 'The Victoria School - Presupuesto Secciones - 2025-2026')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="page-header">
        <h1>The Victoria School</h1>
        <p>Presupuesto por Secciones 2025 - 2026</p>
    </div>
    
    <!-- Cajas de información específicas para secciones -->
    <div class="stats-container">
        <div class="stat-box stat-box-primary">
            <div class="stat-icon">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ count($userSectionPermissions) }}</div>
                <div class="stat-label">Secciones Asignadas</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-success">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">9</div>
                <div class="stat-label">Meses de Ejecución</div>
                <div class="stat-sublabel">Junio 2025 - Febrero 2026</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-warning">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="presupuestoSecciones">
                    @php
                        $totalPresupuesto = 0;
                        foreach($resumenConceptos as $concepto => $datos) {
                            $totalPresupuesto += $datos['presupuesto'] ?? 0;
                        }
                    @endphp
                    {{ number_format($totalPresupuesto, 0, ',', '.') }}
                </div>
                <div class="stat-label">Presupuesto Total Secciones</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-info">
            <div class="stat-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="ejecutadoSecciones">
                    @php
                        $totalEjecutado = 0;
                        foreach($resumenConceptos as $concepto => $datos) {
                            $totalEjecutado += $datos['ejecutado'] ?? 0;
                        }
                    @endphp
                    {{ number_format($totalEjecutado, 0, ',', '.') }}
                </div>
                <div class="stat-label">Ejecutado</div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    
    <!-- Contenido principal de Secciones Generales -->
    <div class="section-content">
        
        <!-- Panel de Secciones Asignadas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Mis Secciones Asignadas
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($userSectionPermissions))
                            <div class="row">
                                @foreach($userSectionPermissions as $seccion)
                                    <div class="col-md-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ ucwords(str_replace('-', ' ', $seccion)) }}</span>
                                                <span class="info-box-number">
                                                    @if(isset($seccionesData[$seccion]))
                                                        {{ count($seccionesData[$seccion]) }} conceptos
                                                    @else
                                                        0 conceptos
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No tienes secciones asignadas. Contacta al administrador.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Conceptos por Sección -->
        @if(!empty($resumenConceptos))
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list-alt mr-2"></i>
                            Resumen de Conceptos - Presupuesto {{ auth()->user()->name }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 40%;">Concepto</th>
                                        <th class="text-right" style="width: 20%;">Presupuesto</th>
                                        <th class="text-right" style="width: 20%;">Ejecutado</th>
                                        <th class="text-right" style="width: 20%;">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalPresupuestoGeneral = 0;
                                        $totalEjecutadoGeneral = 0;
                                        $totalSaldoGeneral = 0;
                                    @endphp
                                    @foreach($resumenConceptos as $concepto => $datos)
                                        @php
                                            $presupuesto = $datos['presupuesto'] ?? 0;
                                            $ejecutado = $datos['ejecutado'] ?? 0;
                                            $saldo = $datos['saldo'] ?? 0;
                                            
                                            if($concepto !== 'TOTAL') {
                                                $totalPresupuestoGeneral += $presupuesto;
                                                $totalEjecutadoGeneral += $ejecutado;
                                                $totalSaldoGeneral += $saldo;
                                            }
                                        @endphp
                                        @if($concepto !== 'TOTAL')
                                        <tr>
                                            <td><strong>{{ $concepto }}</strong></td>
                                            <td class="text-right">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                            <td class="text-right 
                                                @if($ejecutado > $presupuesto) text-danger 
                                                @elseif($ejecutado > 0) text-success 
                                                @else text-muted 
                                                @endif">
                                                {{ number_format($ejecutado, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right 
                                                @if($saldo < 0) text-danger 
                                                @elseif($saldo > 0) text-success 
                                                @else text-muted 
                                                @endif">
                                                {{ number_format($saldo, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    <!-- Fila de TOTAL -->
                                    <tr class="bg-light font-weight-bold border-top-2">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="text-right"><strong>{{ number_format($totalPresupuestoGeneral, 0, ',', '.') }}</strong></td>
                                        <td class="text-right"><strong>{{ number_format($totalEjecutadoGeneral, 0, ',', '.') }}</strong></td>
                                        <td class="text-right"><strong>{{ number_format($totalSaldoGeneral, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Presupuesto Total</span>
                                            <span class="info-box-number">$ {{ number_format($totalPresupuestoGeneral, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Ejecutado</span>
                                            <span class="info-box-number">$ {{ number_format($totalEjecutadoGeneral, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box {{ $totalSaldoGeneral >= 0 ? 'bg-info' : 'bg-danger' }}">
                                        <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Saldo Disponible</span>
                                            <span class="info-box-number">$ {{ number_format($totalSaldoGeneral, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detalles por Sección -->
        @if(!empty($seccionesData))
        @foreach($seccionesData as $seccionNombre => $seccionDatos)
        <div class="row">
            <div class="col-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bookmark mr-2"></i>
                            {{ ucwords(str_replace('-', ' ', $seccionNombre)) }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        @if(!empty($seccionDatos))
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Jun</th>
                                            <th>Jul</th>
                                            <th>Ago</th>
                                            <th>Sep</th>
                                            <th>Oct</th>
                                            <th>Nov</th>
                                            <th>Dic</th>
                                            <th>Ene</th>
                                            <th>Feb</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($seccionDatos as $concepto => $conceptoDatos)
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                @php
                                                    $total = 0;
                                                    $meses = ['junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'];
                                                @endphp
                                                @foreach($meses as $mes)
                                                    @php
                                                        $valor = $conceptoDatos[$mes] ?? 0;
                                                        $total += $valor;
                                                    @endphp
                                                    <td class="text-right">
                                                        @if($valor > 0)
                                                            {{ number_format($valor, 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-right font-weight-bold">
                                                    $ {{ number_format($total, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                No hay datos disponibles para esta sección.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endif

        <!-- Información adicional -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Información del Período
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Período de Ejecución:</strong> Junio 2025 - Febrero 2026 (9 meses)</p>
                                <p><strong>Año Académico:</strong> 2025-2026</p>
                                <p><strong>Última Actualización:</strong> {{ date('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Usuario:</strong> {{ auth()->user()->name }}</p>
                                <p><strong>Secciones Asignadas:</strong> {{ implode(', ', array_map('ucwords', str_replace('-', ' ', $userSectionPermissions))) }}</p>
                                <p><strong>Tipo de Acceso:</strong> Consulta por Secciones</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    .main-container {
        padding: 20px;
    }

    .stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        flex: 1;
        min-width: 250px;
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid;
    }

    .stat-box-primary { border-left-color: #007bff; }
    .stat-box-success { border-left-color: #28a745; }
    .stat-box-warning { border-left-color: #ffc107; }
    .stat-box-info { border-left-color: #17a2b8; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .stat-box-primary .stat-icon { background: #007bff; }
    .stat-box-success .stat-icon { background: #28a745; }
    .stat-box-warning .stat-icon { background: #ffc107; }
    .stat-box-info .stat-icon { background: #17a2b8; }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .stat-sublabel {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .progress {
        height: 8px;
        margin-bottom: 5px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .card-title {
        font-weight: 600;
        color: #495057;
    }

    .info-box {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .section-content {
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
    }

    .alert {
        border: none;
        border-radius: 8px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Animación para las tarjetas colapsables
    $('.card[data-card-widget="collapse"]').on('collapsed.lte.cardwidget', function() {
        $(this).find('.fas').removeClass('fa-minus').addClass('fa-plus');
    });
    
    $('.card[data-card-widget="collapse"]').on('expanded.lte.cardwidget', function() {
        $(this).find('.fas').removeClass('fa-plus').addClass('fa-minus');
    });
    
    console.log('Vista de Secciones Generales cargada correctamente');
});
</script>
@stop
