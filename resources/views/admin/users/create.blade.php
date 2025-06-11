@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1 class="text-dark">Gestión de Usuarios</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('users.bulk.import') }}" class="btn btn-success">
                            <i class="fas fa-file-import"></i> Importación Masiva
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" id="createUserForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre Completo</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                            name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                                            data-validation="required length" data-validation-length="min3">
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                            name="email" value="{{ old('email') }}" required autocomplete="email">
                                        <div class="invalid-feedback" id="email-error"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                            name="password" required autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" id="generatePassword">
                                                <i class="fas fa-random"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="form-text text-muted mt-1" id="passwordStrengthText"></small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password-confirm">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input id="password-confirm" type="password" class="form-control" 
                                            name="password_confirmation" required autocomplete="new-password">
                                        <div class="invalid-feedback" id="password-confirm-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Foto de Perfil</label>
                                    <div class="text-center">
                                        <div class="profile-image-container mb-3">
                                            <img id="profile-image-preview" src="{{ asset('img/default-avatar.png') }}" 
                                                class="profile-user-img img-fluid img-circle">
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="profile_photo" name="profile_photo" 
                                                accept="image/*">
                                            <label class="custom-file-label" for="profile_photo">Elegir foto</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="roles">Roles del Usuario</label>
                                    <div class="select-container">
                                        <select name="roles[]" id="roles" class="form-control select2-roles" 
                                                multiple="multiple" required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" 
                                                        data-description="{{ $role->description ?? 'Sin descripción' }}"
                                                        data-icon="{{ $role->name === 'Admin' ? 'fas fa-user-shield' : 
                                                                   ($role->name === 'User' ? 'fas fa-user' : 'fas fa-users') }}"
                                                        data-permissions="{{ $role->permissions->pluck('name')->implode(', ') }}">
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="role-description" class="role-description mt-2"></div>
                                    <div id="permissions-preview" class="permissions-container mt-3 d-none">
                                        <label class="permissions-label">Permisos incluidos:</label>
                                        <div class="permissions-list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary float-right" id="submitBtn">
                                    <i class="fas fa-save mr-1"></i> Crear Usuario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3"></div>
                <h5>Creando usuario...</h5>
                <p>Por favor espere mientras se procesa la solicitud.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    .profile-image-container {
        width: 150px;
        height: 150px;
        margin: 0 auto;
        position: relative;
    }

    .profile-user-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 3px solid #adb5bd;
        padding: 3px;
    }

    .password-strength {
        margin-top: 5px;
    }

    .role-card {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .role-card:hover {
        background-color: #f8f9fa;
    }

    .permissions-list {
        max-height: 150px;
        overflow-y: auto;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .permission-item {
        display: inline-block;
        padding: 2px 8px;
        margin: 2px;
        background-color: #e9ecef;
        border-radius: 3px;
        font-size: 0.875rem;
    }

    /* Select2 Custom Styles */
    .select2-container--bootstrap4 .select2-selection {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 0.375rem 0.75rem;
        min-height: 45px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #364E76;
        border: none;
        color: #fff;
        padding: 3px 8px;
        border-radius: 4px;
        margin: 2px;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap4 .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
        font-weight: bold;
    }

    .select2-container--bootstrap4 .select2-selection__choice__remove:hover {
        color: #f8f9fa;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background-color: #364E76 !important;
        color: #fff;
    }

    .select2-results__option {
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .select2-results__option i {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .role-description {
        padding: 8px 12px;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-size: 0.875rem;
        border-left: 3px solid #364E76;
    }

    .permissions-container {
        background-color: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
    }

    .permissions-label {
        font-weight: 600;
        color: #364E76;
        margin-bottom: 8px;
        font-size: 0.875rem;
    }

    .permissions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .permission-item {
        background-color: #e9ecef;
        color: #364E76;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .permission-item i {
        font-size: 0.75rem;
    }
</style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password-confirm');
    
    togglePassword.addEventListener('click', function() {
        const type = password.type === 'password' ? 'text' : 'password';
        password.type = type;
        passwordConfirm.type = type;
        this.querySelector('i').className = `fas fa-${type === 'password' ? 'eye' : 'eye-slash'}`;
    });

    // Password generator
    const generatePassword = document.getElementById('generatePassword');
    generatePassword.addEventListener('click', function() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let newPassword = "";
        for (let i = 0; i < length; i++) {
            newPassword += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        password.value = newPassword;
        passwordConfirm.value = newPassword;
        checkPasswordStrength(newPassword);
    });

    // Password strength checker
    function checkPasswordStrength(password) {
        const result = zxcvbn(password);
        const progressBar = document.querySelector('.password-strength .progress-bar');
        const strengthText = document.getElementById('passwordStrengthText');
        
        // Update progress bar
        const strength = (result.score + 1) * 20;
        progressBar.style.width = strength + '%';
        
        // Update colors
        const colors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#364E76'];
        progressBar.style.backgroundColor = colors[result.score];
        
        // Update text
        const strengthLabels = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];
        strengthText.textContent = `Fortaleza: ${strengthLabels[result.score]}`;
        if (result.feedback.warning) {
            strengthText.textContent += ` (${result.feedback.warning})`;
        }
    }

    // Real-time password strength checking
    password.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });

    // Profile photo preview
    const profilePhoto = document.getElementById('profile_photo');
    const previewImage = document.getElementById('profile-image-preview');
    
    profilePhoto.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Role permissions preview
    const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
    const permissionsPreview = document.getElementById('permissions-preview');
    const permissionsList = permissionsPreview.querySelector('.permissions-list');
    
    const rolePermissions = {
        // Add your role permissions mapping here
        'Admin': ['Todos los permisos'],
        'User': ['Ver dashboard', 'Ver perfil'],
        // Add more roles and their permissions
    };

    roleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updatePermissionsPreview();
        });
    });

    function updatePermissionsPreview() {
        const selectedRoles = Array.from(roleCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.nextElementSibling.textContent.trim());

        if (selectedRoles.length > 0) {
            permissionsPreview.classList.remove('d-none');
            permissionsList.innerHTML = selectedRoles
                .map(role => rolePermissions[role] || [])
                .flat()
                .map(perm => `<span class="permission-item">${perm}</span>`)
                .join(' ');
        } else {
            permissionsPreview.classList.add('d-none');
        }
    }

    // Form validation
    const form = document.getElementById('createUserForm');
    const emailInput = document.getElementById('email');
    const nameInput = document.getElementById('name');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            $('#loadingModal').modal('show');
            this.submit();
        }
    });

    function validateForm() {
        let isValid = true;
        
        // Validate name
        if (nameInput.value.length < 3) {
            showError(nameInput, 'El nombre debe tener al menos 3 caracteres');
            isValid = false;
        } else {
            clearError(nameInput);
        }
        
        // Validate email
        if (!emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            showError(emailInput, 'Por favor ingrese un correo electrónico válido');
            isValid = false;
        } else {
            clearError(emailInput);
        }
        
        // Validate password match
        if (password.value !== passwordConfirm.value) {
            showError(passwordConfirm, 'Las contraseñas no coinciden');
            isValid = false;
        } else {
            clearError(passwordConfirm);
        }
        
        return isValid;
    }

    function showError(input, message) {
        input.classList.add('is-invalid');
        const errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.textContent = message;
        }
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.textContent = '';
        }
    }
});
</script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2-roles').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione uno o más roles',
                allowClear: true,
                width: '100%',
                templateResult: formatRole,
                templateSelection: formatRoleSelection
            }).on('change', updateRoleInfo);

            function formatRole(role) {
                if (!role.id) return role.text;
                
                var icon = $(role.element).data('icon');
                var $role = $(
                    '<span><i class="' + icon + '"></i> ' + role.text + '</span>'
                );
                
                return $role;
            }

            function formatRoleSelection(role) {
                if (!role.id) return role.text;
                
                var icon = $(role.element).data('icon');
                return $('<span><i class="' + icon + '"></i> ' + role.text + '</span>');
            }

            function updateRoleInfo() {
                const selectedOptions = $('#roles option:selected');
                const permissionsPreview = $('#permissions-preview');
                const permissionsList = permissionsPreview.find('.permissions-list');
                const roleDescription = $('#role-description');
                
                if (selectedOptions.length > 0) {
                    const permissions = new Set();
                    let descriptions = [];
                    
                    selectedOptions.each(function() {
                        const option = $(this);
                        const rolePerms = option.data('permissions').split(', ').filter(p => p);
                        rolePerms.forEach(p => permissions.add(p));
                        
                        const desc = option.data('description');
                        if (desc) {
                            descriptions.push(`<i class="${option.data('icon')}"></i> <strong>${option.text()}</strong>: ${desc}`);
                        }
                    });

                    if (descriptions.length > 0) {
                        roleDescription.html(descriptions.join('<br>')).show();
                    } else {
                        roleDescription.hide();
                    }

                    if (permissions.size > 0) {
                        permissionsList.html(Array.from(permissions)
                            .map(perm => `<span class="permission-item"><i class="fas fa-check-circle"></i>${perm}</span>`)
                            .join(''));
                        permissionsPreview.removeClass('d-none');
                    } else {
                        permissionsPreview.addClass('d-none');
                    }
                } else {
                    roleDescription.hide();
                    permissionsPreview.addClass('d-none');
                }
            }
        });
    </script>
@stop