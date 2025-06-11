@extends('adminlte::page')

@section('title', 'Avisos')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Avisos Importantes</h1>
        <a href="{{ route('announcements.create') }}" class="btn btn-primary">Nuevo Aviso</a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Fecha de Expiración</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($announcements as $announcement)
                    <tr>
                        <td>{{ $announcement->title }}</td>
                        <td>{{ $announcement->priority }}</td>
                        <td>
                            @if($announcement->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ $announcement->expiry_date ? $announcement->expiry_date->format('d/m/Y') : 'Sin expiración' }}</td>
                        <td>
                            <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirm('¿Está seguro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<style>
    .table th { background-color: #364E76; color: white; }
    .btn-primary { background-color: #364E76; border-color: #364E76; }
    .btn-primary:hover { background-color: #2a3d5d; border-color: #2a3d5d; }
</style>
@stop
