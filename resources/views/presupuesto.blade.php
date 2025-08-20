@extends('adminlte::page')

@section('title', 'Presupuesto Institucional 2024-2025')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-chart-pie text-primary"></i>
                        Presupuesto Institucionalaaaaaaa
                    </h1>
                    <small class="text-muted">Año Escolar 2024-2025</small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/home">Inicio</a></li>
                        <li class="breadcrumb-item active">Presupuesto</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Información introductoria -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-info"></i> Información</h5>
                Aquí puedes consultar y analizar el presupuesto institucional de manera interactiva. Utiliza las pestañas del documento para navegar entre las diferentes secciones presupuestales.
            </div>
        </div>
    </div>

    <!-- Contenedor principal del presupuesto -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body p-0">
                    <div class="embed-responsive" style="height: 85vh; min-height: 600px;">
                        <iframe src="https://docs.google.com/spreadsheets/d/e/2PACX-1vTa6p5Z7trJv0qMl30DxqisXIwDwV1X3n4HdeG-pWrHGck5tcKK7BsL5zyLxQrky6eLVXcGdGVybg9I/pubhtml?widget=true&amp;headers=false" 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border: 0; border-radius: 0 0 8px 8px;"
                                loading="lazy">
                        </iframe>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Última actualización: <span id="lastUpdate">{{ date('d/m/Y H:i') }}</span>
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt mr-1"></i>
                                <a href="#" onclick="location.reload();" class="text-primary">Actualizar datos</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de información adicional -->
    <div class="row mt-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><i class="fas fa-file-excel"></i></h3>
                    <p>Exportar a Excel</p>
                </div>
                <div class="icon">
                    <i class="fas fa-download"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="window.open('https://docs.google.com/spreadsheets/d/e/2PACX-1vTa6p5Z7trJv0qMl30DxqisXIwDwV1X3n4HdeG-pWrHGck5tcKK7BsL5zyLxQrky6eLVXcGdGVybg9I/export?format=xlsx', '_blank')">
                    Descargar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><i class="fas fa-chart-line"></i></h3>
                    <p>Ver en Pantalla Completa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-expand-arrows-alt"></i>
                </div>
                <a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vTa6p5Z7trJv0qMl30DxqisXIwDwV1X3n4HdeG-pWrHGck5tcKK7BsL5zyLxQrky6eLVXcGdGVybg9I/pubhtml" target="_blank" class="small-box-footer">
                    Abrir <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><i class="fas fa-print"></i></h3>
                    <p>Imprimir Reporte</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="window.print()">
                    Imprimir <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><i class="fas fa-question-circle"></i></h3>
                    <p>Ayuda y Soporte</p>
                </div>
                <div class="icon">
                    <i class="fas fa-life-ring"></i>
                </div>
                <a href="#" class="small-box-footer" data-toggle="modal" data-target="#helpModal">
                    Ver ayuda <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ayuda -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle mr-2"></i>Guía de Uso - Presupuesto Institucional
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6><i class="fas fa-mouse-pointer text-primary"></i> Navegación</h6>
                <ul>
                    <li>Utiliza las pestañas en la parte inferior del documento para navegar entre secciones</li>
                    <li>Haz scroll para ver más contenido en cada hoja</li>
                    <li>Puedes usar Ctrl + rueda del mouse para hacer zoom</li>
                </ul>
                
                <h6 class="mt-3"><i class="fas fa-tools text-success"></i> Funciones Disponibles</h6>
                <ul>
                    <li><strong>Exportar:</strong> Descarga el presupuesto en formato Excel</li>
                    <li><strong>Pantalla Completa:</strong> Abre el documento en una nueva ventana</li>
                    <li><strong>Imprimir:</strong> Genera una versión imprimible</li>
                    <li><strong>Actualizar:</strong> Recarga los datos más recientes</li>
                </ul>
                
                <h6 class="mt-3"><i class="fas fa-info-circle text-info"></i> Información Adicional</h6>
                <p>Este panel muestra el presupuesto institucional en tiempo real. Los datos se actualizan automáticamente desde la fuente principal.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.embed-responsive iframe {
    transition: all 0.3s ease;
}

.card-outline.card-primary {
    border-top: 3px solid #007bff;
}

.alert-info {
    border-left: 4px solid #17a2b8;
}

.small-box:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.content-header h1 {
    font-weight: 600;
}

@media print {
    .content-header, .alert, .row:last-child, .modal {
        display: none !important;
    }
    
    .embed-responsive {
        height: 100vh !important;
    }
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Actualizar timestamp cada minuto
    setInterval(function() {
        $('#lastUpdate').text(new Date().toLocaleString('es-ES'));
    }, 60000);
    
    // Mostrar loading cuando se carga el iframe
    $('iframe').on('load', function() {
        console.log('Presupuesto cargado exitosamente');
    });
});
</script>
@endsection
