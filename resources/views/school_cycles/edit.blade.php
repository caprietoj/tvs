@extends('adminlte::page')

@section('title', 'Editar Ciclo Escolar')

@section('content_header')
    <h1>Editar Ciclo Escolar</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('school-cycles.update', $schoolCycle) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre del Ciclo Escolar <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $schoolCycle->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $schoolCycle->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active" name="active"
                               {{ old('active', $schoolCycle->active) ? 'checked' : '' }}
                               {{ $schoolCycle->active ? 'disabled' : '' }}>
                        <label class="custom-control-label" for="active">
                            Ciclo Escolar Activo
                            @if($schoolCycle->active)
                                <span class="text-success">(Este ciclo está activo actualmente)</span>
                            @endif
                        </label>
                        <small class="form-text text-muted ml-4">Al activar este ciclo, cualquier otro ciclo activo se desactivará automáticamente.</small>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Información importante:</strong>
                    <p class="mb-0">La fecha de inicio y la longitud del ciclo no se pueden modificar una vez creado el ciclo, 
                    ya que esto podría afectar a las reservas existentes.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('school-cycles.index') }}" class="btn btn-secondary">
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