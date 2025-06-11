@extends('adminlte::page')

@section('title', 'Agregar Video de Ayuda')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h4>Agregar Video de Ayuda</h4>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('help-videos.index') }}">Videos de Ayuda</a></li>
                <li class="breadcrumb-item active">Agregar</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus"></i>
                Nuevo Video de Ayuda
            </h3>
        </div>
        
        <form action="{{ route('help-videos.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="title">
                                <i class="fas fa-heading"></i>
                                Título del Video <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="Ingrese el título del video" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="video_url">
                                <i class="fas fa-link"></i>
                                URL del Video <span class="text-danger">*</span>
                            </label>
                            <input type="url" 
                                   class="form-control @error('video_url') is-invalid @enderror" 
                                   id="video_url" 
                                   name="video_url" 
                                   value="{{ old('video_url') }}" 
                                   placeholder="https://www.youtube.com/watch?v=..." 
                                   required>
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Soporta URLs de YouTube, Vimeo y otros servicios de video.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Descripción
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe brevemente el contenido del video (opcional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cog"></i>
                                    Configuración
                                </h5>
                            </div>
                            <div class="card-body">                                <div class="form-group mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            Video Activo
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Los videos inactivos no se mostrarán a los usuarios.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-info">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Información
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Formatos soportados:</strong></p>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fab fa-youtube text-danger"></i> YouTube</li>
                                    <li><i class="fab fa-vimeo text-primary"></i> Vimeo</li>
                                    <li><i class="fas fa-link"></i> Enlaces directos</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('help-videos.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Video
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .custom-control-label {
            font-weight: 500;
        }
        .bg-light .card-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6;
        }
        .bg-info .card-header {
            background-color: #17a2b8 !important;
            border-bottom: 1px solid #117a8b;
            color: white;
        }
        .bg-info .card-body {
            background-color: #d1ecf1;
        }
    </style>
@stop

@section('js')
    <script>
        // Preview video URL when user pastes/types
        $('#video_url').on('input', function() {
            const url = $(this).val();
            if (url && isValidUrl(url)) {
                // You could add preview functionality here
                console.log('Valid URL:', url);
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    </script>
@stop
