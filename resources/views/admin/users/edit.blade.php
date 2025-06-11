@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content')
<div class="card">
    <div class="card-header" style="background-color: #364E76;">
        <h3 class="card-title text-white">Editar Usuario: {{ $user->name }}</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="cargo" class="form-label">Cargo</label>
                        <select name="cargo" id="cargo" class="form-control @error('cargo') is-invalid @enderror">
                            <option value="">Seleccione un cargo</option>
                            @php
                                $cargos = [
                                    'Profesor',
                                    'Asistente',
                                    'Tecnico',
                                    'Administrativo',
                                    'Auxiliar',
                                    'Aprendiz',
                                    'Rector(a)',
                                    'Sub Rector(a)'
                                ];
                            @endphp
                            @foreach($cargos as $cargo_option)
                                <option value="{{ $cargo_option }}" {{ old('cargo', $user->cargo) == $cargo_option ? 'selected' : '' }}>
                                    {{ $cargo_option }}
                                </option>
                            @endforeach
                        </select>
                        @error('cargo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Roles</label>
                        <div class="role-cards">
                            @foreach($roles as $role)
                                <div class="role-card">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                           id="role_{{ $role->id }}" class="role-checkbox"
                                           {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                    <label for="role_{{ $role->id }}" class="role-label">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>{{ $role->name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Avatar</label>
                        <div class="avatar-wrapper">
                            <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('img/default-avatar.png') }}" 
                                 class="avatar-preview" id="avatar-preview" alt="Avatar">
                            <div class="avatar-edit">
                                <label for="avatar" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Cambiar Avatar
                                </label>
                                <input type="file" name="avatar" id="avatar" class="avatar-input" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estado de la Cuenta</label>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                                   {{ $user->active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="active">Cuenta Activa</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .form-label {
        color: #364E76;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .role-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .role-card {
        position: relative;
    }

    .role-checkbox {
        display: none;
    }

    .role-label {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .role-label i {
        margin-right: 0.5rem;
        color: #364E76;
    }

    .role-checkbox:checked + .role-label {
        border-color: #364E76;
        background-color: rgba(54, 78, 118, 0.1);
    }

    .avatar-wrapper {
        text-align: center;
        margin-bottom: 1rem;
    }

    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 3px solid #364E76;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .avatar-input {
        display: none;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #364E76;
        border-color: #364E76;
    }

    select.form-control {
        height: auto !important;
        padding: 0.75rem 1rem;
        color: #333;
        background-color: #fff;
        font-size: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 6px;
    }

    select.form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        color: #333;
    }

    select.form-control option {
        padding: 10px;
        color: #333;
        background-color: #fff;
    }

    select.form-control option:hover,
    select.form-control option:checked {
        background-color: #364E76;
        color: #fff;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Preview avatar image before upload
    $('#avatar').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form submission
    $('form').on('submit', function() {
        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: '{{ session("success") }}',
        confirmButtonColor: '#364E76'
    });
</script>
@endif
@stop