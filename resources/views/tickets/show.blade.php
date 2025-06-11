{{-- resources/views/tickets/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle del Ticket')

@section('content_header')
    <h1>Detalle del Ticket</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $ticket->titulo }}</h3>
    </div>
    <div class="card-body">
        <p><strong>Descripción:</strong> {{ $ticket->descripcion }}</p>
        <p><strong>Estado:</strong> {{ $ticket->estado }}</p>
        <p><strong>Prioridad:</strong> {{ $ticket->prioridad }}</p>
        <p><strong>Tipo de Requerimiento:</strong> {{ $ticket->tipo_requerimiento }}</p>
        <p><strong>Creado por:</strong> {{ $ticket->user->name }}</p>
        <p><strong>Fecha de Creación:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Última Actualización:</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
    </div>
    <div class="card-footer">
        <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Regresar</a>
    </div>
</div>
@stop
