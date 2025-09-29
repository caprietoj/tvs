@extends('adminlte::page')

@section('title', 'Detalles de Previsita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Detalles de Previsita #{{ $previsita->id }}</h1>
        <div>
            <a href="{{ route('previsitas.edit', $previsita) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('previsitas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Información General</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lugar</span>
                                <span class="info-box-number">{{ $previsita->lugar }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-user"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Responsable</span>
                                <span class="info-box-number">{{ $previsita->responsable }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha de Visita</span>
                                <span class="info-box-number">{{ $previsita->fecha_visita->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon {{ $previsita->vencimiento < now() ? 'bg-danger' : ($previsita->vencimiento <= now()->addDays(7) ? 'bg-warning' : 'bg-success') }}">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vencimiento</span>
                                @if($previsita->vencimiento)
                                    <span class="info-box-number">{{ $previsita->vencimiento->format('d/m/Y') }}</span>
                                    @if($previsita->vencimiento < now())
                                        <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vencida</small>
                                    @elseif($previsita->vencimiento <= now()->addDays(7))
                                        <small class="text-warning"><i class="fas fa-clock"></i> Próxima a vencer</small>
                                    @endif
                                @else
                                    <span class="info-box-number text-muted">Sin vencimiento</span>
                                    <small class="text-info"><i class="fas fa-info-circle"></i> No requiere inspección</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><strong>Aprobación del Sitio:</strong></label>
                            <div>
                                <span class="badge badge-lg {{ $previsita->aprobacion_sitio ? 'badge-success' : 'badge-danger' }}">
                                    <i class="fas {{ $previsita->aprobacion_sitio ? 'fa-check' : 'fa-times' }}"></i>
                                    {{ $previsita->aprobacion_sitio ? 'Sí - Aprobado' : 'No - No Aprobado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($previsita->observaciones_recomendaciones)
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Observaciones y Recomendaciones Importantes</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div class="mt-2">
                        {{ $previsita->observaciones_recomendaciones }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($previsita->drive_link)
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-google-drive text-primary mr-2"></i>
                    Enlace de Google Drive
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fas fa-link"></i>
                    <div class="mt-2">
                        <a href="{{ $previsita->drive_link }}" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-google-drive mr-1"></i>
                            Abrir enlace de Drive
                            <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                        <br>
                        <small class="text-muted mt-2 d-block">
                            {{ $previsita->drive_link }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Información del Sistema</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><strong>Creado por:</strong></label>
                    <p>{{ $previsita->user->name ?? 'N/A' }}</p>
                </div>
                
                <div class="form-group">
                    <label><strong>Fecha de creación:</strong></label>
                    <p>{{ $previsita->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                <div class="form-group">
                    <label><strong>Última actualización:</strong></label>
                    <p>{{ $previsita->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                @if($previsita->created_at != $previsita->updated_at)
                <div class="form-group">
                    <small class="text-muted">
                        <i class="fas fa-edit"></i> Este registro ha sido modificado
                    </small>
                </div>
                @endif
            </div>
        </div>

        <!-- Sección de archivos -->
        @if($previsita->archivos && $previsita->archivos->count() > 0)
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Archivos de Novedades ({{ $previsita->archivos->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($previsita->archivos as $archivo)
                    <div class="col-md-6 mb-3">
                        <div class="card border">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    @if($archivo->esImagen())
                                        <i class="fas fa-image fa-2x text-success"></i>
                                    @elseif($archivo->esWord())
                                        <i class="fas fa-file-word fa-2x text-primary"></i>
                                    @elseif($archivo->esPdf())
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                    @else
                                        <i class="fas fa-file fa-2x text-secondary"></i>
                                    @endif
                                </div>
                                <h6 class="card-title">{{ $archivo->nombre_original }}</h6>
                                <p class="card-text text-muted small">
                                    {{ $archivo->tamaño_formateado }}
                                </p>
                                <a href="{{ route('previsitas.download-archivo', $archivo) }}" 
                                   class="btn btn-primary btn-sm" 
                                   target="_blank">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('previsitas.edit', $previsita) }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus"></i> Agregar más archivos
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Archivos de Novedades</h3>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                </div>
                <p class="text-muted">No hay archivos adjuntos</p>
                <a href="{{ route('previsitas.edit', $previsita) }}" class="btn btn-outline-primary">
                    <i class="fas fa-upload"></i> Agregar archivos
                </a>
            </div>
        </div>
        @endif

        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">Acciones</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical btn-block">
                    <a href="{{ route('previsitas.edit', $previsita) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Previsita
                    </a>
                    
                    @if($previsita->drive_link)
                    <a href="{{ $previsita->drive_link }}" class="btn btn-info" target="_blank">
                        <i class="fab fa-google-drive"></i> Abrir Drive
                    </a>
                    @endif
                    
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash"></i> Eliminar Previsita
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta previsita?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                    @if($previsita->novedades_visita_archivo)
                        El archivo PDF adjunto también será eliminado.
                    @endif
                </div>
                <div class="bg-light p-3 rounded">
                    <strong>Previsita a eliminar:</strong><br>
                    <strong>Lugar:</strong> {{ $previsita->lugar }}<br>
                    <strong>Responsable:</strong> {{ $previsita->responsable }}<br>
                    <strong>Fecha de Visita:</strong> {{ $previsita->fecha_visita->format('d/m/Y') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form action="{{ route('previsitas.destroy', $previsita) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .custom-card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 1rem;
    }
    
    .info-box {
        margin-bottom: 1rem;
    }
    
    .info-box-number {
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 0.5rem;
    }
    
    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    
    .fa-3x {
        font-size: 3rem;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@stop