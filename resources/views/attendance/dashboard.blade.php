@extends('adminlte::page')

@section('title', 'Dashboard Biométrico')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard Biométrico - {{ $mes }}</h1>
        <div class="form-group mb-0">
            <select id="mes-selector" class="form-control" onchange="cambiarMes(this.value)">
                <option value="actual" {{ $mes == 'actual' ? 'selected' : '' }}>Seleccionar mes...</option>
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
@if($records->isEmpty())
    <div class="alert alert-warning">
        No hay registros disponibles para el mes de {{ $mes }}.
    </div>
@else
    <div class="row">
        <!-- Redistribución de los small-box: ahora son 2 en lugar de 4 -->
        <div class="col-lg-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalEmployees }}</h3>
                    <p>Personal Activo<br><small>Total de empleados registrados en el sistema</small></p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $averageAttendance }}%</h3>
                    <p>Tasa de Asistencia<br><small>Promedio mensual de asistencia puntual</small></p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Nueva fila para el indicador de marcaciones -->
    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="small-box bg-purple">
                <div class="inner">
                    @php
                        // Calcular días laborables (lunes a viernes) del mes
                        $year = date('Y');
                        $monthNum = date('m');
                        if ($mes == 'Enero') $monthNum = 1;
                        elseif ($mes == 'Febrero') $monthNum = 2;
                        elseif ($mes == 'Marzo') $monthNum = 3;
                        elseif ($mes == 'Abril') $monthNum = 4;
                        elseif ($mes == 'Mayo') $monthNum = 5;
                        elseif ($mes == 'Junio') $monthNum = 6;
                        elseif ($mes == 'Julio') $monthNum = 7;
                        elseif ($mes == 'Agosto') $monthNum = 8;
                        elseif ($mes == 'Septiembre') $monthNum = 9;
                        elseif ($mes == 'Octubre') $monthNum = 10;
                        elseif ($mes == 'Noviembre') $monthNum = 11;
                        elseif ($mes == 'Diciembre') $monthNum = 12;
                        
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                        $workingDays = 0;
                        
                        for ($i = 1; $i <= $daysInMonth; $i++) {
                            $dayOfWeek = date('N', strtotime("$year-$monthNum-$i"));
                            if ($dayOfWeek <= 5) { // 1 (lunes) a 5 (viernes)
                                $workingDays++;
                            }
                        }
                        
                        // Calcular marcaciones esperadas (2 por día por empleado)
                        $expectedMarkings = $workingDays * 2 * $totalEmployees;
                        
                        // Contar marcaciones registradas (entradas + salidas)
                        $registeredMarkings = $records->filter(function($record) {
                            return !empty($record->entrada);
                        })->count() + $records->filter(function($record) {
                            return !empty($record->salida);
                        })->count();
                        
                        // Calcular porcentaje de cumplimiento
                        $complianceRate = $expectedMarkings > 0 ? round(($registeredMarkings / $expectedMarkings) * 100, 1) : 0;
                    @endphp
                    <h3>{{ $registeredMarkings }} / {{ $expectedMarkings }} <small>({{ $complianceRate }}%)</small></h3>
                    <p>Marcaciones Registradas vs Esperadas<br>
                    <small>Basado en 2 marcaciones diarias (entrada y salida) por empleado en {{ $workingDays }} días laborables</small></p>
                </div>
                <div class="icon">
                    <i class="fas fa-fingerprint"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-12">
            <div class="small-box bg-teal">
                <div class="inner">
                    @php
                        // Calcular promedio de marcaciones por empleado
                        $avgMarkingsPerEmployee = $totalEmployees > 0 ? round($registeredMarkings / $totalEmployees, 1) : 0;
                        $expectedPerEmployee = $workingDays * 2;
                        $markingDeficit = $expectedPerEmployee - $avgMarkingsPerEmployee;
                    @endphp
                    <h3>{{ $avgMarkingsPerEmployee }} / {{ $expectedPerEmployee }}</h3>
                    <p>Promedio de Marcaciones por Empleado<br>
                    <small>Cada empleado debería registrar {{ $expectedPerEmployee }} marcaciones al mes ({{ $markingDeficit > 0 ? "déficit de $markingDeficit" : "cumplimiento completo" }})</small></p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Nueva sección: Estadísticas de Retrasos por Departamento -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Estadísticas de Retrasos por Departamento</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Total Retrasos</th>
                                    <th>7:00 - 7:10</th>
                                    <th>7:11 - 7:20</th>
                                    <th>Después de 7:20</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($retrasosPorDepartamento as $index => $dept)
                                    @if(isset($dept['Departamento']) && $dept['Departamento'] != 'Total')
                                        <tr>
                                            <td>{{ $dept['Departamento'] }}</td>
                                            <td>{{ $dept['Retrasos'] }}</td>
                                            <td>{{ isset($retrasos700710[$index]) ? $retrasos700710[$index]['Retrasos'] : 0 }}</td>
                                            <td>{{ isset($retrasos710720[$index]) ? $retrasos710720[$index]['Retrasos'] : 0 }}</td>
                                            <td>{{ isset($retrasos720Plus[$index]) ? $retrasos720Plus[$index]['Retrasos'] : 0 }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                <!-- Fila de totales -->
                                @php
                                    $totalIndex = count($retrasosPorDepartamento) - 1;
                                    $total710Index = count($retrasos700710) - 1;
                                    $total720Index = count($retrasos710720) - 1;
                                    $totalAfter720Index = count($retrasos720Plus) - 1;
                                @endphp
                                <tr class="bg-light font-weight-bold">
                                    <td>Total</td>
                                    <td>{{ $totalIndex >= 0 && isset($retrasosPorDepartamento[$totalIndex]['Retrasos']) ? $retrasosPorDepartamento[$totalIndex]['Retrasos'] : 0 }}</td>
                                    <td>{{ $total710Index >= 0 && isset($retrasos700710[$total710Index]['Retrasos']) ? $retrasos700710[$total710Index]['Retrasos'] : 0 }}</td>
                                    <td>{{ $total720Index >= 0 && isset($retrasos710720[$total720Index]['Retrasos']) ? $retrasos710720[$total720Index]['Retrasos'] : 0 }}</td>
                                    <td>{{ $totalAfter720Index >= 0 && isset($retrasos720Plus[$totalAfter720Index]['Retrasos']) ? $retrasos720Plus[$totalAfter720Index]['Retrasos'] : 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Gráfico de barras: Retrasos por departamento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Retrasos por Departamento</h3>
                </div>
                <div class="card-body">
                    <canvas id="departmentLateChart" style="height: 250px;"></canvas>
                </div>
                <div class="card-footer bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="legend-container d-flex flex-wrap">
                                @foreach($retrasosPorDepartamento as $index => $dept)
                                    @if(isset($dept['Departamento']) && $dept['Departamento'] != 'Total')
                                        <div class="legend-item mr-3 mb-2">
                                            <span class="legend-color" id="legend-color-{{ $index }}" style="display:inline-block; width:15px; height:15px; margin-right:5px;"></span>
                                            <span class="legend-text">{{ $dept['Departamento'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de barras: Distribución de retrasos por rango horario -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribución de Retrasos por Rango Horario</h3>
                </div>
                <div class="card-body">
                    <canvas id="timeRangeChart" style="height: 250px;"></canvas>
                </div>
                <div class="card-footer bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="legend-container d-flex flex-wrap">
                                <div class="legend-item mr-3 mb-2">
                                    <span class="legend-color" style="display:inline-block; width:15px; height:15px; margin-right:5px; background-color: rgba(255, 206, 86, 0.7);"></span>
                                    <span class="legend-text">7:00 - 7:10</span>
                                </div>
                                <div class="legend-item mr-3 mb-2">
                                    <span class="legend-color" style="display:inline-block; width:15px; height:15px; margin-right:5px; background-color: rgba(255, 159, 64, 0.7);"></span>
                                    <span class="legend-text">7:11 - 7:20</span>
                                </div>
                                <div class="legend-item mr-3 mb-2">
                                    <span class="legend-color" style="display:inline-block; width:15px; height:15px; margin-right:5px; background-color: rgba(255, 99, 132, 0.7);"></span>
                                    <span class="legend-text">Después de 7:20</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico circular: Distribución por departamento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribución de Empleados</h3>
                </div>
                <div class="card-body">
                    <canvas id="employeeDistributionChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de líneas: Tendencia mensual -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tendencia Mensual de Asistencia</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registros de Asistencia</h3>
        </div>
        <div class="card-body">
            <table id="attendance-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record->no_id }}</td>
                        <td>{{ $record->nombre_apellidos }}</td>
                        <td>{{ $record->fecha ? Carbon\Carbon::parse($record->fecha)->format('d-m-Y') : 'N/A' }}</td>
                        <td>{{ $record->entrada ?: 'No registrado' }}</td>
                        <td>{{ $record->salida ?: 'No registrado' }}</td>
                        <td>{{ $record->departamento }}</td>
                        <td>
                            @if(empty($record->entrada))
                                <span class="badge badge-danger">Ausente</span>
                            @else
                                @php
                                    try {
                                        // Intentar varios formatos de hora
                                        $entrada = null;
                                        $horaStr = $record->entrada;
                                        
                                        $formatos = ['H:i:s', 'H:i', 'h:i:s A', 'h:i A'];
                                        
                                        foreach ($formatos as $formato) {
                                            try {
                                                $entrada = Carbon\Carbon::createFromFormat($formato, $horaStr);
                                                break;
                                            } catch (\Exception $e) {
                                                continue;
                                            }
                                        }
                                        
                                        if (!$entrada) {
                                            $entrada = Carbon\Carbon::parse($horaStr);
                                        }
                                        
                                        $limite = Carbon\Carbon::createFromFormat('H:i', '07:00');
                                    } catch (\Exception $e) {
                                        $entrada = null;
                                    }
                                @endphp
                                
                                @if(!$entrada)
                                    <span class="badge badge-danger">Ausente</span>
                                @elseif($entrada->gt($limite))
                                    <span class="badge badge-warning">Tarde</span>
                                @else
                                    <span class="badge badge-success">A tiempo</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    /* Reducir el tamaño de las tarjetas y gráficos */
    .card {
        margin-bottom: 15px;
    }
    .card-body {
        padding: 0.75rem;
    }
    .card-header {
        padding: 0.5rem 0.75rem;
    }
    /* Ajustar tamaño de fuente en tablas */
    .table {
        font-size: 0.9rem;
    }
    /* Reducir padding en celdas de tabla */
    .table td, .table th {
        padding: 0.5rem;
    }
    /* Estilo para la leyenda */
    .legend-container {
        font-size: 0.8rem;
    }
    .legend-item {
        display: flex;
        align-items: center;
    }
    .legend-color {
        border-radius: 3px;
    }
    .card-footer {
        padding: 0.5rem 0.75rem;
    }
    /* Colores personalizados */
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
    .bg-teal {
        background-color: #20c997 !important;
        color: white;
    }
</style>
@stop

@section('js')
<!-- Cargar jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Luego cargar DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- Finalmente Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function cambiarMes(mes) {
    if (mes) {
        window.location.href = '{{ url("attendance/dashboard") }}/' + mes;
    }
}

$(document).ready(function() {
    var table = $('#attendance-table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron coincidencias",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });

    // Agregar filtro de estado
    var statusFilter = '<select id="status-filter" class="form-control"><option value="">Todos</option><option value="A tiempo">A tiempo</option><option value="Tarde">Tarde</option><option value="Ausente">Ausente</option></select>';
    $('.dataTables_filter').prepend(statusFilter);
    
    $('#status-filter').on('change', function() {
        table.column(6).search($(this).val()).draw();
    });

    // Configuración de gráficos
    renderCharts();
});

function renderCharts() {
    // Preparar datos para los gráficos
    const deptLabels = [];
    const deptData = [];
    
    @foreach($retrasosPorDepartamento as $dept)
        @if(isset($dept['Departamento']) && $dept['Departamento'] !== 'Total')
            deptLabels.push("{{ $dept['Departamento'] }}");
            deptData.push({{ $dept['Retrasos'] }});
        @endif
    @endforeach
    
    // Colores personalizados para cada departamento
    const deptColors = [
        'rgba(255, 99, 132, 0.7)',   // Rojo - EMC
        'rgba(54, 162, 235, 0.7)',   // Azul - Docentes Bachillerato
        'rgba(255, 206, 86, 0.7)',   // Amarillo - Docentes Preescolar y Primaria
        'rgba(75, 192, 192, 0.7)',   // Verde azulado - Depto de Apoyo
        'rgba(153, 102, 255, 0.7)',  // Púrpura - Administración
        'rgba(255, 159, 64, 0.7)',   // Naranja - Mantenimiento
        'rgba(199, 199, 199, 0.7)'   // Gris - Servicios Generales
    ];
    
    const deptBorderColors = [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(199, 199, 199, 1)'
    ];
    
    // Actualizar los colores en la leyenda
    deptColors.forEach((color, index) => {
        const legendElement = document.getElementById(`legend-color-${index}`);
        if (legendElement) {
            legendElement.style.backgroundColor = color;
        }
    });
    
    const timeRangeLabels = ['7:00 - 7:10', '7:11 - 7:20', 'Después de 7:20'];
    
    @php
        $total710 = isset($retrasos700710) && count($retrasos700710) > 0 && isset($retrasos700710[count($retrasos700710)-1]['Retrasos']) ? $retrasos700710[count($retrasos700710)-1]['Retrasos'] : 0;
        $total720 = isset($retrasos710720) && count($retrasos710720) > 0 && isset($retrasos710720[count($retrasos710720)-1]['Retrasos']) ? $retrasos710720[count($retrasos710720)-1]['Retrasos'] : 0;
        $totalAfter720 = isset($retrasos720Plus) && count($retrasos720Plus) > 0 && isset($retrasos720Plus[count($retrasos720Plus)-1]['Retrasos']) ? $retrasos720Plus[count($retrasos720Plus)-1]['Retrasos'] : 0;
    @endphp
    
    const timeRangeData = [
        {{ $total710 }},
        {{ $total720 }},
        {{ $totalAfter720 }}
    ];

    // Gráfico de retrasos por departamento
    const departmentLateCtx = document.getElementById('departmentLateChart');
    if (departmentLateCtx) {
        new Chart(departmentLateCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Total de Retrasos',
                    data: deptData,
                    backgroundColor: deptColors,
                    borderColor: deptBorderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                return tooltipItems[0].label;
                            },
                            label: function(context) {
                                return 'Retrasos: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            },
                            display: false // Ocultar etiquetas del eje X para evitar solapamiento
                        }
                    }
                }
            }
        });
    }

    // Gráfico de distribución de retrasos por rango horario
    const timeRangeCtx = document.getElementById('timeRangeChart');
    if (timeRangeCtx) {
        new Chart(timeRangeCtx, {
            type: 'bar',
            data: {
                labels: timeRangeLabels,
                datasets: [{
                    label: 'Cantidad de Retrasos',
                    data: timeRangeData,
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de distribución de empleados
    const employeeDistributionCtx = document.getElementById('employeeDistributionChart');
    if (employeeDistributionCtx) {
        const deptNames = [];
        const totalData = [];
        
        @foreach($departmentStats as $dept => $stats)
            deptNames.push("{{ $dept }}");
            totalData.push({{ $stats['total'] }});
        @endforeach
        
        new Chart(employeeDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: deptNames,
                datasets: [{
                    data: totalData,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de tendencia mensual
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        const dayLabels = [];
        const onTimeData = [];
        const lateData = [];
        
        @foreach($weeklyTrends as $day => $stats)
            @php
                $dayName = '';
                switch($day) {
                    case '1': $dayName = 'Lunes'; break;
                    case '2': $dayName = 'Martes'; break;
                    case '3': $dayName = 'Miércoles'; break;
                    case '4': $dayName = 'Jueves'; break;
                    case '5': $dayName = 'Viernes'; break;
                    case '6': $dayName = 'Sábado'; break;
                    case '7': $dayName = 'Domingo'; break;
                }
            @endphp
            dayLabels.push("{{ $dayName }}");
            onTimeData.push({{ $stats['onTime'] }});
            lateData.push({{ $stats['late'] }});
        @endforeach
        
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'A Tiempo',
                    data: onTimeData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Tarde',
                    data: lateData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }
}
</script>
@stop