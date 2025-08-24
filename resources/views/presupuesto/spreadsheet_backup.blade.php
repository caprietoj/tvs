@extends('adminlte::page')

@section('title', 'Presupuesto - Hoja de Cálculo')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-table text-primary"></i>
                        Presupuesto - Hoja de Cálculo
                    </h1>
                    <small class="text-muted">Sistema de gestión presupuestaria</small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/home">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presupuesto.index') }}">Presupuesto</a></li>
                        <li class="breadcrumb-item active">Hoja de Cálculo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Menú Hamburguesa para Hojas -->
                    <div class="sheet-menu-container">
                        <button class="sheet-menu-btn" id="sheetMenuBtn" title="Seleccionar hoja">
                            <i class="fas fa-bars"></i>
                            <span class="current-sheet-name">BUDGET</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="sheet-dropdown" id="sheetDropdown">
                            <div class="sheet-dropdown-header">
                                <h6><i class="fas fa-table"></i> Hojas de Presupuesto</h6>
                            </div>
                            <div class="sheet-list">
                                @foreach($sheets as $key => $label)
                                    <div class="sheet-item {{ $loop->first ? 'active' : '' }}" 
                                         data-sheet="{{ $key }}" 
                                         data-color="{{ $loop->index }}">
                                        <div class="sheet-info">
                                            <div class="sheet-name">{{ $key }}</div>
                                            <div class="sheet-description">{{ $label }}</div>
                                        </div>
                                        <div class="sheet-status">
                                            @if($loop->first)
                                                <i class="fas fa-check text-success"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="sheet-dropdown-footer">
                                <button class="btn btn-sm btn-outline-primary w-100" onclick="addNewSheet()">
                                    <i class="fas fa-plus"></i> Nueva Hoja
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formula Bar -->
                    <div class="formula-bar">
                        <div class="cell-reference">
                            <input type="text" id="cell-ref" readonly placeholder="A1">
                        </div>
                        <div class="formula-input">
                            <input type="text" id="formula-input" placeholder="Ingrese valor o fórmula...">
                        </div>
                        <div class="formula-controls">
                            <a href="{{ route('presupuesto.items') }}" class="btn btn-sm btn-outline-primary" title="Procesador Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <button class="btn btn-sm btn-outline-success" onclick="saveData()" title="Guardar datos">
                                <i class="fas fa-save"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="exportToExcel()" title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar para carga de datos -->
                    <div id="dataLoadProgress" class="data-load-progress" style="display: none;">
                        <div class="progress-info">
                            <span id="loadProgressText">Todos los datos cargados</span>
                            <span id="loadProgressCount">2219/2219 registros</span>
                        </div>
                        <div class="progress">
                            <div id="loadProgressBar" class="progress-bar progress-bar-striped" 
                                 style="width: 100%"></div>
                        </div>
                    </div>

                    <!-- Spreadsheet Container -->
                    <div class="spreadsheet-container">
                        @foreach($sheets as $key => $label)
                            <div class="sheet-content {{ $loop->first ? 'active' : '' }}" id="sheet-{{ $key }}">
                                <div class="table-responsive">
                                    <table class="excel-table">
                                        <thead>
                                            <tr>
                                                <th class="row-header"></th>
                                                @for($col = 1; $col <= 20; $col++)
                                                    <th class="col-header filter-header" data-col="{{ $col }}">
                                                        <div class="header-content">
                                                            <span class="header-text">{{ chr(64 + $col) }}</span>
                                                            <div class="filter-dropdown-container">
                                                                <span class="filter-icon" data-col="{{ $col }}" title="Filtrar columna">▼</span>
                                                                <div class="filter-dropdown" id="filter-dropdown-{{ $col }}" style="display: none;">
                                                                    <div class="filter-search">
                                                                        <input type="text" class="filter-search-input" placeholder="Buscar..." data-col="{{ $col }}">
                                                                    </div>
                                                                    <div class="filter-options">
                                                                        <div class="filter-option">
                                                                            <input type="checkbox" id="select-all-{{ $col }}" checked>
                                                                            <label for="select-all-{{ $col }}">Seleccionar todo</label>
                                                                        </div>
                                                                        <div class="filter-option">
                                                                            <input type="checkbox" id="blanks-{{ $col }}" checked>
                                                                            <label for="blanks-{{ $col }}">(En blanco)</label>
                                                                        </div>
                                                                        <div class="filter-values" id="filter-values-{{ $col }}">
                                                                            <!-- Se llenará dinámicamente -->
                                                                        </div>
                                                                    </div>
                                                                    <div class="filter-actions">
                                                                        <button class="btn btn-sm btn-primary apply-filter" data-col="{{ $col }}">Aplicar</button>
                                                                        <button class="btn btn-sm btn-secondary clear-filter" data-col="{{ $col }}">Limpiar</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                if($key === 'Detallado secciones1') {
                                                    // Para la hoja detallada, solo generar filas que tienen datos + algunas extra
                                                    $actualMaxRow = 1;
                                                    if(isset($optimizedData[$key])) {
                                                        $actualMaxRow = max(array_keys($optimizedData[$key]));
                                                    }
                                                    $dynamicMaxRows = $actualMaxRow + 50; // Filas con datos + 50 extra para editar
                                                } else {
                                                    $dynamicMaxRows = 50; // Para otras hojas, mantener 50
                                                }
                                            @endphp
                                            @for($row = 1; $row <= $dynamicMaxRows; $row++)
                                                @php
                                                    // Detectar si es una fila TOTAL
                                                    $isTotalRow = false;
                                                    $rowClass = '';
                                                    if(isset($optimizedData[$key][$row][6])) { // Columna 6 es "Rubro"
                                                        if($optimizedData[$key][$row][6] === 'TOTAL') {
                                                            $isTotalRow = true;
                                                            $rowClass = 'total-row';
                                                        }
                                                    }
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td class="row-header {{ $isTotalRow ? 'total-cell' : '' }}">{{ $row }}</td>
                                                    @for($col = 1; $col <= 20; $col++)
                                                        @php
                                                            $cellData = '';
                                                            if(isset($optimizedData[$key][$row][$col])) {
                                                                $cellData = $optimizedData[$key][$row][$col];
                                                            }
                                                        @endphp
                                                        <td class="cell {{ $isTotalRow ? 'total-cell' : '' }}" 
                                                            data-row="{{ $row }}" 
                                                            data-col="{{ $col }}" 
                                                            data-sheet="{{ $key }}"
                                                            contenteditable="true">{{ $cellData }}</td>
                                                    @endfor
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Barra de estado simple -->
                    <div class="spreadsheet-status-bar">
                        <div class="status-info">
                            <span class="current-sheet-indicator">
                                <i class="fas fa-table"></i>
                                <span id="currentSheetDisplay">BUDGET</span>
                            </span>
                            <span class="cell-count-info">
                                <i class="fas fa-th"></i>
                                <span id="cellCountDisplay">20 x 10</span>
                            </span>
                        </div>
                        <div class="zoom-controls">
                            <button class="btn btn-sm btn-outline-secondary" onclick="adjustZoom(-10)" title="Alejar">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <span class="zoom-indicator">100%</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="adjustZoom(10)" title="Acercar">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div id="context-menu" class="context-menu">
    <div class="menu-item" onclick="insertRow()">
        <i class="fas fa-plus"></i> Insertar fila
    </div>
    <div class="menu-item" onclick="insertColumn()">
        <i class="fas fa-plus"></i> Insertar columna
    </div>
    <div class="menu-item" onclick="deleteRow()">
        <i class="fas fa-minus"></i> Eliminar fila
    </div>
    <div class="menu-item" onclick="deleteColumn()">
        <i class="fas fa-minus"></i> Eliminar columna
    </div>
    <hr>
    <div class="menu-item" onclick="copySelection()">
        <i class="fas fa-copy"></i> Copiar
    </div>
    <div class="menu-item" onclick="cutSelection()">
        <i class="fas fa-cut"></i> Cortar
    </div>
    <div class="menu-item" onclick="pasteSpecial()">
        <i class="fas fa-paste"></i> Pegado especial...
    </div>
    <div class="menu-item" onclick="pasteValues()">
        <i class="fas fa-clipboard"></i> Pegar solo valores
    </div>
    <div class="menu-item" onclick="pasteFormats()">
        <i class="fas fa-paint-brush"></i> Pegar solo formatos
    </div>
</div>

<!-- Paste Special Dialog -->
<div id="paste-special-dialog" class="paste-special-dialog" style="display: none;">
    <div class="dialog-content">
        <div class="dialog-header">
            <h5><i class="fas fa-paste"></i> Pegado especial</h5>
            <button type="button" class="close-btn" onclick="closePasteSpecialDialog()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="dialog-body">
            <div class="paste-options">
                <h6>Pegar:</h6>
                <div class="option-group">
                    <label class="option-item">
                        <input type="radio" name="paste-what" value="all" checked>
                        <span>Todo</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-what" value="values">
                        <span>Valores</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-what" value="formats">
                        <span>Formatos</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-what" value="formulas">
                        <span>Fórmulas</span>
                    </label>
                </div>
                
                <h6>Operación:</h6>
                <div class="option-group">
                    <label class="option-item">
                        <input type="radio" name="paste-operation" value="none" checked>
                        <span>Ninguna</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-operation" value="add">
                        <span>Sumar</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-operation" value="subtract">
                        <span>Restar</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-operation" value="multiply">
                        <span>Multiplicar</span>
                    </label>
                    <label class="option-item">
                        <input type="radio" name="paste-operation" value="divide">
                        <span>Dividir</span>
                    </label>
                </div>

                <div class="checkbox-options">
                    <label class="checkbox-item">
                        <input type="checkbox" name="skip-blanks">
                        <span>Omitir celdas en blanco</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="transpose">
                        <span>Transponer</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-primary" onclick="executePasteSpecial()">
                Aceptar
            </button>
            <button type="button" class="btn btn-secondary" onclick="closePasteSpecialDialog()">
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Clipboard Monitor (hidden) -->
<textarea id="clipboard-monitor" style="position: absolute; left: -9999px; opacity: 0;" readonly></textarea>
@stop

@section('css')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* ============ MENÚ HAMBURGUESA PARA HOJAS ============ */
.sheet-menu-container {
    position: relative;
    display: inline-block;
    z-index: 1000;
    margin-bottom: 10px;
}

.sheet-menu-btn {
    background: #6c757d;
    border: 1px solid #ced4da;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 180px;
    justify-content: space-between;
}

.sheet-menu-btn:hover {
    background: #5a6268;
    transform: none;
    box-shadow: none;
}

.sheet-menu-btn:active {
    transform: none;
    background: #495057;
}

.current-sheet-name {
    flex: 1;
    text-align: left;
    font-weight: 600;
}

.sheet-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #e0e6ed;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 320px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1001;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.sheet-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.sheet-dropdown-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 12px 16px;
    border-bottom: 1px solid #e0e6ed;
    border-radius: 12px 12px 0 0;
}

.sheet-dropdown-header h6 {
    margin: 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sheet-list {
    max-height: 280px;
    overflow-y: auto;
    padding: 8px 0;
}

.sheet-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    gap: 12px;
    border-left: 3px solid transparent;
}

.sheet-item:hover {
    background: #f8f9fa;
    border-left-color: #007bff;
}

.sheet-item.active {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left-color: #2196f3;
    font-weight: 600;
}

.sheet-info {
    flex: 1;
    min-width: 0;
}

.sheet-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 2px;
}

.sheet-description {
    color: #6c757d;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sheet-status {
    flex-shrink: 0;
    width: 20px;
    display: flex;
    justify-content: center;
}

.sheet-dropdown-footer {
    padding: 12px 16px;
    border-top: 1px solid #e0e6ed;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

/* ============ BARRA DE ESTADO ============ */
.spreadsheet-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid #dee2e6;
    padding: 8px 16px;
    font-size: 12px;
    color: #6c757d;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.current-sheet-indicator,
.cell-count-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.zoom-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.zoom-indicator {
    font-weight: 600;
    color: #495057;
    min-width: 40px;
    text-align: center;
}

/* ============ ESTILOS EXCEL EXISTENTES ============ */
.excel-tabs-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 4px 8px;
    position: sticky;
    bottom: 0;
    z-index: 20;
    box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
}

.excel-tabs-container.bottom {
    border-bottom: 1px solid #dee2e6;
}

.tabs-navigation {
    display: flex;
    align-items: center;
    flex: 1;
    overflow: hidden;
}

