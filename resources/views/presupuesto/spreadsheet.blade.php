@extends('adminlte::page')

@section('title', 'The Victoria School - Presupuesto 2025 - 2026')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="page-header">
        <h1>The Victoria School</h1>
        <p>Presupuesto 2025 - 2026</p>
    </div>
    
    <!-- Cajas de información de presupuesto -->
    <div class="stats-container">
        <div class="stat-box stat-box-primary">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="presupuestoTotal">
                    {{ number_format($budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'], 0, ',', '.') }}
                </div>
                <div class="stat-label">Presupuesto Total</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalAlumnos" data-section="config" data-concept="total_alumnos" data-type="valor">260</div>
                <div class="stat-label">Total de Alumnos</div>
                <div class="stat-sublabel">Alumnos becados 14</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-warning">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="presupuestoEjecutado">
                    @if(isset($presupuestoItems))
                        {{ number_format($presupuestoItems->sum('valor_moneda'), 0, ',', '.') }}
                    @else
                        0
                    @endif
                </div>
                <div class="stat-label">Presupuesto Ejecutado</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-info">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="porcentajeEjecucion">
                    @if(isset($presupuestoItems) && $presupuestoItems->sum('valor') > 0)
                        {{ number_format(($presupuestoItems->sum('valor_moneda') / $presupuestoItems->sum('valor')) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <div class="stat-label">% de Ejecución</div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    <!-- Menú Hamburguesa con Dropdown -->
    <div class="hamburger-menu">
        <div class="menu-dropdown-container">
            <button class="hamburger-btn" id="hamburgerBtn">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <!-- Dropdown menu -->
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-header">
                    <span>Hojas de Presupuesto</span>
                </div>
                <div class="dropdown-content">
                    @foreach($sheets as $sheetKey => $sheetName)
                        <div class="dropdown-item {{ $sheetKey == 'BUDGET' ? 'active' : '' }}" 
                             data-sheet="{{ $sheetKey }}">
                            <div class="item-info">
                                <span class="item-title">{{ $sheetName }}</span>
                                <span class="item-key">{{ $sheetKey }}</span>
                            </div>
                            @if($sheetKey == 'Detallado secciones1')
                                <span class="active-indicator">●</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <span class="current-sheet-title" id="currentSheetTitle">BUDGET - Presupuesto Principal</span>
    </div>

    <!-- Contenedor de tablas -->
    <div class="tables-container">
        @foreach($sheets as $sheetKey => $sheetName)
            <div class="table-section {{ $sheetKey == 'BUDGET' ? 'active' : '' }}" id="table-{{ $sheetKey }}">
                <div class="table-header">
                    <h2>{{ $sheetName }}</h2>
                    <div class="table-controls">
                        <input type="text" class="search-input" placeholder="Buscar en esta tabla..." data-table="{{ $sheetKey }}">
                        <a href="{{ route('presupuesto.items') }}" class="btn-import" title="Importar datos">
                            <i class="fas fa-file-import"></i> Importar
                        </a>
                        <button class="btn-export" data-table="{{ $sheetKey }}">
                            <i class="fas fa-file-export"></i> Exportar
                        </button>
                    </div>
                </div>

                @if($sheetKey == 'Detallado secciones1')
                    <!-- Filtros y controles -->
                    <div class="filters-container">
                        <div class="filter-left">
                            <div class="filter-group-compact">
                                <i class="fas fa-filter filter-icon"></i>
                                <select id="sectionFilter" class="filter-select-compact" onchange="filtrarPorSeccion()">
                                    <option value="">🏢 Todas las secciones</option>
                                    @if(isset($presupuestoItems))
                                        @foreach($presupuestoItems->pluck('seccion')->unique()->sort() as $seccion)
                                            <option value="{{ trim($seccion) }}">{{ trim($seccion) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="filter-right">
                            <div class="records-info-compact">
                                <i class="fas fa-eye info-icon"></i>
                                <span class="records-text">
                                    <span id="recordsShown">50</span> de <span id="totalRecords">{{ count($presupuestoItems) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla principal de presupuesto - DETALLADO SECCIONES1 -->
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th data-sort="seccion">Sección</th>
                                    <th data-sort="rubro">Rubro</th>
                                    <th data-sort="cuenta">Cuenta</th>
                                    <th data-sort="documento">Documento</th>
                                    <th data-sort="fecha">Fecha</th>
                                    <th data-sort="valor">Valor</th>
                                    <th data-sort="valor_moneda">Valor Moneda</th>
                                    <th data-sort="diferencia">Diferencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="presupuestoTableBody">
                                @if(isset($presupuestoItems))
                                    @foreach($presupuestoItems as $index => $item)
                                        <tr class="{{ $item->rubro == 'TOTAL' ? 'total-row' : '' }}" data-section="{{ trim($item->seccion) }}" data-index="{{ $index }}" style="{{ $index >= 50 ? 'display: none;' : '' }}">
                                            <td>{{ $item->seccion }}</td>
                                            <td>{{ $item->rubro }}</td>
                                            <td>{{ $item->cuenta }}</td>
                                            <td>{{ $item->documento ?? '-' }}</td>
                                            <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                                            <td class="number-cell valor-clickeable" 
                                data-item-id="{{ $item->id }}"
                                data-seccion="{{ $item->seccion }}"
                                data-rubro="{{ $item->rubro }}"
                                data-cuenta="{{ $item->cuenta }}"
                                data-documento="{{ $item->documento ?? '-' }}"
                                data-fecha="{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}"
                                data-descripcion="{{ $item->descripcion ?? '-' }}"
                                data-nombre-tercero="{{ $item->nombre_tercero ?? '-' }}"
                                data-centro-costo="{{ $item->centro_costo ?? '-' }}"
                                data-valor="{{ $item->valor ?? 0 }}"
                                style="cursor: pointer; color: #007bff; text-decoration: underline;"
                                title="Clic para ver detalles del gasto">
                                {{ number_format($item->valor ?? 0, 0, ',', '.') }}
                            </td>
                                            <td class="number-cell">{{ number_format($item->valor_moneda ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell {{ (($item->valor ?? 0) - ($item->valor_moneda ?? 0)) < 0 ? 'negative' : 'positive' }}">
                                                {{ number_format(($item->valor ?? 0) - ($item->valor_moneda ?? 0), 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <div class="action-buttons-container">
                                                    <button class="btn-edit btn-icon" data-id="{{ $item->id ?? '' }}" title="Editar registro">📝</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="no-data">No hay datos de presupuesto disponibles</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Botón cargar más -->
                    <div class="load-more-container" id="loadMoreContainer">
                        <button class="btn-load-more" id="loadMoreBtn">
                            <i class="fas fa-arrow-down"></i>
                            Cargar más registros
                        </button>
                        <div class="loading-indicator" id="loadingIndicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando...
                        </div>
                    </div>
                    
                    <!-- Modal personalizado para mostrar detalles del gasto -->
                    <div id="detalleGastoModal" class="custom-modal" style="display: none;">
                        <div class="custom-modal-overlay" onclick="cerrarModal()"></div>
                        <div class="custom-modal-content">
                            <div class="custom-modal-header">
                                <h5 class="custom-modal-title">
                                    <i class="fas fa-info-circle"></i> Detalle del Gasto
                                </h5>
                                <button type="button" class="custom-close-btn" onclick="cerrarModal()">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="custom-modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 class="info-title"><i class="fas fa-building"></i> Información Básica</h6>
                                            <div class="info-item">
                                                <strong>Sección:</strong>
                                                <span id="modal-seccion"></span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Rubro:</strong>
                                                <span id="modal-rubro"></span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Cuenta:</strong>
                                                <span id="modal-cuenta"></span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Documento:</strong>
                                                <span id="modal-documento"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <h6 class="info-title"><i class="fas fa-calendar-alt"></i> Información Financiera</h6>
                                            <div class="info-item">
                                                <strong>Fecha:</strong>
                                                <span id="modal-fecha"></span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Valor:</strong>
                                                <span id="modal-valor" class="text-success font-weight-bold"></span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Centro de Costo:</strong>
                                                <span id="modal-centro-costo"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <h6 class="info-title"><i class="fas fa-file-alt"></i> Descripción Detallada</h6>
                                            <div class="info-item">
                                                <strong>Descripción:</strong>
                                                <p id="modal-descripcion" class="mt-2"></p>
                                            </div>
                                            <div class="info-item">
                                                <strong>Tercero:</strong>
                                                <span id="modal-nombre-tercero"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">
                                    <i class="fas fa-times"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Secciones')
                    <!-- Hoja de Secciones con múltiples tablas -->
                    <div class="sections-container">
                        <!-- Filtro por mes -->
                        <div class="filters-container mb-4">
                            <div class="filter-group-compact">
                                <i class="fas fa-calendar filter-icon"></i>
                                <select id="monthFilter" class="filter-select-compact">
                                    <option value="">📅 Todos los meses</option>
                                    @if(isset($availableMonths))
                                        @foreach($availableMonths as $month)
                                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="filter-status">
                                <span id="monthFilterStatus" class="filter-status-text">Mostrando todos los meses</span>
                            </div>
                        </div>
                        
                        @if($isAdmin || in_array('preescolar', $userSectionPermissions))
                        <!-- Tabla 1: Preescolar y Primaria -->
                        <div class="section-table" id="preescolar-table">
                            <h3 class="section-title">Preescolar y Primaria</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'PREESCOLAR Y PRIMARIA';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody id="preescolar-tbody">
                                        @php
                                            $preescolarConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($preescolarConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="preescolar" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="preescolar" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion - $totalEjecucion) < 0 ? 'negative' : '' }}"><strong>{{ number_format($presupuestoTotalSeccion - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($isAdmin || in_array('escuela-media', $userSectionPermissions))
                        <!-- Tabla 2: Escuela Media -->
                        <div class="section-table" id="escuela-media-table">
                            <h3 class="section-title">Escuela Media</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'ESCUELA MEDIA';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody id="escuela-media-tbody">
                                        @php
                                            $escuelaMediaConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($escuelaMediaConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="escuela-media" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="escuela-media" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($isAdmin || in_array('escuela-alta', $userSectionPermissions))
                        <!-- Tabla 3: Escuela Alta -->
                        <div class="section-table" id="escuela-alta-table">
                            <h3 class="section-title">Escuela Alta</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'ALTA';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody id="escuela-alta-tbody">
                                        @php
                                            $escuelaAltaConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($escuelaAltaConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="escuela-alta" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="escuela-alta" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($isAdmin || in_array('pai', $userSectionPermissions))
                        <!-- Tabla 4: PAI -->
                        <div class="section-table">
                            <h3 class="section-title">PAI</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'PAI';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $paiConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($paiConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="pai" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="pai" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($isAdmin || in_array('pep', $userSectionPermissions))
                        <!-- Tabla 5: PEP -->
                        <div class="section-table">
                            <h3 class="section-title">PEP</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'PEP';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $pepConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($pepConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Tabla 6: Deportes -->
                        <div class="section-table">
                            <h3 class="section-title">Deportes</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'DEPORTES';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $deportesConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($deportesConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($isAdmin || in_array('biblioteca', $userSectionPermissions))
                        <!-- Tabla 7: Biblioteca -->
                        <div class="section-table">
                            <h3 class="section-title">Biblioteca</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'BIBLIOTECA';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $bibliotecaConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($bibliotecaConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="biblioteca" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="biblioteca" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Tabla 8: Psicología Institucional -->
                        <div class="section-table">
                            <h3 class="section-title">Psicología Institucional</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'PSICOLOGÍA INSTITUCIONAL';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $psicologiaConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($psicologiaConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="psicologia" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="psicologia" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tabla 9: CAS -->
                        <div class="section-table">
                            <h3 class="section-title">CAS</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'CAS';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $casConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($casConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="cas" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="cas" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tabla 10: Consejería Universitaria -->
                        <div class="section-table">
                            <h3 class="section-title">Consejería Universitaria</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'CONSEJERÍA UNIVERSITARIA';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $consejeriaConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($consejeriaConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="consejeria" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="consejeria" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($isAdmin || in_array('departamento-apoyo', $userSectionPermissions))
                        <!-- Tabla 11: Departamento de Apoyo -->
                        <div class="section-table">
                            <h3 class="section-title">Departamento de Apoyo</h3>
                            <div class="table-wrapper">
                                <table class="data-table section-budget-table">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Presupuesto</th>
                                            <th>Ejecución</th>
                                            <th>Saldo por ejecutar</th>
                                        </tr>
                                        @php
                                            $seccion = 'DEPARTAMENTO DE APOYO';
                                            $presupuestoTotalSeccion = $presupuestosTotalesSecciones[$seccion] ?? 0;
                                        @endphp
                                        @if($presupuestoTotalSeccion > 0)
                                        <tr class="presupuesto-aprobado-row">
                                            <td colspan="4" style="text-align: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: bold; padding: 12px;">
                                                <i class="fas fa-check-circle"></i> PRESUPUESTO APROBADO: ${{ number_format($presupuestoTotalSeccion, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @php
                                            $departamentoConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($departamentoConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos['presupuesto'] ?? 0;
                                                $ejecucion = $datos['ejecutado'] ?? 0;
                                                $saldo = $datos['saldo'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="departamento" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td class="number-cell editable" data-section="departamento" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, ',', '.') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? 'negative' : '' }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ ($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion < 0 ? 'negative' : '' }}"><strong>{{ number_format(($presupuestoTotalSeccion > 0 ? $presupuestoTotalSeccion : $totalPresupuesto) - $totalEjecucion, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Tabla de Resumen Consolidado -->
                    <div class="summary-section" style="margin-top: 40px;">
                        <h2 style="text-align: center; color: #233E6C; margin-bottom: 30px; font-size: 24px; font-weight: 700;">
                            📊 RESUMEN CONSOLIDADO POR CONCEPTO
                        </h2>
                        
                        <div class="table-wrapper">
                            <table class="data-table summary-table" style="width: 100%; margin: 0 auto; max-width: 1200px;">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #233E6C 0%, #1a2f5a 100%); color: white;">
                                        <th style="padding: 15px; text-align: left; font-weight: 600;">Concepto</th>
                                        <th style="padding: 15px; text-align: right; font-weight: 600;">Total Presupuesto</th>
                                        <th style="padding: 15px; text-align: right; font-weight: 600;">Total Ejecutado</th>
                                        <th style="padding: 15px; text-align: right; font-weight: 600;">Total por Ejecutar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalPresupuestoGeneral = 0;
                                        $totalEjecutadoGeneral = 0;
                                        $totalPorEjecutarGeneral = 0;
                                    @endphp
                                    
                                    @foreach($resumenConceptos as $concepto => $datos)
                                        @php
                                            $totalPresupuestoGeneral += $datos['presupuesto'];
                                            $totalEjecutadoGeneral += $datos['ejecutado'];
                                            $totalPorEjecutarGeneral += $datos['por_ejecutar'];
                                        @endphp
                                        <tr style="border-bottom: 1px solid #e9ecef;">
                                            <td style="padding: 12px; font-weight: 500; color: #495057;">{{ $concepto }}</td>
                                            <td style="padding: 12px; text-align: right; font-family: 'Courier New', monospace; font-weight: 500;">
                                                {{ number_format($datos['presupuesto'], 0, ',', '.') }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; font-family: 'Courier New', monospace; font-weight: 500;">
                                                {{ number_format($datos['ejecutado'], 0, ',', '.') }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; font-family: 'Courier New', monospace; font-weight: 500; color: {{ $datos['por_ejecutar'] < 0 ? '#dc3545' : '#28a745' }};">
                                                {{ $datos['por_ejecutar'] < 0 ? '(' . number_format(abs($datos['por_ejecutar']), 0, ',', '.') . ')' : number_format($datos['por_ejecutar'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    <!-- Fila de totales -->
                                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #233E6C; font-weight: 700;">
                                        <td style="padding: 15px; font-weight: 700; color: #233E6C; font-size: 16px;">TOTAL</td>
                                        <td style="padding: 15px; text-align: right; font-family: 'Courier New', monospace; font-weight: 700; color: #233E6C; font-size: 16px;">
                                            {{ number_format($totalPresupuestoGeneral, 0, ',', '.') }}
                                        </td>
                                        <td style="padding: 15px; text-align: right; font-family: 'Courier New', monospace; font-weight: 700; color: #233E6C; font-size: 16px;">
                                            {{ number_format($totalEjecutadoGeneral, 0, ',', '.') }}
                                        </td>
                                        <td style="padding: 15px; text-align: right; font-family: 'Courier New', monospace; font-weight: 700; color: {{ $totalPorEjecutarGeneral < 0 ? '#dc3545' : '#28a745' }}; font-size: 16px;">
                                            {{ $totalPorEjecutarGeneral < 0 ? '(' . number_format(abs($totalPorEjecutarGeneral), 0, ',', '.') . ')' : number_format($totalPorEjecutarGeneral, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($sheetKey == 'BUDGET')
                    <!-- Hoja Budget Principal -->
                    <div class="budget-container">
                        
                        <!-- FILTRO DE TABLAS -->
                        <div class="budget-filter-section" style="margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; border: 1px solid #dee2e6;">
                            <div class="filter-header" style="margin-bottom: 15px;">
                                <h5 style="font-size: 16px; font-weight: 600; color: #495057; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    🔍 Filtrar Tablas del Presupuesto
                                </h5>
                                <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 0;">Selecciona el tipo de tabla que deseas visualizar</p>
                            </div>
                            
                            <div class="filter-controls" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                                <label style="font-weight: 500; color: #495057; margin-right: 10px;">Mostrar:</label>
                                
                                <select id="budget-filter" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 5px; background-color: white; font-size: 14px; min-width: 200px;">
                                    <option value="all">📊 Todas las Tablas</option>
                                    <option value="resumen">📈 Resumen General</option>
                                    <option value="ingresos">💰 Ingresos</option>
                                    <option value="salarios">👥 Salarios y Prestaciones</option>
                                    <option value="gastos">💸 Gastos Operativos</option>
                                    <option value="servicios">🏢 Servicios y Convenios</option>
                                    <option value="academia">🎓 Academia</option>
                                    <option value="contratos">📋 Contratos</option>
                                </select>
                                
                                <button id="reset-filter" style="padding: 8px 15px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-left: 10px;">
                                    🔄 Mostrar Todo
                                </button>
                                
                                @can('admin')
                                <a href="{{ route('presupuesto.configurar-secciones') }}" class="btn btn-warning btn-sm" style="margin-left: 15px; padding: 8px 15px; text-decoration: none;">
                                    <i class="fas fa-cogs"></i> Configurar Presupuesto Secciones
                                </a>
                                @endcan
                                
                                <button id="toggle-editable" style="padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-left: 10px;">
                                    ✏️ Hacer Editables
                                </button>
                                
                                <button id="save-data" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-left: 10px; display: none;">
                                    💾 Guardar Datos
                                </button>
                                
                                <button id="recalculate-totals" style="padding: 8px 15px; background-color: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-left: 10px;">
                                    🧮 Recalcular Totales
                                </button>
                                
                                <button id="debug-structure" style="padding: 8px 15px; background-color: #6f42c1; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-left: 10px;">
                                    🔍 Debug Estructura
                                </button>
                                
                                <span id="filter-status" style="margin-left: 15px; font-size: 12px; color: #28a745; font-weight: 500;">Mostrando: Todas las tablas</span>
                                <span id="editable-status" style="margin-left: 10px; font-size: 12px; color: #dc3545; font-weight: 500;">Modo: Solo Lectura</span>
                            </div>
                        </div>

                        @if($isAdmin)
                        <!-- TABLA 1: RESUMEN -->
                        <div class="budget-section filter-resumen" data-filter-category="resumen">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RESUMEN</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>RESUMEN</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>EJECUCION JUNIO</th>
                                            <th>EJECUCION JULIO</th>
                                            <th>EJECUCION AGOSTO</th>
                                            <th>EJECUCION SEPTIEMBRE</th>
                                            <th>EJECUCION OCTUBRE</th>
                                            <th>EJECUCION NOVIEMBRE</th>
                                            <th>EJECUCION DICIEMBRE</th>
                                            <th>EJECUCION ENERO</th>
                                            <th>EJECUCION FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Total Ingresos</strong></td>
                                            <td class="number-cell">${{ number_format($budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'], 0, ',', '.') }}</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Proyectado utilidad cafeteria</strong></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Proyectado utilidad transporte</strong></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Actividades Curriculares</strong></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Egresos</strong></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell calculated">$-</td>
                                            <td class="number-cell calculated">$-</td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>Total Ingresos - Gastos</strong></td>
                                            <td class="number-cell"><strong>$-2.817.710.490</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 2: RESUMEN INGRESOS Y GASTOS -->
                        <div class="budget-section filter-resumen" data-filter-category="resumen">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RESUMEN INGRESOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Ingresos Escolares</strong></td>
                                            <td class="number-cell">$10.457.915.716</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos otros escolares</strong></td>
                                            <td class="number-cell">$868.862.765</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                            <td class="number-cell">0</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL INGRESOS</strong></td>
                                            <td class="number-cell"><strong>$11.326.778.481</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="budget-section filter-resumen" data-filter-category="resumen">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RESUMEN GASTOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Total Salarios, Prestaciones Academia</strong></td>
                                            <td class="number-cell">$6.600.750.523</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Salarios, Prestaciones Administrativos y Sena</strong></td>
                                            <td class="number-cell">$1.453.226.337</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell"></td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Capacitación e Indemnizaciones</strong></td>
                                            <td class="number-cell">$11.276.365</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Rubros Institucionales</strong></td>
                                            <td class="number-cell">$1.172.440.107</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Seccion Academia</strong></td>
                                            <td class="number-cell">$481.271.150</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Servicios Públicos y Otros Egresos</strong></td>
                                            <td class="number-cell">$2.594.069.715</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Costos Contratos Externos</strong></td>
                                            <td class="number-cell">$1.831.454.774</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">$-</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL GASTOS</strong></td>
                                            <td class="number-cell"><strong>$14.144.488.971</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="budget-section filter-ingresos" data-filter-category="ingresos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">INGRESOS ESCOLARES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Matriculas</strong></td>
                                            <td class="number-cell">$979.804.763</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pensiones</strong></td>
                                            <td class="number-cell">$8.816.286.570</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Seguros Estudiantiles</strong></td>
                                            <td class="number-cell">$3.922.844</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Desarrollo curricular bilingüe / Bibliobanco</strong></td>
                                            <td class="number-cell">$443.751.216</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sistematización de Notas</strong></td>
                                            <td class="number-cell">$98.984.742</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Materiales generales</strong></td>
                                            <td class="number-cell">$115.165.581</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                            <td class="number-cell editable"></td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL INGRESOS ESCOLARES</strong></td>
                                            <td class="number-cell calculated"><strong>$10.457.915.716</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 3: OTROS ESCOLARES -->
                        <div class="budget-section filter-ingresos" data-filter-category="ingresos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">OTROS ESCOLARES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Rendimientos/Intereses</strong></td>
                                            <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agenda escolar</strong></td>
                                            <td class="number-cell">$114.682.596</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Anuario</strong></td>
                                            <td class="number-cell">$9.257.396</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Examenes de Admisión</strong></td>
                                            <td class="number-cell">$38.371.950</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos Por Servicio Cafeteria</strong></td>
                                            <td class="number-cell">$6.424.511</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos Por Servicio Transporte</strong></td>
                                            <td class="number-cell">$700.126.312</td>
                                            <td class="number-cell editable">$-</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                            <td class="number-cell editable">0</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL OTROS ESCOLARES</strong></td>
                                            <td class="number-cell calculated"><strong>$868.862.765</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 4: SALARIOS Y PRESTACIONES SOCIALES ACADEMIA -->
                        <div class="budget-section filter-salarios" data-filter-category="salarios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SALARIOS Y PRESTACIONES SOCIALES ACADEMIA</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $salariosAcademiaData = $budgetDataByConcept['salarios-academia'] ?? [];
                                            // Obtener valores mensuales reales
                                            $junioValue = $salariosAcademiaData['junio'] ?? 0;
                                            $julioValue = $salariosAcademiaData['julio'] ?? 0;
                                            $agostoValue = $salariosAcademiaData['agosto'] ?? 0;
                                            $septiembreValue = $salariosAcademiaData['septiembre'] ?? 0;
                                            $octubreValue = $salariosAcademiaData['octubre'] ?? 0;
                                            $noviembreValue = $salariosAcademiaData['noviembre'] ?? 0;
                                            $diciembreValue = $salariosAcademiaData['diciembre'] ?? 0;
                                            $eneroValue = $salariosAcademiaData['enero'] ?? 0;
                                            $febreroValue = $salariosAcademiaData['febrero'] ?? 0;
                                        @endphp
                                        <tr>
                                            <td><strong>Salarios y Prestaciones Sociales Academia</strong></td>
                                            <td class="number-cell">$6.600.750.523</td>
                                            <td class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                            <td class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL SALARIOS ACADEMIA</strong></td>
                                            <td class="number-cell calculated"><strong>$6.600.750.523</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($junioValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($julioValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($agostoValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($septiembreValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($octubreValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($noviembreValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($diciembreValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($eneroValue, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($febreroValue, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated">0,00%</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                            <td class="number-cell calculated">#DIV/0!</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 4.1: SALARIOS Y PRESTACIONES SOCIALES ADMINISTRACION -->
                        <div class="budget-section filter-salarios" data-filter-category="salarios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SALARIOS Y PRESTACIONES SOCIALES ADMINISTRACION</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $salariosAdminData = $budgetDataByConcept['salarios-administracion'] ?? [];
                                            $conceptLabels = [
                                                'salarios-aux-transporte-admin' => 'Salarios y Aux de Transporte- administración y servicios generales',
                                                'capacitacion-administracion' => 'Capacitacion administracion',
                                                'aprendices-sena' => 'Aprendices Sena'
                                            ];
                                        @endphp
                                        @foreach($conceptLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $salariosAdminData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $salariosAdminData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $salariosAdminData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $salariosAdminData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $salariosAdminData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $salariosAdminData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $salariosAdminData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $salariosAdminData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $salariosAdminData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $salariosAdminData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="salarios-administracion" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @php
                                            // Calcular totales mensuales reales
                                            $totalJunio = ($salariosAdminData['salarios-aux-transporte-admin-junio'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-junio'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-junio'] ?? 0);
                                            $totalJulio = ($salariosAdminData['salarios-aux-transporte-admin-julio'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-julio'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-julio'] ?? 0);
                                            $totalAgosto = ($salariosAdminData['salarios-aux-transporte-admin-agosto'] ?? 0) + 
                                                          ($salariosAdminData['capacitacion-administracion-agosto'] ?? 0) + 
                                                          ($salariosAdminData['aprendices-sena-agosto'] ?? 0);
                                            $totalSeptiembre = ($salariosAdminData['salarios-aux-transporte-admin-septiembre'] ?? 0) + 
                                                              ($salariosAdminData['capacitacion-administracion-septiembre'] ?? 0) + 
                                                              ($salariosAdminData['aprendices-sena-septiembre'] ?? 0);
                                            $totalOctubre = ($salariosAdminData['salarios-aux-transporte-admin-octubre'] ?? 0) + 
                                                           ($salariosAdminData['capacitacion-administracion-octubre'] ?? 0) + 
                                                           ($salariosAdminData['aprendices-sena-octubre'] ?? 0);
                                            $totalNoviembre = ($salariosAdminData['salarios-aux-transporte-admin-noviembre'] ?? 0) + 
                                                             ($salariosAdminData['capacitacion-administracion-noviembre'] ?? 0) + 
                                                             ($salariosAdminData['aprendices-sena-noviembre'] ?? 0);
                                            $totalDiciembre = ($salariosAdminData['salarios-aux-transporte-admin-diciembre'] ?? 0) + 
                                                             ($salariosAdminData['capacitacion-administracion-diciembre'] ?? 0) + 
                                                             ($salariosAdminData['aprendices-sena-diciembre'] ?? 0);
                                            $totalEnero = ($salariosAdminData['salarios-aux-transporte-admin-enero'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-enero'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-enero'] ?? 0);
                                            $totalFebrero = ($salariosAdminData['salarios-aux-transporte-admin-febrero'] ?? 0) + 
                                                           ($salariosAdminData['capacitacion-administracion-febrero'] ?? 0) + 
                                                           ($salariosAdminData['aprendices-sena-febrero'] ?? 0);
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL SALARIOS ADMINISTRATIVOS</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($salariosAdminData), 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalAgosto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalSeptiembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalOctubre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalNoviembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalDiciembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalEnero, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalFebrero, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 4.2: CAPACITACION E INDEMNIZACIONES -->
                        <div class="budget-section filter-salarios" data-filter-category="salarios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">CAPACITACION E INDEMNIZACIONES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $capacitacionData = $budgetDataByConcept['capacitacion-indemnizaciones'] ?? [];
                                            $capacitacionLabels = [
                                                'capacitacion-admin' => 'Capacitación admin',
                                                'capacitacion-emc-docentes' => 'Capacitación EMC/Docentes',
                                                'capacitacion-copassi' => 'Capacitación COPASSI (Inspectoras, bomberos)',
                                                'indemnizaciones' => 'Indemnizaciones'
                                            ];
                                        @endphp
                                        @foreach($capacitacionLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $capacitacionData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $capacitacionData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $capacitacionData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $capacitacionData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $capacitacionData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $capacitacionData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $capacitacionData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $capacitacionData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $capacitacionData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $capacitacionData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="capacitacion-indemnizaciones" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @php
                                            // Calcular totales mensuales reales para Capacitación e Indemnizaciones
                                            $totalCapJunio = ($capacitacionData['capacitacion-admin-junio'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-emc-docentes-junio'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-copassi-junio'] ?? 0) + 
                                                            ($capacitacionData['indemnizaciones-junio'] ?? 0);
                                            $totalCapJulio = ($capacitacionData['capacitacion-admin-julio'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-emc-docentes-julio'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-copassi-julio'] ?? 0) + 
                                                            ($capacitacionData['indemnizaciones-julio'] ?? 0);
                                            $totalCapAgosto = ($capacitacionData['capacitacion-admin-agosto'] ?? 0) + 
                                                             ($capacitacionData['capacitacion-emc-docentes-agosto'] ?? 0) + 
                                                             ($capacitacionData['capacitacion-copassi-agosto'] ?? 0) + 
                                                             ($capacitacionData['indemnizaciones-agosto'] ?? 0);
                                            $totalCapSeptiembre = ($capacitacionData['capacitacion-admin-septiembre'] ?? 0) + 
                                                                 ($capacitacionData['capacitacion-emc-docentes-septiembre'] ?? 0) + 
                                                                 ($capacitacionData['capacitacion-copassi-septiembre'] ?? 0) + 
                                                                 ($capacitacionData['indemnizaciones-septiembre'] ?? 0);
                                            $totalCapOctubre = ($capacitacionData['capacitacion-admin-octubre'] ?? 0) + 
                                                              ($capacitacionData['capacitacion-emc-docentes-octubre'] ?? 0) + 
                                                              ($capacitacionData['capacitacion-copassi-octubre'] ?? 0) + 
                                                              ($capacitacionData['indemnizaciones-octubre'] ?? 0);
                                            $totalCapNoviembre = ($capacitacionData['capacitacion-admin-noviembre'] ?? 0) + 
                                                                ($capacitacionData['capacitacion-emc-docentes-noviembre'] ?? 0) + 
                                                                ($capacitacionData['capacitacion-copassi-noviembre'] ?? 0) + 
                                                                ($capacitacionData['indemnizaciones-noviembre'] ?? 0);
                                            $totalCapDiciembre = ($capacitacionData['capacitacion-admin-diciembre'] ?? 0) + 
                                                                ($capacitacionData['capacitacion-emc-docentes-diciembre'] ?? 0) + 
                                                                ($capacitacionData['capacitacion-copassi-diciembre'] ?? 0) + 
                                                                ($capacitacionData['indemnizaciones-diciembre'] ?? 0);
                                            $totalCapEnero = ($capacitacionData['capacitacion-admin-enero'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-emc-docentes-enero'] ?? 0) + 
                                                            ($capacitacionData['capacitacion-copassi-enero'] ?? 0) + 
                                                            ($capacitacionData['indemnizaciones-enero'] ?? 0);
                                            $totalCapFebrero = ($capacitacionData['capacitacion-admin-febrero'] ?? 0) + 
                                                              ($capacitacionData['capacitacion-emc-docentes-febrero'] ?? 0) + 
                                                              ($capacitacionData['capacitacion-copassi-febrero'] ?? 0) + 
                                                              ($capacitacionData['indemnizaciones-febrero'] ?? 0);
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL CAPACITACION E INDEMNIZACIONES</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($capacitacionData), 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapAgosto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapSeptiembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapOctubre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapNoviembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapDiciembre, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapEnero, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalCapFebrero, 0, ',', '.') }}</strong></td>
                                        </tr>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 5: RUBROS INSTITUCIONALES -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RUBROS INSTITUCIONALES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $rubrosData = $budgetDataByConcept['rubros-institucionales'] ?? [];
                                            $rubrosLabels = [
                                                'equipos-dotacion' => 'Equipos y Dotacion Salones y/o oficinas',
                                                'examenes-medicos' => 'Examenes Médicos (periodicos y de contratacion)',
                                                'tecnologia-institucional' => 'Tecnologia institucional',
                                                'insumos-enfermeria' => 'Insumos enfermeria escolar',
                                                'mercadeo-admisiones' => 'Mercadeo y admisiones',
                                                'eventos-comunidad' => 'Eventos Institucionales de Comunidad',
                                                'mantenimiento-general' => 'Mantenimiento general',
                                                'reparaciones-mayores' => 'Reparaciones mayores (construcciones)',
                                                'reparacion-muebles' => 'Reparación de Muebles',
                                                'utiles-oficina' => 'Útiles de Oficina y Papelería (ABKA)',
                                                'elementos-aseo' => 'Elementos de Aseo y Cafeteria',
                                                'gastos-agasajos' => 'Gastos de Agasajos',
                                                'bienestar-institucional' => 'Bienestar institucional',
                                                'eventos-internos' => 'Eventos institucionales internos',
                                                'gastos-contratacion' => 'Gastos de contratación (pruebas psicotecnicas, plataforma de computrabajo,visitas y poligrafos, anuncios empleo)',
                                                'afiliaciones-inscripciones' => 'Afiliaciones e Inscripciones'
                                            ];
                                        @endphp
                                        @foreach($rubrosLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $rubrosData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $rubrosData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $rubrosData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $rubrosData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $rubrosData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $rubrosData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $rubrosData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $rubrosData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $rubrosData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $rubrosData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="rubros-institucionales" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL INSTITUCIONAL</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($rubrosData), 0, ',', '.') }}</strong></td>
                                            @php
                                                $totalJunio = 0;
                                                $totalJulio = 0;
                                                foreach($rubrosLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $rubrosData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $rubrosData[$conceptKey . '-julio'] ?? 0;
                                                }
                                            @endphp
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                        <tr class="percentage-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 6: MEMBRESIAS Y CONVENIOS -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">MEMBRESIAS Y CONVENIOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $membresiasData = $budgetDataByConcept['membresias-convenios'] ?? [];
                                            $membresiasLabels = [
                                                'bachillerato-internacional' => 'Bachillerato Internacional',
                                                'accbi' => 'ACCBI',
                                                'red-papaz' => 'RED PAPAZ'
                                            ];
                                        @endphp
                                        @foreach($membresiasLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $membresiasData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $membresiasData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $membresiasData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $membresiasData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $membresiasData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $membresiasData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $membresiasData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $membresiasData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $membresiasData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $membresiasData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="membresias-convenios" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL MEMBRESIAS Y CONVENIOS</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($membresiasData), 0, ',', '.') }}</strong></td>
                                            @php
                                                $totalJunio = 0;
                                                $totalJulio = 0;
                                                foreach($membresiasLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $membresiasData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $membresiasData[$conceptKey . '-julio'] ?? 0;
                                                }
                                            @endphp
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                        <tr class="percentage-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 7: SERVICIOS PUBLICOS -->
                        <div class="budget-section filter-servicios" data-filter-category="servicios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SERVICIOS PUBLICOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $serviciosData = $budgetDataByConcept['servicios-publicos'] ?? [];
                                            $serviciosLabels = [
                                                'agua' => 'Agua',
                                                'energia' => 'Energia',
                                                'telefono' => 'Teléfono',
                                                'vigilancia' => 'Vigilancia (METROS CUADRADOS PORTERO)',
                                                'internet-arrendamientos' => 'Internet/ Arrendamientos Tecnológicos'
                                            ];
                                        @endphp
                                        @foreach($serviciosLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $serviciosData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales distribuidos entre conceptos
                                                $junioValue = ($serviciosPublicosData['meses']['junio'] ?? 0) / count($serviciosLabels);
                                                $julioValue = ($serviciosPublicosData['meses']['julio'] ?? 0) / count($serviciosLabels);
                                                $agostoValue = ($serviciosPublicosData['meses']['agosto'] ?? 0) / count($serviciosLabels);
                                                $septiembreValue = ($serviciosPublicosData['meses']['septiembre'] ?? 0) / count($serviciosLabels);
                                                $octubreValue = ($serviciosPublicosData['meses']['octubre'] ?? 0) / count($serviciosLabels);
                                                $noviembreValue = ($serviciosPublicosData['meses']['noviembre'] ?? 0) / count($serviciosLabels);
                                                $diciembreValue = ($serviciosPublicosData['meses']['diciembre'] ?? 0) / count($serviciosLabels);
                                                $eneroValue = ($serviciosPublicosData['meses']['enero'] ?? 0) / count($serviciosLabels);
                                                $febreroValue = ($serviciosPublicosData['meses']['febrero'] ?? 0) / count($serviciosLabels);
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="servicios-publicos" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @php
                                            // Calcular totales mensuales
                                            $junioTotal = $serviciosPublicosData['meses']['junio'] ?? 0;
                                            $julioTotal = $serviciosPublicosData['meses']['julio'] ?? 0;
                                            $agostoTotal = $serviciosPublicosData['meses']['agosto'] ?? 0;
                                            $septiembreTotal = $serviciosPublicosData['meses']['septiembre'] ?? 0;
                                            $octubreTotal = $serviciosPublicosData['meses']['octubre'] ?? 0;
                                            $noviembreTotal = $serviciosPublicosData['meses']['noviembre'] ?? 0;
                                            $diciembreTotal = $serviciosPublicosData['meses']['diciembre'] ?? 0;
                                            $eneroTotal = $serviciosPublicosData['meses']['enero'] ?? 0;
                                            $febreroTotal = $serviciosPublicosData['meses']['febrero'] ?? 0;
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL SERVICIOS PUBLICOS</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($serviciosPublicosData['meses']), 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($junioTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($julioTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($agostoTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($septiembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($octubreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($noviembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($diciembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($eneroTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($febreroTotal, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr class="percentage-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 8: OTROS EGRESOS -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">OTROS EGRESOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $otrosEgresosData = $budgetDataByConcept['otros-egresos'] ?? [];
                                            $otrosEgresosLabels = [
                                                'honorarios' => 'Honorarios',
                                                'legales-sanciones-ugpp' => 'Legales (sanciones UGPP) cámara de comercio',
                                                'agenda' => 'Agenda',
                                                'seguros-generales' => 'Seguros Generales',
                                                'anuario' => 'Anuario',
                                                'comisiones-bancarias' => 'Comisiones Bancarias',
                                                'mensajeria-acarreos' => 'Mensajería y Acarreos',
                                                'miscelaneos' => 'Miscelaneos',
                                                'impuesto-industria-comercio' => 'Impto de Industria y Comercio',
                                                'plan-seguridad-salud-trabajo' => 'Plan de seguridad y Salud en el trabajo',
                                                'otros-egresos-retencion' => 'Otros Egresos Retención',
                                                'impuesto-renta' => 'Impto de renta',
                                                'arrendamientos' => 'Arrendamientos'
                                            ];
                                        @endphp
                                        @foreach($otrosEgresosLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $otrosEgresosData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales de budgetDataByConcept
                                                $junioValue = $otrosEgresosData['junio'] ?? 0;
                                                $julioValue = $otrosEgresosData['julio'] ?? 0;
                                                $agostoValue = $otrosEgresosData['agosto'] ?? 0;
                                                $septiembreValue = $otrosEgresosData['septiembre'] ?? 0;
                                                $octubreValue = $otrosEgresosData['octubre'] ?? 0;
                                                $noviembreValue = $otrosEgresosData['noviembre'] ?? 0;
                                                $diciembreValue = $otrosEgresosData['diciembre'] ?? 0;
                                                $eneroValue = $otrosEgresosData['enero'] ?? 0;
                                                $febreroValue = $otrosEgresosData['febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                                <td data-section="otros-egresos" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue / count($otrosEgresosLabels), 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @php
                                            // Calcular totales mensuales para otros egresos
                                            $junioTotal = $otrosEgresosData['junio'] ?? 0;
                                            $julioTotal = $otrosEgresosData['julio'] ?? 0;
                                            $agostoTotal = $otrosEgresosData['agosto'] ?? 0;
                                            $septiembreTotal = $otrosEgresosData['septiembre'] ?? 0;
                                            $octubreTotal = $otrosEgresosData['octubre'] ?? 0;
                                            $noviembreTotal = $otrosEgresosData['noviembre'] ?? 0;
                                            $diciembreTotal = $otrosEgresosData['diciembre'] ?? 0;
                                            $eneroTotal = $otrosEgresosData['enero'] ?? 0;
                                            $febreroTotal = $otrosEgresosData['febrero'] ?? 0;
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL OTROS EGRESOS</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($otrosEgresosData), 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($junioTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($julioTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($agostoTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($septiembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($octubreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($noviembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($diciembreTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($eneroTotal, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($febreroTotal, 0, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr class="impact-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 9: SECCIONES ACADEMIA GENERAL -->
                        <div class="budget-section filter-academia" data-filter-category="academia">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SECCIONES ACADEMIA GENERAL</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccionesAcademiaData = $budgetDataByConcept['secciones-academia-general'] ?? [];
                                            $seccionesLabels = [
                                                'capacitacion' => 'Capacitación',
                                                'material-importado' => 'Material Importado',
                                                'textos-utiles-consumo' => 'Textos y Utiles de Consumo',
                                                'biblioteca-institucional' => 'Biblioteca institucional',
                                                'materiales-para-clases' => 'Materiales para clases',
                                                'material-deportivo' => 'Material Deportivo',
                                                'musicales' => 'Musicales',
                                                'part-time-teacher-reemplazos' => 'Part time teacher- reemplazos',
                                                'insumos-institucionales-seccion' => 'Insumos institucionales de Seccion (Tecnologia)',
                                                'pep' => 'PEP',
                                                'dp' => 'DP',
                                                'pai' => 'PAI',
                                                'departamento-apoyo' => 'Departamento de Apoyo',
                                                'consejeria-universitaria' => 'Consejeria Universitaria',
                                                'direccion-general' => 'Direcciòn general'
                                            ];
                                        @endphp
                                        @foreach($seccionesLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $seccionesAcademiaData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $seccionesAcademiaData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $seccionesAcademiaData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $seccionesAcademiaData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $seccionesAcademiaData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $seccionesAcademiaData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $seccionesAcademiaData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $seccionesAcademiaData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $seccionesAcademiaData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $seccionesAcademiaData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="secciones-academia-general" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL SECCIONES ACADEMIA GENERAL</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($seccionesAcademiaData), 0, ',', '.') }}</strong></td>
                                            @php
                                                $totalJunio = 0;
                                                $totalJulio = 0;
                                                foreach($seccionesLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $seccionesAcademiaData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $seccionesAcademiaData[$conceptKey . '-julio'] ?? 0;
                                                }
                                            @endphp
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                        <tr class="impact-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                            <td class="number-cell calculated"><strong>-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TABLA 10: CONTRATOS EXTERNOS -->
                        <div class="budget-section filter-contratos" data-filter-category="contratos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">CONTRATOS EXTERNOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
                                            <th>JUNIO</th>
                                            <th>JULIO</th>
                                            <th>AGOSTO</th>
                                            <th>SEPTIEMBRE</th>
                                            <th>OCTUBRE</th>
                                            <th>NOVIEMBRE</th>
                                            <th>DICIEMBRE</th>
                                            <th>ENERO</th>
                                            <th>FEBRERO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $contratosExternosData = $budgetDataByConcept['contratos-externos'] ?? [];
                                            $contratosLabels = [
                                                'cafeteria' => 'Cafeteria',
                                                'transporte' => 'Transporte'
                                            ];
                                        @endphp
                                        @foreach($contratosLabels as $conceptKey => $concepto)
                                            @php
                                                $presupuesto = $contratosExternosData[$conceptKey] ?? 0;
                                                // Obtener valores mensuales reales
                                                $junioValue = $contratosExternosData[$conceptKey . '-junio'] ?? 0;
                                                $julioValue = $contratosExternosData[$conceptKey . '-julio'] ?? 0;
                                                $agostoValue = $contratosExternosData[$conceptKey . '-agosto'] ?? 0;
                                                $septiembreValue = $contratosExternosData[$conceptKey . '-septiembre'] ?? 0;
                                                $octubreValue = $contratosExternosData[$conceptKey . '-octubre'] ?? 0;
                                                $noviembreValue = $contratosExternosData[$conceptKey . '-noviembre'] ?? 0;
                                                $diciembreValue = $contratosExternosData[$conceptKey . '-diciembre'] ?? 0;
                                                $eneroValue = $contratosExternosData[$conceptKey . '-enero'] ?? 0;
                                                $febreroValue = $contratosExternosData[$conceptKey . '-febrero'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $concepto }}</strong></td>
                                                <td class="number-cell editable" data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-type="presupuesto">{{ number_format($presupuesto, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="junio" class="number-cell editable">${{ number_format($junioValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="julio" class="number-cell editable">${{ number_format($julioValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="agosto" class="number-cell editable">${{ number_format($agostoValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="septiembre" class="number-cell editable">${{ number_format($septiembreValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="octubre" class="number-cell editable">${{ number_format($octubreValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="noviembre" class="number-cell editable">${{ number_format($noviembreValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="diciembre" class="number-cell editable">${{ number_format($diciembreValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="enero" class="number-cell editable">${{ number_format($eneroValue, 0, ',', '.') }}</td>
                                                <td data-section="contratos-externos" data-concept="{{ $conceptKey }}" data-column="febrero" class="number-cell editable">${{ number_format($febreroValue, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td><strong>TOTAL CONTRATOS EXTERNOS</strong></td>
                                            <td class="number-cell calculated"><strong>{{ number_format(array_sum($contratosExternosData), 0, ',', '.') }}</strong></td>
                                            @php
                                                $totalJunio = 0;
                                                $totalJulio = 0;
                                                foreach($contratosLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $contratosExternosData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $contratosExternosData[$conceptKey . '-julio'] ?? 0;
                                                }
                                            @endphp
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJunio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalJulio, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                @elseif($sheetKey == 'Equipo y Dotacion Salones')
                    <!-- Hoja Equipo y Dotación Salones -->
                    <div class="table-content">
                        <div class="equipos-dotacion-container">
                            <div class="sheet-header">
                                <h2 style="text-align: center; color: #2c3e50; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
                                    PRESUPUESTO AÑO ESCOLAR 2024-2025
                                </h2>
                                <h3 style="text-align: center; color: #34495e; font-size: 16px; font-weight: 600; margin-bottom: 30px;">
                                    Equipo y Dotación de Salones / Oficinas
                                </h3>
                            </div>

                            <!-- Tabla Principal con Estructura de Excel -->
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" style="font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                            <thead style="background: linear-gradient(135deg, #2c3e50, #34495e); color: white;">
                                                <tr>
                                                    <th style="width: 35%; padding: 12px; font-weight: 600;">Nombre Tercero</th>
                                                    <th style="width: 50%; padding: 12px; font-weight: 600;">Descripción</th>
                                                    <th style="width: 15%; padding: 12px; text-align: right; font-weight: 600;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalGeneral = 0;
                                                    $terceroAnterior = '';
                                                    $rowStyle = '';
                                                @endphp

                                                @if(isset($equiposDotacionData['items_detallados']))
                                                    @foreach($equiposDotacionData['items_detallados'] as $item)
                                                        @php
                                                            $totalGeneral += $item->valor;
                                                            $esPrimeroDelTercero = ($terceroAnterior != $item->nombre_tercero);
                                                            $terceroAnterior = $item->nombre_tercero;
                                                            
                                                            // Alternar colores de fila para mejor legibilidad
                                                            $rowStyle = $esPrimeroDelTercero ? 'background-color: #f8f9fa;' : 'background-color: #ffffff;';
                                                        @endphp
                                                        <tr style="{{ $rowStyle }}">
                                                            <td style="padding: 8px 12px; {{ $esPrimeroDelTercero ? 'font-weight: 600; border-left: 4px solid #3498db;' : 'padding-left: 30px; color: #6c757d;' }}">
                                                                {{ $esPrimeroDelTercero ? $item->nombre_tercero : '' }}
                                                            </td>
                                                            <td style="padding: 8px 12px; {{ $esPrimeroDelTercero ? '' : 'font-style: italic;' }}">
                                                                {{ $item->descripcion }}
                                                            </td>
                                                            <td style="padding: 8px 12px; text-align: right; font-family: 'Courier New', monospace; {{ $esPrimeroDelTercero ? 'font-weight: 600;' : '' }}">
                                                                ${{ number_format($item->valor, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center">No hay datos disponibles</td>
                                                    </tr>
                                                @endif

                                                <!-- Fila de Total -->
                                                <tr style="background: linear-gradient(135deg, #e9ecef, #dee2e6); border-top: 3px solid #495057; font-weight: bold;">
                                                    <td style="padding: 12px; font-weight: bold; color: #495057;">Total general</td>
                                                    <td style="padding: 12px;"></td>
                                                    <td style="padding: 12px; text-align: right; font-family: 'Courier New', monospace; font-weight: bold; color: #495057; font-size: 16px;">
                                                        ${{ number_format($totalGeneral, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Mensual -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                                            <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i>Distribución Mensual</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @if(isset($equiposDotacionData['distribucion_mensual']))
                                                    @foreach($equiposDotacionData['distribucion_mensual'] as $mes => $datos)
                                                        <div class="col-md-3 col-sm-6 mb-3">
                                                            <div class="d-flex justify-content-between align-items-center p-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 4px;">
                                                                <strong>{{ $datos['nombre'] }}:</strong>
                                                                <span class="badge badge-success" style="font-size: 14px;">${{ number_format($datos['valor'], 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-12">
                                                        <p class="text-muted">No hay datos de distribución mensual disponibles</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Aseo y Cafeteria')
                    <!-- Hoja Aseo y Cafetería -->
                    <div class="table-content">
                        <style>
                        /* Forzar color azul institucional en títulos */
                        .aseo-cafeteria-container h2,
                        .aseo-cafeteria-container h3,
                        .aseo-cafeteria-container .sheet-header h2,
                        .aseo-cafeteria-container .sheet-header h3 {
                            color: #364e76 !important;
                        }
                        </style>
                        <div class="aseo-cafeteria-container">
                            <div class="sheet-header">
                                <h2 style="text-align: center; color: #364e76 !important; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
                                    PRESUPUESTO AÑO ESCOLAR 2025-2026
                                </h2>
                                <h3 style="text-align: center; color: #364e76 !important; font-size: 16px; font-weight: 600; margin-bottom: 30px;">
                                    Aseo y Cafetería
                                </h3>
                            </div>

                            <!-- Tabla Principal con Estructura de Excel -->
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" style="font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                            <thead style="background: linear-gradient(135deg, #364e76, #2c3a5e); color: white;">
                                                <tr>
                                                    <th style="width: 35%; padding: 12px; font-weight: 600;">Nombre Tercero</th>
                                                    <th style="width: 50%; padding: 12px; font-weight: 600;">Descripción</th>
                                                    <th style="width: 15%; padding: 12px; text-align: right; font-weight: 600;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalGeneral = 0;
                                                    $terceroAnterior = '';
                                                    $rowStyle = '';
                                                @endphp

                                                @if(isset($aseoCafeteriaData['items_detallados']))
                                                    @foreach($aseoCafeteriaData['items_detallados'] as $item)
                                                        @php
                                                            $totalGeneral += (int)str_replace(['$', ',', '.'], '', $item['total']);
                                                            $esPrimeroDelTercero = ($terceroAnterior != $item['nombre_tercero']);
                                                            $terceroAnterior = $item['nombre_tercero'];
                                                            
                                                            // Alternar colores de fila para mejor legibilidad
                                                            $rowStyle = $esPrimeroDelTercero ? 'background-color: #f8f9fa;' : 'background-color: #ffffff;';
                                                        @endphp
                                                        <tr style="{{ $rowStyle }}">
                                                            <td style="padding: 8px 12px; {{ $esPrimeroDelTercero ? 'font-weight: 600; border-left: 4px solid #364e76;' : 'padding-left: 30px; color: #6c757d;' }}">
                                                                {{ $esPrimeroDelTercero ? $item['nombre_tercero'] : '' }}
                                                            </td>
                                                            <td style="padding: 8px 12px; {{ $esPrimeroDelTercero ? '' : 'font-style: italic;' }}">
                                                                {{ $item['descripcion'] }}
                                                            </td>
                                                            <td style="padding: 8px 12px; text-align: right; font-family: 'Courier New', monospace; {{ $esPrimeroDelTercero ? 'font-weight: 600;' : '' }}">
                                                                ${{ $item['total'] }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center">No hay datos disponibles</td>
                                                    </tr>
                                                @endif

                                                <!-- Fila de Total -->
                                                <tr style="background: linear-gradient(135deg, #e9ecef, #dee2e6); border-top: 3px solid #495057; font-weight: bold;">
                                                    <td style="padding: 12px; font-weight: bold; color: #495057;">Total general</td>
                                                    <td style="padding: 12px;"></td>
                                                    <td style="padding: 12px; text-align: right; font-family: 'Courier New', monospace; font-weight: bold; color: #495057; font-size: 16px;">
                                                        ${{ number_format($totalGeneral, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Mensual -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header" style="background: linear-gradient(135deg, #364e76, #2c3a5e); color: white;">
                                            <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i>Distribución Mensual</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @if(isset($aseoCafeteriaData['distribucion_mensual']))
                                                    @foreach($aseoCafeteriaData['distribucion_mensual'] as $datos)
                                                        <div class="col-md-3 col-sm-6 mb-3">
                                                            <div class="d-flex justify-content-between align-items-center p-3" style="background: #f8f9fa; border-left: 4px solid #364e76; border-radius: 4px;">
                                                                <strong>{{ $datos['mes'] }}:</strong>
                                                                <span class="badge" style="font-size: 14px; background: #364e76; color: white;">${{ $datos['valor'] }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-12">
                                                        <p class="text-muted">No hay datos de distribución mensual disponibles</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @elseif($sheetKey == 'Dotaciones')
                    <!-- Hoja Dotaciones -->
                    <div class="dotaciones-container">
                        <div class="sheet-header">
                            <h2 style="text-align: center; color: #2c3e50; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
                                PRESUPUESTO AÑO ESCOLAR 2024-2025
                            </h2>
                            <h3 style="text-align: center; color: #34495e; font-size: 16px; font-weight: 600; margin-bottom: 30px;">
                                Dotación de Trabajo
                            </h3>
                        </div>

                        <!-- Tabla Resumen -->
                        <div class="table-wrapper" style="margin-bottom: 30px;">
                            <table class="table table-bordered budget-table" style="font-size: 12px;">
                                <thead style="background: linear-gradient(135deg, #8e44ad, #7d3c98); color: white;">
                                    <tr>
                                        <th style="width: 200px;">Concepto</th>
                                        <th style="width: 120px;">Ppto Aprobado</th>
                                        <th style="width: 120px;">Ejecutado</th>
                                        <th style="width: 120px;">Ppto a Ejec</th>
                                        <th style="width: 80px;">%Restante</th>
                                        <th style="width: 100px;">Julio</th>
                                        <th style="width: 100px;">Agosto</th>
                                        <th style="width: 100px;">Septiembre</th>
                                        <th style="width: 100px;">Octubre</th>
                                        <th style="width: 100px;">Noviembre</th>
                                        <th style="width: 100px;">Diciembre</th>
                                        <th style="width: 100px;">Enero</th>
                                        <th style="width: 100px;">Febrero</th>
                                        <th style="width: 100px;">Marzo</th>
                                        <th style="width: 100px;">Abril</th>
                                        <th style="width: 100px;">Mayo</th>
                                        <th style="width: 100px;">Junio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $resumen = $dotacionesData['resumen'];
                                        $ejecucion = $dotacionesData['ejecucion_mensual'];
                                    @endphp
                                    <tr>
                                        <td><strong>Mantenimiento</strong></td>
                                        <td class="number-cell">${{ number_format($resumen['mantenimiento']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['mantenimiento']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['mantenimiento']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell"><strong>{{ $resumen['mantenimiento']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['julio'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['julio']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['mantenimiento']['agosto']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['septiembre'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['septiembre']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['mantenimiento']['octubre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['noviembre'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['noviembre']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['mantenimiento']['diciembre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['enero'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['enero']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['febrero'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['febrero']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['marzo'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['marzo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['abril'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['abril']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['mayo'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['mayo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['mantenimiento']['junio'] > 0 ? '$'.number_format($ejecucion['mantenimiento']['junio']) : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Administración</strong></td>
                                        <td class="number-cell">{{ $resumen['administracion']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['administracion']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['administracion']['ejecutado'] > 0 ? '$'.number_format($resumen['administracion']['ejecutado']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['administracion']['presupuesto_ejecutar'] > 0 ? '$'.number_format($resumen['administracion']['presupuesto_ejecutar']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['administracion']['porcentaje_restante'] > 0 ? $resumen['administracion']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['julio'] > 0 ? '$'.number_format($ejecucion['administracion']['julio']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['agosto'] > 0 ? '$'.number_format($ejecucion['administracion']['agosto']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['septiembre'] > 0 ? '$'.number_format($ejecucion['administracion']['septiembre']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['octubre'] > 0 ? '$'.number_format($ejecucion['administracion']['octubre']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['noviembre'] > 0 ? '$'.number_format($ejecucion['administracion']['noviembre']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['diciembre'] > 0 ? '$'.number_format($ejecucion['administracion']['diciembre']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['administracion']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['administracion']['febrero']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['marzo'] > 0 ? '$'.number_format($ejecucion['administracion']['marzo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['abril'] > 0 ? '$'.number_format($ejecucion['administracion']['abril']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['mayo'] > 0 ? '$'.number_format($ejecucion['administracion']['mayo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['administracion']['junio'] > 0 ? '$'.number_format($ejecucion['administracion']['junio']) : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Servicios generales</strong></td>
                                        <td class="number-cell">{{ $resumen['servicios_generales']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['servicios_generales']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['servicios_generales']['ejecutado'] > 0 ? '$'.number_format($resumen['servicios_generales']['ejecutado']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['servicios_generales']['presupuesto_ejecutar'] > 0 ? '$'.number_format($resumen['servicios_generales']['presupuesto_ejecutar']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['servicios_generales']['porcentaje_restante'] > 0 ? $resumen['servicios_generales']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['julio'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['julio']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['servicios_generales']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['servicios_generales']['septiembre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['octubre'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['servicios_generales']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['servicios_generales']['diciembre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['enero'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['enero']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['febrero'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['febrero']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['marzo'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['marzo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['abril'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['abril']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['mayo'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['mayo']) : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['servicios_generales']['junio'] > 0 ? '$'.number_format($ejecucion['servicios_generales']['junio']) : '' }}</td>
                                    </tr>
                                    <tr style="background: #f8f9fa; font-weight: bold;">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $resumen['total']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['julio'] > 0 ? '$'.number_format($ejecucion['total']['julio']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['marzo'] > 0 ? '$'.number_format($ejecucion['total']['marzo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['abril'] > 0 ? '$'.number_format($ejecucion['total']['abril']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['mayo'] > 0 ? '$'.number_format($ejecucion['total']['mayo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['junio'] > 0 ? '$'.number_format($ejecucion['total']['junio']) : '$0' }}</strong></td>
                                    </tr>
                                    <!-- Fila vacía para separación -->
                                    <tr style="height: 20px;">
                                        <td colspan="17" style="border: none; background: white;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles por mes -->
                        <div class="detalles-mensuales">
                            <!-- Detalle Agosto -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Agosto</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_agosto'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Septiembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Septiembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_septiembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Octubre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Octubre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_octubre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Noviembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Noviembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_noviembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Diciembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Diciembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_diciembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Enero -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Enero</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_enero'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Febrero -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Febrero</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dotacionesData['detalle_febrero'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($sheetKey == 'Agasajos')
                    <!-- Hoja Agasajos -->
                    <div class="agasajos-container">
                        <div class="sheet-header">
                            <h2 style="text-align: center; color: #2c3e50; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
                                PRESUPUESTO AÑO ESCOLAR 2024-2025
                            </h2>
                            <h3 style="text-align: center; color: #34495e; font-size: 16px; font-weight: 600; margin-bottom: 30px;">
                                Agasajos
                            </h3>
                        </div>

                        <!-- Tabla Resumen -->
                        <div class="table-wrapper" style="margin-bottom: 30px;">
                            <table class="table table-bordered budget-table" style="font-size: 12px;">
                                <thead style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
                                    <tr>
                                        <th style="width: 200px;">Concepto</th>
                                        <th style="width: 120px;">Ppto Aprobado</th>
                                        <th style="width: 120px;">Ejecutado</th>
                                        <th style="width: 120px;">Ppto a Ejec</th>
                                        <th style="width: 80px;">%Restante</th>
                                        <th style="width: 100px;">Julio</th>
                                        <th style="width: 100px;">Agosto</th>
                                        <th style="width: 100px;">Septiembre</th>
                                        <th style="width: 100px;">Octubre</th>
                                        <th style="width: 100px;">Noviembre</th>
                                        <th style="width: 100px;">Diciembre</th>
                                        <th style="width: 100px;">Enero</th>
                                        <th style="width: 100px;">Febrero</th>
                                        <th style="width: 100px;">Marzo</th>
                                        <th style="width: 100px;">Abril</th>
                                        <th style="width: 100px;">Mayo</th>
                                        <th style="width: 100px;">Junio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $resumen = $agasajosData['resumen'];
                                        $ejecucion = $agasajosData['ejecucion_mensual'];
                                    @endphp
                                    <tr>
                                        <td><strong>Detalle cumpleaños</strong></td>
                                        <td class="number-cell">${{ number_format($resumen['detalle_cumpleanos']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['detalle_cumpleanos']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['detalle_cumpleanos']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell"><strong>{{ $resumen['detalle_cumpleanos']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['julio'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['julio']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['detalle_cumpleanos']['agosto']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['septiembre'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['septiembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['octubre'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['noviembre'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['diciembre'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['diciembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['enero'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['enero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['febrero'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['marzo'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['abril'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['mayo'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['detalle_cumpleanos']['junio'] > 0 ? '$'.number_format($ejecucion['detalle_cumpleanos']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Día del Colaborador</strong></td>
                                        <td class="number-cell">{{ $resumen['dia_colaborador']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['dia_colaborador']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_colaborador']['ejecutado'] > 0 ? '$'.number_format($resumen['dia_colaborador']['ejecutado']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_colaborador']['presupuesto_ejecutar'] > 0 ? '$'.number_format($resumen['dia_colaborador']['presupuesto_ejecutar']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_colaborador']['porcentaje_restante'] > 0 ? $resumen['dia_colaborador']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['julio'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['julio']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['agosto'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['agosto']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['septiembre'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['septiembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['octubre'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['noviembre'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['diciembre'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['diciembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['enero'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['enero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['febrero'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['marzo'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['abril'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['mayo'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_colaborador']['junio'] > 0 ? '$'.number_format($ejecucion['dia_colaborador']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Día del Profesor/MAYO</strong></td>
                                        <td class="number-cell">{{ $resumen['dia_profesor']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['dia_profesor']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_profesor']['ejecutado'] > 0 ? '$'.number_format($resumen['dia_profesor']['ejecutado']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_profesor']['presupuesto_ejecutar'] > 0 ? '$'.number_format($resumen['dia_profesor']['presupuesto_ejecutar']) : '$0' }}</td>
                                        <td class="number-cell">{{ $resumen['dia_profesor']['porcentaje_restante'] > 0 ? $resumen['dia_profesor']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['julio'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['julio']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['agosto'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['agosto']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['septiembre'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['septiembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['octubre'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['noviembre'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['diciembre'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['diciembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['enero'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['enero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['febrero'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['marzo'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['abril'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['mayo'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['dia_profesor']['junio'] > 0 ? '$'.number_format($ejecucion['dia_profesor']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cena de fin de año</strong></td>
                                        <td class="number-cell">{{ $resumen['cena_fin_ano']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['cena_fin_ano']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($resumen['cena_fin_ano']['ejecutado']) }}</td>
                                        <td class="number-cell">-${{ number_format(abs($resumen['cena_fin_ano']['presupuesto_ejecutar'])) }}</td>
                                        <td class="number-cell">{{ $resumen['cena_fin_ano']['porcentaje_restante'] > 0 ? $resumen['cena_fin_ano']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['julio'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['julio']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['agosto'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['agosto']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['septiembre'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['septiembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['octubre'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['noviembre'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['cena_fin_ano']['diciembre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['enero'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['enero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['febrero'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['marzo'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['abril'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['mayo'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['cena_fin_ano']['junio'] > 0 ? '$'.number_format($ejecucion['cena_fin_ano']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ramos nacimientos, hospitalizaciones y otros</strong></td>
                                        <td class="number-cell">{{ $resumen['ramos_nacimientos']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['ramos_nacimientos']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($resumen['ramos_nacimientos']['ejecutado']) }}</td>
                                        <td class="number-cell">-${{ number_format(abs($resumen['ramos_nacimientos']['presupuesto_ejecutar'])) }}</td>
                                        <td class="number-cell">{{ $resumen['ramos_nacimientos']['porcentaje_restante'] > 0 ? $resumen['ramos_nacimientos']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['julio'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['julio']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['ramos_nacimientos']['agosto']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['septiembre'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['septiembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['octubre'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['octubre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['noviembre'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['ramos_nacimientos']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['ramos_nacimientos']['enero']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['febrero'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['marzo'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['abril'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['mayo'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['ramos_nacimientos']['junio'] > 0 ? '$'.number_format($ejecucion['ramos_nacimientos']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Integración EMC (almuerzos)</strong></td>
                                        <td class="number-cell">{{ $resumen['integracion_emc']['presupuesto_aprobado'] > 0 ? '$'.number_format($resumen['integracion_emc']['presupuesto_aprobado']) : '' }}</td>
                                        <td class="number-cell">${{ number_format($resumen['integracion_emc']['ejecutado']) }}</td>
                                        <td class="number-cell">-${{ number_format(abs($resumen['integracion_emc']['presupuesto_ejecutar'])) }}</td>
                                        <td class="number-cell">{{ $resumen['integracion_emc']['porcentaje_restante'] > 0 ? $resumen['integracion_emc']['porcentaje_restante'].'%' : '' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['julio'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['julio']) : '$0' }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['integracion_emc']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['integracion_emc']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['integracion_emc']['octubre']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['noviembre'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['noviembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['diciembre'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['diciembre']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['enero'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['enero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['febrero'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['febrero']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['marzo'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['abril'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['mayo'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['integracion_emc']['junio'] > 0 ? '$'.number_format($ejecucion['integracion_emc']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr style="background: #f8f9fa; font-weight: bold;">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $resumen['total']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['julio'] > 0 ? '$'.number_format($ejecucion['total']['julio']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['noviembre'] > 0 ? '$'.number_format($ejecucion['total']['noviembre']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['febrero'] > 0 ? '$'.number_format($ejecucion['total']['febrero']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['marzo'] > 0 ? '$'.number_format($ejecucion['total']['marzo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['abril'] > 0 ? '$'.number_format($ejecucion['total']['abril']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['mayo'] > 0 ? '$'.number_format($ejecucion['total']['mayo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['junio'] > 0 ? '$'.number_format($ejecucion['total']['junio']) : '$0' }}</strong></td>
                                    </tr>
                                    <!-- Fila vacía para separación -->
                                    <tr style="height: 20px;">
                                        <td colspan="17" style="border: none; background: white;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles por mes -->
                        <div class="detalles-mensuales">
                            <!-- Detalle Agosto -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Agosto</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agasajosData['detalle_agosto'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Septiembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Septiembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agasajosData['detalle_septiembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Octubre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Octubre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agasajosData['detalle_octubre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Diciembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Diciembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agasajosData['detalle_diciembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Enero -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Enero</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agasajosData['detalle_enero'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($sheetKey == 'Tecnología')
                    <!-- Hoja Tecnología -->
                    <div class="tecnologia-container">
                        <div class="sheet-header">
                            <h2 style="text-align: center; color: #2c3e50; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
                                PRESUPUESTO AÑO ESCOLAR 2024-2025
                            </h2>
                            <h3 style="text-align: center; color: #34495e; font-size: 16px; font-weight: 600; margin-bottom: 30px;">
                                Equipo y Dotación de Salones / Oficinas - Tecnología
                            </h3>
                        </div>

                        <!-- Tabla Resumen -->
                        <div class="table-wrapper" style="margin-bottom: 30px;">
                            <table class="table table-bordered budget-table" style="font-size: 12px;">
                                <thead style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                                    <tr>
                                        <th style="width: 200px;">Concepto</th>
                                        <th style="width: 120px;">Ppto Aprobado</th>
                                        <th style="width: 120px;">Ejecutado</th>
                                        <th style="width: 120px;">Ppto a Ejec</th>
                                        <th style="width: 80px;">%Restante</th>
                                        <th style="width: 100px;">Julio</th>
                                        <th style="width: 100px;">Agosto</th>
                                        <th style="width: 100px;">Septiembre</th>
                                        <th style="width: 100px;">Octubre</th>
                                        <th style="width: 100px;">Noviembre</th>
                                        <th style="width: 100px;">Diciembre</th>
                                        <th style="width: 100px;">Enero</th>
                                        <th style="width: 100px;">Febrero</th>
                                        <th style="width: 100px;">Marzo</th>
                                        <th style="width: 100px;">Abril</th>
                                        <th style="width: 100px;">Mayo</th>
                                        <th style="width: 100px;">Junio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $resumen = $tecnologiaData['resumen'];
                                        $ejecucion = $tecnologiaData['ejecucion_mensual'];
                                    @endphp
                                    <tr>
                                        <td><strong>Tecnología institucional</strong></td>
                                        <td class="number-cell">${{ number_format($resumen['tecnologia_institucional']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['tecnologia_institucional']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($resumen['tecnologia_institucional']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell"><strong>{{ $resumen['tecnologia_institucional']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($ejecucion['tecnologia_institucional']['febrero']) }}</td>
                                        <td class="number-cell">{{ $ejecucion['tecnologia_institucional']['marzo'] > 0 ? '$'.number_format($ejecucion['tecnologia_institucional']['marzo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['tecnologia_institucional']['abril'] > 0 ? '$'.number_format($ejecucion['tecnologia_institucional']['abril']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['tecnologia_institucional']['mayo'] > 0 ? '$'.number_format($ejecucion['tecnologia_institucional']['mayo']) : '$0' }}</td>
                                        <td class="number-cell">{{ $ejecucion['tecnologia_institucional']['junio'] > 0 ? '$'.number_format($ejecucion['tecnologia_institucional']['junio']) : '$0' }}</td>
                                    </tr>
                                    <tr style="background: #f8f9fa; font-weight: bold;">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($resumen['total']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $resumen['total']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($ejecucion['total']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['marzo'] > 0 ? '$'.number_format($ejecucion['total']['marzo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['abril'] > 0 ? '$'.number_format($ejecucion['total']['abril']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['mayo'] > 0 ? '$'.number_format($ejecucion['total']['mayo']) : '$0' }}</strong></td>
                                        <td class="number-cell"><strong>{{ $ejecucion['total']['junio'] > 0 ? '$'.number_format($ejecucion['total']['junio']) : '$0' }}</strong></td>
                                    </tr>
                                    <!-- Fila vacía para separación -->
                                    <tr style="height: 20px;">
                                        <td colspan="17" style="border: none; background: white;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles por mes -->
                        <div class="detalles-mensuales">
                            <!-- Detalle Septiembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Septiembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tecnologiaData['detalle_septiembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Octubre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Octubre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tecnologiaData['detalle_octubre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalle Noviembre -->
                            <div class="detalle-mes" style="margin-bottom: 30px;">
                                <h4 style="color: #2c3e50; font-size: 14px; font-weight: bold; margin-bottom: 15px;">Noviembre</h4>
                                <div class="table-wrapper">
                                    <table class="table table-bordered" style="font-size: 11px;">
                                        <thead style="background: #ecf0f1;">
                                            <tr>
                                                <th style="width: 200px;">Nombre Tercero</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th style="width: 150px;">Concepto</th>
                                                <th style="width: 120px; text-align: right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tecnologiaData['detalle_noviembre'] as $item)
                                            <tr>
                                                <td>{{ $item['proveedor'] }}</td>
                                                <td>{{ $item['descripcion'] }}</td>
                                                <td>{{ $item['concepto'] }}</td>
                                                <td class="number-cell">${{ number_format($item['valor']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                
                @elseif($sheetKey == 'Afiliaciones y Suscrip')
                    <!-- AFILIACIONES Y SUSCRIPCIONES -->
                    <div class="afiliaciones-suscripciones-container">
                        <h2 class="section-title">🏛️ AFILIACIONES Y SUSCRIPCIONES</h2>
                        
                        <!-- Tabla de Resumen por Concepto -->
                        <div class="budget-table">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Ppto Aprobado</th>
                                        <th>Ejecutado</th>
                                        <th>Ppto a Ejecutar</th>
                                        <th>% Restante</th>
                                        <th>Jul</th>
                                        <th>Ago</th>
                                        <th>Sep</th>
                                        <th>Oct</th>
                                        <th>Nov</th>
                                        <th>Dic</th>
                                        <th>Ene</th>
                                        <th>Feb</th>
                                        <th>Mar</th>
                                        <th>Abr</th>
                                        <th>May</th>
                                        <th>Jun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>AACBI</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['aacbi']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['aacbi']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['aacbi']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['aacbi']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['aacbi']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ADVANCED</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['advanced']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['advanced']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['advanced']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['advanced']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['advanced']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Red Papaz</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['red_papaz']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['red_papaz']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['red_papaz']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['red_papaz']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['red_papaz']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Impuestos Asumidos</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['impuestos_asumidos']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['impuestos_asumidos']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['impuestos_asumidos']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['impuestos_asumidos']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['impuestos_asumidos']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Data Coaching Service MAP</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['data_coaching_service']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['data_coaching_service']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['data_coaching_service']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['data_coaching_service']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['data_coaching_service']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Andep</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['andep']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['andep']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['andep']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['andep']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['andep']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>El Tiempo</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['el_tiempo']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['el_tiempo']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['el_tiempo']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['el_tiempo']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['el_tiempo']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bordenorte</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['bordenorte']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['bordenorte']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['bordenorte']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['bordenorte']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['bordenorte']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Datacrédito</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['datacredito']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['datacredito']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['datacredito']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['datacredito']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['datacredito']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Licencias Sokanu Inthinking</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['licencias_sokanu']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['licencias_sokanu']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['licencias_sokanu']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['licencias_sokanu']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['licencias_sokanu']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cognia</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cognia']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cognia']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cognia']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['cognia']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cognia']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cipres (Mejores Colegios)</strong></td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cipres']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cipres']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['resumen']['cipres']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $afiliacionesSuscripcionesData['resumen']['cipres']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['cipres']['junio']) }}</td>
                                    </tr>
                                    <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['resumen']['total']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['resumen']['total']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['resumen']['total']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $afiliacionesSuscripcionesData['resumen']['total']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['marzo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['abril']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['mayo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($afiliacionesSuscripcionesData['ejecucion_mensual']['total']['junio']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles por Mes -->
                        <div class="row mt-4">
                            @php
                                $mesesDetalle = [
                                    'septiembre' => 'Septiembre 2024',
                                    'octubre' => 'Octubre 2024',
                                    'noviembre' => 'Noviembre 2024',
                                    'diciembre' => 'Diciembre 2024',
                                    'enero' => 'Enero 2025'
                                ];
                            @endphp

                            @foreach($mesesDetalle as $mes => $nombreMes)
                                @if(isset($afiliacionesSuscripcionesData['detalle_' . $mes]) && count($afiliacionesSuscripcionesData['detalle_' . $mes]) > 0)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="card-title mb-0">📅 {{ $nombreMes }}</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="font-size: 0.8em;">Proveedor</th>
                                                                <th style="font-size: 0.8em;">Concepto</th>
                                                                <th style="font-size: 0.8em;" class="text-end">Valor</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($afiliacionesSuscripcionesData['detalle_' . $mes] as $item)
                                                            <tr>
                                                                <td style="font-size: 0.85em;">
                                                                    <strong>{{ $item['proveedor'] }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ $item['descripcion'] }}</small>
                                                                </td>
                                                                <td style="font-size: 0.85em;">{{ $item['concepto'] }}</td>
                                                                <td style="font-size: 0.85em;" class="text-end">
                                                                    <span class="badge bg-success">${{ number_format($item['valor']) }}</span>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-light text-center">
                                                @php
                                                    $totalMes = collect($afiliacionesSuscripcionesData['detalle_' . $mes])->sum('valor');
                                                @endphp
                                                <strong>Total: ${{ number_format($totalMes) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'IB')
                    <!-- BACHILLERATO INTERNACIONAL -->
                    <div id="sheet-ib" class="sheet-section">
                        <h2 class="section-title">🎓 BACHILLERATO INTERNACIONAL</h2>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($bachilleratoInternacionalData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($bachilleratoInternacionalData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($bachilleratoInternacionalData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Por Ejecutar</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">{{ $bachilleratoInternacionalData['resumen']['porcentaje_restante'] }}%</div>
                                        <div class="summary-label">% Restante</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Conceptos -->
                        <div class="table-container">
                            <table class="budget-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Ppto Aprobado</th>
                                        <th>Ejecutado</th>
                                        <th>Ppto a Ejec</th>
                                        <th>%Restante</th>
                                        <th>Julio</th>
                                        <th>Agosto</th>
                                        <th>Septiembre</th>
                                        <th>Octubre</th>
                                        <th>Noviembre</th>
                                        <th>Diciembre</th>
                                        <th>Enero</th>
                                        <th>Febrero</th>
                                        <th>Marzo</th>
                                        <th>Abril</th>
                                        <th>Mayo</th>
                                        <th>Junio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bachilleratoInternacionalData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'PYP ANNUAL FEE- SEP US $ 7,443 TRM 4.300')
                                                ${{ number_format(29735564) }}
                                            @elseif($concepto['concepto'] == 'DP ANNUAL FEE - agosto US $10,177 TRM 4.300')
                                                ${{ number_format(40653589) }}
                                            @elseif($concepto['concepto'] == 'RETENCION EN LA FUENTE ASUMIDA PAGOS EXTERIOR')
                                                ${{ number_format(16559593) }}
                                            @elseif($concepto['concepto'] == 'REACREDITACION COGNIA 8000 usd +15000000')
                                                ${{ number_format(12408810) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'MYP ANNUAL FEE - SEP US $ 8780 TRM 4.300')
                                                ${{ number_format(36252565) }}
                                            @elseif($concepto['concepto'] == 'RETENCION EN LA FUENTE ASUMIDA PAGOS EXTERIOR')
                                                ${{ number_format(7250513) }}
                                            @elseif($concepto['concepto'] == 'REACREDITACION COGNIA 8000 usd +15000000')
                                                ${{ number_format(33360800) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'DERECHO EXAMENES US 14409 TRM 4.000')
                                                ${{ number_format(88440551) }}
                                            @elseif($concepto['concepto'] == 'RETENCION EN LA FUENTE ASUMIDA PAGOS EXTERIOR')
                                                ${{ number_format(17688110) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                    </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['resumen']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['resumen']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['resumen']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $bachilleratoInternacionalData['resumen']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['marzo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['abril']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['mayo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($bachilleratoInternacionalData['meses']['junio']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalle Mensual con Cards -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📅 Detalle de Ejecución Mensual</h3>
                            <div class="row">
                                @foreach(['agosto', 'septiembre', 'noviembre'] as $mes)
                                    @if($bachilleratoInternacionalData['meses'][$mes] > 0)
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-gradient-info text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-calendar-alt me-2"></i>
                                                        {{ ucfirst($mes) }} 2024
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    @if($mes == 'agosto')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                                                PYP Annual Fee
                                                            </span>
                                                            <span class="detail-amount badge bg-success">$29,735,564</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                                                DP Annual Fee
                                                            </span>
                                                            <span class="detail-amount badge bg-success">$40,653,589</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-receipt text-warning me-2"></i>
                                                                Retención en la Fuente
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$16,559,593</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-certificate text-info me-2"></i>
                                                                Reacreditación COGNIA
                                                            </span>
                                                            <span class="detail-amount badge bg-info">$12,408,810</span>
                                                        </div>
                                                    @elseif($mes == 'septiembre')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                                                MYP Annual Fee
                                                            </span>
                                                            <span class="detail-amount badge bg-success">$36,252,565</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-receipt text-warning me-2"></i>
                                                                Retención en la Fuente
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$7,250,513</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-certificate text-info me-2"></i>
                                                                Reacreditación COGNIA
                                                            </span>
                                                            <span class="detail-amount badge bg-info">$33,360,800</span>
                                                        </div>
                                                    @elseif($mes == 'noviembre')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-file-alt text-danger me-2"></i>
                                                                Derecho Exámenes
                                                            </span>
                                                            <span class="detail-amount badge bg-danger">$88,440,551</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-receipt text-warning me-2"></i>
                                                                Retención en la Fuente
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$17,688,110</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-footer bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-primary">Total del Mes:</strong>
                                                        <strong class="text-success h5 mb-0">${{ number_format($bachilleratoInternacionalData['meses'][$mes]) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Deportes')
                    <!-- DEPORTES -->
                    <div id="sheet-deportes" class="sheet-section">
                        <h2 class="section-title">⚽ DEPORTES</h2>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($deportesData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($deportesData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($deportesData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Por Ejecutar</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">{{ $deportesData['resumen']['porcentaje_restante'] }}%</div>
                                        <div class="summary-label">% Restante</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Conceptos -->
                        <div class="table-container">
                            <table class="budget-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Ppto Aprobado</th>
                                        <th>Ejecutado</th>
                                        <th>Ppto a Ejec</th>
                                        <th>%Restante</th>
                                        <th>Julio</th>
                                        <th>Agosto</th>
                                        <th>Septiembre</th>
                                        <th>Octubre</th>
                                        <th>Noviembre</th>
                                        <th>Diciembre</th>
                                        <th>Enero</th>
                                        <th>Febrero</th>
                                        <th>Marzo</th>
                                        <th>Abril</th>
                                        <th>Mayo</th>
                                        <th>Junio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deportesData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'AFILIACION')
                                                ${{ number_format(4195000) }}
                                            @elseif($concepto['concepto'] == 'TRANSPORTE SALIDAS DEPORTIVAS')
                                                ${{ number_format(3366000) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'PARTICIPACION EN TEMPORADAS')
                                                ${{ number_format(1950000) }}
                                            @elseif($concepto['concepto'] == 'TRANSPORTE SALIDAS DEPORTIVAS')
                                                ${{ number_format(2448000) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'TRANSPORTE SALIDAS DEPORTIVAS')
                                                ${{ number_format(1125000) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                    </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><strong>Total Gastos</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['resumen']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['resumen']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['resumen']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $deportesData['resumen']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['marzo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['abril']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['mayo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($deportesData['meses']['junio']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalle Mensual con Cards -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📅 Detalle de Ejecución Mensual</h3>
                            <div class="row">
                                @foreach(['septiembre', 'octubre', 'noviembre'] as $mes)
                                    @if($deportesData['meses'][$mes] > 0)
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-gradient-primary text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-calendar-alt me-2"></i>
                                                        {{ ucfirst($mes) }} 2024
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    @if($mes == 'septiembre')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-id-card text-success me-2"></i>
                                                                Afiliación
                                                            </span>
                                                            <span class="detail-amount badge bg-success">$4,195,000</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-bus text-warning me-2"></i>
                                                                Transporte Salidas
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$3,366,000</span>
                                                        </div>
                                                    @elseif($mes == 'octubre')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-trophy text-primary me-2"></i>
                                                                Participación Temporadas
                                                            </span>
                                                            <span class="detail-amount badge bg-primary">$1,950,000</span>
                                                        </div>
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-bus text-warning me-2"></i>
                                                                Transporte Salidas
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$2,448,000</span>
                                                        </div>
                                                    @elseif($mes == 'noviembre')
                                                        <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                            <span class="detail-concept">
                                                                <i class="fas fa-bus text-warning me-2"></i>
                                                                Transporte Salidas
                                                            </span>
                                                            <span class="detail-amount badge bg-warning">$1,125,000</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-footer bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-primary">Total del Mes:</strong>
                                                        <strong class="text-success h5 mb-0">${{ number_format($deportesData['meses'][$mes]) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Entrenamientos')
                    <!-- ENTRENAMIENTOS -->
                    <div id="sheet-entrenamientos" class="sheet-section">
                        <h2 class="section-title">🏃‍♂️ ENTRENAMIENTOS 2024-2025</h2>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-success">${{ number_format($entrenamientosData['resumen']['total_ingresos']) }}</div>
                                        <div class="summary-label">Total Ingresos</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">${{ number_format($entrenamientosData['resumen']['total_gastos']) }}</div>
                                        <div class="summary-label">Total Gastos</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value {{ $entrenamientosData['resumen']['deficit_utilidad'] < 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($entrenamientosData['resumen']['deficit_utilidad']) }}
                                        </div>
                                        <div class="summary-label">{{ $entrenamientosData['resumen']['deficit_utilidad'] < 0 ? 'Déficit' : 'Utilidad' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">{{ number_format($entrenamientosData['resumen']['porcentaje_deficit'], 1) }}%</div>
                                        <div class="summary-label">% Déficit</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Ingresos -->
                        <div class="table-container mb-4">
                            <h3 class="mb-3">💰 INGRESOS</h3>
                            <table class="budget-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Julio</th>
                                        <th>Agosto</th>
                                        <th>Septiembre</th>
                                        <th>Octubre</th>
                                        <th>Noviembre</th>
                                        <th>Diciembre</th>
                                        <th>Enero</th>
                                        <th>Febrero</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Valor Facturado</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell">
                                            @if($entrenamientosData['ingresos'][$mes]['estudiantes'] > 0)
                                                {{ $entrenamientosData['ingresos'][$mes]['estudiantes'] }} est.<br>
                                                ${{ number_format($entrenamientosData['ingresos'][$mes]['valor']) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @endforeach
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['resumen']['total_ingresos']) }}</strong></td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Total Ingreso</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['ingresos'][$mes]['valor']) }}</strong></td>
                                        @endforeach
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['resumen']['total_ingresos']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tabla de Gastos -->
                        <div class="table-container mb-4">
                            <h3 class="mb-3">💸 GASTOS</h3>
                            <table class="budget-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Julio</th>
                                        <th>Agosto</th>
                                        <th>Septiembre</th>
                                        <th>Octubre</th>
                                        <th>Noviembre</th>
                                        <th>Diciembre</th>
                                        <th>Enero</th>
                                        <th>Febrero</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Transporte Entrenamientos</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell">
                                            @if($entrenamientosData['gastos']['transporte'][$mes]['valor'] > 0)
                                                {{ $entrenamientosData['gastos']['transporte'][$mes]['rutas_dias'] }}<br>
                                                ${{ number_format($entrenamientosData['gastos']['transporte'][$mes]['valor']) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @endforeach
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['gastos']['transporte']['total']) }}</strong></td>
                                    </tr>
                                    <tr>
                            