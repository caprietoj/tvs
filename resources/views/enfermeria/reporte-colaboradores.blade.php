@extends('adminlte::page')

@section('title', 'Reporte de Ingresos de Colaboradores')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Reporte de Ingresos de Colaboradores</h1>
        <a href="{{ route('enfermeria.ingreso_colaboradores.index') }}" class="btn btn-secondary">
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
                        <label for="filtro_tipo">
                            <i class="fas fa-user-tie"></i> Tipo de Colaborador
                        </label>
                        <select class="form-control" id="filtro_tipo" name="filtro_tipo">
                            <option value="">Todos</option>
                            <option value="profesores">Profesores</option>
                            <option value="administrativos">Administrativos</option>
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
                               placeholder="Ej: 3">
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
                <button type="button" class="btn btn-success btn-sm" onclick="exportTableToExcel('reportTable', 'Reporte_Ingresos_Colaboradores')">
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
                            <th class="text-center">PROFESORES</th>
                            <th class="text-center">ADMINISTRATIVOS</th>
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
                                    <span class="badge badge-primary badge-lg">{{ $dato->profesores }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-lg">{{ $dato->administrativos }}</span>
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
                                <td colspan="5" class="text-center text-muted">
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
                                    <strong class="text-primary">{{ $reporteData->sum('profesores') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-info">{{ $reporteData->sum('administrativos') }}</strong>
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
        .badge-lg {
            font-size: 16px;
            padding: 8px 15px;
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
    <!-- SheetJS -->
    <script src="https://cdn.sheetjs.com/xlsx-0.18.5/package/dist/xlsx.full.min.js"></script>
    
    <script>
        let tablaOriginal;
        
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

        function exportTableToExcel(tableID, filename = ''){
            try {
                // Verificar si hay filtros activos
                const hayFiltros = document.getElementById('filtro-activo-badge').style.display !== 'none';
                
                // Obtener solo las filas visibles (filtradas)
                const filasVisibles = tablaOriginal.rows({ search: 'applied' }).nodes();
                
                // Crear array para los datos
                const data = [];
                
                // Si hay filtros, agregar información de filtros al inicio
                if (hayFiltros) {
                    data.push(['REPORTE DE INGRESOS - COLABORADORES']);
                    data.push(['Filtros Aplicados:']);
                    
                    const fechaDesde = $('#fecha_desde').val();
                    const fechaHasta = $('#fecha_hasta').val();
                    const filtroTipo = $('#filtro_tipo').val();
                    const filtroCantidad = $('#filtro_cantidad').val();
                    
                    if (fechaDesde) {
                        data.push(['Fecha Desde:', fechaDesde]);
                    }
                    if (fechaHasta) {
                        data.push(['Fecha Hasta:', fechaHasta]);
                    }
                    if (filtroTipo) {
                        const tipoTexto = filtroTipo === 'profesores' ? 'Profesores' : 'Administrativos';
                        data.push(['Tipo:', tipoTexto]);
                    }
                    if (filtroCantidad) {
                        data.push(['Cantidad Mínima:', filtroCantidad]);
                    }
                    
                    data.push([]); // Línea vacía
                }
                
                // Agregar encabezados
                data.push(['FECHA', 'PROFESORES', 'ADMINISTRATIVOS', 'OBSERVACIONES', 'NOVEDADES']);
                
                // Procesar cada fila visible usando jQuery para evitar HTML
                $(filasVisibles).each(function() {
                    const fila = [];
                    
                    // Extraer texto limpio de cada celda
                    $(this).find('td').each(function(index) {
                        let text = '';
                        
                        if (index === 1 || index === 2) {
                            // Para PROFESORES y ADMINISTRATIVOS, extraer el número del badge
                            const badge = $(this).find('.badge');
                            if (badge.length > 0) {
                                text = badge.text().trim();
                            } else {
                                text = $(this).text().trim();
                            }
                        } else if (index === 3 || index === 4) {
                            // Para OBSERVACIONES y NOVEDADES, limpiar el texto
                            text = $(this).text().trim();
                            // Normalizar espacios múltiples y saltos de línea
                            text = text.replace(/\s+/g, ' ').replace(/\n/g, ' ').trim();
                            // Verificar si es "Sin observaciones" o "Sin novedades"
                            if (text.toLowerCase().includes('sin observaciones') || 
                                text.toLowerCase().includes('sin novedades')) {
                                const matches = text.match(/Sin\s+(observaciones|novedades)/i);
                                if (matches) {
                                    text = 'Sin ' + matches[1].toLowerCase();
                                }
                            }
                        } else {
                            // Para FECHA, simplemente extraer el texto
                            text = $(this).text().trim();
                        }
                        
                        fila.push(text);
                    });
                    
                    data.push(fila);
                });
                
                // Agregar totales del tfoot
                const totalProfesores = $('#reportTable tfoot tr td:eq(1) strong').text().trim();
                const totalAdministrativos = $('#reportTable tfoot tr td:eq(2) strong').text().trim();
                
                data.push([]); // Línea vacía
                data.push(['TOTAL', totalProfesores, totalAdministrativos, '', '']);
                
                // Crear el libro de trabajo
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(data);
                
                // Configurar anchos de columna
                ws['!cols'] = [
                    { wch: 12 },  // FECHA
                    { wch: 14 },  // PROFESORES
                    { wch: 18 },  // ADMINISTRATIVOS
                    { wch: 40 },  // OBSERVACIONES
                    { wch: 40 }   // NOVEDADES
                ];
                
                // Aplicar estilos a los encabezados
                let headerRow = hayFiltros ? data.length - filasVisibles.length - 2 : 0;
                
                // Estilos para información de filtros
                if (hayFiltros) {
                    for (let i = 0; i < headerRow; i++) {
                        const cellAddress = XLSX.utils.encode_cell({ r: i, c: 0 });
                        if (!ws[cellAddress]) continue;
                        
                        ws[cellAddress].s = {
                            font: { bold: true, color: { rgb: "1F4E78" } },
                            fill: { fgColor: { rgb: "E7E6E6" } }
                        };
                    }
                }
                
                // Estilos para encabezados de columnas
                for (let col = 0; col < 5; col++) {
                    const cellAddress = XLSX.utils.encode_cell({ r: headerRow, c: col });
                    if (!ws[cellAddress]) continue;
                    
                    ws[cellAddress].s = {
                        font: { bold: true, color: { rgb: "FFFFFF" } },
                        fill: { fgColor: { rgb: "4472C4" } },
                        alignment: { horizontal: "center" }
                    };
                }
                
                // Centrar columnas numéricas
                for (let row = headerRow + 1; row < data.length - 2; row++) {
                    for (let col = 1; col <= 2; col++) {
                        const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
                        if (!ws[cellAddress]) continue;
                        
                        ws[cellAddress].s = {
                            alignment: { horizontal: "center" }
                        };
                    }
                }
                
                // Estilo para fila de totales
                const totalRow = data.length - 1;
                for (let col = 0; col < 5; col++) {
                    const cellAddress = XLSX.utils.encode_cell({ r: totalRow, c: col });
                    if (!ws[cellAddress]) continue;
                    
                    ws[cellAddress].s = {
                        font: { bold: true },
                        fill: { fgColor: { rgb: "D9E1F2" } },
                        alignment: { horizontal: col === 0 ? "left" : "center" }
                    };
                }
                
                // Agregar hoja al libro
                XLSX.utils.book_append_sheet(wb, ws, "Reporte Colaboradores");
                
                // Generar nombre de archivo
                let nombreArchivo = filename;
                if (hayFiltros) {
                    const fechaHoy = new Date().toISOString().split('T')[0];
                    nombreArchivo += '_' + fechaHoy;
                    
                    const filtroTipo = $('#filtro_tipo').val();
                    if (filtroTipo) {
                        nombreArchivo += '_' + filtroTipo;
                    }
                    
                    nombreArchivo += '_FILTRADO';
                }
                nombreArchivo += '.xlsx';
                
                // Descargar archivo
                XLSX.writeFile(wb, nombreArchivo);
                
                toastr.success('Excel exportado exitosamente');
                
            } catch (error) {
                console.error('Error al exportar:', error);
                toastr.error('Error al exportar a Excel: ' + error.message);
            }
        }
        
        // Funciones de filtrado
        function aplicarFiltros() {
            const fechaDesde = $('#fecha_desde').val();
            const fechaHasta = $('#fecha_hasta').val();
            const filtroTipo = $('#filtro_tipo').val();
            const filtroCantidad = parseInt($('#filtro_cantidad').val()) || 0;
            
            // Remover filtros anteriores
            $.fn.dataTable.ext.search.pop();
            
            // Agregar nuevo filtro personalizado
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                // data[0] = FECHA
                // data[1] = PROFESORES (badge)
                // data[2] = ADMINISTRATIVOS (badge)
                
                // Filtro de fecha
                if (fechaDesde || fechaHasta) {
                    const fechaFila = parseFecha(data[0]);
                    
                    if (fechaDesde) {
                        const desde = new Date(fechaDesde);
                        if (fechaFila < desde) return false;
                    }
                    
                    if (fechaHasta) {
                        const hasta = new Date(fechaHasta);
                        if (fechaFila > hasta) return false;
                    }
                }
                
                // Filtro de tipo (profesores o administrativos)
                if (filtroTipo) {
                    if (filtroTipo === 'profesores') {
                        const cantidadProf = parseInt(data[1]) || 0;
                        if (cantidadProf === 0) return false;
                    } else if (filtroTipo === 'administrativos') {
                        const cantidadAdmin = parseInt(data[2]) || 0;
                        if (cantidadAdmin === 0) return false;
                    }
                }
                
                // Filtro de cantidad mínima
                if (filtroCantidad > 0) {
                    const totalProf = parseInt(data[1]) || 0;
                    const totalAdmin = parseInt(data[2]) || 0;
                    const totalFila = totalProf + totalAdmin;
                    
                    if (totalFila < filtroCantidad) return false;
                }
                
                return true;
            });
            
            // Redibujar tabla
            tablaOriginal.draw();
            
            // Recalcular totales
            recalcularTotales();
            
            // Mostrar badge de filtro activo
            const hayFiltros = fechaDesde || fechaHasta || filtroTipo || filtroCantidad > 0;
            $('#filtro-activo-badge').toggle(hayFiltros);
            
            if (hayFiltros) {
                toastr.success('Filtros aplicados correctamente');
            }
        }
        
        function limpiarFiltros() {
            // Limpiar campos del formulario
            $('#fecha_desde').val('');
            $('#fecha_hasta').val('');
            $('#filtro_tipo').val('');
            $('#filtro_cantidad').val('');
            
            // Remover filtros de DataTable
            $.fn.dataTable.ext.search.pop();
            tablaOriginal.draw();
            
            // Recalcular totales
            recalcularTotales();
            
            // Ocultar badge
            $('#filtro-activo-badge').hide();
            
            toastr.info('Filtros limpiados');
        }
        
        function aplicarFiltroRapido(tipo) {
            const hoy = new Date();
            let desde, hasta;
            
            if (tipo === 'hoy') {
                desde = hasta = formatearFechaInput(hoy);
            } else if (tipo === 'semana') {
                // Inicio de la semana (lunes)
                const primerDia = new Date(hoy);
                primerDia.setDate(hoy.getDate() - hoy.getDay() + 1);
                desde = formatearFechaInput(primerDia);
                hasta = formatearFechaInput(hoy);
            } else if (tipo === 'mes') {
                // Primer día del mes
                desde = formatearFechaInput(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
                hasta = formatearFechaInput(hoy);
            }
            
            $('#fecha_desde').val(desde);
            $('#fecha_hasta').val(hasta);
            
            aplicarFiltros();
        }
        
        function recalcularTotales() {
            const filas = tablaOriginal.rows({ search: 'applied' }).nodes();
            
            let totalProfesores = 0;
            let totalAdministrativos = 0;
            
            $(filas).each(function() {
                const profesores = parseInt($(this).find('td:eq(1) .badge').text()) || 0;
                const administrativos = parseInt($(this).find('td:eq(2) .badge').text()) || 0;
                
                totalProfesores += profesores;
                totalAdministrativos += administrativos;
            });
            
            // Actualizar tfoot
            $('#reportTable tfoot tr td:eq(1) strong').text(totalProfesores);
            $('#reportTable tfoot tr td:eq(2) strong').text(totalAdministrativos);
        }
        
        function parseFecha(fechaStr) {
            // Formato esperado: "YYYY-MM-DD" o "DD/MM/YYYY"
            const partes = fechaStr.includes('-') ? fechaStr.split('-') : fechaStr.split('/');
            
            if (partes.length === 3) {
                if (fechaStr.includes('-')) {
                    // Formato YYYY-MM-DD
                    return new Date(partes[0], partes[1] - 1, partes[2]);
                } else {
                    // Formato DD/MM/YYYY
                    return new Date(partes[2], partes[1] - 1, partes[0]);
                }
            }
            
            return new Date(fechaStr);
        }
        
        function formatearFechaInput(fecha) {
            const year = fecha.getFullYear();
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    </script>
@stop
