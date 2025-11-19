@extends('adminlte::page')

@section('title', 'Previsualizar Documento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-eye mr-2"></i>{{ $document->name }}
        </h1>
        <div>
            <a href="{{ route('compras.documents.download', $document->id) }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i>Descargar
            </a>
            <a href="{{ route('compras.documents.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-white">
                <h3 class="card-title mb-1">
                    <i class="fas fa-file-pdf mr-2"></i>{{ $document->original_filename }}
                </h3>
                <small>
                    <i class="fas fa-user mr-1"></i>Subido por: {{ $document->user->name }} | 
                    <i class="fas fa-calendar mr-1"></i>{{ $document->created_at->format('d/m/Y H:i') }} |
                    <i class="fas fa-hdd mr-1"></i>{{ $document->formatted_size }}
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="pdf-viewer-container">
            <iframe 
                src="{{ $filePath }}" 
                type="application/pdf" 
                width="100%" 
                height="800px"
                style="border: none;">
                <p>Su navegador no soporta la visualización de PDFs. 
                   <a href="{{ route('compras.documents.download', $document->id) }}">Descargar el archivo</a>
                </p>
            </iframe>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .pdf-viewer-container {
        background-color: #525659;
        min-height: 800px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,.08);
        border-radius: 8px;
    }

    iframe {
        display: block;
    }
</style>
@stop

@section('js')
<script>
    // Ajustar altura del iframe según la ventana
    window.addEventListener('load', function() {
        const iframe = document.querySelector('iframe');
        const minHeight = 800;
        const windowHeight = window.innerHeight - 250; // Restar header y padding
        
        if (windowHeight > minHeight) {
            iframe.style.height = windowHeight + 'px';
            document.querySelector('.pdf-viewer-container').style.minHeight = windowHeight + 'px';
        }
    });

    window.addEventListener('resize', function() {
        const iframe = document.querySelector('iframe');
        const minHeight = 800;
        const windowHeight = window.innerHeight - 250;
        
        if (windowHeight > minHeight) {
            iframe.style.height = windowHeight + 'px';
            document.querySelector('.pdf-viewer-container').style.minHeight = windowHeight + 'px';
        }
    });
</script>
@stop
