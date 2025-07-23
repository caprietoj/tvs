@extends('adminlte::page')

@section('title', 'Análisis Comparativo - Servicios Complementarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line text-primary"></i> Análisis Comparativo de Encuestas de Satisfacción</h1>
        <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && !empty($comparisonData))
    
    <!-- Resumen Ejecutivo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-analytics"></i> Resumen Ejecutivo de Comparación
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            Comparativo Octubre 2023 (Sapore) - Mayo 2024 (Sapore)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Período Base</span>
                                    <span class="info-box-number">Octubre 2023</span>
                                    <span class="progress-description">Proveedor: Sapore</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Período Comparación</span>
                                    <span class="info-box-number">Mayo 2024</span>
                                    <span class="progress-description">Proveedor: Sapore</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tendencia Cafetería</span>
                                    <span class="info-box-number">Crítica</span>
                                    <span class="progress-description">Requiere acción inmediata</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-bus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Transporte</span>
                                    <span class="info-box-number">Estable</span>
                                    <span class="progress-description">Metro Juniors</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metodología -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Metodología de Evaluación
            </h3>
        </div>
        <div class="card-body">
            <p class="lead">Las variables consideradas fueron las mismas evaluadas en el año escolar anterior para medir el impacto de las acciones de mejora proyectadas.</p>
            <h5><strong>Comparativo Octubre 2023 (Sapore) - Mayo 2024 (Sapore):</strong></h5>
            <p class="text-muted"><strong>AÑO ESCOLAR 2023-2024</strong></p>
        </div>
    </div>

    <!-- Análisis Comparativo por Servicios -->
    <div class="row">
        <!-- Análisis del Servicio de Cafetería -->
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-utensils"></i> SERVICIO DE CAFETERÍA
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger">Deterioro Crítico</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container mb-3">
                        <canvas id="cafeteriaChart" style="height: 250px;"></canvas>
                    </div>
                    
                    <!-- Métricas Detalladas -->
                    <div class="row">
                        <div class="col-6">
                            <strong class="text-info">Octubre 2023</strong>
                            <ul class="list-unstyled text-sm">
                                <li>Calidad y Sabor: <span class="float-right">75,6%</span></li>
                                <li>Porciones: <span class="float-right">64,2%</span></li>
                                <li>Menú: <span class="float-right">48,1%</span></li>
                                <li>Variedad: <span class="float-right">50%</span></li>
                                <li>Temperatura: <span class="float-right">46,3%</span></li>
                                <li>Limpieza: <span class="float-right">93,8%</span></li>
                                <li>Trato Personal: <span class="float-right">54,3%</span></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <strong class="text-warning">Mayo 2024</strong>
                            <ul class="list-unstyled text-sm">
                                <li>Calidad y Sabor: 
                                    <span class="float-right">
                                        31,4% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Porciones: 
                                    <span class="float-right">
                                        44% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Menú: 
                                    <span class="float-right">
                                        37% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Variedad: 
                                    <span class="float-right">
                                        42,5% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Temperatura: 
                                    <span class="float-right">
                                        38,8% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Limpieza: 
                                    <span class="float-right">
                                        74,5% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                                <li>Trato Personal: 
                                    <span class="float-right">
                                        51,8% <i class="fas fa-arrow-down text-danger ml-1"></i>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Indicadores de Cambio -->
                    <div class="mt-3">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Disminuciones Críticas:</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-danger">• Calidad y Sabor: <strong>-44,2%</strong></small><br>
                                <small class="text-danger">• Limpieza: <strong>-19,3%</strong></small><br>
                                <small class="text-danger">• Porciones: <strong>-18,6%</strong></small>
                            </div>
                            <div class="col-6">
                                <small class="text-danger">• Menú: <strong>-11,1%</strong></small><br>
                                <small class="text-danger">• Variedad: <strong>-7,5%</strong></small><br>
                                <small class="text-danger">• Temperatura: <strong>-7,5%</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis del Servicio de Transporte -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bus"></i> SERVICIO DE TRANSPORTE
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">Desempeño Estable</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Metro Juniors:</strong> Mismo aliado estratégico mantenido por buenos resultados.
                    </div>
                    
                    <div class="chart-container mb-3">
                        <canvas id="transportChart" style="height: 200px;"></canvas>
                    </div>
                    
                    <!-- Métricas de Transporte -->
                    <div class="row">
                        <div class="col-6">
                            <strong class="text-info">Mayo 2024</strong>
                            <ul class="list-unstyled text-sm">
                                <li><strong>Colaboradores:</strong></li>
                                <li>Puntualidad: <span class="float-right">96%</span></li>
                                <li>Limpieza: <span class="float-right">100%</span></li>
                                <li>Trato Personal: <span class="float-right">91,6%</span></li>
                                <li>Comunicación: <span class="float-right">100%</span></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <strong class="text-success">Octubre 2024</strong>
                            <ul class="list-unstyled text-sm">
                                <li><strong>Familias:</strong></li>
                                <li>Puntualidad: <span class="float-right">87,5%</span></li>
                                <li>Limpieza: <span class="float-right">97,5%</span></li>
                                <li>Trato Personal: <span class="float-right">100%</span></li>
                                <li>Comunicación: <span class="float-right">87,5%</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-success"><i class="fas fa-check-circle"></i> Fortalezas Mantenidas:</h6>
                        <small class="text-success">• Limpieza de vehículos: Excelente desempeño<br>
                        • Trato del personal: Mejora continua<br>
                        • Comunicación: Niveles altos mantenidos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toma de Decisiones -->
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i> Toma de decisiones enfocadas en la mejora continua del proceso
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <h5><i class="fas fa-clipboard-check"></i> Decisión Estratégica</h5>
                <p class="mb-0">A partir de los resultados obtenidos se toma la determinación de <strong>cambio de proveedor del servicio de alimentos</strong> para el año escolar 2024-2025.</p>
            </div>
        </div>
    </div>

    <!-- Implementación de Estrategia de Mejora -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Implementación de Estrategia de Mejora
            </h3>
            <div class="card-tools">
                <span class="badge badge-success">Cambio de Proveedor</span>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <h5><i class="fas fa-sync"></i> Comparativo Mayo 2024 (Sapore) - Octubre 2024 (Aldimark)</h5>
                <p><strong>SAPORE (Antiguo Proveedor) Vs ALDIMARK (Nuevo Proveedor)</strong></p>
                <p class="mb-0">Se conservan las mismas variantes para evaluar el servicio.</p>
            </div>

            <!-- Grid de Mejoras -->
            <div class="row">
                <!-- Calidad y Sabor -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-star"></i> Calidad y Sabor</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-danger">7,54%</span> → <span class="badge badge-success">32,5%</span>
                                    <span class="text-success d-block"><strong>+25%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-warning">31,4%</span> → <span class="badge badge-success">42%</span>
                                    <span class="text-success d-block"><strong>+10,6%</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Validación de recetas estándar, seguimiento del personal manipulador y calidad de insumos.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Porciones -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-balance-scale"></i> Porciones Ofrecidas</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-danger">9,4%</span> → <span class="badge badge-success">48,8%</span>
                                    <span class="text-success d-block"><strong>+39,4%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-warning">38,8%</span> → <span class="badge badge-success">74,4%</span>
                                    <span class="text-success d-block"><strong>+30%</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Verificación de menaje y gramaje según recomendaciones nutricionales.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Menú -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Menú Ofrecido</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-warning">38,8%</span> → <span class="badge badge-success">86%</span>
                                    <span class="text-success d-block"><strong>+47,16%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-warning">37%</span> → <span class="badge badge-success">42,5%</span>
                                    <span class="text-success d-block"><strong>+5,5%</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Reuniones semanales y ajustes a la oferta gastronómica.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Variedad -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-utensils"></i> Variedad del Menú</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-danger">22,6%</span> → <span class="badge badge-success">65,1%</span>
                                    <span class="text-success d-block"><strong>+42,5%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-success">42,5%</span> → <span class="badge badge-success">42,5%</span>
                                    <span class="text-muted d-block"><strong>Estable</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Comité de alimentos y análisis de nuevas recetas.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Temperatura -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-thermometer-half"></i> Temperatura</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-warning">39,6%</span> → <span class="badge badge-success">67,4%</span>
                                    <span class="text-success d-block"><strong>+27,8%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-warning">38,8%</span> → <span class="badge badge-success">42,5%</span>
                                    <span class="text-success d-block"><strong>+3,7%</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Verificación de equipos y mantenimientos preventivos.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Limpieza -->
                <div class="col-md-6 mb-4">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-broom"></i> Limpieza</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <small><strong>Colaboradores:</strong></small><br>
                                    <span class="badge badge-info">74,5%</span> → <span class="badge badge-success">90,2%</span>
                                    <span class="text-success d-block"><strong>+15,7%</strong></span>
                                </div>
                                <div class="col-6">
                                    <small><strong>Familias:</strong></small><br>
                                    <span class="badge badge-info">51%</span> → <span class="badge badge-info">52%</span>
                                    <span class="text-success d-block"><strong>+1%</strong></span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <strong>Plan:</strong> Revisiones permanentes y buenas prácticas de manipulación.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Mejoras -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-success text-white">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-trophy"></i> Resumen de Mejoras Obtenidas</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h4 class="mb-1">+47,16%</h4>
                                    <small>Mayor mejora: Menú (Colaboradores)</small>
                                </div>
                                <div class="col-md-4">
                                    <h4 class="mb-1">+39,4%</h4>
                                    <small>Porciones (Colaboradores)</small>
                                </div>
                                <div class="col-md-4">
                                    <h4 class="mb-1">+36,3%</h4>
                                    <small>Trato Personal (Colaboradores)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan de Acción para Mejora Continua -->
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i> Plan de Acción para Mejora Continua
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> Estrategia Integral de Mejora</h5>
                        <p class="mb-0">Acciones específicas para mantener y mejorar la calidad de los servicios complementarios.</p>
                    </div>
                    
                    <div class="row">
                        <!-- Plan Transporte -->
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-bus"></i> Servicio de Transporte - Metro Juniors</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-handshake text-primary"></i>
                                            <strong>Acuerdos Estratégicos:</strong><br>
                                            <small>Elaboración y divulgación de acuerdos esenciales (TVS-Metro Juniors-Familias)</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-graduation-cap text-primary"></i>
                                            <strong>Capacitación Continua:</strong><br>
                                            <small>Personal operativo alineado con valores TVS y atributos IB</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-primary"></i>
                                            <strong>Respuestas Oportunas:</strong><br>
                                            <small>Trazabilidad y soportes técnicos para PQR</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-mobile-alt text-primary"></i>
                                            <strong>Herramientas Tecnológicas:</strong><br>
                                            <small>Uso eficiente de ONTRACK para reporte de novedades</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-tools text-primary"></i>
                                            <strong>Verificación Continua:</strong><br>
                                            <small>Estado de vehículos y personal de apoyo</small>
                                        </li>
                                        <li>
                                            <i class="fas fa-comments text-primary"></i>
                                            <strong>Retroalimentación:</strong><br>
                                            <small>Comunicación permanente entre todas las partes</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Cafetería -->
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-utensils"></i> Servicio de Cafetería - Aldimark</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-clipboard-check text-success"></i>
                                            <strong>Validación de Recetas:</strong><br>
                                            <small>Recetas estándar y seguimiento del personal manipulador</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-balance-scale text-success"></i>
                                            <strong>Control de Porciones:</strong><br>
                                            <small>Verificación de menaje según recomendaciones nutricionales</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar-week text-success"></i>
                                            <strong>Reuniones Semanales:</strong><br>
                                            <small>Verificación de propuestas de menús y ajustes</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-users text-success"></i>
                                            <strong>Comité de Alimentos:</strong><br>
                                            <small>Menús balanceados acordes con gustos de la comunidad</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-thermometer-half text-success"></i>
                                            <strong>Control de Temperatura:</strong><br>
                                            <small>Verificación de equipos y mantenimientos preventivos</small>
                                        </li>
                                        <li>
                                            <i class="fas fa-broom text-success"></i>
                                            <strong>Buenas Prácticas:</strong><br>
                                            <small>Revisiones permanentes y seguridad alimentaria</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores de Seguimiento -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-secondary text-white">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Indicadores de Seguimiento</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <h5 class="mb-1">Mensual</h5>
                                            <small>Evaluación de métricas de satisfacción</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <h5 class="mb-1">Trimestral</h5>
                                            <small>Análisis comparativo de tendencias</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <h5 class="mb-1">Anual</h5>
                                            <small>Evaluación integral y renovación</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Retroalimentación y Comentarios -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comments"></i> Retroalimentación y Comentarios
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Su Opinión es Importante</h5>
                        <p class="mb-0">Comparta sus observaciones y sugerencias para mejorar continuamente nuestros servicios complementarios.</p>
                    </div>
                    
                    <form method="POST" action="{{ route('surveys.transport.comparison.comment') }}" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario" class="font-weight-bold">
                                        <i class="fas fa-user"></i> Usuario
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('usuario') is-invalid @enderror" 
                                           id="usuario" 
                                           name="usuario" 
                                           value="{{ old('usuario') }}" 
                                           placeholder="Ingrese su nombre"
                                           required>
                                    @error('usuario')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="font-weight-bold">
                                        <i class="fas fa-envelope"></i> Correo Electrónico
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="correo@ejemplo.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="servicio" class="font-weight-bold">
                                <i class="fas fa-cogs"></i> Servicio a Comentar
                            </label>
                            <select class="form-control @error('servicio') is-invalid @enderror" 
                                    id="servicio" 
                                    name="servicio" 
                                    required>
                                <option value="">Seleccione un servicio</option>
                                <option value="cafeteria" {{ old('servicio') == 'cafeteria' ? 'selected' : '' }}>
                                    Servicio de Cafetería
                                </option>
                                <option value="transporte" {{ old('servicio') == 'transporte' ? 'selected' : '' }}>
                                    Servicio de Transporte
                                </option>
                                <option value="ambos" {{ old('servicio') == 'ambos' ? 'selected' : '' }}>
                                    Ambos Servicios
                                </option>
                            </select>
                            @error('servicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="comentario" class="font-weight-bold">
                                <i class="fas fa-comment-alt"></i> Comentario
                            </label>
                            <textarea class="form-control @error('comentario') is-invalid @enderror" 
                                      id="comentario" 
                                      name="comentario" 
                                      rows="5" 
                                      placeholder="Comparta sus observaciones, sugerencias o comentarios sobre los servicios complementarios..."
                                      required>{{ old('comentario') }}</textarea>
                            @error('comentario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Sus comentarios nos ayudan a mejorar continuamente la calidad de nuestros servicios.
                            </small>
                        </div>
                        
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane"></i> Enviar Comentario
                            </button>
                            <button type="reset" class="btn btn-secondary ml-2">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Contacto -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-dark text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <i class="fas fa-phone-alt"></i> Información de Contacto
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Coordinación Bienestar Estudiantil</strong></p>
                            <small><i class="fas fa-envelope"></i> bienestar@tvs.edu.co</small>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Servicios Complementarios</strong></p>
                            <small><i class="fas fa-phone"></i> Ext. 1234</small>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Horario de Atención</strong></p>
                            <small><i class="fas fa-clock"></i> Lunes a Viernes: 7:00 AM - 5:00 PM</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Formulario de Selección de Períodos -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-search"></i> Seleccionar Períodos para Comparar
            </h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif
            
            <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="period1">Primer Período:</label>
                            <select name="period1" id="period1" class="form-control" required>
                                <option value="">Seleccione un período</option>
                                @if(isset($periods))
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period }}">{{ $period->period }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="period2">Segundo Período:</label>
                            <select name="period2" id="period2" class="form-control" required>
                                <option value="">Seleccione un período</option>
                                @if(isset($periods))
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period }}">{{ $period->period }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service">Servicio:</label>
                            <select name="service" id="service" class="form-control">
                                <option value="both">Ambos Servicios</option>
                                <option value="cafeteria">Solo Cafetería</option>
                                <option value="transport">Solo Transporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dependency">Dependencia:</label>
                            <select name="dependency" id="dependency" class="form-control">
                                <option value="all">Todas las Dependencias</option>
                                <option value="Sistemas">Sistemas</option>
                                <option value="Recursos Humanos">Recursos Humanos</option>
                                <option value="Enfermería">Enfermería</option>
                                <option value="Almacén">Almacén</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-chart-line"></i> Generar Análisis Comparativo
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
@stop

