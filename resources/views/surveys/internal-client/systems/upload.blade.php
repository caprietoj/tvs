@extends('adminlte::page')

@section('title', 'Subir Encuesta de Sistemas')

@section('content_header')
    <h1>
        <i class="fas fa-upload"></i>
        Subir Resultados de Encuesta - Sistemas
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-excel"></i>
                        Carga de Archivo Excel - Encuesta Cliente Interno Sistemas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Formato esperado:</strong> El archivo debe contener las siguientes columnas en orden:
                        <ul class="mt-2">
                            <li><strong>Columna A:</strong> Marca temporal</li>
                            <li><strong>Columna B:</strong> Dependencia</li>
                            <li><strong>Columna C:</strong> Tiempos de respuesta</li>
                            <li><strong>Columna D:</strong> Efectividad técnica</li>
                            <li><strong>Columna E:</strong> Profesionalismo</li>
                            <li><strong>Columna F:</strong> Comentarios personal</li>
                            <li><strong>Columna G:</strong> Estado de equipos</li>
                            <li><strong>Columna H:</strong> Comentarios equipos</li>
                            <li><strong>Columna I:</strong> Apoyo usabilidad</li>
                            <li><strong>Columna J:</strong> Plataformas interacción</li>
                            <li><strong>Columna K:</strong> Otra plataforma</li>
                            <li><strong>Columna L:</strong> Calidad internet</li>
                            <li><strong>Columna M:</strong> Problemas conectividad</li>
                            <li><strong>Columna N:</strong> Intervención eventos</li>
                            <li><strong>Columna O:</strong> Comentarios eventos</li>
                            <li><strong>Columna P:</strong> Aspectos destacados</li>
                            <li><strong>Columna Q:</strong> Oportunidades de mejora</li>
                        </ul>
                    </div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="survey_file">
                                        <i class="fas fa-file-excel"></i>
                                        Archivo de Encuesta
                                    </label>
                                    <input type="file" class="form-control-file" id="survey_file" name="survey_file" 
                                           accept=".xlsx,.xls,.csv" required>
                                    <small class="form-text text-muted">
                                        Formatos permitidos: .xlsx, .xls, .csv
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="survey_year">
                                        <i class="fas fa-calendar-alt"></i>
                                        Año de la Encuesta
                                    </label>
                                    <select class="form-control" id="survey_year" name="survey_year" required>
                                        <option value="">Seleccione el año</option>
                                        @for($year = 2020; $year <= date('Y') + 1; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="survey_month">
                                        <i class="fas fa-calendar"></i>
                                        Mes de la Encuesta
                                    </label>
                                    <select class="form-control" id="survey_month" name="survey_month" required>
                                        <option value="">Seleccione el mes</option>
                                        <option value="1">Enero</option>
                                        <option value="2">Febrero</option>
                                        <option value="3">Marzo</option>
                                        <option value="4">Abril</option>
                                        <option value="5">Mayo</option>
                                        <option value="6" selected>Junio</option>
                                        <option value="7">Julio</option>
                                        <option value="8">Agosto</option>
                                        <option value="9">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload"></i>
                                    Subir y Procesar Encuesta
                                </button>
                                <a href="{{ route('surveys.internal-client.systems') }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Resultados -->
    <div class="row" id="resultsSection" style="display: none;">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Análisis Estadístico de la Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    <div id="analysisContent">
                        <!-- Aquí se cargará el análisis -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Carga -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <h5>Procesando archivo...</h5>
                <p>Por favor espere mientras se procesa el archivo de encuesta</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .form-control-file {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .form-control-file:hover {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
    }
    .alert {
        border-radius: 10px;
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        // Mostrar modal de carga
        $('#loadingModal').modal('show');
        
        $.ajax({
            url: '{{ route("surveys.internal-client.systems.process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    // Mostrar resultados
                    displayAnalysis(response.analysis);
                    $('#resultsSection').show();
                    
                    // Scroll hacia los resultados
                    $('html, body').animate({
                        scrollTop: $('#resultsSection').offset().top
                    }, 1000);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Archivo procesado exitosamente',
                        text: 'Se han procesado ' + response.data.length + ' respuestas',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al procesar archivo',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                $('#loadingModal').modal('hide');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar el archivo: ' + error
                });
            }
        });
    });
});

