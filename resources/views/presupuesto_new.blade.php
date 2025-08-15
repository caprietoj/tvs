@extends('adminlte::page')

@section('title', 'Presupuesto Colegio Victoria SAS 2024-2025')

@section('content_header')
    <h1 class="text-center">
        <i class="fas fa-calculator"></i> 
        Presupuesto Colegio Victoria SAS 2024-2025
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> 
                        Ejecución Presupuestal - Hoja de Cálculo
                    </h3>
                    <div class="card-tools">
                        <a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml" 
                           target="_blank" 
                           class="btn btn-light btn-sm">
                            <i class="fas fa-external-link-alt"></i> Abrir en nueva ventana
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Google Sheets Embedded -->
                    <div class="embed-responsive" style="height: 800px;">
                        <iframe 
                            src="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml?widget=true&amp;headers=false"
                            class="embed-responsive-item"
                            style="width: 100%; height: 100%; border: none;"
                            allowfullscreen="true"
                            mozallowfullscreen="true" 
                            webkitallowfullscreen="true">
                        </iframe>
                    </div>
                </div>
                <div class="card-footer text-muted text-center">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Esta hoja de cálculo se actualiza automáticamente desde Google Sheets
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .embed-responsive {
        position: relative;
        display: block;
        width: 100%;
        padding: 0;
        overflow: hidden;
    }
    
    .embed-responsive-item {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    
    .card-header .btn {
        margin-left: 10px;
    }
    
    /* Asegurar que el iframe sea responsive */
    @media (max-width: 768px) {
        .embed-responsive {
            height: 600px;
        }
    }
    
    @media (max-width: 576px) {
        .embed-responsive {
            height: 500px;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Mostrar mensaje de carga mientras el iframe carga
        $('.embed-responsive').append('<div id="loading" class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Cargando hoja de cálculo...</div>');
        
        // Ocultar mensaje de carga cuando el iframe termine de cargar
        $('iframe').on('load', function() {
            $('#loading').fadeOut();
        });
        
        // Manejar errores de carga
        $('iframe').on('error', function() {
            $('#loading').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No se pudo cargar la hoja de cálculo. <a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vSV4hT9WzetC2ajp1z6GeSY0_yCvu_bNWNgCMmVAjkIYjAG5Mq0BUgQs3NKAS4X61AdmLrsP4iVKS3F/pubhtml" target="_blank">Haz clic aquí para abrirla en una nueva ventana</a>.</div>');
        });
    });
</script>
@stop
