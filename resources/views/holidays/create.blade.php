@extends('adminlte::page')

@section('title', 'Crear Día Festivo')

@section('content_header')
    <h1>Crear Nuevo Día Festivo</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="date">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                           id="date" name="date" value="{{ old('date') }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Ejemplo: "Día de la Independencia", "Suspensión de Labores"</small>
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Información importante:</strong>
                    <p class="mb-0">Al crear un nuevo día festivo, todos los días de ciclo serán recalculados para considerar esta fecha como no lectiva.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        // Scripts adicionales si son necesarios
    </script>
@stop