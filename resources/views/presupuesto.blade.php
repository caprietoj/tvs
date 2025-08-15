@extends('adminlte::page')

@section('title', 'Presupuesto Colegio Victoria SAS 2024-2025')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            <i class="fas fa-calculator text-primary"></i> 
            Presupuesto Colegio Victoria SAS 2024-2025
        </h1>
        <div class="header-actions">
            <button id="fullscreenBtn" class="btn btn-outline-primary btn-sm mr-2">
                <i class="fas fa-expand"></i> Pantalla completa
            </button>
            <button id="refreshBtn" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid p-0">
    <div class="row no-gutters">
        <div class="col-12">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header border-0 bg-gradient-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-white mb-0">
                            <i class="fas fa-chart-line mr-2"></i> 
                            Ejecución Presupuestal - Dashboard Interactivo
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group" role="group">
                                <a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml" 
                                   target="_blank" 
                                   class="btn btn-light btn-sm">
                                    <i class="fas fa-external-link-alt"></i> Nueva ventana
                                </a>
                                <button id="zoomInBtn" class="btn btn-light btn-sm">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button id="zoomOutBtn" class="btn btn-light btn-sm">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <!-- Loading Overlay -->
                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="loading-content">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="sr-only">Cargando...</span>
                            </div>
                            <p class="mt-3 mb-0 text-muted">Cargando hoja de cálculo...</p>
                            <small class="text-muted">Esto puede tomar unos segundos</small>
                        </div>
                    </div>
                    
                    <!-- Google Sheets Embedded -->
                    <div id="embedContainer" class="embed-container">
                        <iframe id="sheetsIframe"
                            src="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml?widget=true&amp;headers=false&amp;chrome=false&amp;gid=0"
                            class="sheets-iframe"
                            allowfullscreen="true"
                            mozallowfullscreen="true" 
                            webkitallowfullscreen="true"
                            scrolling="yes">
                        </iframe>
                    </div>
                </div>
                <div class="card-footer bg-light border-top-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-sync-alt text-success"></i> 
                            Datos actualizados automáticamente desde Google Sheets
                        </small>
                        <div class="zoom-controls">
                            <small class="text-muted mr-2">Zoom:</small>
                            <span id="zoomLevel" class="badge badge-secondary">100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Layout principal */
    .content-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        border: none;
        padding: 1rem 1.5rem;
    }
    
    /* Contenedor del iframe mejorado */
    .embed-container {
        position: relative;
        width: 100%;
        height: 85vh;
        min-height: 600px;
        max-height: 1200px;
        overflow: hidden;
        background: #ffffff;
    }
    
    .sheets-iframe {
        width: 100%;
        height: 100%;
        border: none;
        transition: transform 0.3s ease;
        transform-origin: top left;
    }
    
    /* Loading overlay mejorado */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(2px);
    }
    
    .loading-content {
        text-align: center;
        padding: 2rem;
    }
    
    /* Animaciones */
    .spinner-border {
        animation: spinner-border-custom 1s linear infinite;
    }
    
    @keyframes spinner-border-custom {
        to {
            transform: rotate(360deg);
        }
    }
    
    /* Botones mejorados */
    .header-actions .btn {
        border-radius: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .header-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .card-tools .btn-group .btn {
        border-radius: 0;
        border-right: 1px solid rgba(255,255,255,0.2);
    }
    
    .card-tools .btn-group .btn:first-child {
        border-radius: 5px 0 0 5px;
    }
    
    .card-tools .btn-group .btn:last-child {
        border-radius: 0 5px 5px 0;
        border-right: none;
    }
    
    /* Zoom controls */
    .zoom-controls {
        display: flex;
        align-items: center;
    }
    
    #zoomLevel {
        min-width: 50px;
        text-align: center;
    }
    
    /* Responsive mejorado */
    @media (max-width: 1200px) {
        .embed-container {
            height: 80vh;
            min-height: 500px;
        }
    }
    
    @media (max-width: 768px) {
        .embed-container {
            height: 75vh;
            min-height: 450px;
        }
        
        .header-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .header-actions .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        
        .card-tools .btn-group {
            flex-direction: column;
        }
        
        .card-tools .btn-group .btn {
            border-radius: 5px !important;
            border-right: none;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            width: 100%;
        }
        
        .card-tools .btn-group .btn:last-child {
            border-bottom: none;
        }
    }
    
    @media (max-width: 576px) {
        .embed-container {
            height: 70vh;
            min-height: 400px;
        }
        
        .content-header h1 {
            font-size: 1.5rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
    }
    
    /* Fullscreen mode */
    .fullscreen-mode {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: white;
    }
    
    .fullscreen-mode .embed-container {
        height: 100vh;
        min-height: 100vh;
    }
    
    /* Smooth transitions */
    .card,
    .btn,
    .sheets-iframe {
        transition: all 0.3s ease;
    }
    
    /* Custom scrollbar for webkit browsers */
    .sheets-iframe::-webkit-scrollbar {
        width: 8px;
    }
    
    .sheets-iframe::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .sheets-iframe::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .sheets-iframe::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let currentZoom = 1;
    let isFullscreen = false;
    const iframe = $('#sheetsIframe');
    const loadingOverlay = $('#loadingOverlay');
    const embedContainer = $('#embedContainer');
    const card = $('.card');
    
    // Funciones de utilidad
    function showLoading() {
        loadingOverlay.fadeIn(300);
    }
    
    function hideLoading() {
        loadingOverlay.fadeOut(500);
    }
    
    function updateZoomDisplay() {
        $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
    }
    
    function showNotification(message, type = 'info') {
        const toast = `
            <div class="toast" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000;">
                <div class="toast-header bg-${type} text-white">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong class="mr-auto">Información</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;
        $('body').append(toast);
        $('.toast').last().toast({ delay: 3000 }).toast('show');
        
        // Remover el toast después de que se oculte
        $('.toast').last().on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Manejo de carga del iframe
    iframe.on('load', function() {
        setTimeout(() => {
            hideLoading();
            showNotification('Hoja de cálculo cargada correctamente', 'success');
        }, 1000);
    });
    
    iframe.on('error', function() {
        hideLoading();
        loadingOverlay.html(`
            <div class="loading-content">
                <div class="alert alert-warning border-0 shadow">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <h5>No se pudo cargar la hoja de cálculo</h5>
                    <p class="mb-3">Puede que haya un problema de conexión o la hoja no esté disponible.</p>
                    <div class="btn-group" role="group">
                        <button id="retryBtn" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Reintentar
                        </button>
                        <a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml" 
                           target="_blank" 
                           class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Abrir en nueva ventana
                        </a>
                    </div>
                </div>
            </div>
        `).fadeIn();
    });
    
    // Botón de actualizar
    $('#refreshBtn').click(function() {
        showLoading();
        showNotification('Actualizando hoja de cálculo...', 'info');
        
        // Agregar timestamp para forzar recarga
        const currentSrc = iframe.attr('src').split('?')[0];
        const newSrc = currentSrc + '?widget=true&headers=false&chrome=false&gid=0&t=' + new Date().getTime();
        iframe.attr('src', newSrc);
        
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 2000);
    });
    
    // Botón de pantalla completa
    $('#fullscreenBtn').click(function() {
        if (!isFullscreen) {
            enterFullscreen();
        } else {
            exitFullscreen();
        }
    });
    
    function enterFullscreen() {
        card.addClass('fullscreen-mode');
        $('#fullscreenBtn').html('<i class="fas fa-compress"></i> Salir pantalla completa');
        isFullscreen = true;
        showNotification('Modo pantalla completa activado. Presiona ESC para salir.', 'info');
        
        // Agregar clase al body para ocultar otros elementos
        $('body').addClass('modal-open');
        $('.main-sidebar, .main-header').hide();
    }
    
    function exitFullscreen() {
        card.removeClass('fullscreen-mode');
        $('#fullscreenBtn').html('<i class="fas fa-expand"></i> Pantalla completa');
        isFullscreen = false;
        
        // Restaurar elementos ocultos
        $('body').removeClass('modal-open');
        $('.main-sidebar, .main-header').show();
    }
    
    // Salir de pantalla completa con ESC
    $(document).keyup(function(e) {
        if (e.key === "Escape" && isFullscreen) {
            exitFullscreen();
        }
    });
    
    // Funciones de zoom
    $('#zoomInBtn').click(function() {
        if (currentZoom < 2) {
            currentZoom += 0.1;
            applyZoom();
            showNotification(`Zoom aumentado a ${Math.round(currentZoom * 100)}%`, 'info');
        }
    });
    
    $('#zoomOutBtn').click(function() {
        if (currentZoom > 0.5) {
            currentZoom -= 0.1;
            applyZoom();
            showNotification(`Zoom reducido a ${Math.round(currentZoom * 100)}%`, 'info');
        }
    });
    
    function applyZoom() {
        iframe.css('transform', `scale(${currentZoom})`);
        
        // Ajustar el contenedor para mantener el centrado
        const containerHeight = embedContainer.height();
        const containerWidth = embedContainer.width();
        const scaledHeight = containerHeight * currentZoom;
        const scaledWidth = containerWidth * currentZoom;
        
        if (currentZoom !== 1) {
            embedContainer.css({
                'overflow': 'auto',
                'background': '#f8f9fa'
            });
        } else {
            embedContainer.css({
                'overflow': 'hidden',
                'background': '#ffffff'
            });
        }
        
        updateZoomDisplay();
    }
    
    // Zoom con rueda del mouse (Ctrl + scroll)
    embedContainer.on('wheel', function(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            
            if (e.originalEvent.deltaY < 0) {
                // Scroll hacia arriba - zoom in
                if (currentZoom < 2) {
                    currentZoom += 0.05;
                    applyZoom();
                }
            } else {
                // Scroll hacia abajo - zoom out
                if (currentZoom > 0.5) {
                    currentZoom -= 0.05;
                    applyZoom();
                }
            }
        }
    });
    
    // Manejo del botón retry
    $(document).on('click', '#retryBtn', function() {
        location.reload();
    });
    
    // Inicializar
    updateZoomDisplay();
    
    // Auto-refresh cada 5 minutos (opcional)
    setInterval(function() {
        if (!document.hidden) {
            const currentSrc = iframe.attr('src').split('?')[0];
            const newSrc = currentSrc + '?widget=true&headers=false&chrome=false&gid=0&t=' + new Date().getTime();
            iframe.attr('src', newSrc);
        }
    }, 300000); // 5 minutos
    
    // Optimizar rendimiento - pausar auto-refresh cuando la página no está visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            console.log('Página oculta - pausando actualizaciones automáticas');
        } else {
            console.log('Página visible - reanudando actualizaciones automáticas');
        }
    });
    
    // Mostrar indicador de carga inicial
    showLoading();
});
</script>
@stop
