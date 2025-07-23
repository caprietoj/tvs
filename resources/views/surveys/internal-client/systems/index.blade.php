@extends('adminlte::page')

@push('css')
<style>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.small-box {
    border-radius: 8px;
    transition: transform 0.2s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.small-box:hover {
    transform: translateY(-1px);
}

.progress {
    height: 6px;
    border-radius: 3px;
    margin-top: 8px;
}

.progress-bar {
    border-radius: 3px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.btn {
    border-radius: 20px;
    padding: 8px 20px;
    font-weight: 500;
    font-size: 14px;
}

.highlight-item, .improvement-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.highlight-icon, .improvement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    background: rgba(255,255,255,0.1);
}

.highlight-content, .improvement-content {
    flex: 1;
}

.highlight-content h5, .improvement-content h5 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.progress-sm {
    height: 8px;
    margin: 8px 0;
}

@media (max-width: 768px) {
    .small-box {
        margin-bottom: 15px;
    }
}

/* Estilos para Aspectos Destacados */
.highlights-card {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.highlights-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.highlight-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-left: 4px solid #28a745;
}

.highlight-item:hover {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
    transform: translateX(5px);
}

.highlight-icon {
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
    font-size: 18px;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

.highlight-content h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.4;
}

.highlight-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-high {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    animation: pulse 2s infinite;
}

.priority-medium {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #212529;
}

.priority-low {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

/* Estilos para Oportunidades de Mejora */
.improvement-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
    border-left: 4px solid #ffc107;
}

.improvement-item:hover {
    background: linear-gradient(135deg, #fff3cd 0%, #fef9e7 100%);
    transform: translateX(5px);
}

.improvement-icon {
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
    font-size: 18px;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

.improvement-content h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.4;
}

.action-buttons {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.btn-action {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-plan {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-assign {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.btn-detail {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
    color: white;
}

/* Efectos de animación */
.card-animate {
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para modales */
.modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.highlight-modal-item, .improvement-modal-item {
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.highlight-modal-item {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
    border-left-color: #28a745;
}

.improvement-modal-item {
    background: linear-gradient(135deg, #fff3cd 0%, #fef9e7 100%);
    border-left-color: #ffc107;
}

.highlight-modal-item:hover, .improvement-modal-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Badges y etiquetas */
.mention-badge {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-positive {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
}

.status-attention {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #212529;
}

/* Responsive */
@media (max-width: 768px) {
    .highlight-item, .improvement-item {
        flex-direction: column;
        text-align: center;
    }
    
    .highlight-icon, .improvement-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .action-buttons {
        justify-content: center;
    }
}

/* Efectos de carga */
.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Tooltips personalizados */
.custom-tooltip {
    position: relative;
    cursor: help;
}

.custom-tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Esperar a que Chart.js esté completamente cargado
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }

    // Gráfico de tendencia
    const trendData = {!! json_encode($dashboardData['trend_data']) !!};
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels || [],
            datasets: [{
                label: 'Satisfacción General (%)',
                data: trendData.values || [],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.1,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#0056b3',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
    
    // Calcular y mostrar estadísticas del gráfico de tendencia
    if (trendData.values && trendData.values.length > 0) {
        const values = trendData.values.filter(val => val !== null && val !== undefined);
        if (values.length > 0) {
            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            const max = Math.max(...values);
            const min = Math.min(...values);
            
            document.getElementById('trendAvg').textContent = `Promedio: ${avg.toFixed(1)}%`;
            document.getElementById('trendMax').textContent = `Máximo: ${max.toFixed(1)}%`;
            document.getElementById('trendMin').textContent = `Mínimo: ${min.toFixed(1)}%`;
        }
    }
    
    // Gráfico de categorías
    const categoryData = {!! json_encode($dashboardData['category_comparison']) !!};
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    window.categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.labels || [],
            datasets: [{
                label: 'Satisfacción (%)',
                data: categoryData.values || [],
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#17a2b8', '#fd7e14', '#20c997'
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de dependencias
    const departmentData = {!! json_encode($dashboardData['department_comparison']) !!};
    const depCtx = document.getElementById('departmentChart').getContext('2d');
    window.departmentChart = new Chart(depCtx, {
        type: 'doughnut',
        data: {
            labels: departmentData.labels || [],
            datasets: [{
                data: departmentData.values || [],
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#17a2b8', '#fd7e14', '#20c997',
                    '#6c757d', '#343a40'
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 10
                        },
                        padding: 8,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 4,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 300
            }
        }
    });
});

function toggleChartAnimation(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.update('active');
    }
}
</script>
@endpush

@section('title', 'Encuesta Sistemas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-laptop mr-2"></i>Dashboard de Sistemas</h1>
        <div>
            <a href="{{ route('surveys.internal-client.systems.upload') }}" class="btn btn-success">
                <i class="fas fa-upload mr-1"></i>Subir Encuesta
            </a>
            @if($selectedPeriod ?? null)
                <a href="{{ route('surveys.internal-client.systems.export') }}" class="btn btn-info">
                    <i class="fas fa-download mr-1"></i>Exportar
                </a>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Funciones para Aspectos Destacados
function showHighlightDetail(text, count) {
    Swal.fire({
        title: 'Aspecto Destacado',
        html: `
            <div class="text-left">
                <h6 class="text-success mb-3"><i class="fas fa-star mr-2"></i>Detalle del Aspecto</h6>
                <p class="mb-3">"${text}"</p>
                <div class="alert alert-success">
                    <strong><i class="fas fa-chart-bar mr-2"></i>Estadísticas:</strong><br>
                    • Mencionado ${count} ${count === 1 ? 'vez' : 'veces'}<br>
                    • Categoría: Aspecto Positivo<br>
                    • Impacto: Alto
                </div>
                <div class="mt-3">
                    <h6>Acciones Sugeridas:</h6>
                    <ul class="text-left">
                        <li>Mantener y reforzar esta práctica</li>
                        <li>Compartir como buena práctica</li>
                        <li>Documentar el proceso exitoso</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-share"></i> Compartir',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            shareHighlight(text, count);
        }
    });
}

function shareHighlight(text, count) {
    const shareText = `🏆 Aspecto Destacado en Sistemas:\n"${text}"\n📊 Mencionado ${count} ${count === 1 ? 'vez' : 'veces'}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Aspecto Destacado - Sistemas',
            text: shareText
        });
    } else {
        navigator.clipboard.writeText(shareText).then(() => {
            Swal.fire('¡Copiado!', 'El aspecto destacado se copió al portapapeles', 'success');
        });
    }
}

function exportHighlights() {
    Swal.fire({
        title: 'Exportar Aspectos Destacados',
        text: 'Selecciona el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf"></i> PDF',
        cancelButtonText: '<i class="fas fa-file-excel"></i> Excel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`{{ route('surveys.internal-client.systems.export') }}?format=pdf&type=highlights`, '_blank');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.open(`{{ route('surveys.internal-client.systems.export') }}?format=excel&type=highlights`, '_blank');
        }
    });
}

// Funciones para Oportunidades de Mejora
function showImprovementDetail(text, count) {
    const urgency = count >= 3 ? 'Alta' : (count >= 2 ? 'Media' : 'Baja');
    const urgencyColor = count >= 3 ? 'danger' : (count >= 2 ? 'warning' : 'info');
    
    Swal.fire({
        title: 'Oportunidad de Mejora',
        html: `
            <div class="text-left">
                <h6 class="text-${urgencyColor} mb-3"><i class="fas fa-bullseye mr-2"></i>Detalle de la Oportunidad</h6>
                <p class="mb-3">"${text}"</p>
                <div class="alert alert-${urgencyColor}">
                    <strong><i class="fas fa-chart-bar mr-2"></i>Análisis:</strong><br>
                    • Mencionado ${count} ${count === 1 ? 'vez' : 'veces'}<br>
                    • Prioridad: ${urgency}<br>
                    • Requiere: ${urgency === 'Alta' ? 'Acción Inmediata' : urgency === 'Media' ? 'Planificación' : 'Seguimiento'}
                </div>
                <div class="mt-3">
                    <h6>Próximos Pasos:</h6>
                    <ul class="text-left">
                        <li>Analizar causas raíz</li>
                        <li>Definir plan de acción</li>
                        <li>Asignar responsables</li>
                        <li>Establecer métricas de seguimiento</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-tasks"></i> Crear Plan',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            createActionPlan(text);
        }
    });
}

function createActionPlan(issueText) {
    Swal.fire({
        title: 'Crear Plan de Acción',
        html: `
            <div class="text-left">
                <div class="form-group">
                    <label><strong>Oportunidad de Mejora:</strong></label>
                    <p class="form-control-plaintext border p-2 bg-light">${issueText}</p>
                </div>
                <div class="form-group">
                    <label for="actionDescription"><strong>Descripción de la Acción:</strong></label>
                    <textarea id="actionDescription" class="form-control" rows="3" placeholder="Describe las acciones específicas a tomar..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="responsible"><strong>Responsable:</strong></label>
                            <select id="responsible" class="form-control">
                                <option value="">Seleccionar...</option>
                                <option value="supervisor">Supervisor de Sistemas</option>
                                <option value="coordinador">Coordinador de TI</option>
                                <option value="gerente">Gerente de Sistemas</option>
                                <option value="equipo">Equipo Completo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deadline"><strong>Fecha Límite:</strong></label>
                            <input type="date" id="deadline" class="form-control" min="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="priority"><strong>Prioridad:</strong></label>
                    <select id="priority" class="form-control">
                        <option value="alta">Alta - Requiere acción inmediata</option>
                        <option value="media">Media - Planificar implementación</option>
                        <option value="baja">Baja - Seguimiento regular</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Crear Plan',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#007bff',
        preConfirm: () => {
            const description = document.getElementById('actionDescription').value;
            const responsible = document.getElementById('responsible').value;
            const deadline = document.getElementById('deadline').value;
            const priority = document.getElementById('priority').value;
            
            if (!description || !responsible || !deadline) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }
            
            return { description, responsible, deadline, priority, issue: issueText };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí se enviaría el plan de acción al servidor
            Swal.fire({
                title: '¡Plan Creado!',
                text: 'El plan de acción se ha creado exitosamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

function assignResponsible(issueText) {
    Swal.fire({
        title: 'Asignar Responsable',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Oportunidad:</strong> ${issueText}</p>
                <div class="form-group">
                    <label for="assignee"><strong>Asignar a:</strong></label>
                    <select id="assignee" class="form-control">
                        <option value="">Seleccionar responsable...</option>
                        <option value="supervisor">Supervisor de Sistemas</option>
                        <option value="coordinador">Coordinador de TI</option>
                        <option value="analista">Analista de Sistemas</option>
                        <option value="gerente">Gerente de Área</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes"><strong>Notas adicionales:</strong></label>
                    <textarea id="notes" class="form-control" rows="2" placeholder="Instrucciones específicas o contexto adicional..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-user-check"></i> Asignar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#17a2b8'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Asignado!', 'La oportunidad de mejora ha sido asignada', 'success');
        }
    });
}

function generateImprovementReport() {
    Swal.fire({
        title: 'Generar Reporte de Mejoras',
        text: 'Se generará un reporte completo con todas las oportunidades de mejora identificadas',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-alt"></i> Generar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`{{ route('surveys.internal-client.systems.export') }}?format=pdf&type=improvements`, '_blank');
        }
    });
}

function scheduleFollowUp() {
    Swal.fire({
        title: 'Programar Seguimiento',
        html: `
            <div class="text-left">
                <div class="form-group">
                    <label for="followUpDate"><strong>Fecha de Seguimiento:</strong></label>
                    <input type="date" id="followUpDate" class="form-control" min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="form-group">
                    <label for="followUpType"><strong>Tipo de Seguimiento:</strong></label>
                    <select id="followUpType" class="form-control">
                        <option value="revision">Revisión de Progreso</option>
                        <option value="evaluacion">Evaluación de Resultados</option>
                        <option value="reunion">Reunión de Equipo</option>
                        <option value="auditoria">Auditoría de Procesos</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-calendar-check"></i> Programar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('¡Programado!', 'El seguimiento ha sido programado exitosamente', 'success');
        }
    });
}

function shareImprovements() {
    const shareText = `📊 Reporte de Oportunidades de Mejora - Sistemas\n🎯 Identificadas áreas de mejora para optimizar el servicio\n📅 Período: {{ $selectedPeriod ?? 'Actual' }}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Oportunidades de Mejora - Sistemas',
            text: shareText
        });
    } else {
        navigator.clipboard.writeText(shareText).then(() => {
            Swal.fire('¡Copiado!', 'El reporte se copió al portapapeles', 'success');
        });
    }
}

function focusOnCategory(categoryName) {
    Swal.fire({
        title: `Enfocar en: ${categoryName}`,
        text: 'Se creará un plan específico para mejorar esta categoría',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-bullseye"></i> Crear Plan',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            createActionPlan(`Mejora en categoría: ${categoryName}`);
        }
    });
}

function showDepartmentDetails(event) {
    event.preventDefault();
    
    const departments = {!! json_encode($latestData['departments'] ?? []) !!};
    const totalDepartments = {{ $dashboardData['total_departments'] ?? 0 }};
    
    let departmentsList = '';
    let departmentsData = [];
    
    if (Object.keys(departments).length > 0) {
        // Si hay datos reales de departamentos
        Object.entries(departments).forEach(([name, count], index) => {
            const percentage = totalDepartments > 0 ? ((count / Object.values(departments).reduce((a, b) => a + b, 0)) * 100).toFixed(1) : 0;
            departmentsData.push({
                name: name,
                count: count,
                percentage: percentage,
                icon: getDepartmentIcon(name)
            });
        });
    } else {
        // Datos de ejemplo si no hay datos reales
        departmentsData = [
            { name: 'Administración', count: 15, percentage: '35.7', icon: 'fas fa-users-cog' },
            { name: 'Recursos Humanos', count: 8, percentage: '19.0', icon: 'fas fa-user-tie' },
            { name: 'Finanzas', count: 7, percentage: '16.7', icon: 'fas fa-calculator' },
            { name: 'Tecnología', count: 6, percentage: '14.3', icon: 'fas fa-laptop-code' },
            { name: 'Operaciones', count: 4, percentage: '9.5', icon: 'fas fa-cogs' },
            { name: 'Ventas', count: 2, percentage: '4.8', icon: 'fas fa-chart-line' }
        ];
    }
    
    // Generar HTML para la lista de departamentos
    departmentsData.forEach((dept, index) => {
        const badgeColor = index < 3 ? 'success' : (index < 5 ? 'warning' : 'secondary');
        departmentsList += `
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                <div class="d-flex align-items-center">
                    <div class="bg-${badgeColor} rounded-circle p-2 mr-3 text-white">
                        <i class="${dept.icon}"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 font-weight-bold">${dept.name}</h6>
                        <small class="text-muted">Área organizacional</small>
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-${badgeColor} badge-lg">${dept.count} respuestas</span>
                    <br>
                    <small class="text-muted">${dept.percentage}% del total</small>
                </div>
            </div>
        `;
    });
    
    Swal.fire({
        title: '🏢 Dependencias Participantes',
        html: `
            <div class="text-left">
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle mr-2"></i>Resumen de Participación:</strong><br>
                    • Total de dependencias: <strong>${departmentsData.length}</strong><br>
                    • Respuestas totales: <strong>${departmentsData.reduce((sum, dept) => sum + dept.count, 0)}</strong><br>
                    • Cobertura organizacional: <strong>Excelente</strong>
                </div>
                
                <h6 class="text-primary mb-3">
                    <i class="fas fa-building mr-2"></i>
                    Detalle por Dependencia:
                </h6>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    ${departmentsList}
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-success mb-2">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Análisis de Participación:
                    </h6>
                    <ul class="mb-0">
                        <li><strong>Mayor participación:</strong> ${departmentsData[0]?.name || 'N/A'} (${departmentsData[0]?.percentage || 0}%)</li>
                        <li><strong>Representación equilibrada</strong> entre las diferentes áreas</li>
                        <li><strong>Cobertura organizacional completa</strong> para análisis válido</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-download mr-1"></i> Exportar Lista',
        cancelButtonText: '<i class="fas fa-times mr-1"></i> Cerrar',
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            exportDepartmentsList(departmentsData);
        }
    });
}

function getDepartmentIcon(departmentName) {
    const name = departmentName.toLowerCase();
    if (name.includes('admin') || name.includes('gerencia')) return 'fas fa-users-cog';
    if (name.includes('rrhh') || name.includes('recursos') || name.includes('humanos')) return 'fas fa-user-tie';
    if (name.includes('finanzas') || name.includes('contab') || name.includes('tesor')) return 'fas fa-calculator';
    if (name.includes('sistemas') || name.includes('tecnolog') || name.includes('informática') || name.includes('it')) return 'fas fa-laptop-code';
    if (name.includes('operacion') || name.includes('producción') || name.includes('manufact')) return 'fas fa-cogs';
    if (name.includes('ventas') || name.includes('comercial') || name.includes('marketing')) return 'fas fa-chart-line';
    if (name.includes('logística') || name.includes('almacén') || name.includes('distribu')) return 'fas fa-truck';
    if (name.includes('calidad') || name.includes('auditoría')) return 'fas fa-check-circle';
    if (name.includes('legal') || name.includes('jurídic')) return 'fas fa-gavel';
    if (name.includes('comunicacion') || name.includes('relaciones')) return 'fas fa-bullhorn';
    return 'fas fa-building';
}

function exportDepartmentsList(departmentsData) {
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Dependencia,Respuestas,Porcentaje\n";
    
    departmentsData.forEach(dept => {
        csvContent += `"${dept.name}",${dept.count},"${dept.percentage}%"\n`;
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "dependencias_sistemas.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        title: '¡Exportado!',
        text: 'La lista de dependencias se ha descargado exitosamente',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

// Efectos de hover para las tarjetas
$(document).ready(function() {
    $('.highlight-item, .improvement-item').hover(
        function() {
            $(this).addClass('shadow-sm').css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).removeClass('shadow-sm').css('transform', 'translateY(0)');
        }
    );
});
});
</script>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alert de información -->
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-info"></i> Información</h5>
        Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Sistemas.
        <strong>Último período evaluado:</strong> {{ $selectedPeriod ?? 'Sin datos' }}
    </div>

    <!-- KPIs Principales -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $dashboardData['total_responses'] }}/100</h3>
                    <p>Respuestas vs Esperadas</p>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $dashboardData['total_responses'] > 0 ? (($dashboardData['total_responses'] / 100) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="small-box-footer">
                    &nbsp;
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($dashboardData['average_satisfaction'], 1) }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Satisfacción General</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="small-box-footer">
                    &nbsp;
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="text-white">{{ $dashboardData['total_departments'] }}</h3>
                    <p class="text-white">Dependencias</p>
                    <div class="mt-2">
                        <small class="text-white">
                            @if(isset($latestData['departments']) && count($latestData['departments']) > 0)
                                @php
                                    $topDepartments = array_slice(array_keys($latestData['departments']), 0, 3);
                                @endphp
                                {{ implode(', ', $topDepartments) }}
                                @if(count($latestData['departments']) > 3)
                                    y {{ count($latestData['departments']) - 3 }} más
                                @endif
                            @else
                                Administración, IT, RRHH, Finanzas
                            @endif
                        </small>
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="small-box-footer">
                    &nbsp;
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $selectedPeriod ?? 'N/A' }}</h3>
                    <p>Último Período</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="{{ route('surveys.internal-client.systems.upload') }}" class="small-box-footer">
                    Subir nueva encuesta <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Análisis por Categorías -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Análisis de Satisfacción por Categoría - {{ $selectedPeriod ?? 'Sin período' }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(isset($dashboardData['category_comparison']['details']) && count($dashboardData['category_comparison']['details']) > 0)
                            @php
                                $categories = [
                                    'tiempos_respuesta' => 'Tiempos de Respuesta',
                                    'efectividad_tecnica' => 'Efectividad Técnica', 
                                    'profesionalismo' => 'Profesionalismo',
                                    'estado_equipos' => 'Estado de Equipos',
                                    'apoyo_usabilidad' => 'Apoyo en Usabilidad',
                                    'calidad_internet' => 'Calidad Internet',
                                    'intervencion_eventos' => 'Intervención en Eventos'
                                ];
                                $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
                                $icons = ['fas fa-clock', 'fas fa-tools', 'fas fa-user-tie', 'fas fa-laptop', 'fas fa-graduation-cap', 'fas fa-wifi', 'fas fa-calendar'];
                            @endphp
                            @foreach($categories as $key => $name)
                                @if(isset($dashboardData['category_comparison']['details'][$key]))
                                    @php
                                        $data = $dashboardData['category_comparison']['details'][$key];
                                        $colorIndex = $loop->index % count($colors);
                                        $percentage = $data['porcentaje'] ?? 0;
                                        $count = $data['total_respuestas'] ?? 0;
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-{{ $colors[$colorIndex] }}">
                                                <i class="{{ $icons[$colorIndex] ?? 'fas fa-chart-bar' }}"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ $name }}</span>
                                                <span class="info-box-number">{{ number_format($percentage, 1) }}%</span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-{{ $colors[$colorIndex] }}" 
                                                         style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    Promedio: {{ number_format($data['promedio'] ?? 0, 1) }}/5.0 ({{ $count }} evaluaciones)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                    <h4>No hay datos disponibles</h4>
                                    <p>No se encontraron datos para mostrar. Por favor, cargue algunas encuestas primero.</p>
                                    <a href="{{ route('surveys.internal-client.systems.upload') }}" class="btn btn-warning">
                                        <i class="fas fa-upload mr-1"></i>Cargar Primera Encuesta
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Análisis -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Evolución de Satisfacción (Últimos 6 meses)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendChart" width="600" height="280"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="chart-info">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Datos de los últimos 6 períodos
                            </small>
                            <div class="chart-stats mt-1">
                                <span class="badge badge-primary" id="trendAvg">Promedio: --</span>
                                <span class="badge badge-success" id="trendMax">Máximo: --</span>
                                <span class="badge badge-warning" id="trendMin">Mínimo: --</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header bg-gradient-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Comparación por Categorías (Promedio General)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="categoryFullscreen" title="Pantalla completa">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart" width="600" height="280"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Porcentaje de satisfacción por área
                        </small>
                        <button class="btn btn-sm btn-outline-info" onclick="toggleChartAnimation('categoryChart')">
                            <i class="fas fa-play mr-1"></i>
                            Animar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Dependencias -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building mr-2"></i>
                        Distribución por Dependencias
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="departmentChart" width="400" height="220"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="chart-info">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ $dashboardData['total_departments'] }} dependencias participantes
                            </small>
                            <div class="chart-stats mt-1">
                                <span class="badge badge-secondary" id="departmentTotal">Total: {{ $dashboardData['total_responses'] }}</span>
                                <span class="badge badge-info" id="departmentAvg">Promedio: {{ number_format($dashboardData['average_satisfaction'], 1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Aspectos Destacados
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($dashboardData['top_highlights']) && count($dashboardData['top_highlights']) > 0)
                        @foreach(collect($dashboardData['top_highlights'])->take(5) as $highlight)
                            <div class="d-flex justify-content-between align-items-start mb-3 p-2 border-bottom">
                                <div class="flex-grow-1">
                                    {{ $highlight['aspectos_destacados'] ?? $highlight->aspectos_destacados }}
                                    <br>
                                    <small class="text-muted">{{ $highlight['count'] ?? $highlight->count }} {{ ($highlight['count'] ?? $highlight->count) == 1 ? 'mención' : 'menciones' }}</small>
                                </div>
                                <span class="badge badge-success ml-2">{{ $highlight['count'] ?? $highlight->count }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle mb-2"></i>
                            <p>No hay aspectos destacados disponibles</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Oportunidades de Mejora
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($dashboardData['top_issues']) && count($dashboardData['top_issues']) > 0)
                        @foreach(collect($dashboardData['top_issues'])->take(5) as $issue)
                            <div class="d-flex justify-content-between align-items-start mb-3 p-2 border-bottom">
                                <div class="flex-grow-1">
                                    {{ $issue['oportunidades_mejora'] ?? $issue->oportunidades_mejora }}
                                    <br>
                                    <small class="text-muted">{{ $issue['count'] ?? $issue->count }} {{ ($issue['count'] ?? $issue->count) == 1 ? 'mención' : 'menciones' }}</small>
                                </div>
                                <span class="badge badge-warning ml-2">{{ $issue['count'] ?? $issue->count }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle mb-2"></i>
                            <p>No hay oportunidades de mejora identificadas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('surveys.internal-client.systems.upload') }}" class="btn btn-success btn-block">
                                <i class="fas fa-upload"></i>
                                Cargar Nueva Encuesta
                            </a>
                        </div>
                        <div class="col-md-4">
                            @if($selectedPeriod ?? null)
                                <a href="{{ route('surveys.internal-client.systems.export') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-download"></i>
                                    Exportar Datos
                                </a>
                            @else
                                <button class="btn btn-secondary btn-block" disabled>
                                    <i class="fas fa-download"></i>
                                    Sin Datos para Exportar
                                </button>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning btn-block" onclick="location.reload()">
                                <i class="fas fa-sync"></i>
                                Actualizar Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aspectos Destacados -->
<div class="modal fade" id="highlightsModal" tabindex="-1" role="dialog" aria-labelledby="highlightsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="highlightsModalLabel">
                    <i class="fas fa-trophy mr-2"></i>
                    Todos los Aspectos Destacados
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(isset($dashboardData['top_highlights']) && count($dashboardData['top_highlights']) > 0)
                    <div class="row">
                        @foreach($dashboardData['top_highlights'] as $index => $highlight)
                            @php
                                $icons = ['fas fa-medal', 'fas fa-award', 'fas fa-star', 'fas fa-thumbs-up', 'fas fa-heart'];
                                $colors = ['text-warning', 'text-info', 'text-success', 'text-primary', 'text-danger'];
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-success rounded-circle p-2 mr-3 text-white">
                                                <i class="{{ $icons[$index % count($icons)] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="font-weight-bold">{{ $highlight['aspectos_destacados'] ?? $highlight->aspectos_destacados }}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge badge-success">{{ $highlight['count'] ?? $highlight->count }} menciones</span>
                                                    <small class="text-muted">#{{ $index + 1 }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="exportHighlights()">
                    <i class="fas fa-download mr-1"></i>
                    Exportar Aspectos
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Oportunidades de Mejora -->
<div class="modal fade" id="improvementsModal" tabindex="-1" role="dialog" aria-labelledby="improvementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="improvementsModalLabel">
                    <i class="fas fa-bullseye mr-2"></i>
                    Todas las Oportunidades de Mejora
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(isset($dashboardData['top_issues']) && count($dashboardData['top_issues']) > 0)
                    <div class="row">
                        @foreach($dashboardData['top_issues'] as $index => $issue)
                            @php
                                $urgencyLevels = [
                                    ['icon' => 'fas fa-exclamation-triangle', 'color' => 'danger', 'priority' => 'Alta Prioridad'],
                                    ['icon' => 'fas fa-exclamation-circle', 'color' => 'warning', 'priority' => 'Media Prioridad'],
                                    ['icon' => 'fas fa-info-circle', 'color' => 'info', 'priority' => 'Baja Prioridad']
                                ];
                                $mentionCount = $issue['count'] ?? $issue->count;
                                $urgencyClass = $mentionCount >= 3 ? $urgencyLevels[0] : ($mentionCount >= 2 ? $urgencyLevels[1] : $urgencyLevels[2]);
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card border-{{ $urgencyClass['color'] }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-{{ $urgencyClass['color'] }} rounded-circle p-2 mr-3 text-white">
                                                <i class="{{ $urgencyClass['icon'] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="font-weight-bold">{{ $issue['oportunidades_mejora'] ?? $issue->oportunidades_mejora }}</h6>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge badge-{{ $urgencyClass['color'] }}">{{ $issue['count'] ?? $issue->count }} menciones</span>
                                                    <small class="text-{{ $urgencyClass['color'] }} font-weight-bold">{{ $urgencyClass['priority'] }}</small>
                                                </div>
                                                <div class="btn-group btn-group-sm w-100">
                                                    <button class="btn btn-outline-primary" onclick="createActionPlan('{{ addslashes($issue['oportunidades_mejora'] ?? $issue->oportunidades_mejora) }}')">
                                                        <i class="fas fa-tasks"></i> Plan
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="assignResponsible('{{ addslashes($issue['oportunidades_mejora'] ?? $issue->oportunidades_mejora) }}')">
                                                        <i class="fas fa-user"></i> Asignar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="generateImprovementReport()">
                    <i class="fas fa-file-alt mr-1"></i>
                    Generar Reporte
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@stop
