@extends('adminlte::page')

@section('title', 'Editar Aviso')

@section('content_header')
    <h1>Editar Aviso</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="title">Título</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title', $announcement->title) }}" required>
                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="content">Contenido</label>
                <textarea name="content" rows="3" class="form-control @error('content') is-invalid @enderror" 
                          required>{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Imagen (Opcional)</label>
                @if($announcement->image_path)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                             alt="Imagen actual" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                @endif
                <input type="file" name="image" class="form-control-file @error('image') is-invalid @enderror">
                <small class="form-text text-muted">Deje este campo vacío si no desea cambiar la imagen.</small>
                @error('image')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="expiry_date">Fecha de Expiración</label>
                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                       value="{{ old('expiry_date', $announcement->expiry_date?->format('Y-m-d')) }}">
                @error('expiry_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="priority">Prioridad (0-10)</label>
                <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" 
                       value="{{ old('priority', $announcement->priority) }}" min="0" max="10" required>
                @error('priority')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                           value="1" {{ $announcement->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Aviso Activo</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .btn-primary { background-color: #364E76; border-color: #364E76; }
    .btn-primary:hover { background-color: #2a3d5d; border-color: #2a3d5d; }
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #364E76;
        border-color: #364E76;
    }
</style>
@stop
