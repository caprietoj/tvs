@extends('adminlte::page')

@section('title', $helpVideo->title)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h4>{{ $helpVideo->title }}</h4>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('help-videos.index') }}">Videos de Ayuda</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($helpVideo->title, 30) }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-play-circle"></i>
                        {{ $helpVideo->title }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $helpVideo->is_active ? 'success' : 'secondary' }}">
                            {{ $helpVideo->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" 
                                src="{{ $helpVideo->embed_url }}" 
                                title="{{ $helpVideo->title }}"
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>

                @if($helpVideo->description)
                <div class="card-footer">
                    <h5><i class="fas fa-info-circle"></i> Descripción</h5>
                    <p class="mb-0">{{ $helpVideo->description }}</p>
                </div>
                @endif
            </div>

            <!-- Botones de acción -->
            <div class="card">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        @can('update', $helpVideo)
                            <a href="{{ route('help-videos.edit', $helpVideo) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        @endcan
                          <a href="{{ $helpVideo->video_url }}" target="_blank" class="btn btn-info">
                            <i class="fas fa-external-link-alt"></i> Ver en Plataforma Original
                        </a>
                        
                        <a href="{{ route('help-videos.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Volver a la Lista
                        </a>
                        
                        @can('delete', $helpVideo)
                            <form action="{{ route('help-videos.destroy', $helpVideo) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este video?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Información del video -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info"></i>
                        Información del Video
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                <span class="badge badge-{{ $helpVideo->is_active ? 'success' : 'secondary' }}">
                                    {{ $helpVideo->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de creación:</strong></td>
                            <td>{{ $helpVideo->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($helpVideo->updated_at != $helpVideo->created_at)
                        <tr>
                            <td><strong>Última actualización:</strong></td>
                            <td>{{ $helpVideo->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif                        <tr>
                            <td><strong>Plataforma:</strong></td>
                            <td>{{ $helpVideo->getVideoProvider() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Otros videos disponibles -->
            @php
                $otherVideos = App\Models\HelpVideo::where('id', '!=', $helpVideo->id)
                    ->where('is_active', true)
                    ->latest()
                    ->limit(3)
                    ->get();
            @endphp

            @if($otherVideos->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-video"></i>
                        Otros Videos de Ayuda
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($otherVideos as $video)
                        <div class="media mb-3">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" class="media-object mr-3" style="width: 80px; height: 60px; object-fit: cover;" alt="{{ $video->title }}">
                            @else
                                <div class="media-object mr-3 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                    <i class="fas fa-video text-muted"></i>
                                </div>
                            @endif
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">
                                    <a href="{{ route('help-videos.show', $video) }}" class="text-decoration-none">
                                        {{ Str::limit($video->title, 40) }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ $video->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .embed-responsive-16by9 {
            min-height: 400px;
        }
        
        .media-object {
            border-radius: 4px;
        }
        
        .btn-group .btn {
            margin-right: 5px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
@stop

@section('js')
    <script>
        // Add any video-specific JavaScript here
        console.log('Video loaded:', '{{ $helpVideo->title }}');
    </script>
@stop

@php
    function getVideoProvider($url) {
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'YouTube';
        } elseif (strpos($url, 'vimeo.com') !== false) {
            return 'Vimeo';
        } else {
            return 'Otro';
        }
    }
@endphp