.tab-scroll-btn {
    background: #e9ecef;
    border: 1px solid #ced4da;
    color: #495057;
    padding: 2px 6px;
    cursor: pointer;
    font-size: 10px;
    margin: 0 2px;
    height: 20px;
    width: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tab-scroll-btn:hover {
    background: #f8f9fa;
}

.excel-tabs {
    display: flex;
    gap: 1px;
    flex: 1;
    overflow-x: auto;
    scroll-behavior: smooth;
    max-width: calc(100% - 100px);
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.excel-tabs::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

.tab-item {
    padding: 4px 8px;
    background: #e9ecef;
    border: 1px solid #ced4da;
    border-top: none;
    cursor: pointer;
    font-size: 10px;
    color: #495057;
    border-radius: 0 0 3px 3px;
    min-width: 60px;
    max-width: 120px;
    text-align: center;
    transition: all 0.2s ease;
    white-space: nowrap;
    position: relative;
    height: 20px;
    line-height: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    user-select: none;
}

.tab-item:hover {
    background: #f8f9fa;
    color: #343a40;
    transform: translateY(-1px);
}

.tab-item.active {
    background: #ffffff;
    color: #212529;
    font-weight: 600;
    border-top: 2px solid #007bff;
    z-index: 1;
    box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
}

/* Prevenir parpadeo durante el cambio */
.tab-item.switching {
    pointer-events: none;
    opacity: 0.7;
    transform: scale(0.98);
}

/* Transiciones suaves para las hojas */
.sheet-content {
    display: none;
    opacity: 0;
    transition: opacity 0.15s ease-in-out;
}

.sheet-content.active {
    display: block;
    opacity: 1;
}

/* Colores específicos para cada pestaña tipo Excel */
.tab-item[data-color="0"] { border-bottom: 3px solid #4472C4; } /* BUDGET - Azul */
.tab-item[data-color="1"] { border-bottom: 3px solid #70AD47; } /* Proyección - Verde */
.tab-item[data-color="2"] { border-bottom: 3px solid #FFC000; } /* Cap Admin - Amarillo */
.tab-item[data-color="3"] { border-bottom: 3px solid #ED7D31; } /* Secciones - Naranja */
.tab-item[data-color="4"] { border-bottom: 3px solid #A5A5A5; } /* Cap EMC - Gris */
.tab-item[data-color="5"] { border-bottom: 3px solid #5B9BD5; } /* Cap Brigadas - Azul claro */
.tab-item[data-color="6"] { border-bottom: 3px solid #264478; } /* Detallado secciones1 - Azul oscuro */
.tab-item[data-color="7"] { border-bottom: 3px solid #843C0C; } /* Equipo y Dotacion - Marrón */
.tab-item[data-color="8"] { border-bottom: 3px solid #548235; } /* Aseo y Cafeteria - Verde oscuro */
.tab-item[data-color="9"] { border-bottom: 3px solid #B2182B; } /* Dotaciones - Rojo */
.tab-item[data-color="10"] { border-bottom: 3px solid #762A83; } /* Agasajos - Morado */
.tab-item[data-color="11"] { border-bottom: 3px solid #2D7D32; } /* Tecnología - Verde tech */
.tab-item[data-color="12"] { border-bottom: 3px solid #FF6900; } /* Gts Contrat - Naranja intenso */
.tab-item[data-color="13"] { border-bottom: 3px solid #1976D2; } /* Afiliaciones - Azul corporativo */
.tab-item[data-color="14"] { border-bottom: 3px solid #388E3C; } /* IB - Verde IB */
.tab-item[data-color="15"] { border-bottom: 3px solid #7B1FA2; } /* Deportes - Morado deportivo */
.tab-item[data-color="16"] { border-bottom: 3px solid #E64A19; } /* Entrenamientos - Rojo naranja */
.tab-item[data-color="17"] { border-bottom: 3px solid #0288D1; } /* Servicios Públicos - Azul agua */
.tab-item[data-color="18"] { border-bottom: 3px solid #F57F17; } /* Reparaciones Mayores - Amarillo intenso */
.tab-item[data-color="19"] { border-bottom: 3px solid #5D4037; } /* Reparacion muebles - Café */
.tab-item[data-color="20"] { border-bottom: 3px solid #C2185B; } /* Mercadeo - Rosa */
.tab-item[data-color="21"] { border-bottom: 3px solid #303F9F; } /* Honorarios - Azul profesional */
.tab-item[data-color="22"] { border-bottom: 3px solid #689F38; } /* Auxiliar cc academia - Verde academia */
.tab-item[data-color="23"] { border-bottom: 3px solid #455A64; } /* Detallado secciones - Gris azulado */

.tab-item.active[data-color="0"] { background: #E7F1FF; border-color: #4472C4; color: #4472C4; }
.tab-item.active[data-color="1"] { background: #F2F8F0; border-color: #70AD47; color: #70AD47; }
.tab-item.active[data-color="2"] { background: #FFF9E6; border-color: #FFC000; color: #B8860B; }
.tab-item.active[data-color="3"] { background: #FEF1E8; border-color: #ED7D31; color: #ED7D31; }
.tab-item.active[data-color="4"] { background: #F5F5F5; border-color: #A5A5A5; color: #A5A5A5; }
.tab-item.active[data-color="5"] { background: #EBF4FD; border-color: #5B9BD5; color: #5B9BD5; }
.tab-item.active[data-color="6"] { background: #E8EDF7; border-color: #264478; color: #264478; }
.tab-item.active[data-color="7"] { background: #F0E6DC; border-color: #843C0C; color: #843C0C; }
.tab-item.active[data-color="8"] { background: #EEF4EC; border-color: #548235; color: #548235; }
.tab-item.active[data-color="9"] { background: #F8E6E8; border-color: #B2182B; color: #B2182B; }
.tab-item.active[data-color="10"] { background: #F2E6F2; border-color: #762A83; color: #762A83; }
.tab-item.active[data-color="11"] { background: #E8F5E8; border-color: #2D7D32; color: #2D7D32; }
.tab-item.active[data-color="12"] { background: #FFF0E6; border-color: #FF6900; color: #FF6900; }
.tab-item.active[data-color="13"] { background: #E3F2FD; border-color: #1976D2; color: #1976D2; }
.tab-item.active[data-color="14"] { background: #E8F5E8; border-color: #388E3C; color: #388E3C; }
.tab-item.active[data-color="15"] { background: #F3E5F5; border-color: #7B1FA2; color: #7B1FA2; }
.tab-item.active[data-color="16"] { background: #FBE9E7; border-color: #E64A19; color: #E64A19; }
.tab-item.active[data-color="17"] { background: #E1F5FE; border-color: #0288D1; color: #0288D1; }
.tab-item.active[data-color="18"] { background: #FFF8E1; border-color: #F57F17; color: #F57F17; }
.tab-item.active[data-color="19"] { background: #EFEBE9; border-color: #5D4037; color: #5D4037; }
.tab-item.active[data-color="20"] { background: #FCE4EC; border-color: #C2185B; color: #C2185B; }
.tab-item.active[data-color="21"] { background: #E8EAF6; border-color: #303F9F; color: #303F9F; }
.tab-item.active[data-color="22"] { background: #F1F8E9; border-color: #689F38; color: #689F38; }
.tab-item.active[data-color="23"] { background: #ECEFF1; border-color: #455A64; color: #455A64; }

.tab-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
    display: block;
}

.tab-item:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    z-index: 2;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tab-item:hover .tab-text {
    overflow: visible;
    white-space: normal;
    background: rgba(248, 249, 250, 0.95);
    padding: 2px 4px;
    border-radius: 2px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    min-width: max-content;
    max-width: 200px;
    z-index: 1000;
    border: 1px solid #ddd;
}

/* Regla general para pestañas activas - Los colores específicos la sobrescriben */
.tab-item.active {
    font-weight: 500;
    border-top: 2px solid;
    z-index: 1;
}

.tabs-controls {
    display: flex;
    gap: 2px;
    margin-left: 8px;
    flex-shrink: 0;
}

.tabs-controls .btn {
    height: 20px;
    width: 20px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    border-radius: 2px;
}

.spreadsheet-container {
    height: calc(100vh - 200px);
    overflow: auto;
    position: relative;
}

.sheet-content {
    display: none;
    height: 100%;
}

.sheet-content.active {
    display: block;
}

.excel-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-family: 'Calibri', sans-serif;
    font-size: 11px;
}

.excel-table th,
.excel-table td {
    border: 1px solid #d0d7de;
    padding: 0;
    margin: 0;
    position: relative;
}

.col-header,
.row-header {
    background: #f6f8fa;
    color: #656d76;
    font-weight: 600;
    text-align: center;
    min-width: 50px;
    height: 20px;
    line-height: 20px;
    font-size: 10px;
    user-select: none;
}

.col-header {
    position: sticky;
    top: 0;
    z-index: 10;
    position: relative;
}

/* Estilos para filtros en encabezados */
.filter-header {
    position: relative;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.header-text {
    flex: 1;
}

.filter-dropdown-container {
    position: relative;
    display: inline-block;
}

.filter-icon {
    font-size: 10px;
    color: #666;
    cursor: pointer;
    padding: 2px 4px;
    opacity: 0.7;
    transition: opacity 0.2s;
    user-select: none;
    display: inline-block;
    width: 12px;
    text-align: center;
    font-family: monospace;
    font-weight: bold;
}

.filter-icon:hover {
    opacity: 1;
    color: #007bff;
}

.filter-icon.active {
    color: #007bff;
    opacity: 1;
}

.filter-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    min-width: 200px;
    max-width: 300px;
    max-height: 300px;
    overflow: hidden;
}

.filter-search {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.filter-search-input {
    width: 100%;
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
}

.filter-options {
    max-height: 200px;
    overflow-y: auto;
    padding: 4px 0;
}

.filter-option {
    padding: 4px 8px;
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 12px;
}

.filter-option:hover {
    background-color: #f8f9fa;
}

.filter-option input[type="checkbox"] {
    margin-right: 6px;
}

.filter-option label {
    cursor: pointer;
    margin: 0;
    flex: 1;
}

.filter-actions {
    padding: 8px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 4px;
}

.filter-actions .btn {
    padding: 2px 8px;
    font-size: 11px;
}

/* Mejor apariencia para filas filtradas */
.excel-table tbody tr:hidden {
    display: none;
}

/* Estado de filtro activo */
#filter-status {
    border-left: 4px solid #007bff;
    background-color: #f8f9fa;
    font-size: 13px;
}

#filter-status .btn {
    font-size: 11px;
    padding: 2px 8px;
}

/* Mejorar scrollbar en opciones de filtro */
.filter-options::-webkit-scrollbar {
    width: 6px;
}

.filter-options::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.filter-options::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.filter-options::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.row-header {
    position: sticky;
    left: 0;
    z-index: 5;
    min-width: 40px;
}

.cell {
    min-width: 80px;
    height: 20px;
    padding: 2px 4px;
    outline: none;
    background: white;
    cursor: cell;
    white-space: nowrap;
    overflow: hidden;
    border-right: 1px solid #d0d7de;
    border-bottom: 1px solid #d0d7de;
}

.cell:focus {
    border: 2px solid #007bff;
    z-index: 1;
}

/* Estilos específicos para la hoja Detallado secciones1 */
.sheet-content[id="sheet-Detallado secciones1"] .cell[data-row="1"] {
    background-color: #007bff !important;
    color: white !important;
    font-weight: bold;
    text-align: center;
}

/* Hacer las celdas de encabezado un poco más anchas para esta hoja */
.sheet-content[id="sheet-Detallado secciones1"] .cell {
    min-width: 120px;
}

/* Estilos alternos para filas de datos */
.sheet-content[id="sheet-Detallado secciones1"] tr:nth-child(even) .cell:not([data-row="1"]) {
    background-color: #f8f9fa;
}

.sheet-content[id="sheet-Detallado secciones1"] tr:nth-child(odd) .cell:not([data-row="1"]) {
    background-color: white;
}
    background: #fff;
}

.cell.selected {
    background: #e3f2fd;
}

.cell[data-row="1"] {
    background: #f8f9fa;
    font-weight: bold;
    color: #495057;
}

.formula-bar {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 5px;
    position: sticky;
    top: 0;
    z-index: 20;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cell-reference {
    width: 80px;
    margin-right: 10px;
}

.cell-reference input {
    width: 100%;
    height: 24px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    text-align: center;
    font-size: 11px;
    background: #e9ecef;
}

.formula-input {
    flex: 1;
    margin-right: 10px;
}

.formula-input input {
    width: 100%;
    height: 24px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    padding: 0 8px;
    font-size: 11px;
}

.formula-controls {
    display: flex;
    gap: 3px;
    flex-shrink: 0;
    min-width: 50px;
}

.formula-controls .btn {
    height: 24px;
    width: 24px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    border-radius: 3px;
}

.context-menu {
    position: fixed;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    padding: 4px 0;
    min-width: 150px;
    z-index: 1000;
    display: none;
}

.menu-item {
    padding: 6px 12px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.menu-item:hover {
    background: #f8f9fa;
}

.context-menu hr {
    margin: 4px 0;
    border: none;
    border-top: 1px solid #eee;
}

/* Mejoras adicionales para prevenir flickering */
.tab-item.switching {
    pointer-events: none;
    opacity: 0.7;
    transition: opacity 0.1s ease;
}

.tab-item.active {
    background: white !important;
    border-top: 2px solid #007bff !important;
    border-left: 1px solid #dee2e6 !important;
    border-right: 1px solid #dee2e6 !important;
    border-bottom: 1px solid white !important;
    color: #333 !important;
    font-weight: 600 !important;
    z-index: 10 !important;
    position: relative !important;
}

.tab-item:not(.active) {
    background: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6 !important;
    color: #6c757d !important;
}

.sheet-content {
    transition: opacity 0.15s ease-in-out;
}

.sheet-content:not(.active) {
    pointer-events: none;
    opacity: 0;
    display: none;
}

.sheet-content.active {
    pointer-events: auto;
    opacity: 1;
    display: block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    /* Ajustes para el menú hamburguesa en tablets */
    .sheet-menu-btn {
        min-width: 160px;
        font-size: 13px;
        padding: 6px 12px;
    }
    
    .sheet-dropdown {
        min-width: 280px;
        max-height: 350px;
    }
    
    .spreadsheet-status-bar {
        padding: 6px 12px;
        font-size: 11px;
    }
    
    .status-info {
        gap: 15px;
    }
    
    .excel-tabs-container {
        flex-direction: column;
        gap: 5px;
        padding: 4px;
    }
    
    .tabs-navigation {
        order: 2;
    }
    
    .tabs-controls {
        order: 1;
        justify-content: center;
        margin-left: 0;
    }
    
    .tab-item {
        min-width: 80px !important;
        padding: 8px 12px !important;
        font-size: 12px !important;
        touch-action: manipulation !important;
    }
    
    .tab-item.active {
        transform: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .spreadsheet-container {
        height: calc(100vh - 220px);
    }
    
    .formula-bar {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .cell-reference {
        width: 60px;
        margin-right: 5px;
    }
    
    .formula-input {
        margin-right: 5px;
        min-width: 150px;
    }
    
    .tab-item {
        min-width: 50px;
        max-width: 100px;
        font-size: 9px;
        padding: 4px 6px;
    }
}

@media (max-width: 480px) {
    /* Ajustes para el menú hamburguesa en móviles */
    .sheet-menu-btn {
        min-width: 140px;
        font-size: 12px;
        padding: 6px 10px;
    }
    
    .current-sheet-name {
        font-size: 12px;
    }
    
    .sheet-dropdown {
        min-width: 250px;
        max-height: 300px;
        left: 0;
        right: 0;
        margin: 0 10px;
    }
    
    .sheet-item {
        padding: 10px 12px;
    }
    
    .sheet-name {
        font-size: 13px;
    }
    
    .sheet-description {
        font-size: 11px;
    }
    
    .spreadsheet-status-bar {
        flex-direction: column;
        gap: 8px;
        padding: 8px 12px;
    }
    
    .status-info {
        justify-content: center;
        gap: 12px;
    }
    
    .zoom-controls {
        justify-content: center;
    }
    
    .formula-bar {
        flex-direction: column;
        gap: 3px;
    }
    
    .formula-input {
        margin-right: 0;
        min-width: auto;
    }
    
    .excel-tabs {
        max-width: calc(100% - 60px);
    }
    
    .tab-item {
        min-width: 40px;
        max-width: 80px;
        font-size: 8px;
        padding: 3px 4px;
    }
}

/* Paste Special Dialog Styles */
.paste-special-dialog {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dialog-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    min-width: 400px;
    max-width: 500px;
    width: 90%;
}

.dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.dialog-header h5 {
    margin: 0;
    color: #495057;
    font-size: 16px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 18px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Estilos para indicador de progreso de carga */
.data-load-progress {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 10px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    font-size: 12px;
    color: #6c757d;
}

.progress {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(45deg, #007bff, #0056b3);
    transition: width 0.3s ease;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% { background-position: 0 0; }
    100% { background-position: 40px 0; }
}

/* Estilos simples para filas TOTAL */
.total-row {
    background-color: #ffe6e6 !important;
}

.total-cell {
    font-weight: bold !important;
    color: #333333 !important;
    background-color: #ffe6e6 !important;
}

.close-btn:hover {
    color: #dc3545;
}

.dialog-body {
    padding: 20px;
}

.paste-options h6 {
    margin: 0 0 10px 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
}

.option-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 8px;
    margin-bottom: 20px;
}

.option-item {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.option-item:hover {
    background: #f8f9fa;
}

.option-item input[type="radio"],
.option-item input[type="checkbox"] {
    margin-right: 8px;
}

.checkbox-options {
    margin-top: 15px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 5px;
    margin-bottom: 5px;
}

.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
}

.dialog-footer .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.dialog-footer .btn-primary {
    background: #007bff;
    color: white;
}

.dialog-footer .btn-primary:hover {
    background: #0056b3;
}

.dialog-footer .btn-secondary {
    background: #6c757d;
    color: white;
}

.dialog-footer .btn-secondary:hover {
    background: #545b62;
}

/* Enhanced context menu */
.context-menu {
    min-width: 180px;
}

.context-menu hr {
    margin: 5px 0;
    border: 0;
    border-top: 1px solid #dee2e6;
}

/* Selection styles for multi-cell selection */
.cell.multi-selected {
    background: #cce5ff !important;
    border: 1px solid #007bff;
}

.cell.range-selected {
    background: #e3f2fd !important;
}

/* Copy/Cut/Paste visual feedback */
.cell.copied-cell {
    border: 2px dashed #28a745 !important;
    animation: copied-pulse 1s ease-in-out;
}

.cell.cut-cell {
    border: 2px dashed #dc3545 !important;
    opacity: 0.6;
    animation: cut-pulse 1s ease-in-out infinite;
}

.cell.pasted-cell {
    background: #d4edda !important;
    animation: pasted-flash 1s ease-in-out;
}

@keyframes copied-pulse {
    0% { border-color: #28a745; transform: scale(1); }
    50% { border-color: #20c997; transform: scale(1.02); }
    100% { border-color: #28a745; transform: scale(1); }
}

@keyframes cut-pulse {
    0% { opacity: 0.6; }
    50% { opacity: 0.3; }
    100% { opacity: 0.6; }
}

@keyframes pasted-flash {
    0% { background: #d4edda; }
    50% { background: #c3e6cb; }
    100% { background: #d4edda; }
}

@keyframes deleted-flash {
    0% { background: #f8d7da; }
    50% { background: #f5c6cb; }
    100% { background: white; }
}

.cell.deleted-cell {
    animation: deleted-flash 0.5s ease-in-out;
}

/* Enhanced selection feedback */
.cell:hover {
    background: #f8f9fa;
    transition: background-color 0.1s;
}

.cell.selected:hover {
    background: #d1ecf1;
}
</style>
@stop

@section('js')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentCell = null;
let currentSheet = 'BUDGET';
let clipboard = null;
let data = {};
let isChangingSheet = false; // Variable global para prevenir cambios múltiples

// Variables para selección múltiple y portapapeles avanzado
let selectedCells = [];
let selectionStart = null;
let selectionEnd = null;
let isSelecting = false;
let advancedClipboard = {
    data: [],
    type: 'cells', // 'cells', 'range', 'external'
    format: 'html', // 'html', 'text', 'csv'
    source: 'internal' // 'internal', 'external'
};

// Inicializar el portapapeles en el primer uso
function initializeClipboard() {
    if (!window.clipboardInitialized) {
        advancedClipboard = {
            data: [],
            type: 'cells',
            format: 'html',
            source: 'internal'
        };
        window.clipboardInitialized = true;
        console.log('Clipboard initialized:', advancedClipboard);
    }
}

// Función para cargar el tab activo desde localStorage
function loadActiveTab() {
    const savedTab = localStorage.getItem('presupuesto_active_tab');
    if (savedTab) {
        // Verificar que el tab guardado existe en la lista actual
        const $savedTabElement = $(`.tab-item[data-sheet="${savedTab}"]`);
        if ($savedTabElement.length > 0) {
            return savedTab;
        }
    }
    return 'BUDGET'; // Valor por defecto
}

// Función para guardar el tab activo en localStorage
function saveActiveTab(sheet) {
    localStorage.setItem('presupuesto_active_tab', sheet);
}

// Función para escapar selectores CSS (fallback si CSS.escape no está disponible)
function escapeSelector(str) {
    if (typeof CSS !== 'undefined' && CSS.escape) {
        return CSS.escape(str);
    }
    
    // Fallback manual para escapar caracteres especiales
    return str.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~\s]/g, '\\$&');
}

// Función de debug específica para el problema
function debugSheetElements(sheetName) {
    console.group(`Debug para hoja: ${sheetName}`);
    
    const escapedName = escapeSelector(sheetName);
    const $tab = $(`.tab-item[data-sheet="${sheetName}"]`);
    const $sheet = $(`#sheet-${escapedName}`);
    
    console.log(`Nombre original: "${sheetName}"`);
    console.log(`Nombre escapado: "${escapedName}"`);
    console.log(`Selector tab: .tab-item[data-sheet="${sheetName}"]`);
    console.log(`Selector sheet: #sheet-${escapedName}`);
    console.log(`Tab encontrado: ${$tab.length > 0} (${$tab.length} elementos)`);
    console.log(`Sheet encontrado: ${$sheet.length > 0} (${$sheet.length} elementos)`);
    
    if ($tab.length > 0) {
        console.log(`ID del sheet esperado: sheet-${escapedName}`);
        console.log(`ID del sheet real: ${$sheet.attr('id')}`);
    }
    
    console.groupEnd();
}

// Función específica para Detallado secciones1
function debugDetalladoSecciones1() {
    debugSheetElements('Detallado secciones1');
    
    // Listar todos los IDs de sheets disponibles
    console.log('IDs de sheets disponibles:');
    $('.sheet-content').each(function() {
        console.log(`- ${$(this).attr('id')}`);
    });
}

// Initialize data structure for all sheets
@foreach($sheets as $key => $label)
    data['{{ $key }}'] = {};
@endforeach

// Load sample data
@if(isset($sampleData))
    @foreach($sampleData as $sheetKey => $sheetData)
        @foreach($sheetData as $cellData)
            if (!data['{{ $sheetKey }}'][{{ $cellData[0] }}]) {
                data['{{ $sheetKey }}'][{{ $cellData[0] }}] = {};
            }
            data['{{ $sheetKey }}'][{{ $cellData[0] }}][{{ $cellData[1] }}] = '{{ $cellData[2] }}';
        @endforeach
    @endforeach
@endif

$(document).ready(function() {
    // ============ INICIALIZACIÓN DEL MENÚ HAMBURGUESA ============
    initializeSheetMenu();
    
    // Cargar el tab activo guardado
    currentSheet = loadActiveTab();
    
    // Debug: Verificar que todos los elementos necesarios existen
    console.log('Hojas disponibles:');
    $('.tab-item').each(function() {
        const sheetName = $(this).data('sheet');
        const escapedSheetName = escapeSelector(sheetName);
        const sheetExists = $(`#sheet-${escapedSheetName}`).length > 0;
        console.log(`- ${sheetName}: Tab existe, Sheet existe: ${sheetExists}`);
        
        // Debug específico para Detallado secciones1
        if (sheetName === 'Detallado secciones1') {
            debugDetalladoSecciones1();
        }
    });
    
    initializeSpreadsheet();
    
    // Inicializar funcionalidad de recálculo de totales
    initializeTotalCalculations();
    
    // Establecer el tab activo inicial
    if (currentSheet !== 'BUDGET') {
        switchSheet(currentSheet);
    }
    
    // Agregar handler de click específico para debug en consola
    window.debugSheet = debugSheetElements;
    window.debugClipboard = function() {
        console.group('=== ESTADO DEL PORTAPAPELES ===');
        console.log('advancedClipboard:', advancedClipboard);
        console.log('clipboard (legacy):', clipboard);
        console.log('currentCell:', currentCell);
        console.log('selectedCells count:', getSelectedCells().length);
        console.log('clipboardInitialized:', window.clipboardInitialized);
        console.groupEnd();
    };
    window.testCopy = function() {
        console.log('=== TESTING COPY ===');
        if (currentCell) {
            console.log('Current cell exists, calling copySelection...');
            copySelection();
        } else {
            console.log('No current cell selected. Click on a cell first.');
            const firstCell = $('.cell').first();
            if (firstCell.length > 0) {
                console.log('Selecting first available cell...');
                selectCell(firstCell[0]);
                copySelection();
            }
        }
    };
    window.testPaste = function() {
        console.log('=== TESTING PASTE ===');
        if (currentCell) {
            console.log('Current cell exists, calling executePaste...');
            executePaste();
        } else {
            console.log('No current cell selected. Click on a cell first.');
        }
    };
    window.forceCopy = function(value = 'Test Data') {
        console.log('=== FORCE COPY ===');
        initializeClipboard();
        advancedClipboard.data = [{
            row: 1,
            col: 1,
            value: value,
            style: '',
            classes: '',
            formula: value
        }];
        clipboard = {
            value: value,
            row: 1,
            col: 1
        };
        console.log('Forced clipboard data:', advancedClipboard);
        showNotification('Datos forzados en el portapapeles', 'info');
    };
    
    // Función de prueba completa
    window.testCompleteFlow = function() {
        console.log('=== TEST COMPLETE FLOW ===');
        
        // 1. Verificar que hay una celda activa
        if (!currentCell) {
            console.error('No currentCell found');
            return;
        }
        
        // 2. Asegurar que la celda tenga la clase selected
        const $cell = $(currentCell);
        if (!$cell.hasClass('selected')) {
            console.warn('Current cell does not have selected class, adding it');
            $cell.addClass('selected');
        }
        
        // 3. Ejecutar diagnóstico
        diagnoseSelection();
        
        // 4. Probar getSelectedCells
        const cells = getSelectedCells();
        console.log('getSelectedCells returned:', cells.length, 'cells');
        
        // 5. Probar copySelection
        if (cells.length > 0) {
            console.log('Executing copySelection...');
            copySelection();
            
            // 6. Verificar estado después de copia
            console.log('After copy - advancedClipboard:', advancedClipboard);
            console.log('After copy - clipboard (legacy):', clipboard);
        } else {
            console.error('No cells to copy');
        }
    };
    
    // Función para forzar selección de una celda específica
    window.forceSelectCell = function(row, col, sheet = 'BUDGET') {
        console.log('=== FORCE SELECT CELL ===');
        const selector = `.cell[data-row="${row}"][data-col="${col}"][data-sheet="${sheet}"]`;
        console.log('Looking for cell with selector:', selector);
        
        const $cell = $(selector);
        console.log('Found cells:', $cell.length);
        
        if ($cell.length > 0) {
            const cell = $cell[0];
            selectCell(cell);
            console.log('Cell selected:', {
                row: $cell.data('row'),
                col: $cell.data('col'),
                sheet: $cell.data('sheet'),
                value: $cell.text()
            });
            return cell;
        } else {
            console.error('Cell not found');
            return null;
        }
    };
    
    // Función directa para copiar la celda actual sin usar getSelectedCells
    window.directCopy = function() {
        console.log('=== DIRECT COPY ===');
        
        if (!currentCell) {
            console.error('No currentCell found');
            return;
        }
        
        // Asegurar que el portapapeles esté inicializado
        initializeClipboard();
        
        const $cell = $(currentCell);
        const row = parseInt($cell.data('row')) || 1;
        const col = parseInt($cell.data('col')) || 1;
        const sheet = $cell.data('sheet') || 'BUDGET';
        const value = $cell.text() || '';
        
        console.log('Copying cell data:', {row, col, sheet, value});
        
        // Configurar portapapeles directamente
        advancedClipboard.data = [{
            row: row,
            col: col,
            value: value,
            style: $cell.attr('style') || '',
            classes: $cell.attr('class') || '',
            formula: value,
            sheet: sheet
        }];
        
        advancedClipboard.type = 'cell';
        advancedClipboard.source = 'internal';
        advancedClipboard.format = 'internal';
        advancedClipboard.operation = 'copy';
        
        clipboard = {
            value: value,
            row: row,
            col: col,
            sheet: sheet
        };
        
        console.log('Direct copy completed');
        console.log('advancedClipboard:', advancedClipboard);
        console.log('clipboard (legacy):', clipboard);
        
        showNotification('Copia directa completada', 'success');
    };
    
    // Función para probar todo el flujo paso a paso
    window.stepByStepTest = function() {
        console.log('=== STEP BY STEP TEST ===');
        
        // Paso 1: Seleccionar una celda específica
        console.log('Step 1: Selecting cell (19, 2)');
        const cell = forceSelectCell(19, 2, 'BUDGET');
        
        if (!cell) {
            console.error('Failed to select cell');
            return;
        }
        
        // Paso 2: Verificar selección
        console.log('Step 2: Verifying selection');
        const selectedCells = getSelectedCells();
        console.log('getSelectedCells() returned:', selectedCells.length, 'cells');
        
        // Paso 3: Copia directa
        console.log('Step 3: Direct copy');
        directCopy();
        
        // Paso 4: Verificar copia
        console.log('Step 4: Verifying copy');
        debugClipboard();
        
        // Paso 5: Probar pegado
        console.log('Step 5: Testing paste');
        executePaste();
    };
});

function initializeSpreadsheet() {
    // Prevenir múltiples inicializaciones
    if (window.spreadsheetInitialized) {
        return;
    }
    window.spreadsheetInitialized = true;

    // Inicializar portapapeles
    initializeClipboard();

    // Iniciar monitoreo del estado de tabs
    startTabStateMonitoring();
    
    // Habilitar selección múltiple
    enableMultiSelection();
    
    // Configurar detección de pegado externo
    setupExternalPasteDetection();

    // Tab switching - usar delegación de eventos para evitar conflictos
    $(document).off('click.tabswitch').on('click.tabswitch', '.tab-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Prevenir clicks múltiples y cambios en progreso
        if ($(this).hasClass('switching') || isChangingSheet) {
            return false;
        }
        
        const sheet = $(this).data('sheet');
        if (sheet && sheet !== currentSheet) {
            $(this).addClass('switching');
            
            // Throttle para prevenir cambios demasiado rápidos
            clearTimeout(window.switchTimeout);
            window.switchTimeout = setTimeout(() => {
                switchSheet(sheet);
            }, 50);
            
            // Remover clase después de un breve delay
            setTimeout(() => {
                $(this).removeClass('switching');
            }, 300);
        }
        
        return false;
    });

    // Cell selection - usar delegación de eventos
    $(document).off('click.cellselect').on('click.cellselect', '.cell', function(e) {
        e.stopPropagation();
        console.log('Cell click detected, calling selectCell');
        selectCell(this);
    });

    // Cell editing
    $(document).off('input.celledit').on('input.celledit', '.cell', function() {
        const row = $(this).data('row');
        const col = $(this).data('col');
        const sheet = $(this).data('sheet');
        const value = $(this).text();
        
        saveCell(sheet, row, col, value);
    });

    // Formula bar input
    $('#formula-input').off('keypress.formula').on('keypress.formula', function(e) {
        if (e.which === 13 && currentCell) {
            const value = $(this).val();
            $(currentCell).text(value);
            
            const row = $(currentCell).data('row');
            const col = $(currentCell).data('col');
            const sheet = $(currentCell).data('sheet');
            
            saveCell(sheet, row, col, value);
        }
    });

    // Context menu
    $('.cell').contextmenu(function(e) {
        e.preventDefault();
        showContextMenu(e.pageX, e.pageY);
        selectCell(this);
    });

    // Hide context menu on click elsewhere
    $(document).click(function() {
        $('#context-menu').hide();
    });

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        if (e.ctrlKey) {
            switch(e.which) {
                case 67: // Ctrl+C
                    e.preventDefault();
                    console.log('Ctrl+C pressed');
                    simpleCopy();
                    break;
                case 88: // Ctrl+X
                    e.preventDefault();
                    console.log('Ctrl+X pressed');
                    simpleCut();
                    break;
                case 86: // Ctrl+V
                    e.preventDefault();
                    console.log('Ctrl+V pressed, shiftKey:', e.shiftKey);
                    if (e.shiftKey) {
                        // Ctrl+Shift+V - Pegado especial
                        pasteSpecial();
                    } else {
                        // Ctrl+V - Pegado normal
                        simplePaste();
                    }
                    break;
                case 83: // Ctrl+S
                    e.preventDefault();
                    saveData();
                    break;
                case 65: // Ctrl+A
                    e.preventDefault();
                    selectAllCells();
                    break;
                case 90: // Ctrl+Z
                    e.preventDefault();
                    // TODO: Implementar deshacer
                    showNotification('Función deshacer en desarrollo', 'info');
                    break;
                case 89: // Ctrl+Y
                    e.preventDefault();
                    // TODO: Implementar rehacer
                    showNotification('Función rehacer en desarrollo', 'info');
                    break;
            }
        } else {
            switch(e.which) {
                case 27: // ESC
                    clearSelection();
                    closePasteSpecialDialog();
                    $('#context-menu').hide();
                    break;
                case 46: // Delete
                    deleteSelectedCells();
                    break;
            }
        }
    });

    // Manejar recarga de página y navegación
    $(window).on('beforeunload', function() {
        // Guardar el estado actual antes de salir
        saveActiveTab(currentSheet);
    });

    // Manejar cambios en el historial del navegador
    $(window).on('popstate', function() {
        // Validar el estado cuando se regresa
        setTimeout(validateTabState, 100);
    });
}

function switchSheet(sheet) {
    // Prevenir cambios múltiples simultáneos
    if (isChangingSheet) {
        return;
    }
    
    // Prevenir cambio a la misma hoja
    if (currentSheet === sheet) {
        return;
    }
    
    // Marcar que estamos cambiando de hoja
    isChangingSheet = true;
    
    // Remover clases active de TODOS los tabs y sheets
    $('.tab-item').removeClass('active');
    $('.sheet-content').removeClass('active');
    
    // Escapar el nombre de la hoja para selectores CSS
    const escapedSheet = escapeSelector(sheet);
    
    // Agregar clase active a los elementos correspondientes
    const $targetTab = $(`.tab-item[data-sheet="${sheet}"]`);
    const $targetSheet = $(`#sheet-${escapedSheet}`);
    
    if ($targetTab.length && $targetSheet.length) {
        // Asegurar que solo el tab seleccionado tenga la clase active
        $targetTab.addClass('active');
        $targetSheet.addClass('active');
        
        // Actualizar la variable de estado
        currentSheet = sheet;
        
        // Guardar el estado en localStorage
        saveActiveTab(sheet);
        
        // Limpiar selección de celda actual
        $('.cell.selected').removeClass('selected');
        currentCell = null;
        $('#cell-ref').val('');
        $('#formula-input').val('');
        
        // Pequeño delay para suavizar la transición y liberar el estado
        setTimeout(() => {
            $targetSheet.find('.cell').first().focus();
            isChangingSheet = false; // Liberar el estado
            
            // Inicializar estilos de filas TOTAL si estamos en Detallado secciones1
            if (sheet === 'Detallado secciones1') {
                initializeTotalRowStyles();
            }
            
            // Limpiar filtros activos al cambiar de hoja
            clearAllFilters();
            
            // Verificar que el estado se mantuvo correctamente
            if (!$targetTab.hasClass('active')) {
                $targetTab.addClass('active');
            }
        }, 100);
    } else {
        // Si no se encontraron los elementos, liberar el estado inmediatamente
        isChangingSheet = false;
        console.warn(`No se encontraron elementos para la hoja: ${sheet}`);
        console.warn(`Selector utilizado: #sheet-${escapedSheet}`);
        console.warn(`Elementos encontrados - Tab: ${$targetTab.length}, Sheet: ${$targetSheet.length}`);
    }
}

function selectCell(cell) {
    // Limpiar selección previa
    $('.cell').removeClass('selected');
    
    // Seleccionar la nueva celda
    $(cell).addClass('selected');
    currentCell = cell;
    
    console.log('Cell selected:', {
        row: $(cell).data('row'),
        col: $(cell).data('col'),
        sheet: $(cell).data('sheet'),
        value: $(cell).text()
    });
    
    const row = $(cell).data('row');
    const col = $(cell).data('col');
    const cellRef = String.fromCharCode(64 + col) + row;
    
    $('#cell-ref').val(cellRef);
    $('#formula-input').val($(cell).text());
}

function saveCell(sheet, row, col, value) {
    if (!data[sheet]) {
        data[sheet] = {};
    }
    if (!data[sheet][row]) {
        data[sheet][row] = {};
    }
    data[sheet][row][col] = value;
}

// Función para validar y corregir el estado de los tabs
function validateTabState() {
    const activeTabs = $('.tab-item.active');
    const activeSheets = $('.sheet-content.active');
    
    // Solo debe haber un tab activo
    if (activeTabs.length !== 1) {
        console.warn('Estado inconsistente de tabs detectado, corrigiendo...');
        $('.tab-item').removeClass('active');
        $('.sheet-content').removeClass('active');
        
        // Activar el tab actual
        const $currentTab = $(`.tab-item[data-sheet="${currentSheet}"]`);
        const escapedSheet = escapeSelector(currentSheet);
        const $currentSheet = $(`#sheet-${escapedSheet}`);
        
        if ($currentTab.length && $currentSheet.length) {
            $currentTab.addClass('active');
            $currentSheet.addClass('active');
        }
    }
}

// Función para verificar el estado periódicamente
function startTabStateMonitoring() {
    // Limpiar cualquier monitoreo previo
    if (window.tabStateMonitorInterval) {
        clearInterval(window.tabStateMonitorInterval);
    }
    
    // Temporalmente desactivado para debug
    // window.tabStateMonitorInterval = setInterval(validateTabState, 2000); // Verificar cada 2 segundos
    console.log('Monitoreo de tabs desactivado temporalmente para debug');
}

function showContextMenu(x, y) {
    $('#context-menu').css({
        left: x + 'px',
        top: y + 'px'
    }).show();
}

// ============ FUNCIONES AVANZADAS DE PORTAPAPELES ============

function copySelection() {
    // Inicializar portapapeles si no está inicializado
    initializeClipboard();
    
    console.log('=== COPY SELECTION START ===');
    
    // Obtener celdas seleccionadas
    let selection = getSelectedCells();
    console.log('Initial selection length:', selection.length);
    
    // Si no hay selección, usar la celda actual
    if (selection.length === 0 && currentCell) {
        selection = [currentCell];
        console.log('Using currentCell as selection');
    }
    
    if (selection.length === 0) {
        showNotification('Selecciona al menos una celda para copiar', 'warning');
        console.log('No cells to copy');
        return;
    }
    
    console.log('Final selection length:', selection.length);
    
    // Limpiar datos previos del portapapeles
    advancedClipboard.data = [];
    
    // Copiar datos de las celdas seleccionadas
    selection.forEach((cell, index) => {
        const $cell = $(cell);
        const cellData = {
            row: parseInt($cell.data('row')) || 1,
            col: parseInt($cell.data('col')) || 1,
            value: $cell.text() || '',
            style: $cell.attr('style') || '',
            classes: $cell.attr('class') || '',
            formula: $cell.data('formula') || $cell.text() || ''
        };
        
        advancedClipboard.data.push(cellData);
        console.log(`Copied cell ${index}:`, cellData);
    });
    
    // Configurar metadatos del portapapeles
    advancedClipboard.type = selection.length === 1 ? 'cell' : 'range';
    advancedClipboard.source = 'internal';
    advancedClipboard.format = 'internal';
    
    // También mantener compatibilidad con el clipboard legacy para una sola celda
    if (selection.length === 1) {
        const $cell = $(selection[0]);
        clipboard = {
            value: $cell.text() || '',
            row: parseInt($cell.data('row')) || 1,
            col: parseInt($cell.data('col')) || 1
        };
        console.log('Legacy clipboard set:', clipboard);
    }
    
    console.log('Advanced clipboard final state:', advancedClipboard);
    
    // Feedback visual
    selection.forEach(cell => {
        $(cell).addClass('copied-cell');
    });
    
    setTimeout(() => {
        $('.copied-cell').removeClass('copied-cell');
    }, 2000);
    
    // Notificación de éxito
    showNotification(`${selection.length} celda(s) copiada(s)`, 'success');
    
    // Cerrar menú contextual si está abierto
    $('#context-menu').hide();
    
    console.log('=== COPY SELECTION END ===');
}

function cutSelection() {
    copySelection();
    
    // Marcar celdas para cortar
    const selection = getSelectedCells();
    selection.forEach(cell => {
        $(cell).addClass('cut-cell');
    });
    
    advancedClipboard.type = 'cut';
    showNotification('Datos cortados', 'info');
}

function pasteSpecial() {
    console.log('=== PASTE SPECIAL START ===');
    
    // Inicializar portapapeles si no está inicializado
    initializeClipboard();
    
    console.log('advancedClipboard.data:', advancedClipboard.data);
    console.log('advancedClipboard.data.length:', advancedClipboard.data.length);
    console.log('clipboard (legacy):', clipboard);
    
    // Verificar si hay datos para pegar
    const hasAdvancedData = advancedClipboard.data && advancedClipboard.data.length > 0;
    const hasLegacyData = clipboard && clipboard.value;
    
    console.log('hasAdvancedData:', hasAdvancedData);
    console.log('hasLegacyData:', hasLegacyData);
    
    if (!hasAdvancedData && !hasLegacyData) {
        showNotification('No hay datos para pegar. Primero copia algo.', 'warning');
        console.log('No data available for pasting');
        return;
    }
    
    // Si no hay datos avanzados pero sí legacy, convertir
    if (!hasAdvancedData && hasLegacyData) {
        console.log('Converting legacy clipboard to advanced');
        advancedClipboard.data = [{
            row: 1,
            col: 1,
            value: clipboard.value,
            style: '',
            classes: '',
            formula: clipboard.value
        }];
        advancedClipboard.type = 'cell';
        advancedClipboard.source = 'internal';
    }
    
    console.log('Final clipboard state for paste special:', advancedClipboard);
    
    // Mostrar diálogo de pegado especial
    $('#paste-special-dialog').show();
    $('#context-menu').hide();
    
    console.log('=== PASTE SPECIAL END ===');
}

function pasteValues() {
    console.log('pasteValues called');
    if (advancedClipboard.data.length === 0 && !clipboard) {
        showNotification('No hay datos para pegar', 'warning');
        return;
    }
    executePaste('values');
    $('#context-menu').hide();
}

function pasteFormats() {
    console.log('pasteFormats called');
    if (advancedClipboard.data.length === 0 && !clipboard) {
        showNotification('No hay datos para pegar', 'warning');
        return;
    }
    executePaste('formats');
    $('#context-menu').hide();
}

function executePasteSpecial() {
    const what = $('input[name="paste-what"]:checked').val();
    const operation = $('input[name="paste-operation"]:checked').val();
    const skipBlanks = $('input[name="skip-blanks"]').is(':checked');
    const transpose = $('input[name="transpose"]').is(':checked');
    
    executePaste(what, operation, { skipBlanks, transpose });
    closePasteSpecialDialog();
}

function executePaste(pasteType = 'all', operation = 'none', options = {}) {
    console.log('=== EXECUTE PASTE START ===');
    console.log('pasteType:', pasteType, 'operation:', operation, 'options:', options);
    
    // Inicializar portapapeles si no está inicializado
    initializeClipboard();
    
    console.log('currentCell:', currentCell);
    console.log('advancedClipboard.data:', advancedClipboard.data);
    console.log('clipboard (legacy):', clipboard);
    
    // Verificar si hay una celda de destino
    if (!currentCell) {
        showNotification('Selecciona una celda de destino primero', 'warning');
        console.log('No destination cell selected');
        return;
    }
    
    // Verificar si hay datos para pegar
    const hasAdvancedData = advancedClipboard.data && advancedClipboard.data.length > 0;
    const hasLegacyData = clipboard && clipboard.value !== undefined;
    
    console.log('hasAdvancedData:', hasAdvancedData);
    console.log('hasLegacyData:', hasLegacyData);
    
    if (!hasAdvancedData && !hasLegacyData) {
        showNotification('No hay datos para pegar. Primero copia algo.', 'warning');
        console.log('No data available for pasting');
        return;
    }
    
    // Si no hay datos avanzados pero sí legacy, convertir
    if (!hasAdvancedData && hasLegacyData) {
        console.log('Converting legacy clipboard to advanced for paste');
        advancedClipboard.data = [{
            row: 1,
            col: 1,
            value: clipboard.value,
            style: '',
            classes: '',
            formula: clipboard.value
        }];
    }
    
    // Obtener información de la celda de destino
    const startRow = parseInt($(currentCell).data('row')) || 1;
    const startCol = parseInt($(currentCell).data('col')) || 1;
    const currentSheetName = $(currentCell).data('sheet');
    
    console.log('Paste destination:', { startRow, startCol, currentSheetName });
    
    let pastedCount = 0;
    
    // Procesar cada celda en el portapapeles
    advancedClipboard.data.forEach((cellData, index) => {
        // Calcular posición de destino
        const targetRow = startRow + (cellData.row - advancedClipboard.data[0].row);
        const targetCol = startCol + (cellData.col - advancedClipboard.data[0].col);
        
        // Buscar la celda de destino
        const $targetCell = $(`.cell[data-row="${targetRow}"][data-col="${targetCol}"][data-sheet="${currentSheetName}"]`);
        
        console.log(`Attempting to paste cell ${index} to row=${targetRow}, col=${targetCol}`, cellData);
        
        if ($targetCell.length > 0) {
            let newValue = cellData.value || '';
            
            // Aplicar operaciones matemáticas si es necesario
            if (operation !== 'none' && !isNaN(parseFloat(cellData.value))) {
                const currentValue = parseFloat($targetCell.text()) || 0;
                const pasteValue = parseFloat(cellData.value);
                
                switch (operation) {
                    case 'add':
                        newValue = (currentValue + pasteValue).toString();
                        break;
                    case 'subtract':
                        newValue = (currentValue - pasteValue).toString();
                        break;
                    case 'multiply':
                        newValue = (currentValue * pasteValue).toString();
                        break;
                    case 'divide':
                        newValue = pasteValue !== 0 ? (currentValue / pasteValue).toString() : '0';
                        break;
                }
            }
            
            // Pegar según el tipo seleccionado
            switch (pasteType) {
                case 'values':
                    $targetCell.text(newValue);
                    break;
                case 'formats':
                    if (cellData.style) $targetCell.attr('style', cellData.style);
                    // Mantener las clases base y agregar las nuevas
                    if (cellData.classes) {
                        const baseClasses = 'cell';
                        const additionalClasses = cellData.classes.split(' ').filter(c => c !== 'cell').join(' ');
                        $targetCell.attr('class', baseClasses + (additionalClasses ? ' ' + additionalClasses : ''));
                    }
                    break;
                case 'formulas':
                    $targetCell.text(cellData.formula);
                    if (cellData.formula !== cellData.value) {
                        $targetCell.data('formula', cellData.formula);
                    }
                    break;
                default: // 'all'
                    $targetCell.text(newValue);
                    if (cellData.style) $targetCell.attr('style', cellData.style);
                    if (cellData.formula !== cellData.value) {
                        $targetCell.data('formula', cellData.formula);
                    }
                    break;
            }
            
            // Guardar en el modelo de datos
            saveCell(currentSheetName, targetRow, targetCol, newValue);
            pastedCount++;
            
            // Animación visual
            $targetCell.addClass('pasted-cell');
            setTimeout(() => {
                $targetCell.removeClass('pasted-cell');
            }, 1000);
            
            console.log(`Successfully pasted to cell at row=${targetRow}, col=${targetCol}`);
        } else {
            console.warn(`Target cell not found for row=${targetRow}, col=${targetCol}`);
        }
    });
    
    // Limpiar celdas cortadas si es necesario
    if (advancedClipboard.type === 'cut') {
        $('.cut-cell').each(function() {
            $(this).text('').removeClass('cut-cell');
            const row = $(this).data('row');
            const col = $(this).data('col');
            const sheet = $(this).data('sheet');
            saveCell(sheet, row, col, '');
        });
        // Limpiar portapapeles después de cortar
        advancedClipboard.data = [];
        clipboard = null;
    }
    
    // Mostrar resultado
    if (pastedCount > 0) {
        showNotification(`${pastedCount} celda(s) pegada(s)`, 'success');
    } else {
        showNotification('No se pudieron pegar los datos', 'warning');
    }
    
    console.log(`Paste completed: ${pastedCount} cells pasted`);
    console.log('=== EXECUTE PASTE END ===');
}

function createClipboardText(data) {
    if (data.length === 0) return '';
    
    // Organizar datos en matriz
    const minRow = Math.min(...data.map(d => d.row));
    const maxRow = Math.max(...data.map(d => d.row));
    const minCol = Math.min(...data.map(d => d.col));
    const maxCol = Math.max(...data.map(d => d.col));
    
    let result = '';
    for (let row = minRow; row <= maxRow; row++) {
        let rowData = [];
        for (let col = minCol; col <= maxCol; col++) {
            const cellData = data.find(d => d.row === row && d.col === col);
            rowData.push(cellData ? cellData.value : '');
        }
        result += rowData.join('\t') + '\n';
    }
    
    return result.trim();
}

// Función para diagnosticar el problema de copia
function diagnoseSelection() {
    console.log('=== DIAGNOSE SELECTION ===');
    console.log('currentCell:', currentCell);
    console.log('currentCell type:', typeof currentCell);
    console.log('currentCell jQuery:', $(currentCell).length);
    
    const selectedCells = $('.cell.selected');
    console.log('selectedCells found:', selectedCells.length);
    
    const multiSelected = $('.cell.multi-selected');
    console.log('multiSelected found:', multiSelected.length);
    
    const rangeSelected = $('.cell.range-selected');
    console.log('rangeSelected found:', rangeSelected.length);
    
    if (currentCell) {
        const $current = $(currentCell);
        console.log('currentCell data:', {
            row: $current.data('row'),
            col: $current.data('col'),
            sheet: $current.data('sheet'),
            value: $current.text(),
            hasClass_selected: $current.hasClass('selected'),
            classList: currentCell.className
        });
    }
    
    return {
        currentCell: currentCell,
        selectedCount: selectedCells.length,
        multiSelectedCount: multiSelected.length,
        rangeSelectedCount: rangeSelected.length
    };
}

// Función simplificada para obtener celdas seleccionadas
function getSelectedCells() {
    console.log('=== GET SELECTED CELLS START ===');
    
    // Siempre comenzar con currentCell como base
    if (!currentCell) {
        console.warn('No currentCell available');
        return [];
    }
    
    // Diagnóstico
    const diagnosis = diagnoseSelection();
    console.log('Selection diagnosis:', diagnosis);
    
    // Buscar celdas con diferentes clases de selección
    let selected = $('.cell.selected, .cell.multi-selected, .cell.range-selected');
    console.log('Found cells with selection classes:', selected.length);
    
    // Si no encontramos nada con clases, usar currentCell
    if (selected.length === 0) {
        console.log('No selection classes found, using currentCell');
        return [currentCell];
    }
    
    console.log('Returning array of', selected.length, 'cells');
    return selected.toArray();
}

function closePasteSpecialDialog() {
    $('#paste-special-dialog').hide();
}

// ============ FUNCIONES DE SELECCIÓN MÚLTIPLE ============

function enableMultiSelection() {
    $(document).off('mousedown.multiselect').on('mousedown.multiselect', '.cell', function(e) {
        if (e.shiftKey || e.ctrlKey) {
            e.preventDefault();
            handleMultiSelection(this, e);
        } else if (e.button === 0) { // Left click
            startCellSelection(this, e);
        }
    });
    
    $(document).off('mouseover.multiselect').on('mouseover.multiselect', '.cell', function(e) {
        if (isSelecting) {
            updateCellSelection(this);
        }
    });
    
    $(document).off('mouseup.multiselect').on('mouseup.multiselect', function() {
        isSelecting = false;
    });
}

function startCellSelection(cell, e) {
    if (!e.ctrlKey) {
        clearSelection();
    }
    
    selectCell(cell);
    selectionStart = {
        row: $(cell).data('row'),
        col: $(cell).data('col')
    };
    isSelecting = true;
}

function updateCellSelection(cell) {
    if (!selectionStart) return;
    
    selectionEnd = {
        row: $(cell).data('row'),
        col: $(cell).data('col')
    };
    
    selectRange(selectionStart, selectionEnd);
}

function selectRange(start, end) {
    clearRangeSelection();
    
    const minRow = Math.min(start.row, end.row);
    const maxRow = Math.max(start.row, end.row);
    const minCol = Math.min(start.col, end.col);
    const maxCol = Math.max(start.col, end.col);
    
    for (let row = minRow; row <= maxRow; row++) {
        for (let col = minCol; col <= maxCol; col++) {
            const $cell = $(`.cell[data-row="${row}"][data-col="${col}"][data-sheet="${currentSheet}"]`);
            if ($cell.length > 0) {
                $cell.addClass('range-selected');
            }
        }
    }
}

function handleMultiSelection(cell, e) {
    if (e.ctrlKey) {
        // Ctrl+Click: agregar/quitar de selección
        $(cell).toggleClass('multi-selected');
    } else if (e.shiftKey) {
        // Shift+Click: seleccionar rango
        if (currentCell) {
            const start = {
                row: $(currentCell).data('row'),
                col: $(currentCell).data('col')
            };
            const end = {
                row: $(cell).data('row'),
                col: $(cell).data('col')
            };
            selectRange(start, end);
        }
    }
}

function clearSelection() {
    $('.cell').removeClass('selected multi-selected range-selected');
    selectedCells = [];
}

function clearRangeSelection() {
    $('.cell').removeClass('range-selected');
}

// ============ FUNCIONES DE NOTIFICACIÓN ============

function showNotification(message, type = 'info') {
    const iconMap = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    } else {
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// ============ DETECCIÓN DE PEGADO EXTERNO ============

function setupExternalPasteDetection() {
    $(document).on('paste', function(e) {
        const clipboardData = e.originalEvent.clipboardData;
        if (clipboardData && currentCell) {
            handleExternalPaste(clipboardData);
            e.preventDefault();
        }
    });
}

function handleExternalPaste(clipboardData) {
    const htmlData = clipboardData.getData('text/html');
    const textData = clipboardData.getData('text/plain');
    
    if (htmlData && htmlData.includes('<table')) {
        // Datos de Excel/HTML
        parseHTMLTableData(htmlData);
    } else if (textData) {
        // Datos de texto plano (CSV, TSV)
        parseTextData(textData);
    }
}

function parseHTMLTableData(htmlData) {
    // Crear un elemento temporal para parsear HTML
    const tempDiv = $('<div>').html(htmlData);
    const rows = tempDiv.find('tr');
    
    const pasteData = [];
    rows.each(function(rowIndex) {
        $(this).find('td, th').each(function(colIndex) {
            const cellValue = $(this).text().trim();
            const cellStyle = $(this).attr('style') || '';
            
            pasteData.push({
                row: rowIndex + 1,
                col: colIndex + 1,
                value: cellValue,
                style: cellStyle,
                formula: cellValue
            });
        });
    });
    
    // Actualizar el portapapeles avanzado
    advancedClipboard.data = pasteData;
    advancedClipboard.type = 'range';
    advancedClipboard.source = 'external';
    advancedClipboard.format = 'html';
    
    // Ejecutar pegado automático
    executePaste();
    
    showNotification(`Pegados ${pasteData.length} celdas desde Excel`, 'success');
}

function parseTextData(textData) {
    const lines = textData.split('\n');
    const pasteData = [];
    
    lines.forEach((line, rowIndex) => {
        const cells = line.split('\t'); // Tab-separated
        cells.forEach((cellValue, colIndex) => {
            if (cellValue.trim() !== '') {
                pasteData.push({
                    row: rowIndex + 1,
                    col: colIndex + 1,
                    value: cellValue.trim(),
                    style: '',
                    formula: cellValue.trim()
                });
            }
        });
    });
    
    if (pasteData.length > 0) {
        advancedClipboard.data = pasteData;
        advancedClipboard.type = 'range';
        advancedClipboard.source = 'external';
        advancedClipboard.format = 'text';
        
        executePaste();
        showNotification(`Pegados ${pasteData.length} celdas desde texto`, 'success');
    }
}

// ============ FUNCIONES HEREDADAS (COMPATIBILIDAD) ============

function copyCell() {
    copySelection();
}

function pasteCell() {
    executePaste();
}

// ============ FUNCIONES AUXILIARES ADICIONALES ============

function selectAllCells() {
    clearSelection();
    $(`.sheet-content.active .cell`).addClass('range-selected');
    showNotification('Todas las celdas seleccionadas', 'info');
}

function deleteSelectedCells() {
    const selection = getSelectedCells();
    if (selection.length === 0) return;
    
    selection.forEach(cell => {
        const $cell = $(cell);
        $cell.text('');
        
        const row = $cell.data('row');
        const col = $cell.data('col');
        const sheet = $cell.data('sheet');
        saveCell(sheet, row, col, '');
        
        // Animación visual
        $cell.addClass('deleted-cell');
        setTimeout(() => {
            $cell.removeClass('deleted-cell');
        }, 500);
    });
    
    showNotification(`${selection.length} celdas eliminadas`, 'info');
}

// Función para cerrar diálogos con ESC
function closeAllDialogs() {
    $('#paste-special-dialog').hide();
    $('#context-menu').hide();
}

// Función para formatear números
function formatNumber(value, format = 'general') {
    if (isNaN(parseFloat(value))) return value;
    
    const num = parseFloat(value);
    
    switch (format) {
        case 'currency':
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP'
            }).format(num);
        case 'percentage':
            return new Intl.NumberFormat('es-CO', {
                style: 'percent'
            }).format(num / 100);
        case 'decimal':
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        default:
            return value;
    }
}

function insertRow() {
    if (currentCell) {
        const row = $(currentCell).data('row');
        // Logic to insert row would go here
        console.log('Insert row at:', row);
    }
    $('#context-menu').hide();
}

function insertColumn() {
    if (currentCell) {
        const col = $(currentCell).data('col');
        // Logic to insert column would go here
        console.log('Insert column at:', col);
    }
    $('#context-menu').hide();
}

function deleteRow() {
    if (currentCell) {
        const row = $(currentCell).data('row');
        // Logic to delete row would go here
        console.log('Delete row:', row);
    }
    $('#context-menu').hide();
}

function deleteColumn() {
    if (currentCell) {
        const col = $(currentCell).data('col');
        // Logic to delete column would go here
        console.log('Delete column:', col);
    }
    $('#context-menu').hide();
}

function addNewSheet() {
    const sheetName = prompt('Nombre de la nueva hoja:');
    if (sheetName && sheetName.trim()) {
        // Logic to add new sheet would go here
        console.log('Add new sheet:', sheetName);
    }
}

function saveData() {
    // Show loading
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere mientras se guardan los datos',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Simulate save operation
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Datos guardados',
            text: 'Los datos se han guardado correctamente',
            timer: 2000,
            showConfirmButton: false
        });
        
        console.log('Saving data:', data);
    }, 1000);
}

function exportToExcel() {
    // Logic to export to Excel would go here
    Swal.fire({
        icon: 'info',
        title: 'Exportar a Excel',
        text: 'Funcionalidad de exportación en desarrollo',
        confirmButtonText: 'Entendido'
    });
}

// Funciones adicionales para soporte móvil y mejora de UX
function handleTouchEvents() {
    // Prevenir zoom en doble tap para botones de tabs
    $('.tab-item').off('touchend.tabswitch').on('touchend.tabswitch', function(e) {
        e.preventDefault();
        
        // Evitar conflictos con click events
        if ($(this).hasClass('switching') || isChangingSheet) {
            return false;
        }
        
        const sheet = $(this).data('sheet');
        if (sheet && sheet !== currentSheet) {
            // Throttle también para eventos touch
            clearTimeout(window.touchSwitchTimeout);
            window.touchSwitchTimeout = setTimeout(() => {
                switchSheet(sheet);
            }, 100);
        }
        
        return false;
    });
}

// Función para forzar actualización del estado visual
function forceTabStateUpdate() {
    const $activeTab = $(`.tab-item[data-sheet="${currentSheet}"]`);
    const escapedSheet = escapeSelector(currentSheet);
    const $activeSheet = $(`#sheet-${escapedSheet}`);
    
    // Forzar la actualización visual
    $('.tab-item').removeClass('active');
    $('.sheet-content').removeClass('active');
    
    $activeTab.addClass('active');
    $activeSheet.addClass('active');
}

// Función para detectar si estamos en dispositivo móvil
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Inicializar eventos móviles si es necesario
if (isMobileDevice()) {
    $(document).ready(function() {
        handleTouchEvents();
        
        // Reducir la frecuencia de monitoreo en móviles
        clearInterval(window.tabStateMonitorInterval);
        window.tabStateMonitorInterval = setInterval(validateTabState, 5000);
    });
}

function scrollTabs(direction) {
    const tabsContainer = document.querySelector('.excel-tabs');
    const scrollAmount = 200;
    
    if (direction === 'left') {
        tabsContainer.scrollLeft -= scrollAmount;
    } else {
        tabsContainer.scrollLeft += scrollAmount;
    }
}

// ============ SISTEMA DE COPIA Y PEGADO SIMPLIFICADO ============

// Variables globales para el portapapeles simplificado
let simpleCopiedData = null;
let simpleCutMode = false;

function simpleCopy() {
    console.log('=== SIMPLE COPY START ===');
    
    // Buscar celda actual
    let cell = currentCell;
    if (!cell) {
        cell = $('.cell.selected')[0];
    }
    if (!cell) {
        const focused = $('.cell:focus')[0];
        if (focused) cell = focused;
    }
    
    if (!cell) {
        console.error('No cell found for copy');
        showNotification('No hay celda seleccionada para copiar', 'warning');
        return;
    }
    
    const $cell = $(cell);
    const value = $cell.text() || '';
    const row = parseInt($cell.data('row')) || 1;
    const col = parseInt($cell.data('col')) || 1;
    const sheet = $cell.data('sheet') || 'BUDGET';
    
    // Guardar datos copiados
    simpleCopiedData = {
        value: value,
        row: row,
        col: col,
        sheet: sheet,
        style: $cell.attr('style') || '',
        classes: $cell.attr('class') || ''
    };
    
    simpleCutMode = false;
    
    console.log('Copied data:', simpleCopiedData);
    
    // Feedback visual
    $('.copied-cell').removeClass('copied-cell');
    $cell.addClass('copied-cell');
    
    setTimeout(() => {
        $('.copied-cell').removeClass('copied-cell');
    }, 2000);
    
    showNotification(`Celda copiada: "${value}"`, 'success');
    
    console.log('=== SIMPLE COPY END ===');
}

function simpleCut() {
    console.log('=== SIMPLE CUT START ===');
    
    simpleCopy(); // Primero copiamos
    
    if (simpleCopiedData) {
        simpleCutMode = true;
        
        // Marcar celda para cortar
        let cell = currentCell;
        if (!cell) {
            cell = $('.cell.selected')[0];
        }
        
        if (cell) {
            $(cell).addClass('cut-cell');
        }
        
        showNotification('Celda cortada', 'info');
    }
    
    console.log('=== SIMPLE CUT END ===');
}

function simplePaste() {
    console.log('=== SIMPLE PASTE START ===');
    
    if (!simpleCopiedData) {
        console.error('No data to paste');
        showNotification('No hay datos para pegar', 'warning');
        return;
    }
    
    // Buscar celda destino
    let targetCell = currentCell;
    if (!targetCell) {
        targetCell = $('.cell.selected')[0];
    }
    if (!targetCell) {
        const focused = $('.cell:focus')[0];
        if (focused) targetCell = focused;
    }
    
    if (!targetCell) {
        console.error('No target cell found');
        showNotification('No hay celda destino para pegar', 'warning');
        return;
    }
    
    const $targetCell = $(targetCell);
    
    console.log('Pasting data:', simpleCopiedData);
    console.log('Target cell:', {
        row: $targetCell.data('row'),
        col: $targetCell.data('col'),
        sheet: $targetCell.data('sheet')
    });
    
    // Pegar el valor
    $targetCell.text(simpleCopiedData.value);
    
    // Feedback visual
    $targetCell.addClass('pasted-cell');
    setTimeout(() => {
        $targetCell.removeClass('pasted-cell');
    }, 1000);
    
    // Si era cortar, limpiar celda origen
    if (simpleCutMode) {
        const originSelector = `.cell[data-row="${simpleCopiedData.row}"][data-col="${simpleCopiedData.col}"][data-sheet="${simpleCopiedData.sheet}"]`;
        const $originCell = $(originSelector);
        if ($originCell.length > 0) {
            $originCell.text('');
            $originCell.removeClass('cut-cell');
        }
        simpleCutMode = false;
        simpleCopiedData = null; // Limpiar después de cortar
    }
    
    showNotification(`Datos pegados: "${simpleCopiedData.value}"`, 'success');
    
    console.log('=== SIMPLE PASTE END ===');
}

// Funciones de debug para el sistema simplificado
window.debugSimple = function() {
    console.log('=== SIMPLE CLIPBOARD DEBUG ===');
    console.log('simpleCopiedData:', simpleCopiedData);
    console.log('simpleCutMode:', simpleCutMode);
    console.log('currentCell:', currentCell);
    if (currentCell) {
        const $cell = $(currentCell);
        console.log('currentCell data:', {
            row: $cell.data('row'),
            col: $cell.data('col'),
            sheet: $cell.data('sheet'),
            value: $cell.text()
        });
    }
};

window.testSimpleCopy = function() {
    console.log('Testing simple copy...');
    simpleCopy();
};

window.testSimplePaste = function() {
    console.log('Testing simple paste...');
    simplePaste();
};

// ============ FUNCIONES DEL MENÚ HAMBURGUESA ============

function initializeSheetMenu() {
    const menuBtn = $('#sheetMenuBtn');
    const dropdown = $('#sheetDropdown');
    
    // Toggle del menú
    menuBtn.on('click', function(e) {
        e.stopPropagation();
        dropdown.toggleClass('show');
    });
    
    // Cerrar menú al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.sheet-menu-container').length) {
            dropdown.removeClass('show');
        }
    });
    
    // Manejar selección de hojas
    $('.sheet-item').on('click', function(e) {
        e.stopPropagation();
        
        const sheetName = $(this).data('sheet');
        const sheetColor = $(this).data('color');
        
        // Actualizar estado visual
        $('.sheet-item').removeClass('active');
        $(this).addClass('active');
        
        // Actualizar botón del menú
        updateMenuButton(sheetName);
        
        // Cambiar a la hoja seleccionada
        switchToSheet(sheetName);
        
        // Cerrar menú
        dropdown.removeClass('show');
        
        // Actualizar barra de estado
        updateStatusBar(sheetName);
        
        console.log('Switched to sheet:', sheetName);
    });
    
    // Inicializar con la primera hoja
    const firstSheet = $('.sheet-item.active').data('sheet') || 'BUDGET';
    updateMenuButton(firstSheet);
    updateStatusBar(firstSheet);
}

function updateMenuButton(sheetName) {
    $('.current-sheet-name').text(sheetName);
}

function updateStatusBar(sheetName) {
    $('#currentSheetDisplay').text(sheetName);
    
    // Contar celdas visibles
    const visibleCells = $(`.sheet-content[data-sheet="${sheetName}"] .cell`).length;
    const rows = $(`.sheet-content[data-sheet="${sheetName}"] tr`).length - 1; // -1 para el header
    const cols = $(`.sheet-content[data-sheet="${sheetName}"] tr:first th`).length - 1; // -1 para row numbers
    
    $('#cellCountDisplay').text(`${rows} x ${cols}`);
}

function switchToSheet(sheetName) {
    try {
        // Ocultar todas las hojas
        $('.sheet-content').removeClass('active');
        
        // Mostrar la hoja seleccionada
        const escapedSheet = escapeSelector(sheetName);
        const $targetSheet = $(`#sheet-${escapedSheet}`);
        
        if ($targetSheet.length > 0) {
            $targetSheet.addClass('active');
            currentSheet = sheetName;
            
            // Guardar la selección
            saveActiveTab(sheetName);
            
            // Limpiar selección de celdas
            $('.cell').removeClass('selected');
            currentCell = null;
            
            // Limpiar referencia de celda
            $('#cell-ref').val('');
            $('#formula-input').val('');
            
            console.log(`Successfully switched to sheet: ${sheetName}`);
        } else {
            console.error(`Sheet not found: ${sheetName}`);
            showNotification('Error: Hoja no encontrada', 'error');
        }
    } catch (error) {
        console.error('Error switching sheet:', error);
        showNotification('Error al cambiar de hoja', 'error');
    }
}

// Funciones de zoom
function adjustZoom(delta) {
    const currentZoom = parseInt($('.zoom-indicator').text()) || 100;
    const newZoom = Math.max(50, Math.min(200, currentZoom + delta));
    
    $('.zoom-indicator').text(newZoom + '%');
    
    // Aplicar zoom al contenido
    $('.spreadsheet-content').css('transform', `scale(${newZoom / 100})`);
    $('.spreadsheet-content').css('transform-origin', 'top left');
    
    showNotification(`Zoom: ${newZoom}%`, 'info');
}

// Función para agregar nueva hoja (actualizada)
function addNewSheet() {
    const sheetCount = $('.sheet-item').length;
    const newSheetName = `Hoja${sheetCount + 1}`;
    
    // Agregar a la lista del menú
    const newSheetHtml = `
        <div class="sheet-item" data-sheet="${newSheetName}" data-color="${sheetCount}">
            <div class="sheet-info">
                <div class="sheet-name">${newSheetName}</div>
                <div class="sheet-description">Nueva hoja de trabajo</div>
            </div>
            <div class="sheet-status"></div>
        </div>
    `;
    
    $('.sheet-list').append(newSheetHtml);
    
    // Agregar eventos al nuevo item
    $('.sheet-item').last().on('click', function(e) {
        e.stopPropagation();
        
        const sheetName = $(this).data('sheet');
        
        $('.sheet-item').removeClass('active');
        $(this).addClass('active');
        
        updateMenuButton(sheetName);
        updateStatusBar(sheetName);
        
        $('#sheetDropdown').removeClass('show');
        
        console.log('Switched to new sheet:', sheetName);
    });
    
    showNotification(`Nueva hoja "${newSheetName}" creada`, 'success');
}

// Variables para carga bajo demanda - ya no necesarias pero mantenemos compatibilidad
let loadingMoreData = false;
let currentDataOffset = 2219; // Todos los datos ya están cargados
let hasMoreData = false; // No hay más datos para cargar

// Función para cargar más datos
function loadMoreData() {
    if (loadingMoreData || !hasMoreData || currentSheet !== 'Detallado secciones1') {
        return;
    }
    
    loadingMoreData = true;
    $('#dataLoadProgress').show();
    $('#loadProgressText').text('Cargando más datos...');
    
    $.ajax({
        url: '{{ route("presupuesto.load-more-data") }}',
        method: 'GET',
        data: {
            offset: currentDataOffset,
            limit: 500,
            sheet: currentSheet
        },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                // Procesar los nuevos datos
                const optimizedData = {};
                response.data.forEach(function(cellData) {
                    const row = cellData[0];
                    const col = cellData[1];
                    const value = cellData[2];
                    
                    if (!optimizedData[row]) {
                        optimizedData[row] = {};
                    }
                    optimizedData[row][col] = value;
                });
                
                // Agregar las nuevas filas a la tabla
                const $tbody = $(`#sheet-${currentSheet} tbody`);
                Object.keys(optimizedData).forEach(function(row) {
                    // Verificar si la fila ya existe
                    if ($tbody.find(`tr:has(td[data-row="${row}"])`).length === 0) {
                        // Detectar si es una fila TOTAL
                        const isTotalRow = optimizedData[row][6] === 'TOTAL'; // Columna 6 es "Rubro"
                        const rowClass = isTotalRow ? 'total-row' : '';
                        const cellClass = isTotalRow ? 'total-cell' : '';
                        
                        // Crear nueva fila
                        let $newRow = $(`<tr class="${rowClass}">`);
                        $newRow.append(`<td class="row-header ${cellClass}">${row}</td>`);
                        
                        for (let col = 1; col <= 20; col++) {
                            const cellValue = optimizedData[row][col] || '';
                            
                            $newRow.append(`
                                <td class="cell ${cellClass}" 
                                    data-row="${row}" 
                                    data-col="${col}" 
                                    data-sheet="${currentSheet}"
                                    contenteditable="true">${cellValue}</td>
                            `);
                        }
                        
                        $tbody.append($newRow);
                    } else {
                        // Actualizar fila existente
                        const $existingRow = $tbody.find(`tr:has(td[data-row="${row}"])`);
                        const isTotalRow = optimizedData[row][6] === 'TOTAL';
                        
                        if (isTotalRow) {
                            $existingRow.addClass('total-row');
                            $existingRow.find('td').addClass('total-cell');
                        }
                        
                        Object.keys(optimizedData[row]).forEach(function(col) {
                            const cellValue = optimizedData[row][col];
                            $tbody.find(`td[data-row="${row}"][data-col="${col}"]`).text(cellValue);
                        });
                    }
                });
                
                currentDataOffset += response.data.length / 15; // Dividir por 15 columnas
                hasMoreData = response.hasMore;
                
                // Actualizar indicador de progreso
                const progressPercent = Math.round((response.loadedCount / response.totalCount) * 100);
                $('#loadProgressBar').css('width', progressPercent + '%');
                $('#loadProgressCount').text(`${response.loadedCount}/${response.totalCount} registros`);
                $('#loadProgressText').text(hasMoreData ? 'Datos cargados' : 'Todos los datos cargados');
                
                console.log(`Cargados ${response.loadedCount} de ${response.totalCount} registros`);
                
                if (!hasMoreData) {
                    setTimeout(() => $('#dataLoadProgress').fadeOut(), 2000);
                    showNotification('Todos los datos han sido cargados', 'success');
                } else {
                    showNotification(`Se cargaron más datos. Total: ${response.loadedCount}/${response.totalCount}`, 'info');
                }
            }
            
            loadingMoreData = false;
        },
        error: function(xhr, status, error) {
            console.error('Error cargando más datos:', error);
            loadingMoreData = false;
            $('#dataLoadProgress').hide();
            showNotification('Error al cargar más datos', 'error');
        }
    });
}

// Detectar scroll para carga automática
$(document).ready(function() {
    // Inicializar estilos de filas TOTAL existentes
    initializeTotalRowStyles();
    
    // Inicializar sistema de filtros
    initializeFilters();
    
    // Verificar que los filtros se inicializaron correctamente
    setTimeout(() => {
        const filterCount = $('.filter-icon').length;
        console.log('Filtros inicializados:', filterCount);
        if (filterCount > 0) {
            console.log('✅ Sistema de filtros listo');
        } else {
            console.error('❌ No se encontraron filtros');
        }
    }, 1000);
    
    // Mostrar progreso inicial para Detallado secciones1
    if (currentSheet === 'Detallado secciones1') {
        $('#dataLoadProgress').show();
        $('#loadProgressBar').css('width', '100%'); // Todos los datos cargados
        $('#loadProgressCount').text('2219/2219 registros');
        $('#loadProgressText').text('Todos los datos cargados');
        
        // Ocultar después de 3 segundos
        setTimeout(() => $('#dataLoadProgress').fadeOut(), 3000);
    }
    
    $('.table-responsive').on('scroll', function() {
        // Funcionalidad de scroll mantenida para compatibilidad
        // pero ya no es necesaria cargar más datos
    });
    
    // Ya no es necesario el botón "Más datos" pero mantenemos el espacio por compatibilidad
});

// Función para inicializar el sistema de recálculo de totales
function initializeTotalCalculations() {
    console.log('Inicializando sistema de recálculo de totales...');
    
    // Agregar event listener para cambios en cualquier celda de Detallado secciones1
    $(document).on('blur', 'td.cell[contenteditable="true"]', function() {
        const $cell = $(this);
        const sheet = $cell.data('sheet');
        
        // Solo procesar si estamos en la hoja correcta
        if (sheet !== 'Detallado secciones1') {
            return;
        }
        
        const newValue = $cell.text().trim();
        const column = $cell.data('col');
        const row = $cell.data('row');
        
        console.log(`Celda modificada - Fila: ${row}, Columna: ${column}, Valor: ${newValue}`);
        
        // No procesar celdas de encabezado (fila 1)
        if (row <= 1) return;
        
        // Verificar si es una celda de una fila TOTAL
        const $row = $cell.closest('tr');
        const $rubroCell = $row.find('td[data-col="6"]');
        const rubro = $rubroCell.text().trim();
        const isTotal = (rubro === 'TOTAL');
        
        if (isTotal && column == 8) {
            // No permitir edición directa de valores en filas TOTAL
            showNotification('No se puede editar directamente el total. Modifica los valores individuales.', 'warning');
            // Restaurar valor anterior si es posible
            return;
        }
        
        // Actualizar la celda en la base de datos
        updateCellInDatabase(row, column, newValue);
        
        // Si es la columna Valor (8) y no es TOTAL, recalcular totales
        if (column == 8 && !isTotal) {
            const $sectionCell = $row.find('td[data-col="5"]'); // Columna 5 es "Sección"
            const section = $sectionCell.text().trim();
            
            console.log(`Recalculando total para sección: ${section}`);
            
            if (section) {
                setTimeout(() => {
                    recalculateSectionTotal(section);
                }, 500);
            }
        }
    });
    
    // También procesar cuando se presiona Enter
    $(document).on('keypress', 'td.cell[contenteditable="true"]', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $(this).blur();
        }
    });
    
    console.log('Sistema de recálculo inicializado correctamente');
}

// Función para actualizar una celda en la base de datos
function updateCellInDatabase(row, column, value) {
    $.ajax({
        url: '/presupuesto/update-cell',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            row: row,
            column: column,
            value: value
        },
        success: function(response) {
            if (response.success) {
                console.log('Celda actualizada:', response);
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error actualizando celda:', error);
            showNotification('Error al actualizar la celda', 'error');
        }
    });
}

// Función para recalcular el total de una sección específica
function recalculateSectionTotal(section) {
    let total = 0;
    let totalRowFound = false;
    let $totalRow = null;
    
    // Buscar todas las filas de esta sección y sumar los valores
    $('tr').each(function() {
        const $row = $(this);
        const $sectionCell = $row.find('td[data-col="5"]'); // Columna Sección
        const $rubroCell = $row.find('td[data-col="6"]'); // Columna Rubro
        const $valorCell = $row.find('td[data-col="8"]'); // Columna Valor
        
        const rowSection = $sectionCell.text().trim();
        const rubro = $rubroCell.text().trim();
        
        if (rowSection === section) {
            if (rubro === 'TOTAL') {
                // Esta es la fila TOTAL para esta sección
                $totalRow = $row;
                totalRowFound = true;
            } else {
                // Esta es una fila de datos, sumar al total
                const valorText = $valorCell.text().trim();
                const valor = parseValueFromText(valorText);
                if (!isNaN(valor)) {
                    total += valor;
                }
            }
        }
    });
    
    // Actualizar la fila TOTAL si se encontró
    if (totalRowFound && $totalRow) {
        const formattedTotal = formatCurrencyValue(total);
        const $totalValueCell = $totalRow.find('td[data-col="8"]');
        $totalValueCell.text(formattedTotal);
        
        // Mostrar notificación
        showNotification(`Total actualizado para ${section}: ${formattedTotal}`, 'success');
        
        // Enviar actualización al servidor
        updateTotalInDatabase(section, total);
    }
}

// Función para extraer valor numérico del texto formateado
function parseValueFromText(text) {
    if (!text || text === '') return 0;
    
    // Remover símbolo de peso, espacios y puntos de miles
    let cleanText = text.replace(/\$/g, '').replace(/\s/g, '').replace(/\./g, '');
    
    // Reemplazar coma decimal por punto
    cleanText = cleanText.replace(/,/g, '.');
    
    // Convertir a número
    const number = parseFloat(cleanText);
    return isNaN(number) ? 0 : number;
}

// Función para formatear valor como moneda colombiana
function formatCurrencyValue(value) {
    if (isNaN(value)) return '$ 0,00';
    
    // Formatear como moneda colombiana
    const formatted = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
    
    return formatted.replace('COP', '$').trim();
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear una notificación simple
    const notification = $(`
        <div class="alert alert-${type} alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            ${message}
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-dismiss después de 3 segundos
    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}

// Función para actualizar el total en la base de datos
function updateTotalInDatabase(section, total) {
    // Enviar actualización al servidor mediante AJAX
    $.ajax({
        url: '/presupuesto/update-total',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            section: section,
            total: total
        },
        success: function(response) {
            console.log('Total actualizado en base de datos:', response);
        },
        error: function(xhr, status, error) {
            console.error('Error actualizando total:', error);
            showNotification('Error al actualizar total en base de datos', 'error');
        }
    });
}

// Variables globales para filtros
let columnFilters = {};
let activeFilters = {};

// Función para inicializar sistema de filtros
function initializeFilters() {
    console.log('Inicializando sistema de filtros...');
    
    // Verificar si existen iconos de filtro
    setTimeout(() => {
        const filterIcons = $('.filter-icon');
        console.log('Iconos de filtro encontrados:', filterIcons.length);
        
        if (filterIcons.length === 0) {
            console.warn('No se encontraron iconos de filtro. Reintentando...');
            // Reintentar después de que se cargue completamente
            setTimeout(initializeFilters, 1000);
            return;
        }
        
        filterIcons.each(function() {
            console.log('Icono de filtro para columna:', $(this).data('col'));
        });
    }, 100);
    
    // Event listener para abrir/cerrar filtros
    $(document).on('click', '.filter-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Click en filtro de columna:', $(this).data('col'));
        
        const col = $(this).data('col');
        const dropdown = $('#filter-dropdown-' + col);
        
        console.log('Dropdown encontrado:', dropdown.length);
        
        // Cerrar otros dropdowns
        $('.filter-dropdown').not(dropdown).hide();
        $('.filter-icon').not(this).removeClass('active');
        
        if (dropdown.is(':visible')) {
            dropdown.hide();
            $(this).removeClass('active');
            console.log('Dropdown cerrado');
        } else {
            // Populate filter options before showing
            console.log('Poblando opciones de filtro...');
            populateFilterOptions(col);
            dropdown.show();
            $(this).addClass('active');
            console.log('Dropdown abierto');
        }
    });
    
    // Cerrar dropdown al hacer click fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-dropdown-container').length) {
            $('.filter-dropdown').hide();
            $('.filter-icon').removeClass('active');
        }
    });
    
    // Event listeners para filtros
    $(document).on('change', '.filter-option input[type="checkbox"]', function() {
        const col = $(this).closest('.filter-dropdown').attr('id').replace('filter-dropdown-', '');
        updateSelectAllStatus(col);
    });
    
    // Búsqueda en filtros
    $(document).on('input', '.filter-search-input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const col = $(this).data('col');
        const options = $('#filter-values-' + col + ' .filter-option');
        
        options.each(function() {
            const text = $(this).find('label').text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });
    
    // Aplicar filtro
    $(document).on('click', '.apply-filter', function() {
        const col = $(this).data('col');
        applyColumnFilter(col);
        $('.filter-dropdown').hide();
        $('.filter-icon').removeClass('active');
    });
    
    // Limpiar filtro
    $(document).on('click', '.clear-filter', function() {
        const col = $(this).data('col');
        clearColumnFilter(col);
        $('.filter-dropdown').hide();
        $('.filter-icon').removeClass('active');
    });
    
    // Seleccionar/deseleccionar todo
    $(document).on('change', 'input[id^="select-all-"]', function() {
        const col = $(this).attr('id').replace('select-all-', '');
        const checked = $(this).is(':checked');
        $('#filter-dropdown-' + col + ' .filter-option input[type="checkbox"]').prop('checked', checked);
    });
}
}

// Función para poblar opciones de filtro
function populateFilterOptions(col) {
    console.log('Poblando filtro para columna:', col);
    
    const dropdown = $('#filter-dropdown-' + col);
    const valuesContainer = $('#filter-values-' + col);
    
    console.log('Dropdown encontrado:', dropdown.length);
    console.log('Values container encontrado:', valuesContainer.length);
    
    // Si ya está poblado, no hacer nada
    if (valuesContainer.children().length > 0) {
        console.log('Filtro ya poblado, saltando...');
        return;
    }
    
    const uniqueValues = new Set();
    const activeSheet = $('.sheet-content.active');
    
    console.log('Hoja activa:', activeSheet.length);
    
    // Obtener valores únicos de la columna
    let cellCount = 0;
    activeSheet.find('tbody tr').each(function() {
        const cell = $(this).find(`td[data-col="${col}"]`);
        if (cell.length) {
            cellCount++;
            const value = cell.text().trim();
            if (value !== '') {
                uniqueValues.add(value);
            }
        }
    });
    
    console.log(`Celdas procesadas: ${cellCount}, Valores únicos: ${uniqueValues.size}`);
    
    // Ordenar valores
    const sortedValues = Array.from(uniqueValues).sort();
    
    // Agregar opciones al dropdown
    sortedValues.slice(0, 50).forEach((value, index) => { // Limitar a 50 para evitar lag
        const safeId = `filter-${col}-${index}`;
        const option = $(`
            <div class="filter-option">
                <input type="checkbox" id="${safeId}" checked data-value="${value}">
                <label for="${safeId}">${value}</label>
            </div>
        `);
        valuesContainer.append(option);
    });
    
    if (sortedValues.length > 50) {
        valuesContainer.append(`<div class="filter-option"><small>... y ${sortedValues.length - 50} más</small></div>`);
    }
    
    console.log(`✅ Filtro poblado para columna ${col}: ${Math.min(sortedValues.length, 50)} opciones mostradas`);
}

// Función para aplicar filtro de columna
function applyColumnFilter(col) {
    console.log('Aplicando filtro para columna:', col);
    
    const selectedValues = new Set();
    const includeBlank = $(`#blanks-${col}`).is(':checked');
    
    // Obtener valores seleccionados usando el atributo data-value
    $(`#filter-values-${col} input[type="checkbox"]:checked`).each(function() {
        const value = $(this).data('value') || $(this).next('label').text();
        if (value && value.trim() !== '') {
            selectedValues.add(value);
        }
    });
    
    console.log('Valores seleccionados:', selectedValues);
    console.log('Incluir blancos:', includeBlank);
    
    // Guardar filtro activo
    activeFilters[col] = {
        values: selectedValues,
        includeBlank: includeBlank
    };
    
    // Aplicar todos los filtros
    applyAllFilters();
    
    // Marcar icono como activo si hay filtro activo
    const hasActiveFilter = selectedValues.size < $('.sheet-content.active tbody tr').find(`td[data-col="${col}"]`).length || !includeBlank;
    
    if (hasActiveFilter) {
        $(`.filter-icon[data-col="${col}"]`).addClass('active');
    } else {
        $(`.filter-icon[data-col="${col}"]`).removeClass('active');
    }
    
    console.log(`✅ Filtro aplicado a columna ${col}: ${selectedValues.size} valores seleccionados`);
}

// Función para limpiar filtro de columna
function clearColumnFilter(col) {
    delete activeFilters[col];
    
    // Resetear checkboxes
    $(`#filter-dropdown-${col} input[type="checkbox"]`).prop('checked', true);
    
    // Aplicar filtros restantes
    applyAllFilters();
    
    // Quitar marca activa del icono
    $(`.filter-icon[data-col="${col}"]`).removeClass('active');
    
    console.log(`Filtro limpiado para columna ${col}`);
}

// Función para aplicar todos los filtros activos
function applyAllFilters() {
    const currentSheet = $('.sheet-content.active');
    let visibleCount = 0;
    let totalCount = 0;
    
    currentSheet.find('tbody tr').each(function() {
        const $row = $(this);
        let showRow = true;
        totalCount++;
        
        // Aplicar cada filtro activo
        for (const [col, filter] of Object.entries(activeFilters)) {
            const cell = $row.find(`td[data-col="${col}"]`);
            const cellValue = cell.text().trim();
            
            if (cellValue === '') {
                if (!filter.includeBlank) {
                    showRow = false;
                    break;
                }
            } else {
                if (!filter.values.has(cellValue)) {
                    showRow = false;
                    break;
                }
            }
        }
        
        if (showRow) {
            $row.show();
            visibleCount++;
        } else {
            $row.hide();
        }
    });
    
    console.log(`Filtros aplicados: ${visibleCount}/${totalCount} filas visibles`);
    
    // Mostrar contador de filtros (opcional)
    if (Object.keys(activeFilters).length > 0) {
        showFilterStatus(visibleCount, totalCount);
    } else {
        hideFilterStatus();
    }
}

// Función para actualizar estado de "Seleccionar todo"
function updateSelectAllStatus(col) {
    const dropdown = $('#filter-dropdown-' + col);
    const allOptions = dropdown.find('.filter-option:not(:first) input[type="checkbox"]');
    const checkedOptions = dropdown.find('.filter-option:not(:first) input[type="checkbox"]:checked');
    const selectAll = dropdown.find('#select-all-' + col);
    
    if (checkedOptions.length === 0) {
        selectAll.prop('indeterminate', false).prop('checked', false);
    } else if (checkedOptions.length === allOptions.length) {
        selectAll.prop('indeterminate', false).prop('checked', true);
    } else {
        selectAll.prop('indeterminate', true);
    }
}

// Función para mostrar estado de filtros
function showFilterStatus(visible, total) {
    let statusDiv = $('#filter-status');
    if (statusDiv.length === 0) {
        statusDiv = $(`
            <div id="filter-status" class="alert alert-info" style="margin: 10px 0; padding: 8px 12px;">
                <i class="fas fa-filter"></i> <span id="filter-count"></span>
                <button class="btn btn-sm btn-outline-secondary float-right" onclick="clearAllFilters()">Limpiar todos los filtros</button>
            </div>
        `);
        $('.sheet-content.active .table-responsive').before(statusDiv);
    }
    
    $('#filter-count').text(`Mostrando ${visible} de ${total} filas`);
    statusDiv.show();
}

// Función para ocultar estado de filtros
function hideFilterStatus() {
    $('#filter-status').hide();
}

// Función para limpiar todos los filtros
function clearAllFilters() {
    activeFilters = {};
    $('.filter-icon').removeClass('active');
    $('.sheet-content.active tbody tr').show();
    hideFilterStatus();
    console.log('Todos los filtros limpiados');
}

// Función para inicializar estilos de filas TOTAL
function initializeTotalRowStyles() {
    $('.excel-table tbody tr').each(function() {
        const $row = $(this);
        const rubroCell = $row.find('td[data-col="6"]').text().trim(); // Columna 6 es "Rubro"
        
        if (rubroCell === 'TOTAL') {
            $row.addClass('total-row');
            $row.find('td').addClass('total-cell');
            
            // Opcional: agregar iconos solo si no causan problemas
            // const $rubroCell = $row.find('td[data-col="6"]');
            // if (!$rubroCell.find('.total-icon').length) {
            //     $rubroCell.prepend('<i class="fas fa-calculator total-icon" style="margin-right: 5px;"></i>');
            // }
        }
    });
}
</script>
@stop
