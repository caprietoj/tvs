@extends('adminlte::page')

@section('title', 'Nueva Solicitud de Mantenimiento')

@section('content_header')
    <h1 class="text-primary">Nueva Solicitud de Mantenimiento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('maintenance.store') }}">
                @csrf

                <div class="form-group">
                    <label for="request_type">Tipo de Solicitud</label>
                    <select class="form-control @error('request_type') is-invalid @enderror" 
                            id="request_type" name="request_type" required>
                        <option value="">Seleccione un tipo</option>
                        @foreach($request_types as $value => $label)
                            <option value="{{ $value }}" {{ old('request_type') == $value ? 'selected' : '' }}>
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
                           id="location" name="location" required>
                    @error('location')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" required></textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Cancelar</a>
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

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: '¿Confirmar envío?',
                text: "¿Desea enviar esta solicitud de mantenimiento?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@stop
