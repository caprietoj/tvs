@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <h1>Editar Rol</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.update', $role['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nombre del Rol</label>
                    <input type="text" name="name" class="form-control" value="{{ $role['name'] }}" required>
                </div>
                <!-- Replace text input with a multiple select for permissions -->
                <div class="form-group">
                    <label>Permisos</label>
                    <select name="permissions[]" class="form-control" multiple>
                        @foreach($availablePermissions as $perm)
                        <option value="{{ $perm }}" {{ in_array($perm, $rolePermissions) ? 'selected' : '' }}>
                            {{ $perm }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-info">Actualizar</button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Optional: Initialize Select2 for better UI
            $('select[name="permissions[]"]').select2();
        });
    </script>
@stop
