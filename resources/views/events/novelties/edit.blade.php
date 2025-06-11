@extends('adminlte::page')

@section('title', 'Editar Novedad')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Editar Novedad</h1>
        <a href="{{ route('event.novelties.index', $event) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Novedades
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Información del Evento</h3>
            </div>
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $event->event_name }}</h3>
                <p class="text-muted text-center">{{ $event->consecutive }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-calendar"></i> Fecha</b> <a class="float-right">{{ $event->service_date->format('d/m/Y') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-clock"></i> Hora</b> <a class="float-right">{{ $event->service_date->format('h:i A') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-map-marker-alt"></i> Ubicación</b> <a class="float-right">{{ $event->location }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Editar Novedad</h3>
                <div class="card-tools">
                    <span class="badge badge-info">
                        Creado por: {{ $novelty->user->name }} - {{ $novelty->created_at->format('d/m/Y h:i A') }}
                    </span>
                </div>
            </div>
            <form action="{{ route('event.novelties.update', ['event' => $event, 'novelty' => $novelty]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="observation">Novedad / Observación</label>
                        <textarea name="observation" id="observation" rows="5" class="form-control @error('observation') is-invalid @enderror" placeholder="Ingrese la novedad u observación relacionada con el evento...">{{ old('observation', $novelty->observation) }}</textarea>
                        @error('observation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Novedad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    textarea {
        min-height: 150px;
    }
</style>
@stop