@extends('adminlte::page')

@section('title', 'Reporte de Ingresos de Estudiantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Reporte de Ingresos de Estudiantes</h1>
        <a href="{{ route('enfermeria.ingreso_estudiantes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <!-- Card de Filtros -->
    <div class="card card-primary collapsed-card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form id="filterForm" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">
                            <i class="fas fa-calendar"></i> Fecha Desde
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_desde" 
                               name="fecha_desde">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">
                            <i class="fas fa-calendar"></i> Fecha Hasta
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_hasta" 
                               name="fecha_hasta">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtro_seccion">
                            <i class="fas fa-graduation-cap"></i> Sección
                        </label>
                        <select class="form-control" id="filtro_seccion" name="filtro_seccion">
                            <option value="">Todas las secciones</option>
                            <option value="preescolar">Preescolar</option>
                            <option value="primaria">Primaria</option>
                            <option value="bachillerato">Bachillerato</option>
                            <option value="deportivas">Actividades Deportivas</option>
                            <option value="especiales">Casos Especiales</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtro_cantidad">
                            <i class="fas fa-sort-amount-down"></i> Cantidad Mínima
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="filtro_cantidad" 
                               name="filtro_cantidad"
                               min="0"
                               placeholder="Ej: 5">
                    </div>
                </div>
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-info" onclick="aplicarFiltroRapido('hoy')">
                            <i class="fas fa-calendar-day"></i> Hoy
                        </button>
                        <button type="button" class="btn btn-info" onclick="aplicarFiltroRapido('semana')">
                            <i class="fas fa-calendar-week"></i> Esta Semana
                        </button>
                        <button type="button" class="btn btn-info" onclick="aplicarFiltroRapido('mes')">
                            <i class="fas fa-calendar-alt"></i> Este Mes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card de Reporte -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Resumen por Fecha y Área
                <span id="filtro-activo-badge" class="badge badge-info ml-2" style="display: none;">
                    Filtro Activo
                </span>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="exportTableToExcel('reportTable', 'Reporte_Ingresos_Estudiantes')">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportTable" class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">FECHA</th>
                            <th class="text-center">PREESCOLAR</th>
                            <th class="text-center">PRIMARIA</th>
                            <th class="text-center">BACHILLERATO</th>
                            <th class="text-center">ACTIVIDADES DEPORTIVAS</th>
                            <th class="text-center">CASOS ESPECIALES</th>
                            <th class="text-center">SALIDAS</th>
                            <th class="text-center" style="min-width: 200px;">OBSERVACIONES</th>
                            <th class="text-center" style="min-width: 200px;">NOVEDADES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporteData as $dato)
                            <tr>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($dato->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $dato->preescolar }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $dato->primaria }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $dato->bachillerato }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning">{{ $dato->deportivas }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">{{ $dato->casos_especiales }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $dato->salidas }}</span>
                                </td>
                                <td class="text-left">
                                    <small>{{ $dato->observaciones ?: 'Sin observaciones' }}</small>
                                </td>
                                <td class="text-left">
                                    <small class="text-danger">
                                        <strong>{{ $dato->novedades ?: 'Sin novedades' }}</strong>
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay datos disponibles para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($reporteData->count() > 0)
                        <tfoot class="bg-light">
                            <tr>
                                <td class="text-right"><strong>TOTALES:</strong></td>
                                <td class="text-center">
                                    <strong class="text-info">{{ $reporteData->sum('preescolar') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-primary">{{ $reporteData->sum('primaria') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ $reporteData->sum('bachillerato') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{{ $reporteData->sum('deportivas') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-danger">{{ $reporteData->sum('casos_especiales') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-info">{{ $reporteData->sum('salidas') }}</strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 14px;
            padding: 5px 10px;
        }
        thead.thead-dark th {
            background-color: #304367 !important;
            color: white !important;
            font-weight: bold;
        }
        
        /* Estilos para el card de filtros */
        .card-primary {
            border-top: 3px solid #364E76;
        }
        
        .card-primary .card-header {
            background-color: #364E76;
            color: white;
        }
        
        /* Estilos para el formulario de filtros */
        #filterForm .form-group label {
            font-weight: 600;
            color: #364E76;
        }
        
        #filterForm .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        #filterForm .form-control:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        
        /* Botones del filtro */
        .btn-group button {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        /* Badge de filtro activo */
        #filtro-activo-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
        
        /* Mejorar aspecto del collapsed card */
        .collapsed-card .card-body {
            display: none;
        }
        
        .card-tools .btn-tool {
            color: white;
        }
        
        .card-tools .btn-tool:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
            }
            
            .btn-group button {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
@stop

@section('js')
    <!-- SheetJS para exportar Excel con formato profesional -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        function exportTableToExcel(tableID, filename = '') {
            // Verificar si hay filtros activos
            const filtroActivo = document.getElementById('filtro-activo-badge').style.display !== 'none';
            
            // Crear un nuevo workbook
            const wb = XLSX.utils.book_new();
            
            // Preparar los datos
            const data = [];
            
            // Agregar información de filtros aplicados (si existen)
            if (filtroActivo) {
                const fechaDesde = document.getElementById('fecha_desde').value;
                const fechaHasta = document.getElementById('fecha_hasta').value;
                const seccion = document.getElementById('filtro_seccion').value;
                const cantidadMin = document.getElementById('filtro_cantidad').value;
                
                data.push(['REPORTE DE INGRESOS DE ESTUDIANTES - ENFERMERÍA']);
                data.push(['FILTROS APLICADOS:']);
                
                if (fechaDesde) data.push(['Fecha Desde:', fechaDesde]);
                if (fechaHasta) data.push(['Fecha Hasta:', fechaHasta]);
                if (seccion) data.push(['Sección:', seccion.toUpperCase()]);
                if (cantidadMin) data.push(['Cantidad Mínima:', cantidadMin]);
                
                data.push([]); // Línea en blanco
            }
            
            // Encabezados con formato
            data.push([
                'FECHA',
                'PREESCOLAR',
                'PRIMARIA',
                'BACHILLERATO',
                'ACTIVIDADES DEPORTIVAS',
                'CASOS ESPECIALES',
                'SALIDAS',
                'OBSERVACIONES',
                'NOVEDADES'
            ]);
            
            // Obtener filas visibles directamente del DOM (para texto limpio sin HTML)
            if (tablaOriginal && $.fn.DataTable.isDataTable('#reportTable')) {
                // Usar DataTables para obtener índices de filas visibles/filtradas
                const filasVisibles = tablaOriginal.rows({ search: 'applied' }).nodes();
                
                // Recorrer cada fila visible
                $(filasVisibles).each(function() {
                    const cells = $(this).find('td');
                    if (cells.length > 0 && cells[0].colSpan !== 9) { // Evitar fila de "No hay datos"
                        const rowData = [];
                        
                        cells.each(function(index) {
                            // Extraer texto limpio sin HTML
                            let text = $(this).text().trim();
                            
                            // Limpiar texto adicional
                            text = text.replace(/\s+/g, ' '); // Reemplazar múltiples espacios por uno
                            
                            // Para columnas numéricas (índices 1-6), convertir a número
                            if (index >= 1 && index <= 6) {
                                const num = parseInt(text);
                                rowData.push(isNaN(num) ? 0 : num);
                            } else if (index === 7 || index === 8) {
                                // Para observaciones y novedades, limpiar más
                                text = text.replace(/\n/g, ' ').replace(/\t/g, ' ').trim();
                                
                                // Si es "Sin observaciones" o "Sin novedades", simplificar
                                if (text.includes('Sin observaciones')) {
                                    text = 'Sin observaciones';
                                } else if (text.includes('Sin novedades')) {
                                    text = 'Sin novedades';
                                }
                                
                                rowData.push(text || '');
                            } else {
                                rowData.push(text);
                            }
                        });
                        
                        data.push(rowData);
                    }
                });
            } else {
                // Fallback: obtener todas las filas visibles del DOM
                const tbody = document.querySelector('#reportTable tbody');
                const rows = tbody.querySelectorAll('tr');
                
                rows.forEach(row => {
                    // Solo exportar filas visibles
                    if (row.style.display !== 'none' && !row.classList.contains('d-none')) {
                        const cells = row.querySelectorAll('td');
                        if (cells.length > 0 && cells[0].colSpan !== 9) {
                            const rowData = [];
                            
                            cells.forEach((cell, index) => {
                                let text = cell.textContent.trim();
                                text = text.replace(/\s+/g, ' ');
                                
                                if (index >= 1 && index <= 6) {
                                    const num = parseInt(text);
                                    rowData.push(isNaN(num) ? 0 : num);
                                } else if (index === 7 || index === 8) {
                                    text = text.replace(/\n/g, ' ').replace(/\t/g, ' ').trim();
                                    
                                    if (text.includes('Sin observaciones')) {
                                        text = 'Sin observaciones';
                                    } else if (text.includes('Sin novedades')) {
                                        text = 'Sin novedades';
                                    }
                                    
                                    rowData.push(text || '');
                                } else {
                                    rowData.push(text);
                                }
                            });
                            
                            data.push(rowData);
                        }
                    }
                });
            }
            
            // Agregar fila de totales (usar los valores filtrados actuales)
            const tfoot = document.querySelector('#reportTable tfoot');
            if (tfoot) {
                const cells = tfoot.querySelectorAll('td');
                const rowData = [];
                cells.forEach((cell, index) => {
                    // Extraer texto limpio sin HTML
                    let text = cell.textContent.trim();
                    text = text.replace(/\s+/g, ' ');
                    
                    if (index >= 1 && index <= 6) {
                        const num = parseInt(text);
                        rowData.push(isNaN(num) ? 0 : num);
                    } else {
                        // Limpiar texto como "TOTALES:"
                        rowData.push(text);
                    }
                });
                data.push(rowData);
            }
            
            // Crear worksheet desde los datos
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Determinar fila de inicio de datos (depende si hay filtros)
            const headerRow = filtroActivo ? data.findIndex(row => row[0] === 'FECHA') : 0;
            
            // Configurar anchos de columna
            ws['!cols'] = [
                { wch: 12 },  // FECHA
                { wch: 12 },  // PREESCOLAR
                { wch: 10 },  // PRIMARIA
                { wch: 15 },  // BACHILLERATO
                { wch: 20 },  // ACTIVIDADES DEPORTIVAS
                { wch: 17 },  // CASOS ESPECIALES
                { wch: 10 },  // SALIDAS
                { wch: 40 },  // OBSERVACIONES
                { wch: 40 }   // NOVEDADES
            ];
            
            // Aplicar estilos a la fila de encabezado
            const range = XLSX.utils.decode_range(ws['!ref']);
            
            // Si hay filtros, aplicar estilo especial a las primeras filas
            if (filtroActivo && headerRow > 0) {
                // Estilo para el título principal (primera fila)
                for (let C = range.s.c; C <= range.e.c; ++C) {
                    const address = XLSX.utils.encode_col(C) + "1";
                    if (!ws[address]) continue;
                    
                    ws[address].s = {
                        font: { bold: true, size: 14, color: { rgb: "364E76" } },
                        alignment: { horizontal: "center", vertical: "center" }
                    };
                }
                
                // Estilo para "FILTROS APLICADOS:" (segunda fila)
                const filtrosLabel = XLSX.utils.encode_col(0) + "2";
                if (ws[filtrosLabel]) {
                    ws[filtrosLabel].s = {
                        font: { bold: true, color: { rgb: "E74C3C" } },
                        alignment: { horizontal: "left", vertical: "center" }
                    };
                }
                
                // Estilo para las líneas de filtros (filas 3 en adelante hasta headerRow)
                for (let R = 2; R < headerRow; R++) {
                    for (let C = range.s.c; C <= range.e.c; ++C) {
                        const address = XLSX.utils.encode_col(C) + (R + 1);
                        if (!ws[address]) continue;
                        
                        ws[address].s = {
                            font: { italic: true, color: { rgb: "555555" } },
                            fill: { fgColor: { rgb: "FFF9E6" } },
                            alignment: { horizontal: "left", vertical: "center" }
                        };
                    }
                }
            }
            
            // Estilo para encabezados de tabla (fila FECHA, PREESCOLAR, etc.)
            const headerRowNum = headerRow + 1; // +1 porque Excel es 1-indexed
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const address = XLSX.utils.encode_col(C) + headerRowNum;
                if (!ws[address]) continue;
                
                ws[address].s = {
                    font: { bold: true, color: { rgb: "FFFFFF" } },
                    fill: { fgColor: { rgb: "364E76" } },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            }
            
            // Centrar columnas numéricas (columnas 1-6 = B-G) en filas de datos
            for (let R = headerRow + 1; R <= range.e.r - 1; ++R) { // -1 para no incluir totales
                for (let C = 1; C <= 6; ++C) {
                    const address = XLSX.utils.encode_col(C) + (R + 1);
                    if (!ws[address]) continue;
                    
                    ws[address].s = {
                        alignment: { horizontal: "center", vertical: "center" }
                    };
                }
            }
            
            // Estilo para la última fila (totales)
            const lastRow = range.e.r + 1;
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const address = XLSX.utils.encode_col(C) + lastRow;
                if (!ws[address]) continue;
                
                ws[address].s = {
                    font: { bold: true },
                    fill: { fgColor: { rgb: "F0F0F0" } },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            }
            
            // Agregar la hoja al workbook
            XLSX.utils.book_append_sheet(wb, ws, "Reporte Estudiantes");
            
            // Generar nombre de archivo con fecha y filtros
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            
            let finalFilename = `${filename}_${dateStr}`;
            
            // Agregar información de filtros al nombre del archivo
            if (filtroActivo) {
                const seccion = document.getElementById('filtro_seccion').value;
                if (seccion) {
                    finalFilename += `_${seccion}`;
                }
                
                const fechaDesde = document.getElementById('fecha_desde').value;
                const fechaHasta = document.getElementById('fecha_hasta').value;
                if (fechaDesde && fechaHasta) {
                    finalFilename += `_${fechaDesde}_a_${fechaHasta}`;
                } else if (fechaDesde) {
                    finalFilename += `_desde_${fechaDesde}`;
                } else if (fechaHasta) {
                    finalFilename += `_hasta_${fechaHasta}`;
                }
                
                finalFilename += '_FILTRADO';
            }
            
            finalFilename += '.xlsx';
            
            // Descargar el archivo
            XLSX.writeFile(wb, finalFilename);
            
            // Mostrar mensaje de éxito con información
            const mensaje = filtroActivo 
                ? '✅ Archivo Excel exportado con filtros aplicados: ' + finalFilename
                : '✅ Archivo Excel exportado exitosamente: ' + finalFilename;
            
            console.log(mensaje);
            
            // Notificación visual
            if (typeof toastr !== 'undefined') {
                toastr.success(filtroActivo ? 'Excel exportado con filtros aplicados' : 'Excel exportado exitosamente');
            }
        }

        // ========================================
        // SISTEMA DE FILTROS
        // ========================================
        
        let tablaOriginal; // Variable para almacenar la instancia de DataTable
        
        // DataTables initialization with date sorting
        $(document).ready(function() {
            if ($.fn.DataTable) {
                tablaOriginal = $('#reportTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    "order": [[0, "desc"]],
                    "pageLength": 25,
                    "responsive": true,
                    "dom": 'Bfrtip',
                    "buttons": []
                });
            }
        });
        
        // Función para aplicar filtros
        function aplicarFiltros() {
            const fechaDesde = document.getElementById('fecha_desde').value;
            const fechaHasta = document.getElementById('fecha_hasta').value;
            const seccion = document.getElementById('filtro_seccion').value;
            const cantidadMin = parseInt(document.getElementById('filtro_cantidad').value) || 0;
            
            let filtroActivo = false;
            
            // Limpiar filtros anteriores
            if (tablaOriginal) {
                tablaOriginal.search('').columns().search('').draw();
            }
            
            // Filtrar por fecha
            if (fechaDesde || fechaHasta) {
                filtroActivo = true;
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        // data[0] contiene la fecha
                        const fechaFila = parseFecha(data[0]);
                        
                        const desde = fechaDesde ? new Date(fechaDesde) : null;
                        const hasta = fechaHasta ? new Date(fechaHasta) : null;
                        
                        if (desde && fechaFila < desde) return false;
                        if (hasta && fechaFila > hasta) return false;
                        
                        return true;
                    }
                );
            }
            
            // Filtrar por sección
            if (seccion) {
                filtroActivo = true;
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        let columnaIndex;
                        
                        switch(seccion) {
                            case 'preescolar':
                                columnaIndex = 1;
                                break;
                            case 'primaria':
                                columnaIndex = 2;
                                break;
                            case 'bachillerato':
                                columnaIndex = 3;
                                break;
                            case 'deportivas':
                                columnaIndex = 4;
                                break;
                            case 'especiales':
                                columnaIndex = 5;
                                break;
                            default:
                                return true;
                        }
                        
                        const valor = parseInt(data[columnaIndex]) || 0;
                        return valor > 0;
                    }
                );
            }
            
            // Filtrar por cantidad mínima
            if (cantidadMin > 0) {
                filtroActivo = true;
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        // Sumar todas las columnas numéricas (índices 1-6)
                        let total = 0;
                        for (let i = 1; i <= 6; i++) {
                            total += parseInt(data[i]) || 0;
                        }
                        return total >= cantidadMin;
                    }
                );
            }
            
            // Redibujar la tabla
            if (tablaOriginal) {
                tablaOriginal.draw();
            }
            
            // Actualizar totales
            recalcularTotales();
            
            // Mostrar badge de filtro activo
            const badge = document.getElementById('filtro-activo-badge');
            if (badge) {
                badge.style.display = filtroActivo ? 'inline-block' : 'none';
            }
            
            // Mensaje de confirmación
            if (filtroActivo) {
                toastr.success('Filtros aplicados correctamente');
            }
        }
        
        // Función para limpiar filtros
        function limpiarFiltros() {
            // Limpiar campos del formulario
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
            document.getElementById('filtro_seccion').value = '';
            document.getElementById('filtro_cantidad').value = '';
            
            // Limpiar filtros de DataTables
            $.fn.dataTable.ext.search = [];
            
            if (tablaOriginal) {
                tablaOriginal.search('').columns().search('').draw();
            }
            
            // Recalcular totales
            recalcularTotales();
            
            // Ocultar badge
            const badge = document.getElementById('filtro-activo-badge');
            if (badge) {
                badge.style.display = 'none';
            }
            
            toastr.info('Filtros limpiados');
        }
        
        // Función para aplicar filtros rápidos
        function aplicarFiltroRapido(tipo) {
            const hoy = new Date();
            let desde, hasta;
            
            switch(tipo) {
                case 'hoy':
                    desde = hoy;
                    hasta = hoy;
                    break;
                case 'semana':
                    // Inicio de la semana (domingo)
                    desde = new Date(hoy);
                    desde.setDate(hoy.getDate() - hoy.getDay());
                    hasta = hoy;
                    break;
                case 'mes':
                    // Primer día del mes
                    desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    hasta = hoy;
                    break;
            }
            
            // Formatear fechas para input type="date"
            document.getElementById('fecha_desde').value = formatearFechaInput(desde);
            document.getElementById('fecha_hasta').value = formatearFechaInput(hasta);
            
            // Aplicar filtros
            aplicarFiltros();
        }
        
        // Función para parsear fecha del formato dd/mm/yyyy
        function parseFecha(fechaStr) {
            const partes = fechaStr.split('/');
            if (partes.length === 3) {
                return new Date(partes[2], partes[1] - 1, partes[0]);
            }
            return new Date(fechaStr);
        }
        
        // Función para formatear fecha para input
        function formatearFechaInput(fecha) {
            const year = fecha.getFullYear();
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        // Función para recalcular totales de las filas visibles
        function recalcularTotales() {
            if (!tablaOriginal) return;
            
            const filasVisibles = tablaOriginal.rows({ search: 'applied' }).data();
            
            let totales = {
                preescolar: 0,
                primaria: 0,
                bachillerato: 0,
                deportivas: 0,
                especiales: 0,
                salidas: 0
            };
            
            filasVisibles.each(function(fila) {
                totales.preescolar += parseInt(fila[1]) || 0;
                totales.primaria += parseInt(fila[2]) || 0;
                totales.bachillerato += parseInt(fila[3]) || 0;
                totales.deportivas += parseInt(fila[4]) || 0;
                totales.especiales += parseInt(fila[5]) || 0;
                totales.salidas += parseInt(fila[6]) || 0;
            });
            
            // Actualizar los totales en el tfoot
            const tfoot = document.querySelector('#reportTable tfoot');
            if (tfoot) {
                const celdas = tfoot.querySelectorAll('td');
                if (celdas.length >= 7) {
                    celdas[1].innerHTML = `<strong class="text-info">${totales.preescolar}</strong>`;
                    celdas[2].innerHTML = `<strong class="text-primary">${totales.primaria}</strong>`;
                    celdas[3].innerHTML = `<strong class="text-success">${totales.bachillerato}</strong>`;
                    celdas[4].innerHTML = `<strong class="text-warning">${totales.deportivas}</strong>`;
                    celdas[5].innerHTML = `<strong class="text-danger">${totales.especiales}</strong>`;
                    celdas[6].innerHTML = `<strong class="text-info">${totales.salidas}</strong>`;
                }
            }
        }
    </script>
@stop
