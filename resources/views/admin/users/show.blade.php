@extends('adminlte::page')

@section('title', 'Detalles de Usuario')

@section('content')
<div class="card">
    <div class="card-header" style="background-color: #364E76;">
        <h3 class="card-title text-white">Detalles del Usuario: {{ $user->name }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <table class="table">
                    <tr>
                        <th style="width: 200px;">Nombre:</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Cargo:</th>
                        <td>{{ $user->cargo ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            <span class="badge badge-{{ $user->active ? 'success' : 'danger' }}">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Roles:</th>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-info mr-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4 text-center">
                <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('img/default-avatar.png') }}" 
                     class="img-circle elevation-2" alt="User Avatar" style="width: 150px; height: 150px; object-fit: cover;">
            </div>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
        </div>
    </div>
</div>
@stop