@section('css')
<style>
    .badge {
        font-size: 0.9em;
    }
    .card-title i {
        margin-right: 10px;
    }
    .list-unstyled li {
        margin-bottom: 5px;
    }
    
    /* Estilos adicionales para mejorar la apariencia profesional */
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .info-box {
        transition: transform 0.2s ease-in-out;
    }
    
    .info-box:hover {
        transform: scale(1.02);
    }
    
    .badge-lg {
        font-size: 1.1em;
        padding: 0.5em 0.8em;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: none;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    }
    
    .text-shadow {
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .card {
            margin-bottom: 1rem;
        }
        
        .info-box {
            margin-bottom: 1rem;
        }
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Animaciones de las tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.2s ease-in-out';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Tooltips para los badges
    const badges = document.querySelectorAll('.badge');
    badges.forEach(function(badge) {
        badge.setAttribute('data-toggle', 'tooltip');
        badge.setAttribute('data-placement', 'top');
        badge.setAttribute('title', 'Porcentaje de satisfacción');
    });

    // Inicializar tooltips
    if (typeof $ !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Efectos de aparición progresiva
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Aplicar efectos a las tarjetas
    cards.forEach(function(card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(card);
    });

    // Contador animado para badges
    const animateCounters = function() {
        const counters = document.querySelectorAll('.badge');
        counters.forEach(function(counter) {
            const text = counter.textContent;
            const match = text.match(/(\d+(?:\.\d+)?)/);
            if (match) {
                const finalValue = parseFloat(match[1]);
                const duration = 1000;
                const startTime = performance.now();
                
                const animate = function(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const currentValue = finalValue * progress;
                    
                    counter.textContent = text.replace(match[1], currentValue.toFixed(1));
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        counter.textContent = text;
                    }
                };
                
                requestAnimationFrame(animate);
            }
        });
    };

    // Ejecutar animación de contadores después de un breve retraso
    setTimeout(animateCounters, 500);
});
</script>
@stop
