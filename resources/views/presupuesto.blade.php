@extends('adminlte::page')

@section('title', 'Presupuesto Colegio Victoria SAS 2024-2025')

@section('adminlte_css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0 text-center">{{ $colegio ?? 'COLEGIO VICTORIA SAS' }}</h1>
                    <h2 class="h5 mb-0 text-center">EJECUCIÓN PRESUPUESTAL AÑO ESCOLAR 2024-2025</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6>Presupuesto Aprobado</h6>
                                <p class="h5 text-primary">{{ number_format($presupuesto_aprobado ?? 315, 0) }} estudiantes</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6>Becas</h6>
                                <p class="h5 text-info">{{ $becas ?? 16.5 }}%</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6>Estudiantes Pagando</h6>
                                <p class="h5 text-success">{{ number_format($estudiantes_pagando ?? 300, 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Carga de Excel -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="h4 mb-0">
                        <i class="fas fa-file-excel me-2"></i>Gestión de Archivos Excel
                    </h3>
                </div>
                <div class="card-body">
                    @if(!$excel_data)
                        <!-- Formulario de carga -->
                        <div class="row">
                            <div class="col-md-8">
                                <form action="{{ route('presupuesto.upload-excel') }}" method="POST" enctype="multipart/form-data" id="excelUploadForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="excel_file" class="form-label">Seleccionar archivo Excel</label>
                                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">Formatos soportados: .xlsx, .xls, .csv (máximo 10MB)</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Subir Archivo
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Instrucciones</h6>
                                    <ul class="mb-0 small">
                                        <li>Selecciona un archivo Excel con datos presupuestales</li>
                                        <li>Una vez cargado, podrás visualizar y editar los datos</li>
                                        <li>Los cambios se pueden descargar como nuevo archivo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Vista de datos cargados -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5><i class="fas fa-file-excel text-success me-2"></i>{{ $excel_filename }}</h5>
                                <p class="text-muted mb-0">Archivo cargado exitosamente</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('presupuesto.download-excel') }}" class="btn btn-success me-2">
                                    <i class="fas fa-download me-2"></i>Descargar
                                </a>
                                <form action="{{ route('presupuesto.clear-excel') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar los datos cargados?')">
                                        <i class="fas fa-trash me-2"></i>Limpiar
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Tabla editable con datos del Excel -->
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-sm" id="excelTable">
                                @foreach($excel_data as $rowIndex => $row)
                                    <tr>
                                        @foreach($row as $colIndex => $cell)
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm excel-cell" 
                                                       value="{{ $cell }}" 
                                                       data-row="{{ $rowIndex }}" 
                                                       data-col="{{ $colIndex }}"
                                                       style="border: none; background: transparent;">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Datos cargados:</strong> {{ count($excel_data) }} filas × {{ count($excel_data[0] ?? []) }} columnas
                                <br><small>Puedes editar directamente en la tabla. Los cambios se guardan automáticamente.</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resumen Ejecutivo -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="h4 mb-0">Resumen Ejecutivo</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="summary-box bg-light p-3 rounded">
                                <h6 class="text-muted">Total Ingresos</h6>
                                <p class="h4 text-success mb-0">${{ number_format($total_ingresos_aprobado ?? 12673249452.61, 2) }}</p>
                                <small class="text-muted">Ejecutado: ${{ number_format($total_ingresos_ejecutado ?? 8885475450.37, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box bg-light p-3 rounded">
                                <h6 class="text-muted">Total Egresos</h6>
                                <p class="h4 text-danger mb-0">${{ number_format($total_egresos_aprobado ?? 14254510486.20, 2) }}</p>
                                <small class="text-muted">Ejecutado: ${{ number_format($total_egresos_ejecutado ?? 8308529588.39, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box bg-light p-3 rounded">
                                <h6 class="text-muted">Resultado</h6>
                                <p class="h4 {{ ($resultado_ejecutado ?? 565953205.98) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    ${{ number_format($resultado_ejecutado ?? 565953205.98, 2) }}
                                </p>
                                <small class="text-muted">Proyectado: ${{ number_format($resultado_aprobado ?? -1581261033.59, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ejecución Mensual -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="h4 mb-0">Ejecución Mensual de Ingresos</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mes</th>
                                    <th>Estudiantes</th>
                                    <th>Ejecución ($)</th>
                                    <th>% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $meses_ejecucion = [
                                        ['mes' => 'Julio', 'estudiantes' => 297, 'ejecucion' => 1691924475.97],
                                        ['mes' => 'Agosto', 'estudiantes' => 297, 'ejecucion' => 557846419.79],
                                        ['mes' => 'Septiembre', 'estudiantes' => 297, 'ejecucion' => 1107182170.79],
                                        ['mes' => 'Octubre', 'estudiantes' => 297, 'ejecucion' => 1106559736.86],
                                        ['mes' => 'Noviembre', 'estudiantes' => 297, 'ejecucion' => 1119817255.10],
                                        ['mes' => 'Diciembre', 'estudiantes' => 299, 'ejecucion' => 1074800015.35],
                                        ['mes' => 'Enero', 'estudiantes' => 299, 'ejecucion' => 1114447347.83],
                                        ['mes' => 'Febrero', 'estudiantes' => 299, 'ejecucion' => 0],
                                    ];
                                    $total_ejecutado = $total_ingresos_ejecutado ?? 8885475450.37;
                                @endphp

                                @foreach($meses_ejecucion as $mes)
                                <tr>
                                    <td><strong>{{ $mes['mes'] }}</strong></td>
                                    <td>{{ number_format($mes['estudiantes']) }}</td>
                                    <td>${{ number_format($mes['ejecucion'], 2) }}</td>
                                    <td>
                                        @if($total_ejecutado > 0)
                                            {{ number_format(($mes['ejecucion'] / $total_ejecutado) * 100, 2) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detalle de Ingresos -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h4 mb-0">Detalle de Ingresos</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="text-primary">Ingresos Escolares</h5>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Código</th>
                                    <th>Presupuesto 2024-2025</th>
                                    <th>Total Ejecutado</th>
                                    <th>% Ejecución</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $ingresos_escolares = [
                                        ['concepto' => 'Matrículas', 'codigo' => '416010-41751010-41751510', 'presupuesto' => 977368356.75, 'ejecutado' => 979993594],
                                        ['concepto' => 'Pensiones', 'codigo' => '416025-41751005-41751506', 'presupuesto' => 8699932053.86, 'ejecutado' => 5675221162],
                                        ['concepto' => 'Seguros Estudiantiles', 'codigo' => '416065', 'presupuesto' => 33921165, 'ejecutado' => 34956048],
                                        ['concepto' => 'Desarrollo Curricular Bilingüe', 'codigo' => '416015', 'presupuesto' => 442203342, 'ejecutado' => 454536906],
                                        ['concepto' => 'Sistematización de Notas', 'codigo' => '416055', 'presupuesto' => 98931495, 'ejecutado' => 84596935],
                                        ['concepto' => 'Materiales Generales', 'codigo' => '28150512', 'presupuesto' => 122272780, 'ejecutado' => 109497992],
                                    ];
                                @endphp

                                @foreach($ingresos_escolares as $ingreso)
                                <tr>
                                    <td><strong>{{ $ingreso['concepto'] }}</strong></td>
                                    <td><small class="text-muted">{{ $ingreso['codigo'] }}</small></td>
                                    <td>${{ number_format($ingreso['presupuesto'], 2) }}</td>
                                    <td>${{ number_format($ingreso['ejecutado'], 2) }}</td>
                                    <td>
                                        @php
                                            $porcentaje = $ingreso['presupuesto'] > 0 ? ($ingreso['ejecutado'] / $ingreso['presupuesto']) * 100 : 0;
                                        @endphp
                                        <span class="badge {{ $porcentaje >= 80 ? 'bg-success' : ($porcentaje >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ number_format($porcentaje, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td><strong>TOTAL INGRESOS ESCOLARES</strong></td>
                                    <td></td>
                                    <td><strong>${{ number_format(10374629192.61, 2) }}</strong></td>
                                    <td><strong>${{ number_format(7338802637, 2) }}</strong></td>
                                    <td><strong>70.8%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4 mb-3">
                        <div class="col-12">
                            <h5 class="text-primary">Otros Ingresos Escolares</h5>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th>Concepto</th>
                                    <th>Código</th>
                                    <th>Presupuesto 2024-2025</th>
                                    <th>Total Ejecutado</th>
                                    <th>% Ejecución</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $otros_ingresos = [
                                        ['concepto' => 'Rendimientos/Intereses/Certificados', 'codigo' => '41600502-416040-416095', 'presupuesto' => 109185300, 'ejecutado' => 76982481.37],
                                        ['concepto' => 'Agenda Escolar', 'codigo' => '416070', 'presupuesto' => 10689600, 'ejecutado' => 11108800],
                                        ['concepto' => 'Anuario', 'codigo' => '416060', 'presupuesto' => 41103070, 'ejecutado' => 22079461],
                                        ['concepto' => 'Exámenes de Admisión', 'codigo' => '41600501', 'presupuesto' => 0, 'ejecutado' => 4486950],
                                        ['concepto' => 'Ingresos Cafetería', 'codigo' => '416035-41750509', 'presupuesto' => 693742240, 'ejecutado' => 463413722],
                                    ];
                                @endphp

                                @foreach($otros_ingresos as $ingreso)
                                <tr>
                                    <td><strong>{{ $ingreso['concepto'] }}</strong></td>
                                    <td><small class="text-muted">{{ $ingreso['codigo'] }}</small></td>
                                    <td>${{ number_format($ingreso['presupuesto'], 2) }}</td>
                                    <td>${{ number_format($ingreso['ejecutado'], 2) }}</td>
                                    <td>
                                        @php
                                            $porcentaje = $ingreso['presupuesto'] > 0 ? ($ingreso['ejecutado'] / $ingreso['presupuesto']) * 100 : 0;
                                        @endphp
                                        <span class="badge {{ $porcentaje >= 80 ? 'bg-success' : ($porcentaje >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $ingreso['presupuesto'] > 0 ? number_format($porcentaje, 1) . '%' : 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td><strong>TOTAL OTROS INGRESOS</strong></td>
                                    <td></td>
                                    <td><strong>${{ number_format(2298620260, 2) }}</strong></td>
                                    <td><strong>${{ number_format(1546672813.37, 2) }}</strong></td>
                                    <td><strong>67.3%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen de Gastos -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h3 class="h4 mb-0">Resumen de Gastos por Categoría</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-warning">
                                <tr>
                                    <th>Categoría</th>
                                    <th>Presupuesto Aprobado</th>
                                    <th>Total Ejecutado</th>
                                    <th>Disponible</th>
                                    <th>% Ejecución</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $categorias_gastos = [
                                        ['categoria' => 'Salarios y Prestaciones Academia', 'presupuesto' => 6763631136.12, 'ejecutado' => 4059280381.96],
                                        ['categoria' => 'Salarios y Prestaciones Administrativos', 'presupuesto' => 1502858812.15, 'ejecutado' => 883615923.35],
                                        ['categoria' => 'Capacitación e Indemnizaciones', 'presupuesto' => 1213275, 'ejecutado' => 0],
                                        ['categoria' => 'Institucional y Academia', 'presupuesto' => 1208623869.73, 'ejecutado' => 871548500.39],
                                        ['categoria' => 'Servicios Públicos y Otros', 'presupuesto' => 2502386479.51, 'ejecutado' => 1109326157.24],
                                        ['categoria' => 'Sección Academia', 'presupuesto' => 412349001.78, 'ejecutado' => 186866053.24],
                                        ['categoria' => 'Contratos Externos', 'presupuesto' => 1863447911.90, 'ejecutado' => 1197892572.21],
                                    ];
                                @endphp

                                @foreach($categorias_gastos as $categoria)
                                <tr>
                                    <td><strong>{{ $categoria['categoria'] }}</strong></td>
                                    <td>${{ number_format($categoria['presupuesto'], 2) }}</td>
                                    <td>${{ number_format($categoria['ejecutado'], 2) }}</td>
                                    <td>${{ number_format($categoria['presupuesto'] - $categoria['ejecutado'], 2) }}</td>
                                    <td>
                                        @php
                                            $porcentaje = $categoria['presupuesto'] > 0 ? ($categoria['ejecutado'] / $categoria['presupuesto']) * 100 : 0;
                                        @endphp
                                        <span class="badge {{ $porcentaje >= 80 ? 'bg-danger' : ($porcentaje >= 50 ? 'bg-warning' : 'bg-success') }}">
                                            {{ number_format($porcentaje, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-warning">
                                    <td><strong>TOTAL GASTOS</strong></td>
                                    <td><strong>${{ number_format(14254510486.20, 2) }}</strong></td>
                                    <td><strong>${{ number_format(8308529588.39, 2) }}</strong></td>
                                    <td><strong>${{ number_format(5945980897.81, 2) }}</strong></td>
                                    <td><strong>58.3%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actividades Cocurriculares -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h3 class="h4 mb-0">Actividades Cocurriculares</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Actividad</th>
                                    <th>Total Ejecutado</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $actividades = [
                                        ['actividad' => 'Monografía', 'ejecutado' => -1179938],
                                        ['actividad' => 'Chaqueta', 'ejecutado' => -2886000],
                                        ['actividad' => 'Proyecto Comunitario', 'ejecutado' => -2127562.5],
                                        ['actividad' => 'Proyecto Personal', 'ejecutado' => 584686.5],
                                        ['actividad' => 'Salidas Pedagógicas y Convivencias', 'ejecutado' => 721573],
                                        ['actividad' => 'Extracurriculares', 'ejecutado' => 15879897],
                                    ];
                                @endphp

                                @foreach($actividades as $actividad)
                                <tr>
                                    <td><strong>{{ $actividad['actividad'] }}</strong></td>
                                    <td>${{ number_format($actividad['ejecutado'], 2) }}</td>
                                    <td>
                                        <span class="badge {{ $actividad['ejecutado'] >= 0 ? 'bg-success' : 'bg-info' }}">
                                            {{ $actividad['ejecutado'] >= 0 ? 'Ingreso' : 'Pendiente' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-secondary">
                                    <td><strong>TOTAL ACTIVIDADES</strong></td>
                                    <td><strong>${{ number_format(10992656, 2) }}</strong></td>
                                    <td><strong>Neto</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer con información adicional -->
            <div class="card">
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Fecha de generación:</strong> {{ date('d/m/Y H:i:s') }}<br>
                                <strong>Período:</strong> Año Escolar 2024-2025<br>
                                <strong>Corte:</strong> Febrero 2025
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <strong>Sistema:</strong> Gestión Presupuestal<br>
                                <strong>Usuario:</strong> {{ Auth::user()->name ?? 'Sistema' }}<br>
                                <strong>Versión:</strong> 1.0
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        border-left: 4px solid #007bff;
        margin-bottom: 1rem;
    }

    .summary-box {
        border-left: 4px solid #28a745;
        transition: transform 0.2s;
    }

    .summary-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.8rem;
    }

    /* Estilos para la tabla Excel */
    #excelTable {
        font-size: 0.85rem;
    }

    #excelTable td {
        padding: 2px;
        vertical-align: middle;
    }

    .excel-cell {
        width: 100%;
        min-width: 80px;
        padding: 4px 6px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .excel-cell:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.5);
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    /* Indicador de carga */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    /* Estilos para el formulario de carga */
    #excelUploadForm {
        position: relative;
    }

    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }

    .file-upload-area.dragover {
        border-color: #28a745;
        background-color: #d4edda;
    }

    @media print {
        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-header {
            background: #333 !important;
            color: white !important;
        }

        .btn {
            display: none;
        }

        /* Ocultar sección de Excel en impresión */
        .card:has(.bg-info) {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Función para imprimir el reporte
    function imprimirReporte() {
        window.print();
    }

    // Función para exportar a Excel (requiere librería adicional)
    function exportarExcel() {
        // Implementar exportación a Excel
        alert('Función de exportación en desarrollo');
    }

    // Funcionalidad para editar celdas del Excel
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips de Bootstrap si están disponibles
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Manejar edición de celdas Excel
        const excelCells = document.querySelectorAll('.excel-cell');
        let updateTimeout;

        excelCells.forEach(function(cell) {
            cell.addEventListener('input', function() {
                const row = this.getAttribute('data-row');
                const col = this.getAttribute('data-col');
                const value = this.value;

                // Debounce para evitar demasiadas peticiones
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(function() {
                    updateExcelData(row, col, value);
                }, 500);
            });

            // Cambiar estilo al enfocar
            cell.addEventListener('focus', function() {
                this.style.background = '#fff3cd';
                this.style.border = '1px solid #ffc107';
            });

            cell.addEventListener('blur', function() {
                this.style.background = 'transparent';
                this.style.border = 'none';
            });
        });

        // Función para actualizar datos via AJAX
        function updateExcelData(row, col, value) {
            fetch('{{ route("presupuesto.update-excel-data") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    data: {
                        row: row,
                        col: col,
                        value: value
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar indicador de guardado
                    showSaveIndicator();
                } else {
                    console.error('Error al actualizar:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Mostrar indicador de guardado
        function showSaveIndicator() {
            const indicator = document.createElement('div');
            indicator.className = 'alert alert-success alert-dismissible fade show position-fixed';
            indicator.style.cssText = 'top: 20px; right: 20px; z-index: 9999; width: auto;';
            indicator.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>Cambios guardados
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(indicator);

            // Auto-remover después de 3 segundos
            setTimeout(function() {
                if (indicator.parentNode) {
                    indicator.remove();
                }
            }, 3000);
        }

        // Validación del formulario de carga
        const uploadForm = document.getElementById('excelUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                const fileInput = document.getElementById('excel_file');
                const file = fileInput.files[0];
                
                if (file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        e.preventDefault();
                        alert('El archivo es demasiado grande. El tamaño máximo es 10MB.');
                        return false;
                    }
                    
                    const allowedTypes = ['.xlsx', '.xls', '.csv'];
                    const fileName = file.name.toLowerCase();
                    const isValidType = allowedTypes.some(type => fileName.endsWith(type));
                    
                    if (!isValidType) {
                        e.preventDefault();
                        alert('Tipo de archivo no válido. Solo se permiten archivos .xlsx, .xls y .csv');
                        return false;
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
