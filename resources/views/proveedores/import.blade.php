@extends('adminlte::page')

@section('title', 'Importar Proveedores')

@section('content_header')
    <h1>Importar Proveedores</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title">Importación Masiva de Proveedores</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('errors'))
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Errores encontrados:</h5>
                <ul class="mb-0">
                    @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Instrucciones:</h5>
                    <p>1. Copie los datos desde Excel manteniendo el siguiente orden de columnas:</p>
                    <p class="ml-3"><strong>Nombre | NIT | Dirección | Ciudad | Teléfono | Email | Persona de Contacto | Segmento de Mercado | Servicio/Producto | Alto Riesgo (0/1) | Proveedor Crítico (0/1)</strong></p>
                    <p>2. Los segmentos de mercado válidos son:</p>
                    <ul class="ml-4">
                        <li>Papelería y útiles de oficina</li>
                        <li>Aseo y limpieza</li>
                        <li>Tecnología y equipos de cómputo</li>
                        <li>Alimentos y cafetería</li>
                        <li>Materiales de construcción</li>
                        <li>Publicidad e impresión</li>
                        <li>Otro</li>
                    </ul>
                    <p>3. Pegue los datos en el área de texto de abajo</p>
                    <p>4. Cada fila debe representar un proveedor</p>
                    <p>5. Los campos deben estar separados por tabuladores (como al copiar desde Excel)</p>
                    
                    <div class="mb-3">
                        <a href="{{ route('proveedores.download-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download mr-1"></i>Descargar Plantilla de Ejemplo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('proveedores.process-import') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="data">Datos de Proveedores</label>
                <textarea name="data" id="data" class="form-control" rows="10" placeholder="Pegue aquí los datos desde Excel..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload mr-2"></i>Procesar Importación
            </button>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .card-header {
        background-color: #364E76 !important;
    }
    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }
    .btn-primary:hover {
        background-color: #2b3e5f;
        border-color: #2b3e5f;
    }
</style>
@stop
