@extends('adminlte::page')

@section('title', 'Editar Solicitud de Mantenimiento')

@section('content_header')
    <h1 class="text-primary">Editar Solicitud #{{ $maintenance->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('maintenance.update', $maintenance) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="request_type">Tipo de Solicitud</label>
                    <select class="form-control @error('request_type') is-invalid @enderror" 
                            id="request_type" name="request_type" required>
                        @foreach($request_types as $value => $label)
                            <option value="{{ $value }}" {{ $maintenance->request_type === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('request_type')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="location">Ubicación</label>
                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                           id="location" name="location" value="{{ $maintenance->location }}" required>
                    @error('location')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" required>{{ $maintenance->description }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                @if(auth()->user()->hasRole('admin'))
                    <div class="form-group">
                        <label for="status">Estado de la Solicitud</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                            <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="rejected" {{ $maintenance->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="technician_id">Técnico Asignado</label>
                        <select name="technician_id" id="technician_id" class="form-control @error('technician_id') is-invalid @enderror">
                            <option value="">Seleccionar Técnico</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ $maintenance->technician_id == $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('technician_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar Solicitud</button>
                    <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        :root {
            --primary: #364E76;
        }
        
        .text-primary {
            color: var(--primary) !important;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #2a3d5d;
            border-color: #2a3d5d;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
    </style>
@stop
