@extends('adminlte::page')

@section('title', 'Dashboard Biométrico Portería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard Biométrico Portería - {{ $mesTexto }}</h1>
        <div class="form-group mb-0">
            <select id="mes-selector" class="form-control" onchange="cambiarMes(this.value)">
                <option value="actual" {{ $mes == 'actual' ? 'selected' : '' }}>Mes Actual</option>
                <option value="Enero" {{ $mes == 'Enero' ? 'selected' : '' }}>Enero</option>
                <option value="Febrero" {{ $mes == 'Febrero' ? 'selected' : '' }}>Febrero</option>
                <option value="Marzo" {{ $mes == 'Marzo' ? 'selected' : '' }}>Marzo</option>
                <option value="Abril" {{ $mes == 'Abril' ? 'selected' : '' }}>Abril</option>
                <option value="Mayo" {{ $mes == 'Mayo' ? 'selected' : '' }}>Mayo</option>
                <option value="Junio" {{ $mes == 'Junio' ? 'selected' : '' }}>Junio</option>
                <option value="Julio" {{ $mes == 'Julio' ? 'selected' : '' }}>Julio</option>
                <option value="Agosto" {{ $mes == 'Agosto' ? 'selected' : '' }}>Agosto</option>
                <option value="Septiembre" {{ $mes == 'Septiembre' ? 'selected' : '' }}>Septiembre</option>
                <option value="Octubre" {{ $mes == 'Octubre' ? 'selected' : '' }}>Octubre</option>
                <option value="Noviembre" {{ $mes == 'Noviembre' ? 'selected' : '' }}>Noviembre</option>
                <option value="Diciembre" {{ $mes == 'Diciembre' ? 'selected' : '' }}>Diciembre</option>
            </select>
        </div>
    </div>
@stop

@section('content')
    @if(session('info'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('info') }}
        </div>
    @endif

    @if(isset($estadisticas['error']))
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Advertencia</h5>
            {{ $estadisticas['error'] }}. Asegúrate de que la tabla de registros de portería esté configurada correctamente.
        </div>
    @endif

    <!-- Tarjetas de estadísticas principales -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_entradas']) }}</h3>
                    <p>Total Entradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_salidas']) }}</h3>
                    <p>Total Salidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['empleados_registrados']) }}</h3>
                    <p>Empleados Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['estudiantes_registrados']) }}</h3>
                    <p>Estudiantes Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas por tipo de persona -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['visitantes_registrados']) }}</h3>
                    <p>Visitantes Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_registros']) }}</h3>
                    <p>Total Registros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <!-- Espacio vacío para mantener el layout -->
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fas fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Promedio Diario</span>
                    <span class="info-box-number">{{ $estadisticas['promedio_diario'] }} registros/día</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Período</span>
                    <span class="info-box-number">{{ $estadisticas['periodo'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Distribución por Tipo</span>
                    <span class="info-box-number">
                        Emp: {{ $estadisticas['empleados_registrados'] }} | 
                        Est: {{ $estadisticas['estudiantes_registrados'] }} | 
                        Vis: {{ $estadisticas['visitantes_registrados'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis Comparativo -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Análisis Comparativo con Período Anterior
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $analisisComparativo['variaciones']['total_registros'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-{{ $analisisComparativo['variaciones']['total_registros'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cambio en Registros</span>
                                    <span class="info-box-number">{{ $analisisComparativo['variaciones']['total_registros'] > 0 ? '+' : '' }}{{ number_format($analisisComparativo['variaciones']['total_registros'], 1) }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $analisisComparativo['variaciones']['total_registros'] >= 0 ? 'success' : 'danger' }}" 
                                             style="width: {{ min(abs($analisisComparativo['variaciones']['total_registros']), 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Compara total de registros actuales vs período anterior<br>
                                        Ej: Si octubre tuvo 100 registros y septiembre 80 → +25% ↗️
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $analisisComparativo['variaciones']['personas_unicas'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-{{ $analisisComparativo['variaciones']['personas_unicas'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cambio en Personas Únicas</span>
                                    <span class="info-box-number">{{ $analisisComparativo['variaciones']['personas_unicas'] > 0 ? '+' : '' }}{{ number_format($analisisComparativo['variaciones']['personas_unicas'], 1) }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $analisisComparativo['variaciones']['personas_unicas'] >= 0 ? 'success' : 'danger' }}" 
                                             style="width: {{ min(abs($analisisComparativo['variaciones']['personas_unicas']), 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Compara personas diferentes actuales vs período anterior<br>
                                        Ej: Si octubre tuvo 50 personas únicas y septiembre 45 → +11.1% ↗️
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $analisisComparativo['variaciones']['promedio_diario'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-{{ $analisisComparativo['variaciones']['promedio_diario'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cambio Promedio Diario</span>
                                    <span class="info-box-number">{{ $analisisComparativo['variaciones']['promedio_diario'] > 0 ? '+' : '' }}{{ number_format($analisisComparativo['variaciones']['promedio_diario'], 1) }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $analisisComparativo['variaciones']['promedio_diario'] >= 0 ? 'success' : 'danger' }}" 
                                             style="width: {{ min(abs($analisisComparativo['variaciones']['promedio_diario']), 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Compara promedio diario actual vs período anterior<br>
                                        Ej: Si octubre promedia 5/día y septiembre 4/día → +25% ↗️
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Visitantes Frecuentes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        Top 10 Visitantes Más Frecuentes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th>Total Registros</th>
                                    <th>Tiempo Prom.</th>
                                    <th>Frecuencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patronesComportamiento['frecuencia_visitas'] as $index => $persona)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $persona->nombre }}</td>
                                        <td>{{ $persona->documento }}</td>
                                        <td>
                                            <span class="badge badge-{{ $persona->tipo_persona == 'Empleado' ? 'primary' : 'warning' }}">
                                                {{ $persona->tipo_persona }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $persona->total_visitas }}</span>
                                        </td>
                                        <td>
                                            @if($persona->tiempo_promedio)
                                                {{ number_format($persona->tiempo_promedio / 60, 1) }}h
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-primary" 
                                                     style="width: {{ ($persona->total_visitas / $patronesComportamiento['frecuencia_visitas']->first()->total_visitas) * 100 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de exportación -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ route('porteria.dashboard.export', ['mes' => $mes]) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel mr-1"></i>
                        Exportar a Excel
                    </a>
                    <a href="{{ route('porteria.dashboard.export-html', ['mes' => $mes]) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-file-code mr-1"></i>
                        Exportar a HTML
                    </a>
                    <p class="text-muted mt-2">
                        <i class="fas fa-info-circle"></i>
                        Descarga un archivo Excel (.xlsx) con todos los datos, filtros automáticos y columnas calculadas
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de registros -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Registros de Portería
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">
                            Página {{ $registros->currentPage() }} de {{ $registros->lastPage() }} 
                            ({{ $registros->total() }} registros totales)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" 
                                       id="searchInput"
                                       class="form-control" 
                                       placeholder="Buscar por documento o nombre completo..." 
                                       value="{{ $search ?? '' }}"
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> La búsqueda se realiza automáticamente mientras escribes
                            </small>
                        </div>
                        @if(isset($search) && $search != '')
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0 py-2">
                                    <i class="fas fa-filter"></i>
                                    Filtrando por: <strong>"{{ $search }}"</strong>
                                    <button type="button" class="close ml-2" onclick="clearSearch()">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Indicador de carga -->
                    <div id="loadingIndicator" style="display: none;" class="text-center mb-3">
                        <i class="fas fa-spinner fa-spin"></i> Buscando...
                    </div>
                    
                    <div id="tablaRegistros">
                    @if(isset($registros) && count($registros) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="registrosTable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Documento</th>
                                        <th>Nombre Completo</th>
                                        <th>Tipo</th>
                                        <th>Hora Entrada</th>
                                        <th>Hora Salida</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registros as $registro)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                            <td>{{ $registro->documento }}</td>
                                            <td>{{ $registro->nombre }}</td>
                                            <td>
                                                <span class="badge badge-{{ $registro->tipo_persona == 'Empleado' ? 'primary' : ($registro->tipo_persona == 'Visitante' ? 'warning' : 'info') }}">
                                                    {{ $registro->tipo_persona ?? 'No especificado' }}
                                                </span>
                                            </td>
                                            <td>{{ $registro->hora_entrada ? \Carbon\Carbon::parse($registro->hora_entrada)->format('H:i:s') : '-' }}</td>
                                            <td>{{ $registro->hora_salida ? \Carbon\Carbon::parse($registro->hora_salida)->format('H:i:s') : '-' }}</td>
                                            <td>
                                                @if($registro->hora_salida)
                                                    <span class="badge badge-success"><i class="fas fa-check"></i> Completado</span>
                                                @else
                                                    <span class="badge badge-danger"><i class="fas fa-clock"></i> Dentro</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación de Laravel -->
                        @if($registros->hasPages())
                            <div class="d-flex justify-content-center mt-3" id="paginationContainer">
                                {{ $registros->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay registros para mostrar{{ isset($search) && $search != '' ? ' con los criterios de búsqueda' : ' en el período seleccionado' }}.
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
function cambiarMes(mes) {
    const searchValue = document.getElementById('searchInput').value;
    let url = '{{ route("porteria.dashboard") }}?mes=' + mes;
    if (searchValue) {
        url += '&search=' + encodeURIComponent(searchValue);
    }
    window.location.href = url;
}

// Búsqueda en tiempo real
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const loadingIndicator = document.getElementById('loadingIndicator');
const tablaRegistros = document.getElementById('tablaRegistros');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = this.value.trim();
        
        // Mostrar indicador de carga
        loadingIndicator.style.display = 'block';
        
        // Esperar 500ms después de que el usuario deja de escribir
        searchTimeout = setTimeout(function() {
            realizarBusqueda(searchValue);
        }, 500);
    });
}

function realizarBusqueda(searchValue) {
    const mes = '{{ $mes }}';
    const url = '{{ route("porteria.dashboard") }}?mes=' + mes + '&search=' + encodeURIComponent(searchValue);
    
    // Realizar petición AJAX
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Crear un elemento temporal para parsear el HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extraer solo la tabla de registros
        const nuevoContenido = doc.getElementById('tablaRegistros');
        
        if (nuevoContenido) {
            tablaRegistros.innerHTML = nuevoContenido.innerHTML;
            
            // Reactivar los eventos de paginación
            activarPaginacion();
        }
        
        // Ocultar indicador de carga
        loadingIndicator.style.display = 'none';
    })
    .catch(error => {
        console.error('Error en la búsqueda:', error);
        loadingIndicator.style.display = 'none';
    });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    realizarBusqueda('');
}

function activarPaginacion() {
    // Interceptar clicks en los enlaces de paginación
    const paginationLinks = document.querySelectorAll('#paginationContainer a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            const searchValue = document.getElementById('searchInput').value;
            
            loadingIndicator.style.display = 'block';
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nuevoContenido = doc.getElementById('tablaRegistros');
                
                if (nuevoContenido) {
                    tablaRegistros.innerHTML = nuevoContenido.innerHTML;
                    activarPaginacion();
                }
                
                loadingIndicator.style.display = 'none';
            })
            .catch(error => {
                console.error('Error en la paginación:', error);
                loadingIndicator.style.display = 'none';
            });
        });
    });
}

// Inicializar tabla simple sin DataTables (usando paginación de Laravel)
$(document).ready(function() {
    console.log('Tabla de registros cargada correctamente');
    
    // Activar paginación AJAX al cargar la página
    activarPaginacion();
});

// Datos para los gráficos analíticos
const patronesComportamiento = @json($patronesComportamiento['frecuencia_visitas']);

// Solo mantener la funcionalidad de la tabla
console.log('Dashboard de Portería cargado correctamente');
const dailyData = @json($datosDiarios);
const hourlyData = @json($datosHorarios);
const personTypeData = @json($datosTipoPersona);

// Gráfico de registros diarios
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => item.fecha),
        datasets: [{
            label: 'Total Registros',
            data: dailyData.map(item => item.total_registros),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }, {
            label: 'Personas Únicas',
            data: dailyData.map(item => item.personas_unicas),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de tipos de persona
const personTypeCtx = document.getElementById('personTypeChart').getContext('2d');
new Chart(personTypeCtx, {
    type: 'doughnut',
    data: {
        labels: personTypeData.map(item => item.tipo),
        datasets: [{
            data: personTypeData.map(item => item.total),
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico horario
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: hourlyData.map(item => item.hora),
        datasets: [{
            label: 'Entradas por Hora',
            data: hourlyData.map(item => item.entradas),
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    maxRotation: 45
                }
            }
        }
    }
});
</script>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
.small-box {
    border-radius: 10px;
}

.info-box {
    border-radius: 10px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}

.card {
    border-radius: 10px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}

.alert {
    border-radius: 10px;
}

#registrosTable {
    font-size: 0.9rem;
}

.table td {
    vertical-align: middle;
}
</style>
@stop