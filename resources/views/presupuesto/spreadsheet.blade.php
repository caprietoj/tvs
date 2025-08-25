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
                    @if(isset($presupuestoItems))
                        {{ number_format($presupuestoItems->sum('valor'), 0, ',', '.') }}
                    @else
                        0
                    @endif
                </div>
                <div class="stat-label">Presupuesto Total</div>
            </div>
        </div>
        
        <div class="stat-box stat-box-success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalAlumnos">1,248</div>
                <div class="stat-label">Total de Alumnos</div>
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
                                            <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '-' }}</td>
                                            <td class="number-cell">{{ number_format($item->valor ?? 0, 0, ',', '.') }}</td>
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
                                        <td colspan="8" class="no-data">No hay datos de presupuesto disponibles</td>
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
                                    </thead>
                                    <tbody id="preescolar-tbody">
                                        @php
                                            $seccion = 'PREESCOLAR Y PRIMARIA';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody id="escuela-media-tbody">
                                        @php
                                            $seccion = 'ESCUELA MEDIA';
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
                                    </thead>
                                    <tbody id="escuela-alta-tbody">
                                        @php
                                            $seccion = 'ALTA';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccion = 'PAI';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccion = 'PEP';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccion = 'DEPORTES';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccion = 'BIBLIOTECA';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                    </thead>
                                    <tbody>
                                        @php
                                            $seccion = 'PSICOLOGÍA INSTITUCIONAL';
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
                                            <td class="number-cell total-presupuesto"><strong>{{ number_format($totalPresupuesto, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-ejecucion"><strong>{{ number_format($totalEjecucion, 0, ',', '.') }}</strong></td>
                                            <td class="number-cell total-saldo {{ $totalSaldo < 0 ? 'negative' : '' }}"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

                        <!-- TABLA 1: RESUMEN -->
                        <div class="budget-section filter-resumen" data-filter-category="resumen">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RESUMEN</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>RESUMEN</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td class="number-cell">$12.856.980.087</td>
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
                                        </tr>
                                        <tr>
                                            <td><strong>Total Egresos</strong></td>
                                            <td class="number-cell"></td>
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
                                        </tr>
                                        <tr>
                                            <td><strong>Total Salarios, Prestaciones Administrativos y Servicios</strong></td>
                                            <td class="number-cell">$1.453.226.337</td>
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
                                        </tr>
                                        <tr>
                                            <td><strong>Institucional e Institucional academia</strong></td>
                                            <td class="number-cell">$1.172.440.107</td>
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
                                            <td><strong>Sección Academia</strong></td>
                                            <td class="number-cell">$481.271.150</td>
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
                                            <td><strong>Servicios Públicos y Otros Egresos</strong></td>
                                            <td class="number-cell">$2.594.069.715</td>
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
                                            <td><strong>Proyectado Costos Contratos Externos</strong></td>
                                            <td class="number-cell">$1.831.454.774</td>
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
                                            <td><strong>Salarios</strong></td>
                                            <td class="number-cell">$4.216.589.763</td>
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
                                            <td><strong>Bono Dirección Sección</strong></td>
                                            <td class="number-cell">$134.432.647</td>
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
                                            <td><strong>Bono Sábados</strong></td>
                                            <td class="number-cell">$52.026.480</td>
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
                                            <td><strong>Pago de Agosto</strong></td>
                                            <td class="number-cell">$4.568.188</td>
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
                                            <td><strong>Medicina Prepagada</strong></td>
                                            <td class="number-cell">$57.058.480</td>
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
                                            <td><strong>Costo Vivienda-instalación profesores</strong></td>
                                            <td class="number-cell">$0</td>
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
                                            <td><strong>Bono Extralectiva</strong></td>
                                            <td class="number-cell">$63.258.765</td>
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
                                            <td><strong>Bono de Rendimiento</strong></td>
                                            <td class="number-cell">$30.000.000</td>
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
                                            <td><strong>Bono Director de Grupo</strong></td>
                                            <td class="number-cell">$45.551.527</td>
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
                                            <td><strong>Bono Lider Académico (EFES OPTO)</strong></td>
                                            <td class="number-cell">$26.019.336</td>
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
                                            <td><strong>Bono Proyecto Personal</strong></td>
                                            <td class="number-cell">$7.434.096</td>
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
                                            <td><strong>Bono Monografía</strong></td>
                                            <td class="number-cell">$0</td>
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
                                            <td><strong>Bono MUN</strong></td>
                                            <td class="number-cell">$3.717.048</td>
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
                                            <td><strong>Bono CAS</strong></td>
                                            <td class="number-cell">$3.717.048</td>
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
                                            <td><strong>Bono Anuario /Vídeos TVS/ ANUARIO</strong></td>
                                            <td class="number-cell">$0</td>
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
                                            <td><strong>Bono Consejo Estudiantil</strong></td>
                                            <td class="number-cell">$7.434.096</td>
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
                                            <td><strong>Prima</strong></td>
                                            <td class="number-cell">$358.257.482</td>
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
                                            <td><strong>Vacaciones</strong></td>
                                            <td class="number-cell">$383.326.342</td>
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
                                            <td><strong>Cesantías</strong></td>
                                            <td class="number-cell">$358.257.482</td>
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
                                            <td><strong>Intereses Cesantías</strong></td>
                                            <td class="number-cell">$42.990.898</td>
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
                                            <td><strong>Seguridad Social</strong></td>
                                            <td class="number-cell">$612.204.790</td>
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
                                            <td><strong>Aportes Parafiscales</strong></td>
                                            <td class="number-cell">$191.786.134</td>
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
                                            <td><strong>Viáticos</strong></td>
                                            <td class="number-cell">$2.120.000</td>
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
                                            <td><strong>TOTAL SALARIOS ACADEMIA</strong></td>
                                            <td class="number-cell calculated"><strong>$6.600.750.523</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
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
                                            <td><strong>Salarios y Aux de Transporte- administración y servicios generales</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Capacitacion administracion</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Aprendices Sena</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL SALARIOS ADMINISTRATIVOS</strong></td>
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

                        <!-- TABLA 4.2: CAPACITACION E INDEMNIZACIONES -->
                        <div class="budget-section filter-salarios" data-filter-category="salarios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">CAPACITACION E INDEMNIZACIONES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Capacitación admin</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Capacitación EMC/Docentes</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Capacitacin COPASST (brigadas cruz roja, bomberos)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Indemnizaciones</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL CAPACITACION E INDEMNIZACIONES</strong></td>
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

                        <!-- TABLA 5: RUBROS INSTITUCIONALES -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">RUBROS INSTITUCIONALES</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Equipos y Dotacion Salones y/o oficinas</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Examenes Médicos (periodicos y de contratacion)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Tecnologia institucional</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Insumos enfermeria escolar</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Mercadeo y admisiones</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Eventos Institucionales de Comunidad</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Mantenimiento general</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Reparaciones mayores (construcciones)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Reparación de Muebles</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Utiles de Oficna y Papeleria (ABKA)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Elementos de Aseo y Cafeteria</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Gastos de Agasajos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Bienestar institucional</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Eventos institucionales internos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Gastos de contratación (pruebas psicotecnicas, plataforma de computrabajo,visitas y poligrafos, anuncios empleo)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Afiliaciones e Inscripciones</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL INSTITUCIONAL</strong></td>
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

                        <!-- TABLA 6: MEMBRESIAS Y CONVENIOS -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">MEMBRESIAS Y CONVENIOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Bachillerato Internacional</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>ACCBI</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>RED PAPAZ</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL INSTITUCIONAL ACADEMIA</strong></td>
                                            <td class="number-cell calculated">0</td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
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

                        <!-- TABLA 7: SERVICIOS PUBLICOS -->
                        <div class="budget-section filter-servicios" data-filter-category="servicios">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SERVICIOS PUBLICOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Agua</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Energia</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Teléfono</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Vigilancia (METROS CUADRADOS PORTERO)</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Internet/ Arrendamientos Tecnológicos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL SERVICIOS PUBLICOS</strong></td>
                                            <td class="number-cell calculated">0</td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
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

                        <!-- TABLA 8: OTROS EGRESOS -->
                        <div class="budget-section filter-gastos" data-filter-category="gastos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">OTROS EGRESOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Honorarios</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Legales (sanciones UGPP) càmara de comercio</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Agenda</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Seguros Generales</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Anuario</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Comisiones Bancarias</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Mensajería y Acarreos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Miscelaneos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Impto de Industria y Comercio</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Plan de seguridad y Salud en el trabajo</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Otros Egresos Retención</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Impto de renta</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Arrendamientos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL OTROS EGRESOS</strong></td>
                                            <td class="number-cell calculated">0</td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
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

                        <!-- TABLA 9: SECCIONES ACADEMIA GENERAL -->
                        <div class="budget-section filter-academia" data-filter-category="academia">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">SECCIONES ACADEMIA GENERAL</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Capacitación</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Gastos Importacion/Material Importado</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Textos y Utiles de Consumo</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Biblioteca institucional</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Materiales para clases</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Material Deportivo</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Musicales</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Part time teacher- reemplazos</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Insumos institucionales de Seccion (Tecnologia )</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>PEP</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>DP</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>PAI</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Departamento de Apoyo</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Consejeria Universitaria</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Direcciòn general</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL SECCIONES ACADEMIA GENERAL</strong></td>
                                            <td class="number-cell calculated">0</td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
                                            <td class="number-cell calculated"><strong>$-</strong></td>
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

                        <!-- TABLA 10: CONTRATOS EXTERNOS -->
                        <div class="budget-section filter-contratos" data-filter-category="contratos">
                            <h5 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin: 20px 0; padding: 12px 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e9ecef;">CONTRATOS EXTERNOS</h5>
                            <div class="table-wrapper">
                                <table class="data-table budget-table">
                                    <thead>
                                        <tr>
                                            <th>CONCEPTO</th>
                                            <th>PRESUPUESTO APROBADO</th>
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
                                            <td><strong>Cafeteria</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>Transporte</strong></td>
                                            <td class="number-cell editable">0</td>
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
                                            <td><strong>TOTAL CONTRATOS EXTERNOS</strong></td>
                                            <td class="number-cell calculated">0</td>
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
#toggle-editable, #recalculate-totals, #save-data {
    transition: all 0.3s ease;
}

#toggle-editable:hover, #recalculate-totals:hover, #save-data:hover {
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

#recalculate-totals:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
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
</style>
@stop

@section('js')
<script>
// Datos del servidor
const serverData = @json(isset($presupuestoItems) ? $presupuestoItems->values() : []);

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentSheet = 'BUDGET';
    let sortOrder = {};
    let currentPage = 1;
    let itemsPerPage = 50;
    let allItems = [];
    let filteredItems = [];
    let isLoading = false;
    
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
    const recalculateButton = document.getElementById('recalculate-totals');
    const debugButton = document.getElementById('debug-structure');
    
    let isEditableMode = false;
    
    console.log('Elementos encontrados:', {
        filterDropdown: !!filterDropdown,
        resetButton: !!resetButton,
        filterStatus: !!filterStatus,
        toggleEditableButton: !!toggleEditableButton,
        editableStatus: !!editableStatus,
        recalculateButton: !!recalculateButton,
        debugButton: !!debugButton
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
    
    // Manejo del botón para recalcular totales
    if (recalculateButton) {
        recalculateButton.addEventListener('click', function() {
            console.log('Botón recalcular presionado');
            
            // Cambiar el texto del botón temporalmente
            const originalText = this.textContent;
            this.textContent = '🔄 Calculando...';
            this.disabled = true;
            
            // Recalcular después de un pequeño delay para mostrar el feedback
            setTimeout(() => {
                calculateAllTotals();
                
                // Restaurar el botón
                this.textContent = originalText;
                this.disabled = false;
                
                // Mostrar confirmación temporal
                this.textContent = '✅ ¡Calculado!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 1500);
            }, 200);
        });
    } else {
        console.error('No se encontró el elemento recalculate-totals');
    }
    
    // Manejo del botón de debug
    if (debugButton) {
        debugButton.addEventListener('click', function() {
            console.log('🔍 Botón debug presionado');
            debugTableStructure();
        });
    } else {
        console.error('No se encontró el elemento debug-structure');
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
        
        // Buscar todas las celdas que pueden ser editables
        const allNumberCells = document.querySelectorAll('.number-cell');
        
        console.log('Celdas numéricas encontradas:', allNumberCells.length);
        
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
                    const cellData = extractCellData(this);
                    if (cellData) {
                        console.log('🔢 Datos extraídos:', cellData);
                        saveCellToDatabase(cellData, this);
                    } else {
                        console.log('❌ No se pudieron extraer datos de la celda');
                    }
                    calculateTableTotals(this);
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
                // Agregar debugging de estructura de tablas
                debugTableStructure();
            }, 100);
        }
    }
    
    // Función de debugging para entender la estructura de las tablas
    function debugTableStructure() {
        console.log('=== DEBUG: Estructura de Tablas ===');
        
        const budgetTables = document.querySelectorAll('.budget-table');
        
        budgetTables.forEach((table, tableIndex) => {
            console.log(`\nTabla ${tableIndex + 1}:`);
            
            // Headers
            const headers = table.querySelectorAll('thead th');
            console.log('Headers:', Array.from(headers).map((h, i) => `[${i}] ${h.textContent.trim()}`));
            
            // Primera fila de datos
            const firstDataRow = table.querySelector('tbody tr:not(.total-row)');
            if (firstDataRow) {
                const dataCells = firstDataRow.querySelectorAll('td');
                console.log('Primera fila datos:', Array.from(dataCells).map((c, i) => `[${i}] ${c.textContent.trim()} (${c.classList.toString()})`));
            }
            
            // Fila de totales
            const totalRow = table.querySelector('.total-row');
            if (totalRow) {
                const totalCells = totalRow.querySelectorAll('td');
                console.log('Fila totales:', Array.from(totalCells).map((c, i) => `[${i}] ${c.textContent.trim()} (${c.classList.toString()})`));
            }
        });
        
        console.log('=== FIN DEBUG ===\n');
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
                    }, 3000);
                }
                
                // Agregar indicador visual a todas las celdas guardadas
                editableCells.forEach(cell => {
                    cell.style.borderLeft = '3px solid #28a745';
                    setTimeout(() => {
                        cell.style.borderLeft = '';
                    }, 5000);
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
                    }, 3000);
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
                }, 3000);
            }
        });
    }
    function extractCellData(cell) {
        const row = cell.closest('tr');
        const table = cell.closest('table');
        const budgetSection = cell.closest('.budget-section');
        
        if (!row || !table) {
            console.error('❌ No se pudo encontrar fila o tabla para la celda (función duplicada)');
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
        
        console.log('📊 Celda individual - Concepto:', concepto, 'Columna:', columna, 'Valor:', valor);
        return cellData;
    }
    
    function saveCellToDatabase(cellData, cellElement) {
        // No guardar celdas de totales (se calculan automáticamente)
        if (cellData.es_total) {
            console.log('Saltando guardado de celda de total');
            return;
        }
        
        console.log('🔄 Iniciando guardado de celda:', cellData);
        
        // Verificar CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('❌ Token CSRF no encontrado');
            return;
        }
        
        console.log('🔑 Token CSRF encontrado:', csrfToken.getAttribute('content').substring(0, 10) + '...');
        
        // Añadir indicador visual de guardado
        cellElement.style.borderLeft = '3px solid #007bff';
        
        // Enviar datos al servidor
        fetch('/contabilidad/presupuesto/guardar-celda', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify(cellData)
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
                console.log('✅ Celda guardada exitosamente:', data.message);
                // Indicador visual de éxito
                cellElement.style.borderLeft = '3px solid #28a745';
                setTimeout(() => {
                    cellElement.style.borderLeft = '';
                }, 2000);
            } else {
                console.error('❌ Error al guardar celda:', data.message);
                // Indicador visual de error
                cellElement.style.borderLeft = '3px solid #dc3545';
                setTimeout(() => {
                    cellElement.style.borderLeft = '';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('❌ Error de conexión al guardar celda:', error);
            // Indicador visual de error
            cellElement.style.borderLeft = '3px solid #dc3545';
            setTimeout(() => {
                cellElement.style.borderLeft = '';
            }, 3000);
        });
    }
    
    // Función para cargar datos desde la base de datos
    function loadDataFromDatabase() {
        // Los datos ya vienen desde el servidor con la variable $spreadsheetData
        @if(isset($spreadsheetData))
            const spreadsheetData = @json($spreadsheetData);
            console.log('Datos cargados desde la base de datos:', spreadsheetData);
            populateTableWithData(spreadsheetData);
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
    loadDataFromDatabase();
    
    // Inicializar sistema completo después de cargar datos
    setTimeout(() => {
        console.log('🚀 Inicializando sistema completo...');
        
        // Solo inicializar auto-cálculo si ya hay modo editable activado
        const editButton = document.querySelector('[onclick="toggleEditable()"]');
        if (editButton && editButton.textContent.includes('Solo Lectura')) {
            console.log('📝 Modo editable detectado, inicializando auto-cálculo...');
            initializeAutoCalculation();
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
