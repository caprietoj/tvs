@extends('adminlte::page')

@section('title', 'Detalles de Solicitud')

@section('content_header')
    <h1 class="text-primary">Detalles de Solicitud #{{ $maintenance->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Información de la Solicitud</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Tipo:</dt>
                        <dd class="col-sm-8">{{ $request_types[$maintenance->request_type] ?? ucfirst(str_replace('_', ' ', $maintenance->request_type)) }}</dd>
                        
                        <dt class="col-sm-4">Ubicación:</dt>
                        <dd class="col-sm-8">{{ $maintenance->location }}</dd>
                        
                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">{{ $maintenance->description }}</dd>
                        
                        <dt class="col-sm-4">Prioridad:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-{{ $maintenance->priority == 'high' ? 'danger' : ($maintenance->priority == 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($maintenance->priority) }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-{{ $maintenance->status == 'pending' ? 'warning' : ($maintenance->status == 'in_progress' ? 'info' : ($maintenance->status == 'completed' ? 'success' : 'danger')) }}">
                                {{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                            </span>
                        </dd>
                    </dl>
                </div>
                
                <div class="col-md-6">
                    @if(auth()->user()->hasRole('admin'))
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Asignar Técnico</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('maintenance.assign-technician', $maintenance) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-group">
                                        <select name="technician_id" class="form-control" required>
                                            <option value="">Seleccionar Técnico</option>
                                            @foreach($technicians as $technician)
                                                <option value="{{ $technician->id }}" {{ $maintenance->technician_id == $technician->id ? 'selected' : '' }}>
                                                    {{ $technician->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Asignar Técnico</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop
