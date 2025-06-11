@extends('adminlte::page')

@section('title', 'Videos de Ayuda')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h4>Videos de Ayuda</h4>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Videos de Ayuda</li>
            </ol>
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

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title">
                        <i class="fas fa-video"></i>
                        Lista de Videos de Ayuda
                    </h3>
                </div>                <div class="col-md-6 text-right">
                    @can('create', App\Models\HelpVideo::class)
                        <a href="{{ route('help-videos.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Video
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="card-body">
            @if($videos->count() > 0)
                <div class="row">
                    @foreach($videos as $video)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                @if($video->thumbnail)
                                    <img src="{{ $video->thumbnail }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $video->title }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-video fa-3x text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $video->title }}</h5>
                                    @if($video->description)
                                        <p class="card-text">{{ Str::limit($video->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="mt-auto">
                                        <div class="mb-2">
                                            <span class="badge badge-{{ $video->is_active ? 'success' : 'secondary' }}">
                                                {{ $video->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                          <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('help-videos.show', $video) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            @can('update', $video)
                                                <a href="{{ route('help-videos.edit', $video) }}" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            @endcan
                                            @can('delete', $video)
                                                <form action="{{ route('help-videos.destroy', $video) }}" method="POST" class="d-inline" 
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
                                
                                <div class="card-footer text-muted">
                                    <small>Creado: {{ $video->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else                <div class="text-center py-5">
                    <i class="fas fa-video fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay videos de ayuda disponibles</h5>
                    <p class="text-muted">Agrega el primer video de ayuda para comenzar.</p>
                    @can('create', App\Models\HelpVideo::class)
                        <a href="{{ route('help-videos.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Primer Video
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-img-top {
            transition: transform 0.2s;
        }
        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        .btn-group-sm .btn {
            font-size: 0.75rem;
        }
    </style>
@stop

@section('js')
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop
