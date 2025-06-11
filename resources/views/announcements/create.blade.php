@extends('adminlte::page')

@section('title', 'Crear Aviso')

@section('plugins.Summernote', true)

@section('content_header')
    <h1>Crear Nuevo Aviso</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('announcements.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="title">Título</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" required>
                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="content">Contenido</label>
                <textarea id="summernote" name="content" class="form-control @error('content') is-invalid @enderror">
                    {{ old('content') }}
                </textarea>
                @error('content')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="expiry_date">Fecha de Expiración</label>
                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                       value="{{ old('expiry_date') }}">
                @error('expiry_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="priority">Prioridad (0-10)</label>
                <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" 
                       value="{{ old('priority', 0) }}" min="0" max="10" required>
                @error('priority')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('vendor/summernote/summernote-bs4.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('#summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'help']]
        ]
    });
});
</script>
@stop
