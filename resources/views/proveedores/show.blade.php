@extends('adminlte::page')

@section('title', 'Detalles del Proveedor')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Detalles del Proveedor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}">Proveedores</a></li>
                    <li class="breadcrumb-item active">Detalles</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información del Proveedor</h3>
                    </div>
                    <div class="card-body">
                        <!-- Información Básica -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fas fa-info-circle"></i> Información Básica</h4>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre/Razón Social:</label>
                                    <p class="form-control-static">{{ $proveedor->nombre }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NIT:</label>
                                    <p class="form-control-static">{{ $proveedor->nit }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fas fa-address-card"></i> Información de Contacto</h4>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Dirección:</label>
                                    <p class="form-control-static">{{ $proveedor->direccion }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ciudad:</label>
                                    <p class="form-control-static">{{ $proveedor->ciudad }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Teléfono:</label>
                                    <p class="form-control-static">{{ $proveedor->telefono }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email:</label>
                                    <p class="form-control-static">{{ $proveedor->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Persona de Contacto:</label>
                                    <p class="form-control-static">{{ $proveedor->persona_contacto }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Clasificación -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fas fa-tags"></i> Clasificación del Proveedor</h4>
                                <hr>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Servicio/Producto Ofrecido:</label>
                                    <p class="form-control-static">{{ $proveedor->servicio_producto }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proveedor Crítico:</label>
                                    <p class="form-control-static">
                                        @if($proveedor->proveedor_critico)
                                            <span class="badge badge-danger">Sí</span>
                                        @else
                                            <span class="badge badge-success">No</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alto Riesgo:</label>
                                    <p class="form-control-static">
                                        @if($proveedor->alto_riesgo)
                                            <span class="badge badge-warning">Sí</span>
                                        @else
                                            <span class="badge badge-success">No</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Evaluación y Puntajes -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fas fa-chart-bar"></i> Evaluación y Puntajes</h4>
                                <hr>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-money-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Forma de Pago</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_forma_pago }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Referencias</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_referencias }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Descuento</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_descuento }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-globe"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cobertura</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_cobertura }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-purple"><i class="fas fa-plus-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Valores Agregados</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_valores_agregados }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-maroon"><i class="fas fa-tags"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Nivel de Precios</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_precios }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fas fa-clipboard-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Criterios Técnicos</span>
                                        <span class="info-box-number">{{ $proveedor->puntaje_criterios_tecnicos }} pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="info-box bg-{{ $proveedor->estado === 'Seleccionado' ? 'success' : 'danger' }}">
                                    <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Puntaje Total</span>
                                        <span class="info-box-number">{{ number_format($proveedor->puntaje_total, 2) }} pts</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $proveedor->puntaje_total }}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Estado: {{ $proveedor->estado }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documentación -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="text-primary"><i class="fas fa-file"></i> Documentación</h4>
                                <hr>
                            </div>
                            @php
                                $documents = [
                                    'camara_comercio' => ['icon' => 'fas fa-file-pdf text-danger', 'name' => 'Cámara de Comercio'],
                                    'rut' => ['icon' => 'fas fa-file-pdf text-danger', 'name' => 'RUT'],
                                    'cedula_representante' => ['icon' => 'fas fa-id-card text-info', 'name' => 'Cédula Representante'],
                                    'certificacion_bancaria' => ['icon' => 'fas fa-university text-success', 'name' => 'Certificación Bancaria'],
                                    'seguridad_social' => ['icon' => 'fas fa-shield-alt text-warning', 'name' => 'Seguridad Social'],
                                    'certificacion_alturas' => ['icon' => 'fas fa-hard-hat text-warning', 'name' => 'Certificación Alturas'],
                                    'matriz_peligros' => ['icon' => 'fas fa-exclamation-triangle text-danger', 'name' => 'Matriz de Peligros'],
                                    'matriz_epp' => ['icon' => 'fas fa-user-shield text-info', 'name' => 'Matriz EPP'],
                                    'estadisticas' => ['icon' => 'fas fa-chart-line text-success', 'name' => 'Estadísticas']
                                ];
                            @endphp
                            
                            @foreach($documents as $key => $doc)
                                @if($proveedor->{$key.'_path'})
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon"><i class="{{ $doc['icon'] }}"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $doc['name'] }}</span>
                                            <a href="{{ Storage::url($proveedor->{$key.'_path'}) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                                <i class="fas fa-download mr-1"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary float-right">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-primary:not(.card-outline) > .card-header {
            background-color: #364E76;
        }
        
        .text-primary {
            color: #364E76 !important;
        }
        
        .btn-warning {
            color: #ffffff;
            background-color: #f0ad4e;
            border-color: #eea236;
        }
        
        .btn-warning:hover {
            color: #ffffff;
            background-color: #ec971f;
            border-color: #d58512;
        }
        
        .form-control-static {
            font-size: 1rem;
            padding: 0.375rem 0;
            margin-bottom: 0;
            border-bottom: 1px solid rgba(0,0,0,.1);
        }
        
        hr {
            border-top: 1px solid rgba(54, 78, 118, 0.1);
        }
        
        .badge {
            font-size: 0.9rem;
            padding: 0.375rem 0.5625rem;
        }
        
        .badge-success {
            background-color: #28a745;
        }
    </style>
@stop