function displayAnalysis(analysis) {
    let html = `
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> ${analysis.total_respuestas}</h3>
                    <p>Total de Respuestas</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><i class="fas fa-percentage"></i> ${analysis.estadisticas.satisfaccion_general.porcentaje}%</h3>
                    <p>Satisfacción General</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><i class="fas fa-star"></i> ${analysis.estadisticas.satisfaccion_general.promedio}/5</h3>
                    <p>Promedio General</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><i class="fas fa-building"></i> ${Object.keys(analysis.dependencias).length}</h3>
                    <p>Dependencias</p>
                </div>
            </div>
        </div>
    `;
    
    // Gráfico de satisfacción por categoría
    html += `
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-bar"></i> Satisfacción por Categoría</h5>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie"></i> Distribución por Dependencia</h5>
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>
    `;
    
    // Tabla de estadísticas detalladas
    html += `
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="fas fa-table"></i> Estadísticas Detalladas</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Promedio</th>
                                <th>Porcentaje</th>
                                <th>Total Respuestas</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    Object.keys(analysis.estadisticas).forEach(function(key) {
        if (key !== 'satisfaccion_general') {
            const stat = analysis.estadisticas[key];
            html += `
                <tr>
                    <td>${stat.label}</td>
                    <td>${stat.promedio}/5</td>
                    <td>${stat.porcentaje}%</td>
                    <td>${stat.total_respuestas}</td>
                </tr>
            `;
        }
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    // Aspectos destacados y oportunidades de mejora
    html += `
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-star"></i> Aspectos Más Destacados</h5>
                    <ul class="list-group">
    `;
    
    Object.keys(analysis.aspectos_destacados_frecuentes).slice(0, 5).forEach(function(aspecto) {
        html += `<li class="list-group-item d-flex justify-content-between">
                    <span>${aspecto}</span>
                    <span class="badge badge-success">${analysis.aspectos_destacados_frecuentes[aspecto]}</span>
                </li>`;
    });
    
    html += `
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-tools"></i> Oportunidades de Mejora</h5>
                    <ul class="list-group">
    `;
    
    Object.keys(analysis.oportunidades_mejora_frecuentes).slice(0, 5).forEach(function(oportunidad) {
        html += `<li class="list-group-item d-flex justify-content-between">
                    <span>${oportunidad}</span>
                    <span class="badge badge-warning">${analysis.oportunidades_mejora_frecuentes[oportunidad]}</span>
                </li>`;
    });
    
    html += `
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    // Plataformas más utilizadas
    html += `
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="fas fa-desktop"></i> Plataformas Más Utilizadas</h5>
                    <canvas id="platformChart"></canvas>
                </div>
            </div>
        </div>
    `;
    
    $('#analysisContent').html(html);
    
    // Crear gráficos
    createCharts(analysis);
}

function createCharts(analysis) {
    // Gráfico de satisfacción por categoría
    const categoryData = [];
    const categoryLabels = [];
    
    Object.keys(analysis.estadisticas).forEach(function(key) {
        if (key !== 'satisfaccion_general') {
            const stat = analysis.estadisticas[key];
            categoryLabels.push(stat.label);
            categoryData.push(stat.porcentaje);
        }
    });
    
    const ctx1 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Satisfacción (%)',
                data: categoryData,
                backgroundColor: [
                    '#4CAF50', '#2196F3', '#FF9800', '#9C27B0', 
                    '#F44336', '#00BCD4', '#795548'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Gráfico de departamentos
    const deptLabels = Object.keys(analysis.dependencias);
    const deptData = Object.values(analysis.dependencias);
    
    const ctx2 = document.getElementById('departmentChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: deptLabels,
            datasets: [{
                data: deptData,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
    
    // Gráfico de plataformas
    const platformLabels = Object.keys(analysis.plataformas_mas_usadas).slice(0, 5);
    const platformData = Object.values(analysis.plataformas_mas_usadas).slice(0, 5);
    
    const ctx3 = document.getElementById('platformChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: platformLabels,
            datasets: [{
                label: 'Uso frecuente',
                data: platformData,
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y'
        }
    });
}
</script>
@stop
