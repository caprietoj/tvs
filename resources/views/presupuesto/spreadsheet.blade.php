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
                                    <th data-sort="fuente">Fuente</th>
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
                                            <td>{{ $item->fuente ?? '-' }}</td>
                                            <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                                            <td class="number-cell valor-clickeable" 
                                data-item-id="{{ $item->id }}"
                                data-seccion="{{ $item->seccion }}"
                                data-rubro="{{ $item->rubro }}"
                                data-cuenta="{{ $item->cuenta }}"
                                data-documento="{{ $item->documento ?? '-' }}"
                                data-fuente="{{ $item->fuente ?? '-' }}"
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
                                            <div class="info-item">
                                                <strong>Fuente:</strong>
                                                <span id="modal-fuente"></span>
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
                                        @php
                                            // Obtener datos dinámicos de ingresos escolares por mes (desde el controlador)
                                            $ingresosEscolaresData = $budgetDataByConcept['ingresos-escolares'] ?? [];
                                            $otrosEscolaresData = $budgetDataByConcept['otros-escolares'] ?? [];
                                            
                                            // Debug temporal - ver qué datos llegan
                                            \Log::info('Vista - Ingresos escolares data:', $ingresosEscolaresData);
                                            \Log::info('Vista - Otros escolares data:', $otrosEscolaresData);
                                            
                                            // Presupuestos aprobados
                                            $presupuestoIngresosEscolares = 10457915716;
                                            $presupuestoOtrosEscolares = 868862765;
                                        @endphp
                                        <tr>
                                            <td><strong>Ingresos Escolares</strong></td>
                                            <td class="number-cell">${{ number_format($presupuestoIngresosEscolares, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($ingresosEscolaresData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos otros escolares</strong></td>
                                            <td class="number-cell">${{ number_format($presupuestoOtrosEscolares, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($otrosEscolaresData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @php
                                            // Calcular totales dinámicos por mes
                                            $meses = ['junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'];
                                            $totalesPorMes = [];
                                            foreach ($meses as $mes) {
                                                $totalesPorMes[$mes] = ($ingresosEscolaresData[$mes] ?? 0) + ($otrosEscolaresData[$mes] ?? 0);
                                            }
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL INGRESOS</strong></td>
                                            <td class="number-cell"><strong>${{ number_format($presupuestoIngresosEscolares + $presupuestoOtrosEscolares, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['junio'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['julio'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['agosto'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['septiembre'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['octubre'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['noviembre'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['diciembre'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['enero'], 0, ',', '.') }}</strong></td>
                                            <td class="number-cell calculated"><strong>${{ number_format($totalesPorMes['febrero'], 0, ',', '.') }}</strong></td>
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
                                        @php
                                            // Obtener datos de las tablas detalladas correspondientes
                                            $salariosAcademiaData = $budgetDataByConcept['salarios-academia'] ?? [];
                                            $salariosAdminData = $budgetDataByConcept['salarios-administracion'] ?? [];
                                            $rubrosData = $budgetDataByConcept['rubros-institucionales'] ?? [];
                                            $seccionesAcademiaData = $budgetDataByConcept['secciones-academia-general'] ?? [];
                                            $serviciosData = $budgetDataByConcept['servicios-publicos'] ?? [];
                                            $contratosExternosData = $budgetDataByConcept['contratos-externos'] ?? [];
                                        @endphp
                                        
                                        {{-- Total Salarios, Prestaciones Academia --}}
                                        <tr>
                                            <td><strong>Total Salarios, Prestaciones Academia</strong></td>
                                            <td class="number-cell">$6.600.750.523</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($salariosAcademiaData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        
                                        {{-- Total Salarios, Prestaciones Administrativos y Sena --}}
                                        @php
                                            // Calcular totales de administración sumando todos los conceptos por mes
                                            $adminJunio = ($salariosAdminData['salarios-aux-transporte-admin-junio'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-junio'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-junio'] ?? 0);
                                            $adminJulio = ($salariosAdminData['salarios-aux-transporte-admin-julio'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-julio'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-julio'] ?? 0);
                                            $adminAgosto = ($salariosAdminData['salarios-aux-transporte-admin-agosto'] ?? 0) + 
                                                          ($salariosAdminData['capacitacion-administracion-agosto'] ?? 0) + 
                                                          ($salariosAdminData['aprendices-sena-agosto'] ?? 0);
                                            $adminSeptiembre = ($salariosAdminData['salarios-aux-transporte-admin-septiembre'] ?? 0) + 
                                                              ($salariosAdminData['capacitacion-administracion-septiembre'] ?? 0) + 
                                                              ($salariosAdminData['aprendices-sena-septiembre'] ?? 0);
                                            $adminOctubre = ($salariosAdminData['salarios-aux-transporte-admin-octubre'] ?? 0) + 
                                                           ($salariosAdminData['capacitacion-administracion-octubre'] ?? 0) + 
                                                           ($salariosAdminData['aprendices-sena-octubre'] ?? 0);
                                            $adminNoviembre = ($salariosAdminData['salarios-aux-transporte-admin-noviembre'] ?? 0) + 
                                                             ($salariosAdminData['capacitacion-administracion-noviembre'] ?? 0) + 
                                                             ($salariosAdminData['aprendices-sena-noviembre'] ?? 0);
                                            $adminDiciembre = ($salariosAdminData['salarios-aux-transporte-admin-diciembre'] ?? 0) + 
                                                             ($salariosAdminData['capacitacion-administracion-diciembre'] ?? 0) + 
                                                             ($salariosAdminData['aprendices-sena-diciembre'] ?? 0);
                                            $adminEnero = ($salariosAdminData['salarios-aux-transporte-admin-enero'] ?? 0) + 
                                                         ($salariosAdminData['capacitacion-administracion-enero'] ?? 0) + 
                                                         ($salariosAdminData['aprendices-sena-enero'] ?? 0);
                                            $adminFebrero = ($salariosAdminData['salarios-aux-transporte-admin-febrero'] ?? 0) + 
                                                           ($salariosAdminData['capacitacion-administracion-febrero'] ?? 0) + 
                                                           ($salariosAdminData['aprendices-sena-febrero'] ?? 0);
                                        @endphp
                                        <tr>
                                            <td><strong>Total Salarios, Prestaciones Administrativos y Sena</strong></td>
                                            <td class="number-cell">$1.453.226.337</td>
                                            <td class="number-cell">${{ number_format($adminJunio, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminJulio, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminAgosto, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminSeptiembre, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminOctubre, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminNoviembre, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminDiciembre, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminEnero, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($adminFebrero, 0, ',', '.') }}</td>
                                        </tr>
                                        
                                        {{-- Total Rubros Institucionales --}}
                                        <tr>
                                            <td><strong>Total Rubros Institucionales</strong></td>
                                            <td class="number-cell">$1.172.440.107</td>
                                            <td class="number-cell">${{ number_format($rubrosData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($rubrosData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        
                                        {{-- Total Seccion Academia --}}
                                        <tr>
                                            <td><strong>Total Seccion Academia</strong></td>
                                            <td class="number-cell">$481.271.150</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($seccionesAcademiaData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        
                                        {{-- Total Servicios Públicos y Otros Egresos --}}
                                        <tr>
                                            <td><strong>Total Servicios Públicos y Otros Egresos</strong></td>
                                            <td class="number-cell">$2.594.069.715</td>
                                            <td class="number-cell">${{ number_format($serviciosData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($serviciosData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        
                                        {{-- Total Costos Contratos Externos --}}
                                        <tr>
                                            <td><strong>Total Costos Contratos Externos</strong></td>
                                            <td class="number-cell">$1.831.454.774</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['junio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['julio'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['agosto'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['septiembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['octubre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['noviembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['diciembre'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['enero'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="number-cell">${{ number_format($contratosExternosData['febrero'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @php
                                            // Calcular totales por mes sumando todas las categorías
                                            $totalJunio = ($salariosAcademiaData['junio'] ?? 0) + $adminJunio + ($rubrosData['junio'] ?? 0) + ($seccionesAcademiaData['junio'] ?? 0) + ($serviciosData['junio'] ?? 0) + ($contratosExternosData['junio'] ?? 0);
                                            $totalJulio = ($salariosAcademiaData['julio'] ?? 0) + $adminJulio + ($rubrosData['julio'] ?? 0) + ($seccionesAcademiaData['julio'] ?? 0) + ($serviciosData['julio'] ?? 0) + ($contratosExternosData['julio'] ?? 0);
                                            $totalAgosto = ($salariosAcademiaData['agosto'] ?? 0) + $adminAgosto + ($rubrosData['agosto'] ?? 0) + ($seccionesAcademiaData['agosto'] ?? 0) + ($serviciosData['agosto'] ?? 0) + ($contratosExternosData['agosto'] ?? 0);
                                            $totalSeptiembre = ($salariosAcademiaData['septiembre'] ?? 0) + $adminSeptiembre + ($rubrosData['septiembre'] ?? 0) + ($seccionesAcademiaData['septiembre'] ?? 0) + ($serviciosData['septiembre'] ?? 0) + ($contratosExternosData['septiembre'] ?? 0);
                                            $totalOctubre = ($salariosAcademiaData['octubre'] ?? 0) + $adminOctubre + ($rubrosData['octubre'] ?? 0) + ($seccionesAcademiaData['octubre'] ?? 0) + ($serviciosData['octubre'] ?? 0) + ($contratosExternosData['octubre'] ?? 0);
                                            $totalNoviembre = ($salariosAcademiaData['noviembre'] ?? 0) + $adminNoviembre + ($rubrosData['noviembre'] ?? 0) + ($seccionesAcademiaData['noviembre'] ?? 0) + ($serviciosData['noviembre'] ?? 0) + ($contratosExternosData['noviembre'] ?? 0);
                                            $totalDiciembre = ($salariosAcademiaData['diciembre'] ?? 0) + $adminDiciembre + ($rubrosData['diciembre'] ?? 0) + ($seccionesAcademiaData['diciembre'] ?? 0) + ($serviciosData['diciembre'] ?? 0) + ($contratosExternosData['diciembre'] ?? 0);
                                            $totalEnero = ($salariosAcademiaData['enero'] ?? 0) + $adminEnero + ($rubrosData['enero'] ?? 0) + ($seccionesAcademiaData['enero'] ?? 0) + ($serviciosData['enero'] ?? 0) + ($contratosExternosData['enero'] ?? 0);
                                            $totalFebrero = ($salariosAcademiaData['febrero'] ?? 0) + $adminFebrero + ($rubrosData['febrero'] ?? 0) + ($seccionesAcademiaData['febrero'] ?? 0) + ($serviciosData['febrero'] ?? 0) + ($contratosExternosData['febrero'] ?? 0);
                                        @endphp
                                        <tr class="total-row">
                                            <td><strong>TOTAL GASTOS</strong></td>
                                            <td class="number-cell"><strong>$14.144.488.971</strong></td>
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
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Matriculas'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Matriculas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Matriculas'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Matriculas'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pensiones</strong></td>
                                            <td class="number-cell">$8.816.286.570</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Pensiones'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Pensiones'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Pensiones'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Pensiones'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Seguros Estudiantiles</strong></td>
                                            <td class="number-cell">$3.922.844</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Seguros Estudiantiles'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Seguros Estudiantiles'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Seguros Estudiantiles'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Desarrollo curricular bilingüe / Bibliobanco</strong></td>
                                            <td class="number-cell">$443.751.216</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Desarrollo curricular bilingüe / Bibliobanco'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Desarrollo curricular bilingüe / Bibliobanco'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sistematización de Notas</strong></td>
                                            <td class="number-cell">$98.984.742</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Sistematización de Notas'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Sistematización de Notas'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Sistematización de Notas'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Materiales generales</strong></td>
                                            <td class="number-cell">$115.165.581</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['junio']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['junio']['Materiales generales'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['julio']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['julio']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['agosto']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['agosto']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['septiembre']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['septiembre']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['octubre']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['octubre']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['noviembre']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['noviembre']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['diciembre']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['diciembre']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['enero']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['enero']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $ingresosEscolaresReales['febrero']['Materiales generales'] > 0 ? '$'.number_format($ingresosEscolaresReales['febrero']['Materiales generales'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL INGRESOS ESCOLARES</strong></td>
                                            <td class="number-cell calculated"><strong>$10.457.915.716</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['junio'] > 0 ? '$'.number_format($totalesIngresosEscolares['junio'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['julio'] > 0 ? '$'.number_format($totalesIngresosEscolares['julio'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['agosto'] > 0 ? '$'.number_format($totalesIngresosEscolares['agosto'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['septiembre'] > 0 ? '$'.number_format($totalesIngresosEscolares['septiembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['octubre'] > 0 ? '$'.number_format($totalesIngresosEscolares['octubre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['noviembre'] > 0 ? '$'.number_format($totalesIngresosEscolares['noviembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['diciembre'] > 0 ? '$'.number_format($totalesIngresosEscolares['diciembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['enero'] > 0 ? '$'.number_format($totalesIngresosEscolares['enero'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesIngresosEscolares['febrero'] > 0 ? '$'.number_format($totalesIngresosEscolares['febrero'], 0, ',', '.') : '$-' }}</strong></td>
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
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Rendimientos/Intereses'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Rendimientos/Intereses'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Rendimientos/Intereses'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Rendimientos/Intereses'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Rendimientos/Intereses'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agenda escolar</strong></td>
                                            <td class="number-cell">$114.682.596</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Agenda escolar'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Agenda escolar'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Agenda escolar'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Agenda escolar'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Agenda escolar'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Anuario</strong></td>
                                            <td class="number-cell">$9.257.396</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Anuario'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Anuario'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Anuario'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Anuario'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Anuario'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Anuario'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Anuario'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Anuario'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Anuario'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Anuario'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Examenes de Admisión</strong></td>
                                            <td class="number-cell">$38.371.950</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Examenes de Admisión'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Examenes de Admisión'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Examenes de Admisión'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Examenes de Admisión'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Examenes de Admisión'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos Por Servicio Cafeteria</strong></td>
                                            <td class="number-cell">$6.424.511</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Ingresos Por Servicio Cafeteria'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Ingresos Por Servicio Cafeteria'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ingresos Por Servicio Transporte</strong></td>
                                            <td class="number-cell">$700.126.312</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['junio']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['junio']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['julio']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['julio']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['agosto']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['agosto']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '$-' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['septiembre']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['septiembre']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['octubre']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['octubre']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['noviembre']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['noviembre']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['diciembre']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['diciembre']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['enero']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['enero']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                            <td class="number-cell">{{ $otrosEscolaresReales['febrero']['Ingresos Por Servicio Transporte'] > 0 ? '$'.number_format($otrosEscolaresReales['febrero']['Ingresos Por Servicio Transporte'], 0, ',', '.') : '' }}</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>TOTAL OTROS ESCOLARES</strong></td>
                                            <td class="number-cell calculated"><strong>$868.862.765</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['junio'] > 0 ? '$'.number_format($totalesOtrosEscolares['junio'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['julio'] > 0 ? '$'.number_format($totalesOtrosEscolares['julio'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['agosto'] > 0 ? '$'.number_format($totalesOtrosEscolares['agosto'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['septiembre'] > 0 ? '$'.number_format($totalesOtrosEscolares['septiembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['octubre'] > 0 ? '$'.number_format($totalesOtrosEscolares['octubre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['noviembre'] > 0 ? '$'.number_format($totalesOtrosEscolares['noviembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['diciembre'] > 0 ? '$'.number_format($totalesOtrosEscolares['diciembre'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['enero'] > 0 ? '$'.number_format($totalesOtrosEscolares['enero'], 0, ',', '.') : '$-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $totalesOtrosEscolares['febrero'] > 0 ? '$'.number_format($totalesOtrosEscolares['febrero'], 0, ',', '.') : '$-' }}</strong></td>
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
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? (6600750523 / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $junioValue > 0) ? ($junioValue / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $julioValue > 0) ? ($julioValue / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $agostoValue > 0) ? ($agostoValue / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $septiembreValue > 0) ? ($septiembreValue / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $octubreValue > 0) ? ($octubreValue / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $noviembreValue > 0) ? ($noviembreValue / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $diciembreValue > 0) ? ($diciembreValue / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $eneroValue > 0) ? ($eneroValue / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $febreroValue > 0) ? ($febreroValue / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated">{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</td>
                                            <td class="number-cell calculated">{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</td>
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
                                                $totalAgosto = 0;
                                                $totalSeptiembre = 0;
                                                $totalOctubre = 0;
                                                $totalNoviembre = 0;
                                                $totalDiciembre = 0;
                                                $totalEnero = 0;
                                                $totalFebrero = 0;
                                                foreach($rubrosLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $rubrosData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $rubrosData[$conceptKey . '-julio'] ?? 0;
                                                    $totalAgosto += $rubrosData[$conceptKey . '-agosto'] ?? 0;
                                                    $totalSeptiembre += $rubrosData[$conceptKey . '-septiembre'] ?? 0;
                                                    $totalOctubre += $rubrosData[$conceptKey . '-octubre'] ?? 0;
                                                    $totalNoviembre += $rubrosData[$conceptKey . '-noviembre'] ?? 0;
                                                    $totalDiciembre += $rubrosData[$conceptKey . '-diciembre'] ?? 0;
                                                    $totalEnero += $rubrosData[$conceptKey . '-enero'] ?? 0;
                                                    $totalFebrero += $rubrosData[$conceptKey . '-febrero'] ?? 0;
                                                }
                                            @endphp
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
                                        <tr class="percentage-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? (array_sum($rubrosData) / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $totalJunio > 0) ? ($totalJunio / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $totalJulio > 0) ? ($totalJulio / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $totalAgosto > 0) ? ($totalAgosto / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $totalSeptiembre > 0) ? ($totalSeptiembre / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $totalOctubre > 0) ? ($totalOctubre / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $totalNoviembre > 0) ? ($totalNoviembre / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $totalDiciembre > 0) ? ($totalDiciembre / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $totalEnero > 0) ? ($totalEnero / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $totalFebrero > 0) ? ($totalFebrero / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated"><strong>{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</strong></td>
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
                                                $totalAgosto = 0;
                                                $totalSeptiembre = 0;
                                                $totalOctubre = 0;
                                                $totalNoviembre = 0;
                                                $totalDiciembre = 0;
                                                $totalEnero = 0;
                                                $totalFebrero = 0;
                                                foreach($membresiasLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $membresiasData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $membresiasData[$conceptKey . '-julio'] ?? 0;
                                                    $totalAgosto += $membresiasData[$conceptKey . '-agosto'] ?? 0;
                                                    $totalSeptiembre += $membresiasData[$conceptKey . '-septiembre'] ?? 0;
                                                    $totalOctubre += $membresiasData[$conceptKey . '-octubre'] ?? 0;
                                                    $totalNoviembre += $membresiasData[$conceptKey . '-noviembre'] ?? 0;
                                                    $totalDiciembre += $membresiasData[$conceptKey . '-diciembre'] ?? 0;
                                                    $totalEnero += $membresiasData[$conceptKey . '-enero'] ?? 0;
                                                    $totalFebrero += $membresiasData[$conceptKey . '-febrero'] ?? 0;
                                                }
                                            @endphp
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
                                        <tr class="percentage-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? (array_sum($membresiasData) / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $totalJunio > 0) ? ($totalJunio / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $totalJulio > 0) ? ($totalJulio / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $totalAgosto > 0) ? ($totalAgosto / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $totalSeptiembre > 0) ? ($totalSeptiembre / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $totalOctubre > 0) ? ($totalOctubre / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $totalNoviembre > 0) ? ($totalNoviembre / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $totalDiciembre > 0) ? ($totalDiciembre / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $totalEnero > 0) ? ($totalEnero / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $totalFebrero > 0) ? ($totalFebrero / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated"><strong>{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</strong></td>
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
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $totalPresupuesto = array_sum($serviciosPublicosData['meses']);
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? ($totalPresupuesto / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $junioTotal > 0) ? ($junioTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $julioTotal > 0) ? ($julioTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $agostoTotal > 0) ? ($agostoTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $septiembreTotal > 0) ? ($septiembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $octubreTotal > 0) ? ($octubreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $noviembreTotal > 0) ? ($noviembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $diciembreTotal > 0) ? ($diciembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $eneroTotal > 0) ? ($eneroTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $febreroTotal > 0) ? ($febreroTotal / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated"><strong>{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</strong></td>
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
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $totalPresupuesto = array_sum($otrosEgresosData);
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? ($totalPresupuesto / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $junioTotal > 0) ? ($junioTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $julioTotal > 0) ? ($julioTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $agostoTotal > 0) ? ($agostoTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $septiembreTotal > 0) ? ($septiembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $octubreTotal > 0) ? ($octubreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $noviembreTotal > 0) ? ($noviembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $diciembreTotal > 0) ? ($diciembreTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $eneroTotal > 0) ? ($eneroTotal / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $febreroTotal > 0) ? ($febreroTotal / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated"><strong>{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</strong></td>
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
                                                $totalAgosto = 0;
                                                $totalSeptiembre = 0;
                                                $totalOctubre = 0;
                                                $totalNoviembre = 0;
                                                $totalDiciembre = 0;
                                                $totalEnero = 0;
                                                $totalFebrero = 0;
                                                foreach($seccionesLabels as $conceptKey => $concepto) {
                                                    $totalJunio += $seccionesAcademiaData[$conceptKey . '-junio'] ?? 0;
                                                    $totalJulio += $seccionesAcademiaData[$conceptKey . '-julio'] ?? 0;
                                                    $totalAgosto += $seccionesAcademiaData[$conceptKey . '-agosto'] ?? 0;
                                                    $totalSeptiembre += $seccionesAcademiaData[$conceptKey . '-septiembre'] ?? 0;
                                                    $totalOctubre += $seccionesAcademiaData[$conceptKey . '-octubre'] ?? 0;
                                                    $totalNoviembre += $seccionesAcademiaData[$conceptKey . '-noviembre'] ?? 0;
                                                    $totalDiciembre += $seccionesAcademiaData[$conceptKey . '-diciembre'] ?? 0;
                                                    $totalEnero += $seccionesAcademiaData[$conceptKey . '-enero'] ?? 0;
                                                    $totalFebrero += $seccionesAcademiaData[$conceptKey . '-febrero'] ?? 0;
                                                }
                                            @endphp
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
                                        <tr class="impact-row">
                                            <td><strong>Impacto % frente a ingresos totales</strong></td>
                                            @php
                                                $totalIngresos = $budgetData['resumen_ingresos']['total_ingresos']['presupuesto_aprobado'] ?? 1;
                                                $totalPresupuesto = array_sum($seccionesAcademiaData);
                                                $porcentajePresupuesto = ($totalIngresos > 0) ? ($totalPresupuesto / $totalIngresos) * 100 : 0;
                                                $porcentajeJunio = ($totalIngresos > 0 && $totalJunio > 0) ? ($totalJunio / $totalIngresos) * 100 : 0;
                                                $porcentajeJulio = ($totalIngresos > 0 && $totalJulio > 0) ? ($totalJulio / $totalIngresos) * 100 : 0;
                                                $porcentajeAgosto = ($totalIngresos > 0 && $totalAgosto > 0) ? ($totalAgosto / $totalIngresos) * 100 : 0;
                                                $porcentajeSeptiembre = ($totalIngresos > 0 && $totalSeptiembre > 0) ? ($totalSeptiembre / $totalIngresos) * 100 : 0;
                                                $porcentajeOctubre = ($totalIngresos > 0 && $totalOctubre > 0) ? ($totalOctubre / $totalIngresos) * 100 : 0;
                                                $porcentajeNoviembre = ($totalIngresos > 0 && $totalNoviembre > 0) ? ($totalNoviembre / $totalIngresos) * 100 : 0;
                                                $porcentajeDiciembre = ($totalIngresos > 0 && $totalDiciembre > 0) ? ($totalDiciembre / $totalIngresos) * 100 : 0;
                                                $porcentajeEnero = ($totalIngresos > 0 && $totalEnero > 0) ? ($totalEnero / $totalIngresos) * 100 : 0;
                                                $porcentajeFebrero = ($totalIngresos > 0 && $totalFebrero > 0) ? ($totalFebrero / $totalIngresos) * 100 : 0;
                                            @endphp
                                            <td class="number-cell calculated"><strong>{{ sprintf('%.2f%%', $porcentajePresupuesto) }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJunio > 0 ? sprintf('%.2f%%', $porcentajeJunio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeJulio > 0 ? sprintf('%.2f%%', $porcentajeJulio) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeAgosto > 0 ? sprintf('%.2f%%', $porcentajeAgosto) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeSeptiembre > 0 ? sprintf('%.2f%%', $porcentajeSeptiembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeOctubre > 0 ? sprintf('%.2f%%', $porcentajeOctubre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeNoviembre > 0 ? sprintf('%.2f%%', $porcentajeNoviembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeDiciembre > 0 ? sprintf('%.2f%%', $porcentajeDiciembre) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeEnero > 0 ? sprintf('%.2f%%', $porcentajeEnero) : '-' }}</strong></td>
                                            <td class="number-cell calculated"><strong>{{ $porcentajeFebrero > 0 ? sprintf('%.2f%%', $porcentajeFebrero) : '-' }}</strong></td>
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
                                        <td><strong>Cobro Entrenadores</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell">
                                            @if($entrenamientosData['gastos']['entrenadores'][$mes] > 0)
                                                ${{ number_format($entrenamientosData['gastos']['entrenadores'][$mes]) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @endforeach
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['gastos']['entrenadores']['total']) }}</strong></td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Total Gastos</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['totales_mensuales'][$mes]['gastos']) }}</strong></td>
                                        @endforeach
                                        <td class="number-cell"><strong>${{ number_format($entrenamientosData['resumen']['total_gastos']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tabla de Resultados -->
                        <div class="table-container">
                            <h3 class="mb-3">📊 (DÉFICIT) / UTILIDAD</h3>
                            <table class="budget-table">
                                <thead>
                                    <tr>
                                        <th>Resultado</th>
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
                                    <tr class="total-row">
                                        <td><strong>(Déficit) / Utilidad</strong></td>
                                        @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                        <td class="number-cell">
                                            <strong class="{{ $entrenamientosData['totales_mensuales'][$mes]['resultado'] < 0 ? 'text-danger' : 'text-success' }}">
                                                @if($entrenamientosData['totales_mensuales'][$mes]['resultado'] < 0)
                                                    (${{ number_format(abs($entrenamientosData['totales_mensuales'][$mes]['resultado'])) }})
                                                @else
                                                    ${{ number_format($entrenamientosData['totales_mensuales'][$mes]['resultado']) }}
                                                @endif
                                            </strong>
                                        </td>
                                        @endforeach
                                        <td class="number-cell">
                                            <strong class="{{ $entrenamientosData['resumen']['deficit_utilidad'] < 0 ? 'text-danger' : 'text-success' }}">
                                                @if($entrenamientosData['resumen']['deficit_utilidad'] < 0)
                                                    (${{ number_format(abs($entrenamientosData['resumen']['deficit_utilidad'])) }})
                                                @else
                                                    ${{ number_format($entrenamientosData['resumen']['deficit_utilidad']) }}
                                                @endif
                                            </strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalle Mensual con Cards -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📅 Resumen Mensual</h3>
                            <div class="row">
                                @foreach(['septiembre', 'octubre', 'noviembre', 'febrero'] as $mes)
                                    @if($entrenamientosData['ingresos'][$mes]['valor'] > 0 || $entrenamientosData['totales_mensuales'][$mes]['gastos'] > 0)
                                        <div class="col-lg-3 col-md-6 mb-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header {{ $entrenamientosData['totales_mensuales'][$mes]['resultado'] >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }} text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-calendar-alt me-2"></i>
                                                        {{ ucfirst($mes) }} 2024
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-users text-info me-2"></i>
                                                            Estudiantes
                                                        </span>
                                                        <span class="detail-amount badge bg-info">{{ $entrenamientosData['ingresos'][$mes]['estudiantes'] }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-dollar-sign text-success me-2"></i>
                                                            Ingresos
                                                        </span>
                                                        <span class="detail-amount badge bg-success">${{ number_format($entrenamientosData['ingresos'][$mes]['valor']) }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-3">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-credit-card text-warning me-2"></i>
                                                            Gastos
                                                        </span>
                                                        <span class="detail-amount badge bg-warning">${{ number_format($entrenamientosData['totales_mensuales'][$mes]['gastos']) }}</span>
                                                    </div>
                                                </div>
                                                <div class="card-footer {{ $entrenamientosData['totales_mensuales'][$mes]['resultado'] >= 0 ? 'bg-light' : 'bg-light' }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-primary">{{ $entrenamientosData['totales_mensuales'][$mes]['resultado'] >= 0 ? 'Utilidad:' : 'Déficit:' }}</strong>
                                                        <strong class="{{ $entrenamientosData['totales_mensuales'][$mes]['resultado'] >= 0 ? 'text-success' : 'text-danger' }} h5 mb-0">
                                                            @if($entrenamientosData['totales_mensuales'][$mes]['resultado'] < 0)
                                                                (${{ number_format(abs($entrenamientosData['totales_mensuales'][$mes]['resultado'])) }})
                                                            @else
                                                                ${{ number_format($entrenamientosData['totales_mensuales'][$mes]['resultado']) }}
                                                            @endif
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Servicios Publicos')
                    <!-- SERVICIOS PÚBLICOS Y ARRENDAMIENTOS -->
                    <div id="sheet-servicios-publicos" class="sheet-section">
                        <h2 class="section-title">🏢 SERVICIOS PÚBLICOS Y ARRENDAMIENTOS</h2>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($serviciosPublicosData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($serviciosPublicosData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($serviciosPublicosData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Por Ejecutar</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">{{ $serviciosPublicosData['resumen']['porcentaje_restante'] }}%</div>
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
                                    @foreach($serviciosPublicosData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_ejecutar']) }}</td>
                                        <td class="number-cell {{ $concepto['porcentaje_restante'] < 0 ? 'text-danger' : '' }}">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'AGUA')
                                                ${{ number_format(143558) }}
                                            @elseif($concepto['concepto'] == 'LUZ')
                                                ${{ number_format(6016849) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - ETB')
                                                ${{ number_format(1599660) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - CORPORATIVO')
                                                ${{ number_format(837904) }}
                                            @elseif($concepto['concepto'] == 'VIGILANCIA - Metros cuadrados')
                                                ${{ number_format(11665732) }}
                                            @elseif($concepto['concepto'] == 'INTERNET IFX')
                                                ${{ number_format(10445750) }}
                                            @elseif($concepto['concepto'] == 'Phidias')
                                                ${{ number_format(1847670) }}
                                            @elseif($concepto['concepto'] == 'Zeus Nomina/contabilidad/activos fijos')
                                                ${{ number_format(2669765) }}
                                            @elseif($concepto['concepto'] == 'Credibanco')
                                                ${{ number_format(53767) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'AGUA')
                                                ${{ number_format(298596) }}
                                            @elseif($concepto['concepto'] == 'LUZ')
                                                ${{ number_format(14485034) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - ETB')
                                                ${{ number_format(1588800) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - CORPORATIVO')
                                                ${{ number_format(837904) }}
                                            @elseif($concepto['concepto'] == 'VIGILANCIA - Metros cuadrados')
                                                ${{ number_format(11716836) }}
                                            @elseif($concepto['concepto'] == 'INTERNET IFX')
                                                ${{ number_format(8308009) }}
                                            @elseif($concepto['concepto'] == 'Phidias')
                                                ${{ number_format(1847670) }}
                                            @elseif($concepto['concepto'] == 'Zeus Nomina/contabilidad/activos fijos')
                                                ${{ number_format(2669765) }}
                                            @elseif($concepto['concepto'] == 'Credibanco')
                                                ${{ number_format(112809) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'AGUA')
                                                ${{ number_format(148049) }}
                                            @elseif($concepto['concepto'] == 'LUZ')
                                                ${{ number_format(8451133) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - ETB')
                                                ${{ number_format(1584035) }}
                                            @elseif($concepto['concepto'] == 'TELEFONO - CORPORATIVO')
                                                ${{ number_format(964265) }}
                                            @elseif($concepto['concepto'] == 'VIGILANCIA - Metros cuadrados')
                                                ${{ number_format(11716836) }}
                                            @elseif($concepto['concepto'] == 'INTERNET IFX')
                                                ${{ number_format(8308009) }}
                                            @elseif($concepto['concepto'] == 'Phidias')
                                                ${{ number_format(1847670) }}
                                            @elseif($concepto['concepto'] == 'Zeus Nomina/contabilidad/activos fijos')
                                                ${{ number_format(2669765) }}
                                            @elseif($concepto['concepto'] == 'Credibanco')
                                                ${{ number_format(112809) }}
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
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                        <td class="number-cell">$0</td>
                                            <td class="number-cell editable">$-</td>
                                    </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['resumen']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['resumen']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['resumen']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $serviciosPublicosData['resumen']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['marzo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['abril']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['mayo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($serviciosPublicosData['meses']['junio']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalle Mensual con Cards -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📅 Distribución de Gastos Principales</h3>
                            <div class="row">
                                @foreach(['julio', 'agosto', 'septiembre'] as $mes)
                                    @if($serviciosPublicosData['meses'][$mes] > 0)
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-gradient-secondary text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-calendar-alt me-2"></i>
                                                        {{ ucfirst($mes) }} 2024
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-2">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-tint text-primary me-2"></i>
                                                            Agua
                                                        </span>
                                                        <span class="detail-amount badge bg-primary">${{ number_format($serviciosPublicosData['detalle_meses'][$mes]['agua']) }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-2">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-bolt text-warning me-2"></i>
                                                            Luz
                                                        </span>
                                                        <span class="detail-amount badge bg-warning">${{ number_format($serviciosPublicosData['detalle_meses'][$mes]['luz']) }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-2">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-phone text-info me-2"></i>
                                                            Teléfonos
                                                        </span>
                                                        <span class="detail-amount badge bg-info">${{ number_format($serviciosPublicosData['detalle_meses'][$mes]['telefono_etb'] + $serviciosPublicosData['detalle_meses'][$mes]['telefono_corp']) }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-2">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-shield-alt text-success me-2"></i>
                                                            Vigilancia
                                                        </span>
                                                        <span class="detail-amount badge bg-success">${{ number_format($serviciosPublicosData['detalle_meses'][$mes]['vigilancia']) }}</span>
                                                    </div>
                                                    <div class="detail-item d-flex justify-content-between align-items-center mb-2">
                                                        <span class="detail-concept">
                                                            <i class="fas fa-wifi text-secondary me-2"></i>
                                                            Internet
                                                        </span>
                                                        <span class="detail-amount badge bg-secondary">${{ number_format($serviciosPublicosData['detalle_meses'][$mes]['internet']) }}</span>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-primary">Total del Mes:</strong>
                                                        <strong class="text-dark h5 mb-0">${{ number_format($serviciosPublicosData['meses'][$mes]) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <!-- Sección de Categorías de Gastos -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0">🏠 Servicios Básicos</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>💧 Agua</span>
                                                <span class="badge bg-primary">${{ number_format(4087293) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>⚡ Energía Eléctrica</span>
                                                <span class="badge bg-warning">${{ number_format(62345219) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>📞 Telefonía</span>
                                                <span class="badge bg-info">${{ number_format(17275774) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between">
                                                <span>🛜 Internet</span>
                                                <span class="badge bg-secondary">${{ number_format(60230819) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">🔒 Servicios Especializados</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>👮 Vigilancia</span>
                                                <span class="badge bg-success">${{ number_format(102627572) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>💻 Software Phidias</span>
                                                <span class="badge bg-primary">${{ number_format(14983787) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between mb-2">
                                                <span>📊 Software Zeus</span>
                                                <span class="badge bg-warning">${{ number_format(18966011) }}</span>
                                            </div>
                                            <div class="service-item d-flex justify-content-between">
                                                <span>🧾 Otros Servicios</span>
                                                <span class="badge bg-secondary">${{ number_format(1607095) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Reparaciones Mayores')
                    <!-- REPARACIONES MAYORES -->
                    <div id="sheet-reparaciones-mayores" class="sheet-section">
                        <h2 class="section-title">🔧 REPARACIONES MAYORES - MANTENIMIENTO</h2>
                        
                        <!-- Alerta de Sobreejecución -->
                        <div class="alert alert-warning mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3 fa-2x"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">⚠️ Sobreejecución Presupuestal Detectada</h5>
                                    <p class="mb-0">Este rubro presenta una sobreejecución del <strong>52%</strong> ($90,230,748) sobre el presupuesto aprobado. Se requiere análisis y control inmediato.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($reparacionesMayoresData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">${{ number_format($reparacionesMayoresData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">(${{ number_format(abs($reparacionesMayoresData['resumen']['presupuesto_ejecutar'])) }})</div>
                                        <div class="summary-label">Sobreejecución</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">{{ $reparacionesMayoresData['resumen']['porcentaje_restante'] }}%</div>
                                        <div class="summary-label">% Sobreejecución</div>
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
                                        <th>Sobreejecución</th>
                                        <th>%Sobreejecución</th>
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
                                    @foreach($reparacionesMayoresData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell text-danger">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell text-danger">(${{ number_format(abs($concepto['ppto_ejecutar'])) }})</td>
                                        <td class="number-cell text-danger">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['noviembre']) }}</td>
                                        <td class="number-cell {{ $reparacionesMayoresData['meses']['diciembre'] < 0 ? 'text-success' : '' }}">
                                            @if($reparacionesMayoresData['meses']['diciembre'] < 0)
                                                (${{ number_format(abs($reparacionesMayoresData['meses']['diciembre'])) }})
                                            @else
                                                ${{ number_format($reparacionesMayoresData['meses']['diciembre']) }}
                                            @endif
                                        </td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionesMayoresData['meses']['febrero']) }}</td>
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
                                </tbody>
                            </table>
                        </div>

                        <!-- Análisis de Gastos por Categoría -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📊 Análisis de Gastos por Categoría</h3>
                            <div class="row">
                                @foreach($reparacionesMayoresData['categorias_gastos'] as $categoria)
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-header bg-gradient-warning text-dark">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-tools me-2"></i>
                                                    {{ $categoria['nombre'] }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <h4 class="text-warning">${{ number_format($categoria['total']) }}</h4>
                                                    <p class="text-muted small">{{ $categoria['descripcion'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Detalle de Proveedores Principales -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">🛠️ Septiembre - Principales Proveedores</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach(array_slice($reparacionesMayoresData['detalle_septiembre'], 0, 8, true) as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 70%;">{{ $proveedor }}</span>
                                            <span class="badge bg-primary">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Septiembre:</strong>
                                            <strong class="text-primary">${{ number_format($reparacionesMayoresData['meses']['septiembre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">🔧 Octubre - Principales Proveedores</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach(array_slice($reparacionesMayoresData['detalle_octubre'], 0, 8, true) as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 70%;">{{ $proveedor }}</span>
                                            <span class="badge {{ $valor < 0 ? 'bg-danger' : 'bg-success' }}">
                                                @if($valor < 0)
                                                    (${{ number_format(abs($valor)) }})
                                                @else
                                                    ${{ number_format($valor) }}
                                                @endif
                                            </span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Octubre:</strong>
                                            <strong class="text-success">${{ number_format($reparacionesMayoresData['meses']['octubre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Evolución Mensual -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">📈 Evolución Mensual de Gastos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                                @if($reparacionesMayoresData['meses'][$mes] != 0)
                                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                                    <div class="month-stat text-center">
                                                        <div class="month-name text-uppercase text-muted small">{{ $mes }}</div>
                                                        <div class="month-value {{ $reparacionesMayoresData['meses'][$mes] < 0 ? 'text-success' : ($reparacionesMayoresData['meses'][$mes] > 50000000 ? 'text-danger' : 'text-warning') }}">
                                                            @if($reparacionesMayoresData['meses'][$mes] < 0)
                                                                (${{ number_format(abs($reparacionesMayoresData['meses'][$mes])) }})
                                                            @else
                                                                ${{ number_format($reparacionesMayoresData['meses'][$mes]) }}
                                                            @endif
                                                        </div>
                                                        <div class="progress mt-2" style="height: 8px;">
                                                            <div class="progress-bar {{ $reparacionesMayoresData['meses'][$mes] < 0 ? 'bg-success' : ($reparacionesMayoresData['meses'][$mes] > 50000000 ? 'bg-danger' : 'bg-warning') }}" 
                                                                 style="width: {{ min(100, abs($reparacionesMayoresData['meses'][$mes]) / 1200000) }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Honorarios')
                    <!-- HONORARIOS -->
                    <div id="sheet-honorarios" class="sheet-section">
                        <h2 class="section-title">💼 HONORARIOS</h2>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($honorariosData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-info">${{ number_format($honorariosData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-success">${{ number_format($honorariosData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Por Ejecutar</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-success">{{ $honorariosData['resumen']['porcentaje_restante'] }}%</div>
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
                                        <th>Ppto a Ejecutar</th>
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
                                    @foreach($honorariosData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell text-info">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell {{ $concepto['porcentaje_restante'] > 50 ? 'text-success' : ($concepto['porcentaje_restante'] > 20 ? 'text-warning' : 'text-danger') }}">
                                            ${{ number_format($concepto['ppto_ejecutar']) }}
                                        </td>
                                        <td class="number-cell {{ $concepto['porcentaje_restante'] > 50 ? 'text-success' : ($concepto['porcentaje_restante'] > 20 ? 'text-warning' : 'text-danger') }}">
                                            {{ $concepto['porcentaje_restante'] }}%
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Julio']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Julio']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Julio']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(2379906) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Agosto']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Agosto']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Agosto']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(2379906) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Septiembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Septiembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Septiembre']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(5045401) }}
                                            @elseif($concepto['concepto'] == 'Otras Asesorias')
                                                ${{ number_format(1168500) }}
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Octubre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Octubre']) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Noviembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Noviembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Noviembre']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(5634809) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Diciembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Diciembre']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Diciembre']) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Enero']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Enero']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Enero']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(2665495) }}
                                            @else
                                                $0
                                            @endif
                                        </td>
                                        <td class="number-cell">
                                            @if($concepto['concepto'] == 'Honorarios Financiera')
                                                ${{ number_format($honorariosData['detalle_financiera']['Febrero']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Astaff')
                                                ${{ number_format($honorariosData['detalle_astaff']['Febrero']) }}
                                            @elseif($concepto['concepto'] == 'Honorarios Morand')
                                                ${{ number_format($honorariosData['detalle_morand']['Febrero']) }}
                                            @elseif($concepto['concepto'] == 'Mary Hayes')
                                                ${{ number_format(5330990) }}
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
                                    </tr>
                                    @endforeach
                                    <!-- Fila Total -->
                                    <tr class="table-total">
                                        <td><strong>Total</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['resumen']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['resumen']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['resumen']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $honorariosData['resumen']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($honorariosData['meses']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>$0</strong></td>
                                        <td class="number-cell"><strong>$0</strong></td>
                                        <td class="number-cell"><strong>$0</strong></td>
                                        <td class="number-cell"><strong>$0</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Análisis de Honorarios por Categoría -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">👥 Análisis de Honorarios por Categoría</h3>
                            <div class="row">
                                @foreach($honorariosData['categorias_gastos'] as $categoria)
                                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-header bg-gradient-secondary text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-user-tie me-2"></i>
                                                    {{ $categoria['nombre'] }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <h5 class="text-secondary">${{ number_format($categoria['total']) }}</h5>
                                                    <p class="text-muted small">{{ $categoria['descripcion'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gráfico de Evolución Mensual -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">📈 Evolución Mensual de Honorarios</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                                @if($honorariosData['meses'][$mes] != 0)
                                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                                    <div class="month-stat text-center">
                                                        <div class="month-name text-uppercase text-muted small">{{ $mes }}</div>
                                                        <div class="month-value {{ $honorariosData['meses'][$mes] > 15000000 ? 'text-primary' : ($honorariosData['meses'][$mes] > 10000000 ? 'text-warning' : 'text-secondary') }}">
                                                            ${{ number_format($honorariosData['meses'][$mes]) }}
                                                        </div>
                                                        <div class="progress mt-2" style="height: 8px;">
                                                            <div class="progress-bar {{ $honorariosData['meses'][$mes] > 15000000 ? 'bg-primary' : ($honorariosData['meses'][$mes] > 10000000 ? 'bg-warning' : 'bg-secondary') }}" 
                                                                 style="width: {{ min(100, $honorariosData['meses'][$mes] / 200000) }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalle por Consultor -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">💰 Honorarios Financiera</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h4 class="text-primary">${{ number_format(35558213) }}</h4>
                                            <span class="badge bg-danger">14% restante</span>
                                        </div>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-primary" style="width: 86%"></div>
                                        </div>
                                        <small class="text-muted">Ejecutado: ${{ number_format(30638210) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">👥 Honorarios Astaff</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h4 class="text-info">${{ number_format(43699942) }}</h4>
                                            <span class="badge bg-warning">33% restante</span>
                                        </div>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-info" style="width: 67%"></div>
                                        </div>
                                        <small class="text-muted">Ejecutado: ${{ number_format(29433460) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">🎯 Otras Asesorías</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h4 class="text-success">${{ number_format(20674135) }}</h4>
                                            <span class="badge bg-success">94% disponible</span>
                                        </div>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: 6%"></div>
                                        </div>
                                        <small class="text-muted">Ejecutado: ${{ number_format(1168500) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Mercadeo')
                    <!-- MERCADEO -->
                    <div id="sheet-mercadeo" class="sheet-section">
                        <h2 class="section-title">📢 MERCADEO</h2>
                        
                        <!-- Alerta de Estado Presupuestal -->
                        <div class="alert alert-warning mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line me-3 fa-2x"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">⚠️ Presupuesto Próximo al Límite</h5>
                                    <p class="mb-0">Se ha ejecutado el <strong>93%</strong> del presupuesto. Solo queda <strong>$5.255.060</strong> disponible para el resto del año escolar.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($mercadeoData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-warning">${{ number_format($mercadeoData['resumen']['ejecutado']) }}</div>
                                        <div class="summary-label">Ejecutado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">${{ number_format($mercadeoData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Por Ejecutar</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-danger">{{ $mercadeoData['resumen']['porcentaje_restante'] }}%</div>
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
                                        <th>Ppto a Ejecutar</th>
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
                                    @foreach($mercadeoData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell text-warning">${{ number_format($concepto['ejecutado']) }}</td>
                                        <td class="number-cell text-danger">${{ number_format($concepto['ppto_ejecutar']) }}</td>
                                        <td class="number-cell text-danger">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['diciembre']) }}</td>
                                        <td class="number-cell text-primary">${{ number_format($mercadeoData['meses']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($mercadeoData['meses']['febrero']) }}</td>
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
                                </tbody>
                            </table>
                        </div>

                        <!-- Análisis de Gastos por Categoría -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">📊 Análisis de Gastos por Categoría</h3>
                            <div class="row">
                                @foreach($mercadeoData['categorias_gastos'] as $categoria)
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-header bg-gradient-info text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-bullhorn me-2"></i>
                                                    {{ $categoria['nombre'] }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <h4 class="text-info">${{ number_format($categoria['total']) }}</h4>
                                                    <p class="text-muted small">{{ $categoria['descripcion'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Detalle de Proveedores por Mes -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">🗓️ Septiembre - Principales Proveedores</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach(array_slice($mercadeoData['detalle_septiembre'], 0, 7, true) as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-success">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Septiembre:</strong>
                                            <strong class="text-success">${{ number_format($mercadeoData['meses']['septiembre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0">🗓️ Octubre - Principales Proveedores</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach(array_slice($mercadeoData['detalle_octubre'], 0, 7, true) as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-warning text-dark">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Octubre:</strong>
                                            <strong class="text-warning">${{ number_format($mercadeoData['meses']['octubre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Más Detalles por Mes -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">🗓️ Noviembre</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($mercadeoData['detalle_noviembre'] as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-info">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong class="text-info">${{ number_format($mercadeoData['meses']['noviembre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0">🗓️ Diciembre</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($mercadeoData['detalle_diciembre'] as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-secondary">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong class="text-secondary">${{ number_format($mercadeoData['meses']['diciembre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">🗓️ Enero - Mayor Inversión</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($mercadeoData['detalle_enero'] as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-primary">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong class="text-primary">${{ number_format($mercadeoData['meses']['enero']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Evolución Mensual -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">📈 Evolución Mensual de Mercadeo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(['julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre', 'enero', 'febrero'] as $mes)
                                                @if($mercadeoData['meses'][$mes] != 0)
                                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                                    <div class="month-stat text-center">
                                                        <div class="month-name text-uppercase text-muted small">{{ $mes }}</div>
                                                        <div class="month-value {{ $mercadeoData['meses'][$mes] > 15000000 ? 'text-primary' : ($mercadeoData['meses'][$mes] > 10000000 ? 'text-warning' : 'text-info') }}">
                                                            ${{ number_format($mercadeoData['meses'][$mes]) }}
                                                        </div>
                                                        <div class="progress mt-2" style="height: 8px;">
                                                            <div class="progress-bar {{ $mercadeoData['meses'][$mes] > 15000000 ? 'bg-primary' : ($mercadeoData['meses'][$mes] > 10000000 ? 'bg-warning' : 'bg-info') }}" 
                                                                 style="width: {{ min(100, $mercadeoData['meses'][$mes] / 200000) }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Reparacion muebles')
                    <!-- REPARACIÓN DE MUEBLES -->
                    <div id="sheet-reparacion-muebles" class="sheet-section">
                        <h2 class="section-title">🪑 REPARACIÓN DE MUEBLES</h2>
                        
                        <!-- Nota Informativa -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3 fa-2x"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">💡 Estado del Presupuesto</h5>
                                    <p class="mb-0">Este rubro presenta un <strong>crédito neto</strong> de $1,064,523, dejando <strong>107%</strong> del presupuesto disponible para uso futuro.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="summary-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value">${{ number_format($reparacionMueblesData['resumen']['presupuesto_aprobado']) }}</div>
                                        <div class="summary-label">Presupuesto Aprobado</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-success">(${{ number_format(abs($reparacionMueblesData['resumen']['ejecutado'])) }})</div>
                                        <div class="summary-label">Crédito Neto</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-primary">${{ number_format($reparacionMueblesData['resumen']['presupuesto_ejecutar']) }}</div>
                                        <div class="summary-label">Disponible</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-card">
                                        <div class="summary-value text-success">{{ $reparacionMueblesData['resumen']['porcentaje_restante'] }}%</div>
                                        <div class="summary-label">% Disponible</div>
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
                                        <th>Ppto a Ejecutar</th>
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
                                    @foreach($reparacionMueblesData['conceptos'] as $concepto)
                                    <tr>
                                        <td><strong>{{ $concepto['concepto'] }}</strong></td>
                                        <td class="number-cell">${{ number_format($concepto['ppto_aprobado']) }}</td>
                                        <td class="number-cell text-success">(${{ number_format(abs($concepto['ejecutado'])) }})</td>
                                        <td class="number-cell text-primary">${{ number_format($concepto['ppto_ejecutar']) }}</td>
                                        <td class="number-cell text-success">{{ $concepto['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['noviembre']) }}</td>
                                        <td class="number-cell text-success">(${{ number_format(abs($reparacionMueblesData['meses']['diciembre'])) }})</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($reparacionMueblesData['meses']['febrero']) }}</td>
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
                                </tbody>
                            </table>
                        </div>

                        <!-- Análisis de Gastos por Categoría -->
                        <div class="monthly-details-section mt-5 mb-4">
                            <h3 class="mb-4">🪑 Análisis de Mobiliario por Categoría</h3>
                            <div class="row">
                                @foreach($reparacionMueblesData['categorias_gastos'] as $categoria)
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-header bg-gradient-primary text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-chair me-2"></i>
                                                    {{ $categoria['nombre'] }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <h4 class="text-primary">${{ number_format($categoria['total']) }}</h4>
                                                    <p class="text-muted small">{{ $categoria['descripcion'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Detalle de Movimientos por Mes -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0">🔨 Noviembre - Reparaciones</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($reparacionMueblesData['detalle_noviembre'] as $proveedor => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $proveedor }}</span>
                                            <span class="badge bg-warning text-dark">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Noviembre:</strong>
                                            <strong class="text-warning">${{ number_format($reparacionMueblesData['meses']['noviembre']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">💰 Diciembre - Créditos</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($reparacionMueblesData['detalle_diciembre'] as $concepto => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $concepto }}</span>
                                            <span class="badge bg-success">(${{ number_format(abs($valor)) }})</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Diciembre:</strong>
                                            <strong class="text-success">(${{ number_format(abs($reparacionMueblesData['meses']['diciembre'])) }})</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">🔄 Enero - Renovaciones</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($reparacionMueblesData['detalle_enero'] as $actividad => $valor)
                                        <div class="provider-item d-flex justify-content-between mb-2">
                                            <span class="text-truncate me-2" style="max-width: 65%;">{{ $actividad }}</span>
                                            <span class="badge bg-info">${{ number_format($valor) }}</span>
                                        </div>
                                        @endforeach
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Enero:</strong>
                                            <strong class="text-info">${{ number_format($reparacionMueblesData['meses']['enero']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Evolución Mensual -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">📈 Evolución Mensual - Reparación de Muebles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(['agosto', 'septiembre', 'noviembre', 'diciembre', 'enero'] as $mes)
                                                @if($reparacionMueblesData['meses'][$mes] != 0)
                                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                                    <div class="month-stat text-center">
                                                        <div class="month-name text-uppercase text-muted small">{{ $mes }}</div>
                                                        <div class="month-value {{ $reparacionMueblesData['meses'][$mes] < 0 ? 'text-success' : 'text-primary' }}">
                                                            @if($reparacionMueblesData['meses'][$mes] < 0)
                                                                (${{ number_format(abs($reparacionMueblesData['meses'][$mes])) }})
                                                            @else
                                                                ${{ number_format($reparacionMueblesData['meses'][$mes]) }}
                                                            @endif
                                                        </div>
                                                        <div class="progress mt-2" style="height: 8px;">
                                                            <div class="progress-bar {{ $reparacionMueblesData['meses'][$mes] < 0 ? 'bg-success' : 'bg-primary' }}" 
                                                                 style="width: {{ min(100, abs($reparacionMueblesData['meses'][$mes]) / 100000) }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                @elseif($sheetKey == 'Gts Contrat')
                    <!-- GASTOS DE CONTRATACIÓN -->
                    <div class="gastos-contratos-container">
                        <h2 class="section-title">💼 GASTOS DE CONTRATACIÓN</h2>
                        
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
                                        <td><strong>Visitas Domiciliarias</strong></td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['visitas_domiciliarias']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['visitas_domiciliarias']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['visitas_domiciliarias']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $gastosContratosData['resumen']['visitas_domiciliarias']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['visitas_domiciliarias']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Computrabajo</strong></td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['computrabajo']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['computrabajo']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['computrabajo']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $gastosContratosData['resumen']['computrabajo']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['computrabajo']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Anuncio Periódico</strong></td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['anuncio_periodico']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['anuncio_periodico']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['anuncio_periodico']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $gastosContratosData['resumen']['anuncio_periodico']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['anuncio_periodico']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Docentes Extranjeros</strong></td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['docentes_extranjeros']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['docentes_extranjeros']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['docentes_extranjeros']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $gastosContratosData['resumen']['docentes_extranjeros']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['docentes_extranjeros']['junio']) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pruebas Psicología</strong></td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['pruebas_psicologia']['presupuesto_aprobado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['pruebas_psicologia']['ejecutado']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['resumen']['pruebas_psicologia']['presupuesto_ejecutar']) }}</td>
                                        <td class="number-cell">{{ $gastosContratosData['resumen']['pruebas_psicologia']['porcentaje_restante'] }}%</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['julio']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['agosto']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['septiembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['octubre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['noviembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['diciembre']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['enero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['febrero']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['marzo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['abril']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['mayo']) }}</td>
                                        <td class="number-cell">${{ number_format($gastosContratosData['ejecucion_mensual']['pruebas_psicologia']['junio']) }}</td>
                                    </tr>
                                    <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['resumen']['total']['presupuesto_aprobado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['resumen']['total']['ejecutado']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['resumen']['total']['presupuesto_ejecutar']) }}</strong></td>
                                        <td class="number-cell"><strong>{{ $gastosContratosData['resumen']['total']['porcentaje_restante'] }}%</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['julio']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['agosto']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['septiembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['octubre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['noviembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['diciembre']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['enero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['febrero']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['marzo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['abril']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['mayo']) }}</strong></td>
                                        <td class="number-cell"><strong>${{ number_format($gastosContratosData['ejecucion_mensual']['total']['junio']) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles por Mes -->
                        <div class="row mt-4">
                            @php
                                $mesesDetalle = [
                                    'julio' => 'Julio 2024',
                                    'agosto' => 'Agosto 2024', 
                                    'septiembre' => 'Septiembre 2024',
                                    'octubre' => 'Octubre 2024',
                                    'noviembre' => 'Noviembre 2024'
                                ];
                            @endphp

                            @foreach($mesesDetalle as $mes => $nombreMes)
                                @if(isset($gastosContratosData['detalle_' . $mes]) && count($gastosContratosData['detalle_' . $mes]) > 0)
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
                                                            @foreach($gastosContratosData['detalle_' . $mes] as $item)
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
                                                    $totalMes = collect($gastosContratosData['detalle_' . $mes])->sum('valor');
                                                @endphp
                                                <strong>Total: ${{ number_format($totalMes) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                @else
                    <!-- Otras hojas - SIN DATOS POR AHORA -->
                    <div class="table-wrapper">
                        <div class="empty-sheet">
                            <div class="empty-icon">📊</div>
                            <h3>Hoja sin configurar</h3>
                            <p>Esta hoja de presupuesto aún no tiene datos configurados.</p>
                            <p class="sheet-key">Clave: <strong>{{ $sheetKey }}</strong></p>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Modal para editar datos -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Registro</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <div class="form-group">
                    <label>Sección:</label>
                    <input type="text" id="editSeccion" name="seccion">
                </div>
                <div class="form-group">
                    <label>Rubro:</label>
                    <input type="text" id="editRubro" name="rubro">
                </div>
                <div class="form-group">
                    <label>Presupuestado:</label>
                    <input type="number" id="editPresupuestado" name="presupuestado">
                </div>
                <div class="form-group">
                    <label>Ejecutado:</label>
                    <input type="number" id="editEjecutado" name="ejecutado">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save">Guardar</button>
                    <button type="button" class="btn-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- Meta tag para CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
/* Estilos específicos para el contenido de la página */
.main-container * {
    box-sizing: border-box;
}

.main-container {
    padding: 20px;
    font-family: Arial, sans-serif;
}

/* Estilos para valores clickeables */
.valor-clickeable {
    cursor: pointer !important;
    color: #007bff !important;
    text-decoration: underline !important;
    transition: all 0.2s ease;
}

.valor-clickeable:hover {
    background-color: #f8f9fa !important;
    color: #0056b3 !important;
    font-weight: bold !important;
}

/* Estilos para el modal de detalles */
.info-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid #007bff;
}

.info-title {
    color: #007bff;
    margin-bottom: 15px;
    font-weight: bold;
    font-size: 14px;
}

.info-item {
    margin-bottom: 10px;
    font-size: 13px;
}

.info-item strong {
    color: #495057;
    display: inline-block;
    min-width: 120px;
}

.info-item span {
    color: #212529;
}

.info-item p {
    margin-bottom: 0;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    color: #495057;
}

.modal-dialog.modal-lg {
    max-width: 800px;
}

.modal-header.bg-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Estilos para modal personalizado */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    cursor: pointer;
}

.custom-modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10000;
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.custom-modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.custom-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.custom-close-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.custom-modal-body {
    padding: 20px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 0 0 8px 8px;
    text-align: right;
}

/* Header de página */
.main-container .page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.main-container .page-header h1 {
    color: #333;
    margin: 0 0 5px 0;
    font-size: 28px;
}

.main-container .page-header p {
    color: #666;
    margin: 0;
    font-size: 14px;
}

/* Cajas de estadísticas */
.stats-container {
    display: flex;
    gap: 15px;
    margin: 20px 0 30px 0;
    flex-wrap: wrap;
}

.stat-box {
    background: white;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
    min-width: 200px;
    flex: 1;
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.stat-box-primary {
    border-left-color: #007bff;
}

.stat-box-success {
    border-left-color: #28a745;
}

.stat-box-warning {
    border-left-color: #ffc107;
}

.stat-box-info {
    border-left-color: #17a2b8;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
    color: white;
}

.stat-box-primary .stat-icon {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.stat-box-success .stat-icon {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.stat-box-warning .stat-icon {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.stat-box-info .stat-icon {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.stat-sublabel {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
    font-style: italic;
    font-weight: 400;
}

/* Responsive para cajas de estadísticas */
@media (max-width: 768px) {
    .stats-container {
        flex-direction: column;
    }
    
    .stat-box {
        min-width: auto;
    }
    
    .stat-number {
        font-size: 20px;
    }
}

/* Filtros y controles - Diseño compacto */
.filters-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 12px 20px;
    border-radius: 12px;
    margin: 15px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.filter-left {
    display: flex;
    align-items: center;
}

.filter-right {
    display: flex;
    align-items: center;
}

.filter-group-compact {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 8px 15px;
    border-radius: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.filter-icon {
    color: #6c757d;
    font-size: 14px;
}

.filter-select-compact {
    background: transparent;
    border: none;
    color: #495057;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    outline: none;
    min-width: 180px;
    padding: 0;
}

.filter-select-compact option {
    background: white;
    color: #495057;
    padding: 8px 12px;
}

.filter-select-compact:focus {
    outline: none;
}

.records-info-compact {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 8px 15px;
    border-radius: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.info-icon {
    color: #6c757d;
    font-size: 12px;
}

.records-text {
    color: #495057;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.3px;
}

.records-text span {
    font-weight: 600;
    color: #343a40;
}

/* Botón cargar más */
.load-more-container {
    text-align: center;
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    margin-top: 20px;
}

.btn-load-more {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
}

.btn-load-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 123, 255, 0.4);
}

.btn-load-more:active {
    transform: translateY(0);
}

.loading-indicator {
    color: #007bff;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Animación para el botón cargar más */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@media (max-width: 768px) {
    .filters-container {
        flex-direction: column;
        gap: 12px;
        padding: 15px;
    }
    
    .filter-left,
    .filter-right {
        justify-content: center;
    }
    
    .filter-group-compact,
    .records-info-compact {
        width: 100%;
        justify-content: center;
        min-width: unset;
    }
    
    .filter-select-compact {
        min-width: 150px;
        text-align: center;
    }
}

/* Menú Hamburguesa con Dropdown */
.main-container .hamburger-menu {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px 0;
    border-bottom: 2px solid #e0e0e0;
    gap: 15px;
    position: relative;
}

.main-container .menu-dropdown-container {
    position: relative;
    display: inline-block;
}

.main-container .hamburger-btn {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 32px;
    height: 26px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    transition: all 0.3s ease;
    border-radius: 4px;
}

.main-container .hamburger-btn:hover {
    background: #f0f0f0;
}

.main-container .hamburger-btn.active {
    background: #007bff;
}

.main-container .hamburger-line {
    width: 100%;
    height: 3px;
    background-color: #333;
    border-radius: 2px;
    transition: all 0.3s ease;
    transform-origin: center;
}

.main-container .hamburger-btn.active .hamburger-line {
    background-color: white;
}

.main-container .hamburger-btn.active .hamburger-line:nth-child(1) {
    transform: rotate(45deg) translate(3px, 3px);
}

.main-container .hamburger-btn.active .hamburger-line:nth-child(2) {
    opacity: 0;
}

.main-container .hamburger-btn.active .hamburger-line:nth-child(3) {
    transform: rotate(-45deg) translate(3px, -3px);
}

.main-container .current-sheet-title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

/* Dropdown Menu */
.main-container .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 400px;
    max-width: 500px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
    max-height: 70vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.main-container .dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.main-container .dropdown-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    border-radius: 8px 8px 0 0;
}

.main-container .dropdown-header span {
    font-weight: bold;
    color: #333;
    font-size: 16px;
}

.main-container .dropdown-content {
    max-height: calc(70vh - 60px);
    overflow-y: auto;
    padding: 5px 0;
}

.main-container .dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f5f5f5;
}

.main-container .dropdown-item:last-child {
    border-bottom: none;
}

.main-container .dropdown-item:hover {
    background: #f8f9fa;
}

.main-container .dropdown-item.active {
    background: #e3f2fd;
    color: #1976d2;
}

.main-container .dropdown-item.active:hover {
    background: #bbdefb;
}

.main-container .item-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.main-container .item-title {
    font-weight: 500;
    font-size: 14px;
    line-height: 1.2;
}

.main-container .item-key {
    font-size: 12px;
    color: #666;
    font-family: 'Courier New', monospace;
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    display: inline-block;
}

.main-container .dropdown-item.active .item-key {
    background: #c5e1fd;
    color: #0d47a1;
}

.main-container .active-indicator {
    color: #007bff;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

/* Contenedor de tablas */
.tables-container {
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.table-section {
    display: none;
    padding: 20px;
}

.table-section.active {
    display: block;
}

.table-content {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h2 {
    color: #333;
    margin: 0;
    font-size: 22px;
}

.table-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 250px;
}

.search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

/* Tabla */
.table-wrapper {
    overflow-x: auto;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 14px;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px 8px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #ddd;
    position: sticky;
    top: 0;
    cursor: pointer;
    user-select: none;
}

.data-table th[data-sort]::after {
    content: " ↕";
    color: #999;
    font-size: 12px;
}

.data-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.data-table tr.total-row {
    background: #e6f3ff;
    font-weight: bold;
    border-top: 2px solid #007bff;
}

.data-table tr.total-row td {
    color: #0056b3;
}

/* Celdas numéricas */
.number-cell {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.percentage-cell {
    text-align: center;
    font-weight: bold;
}

.negative {
    color: #dc3545;
}

.positive {
    color: #28a745;
}

.no-data {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px;
}

/* Estilos para hojas vacías */
.empty-sheet {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 20px 0;
}

.empty-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-message {
    font-size: 18px;
    margin-bottom: 10px;
    font-weight: 500;
}

.empty-description {
    font-size: 14px;
    color: #888;
}

/* Estilos para edición inline */
tr.editing {
    background-color: #fff3cd !important;
    box-shadow: 0 0 0 2px #ffc107;
}

.edit-input {
    width: 100%;
    padding: 5px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 14px;
    background-color: white;
    box-sizing: border-box;
}

.edit-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.edit-input[type="number"] {
    text-align: right;
}

/* Contenedor de botones de edición */
.edit-buttons-container {
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

/* Contenedor de botones de acción normales */
.action-buttons-container {
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

/* Botones */
.btn-export, .btn-edit, .btn-detail, .btn-save, .btn-cancel {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

/* Botones de solo icono - sin fondo */
.btn-icon {
    background: none !important;
    border: none !important;
    padding: 8px;
    font-size: 18px;
    cursor: pointer;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: inherit;
    box-shadow: none !important;
}

.btn-icon:hover {
    background-color: rgba(0, 0, 0, 0.1) !important;
    transform: scale(1.1);
}

.btn-icon:active {
    transform: scale(0.95);
}

/* Específico para botón cancelar */
.btn-cancel.btn-icon {
    color: #dc3545 !important;
    font-weight: bold;
    font-size: 20px;
    font-family: monospace;
}

.btn-cancel.btn-icon:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.btn-export, .btn-import {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    color: white;
}

.btn-export {
    background: #28a745;
}

.btn-export:hover {
    background: #218838;
    color: white;
}

.btn-import {
    background: #17a2b8;
}

.btn-import:hover {
    background: #138496;
    color: white;
    text-decoration: none;
}

.btn-export i,
.btn-import i {
    font-size: 14px;
}

/* Estilos para las tablas de secciones */
.sections-container {
    padding: 20px 0;
}

.section-table {
    margin-bottom: 40px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.section-title {
    background: #233E6C;
    color: white;
    margin: 0;
    padding: 15px 20px;
    font-size: 18px;
    font-weight: 600;
}

.section-budget-table {
    width: 100%;
    margin: 0;
}

.section-budget-table th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    text-align: center;
    padding: 12px 8px;
    border-bottom: 2px solid #dee2e6;
}

.section-budget-table th:first-child {
    text-align: left;
    width: 40%;
}

.section-budget-table th:nth-child(2),
.section-budget-table th:nth-child(3),
.section-budget-table th:nth-child(4) {
    width: 20%;
}

.section-budget-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.section-budget-table td:first-child {
    font-weight: 500;
    color: #495057;
}

.section-budget-table .number-cell {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.section-budget-table .editable {
    cursor: pointer;
    transition: all 0.2s ease;
}

.section-budget-table .editable:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.section-budget-table .calculated {
    font-weight: 600;
}

.section-budget-table .positive {
    color: #28a745;
}

.section-budget-table .negative {
    color: #dc3545;
}

.section-budget-table .total-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 2px solid #dee2e6;
    font-weight: 700;
}

.section-budget-table .total-row td {
    font-weight: 700;
    color: #495057;
    padding: 12px 8px;
}

/* Responsive para tablas de secciones */
@media (max-width: 768px) {
    .sections-container {
        padding: 10px 0;
    }
    
    .section-table {
        margin-bottom: 30px;
    }
    
    .section-title {
        padding: 12px 15px;
        font-size: 16px;
    }
    
    .section-budget-table th,
    .section-budget-table td {
        padding: 8px 6px;
        font-size: 13px;
    }
    
    .section-budget-table th:first-child,
    .section-budget-table th:nth-child(2),
    .section-budget-table th:nth-child(3),
    .section-budget-table th:nth-child(4) {
        width: auto;
    }
}

.btn-save {
    background: #007bff;
    color: white;
}

.btn-save:hover {
    background: #0056b3;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #545b62;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .main-container {
        padding: 10px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .table-controls {
        justify-content: space-between;
    }
    
    .search-input {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 4px;
    }
}

@media (max-width: 480px) {
    .table-header h2 {
        font-size: 18px;
    }
    
    .data-table {
        font-size: 11px;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
}

/* Estilos específicos para BUDGET */
.budget-principal-container {
    padding: 20px;
}

.estudiantes-section, .resumen-section {
    margin-bottom: 30px;
}

.budget-table {
    width: 100%;
    max-width: none;
}

.budget-table th {
    background-color: #233E6C;
    color: white;
    font-weight: bold;
    text-align: center;
    padding: 12px 8px;
    font-size: 11px;
}

.budget-table td {
    padding: 10px 8px;
    border: 1px solid #ddd;
    font-size: 12px;
}

.budget-table .number-cell {
    text-align: center;
    font-weight: bold;
}

.budget-table .editable {
    background-color: #f8f9fa;
    cursor: pointer;
}

.budget-table .editable:hover {
    background-color: #e9ecef;
}

.budget-table .calculated {
    background-color: #e3f2fd;
    font-style: italic;
}

.budget-table .total-row {
    background-color: #f5f5f5;
    font-weight: bold;
}

.budget-table .total-row td {
    font-weight: bold;
    border-top: 2px solid #233E6C;
}

.budget-table .saldo-contable-row {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
}

.budget-table .negative {
    color: #dc3545;
    font-weight: bold;
}

.budget-table h4 {
    margin: 20px 0 10px 0;
    color: #233E6C;
    font-weight: bold;
    font-size: 16px;
}

.resumen-section h4 {
    margin: 20px 0 10px 0;
    color: #233E6C;
    font-weight: bold;
    font-size: 16px;
    border-bottom: 2px solid #233E6C;
    padding-bottom: 5px;
}

/* Estilos para el modo editable */
.editing-mode {
    transition: all 0.3s ease;
    outline: none;
}

.editing-mode:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

.editing-mode:hover {
    background-color: #f8f9fa !important;
}

/* Estilos para el botón de toggle editable */
#toggle-editable, #save-data {
    transition: all 0.3s ease;
}

#toggle-editable:hover, #save-data:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

#save-data {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

#save-data:disabled {
    background-color: #6c757d !important;
    cursor: not-allowed;
    animation: none;
}

/* Indicador visual para celdas editables */
.number-cell[contenteditable="true"]::before {
    content: "✏️";
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 8px;
    opacity: 0.6;
}

.number-cell {
    position: relative;
}

/* Estilos para celdas calculadas */
.number-cell.calculated {
    background-color: #e8f5e8 !important;
    font-weight: bold;
    border-left: 3px solid #28a745;
}

.number-cell.calculated::after {
    content: "🧮";
    position: absolute;
    top: 2px;
    left: 2px;
    font-size: 8px;
    opacity: 0.7;
}

/* Animación para cuando se actualiza un cálculo */
.number-cell.calculated.updated {
    animation: calculatePulse 0.6s ease-in-out;
}

@keyframes calculatePulse {
    0% { background-color: #e8f5e8; }
    50% { background-color: #c3e6c3; transform: scale(1.02); }
    100% { background-color: #e8f5e8; }
}

/* Estilo para filas de totales */
.total-row {
    background-color: #f8f9fa !important;
    border-top: 2px solid #dee2e6;
    border-bottom: 2px solid #dee2e6;
}

.total-row .number-cell {
    font-weight: bold;
    color: #495057;
}

/* Estilos para filtro por mes */
.filters-container {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.filter-status {
    flex: 1;
    text-align: right;
}

.filter-status-text {
    color: #6c757d;
    font-size: 14px;
    font-style: italic;
}

/* Efectos de carga para las tablas */
.section-table {
    transition: opacity 0.3s ease;
}

.section-table.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Estilos específicos para la hoja Equipos y Dotación Salones */
.equipos-dotacion-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.equipos-dotacion-container .sheet-header h2 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.equipos-dotacion-container .sheet-header h3 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
}

.equipos-dotacion-container .table-wrapper {
    background: white;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.equipos-dotacion-container .detalle-mes h4 {
    background: linear-gradient(135deg, #34495e, #2c3e50);
    color: white;
    padding: 8px 15px;
    margin: 0 0 15px 0;
    border-radius: 4px;
    font-size: 14px;
}

.equipos-dotacion-container .detalles-mensuales {
    margin-top: 40px;
}

.equipos-dotacion-container .number-cell {
    text-align: right;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

/* Estilos específicos para la hoja Aseo y Cafetería */
.aseo-cafeteria-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.aseo-cafeteria-container .sheet-header h2 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-bottom: 2px solid #ff6b35;
    padding-bottom: 10px;
}

.aseo-cafeteria-container .sheet-header h3 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #ff6b35 !important;
}

.aseo-cafeteria-container .table-wrapper {
    background: white;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.aseo-cafeteria-container .detalle-mes h4 {
    background: linear-gradient(135deg, #ff6b35, #e55a2b);
    color: white;
    padding: 8px 15px;
    margin: 0 0 15px 0;
    border-radius: 4px;
    font-size: 14px;
}

.aseo-cafeteria-container .detalles-mensuales {
    margin-top: 40px;
}

.aseo-cafeteria-container .number-cell {
    text-align: right;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

/* Estilos específicos para Dotaciones */
.dotaciones-container {
    background: linear-gradient(135deg, #f4f3ff, #ebe9ff);
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(142, 68, 173, 0.15);
    border: 1px solid #d1c4e9;
}

.dotaciones-container .sheet-header h2 {
    color: #4a148c;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    border-bottom: 2px solid #8e44ad;
    padding-bottom: 10px;
}

.dotaciones-container .sheet-header h3 {
    color: #6a1b9a;
    font-weight: 600;
}

.dotaciones-container .table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(142, 68, 173, 0.1);
}

.dotaciones-container .detalle-mes h4 {
    background: linear-gradient(135deg, #8e44ad, #7d3c98);
    color: white;
    padding: 10px 15px;
    margin: 0;
    border-radius: 6px 6px 0 0;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dotaciones-container .detalles-mensuales {
    margin-top: 30px;
}

.dotaciones-container .number-cell {
    text-align: right;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

/* Estilos específicos para Agasajos */
.agasajos-container {
    background: linear-gradient(135deg, #fdf2f2, #fce8e8);
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.15);
    border: 1px solid #f5b7b1;
}

.agasajos-container .sheet-header h2 {
    color: #c0392b;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    border-bottom: 2px solid #e74c3c;
    padding-bottom: 10px;
}

.agasajos-container .sheet-header h3 {
    color: #e74c3c;
    font-weight: 600;
}

.agasajos-container .table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.1);
}

.agasajos-container .detalle-mes h4 {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 10px 15px;
    margin: 0;
    border-radius: 6px 6px 0 0;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.agasajos-container .detalles-mensuales {
    margin-top: 30px;
}

.agasajos-container .number-cell {
    text-align: right;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

/* Estilos específicos para Tecnología */
.tecnologia-container {
    background: linear-gradient(135deg, #e8f8fa, #d1ecf1);
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.15);
    border: 1px solid #b3d9e6;
}

.tecnologia-container .sheet-header h2 {
    color: #0c5460;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    border-bottom: 2px solid #17a2b8;
    padding-bottom: 10px;
}

.tecnologia-container .sheet-header h3 {
    color: #138496;
    font-weight: 600;
}

.tecnologia-container .table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(23, 162, 184, 0.1);
}

.tecnologia-container .detalle-mes h4 {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 10px 15px;
    margin: 0;
    border-radius: 6px 6px 0 0;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tecnologia-container .detalles-mensuales {
    margin-top: 30px;
}

.tecnologia-container .number-cell {
    text-align: right;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}
</style>
@stop

@section('js')
<script>
// Datos del servidor
const serverData = @json(isset($presupuestoItems) ? $presupuestoItems->values() : []);

// Función global para cerrar el modal
function cerrarModal() {
    const modal = document.getElementById('detalleGastoModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaurar scroll del body
}

// Función global para abrir el modal
function abrirModal() {
    const modal = document.getElementById('detalleGastoModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
}

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentSheet = 'BUDGET';
    let sortOrder = {};
    let currentPage = 1;
    let itemsPerPage = 50;
    let allItems = [];
    let filteredItems = [];
    let isLoading = false;
    
    // Función para mostrar modal de detalles del gasto
    function mostrarDetalleGasto(element) {
        // Extraer datos del elemento
        const datos = {
            seccion: element.getAttribute('data-seccion'),
            rubro: element.getAttribute('data-rubro'),
            cuenta: element.getAttribute('data-cuenta'),
            documento: element.getAttribute('data-documento'),
            fuente: element.getAttribute('data-fuente'),
            fecha: element.getAttribute('data-fecha'),
            descripcion: element.getAttribute('data-descripcion'),
            nombreTercero: element.getAttribute('data-nombre-tercero'),
            centroCosto: element.getAttribute('data-centro-costo'),
            valor: element.getAttribute('data-valor')
        };
        
        // Llenar el modal con los datos
        document.getElementById('modal-seccion').textContent = datos.seccion || '-';
        document.getElementById('modal-rubro').textContent = datos.rubro || '-';
        document.getElementById('modal-cuenta').textContent = datos.cuenta || '-';
        document.getElementById('modal-documento').textContent = datos.documento || '-';
        document.getElementById('modal-fuente').textContent = datos.fuente || '-';
        document.getElementById('modal-fecha').textContent = datos.fecha || '-';
        document.getElementById('modal-valor').textContent = '$' + new Intl.NumberFormat('es-CO').format(datos.valor || 0);
        document.getElementById('modal-centro-costo').textContent = datos.centroCosto || '-';
        document.getElementById('modal-descripcion').textContent = datos.descripcion || 'Sin descripción disponible';
        document.getElementById('modal-nombre-tercero').textContent = datos.nombreTercero || '-';
        
        // Mostrar el modal personalizado
        abrirModal();
    }
    
    // Event listener para valores clickeables
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('valor-clickeable')) {
            e.preventDefault();
            mostrarDetalleGasto(e.target);
        }
    });
    
    // Event listeners para el modal personalizado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('detalleGastoModal');
            if (modal.style.display === 'flex') {
                cerrarModal();
            }
        }
    });
    
    // Elementos del DOM
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const currentSheetTitle = document.getElementById('currentSheetTitle');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    const tableSections = document.querySelectorAll('.table-section');
    const searchInputs = document.querySelectorAll('.search-input');
    const sortHeaders = document.querySelectorAll('th[data-sort]');
    const editButtons = document.querySelectorAll('.btn-edit');
    const exportButtons = document.querySelectorAll('.btn-export');
    const sectionFilter = document.getElementById('sectionFilter');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const tableBody = document.getElementById('presupuestoTableBody');
    const recordsShown = document.getElementById('recordsShown');
    const totalRecords = document.getElementById('totalRecords');
    
    // Inicialización
    init();
    
    function init() {
        setupDropdownMenu();
        setupNavigation();
        setupSearch();
        setupSorting();
        setupInlineEdit();
        setupExport();
        setupPagination();
        
        console.log('Sistema de tablas inicializado correctamente');
    }
    
    // Función de test para verificar elementos
    function testFilterSetup() {
        // Verificar que estemos en la pestaña correcta
        const activeSection = document.querySelector('.table-section.active');
        if (!activeSection || !activeSection.id.includes('Detallado secciones1')) {
            console.log('No estamos en la pestaña Detallado secciones1, saltando test');
            return;
        }
        
        const sectionSelect = document.getElementById('sectionFilter');
        const tbody = document.getElementById('presupuestoTableBody');
        const recordsShown = document.getElementById('recordsShown');
        const totalRecords = document.getElementById('totalRecords');
        
        console.log('Test de elementos en pestaña activa:');
        console.log('- sectionFilter:', sectionSelect ? 'ENCONTRADO' : 'NO ENCONTRADO');
        console.log('- presupuestoTableBody:', tbody ? 'ENCONTRADO' : 'NO ENCONTRADO');
        console.log('- recordsShown:', recordsShown ? 'ENCONTRADO' : 'NO ENCONTRADO');
        console.log('- totalRecords:', totalRecords ? 'ENCONTRADO' : 'NO ENCONTRADO');
        
        if (tbody) {
            const rows = tbody.querySelectorAll('tr');
            console.log('- Filas encontradas:', rows.length);
            
            if (rows.length > 0) {
                const firstRow = rows[0];
                const dataSection = firstRow.getAttribute('data-section');
                const cellText = firstRow.cells[0] ? firstRow.cells[0].textContent.trim() : 'NO CELL';
                console.log('- Primera fila data-section:', dataSection);
                console.log('- Primera fila celda texto:', cellText);
            }
        }
        
        // Inicializar contadores
        if (tbody && recordsShown && totalRecords) {
            const rows = tbody.querySelectorAll('tr');
            recordsShown.textContent = rows.length;
            totalRecords.textContent = rows.length;
        }
    }
    
    // Menú dropdown
    function setupDropdownMenu() {
        hamburgerBtn.addEventListener('click', toggleDropdown);
        
        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!hamburgerBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                closeDropdown();
            }
        });
        
        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDropdown();
            }
        });
    }
    
    function toggleDropdown() {
        if (dropdownMenu.classList.contains('show')) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }
    
    function openDropdown() {
        dropdownMenu.classList.add('show');
        hamburgerBtn.classList.add('active');
    }
    
    function closeDropdown() {
        dropdownMenu.classList.remove('show');
        hamburgerBtn.classList.remove('active');
    }
    
    // Navegación entre hojas
    function setupNavigation() {
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                const sheetKey = this.dataset.sheet;
                switchToSheet(sheetKey);
                closeDropdown();
            });
        });
    }
    
    function switchToSheet(sheetKey) {
        // Actualizar dropdown items
        dropdownItems.forEach(item => {
            item.classList.remove('active');
            const indicator = item.querySelector('.active-indicator');
            if (indicator) {
                indicator.remove();
            }
        });
        
        const selectedItem = document.querySelector(`[data-sheet="${sheetKey}"]`);
        if (selectedItem) {
            selectedItem.classList.add('active');
            
            // Agregar indicador activo
            const indicator = document.createElement('span');
            indicator.className = 'active-indicator';
            indicator.textContent = '●';
            selectedItem.appendChild(indicator);
            
            // Actualizar título
            const title = selectedItem.querySelector('.item-title').textContent;
            currentSheetTitle.textContent = `${sheetKey} - ${title}`;
        }
        
        // Actualizar secciones de tabla
        tableSections.forEach(section => section.classList.remove('active'));
        document.getElementById(`table-${sheetKey}`).classList.add('active');
        
        currentSheet = sheetKey;
        console.log('Cambiado a hoja:', sheetKey);
    }
    
    // Sistema de búsqueda
    function setupSearch() {
        searchInputs.forEach(input => {
            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const tableKey = this.dataset.table;
                const tableSection = document.getElementById(`table-${tableKey}`);
                const rows = tableSection.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // Sistema de ordenamiento
    function setupSorting() {
        sortHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                const table = this.closest('table');
                sortTable(table, column);
            });
        });
    }
    
    function sortTable(table, column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isNumeric = ['valor', 'valor_moneda', 'diferencia'].includes(column);
        
        // Determinar dirección de ordenamiento
        if (!sortOrder[column]) sortOrder[column] = 'asc';
        sortOrder[column] = sortOrder[column] === 'asc' ? 'desc' : 'asc';
        
        // Obtener índice de columna
        const headers = table.querySelectorAll('th');
        let columnIndex = -1;
        headers.forEach((header, index) => {
            if (header.dataset.sort === column) {
                columnIndex = index;
            }
        });
        
        if (columnIndex === -1) return;
        
        // Ordenar filas
        rows.sort((a, b) => {
            let aVal = a.cells[columnIndex].textContent.trim();
            let bVal = b.cells[columnIndex].textContent.trim();
            
            if (isNumeric) {
                aVal = parseFloat(aVal.replace(/[^\d.-]/g, '')) || 0;
                bVal = parseFloat(bVal.replace(/[^\d.-]/g, '')) || 0;
            }
            
            if (sortOrder[column] === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
        
        // Reordenar en el DOM
        rows.forEach(row => tbody.appendChild(row));
        
        // Actualizar indicadores visuales
        headers.forEach(header => {
            if (header.dataset.sort) {
                header.style.backgroundColor = '';
                header.innerHTML = header.innerHTML.replace(/ [↑↓]/, '');
            }
        });
        
        const currentHeader = table.querySelector(`th[data-sort="${column}"]`);
        currentHeader.style.backgroundColor = '#e3f2fd';
        currentHeader.innerHTML += sortOrder[column] === 'asc' ? ' ↑' : ' ↓';
    }
    
    // Sistema de edición inline
    function setupInlineEdit() {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const id = this.dataset.id;
                toggleRowEdit(row, id);
            });
        });
    }
    
    function toggleRowEdit(row, id) {
        const isEditing = row.classList.contains('editing');
        
        if (isEditing) {
            cancelRowEdit(row);
        } else {
            startRowEdit(row, id);
        }
    }
    
    function startRowEdit(row, id) {
        // Marcar la fila como en edición
        row.classList.add('editing');
        
        // Obtener las celdas editables (excluir seccion, rubro, cuenta, fecha y acciones)
        const valorCell = row.cells[4]; // Valor
        const valorMonedaCell = row.cells[5]; // Valor Moneda
        const actionsCell = row.cells[7]; // Acciones
        
        // Convertir celdas a campos editables
        makeEditable(valorCell, 'number');
        makeEditable(valorMonedaCell, 'number');
        
        // Cambiar botones de acción
        actionsCell.innerHTML = `
            <div class="edit-buttons-container">
                <button class="btn-save btn-icon" data-id="${id}" title="Guardar cambios">💾</button>
                <button class="btn-cancel btn-icon" data-id="${id}" title="Cancelar edición">×</button>
            </div>
        `;
        
        // Agregar event listeners a los nuevos botones
        const saveBtn = actionsCell.querySelector('.btn-save');
        const cancelBtn = actionsCell.querySelector('.btn-cancel');
        
        saveBtn.addEventListener('click', () => saveRowEdit(row, id));
        cancelBtn.addEventListener('click', () => cancelRowEdit(row));
        
        console.log('Iniciando edición para fila ID:', id);
    }
    
    function makeEditable(cell, type = 'text') {
        const currentValue = cell.textContent.trim();
        const cleanValue = currentValue.replace(/[.,]/g, ''); // Remover formato de números
        
        if (type === 'number') {
            cell.innerHTML = `<input type="number" class="edit-input" value="${cleanValue}" min="0" step="1">`;
        } else {
            cell.innerHTML = `<input type="text" class="edit-input" value="${currentValue}">`;
        }
        
        // Focus en el primer input
        const input = cell.querySelector('.edit-input');
        if (input) {
            input.focus();
            input.select();
        }
    }
    
    function saveRowEdit(row, id) {
        const valorInput = row.cells[4].querySelector('.edit-input');
        const valorMonedaInput = row.cells[5].querySelector('.edit-input');
        
        const newValor = parseInt(valorInput.value) || 0;
        const newValorMoneda = parseInt(valorMonedaInput.value) || 0;
        const diferencia = newValor - newValorMoneda;
        
        // Aquí puedes hacer la llamada AJAX para guardar en el servidor
        console.log('Guardando cambios:', {
            id: id,
            valor: newValor,
            valor_moneda: newValorMoneda
        });
        
        // Actualizar las celdas con los nuevos valores
        row.cells[4].innerHTML = `${newValor.toLocaleString('es-ES')}`;
        row.cells[5].innerHTML = `${newValorMoneda.toLocaleString('es-ES')}`;
        row.cells[6].innerHTML = `${diferencia.toLocaleString('es-ES')}`;
        row.cells[6].className = `number-cell ${diferencia < 0 ? 'negative' : 'positive'}`;
        
        // Restaurar botones originales
        row.cells[7].innerHTML = `
            <div class="action-buttons-container">
                <button class="btn-edit btn-icon" data-id="${id}" title="Editar registro">📝</button>
            </div>
        `;
        
        // Reagregar event listeners
        const editBtn = row.cells[7].querySelector('.btn-edit');
        
        editBtn.addEventListener('click', function() {
            toggleRowEdit(row, id);
        });
        
        // Remover clase de edición
        row.classList.remove('editing');
        
        // Mostrar mensaje de éxito
        showMessage('Cambios guardados correctamente', 'success');
    }
    
    function cancelRowEdit(row) {
        // Restaurar valores originales y botones
        location.reload(); // Solución simple - recargar la página
    }
    
    function showMessage(message, type = 'info') {
        // Crear elemento de mensaje
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type}`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 20px;
            border-radius: 4px;
            background-color: ${type === 'success' ? '#d4edda' : '#f8d7da'};
            color: ${type === 'success' ? '#155724' : '#721c24'};
            border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        `;
        
        document.body.appendChild(messageDiv);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 3000);
    }
    
    // Sistema de exportación
    function setupExport() {
        exportButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tableKey = this.dataset.table;
                exportTable(tableKey);
            });
        });
    }
    
    function exportTable(tableKey) {
        const tableSection = document.getElementById(`table-${tableKey}`);
        const table = tableSection.querySelector('table');
        
        if (!table) return;
        
        // Crear CSV simple
        let csv = '';
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = Array.from(cells).map(cell => 
                cell.textContent.trim().replace(/,/g, ';')
            );
            csv += rowData.join(',') + '\n';
        });
        
        // Descargar archivo
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${tableKey}_${new Date().getTime()}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        console.log('Tabla exportada:', tableKey);
    }
    
    // Configurar filtros
    function setupFilters() {
        const sectionSelect = document.getElementById('sectionFilter');
        if (sectionSelect) {
            console.log('Filtro de sección encontrado');
            sectionSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                console.log('Cambiando filtro a:', selectedValue);
                filterBySection(selectedValue);
            });
        } else {
            console.log('No se encontró el filtro de sección');
        }
    }
    
    // Filtrar por sección
    function filterBySection(sectionValue) {
        const tbody = document.getElementById('presupuestoTableBody');
        if (!tbody) {
            console.log('No se encontró el tbody');
            return;
        }
        
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;
        
        console.log('Total de filas:', rows.length);
        
        rows.forEach((row, index) => {
            const dataSection = row.getAttribute('data-section');
            const cellSection = row.cells[0] ? row.cells[0].textContent.trim() : '';
            
            // Usar tanto el atributo data-section como el contenido de la primera celda
            const rowSection = dataSection || cellSection;
            
            console.log(`Fila ${index}: data-section="${dataSection}", cell="${cellSection}", comparando con "${sectionValue}"`);
            
            const shouldShow = sectionValue === '' || rowSection === sectionValue;
            
            if (shouldShow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Actualizar contadores
        const recordsShownEl = document.getElementById('recordsShown');
        const totalRecordsEl = document.getElementById('totalRecords');
        
        if (recordsShownEl) recordsShownEl.textContent = visibleCount;
        if (totalRecordsEl) totalRecordsEl.textContent = rows.length;
        
        console.log(`Filtro aplicado: ${visibleCount} de ${rows.length} filas visibles`);
    }
    
    // Configurar paginación
    function setupPagination() {
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadMoreRecords);
        }
        
        // Scroll infinito
        window.addEventListener('scroll', function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                if (!isLoading && hasMoreRecords()) {
                    showScrollPrompt();
                }
            }
        });
    }
    
    // Cargar datos iniciales
    function loadInitialData() {
        if (tableBody) {
            // Extraer datos de las filas existentes
            const existingRows = tableBody.querySelectorAll('tr');
            allItems = Array.from(existingRows).map((row, index) => {
                const cells = row.querySelectorAll('td');
                const section = row.dataset.section || row.getAttribute('data-section') || '';
                console.log('Fila', index, 'Sección:', section);
                return {
                    index: index,
                    section: section,
                    element: row,
                    visible: true
                };
            });
            
            filteredItems = [...allItems];
            console.log('Datos cargados:', allItems.length, 'items');
            console.log('Secciones encontradas:', [...new Set(allItems.map(item => item.section))]);
            updateRecordsInfo();
        }
    }
    
    // Aplicar filtros
    function applyFilters() {
        const selectedSection = sectionFilter ? sectionFilter.value.trim() : '';
        console.log('Filtro aplicado. Sección seleccionada:', selectedSection);
        
        filteredItems = allItems.filter(item => {
            if (selectedSection && selectedSection !== '' && item.section !== selectedSection) {
                return false;
            }
            return true;
        });
        
        console.log('Items filtrados:', filteredItems.length, 'de', allItems.length);
        
        // Mostrar/ocultar filas según filtros
        allItems.forEach(item => {
            const isVisible = filteredItems.includes(item);
            item.element.style.display = isVisible ? '' : 'none';
            item.visible = isVisible;
        });
        
        updateRecordsInfo();
        updateLoadMoreButton();
    }
    
    // Cargar más registros
    function loadMoreRecords() {
        // Usar la nueva función que respeta los filtros
        var select = document.getElementById('sectionFilter');
        var filtroActual = select ? select.value : '';
        cargarMasRegistros(filtroActual);
    }
    
    // Verificar si hay más registros
    function hasMoreRecords() {
        // En este caso, como estamos mostrando todos los registros filtrados,
        // no hay más que cargar, pero podrías implementar lógica real aquí
        return false;
    }
    
    // Mostrar prompt de scroll
    function showScrollPrompt() {
        if (loadMoreContainer && !isLoading) {
            loadMoreContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            loadMoreBtn.style.animation = 'pulse 1s ease-in-out';
            setTimeout(() => {
                loadMoreBtn.style.animation = '';
            }, 1000);
        }
    }
    
    // Mostrar indicador de carga
    function showLoading(show) {
        if (loadMoreBtn && loadingIndicator) {
            loadMoreBtn.style.display = show ? 'none' : 'inline-flex';
            loadingIndicator.style.display = show ? 'flex' : 'none';
        }
    }
    
    // Actualizar información de registros
    function updateRecordsInfo() {
        if (recordsShown && totalRecords) {
            const visibleCount = filteredItems.filter(item => item.visible).length;
            const totalCount = allItems.length;
            
            recordsShown.textContent = visibleCount;
            totalRecords.textContent = totalCount;
        }
    }
    
    // Actualizar botón cargar más
    function updateLoadMoreButton() {
        if (loadMoreContainer) {
            const hasMore = hasMoreRecords();
            loadMoreContainer.style.display = hasMore ? 'block' : 'none';
        }
    }
    
    console.log('JavaScript cargado correctamente');
    
    // Cargar datos iniciales para las secciones si estamos en la pestaña Secciones
    loadInitialSectionData();
});

// Función para cargar datos iniciales de las secciones
function loadInitialSectionData() {
    const sectionsData = @json($seccionesData ?? []);
    
    if (sectionsData && Object.keys(sectionsData).length > 0) {
        // Cargar datos iniciales sin filtro
        updateSectionTables({
            'PREESCOLAR Y PRIMARIA': sectionsData['PREESCOLAR Y PRIMARIA'] || {},
            'ESCUELA MEDIA': sectionsData['ESCUELA MEDIA'] || {},
            'ALTA': sectionsData['ALTA'] || {}
        });
    }
}

// Función global MUY SIMPLE para filtrar por sección
function filtrarPorSeccion() {
    console.log('=== INICIO FILTRO ===');
    
    var select = document.getElementById('sectionFilter');
    var tabla = document.getElementById('presupuestoTableBody');
    
    if (!select || !tabla) {
        alert('ERROR: No se encontraron los elementos select o tabla');
        return;
    }
    
    var valorFiltro = select.value;
    console.log('Valor del filtro:', valorFiltro);
    
    var filas = tabla.getElementsByTagName('tr');
    console.log('Número de filas encontradas:', filas.length);
    
    var contador = 0;
    var mostradas = 0;
    
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var celdaSeccion = fila.getElementsByTagName('td')[0];
        
        if (celdaSeccion) {
            var textoSeccion = celdaSeccion.textContent || celdaSeccion.innerText;
            var coincide = (valorFiltro === '' || textoSeccion.indexOf(valorFiltro) >= 0);
            
            if (coincide) {
                // Mostrar solo las primeras 50 que coinciden
                if (contador < 50) {
                    fila.style.display = '';
                    mostradas++;
                } else {
                    fila.style.display = 'none';
                }
                contador++;
            } else {
                fila.style.display = 'none';
            }
        }
    }
    
    console.log('Total coincidencias encontradas:', contador);
    console.log('Filas mostradas (max 50):', mostradas);
    
    // Actualizar contador
    var spanContador = document.getElementById('recordsShown');
    if (spanContador) {
        spanContador.textContent = mostradas;
    }
    
    // Mostrar/ocultar botón "Cargar más"
    var loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        if (contador > 50) {
            loadMoreBtn.style.display = 'inline-flex';
            loadMoreBtn.onclick = function() { cargarMasRegistros(valorFiltro); };
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
    
    console.log('=== FIN FILTRO ===');
}

// Función para cargar más registros cuando hay filtro activo
function cargarMasRegistros(filtro) {
    console.log('Cargando más registros para filtro:', filtro);
    
    var tabla = document.getElementById('presupuestoTableBody');
    var filas = tabla.getElementsByTagName('tr');
    var mostradas = 0;
    var agregadas = 0;
    
    // Contar cuántas están visibles
    for (var i = 0; i < filas.length; i++) {
        if (filas[i].style.display !== 'none') {
            mostradas++;
        }
    }
    
    // Mostrar las siguientes 50
    for (var i = 0; i < filas.length && agregadas < 50; i++) {
        var fila = filas[i];
        var celdaSeccion = fila.getElementsByTagName('td')[0];
        
        if (celdaSeccion && fila.style.display === 'none') {
            var textoSeccion = celdaSeccion.textContent || celdaSeccion.innerText;
            var coincide = (filtro === '' || textoSeccion.indexOf(filtro) >= 0);
            
            if (coincide) {
                fila.style.display = '';
                agregadas++;
            }
        }
    }
    
    console.log('Se agregaron', agregadas, 'registros más');
    
    // Actualizar contador
    var spanContador = document.getElementById('recordsShown');
    if (spanContador) {
        spanContador.textContent = mostradas + agregadas;
    }
    
    // Verificar si quedan más registros
    var quedanMas = false;
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var celdaSeccion = fila.getElementsByTagName('td')[0];
        
        if (celdaSeccion && fila.style.display === 'none') {
            var textoSeccion = celdaSeccion.textContent || celdaSeccion.innerText;
            var coincide = (filtro === '' || textoSeccion.indexOf(filtro) >= 0);
            
            if (coincide) {
                quedanMas = true;
                break;
            }
        }
    }
    
    // Ocultar botón si no quedan más
    var loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn && !quedanMas) {
        loadMoreBtn.style.display = 'none';
    }
}

// Funciones para las tablas de secciones
document.addEventListener('DOMContentLoaded', function() {
    initSectionTables();
});

function initSectionTables() {
    // Hacer editables solo las celdas de ejecución (no presupuesto)
    const editableCells = document.querySelectorAll('.section-budget-table .editable[data-type="ejecucion"]');
    
    editableCells.forEach(cell => {
        cell.addEventListener('click', function() {
            if (this.querySelector('input')) return; // Ya está siendo editada
            
            const currentValue = this.textContent.trim().replace(/\./g, '').replace(/,/g, ''); // Remover formato
            const input = document.createElement('input');
            input.type = 'number';
            input.value = currentValue || 0;
            input.style.width = '100%';
            input.style.border = 'none';
            input.style.background = 'transparent';
            input.style.textAlign = 'right';
            input.style.fontFamily = 'Courier New, monospace';
            input.style.fontSize = '14px';
            
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            input.select();
            
            // Guardar al perder el foco o presionar Enter
            const saveValue = () => {
                const newValue = parseFloat(input.value) || 0;
                this.textContent = formatNumber(newValue);
                calculateRowSaldo(this);
                updateTableTotals(this.closest('.section-budget-table'));
            };
            
            input.addEventListener('blur', saveValue);
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    saveValue();
                }
            });
        });
    });
    
    // Calcular totales iniciales para todas las tablas
    document.querySelectorAll('.section-budget-table').forEach(table => {
        updateTableTotals(table);
    });
}

function calculateRowSaldo(cell) {
    const row = cell.closest('tr');
    const presupuestoCell = row.querySelector('[data-type="presupuesto"]');
    const ejecucionCell = row.querySelector('[data-type="ejecucion"]');
    const saldoCell = row.querySelector('.calculated');
    
    if (presupuestoCell && ejecucionCell && saldoCell) {
        const presupuesto = parseFloat(presupuestoCell.textContent.replace(/[,\.]/g, '')) || 0;
        const ejecucion = parseFloat(ejecucionCell.textContent.replace(/[,\.]/g, '')) || 0;
        const saldo = presupuesto - ejecucion;
        
        saldoCell.textContent = formatNumber(saldo);
        saldoCell.className = 'number-cell calculated ' + (saldo < 0 ? 'negative' : 'positive');
    }
}

function updateTableTotals(table) {
    let totalPresupuesto = 0;
    let totalEjecucion = 0;
    let totalSaldo = 0;
    
    // Sumar todas las filas (excepto la fila de total)
    const dataRows = table.querySelectorAll('tbody tr:not(.total-row)');
    dataRows.forEach(row => {
        const presupuestoCell = row.querySelector('[data-type="presupuesto"]');
        const ejecucionCell = row.querySelector('[data-type="ejecucion"]');
        const saldoCell = row.querySelector('.calculated');
        
        if (presupuestoCell && ejecucionCell) {
            totalPresupuesto += parseFloat(presupuestoCell.textContent.replace(/[,\.]/g, '')) || 0;
            totalEjecucion += parseFloat(ejecucionCell.textContent.replace(/[,\.]/g, '')) || 0;
            
            // Recalcular saldo de la fila
            calculateRowSaldo(presupuestoCell);
        }
    });
    
    totalSaldo = totalPresupuesto - totalEjecucion;
    
    // Actualizar fila de totales
    const totalRow = table.querySelector('.total-row');
    if (totalRow) {
        totalRow.querySelector('.total-presupuesto').textContent = formatNumber(totalPresupuesto);
        totalRow.querySelector('.total-ejecucion').textContent = formatNumber(totalEjecucion);
        const totalSaldoCell = totalRow.querySelector('.total-saldo');
        totalSaldoCell.textContent = formatNumber(totalSaldo);
        totalSaldoCell.className = 'number-cell total-saldo ' + (totalSaldo < 0 ? 'negative' : 'positive');
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
}

// Funcionalidad de filtrado de tablas
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando sistema de filtros...');
    
    const filterDropdown = document.getElementById('budget-filter');
    const resetButton = document.getElementById('reset-filter');
    const filterStatus = document.getElementById('filter-status');
    const toggleEditableButton = document.getElementById('toggle-editable');
    const editableStatus = document.getElementById('editable-status');
    
    let isEditableMode = false;
    
    console.log('Elementos encontrados:', {
        filterDropdown: !!filterDropdown,
        resetButton: !!resetButton,
        filterStatus: !!filterStatus,
        toggleEditableButton: !!toggleEditableButton,
        editableStatus: !!editableStatus
    });
    
    if (filterDropdown) {
        filterDropdown.addEventListener('change', function() {
            const selectedCategory = this.value;
            console.log('Filtro seleccionado:', selectedCategory);
            filterTables(selectedCategory);
            updateFilterStatus(selectedCategory);
        });
    } else {
        console.error('No se encontró el elemento budget-filter');
    }
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            console.log('Botón reset presionado');
            filterDropdown.value = 'all';
            filterTables('all');
            updateFilterStatus('all');
        });
    } else {
        console.error('No se encontró el elemento reset-filter');
    }
    
    // Manejo del botón para hacer editables las tablas
    if (toggleEditableButton) {
        toggleEditableButton.addEventListener('click', function() {
            console.log('Botón toggle editable presionado');
            isEditableMode = !isEditableMode;
            toggleEditableModeWithCalculation(isEditableMode);
            updateEditableStatus(isEditableMode);
        });
    } else {
        console.error('No se encontró el elemento toggle-editable');
    }
    
    // Manejo del botón de guardar datos
    const saveButton = document.getElementById('save-data');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            console.log('🚀 Iniciando guardado masivo de datos...');
            saveAllData();
        });
    } else {
        console.error('No se encontró el elemento save-data');
    }
    
    function filterTables(category) {
        const allSections = document.querySelectorAll('.budget-section');
        console.log('Secciones encontradas:', allSections.length);
        
        let visibleCount = 0;
        
        allSections.forEach(section => {
            if (category === 'all') {
                section.style.display = 'block';
                visibleCount++;
            } else {
                const sectionCategory = section.getAttribute('data-filter-category');
                if (sectionCategory === category) {
                    section.style.display = 'block';
                    visibleCount++;
                } else {
                    section.style.display = 'none';
                }
            }
        });
        
        console.log('Tablas visibles:', visibleCount, 'de', allSections.length);
    }
    
    function updateFilterStatus(category) {
        if (filterStatus) {
            let statusText = 'Mostrando: ';
            
            switch(category) {
                case 'all':
                    statusText += 'Todas las tablas';
                    break;
                case 'resumen':
                    statusText += 'Tablas de resumen';
                    break;
                case 'ingresos':
                    statusText += 'Tablas de ingresos';
                    break;
                case 'salarios':
                    statusText += 'Tablas de salarios';
                    break;
                case 'gastos':
                    statusText += 'Tablas de gastos';
                    break;
                case 'servicios':
                    statusText += 'Tablas de servicios';
                    break;
                case 'academia':
                    statusText += 'Tablas de academia';
                    break;
                case 'contratos':
                    statusText += 'Tablas de contratos';
                    break;
                default:
                    statusText += 'Sin filtro';
            }
            
            filterStatus.textContent = statusText;
            console.log('Estado actualizado:', statusText);
        } else {
            console.error('No se encontró el elemento filter-status');
        }
    }
    
    function toggleEditableMode(isEditable) {
        console.log('Cambiando modo editable:', isEditable);
        
        // Buscar todas las celdas que pueden ser editables
        const editableCells = document.querySelectorAll('.number-cell');
        const button = document.getElementById('toggle-editable');
        
        console.log('Celdas encontradas:', editableCells.length);
        
        editableCells.forEach(cell => {
            if (isEditable) {
                // Hacer editable
                cell.contentEditable = true;
                cell.style.backgroundColor = '#fff3cd';
                cell.style.border = '1px solid #ffeaa7';
                cell.style.cursor = 'text';
                cell.classList.add('editing-mode');
                
                // Agregar event listeners para mejor UX
                cell.addEventListener('focus', function() {
                    this.style.backgroundColor = '#fff';
                    this.style.border = '2px solid #007bff';
                });
                
                cell.addEventListener('blur', function() {
                    this.style.backgroundColor = '#fff3cd';
                    this.style.border = '1px solid #ffeaa7';
                });
            } else {
                // Hacer solo lectura
                cell.contentEditable = false;
                cell.style.backgroundColor = '';
                cell.style.border = '';
                cell.style.cursor = '';
                cell.classList.remove('editing-mode');
                
                // Remover event listeners previos
                cell.removeEventListener('focus', arguments.callee);
                cell.removeEventListener('blur', arguments.callee);
            }
        });
        
        // Actualizar el texto del botón
        const saveButton = document.getElementById('save-data');
        
        if (button) {
            if (isEditable) {
                button.textContent = '🔒 Bloquear Edición';
                button.style.backgroundColor = '#dc3545';
                
                // Mostrar el botón de guardar
                if (saveButton) {
                    saveButton.style.display = 'inline-block';
                }
            } else {
                button.textContent = '✏️ Hacer Editables';
                button.style.backgroundColor = '#28a745';
                
                // Ocultar el botón de guardar
                if (saveButton) {
                    saveButton.style.display = 'none';
                }
            }
        }
    }
    
    function updateEditableStatus(isEditable) {
        if (editableStatus) {
            if (isEditable) {
                editableStatus.textContent = 'Modo: Edición Activa';
                editableStatus.style.color = '#28a745';
            } else {
                editableStatus.textContent = 'Modo: Solo Lectura';
                editableStatus.style.color = '#dc3545';
            }
            console.log('Estado editable actualizado:', isEditable);
        } else {
            console.error('No se encontró el elemento editable-status');
        }
    }
    
    // Funcionalidad de cálculo automático
    function initializeAutoCalculation() {
        console.log('Inicializando cálculo automático...');
        
        // Buscar todas las celdas que pueden ser editables (solo dentro de tablas)
        const allNumberCells = document.querySelectorAll('table .number-cell');
        
        console.log('Celdas numéricas encontradas en tablas:', allNumberCells.length);
        
        // Agregar event listeners a todas las celdas numéricas (no solo las que ya son editables)
        allNumberCells.forEach((cell, index) => {
            // Verificar que no sea una celda de total
            const isInTotalRow = cell.closest('.total-row') !== null;
            
            if (!isInTotalRow) {
                console.log(`Configurando listeners para celda ${index}:`, cell.textContent.trim());
                
                // Event listener para cambios inmediatos
                cell.addEventListener('input', function() {
                    console.log('🔄 Celda modificada:', this.textContent);
                    console.log('Posición de la celda:', getColumnIndex(this));
                    calculateTableTotals(this);
                });
                
                // Event listener para cuando se termina de editar
                cell.addEventListener('blur', function() {
                    console.log('🔄 Evento blur disparado en celda:', this.textContent);
                    console.log('✅ Celda editada finalizada:', this.textContent);
                    
                    // Verificar que la celda esté dentro de una tabla válida
                    const isInTable = this.closest('table') !== null;
                    const isInRow = this.closest('tr') !== null;
                    
                    if (isInTable && isInRow) {
                        const cellData = extractCellData(this);
                        if (cellData) {
                            console.log('🔢 Datos extraídos:', cellData);
                            saveCellToDatabase(cellData, this);
                        } else {
                            console.log('❌ No se pudieron extraer datos de la celda');
                        }
                        calculateTableTotals(this);
                    } else {
                        console.log('⚠️ Celda no está en una tabla válida, saltando guardado automático');
                    }
                    
                    formatCurrency(this);
                });
                
                // Event listener para Enter
                cell.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            }
        });
        
        // Calcular totales iniciales
        calculateAllTotals();
    }
    
    // Función para manejar elementos de estadísticas (fuera de tablas)
    function initializeStatElements() {
        console.log('Inicializando elementos de estadísticas...');
        
        // Buscar elementos de estadísticas que tienen number-cell pero no están en tablas
        const statElements = document.querySelectorAll('.stat-number.number-cell');
        
        console.log('Elementos de estadísticas encontrados:', statElements.length);
        
        statElements.forEach((element, index) => {
            console.log(`Configurando listeners para elemento estadístico ${index}:`, element.id);
            
            // Event listener para cambios inmediatos
            element.addEventListener('input', function() {
                console.log('🔄 Elemento estadístico modificado:', this.textContent);
                // Formatear pero no intentar extraer datos de tabla
                formatCurrency(this);
            });
            
            // Event listener para cuando se termina de editar
            element.addEventListener('blur', function() {
                console.log('🔄 Evento blur en elemento estadístico:', this.textContent);
                
                // Crear datos específicos para elementos estadísticos
                const statData = {
                    section: this.dataset.section,
                    concept: this.dataset.concept,
                    type: this.dataset.type,
                    value: extractNumericValue(this.textContent),
                    element_id: this.id
                };
                
                console.log('📊 Datos de elemento estadístico:', statData);
                // Aquí puedes agregar lógica para guardar datos de estadísticas si es necesario
                
                formatCurrency(this);
            });
            
            // Event listener para Enter
            element.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.blur();
                }
            });
        });
    }
    
    // Función auxiliar para obtener el índice real de la columna
    function getColumnIndex(cell) {
        const row = cell.parentNode;
        const cells = Array.from(row.querySelectorAll('td'));
        const index = cells.indexOf(cell);
        console.log(`Índice de columna encontrado: ${index}`);
        return index;
    }
    
    function calculateTableTotals(changedCell) {
        // Encontrar la tabla que contiene la celda modificada
        const table = changedCell.closest('table');
        if (!table) return;
        
        console.log('Calculando totales para tabla...');
        
        // Encontrar todas las filas de totales en esta tabla
        const totalRows = table.querySelectorAll('.total-row');
        
        totalRows.forEach(totalRow => {
            // Obtener todas las celdas TD de la fila de totales (no solo number-cell)
            const totalCells = totalRow.querySelectorAll('td');
            
            totalCells.forEach((totalCell, cellIndex) => {
                // Saltar la primera columna (concepto) 
                if (cellIndex === 0) return;
                
                // Solo procesar si es una celda numérica
                if (totalCell.classList.contains('number-cell')) {
                    // Calcular suma de la columna usando el índice real de la celda
                    const columnTotal = calculateColumnSum(table, cellIndex);
                    
                    // Actualizar la celda de total con formato y animación
                    updateTotalCell(totalCell, columnTotal);
                    
                    console.log(`Columna ${cellIndex} actualizada con total: ${columnTotal}`);
                }
            });
        });
    }
    
    function calculateColumnSum(table, columnIndex) {
        let sum = 0;
        
        console.log(`Calculando suma para columna ${columnIndex}...`);
        
        // Obtener todas las filas de datos (excluir filas de total y header)
        const dataRows = table.querySelectorAll('tbody tr:not(.total-row)');
        
        dataRows.forEach((row, rowIndex) => {
            // Obtener todas las celdas TD de la fila (no solo number-cell)
            const cells = row.querySelectorAll('td');
            
            if (cells[columnIndex]) {
                // Solo sumar si la celda es numérica y no es la primera columna
                if (cells[columnIndex].classList.contains('number-cell')) {
                    const cellValue = extractNumericValue(cells[columnIndex].textContent);
                    if (!isNaN(cellValue) && cellValue !== 0) {
                        sum += cellValue;
                        console.log(`Fila ${rowIndex}, Columna ${columnIndex}: Sumando ${cellValue} (total: ${sum})`);
                    }
                }
            }
        });
        
        console.log(`Total final para columna ${columnIndex}: ${sum}`);
        return sum;
    }
    
    function calculateAllTotals() {
        console.log('Calculando todos los totales...');
        
        // Buscar todas las tablas del presupuesto
        const budgetTables = document.querySelectorAll('.budget-table');
        
        budgetTables.forEach((table, tableIndex) => {
            console.log(`Procesando tabla ${tableIndex + 1}...`);
            const totalRows = table.querySelectorAll('.total-row');
            
            totalRows.forEach((totalRow, rowIndex) => {
                console.log(`  Procesando fila de totales ${rowIndex + 1}...`);
                
                // Obtener todas las celdas TD de la fila de totales
                const totalCells = totalRow.querySelectorAll('td');
                
                totalCells.forEach((totalCell, cellIndex) => {
                    // Saltar la primera columna (concepto)
                    if (cellIndex === 0) return;
                    
                    // Solo procesar si es una celda numérica
                    if (totalCell.classList.contains('number-cell')) {
                        const columnTotal = calculateColumnSum(table, cellIndex);
                        updateTotalCell(totalCell, columnTotal);
                        console.log(`    Celda [${cellIndex}] actualizada: ${columnTotal}`);
                    }
                });
            });
        });
        
        console.log('Cálculo de todos los totales completado.');
        
        // Calcular porcentajes de impacto después de todos los totales
        setTimeout(() => {
            calculateImpactPercentages();
        }, 100);
    }
    
    function updateTotalCell(cell, value) {
        const formattedValue = `<strong>$${formatNumber(value)}</strong>`;
        
        // Solo actualizar si el valor ha cambiado
        if (cell.innerHTML !== formattedValue) {
            cell.innerHTML = formattedValue;
            
            // Agregar clase para animación
            cell.classList.add('updated');
            
            // Marcar como calculada
            cell.classList.add('calculated');
            
            // Remover la clase de animación después de completarla
            setTimeout(() => {
                cell.classList.remove('updated');
            }, 600);
            
            console.log(`Total actualizado: $${formatNumber(value)}`);
        }
    }
    
    function extractNumericValue(text) {
        if (!text) return 0;
        
        // Remover tags HTML
        const cleanHTML = text.replace(/<[^>]*>/g, '');
        
        // Remover símbolos de moneda, comas, puntos y espacios
        let cleanText = cleanHTML.toString()
            .replace(/[$\s]/g, '')          // Remover $ y espacios
            .replace(/\./g, '')             // Remover puntos (separadores de miles)
            .replace(/,/g, '.')             // Convertir comas a puntos decimales
            .replace(/[^\d.-]/g, '');       // Mantener solo dígitos, punto decimal y signo negativo
        
        // Si está vacío o es solo símbolos, retornar 0
        if (cleanText === '' || cleanText === '-' || cleanText === '.') return 0;
        
        const number = parseFloat(cleanText);
        return isNaN(number) ? 0 : Math.round(number); // Redondear a entero
    }
    
    // Función para calcular porcentajes de impacto frente a ingresos totales
    function calculateImpactPercentages() {
        console.log('Calculando porcentajes de impacto...');
        
        // Obtener el total de ingresos desde la tabla RESUMEN
        const totalIngresosValue = getTotalIngresosValue();
        
        if (totalIngresosValue === 0) {
            console.log('Total de ingresos es 0, no se pueden calcular porcentajes');
            return;
        }
        
        // Buscar todas las filas de "Impacto % frente a ingresos totales"
        const impactRows = document.querySelectorAll('tr');
        
        impactRows.forEach(row => {
            const firstCell = row.querySelector('td');
            if (firstCell && firstCell.textContent.includes('Impacto % frente a ingresos totales')) {
                console.log('Procesando fila de impacto:', firstCell.textContent);
                
                // Encontrar la fila de totales anterior
                const totalRow = findPreviousTotalRow(row);
                if (totalRow) {
                    const impactCells = row.querySelectorAll('td.number-cell');
                    const totalCells = totalRow.querySelectorAll('td.number-cell');
                    
                    // Procesar cada mes (saltar primera columna de presupuesto aprobado)
                    for (let i = 1; i < Math.min(impactCells.length, totalCells.length); i++) {
                        const totalValue = extractNumericValue(totalCells[i].textContent);
                        const percentage = totalValue > 0 ? ((totalValue / totalIngresosValue) * 100) : 0;
                        
                        updateImpactCell(impactCells[i], percentage);
                    }
                }
            }
        });
        
        console.log('Cálculo de porcentajes de impacto completado.');
    }
    
    // Función para obtener el valor de Total Ingresos del presupuesto aprobado
    function getTotalIngresosValue() {
        const resumenTables = document.querySelectorAll('.filter-resumen .budget-table');
        
        for (let table of resumenTables) {
            const rows = table.querySelectorAll('tbody tr');
            
            for (let row of rows) {
                const firstCell = row.querySelector('td');
                if (firstCell && firstCell.textContent.includes('Total Ingresos')) {
                    const presupuestoCell = row.querySelector('td.number-cell:nth-child(2)');
                    if (presupuestoCell) {
                        const value = extractNumericValue(presupuestoCell.textContent);
                        console.log('Total Ingresos encontrado:', value);
                        return value;
                    }
                }
            }
        }
        
        console.log('No se encontró Total Ingresos');
        return 0;
    }
    
    // Función para encontrar la fila de totales anterior
    function findPreviousTotalRow(impactRow) {
        let currentRow = impactRow.previousElementSibling;
        
        while (currentRow) {
            if (currentRow.classList.contains('total-row')) {
                return currentRow;
            }
            currentRow = currentRow.previousElementSibling;
        }
        
        return null;
    }
    
    // Función para actualizar celda de impacto con porcentaje
    function updateImpactCell(cell, percentage) {
        const formattedPercentage = percentage > 0 ? `${percentage.toFixed(2)}%` : '-';
        
        if (cell.innerHTML !== formattedPercentage) {
            cell.innerHTML = formattedPercentage;
            
            // Agregar clase para animación
            cell.classList.add('updated');
            
            // Remover la clase de animación después de completarla
            setTimeout(() => {
                cell.classList.remove('updated');
            }, 600);
            
            console.log(`Porcentaje de impacto actualizado: ${formattedPercentage}`);
        }
    }
    
    function formatNumber(number) {
        if (isNaN(number) || number === null || number === undefined) return '0';
        
        // Formatear con separadores de miles para Colombia
        return Math.round(number).toLocaleString('es-CO');
    }
    
    function formatCurrency(cell) {
        const value = extractNumericValue(cell.textContent);
        if (cell.contentEditable === 'true') {
            // Si está en modo editable, solo mostrar el número
            cell.textContent = formatNumber(value);
        } else {
            // Si no está editable, mostrar con símbolo de moneda
            cell.textContent = `$${formatNumber(value)}`;
        }
    }
    
    // Actualizar la función toggleEditableMode para incluir auto-cálculo
    function toggleEditableModeWithCalculation(isEditable) {
        toggleEditableMode(isEditable);
        
        if (isEditable) {
            // Reinicializar auto-cálculo cuando se activa el modo editable
            setTimeout(() => {
                initializeAutoCalculation();
                initializeStatElements();
                // Agregar debugging de estructura de tablas
                debugTableStructure();
            }, 100);
        }
    }
    
    function getColumnIndex(cell) {
        const row = cell.closest('tr');
        const cells = row.querySelectorAll('td');
        return Array.from(cells).indexOf(cell);
    }
    
    // Funciones para persistencia de datos
    function extractCellData(cell) {
        const row = cell.closest('tr');
        const table = cell.closest('table');
        const budgetSection = cell.closest('.budget-section');
        
        if (!row || !table) {
            console.error('❌ No se pudo encontrar fila o tabla para la celda');
            console.error('❌ Elemento:', cell.tagName, cell.className);
            return null;
        }
        
        // Obtener nombre de la tabla desde el título de la sección
        let tablaNombre = 'Presupuesto';
        if (budgetSection) {
            const titleElement = budgetSection.querySelector('h5');
            tablaNombre = titleElement ? titleElement.textContent.trim() : 'Presupuesto';
        }
        
        // Obtener concepto desde la primera celda de la fila
        const firstCell = row.querySelector('td');
        const concepto = firstCell ? firstCell.textContent.trim().replace(/\*\*/g, '') : 'Sin concepto';
        
        // Obtener nombre de la columna desde el header
        const cellIndex = getColumnIndex(cell);
        const headerRow = table.querySelector('thead tr');
        const headerCells = headerRow ? headerRow.querySelectorAll('th') : [];
        const columna = headerCells[cellIndex] ? headerCells[cellIndex].textContent.trim() : `Columna_${cellIndex}`;
        
        // Extraer valor numérico
        const valor = extractNumericValue(cell.textContent);
        
        // Determinar si es una fila de total
        const esTotal = row.classList.contains('total-row');
        
        const cellData = {
            tabla_nombre: tablaNombre,
            concepto: concepto,
            columna: columna,
            valor: valor,
            fila_orden: Array.from(row.parentNode.children).indexOf(row),
            columna_orden: cellIndex,
            es_total: esTotal
        };
        
        console.log('📊 Celda extraída - Concepto:', concepto, 'Columna:', columna, 'Valor:', valor);
        return cellData;
    }
    
    // Variable para controlar el debouncing de guardado
    let saveTimeouts = new Map();
    
    // Función para guardar una celda individual en la base de datos
    function saveCellToDatabase(cellData, cellElement) {
        console.log('💾 Solicitando guardado de celda individual:', cellData);
        
        // Verificar que los datos sean válidos
        if (!cellData || cellData.es_total) {
            console.log('⚠️ Saltando guardado: celda sin datos o es total');
            return;
        }
        
        // Crear una clave única para esta celda
        const cellKey = `${cellData.tabla_nombre}-${cellData.concepto}-${cellData.columna}`;
        
        // Cancelar guardado anterior si existe
        if (saveTimeouts.has(cellKey)) {
            clearTimeout(saveTimeouts.get(cellKey));
            console.log('⏱️ Cancelando guardado anterior para:', cellKey);
        }
        
        // Programar guardado con debouncing de 500ms
        const timeoutId = setTimeout(() => {
            console.log('💾 Ejecutando guardado para:', cellKey);
            actualSaveCellToDatabase(cellData, cellElement);
            saveTimeouts.delete(cellKey);
        }, 500);
        
        saveTimeouts.set(cellKey, timeoutId);
        
        // Indicador visual inmediato de "pendiente de guardado"
        cellElement.style.borderLeft = '3px solid #6c757d'; // Gris para "pendiente"
    }
    
    // Función interna que realiza el guardado real
    function actualSaveCellToDatabase(cellData, cellElement) {
        console.log('💾 Guardando celda individual:', cellData);
        
        // Verificar que los datos sean válidos
        if (!cellData || cellData.es_total) {
            console.log('⚠️ Saltando guardado: celda sin datos o es total');
            return;
        }
        
        // Verificar CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('❌ Token CSRF no encontrado');
            return;
        }
        
        // Agregar indicador visual de guardado
        cellElement.style.borderLeft = '3px solid #ffc107'; // Amarillo para "guardando"
        
        // Enviar datos al servidor usando la misma ruta masiva pero con una sola celda
        fetch('{{ route("presupuesto.guardar-celda-masivo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({
                celdas: [cellData] // Enviar como array con una sola celda
            })
        })
        .then(response => {
            console.log('📨 Respuesta del servidor para celda individual:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Datos recibidos para celda individual:', data);
            if (data.success) {
                console.log('✅ Celda guardada exitosamente');
                
                // Indicador visual de éxito
                cellElement.style.borderLeft = '3px solid #28a745'; // Verde para "guardado"
                setTimeout(() => {
                    cellElement.style.borderLeft = '';
                }, 2000);
                
                // Recalcular porcentajes de impacto después de guardar
                setTimeout(() => {
                    calculateImpactPercentages();
                }, 100);
                
            } else {
                console.error('❌ Error al guardar celda:', data.message);
                
                // Indicador visual de error
                cellElement.style.borderLeft = '3px solid #dc3545'; // Rojo para "error"
                setTimeout(() => {
                    cellElement.style.borderLeft = '';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('❌ Error de conexión al guardar celda:', error);
            
            // Indicador visual de error
            cellElement.style.borderLeft = '3px solid #dc3545'; // Rojo para "error"
            setTimeout(() => {
                cellElement.style.borderLeft = '';
            }, 3000);
        });
    }
    
    function saveAllData() {
        const saveButton = document.getElementById('save-data');
        
        // Deshabilitar botón y cambiar texto
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.innerHTML = '⏳ Guardando...';
        }
        
        // Buscar todas las celdas editables que no sean totales
        const editableCells = document.querySelectorAll('.number-cell:not(.total-row .number-cell)');
        const dataToSave = [];
        
        console.log(`📊 Preparando guardado de ${editableCells.length} celdas...`);
        
        // Extraer datos de todas las celdas
        editableCells.forEach((cell, index) => {
            const cellData = extractCellData(cell);
            if (cellData && !cellData.es_total) {
                dataToSave.push(cellData);
            }
        });
        
        console.log(`💾 ${dataToSave.length} celdas válidas para guardar`);
        
        if (dataToSave.length === 0) {
            console.log('⚠️ No hay datos para guardar');
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = '💾 Guardar Datos';
            }
            return;
        }
        
        // Verificar CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('❌ Token CSRF no encontrado');
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = '💾 Guardar Datos';
            }
            return;
        }
        
        // Enviar datos al servidor
        fetch('{{ route("presupuesto.guardar-celda-masivo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({
                celdas: dataToSave
            })
        })
        .then(response => {
            console.log('📨 Respuesta del servidor:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Datos recibidos:', data);
            if (data.success) {
                console.log(`✅ ${data.guardadas} celdas guardadas exitosamente`);
                
                // Mostrar mensaje de éxito
                if (saveButton) {
                    saveButton.innerHTML = '✅ ¡Guardado!';
                    saveButton.style.backgroundColor = '#28a745';
                    
                    setTimeout(() => {
                        saveButton.innerHTML = '💾 Guardar Datos';
                        saveButton.style.backgroundColor = '#007bff';
                        saveButton.disabled = false;
                    }, 1000); // Reducido de 3000 a 1000ms (1 segundo)
                }
                
                // Agregar indicador visual a todas las celdas guardadas
                editableCells.forEach(cell => {
                    cell.style.borderLeft = '3px solid #28a745';
                    setTimeout(() => {
                        cell.style.borderLeft = '';
                    }, 2000); // Reducido de 5000 a 2000ms (2 segundos)
                });
                
            } else {
                console.error('❌ Error al guardar:', data.message);
                if (saveButton) {
                    saveButton.innerHTML = '❌ Error';
                    saveButton.style.backgroundColor = '#dc3545';
                    
                    setTimeout(() => {
                        saveButton.innerHTML = '💾 Guardar Datos';
                        saveButton.style.backgroundColor = '#007bff';
                        saveButton.disabled = false;
                    }, 1500); // Reducido de 3000 a 1500ms (1.5 segundos)
                }
            }
        })
        .catch(error => {
            console.error('❌ Error de conexión:', error);
            if (saveButton) {
                saveButton.innerHTML = '❌ Error';
                saveButton.style.backgroundColor = '#dc3545';
                
                setTimeout(() => {
                    saveButton.innerHTML = '💾 Guardar Datos';
                    saveButton.style.backgroundColor = '#007bff';
                    saveButton.disabled = false;
                }, 1500); // Reducido de 3000 a 1500ms (1.5 segundos)
            }
        });
    }
    
    // Función para cargar datos desde la base de datos
    function loadDataFromDatabase() {
        // Los datos ya vienen desde el servidor con la variable $spreadsheetData
        @if(isset($spreadsheetData))
            const spreadsheetData = @json($spreadsheetData);
            console.log('Datos cargados desde la base de datos:', spreadsheetData);
            // TEMPORALMENTE DESHABILITADO: populateTableWithData(spreadsheetData);
            console.log('⚠️ populateTableWithData() deshabilitado temporalmente para probar datos dinámicos');
        @else
            console.log('No hay datos guardados para cargar');
        @endif
    }
    
    function populateTableWithData(spreadsheetData) {
        // Iterar sobre todas las tablas y llenar con datos guardados
        Object.keys(spreadsheetData).forEach(tablaNombre => {
            const tabla = spreadsheetData[tablaNombre];
            
            Object.keys(tabla).forEach(concepto => {
                const conceptoData = tabla[concepto];
                
                Object.keys(conceptoData).forEach(columna => {
                    const cellData = conceptoData[columna];
                    
                    // Buscar la celda correspondiente en el DOM
                    const cell = findCellInDOM(tablaNombre, concepto, columna);
                    if (cell && !cellData.es_total) {
                        // Solo llenar celdas no-totales (los totales se calculan)
                        cell.textContent = `$${formatNumber(cellData.valor)}`;
                        cell.style.backgroundColor = '#e8f5e8'; // Indicar que viene de BD
                    }
                });
            });
        });
        
        // Recalcular totales después de cargar datos
        setTimeout(() => {
            calculateAllTotals();
        }, 500);
    }
    
    function findCellInDOM(tablaNombre, concepto, columna) {
        // Buscar la sección de presupuesto correspondiente
        const sections = document.querySelectorAll('.budget-section');
        
        for (let section of sections) {
            const titleElement = section.querySelector('h5');
            if (titleElement && titleElement.textContent.trim() === tablaNombre) {
                const table = section.querySelector('table');
                if (table) {
                    // Buscar la fila con el concepto
                    const rows = table.querySelectorAll('tbody tr');
                    for (let row of rows) {
                        const firstCell = row.querySelector('td');
                        if (firstCell && firstCell.textContent.trim().replace(/\*\*/g, '') === concepto) {
                            // Encontrar la columna correspondiente
                            const headerCells = table.querySelectorAll('thead th');
                            for (let i = 0; i < headerCells.length; i++) {
                                if (headerCells[i].textContent.trim() === columna) {
                                    const targetCell = row.querySelectorAll('td')[i];
                                    if (targetCell && targetCell.classList.contains('number-cell')) {
                                        return targetCell;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return null;
    }
    
    // Cargar datos al inicializar la página
    // TEMPORALMENTE DESHABILITADO: loadDataFromDatabase();
    console.log('⚠️ loadDataFromDatabase() deshabilitado temporalmente para probar datos dinámicos');
    
    // Inicializar sistema completo después de cargar datos
    setTimeout(() => {
        console.log('🚀 Inicializando sistema completo...');
        
        // Solo inicializar auto-cálculo si ya hay modo editable activado
        const editButton = document.querySelector('[onclick="toggleEditable()"]');
        if (editButton && editButton.textContent.includes('Solo Lectura')) {
            console.log('📝 Modo editable detectado, inicializando auto-cálculo...');
            initializeAutoCalculation();
            initializeStatElements();
        } else {
            console.log('📖 Modo solo lectura, esperando activación manual');
        }
    }, 1000);
    
    // Función de test para debug
    window.testSaveFunction = function() {
        console.log('🧪 Iniciando test de función de guardado...');
        
        // Simular datos de celda
        const testCellData = {
            tabla_nombre: 'Test Table',
            concepto: 'Test Concepto',
            columna: 'Enero',
            valor: 12345,
            fila_orden: 1,
            columna_orden: 1,
            es_total: false
        };
        
        // Crear elemento de prueba
        const testElement = document.createElement('div');
        testElement.textContent = '$12,345';
        
        console.log('🔢 Datos de prueba:', testCellData);
        saveCellToDatabase(testCellData, testElement);
    };
    
    // Función para test directo de edición
    window.testDirectEdit = function() {
        console.log('🧪 Test directo de edición...');
        
        // Buscar la primera celda editable
        const firstCell = document.querySelector('.number-cell:not(.total-row .number-cell)');
        if (firstCell) {
            console.log('🎯 Celda encontrada:', firstCell.textContent);
            
            // Hacer la celda editable
            firstCell.contentEditable = true;
            firstCell.style.backgroundColor = '#fff3cd';
            
            // Simular edición
            firstCell.textContent = '$99,999';
            
            // Extraer datos
            const cellData = extractCellData(firstCell);
            console.log('📊 Datos extraídos:', cellData);
            
            if (cellData) {
                saveCellToDatabase(cellData, firstCell);
            }
        } else {
            console.log('❌ No se encontró celda editable');
        }
    };
    
    console.log('🎯 Funciones de test disponibles:');
    console.log('- testSaveFunction() - Prueba función de guardado');
    console.log('- testDirectEdit() - Prueba edición directa');

    // Filtro por mes para secciones
    const monthFilter = document.getElementById('monthFilter');
    const monthFilterStatus = document.getElementById('monthFilterStatus');
    
    if (monthFilter) {
        monthFilter.addEventListener('change', function() {
            const selectedMonth = this.value;
            const selectedText = this.options[this.selectedIndex].text;
            
            if (selectedMonth) {
                // Mostrar loading
                showLoadingForSections();
                
                // Actualizar estado del filtro
                monthFilterStatus.textContent = `Filtrando por: ${selectedText}`;
                
                // Realizar petición AJAX
                fetch('{{ route("presupuesto.filter-sections-by-month") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        month: selectedMonth
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSectionTables(data.data);
                        hideLoadingForSections();
                    } else {
                        console.error('Error al filtrar por mes:', data);
                        hideLoadingForSections();
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    hideLoadingForSections();
                });
            } else {
                // Reset - mostrar todos los meses
                monthFilterStatus.textContent = 'Mostrando todos los meses';
                location.reload(); // Recargar para mostrar todos los datos
            }
        });
    }

    function showLoadingForSections() {
        const tables = ['preescolar-table', 'escuela-media-table', 'escuela-alta-table'];
        tables.forEach(tableId => {
            const table = document.getElementById(tableId);
            if (table) {
                table.style.opacity = '0.5';
                table.style.pointerEvents = 'none';
            }
        });
    }

    function hideLoadingForSections() {
        const tables = ['preescolar-table', 'escuela-media-table', 'escuela-alta-table'];
        tables.forEach(tableId => {
            const table = document.getElementById(tableId);
            if (table) {
                table.style.opacity = '1';
                table.style.pointerEvents = 'auto';
            }
        });
    }

    function updateSectionTables(data) {
        // Actualizar tabla Preescolar y Primaria
        updateSectionTable('preescolar-tbody', data['PREESCOLAR Y PRIMARIA'] || {}, 'preescolar');
        
        // Actualizar tabla Escuela Media
        updateSectionTable('escuela-media-tbody', data['ESCUELA MEDIA'] || {}, 'escuela-media');
        
        // Actualizar tabla Escuela Alta
        updateSectionTable('escuela-alta-tbody', data['ALTA'] || {}, 'escuela-alta');
    }

    function updateSectionTable(tbodyId, sectionData, sectionKey) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        // Limpiar tabla
        tbody.innerHTML = '';

        let totalPresupuesto = 0;
        let totalEjecucion = 0;
        let totalSaldo = 0;

        // Agregar filas de datos
        Object.keys(sectionData).forEach(concepto => {
            const datos = sectionData[concepto];
            const presupuesto = datos.presupuesto || 0;
            const ejecutado = datos.ejecutado || 0;
            const saldo = datos.saldo || 0;

            totalPresupuesto += presupuesto;
            totalEjecucion += ejecutado;
            totalSaldo += saldo;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${concepto}</td>
                <td class="number-cell" data-section="${sectionKey}" data-concept="${concepto}" data-type="presupuesto">${formatNumber(presupuesto)}</td>
                <td class="number-cell editable" data-section="${sectionKey}" data-concept="${concepto}" data-type="ejecucion">${formatNumber(ejecutado)}</td>
                <td class="number-cell calculated ${saldo < 0 ? 'negative' : ''}">${formatNumber(saldo)}</td>
            `;
            tbody.appendChild(row);
        });

        // Agregar fila total
        const totalRow = document.createElement('tr');
        totalRow.className = 'total-row';
        totalRow.innerHTML = `
            <td><strong>TOTAL</strong></td>
            <td class="number-cell total-presupuesto"><strong>${formatNumber(totalPresupuesto)}</strong></td>
            <td class="number-cell total-ejecucion"><strong>${formatNumber(totalEjecucion)}</strong></td>
            <td class="number-cell total-saldo ${totalSaldo < 0 ? 'negative' : ''}"><strong>${formatNumber(totalSaldo)}</strong></td>
        `;
        tbody.appendChild(totalRow);
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('es-CO').format(number);
    }

    // Hacer funciones globales
    window.updateSectionTables = updateSectionTables;
    window.updateSectionTable = updateSectionTable;
    window.formatNumber = formatNumber;
});
</script>
@stop
