@extends('adminlte::page')

@section('title', 'Editar Reserva')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Editar Reserva #{{ $reservation->id }}</h1>
        <div>
            <a href="{{ route('space-reservations.show', $reservation) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('space-reservations.update', $reservation) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="space_id">Espacio <span class="text-danger">*</span></label>
                                <select name="space_id" id="space_id" class="form-control @error('space_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un espacio</option>
                                    @foreach ($spaces as $space)
                                        <option value="{{ $space->id }}" {{ (old('space_id', $reservation->space_id) == $space->id) ? 'selected' : '' }}>
                                            {{ $space->name }} @if($space->location) ({{ $space->location }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('space_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="date">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" 
                                    name="date" value="{{ old('date', $reservation->date->format('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small id="date_help" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="start_time">Hora de Inicio <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                    id="start_time" name="start_time" value="{{ old('start_time', substr($reservation->start_time, 0, 5)) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="end_time">Hora de Fin <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                    id="end_time" name="end_time" value="{{ old('end_time', substr($reservation->end_time, 0, 5)) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="purpose">Propósito de la Reserva <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" 
                                name="purpose" rows="3" required>{{ old('purpose', $reservation->purpose) }}</textarea>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notas Adicionales</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" 
                                name="notes" rows="2">{{ old('notes', $reservation->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sección de acompañamiento de bibliotecóloga (solo para bibliotecas) -->
                        <div id="librarian-assistance-section" class="form-group {{ $reservation->space->is_library ? '' : 'd-none' }}">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-graduate"></i> Acompañamiento de Bibliotecóloga
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="requires_librarian" name="requires_librarian" value="1" {{ old('requires_librarian', $reservation->requires_librarian) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_librarian">
                                            Solicitar acompañamiento de la bibliotecóloga durante esta reserva
                                        </label>
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Si necesita orientación o apoyo para utilizar los recursos de la biblioteca, 
                                        active esta opción para solicitar el acompañamiento de la bibliotecóloga durante su reserva.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Reserva
                            </button>
                            <a href="{{ route('space-reservations.show', $reservation) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>

                        <div id="availability-info" class="mt-4 d-none">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title">
                                        <i class="fas fa-info-circle"></i> Información de Disponibilidad
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div id="availability-loading" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando...</span>
                                        </div>
                                        <p>Verificando disponibilidad...</p>
                                    </div>
                                    <div id="availability-results" class="d-none">
                                        <h5>Reservas Existentes para este Día:</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Horario</th>
                                                        <th>Usuario</th>
                                                        <th>Propósito</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="reservations-list">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="no-availability-results" class="d-none">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-circle"></i> No hay otras reservas existentes para la fecha seleccionada.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const spaceSelect = document.getElementById('space_id');
        const dateInput = document.getElementById('date_help');
        const dateField = document.getElementById('date');
        const startTimeField = document.getElementById('start_time');
        const endTimeField = document.getElementById('end_time');
        const availabilityInfo = document.getElementById('availability-info');
        const availabilityLoading = document.getElementById('availability-loading');
        const availabilityResults = document.getElementById('availability-results');
        const noAvailabilityResults = document.getElementById('no-availability-results');
        const reservationsList = document.getElementById('reservations-list');
        const currentReservationId = {{ $reservation->id }};
        
        // Set minimum date to today
        if (new Date(dateField.value) >= new Date()) {
            dateField.min = new Date().toISOString().split('T')[0];
        }
        
        // Check availability when space or date changes
        function checkAvailability() {
            const spaceId = spaceSelect.value;
            const date = dateField.value;
            
            if (!spaceId || !date) {
                availabilityInfo.classList.add('d-none');
                return;
            }
            
            availabilityInfo.classList.remove('d-none');
            availabilityLoading.classList.remove('d-none');
            availabilityResults.classList.add('d-none');
            noAvailabilityResults.classList.add('d-none');
            
            fetch(`{{ url('space-reservations/check-availability') }}/${spaceId}/${date}?exclude_id=${currentReservationId}`)
                .then(response => response.json())
                .then(data => {
                    availabilityLoading.classList.add('d-none');
                    
                    if (!data.available) {
                        // Space is not available on this date
                        dateField.setCustomValidity(data.message);
                        dateField.reportValidity();
                        dateInput.textContent = data.message;
                        dateInput.classList.add('text-danger');
                        return;
                    }
                    
                    // Space is available, clear any validation errors
                    dateField.setCustomValidity('');
                    dateInput.textContent = data.cycle_day ? `Día del ciclo: ${data.cycle_day}` : '';
                    dateInput.classList.remove('text-danger');
                    
                    // Show existing reservations if any
                    if (data.reservations && data.reservations.length > 0) {
                        availabilityResults.classList.remove('d-none');
                        reservationsList.innerHTML = '';
                        
                        data.reservations.forEach(res => {
                            let statusBadge = '';
                            if (res.status === 'approved') {
                                statusBadge = '<span class="badge badge-success">Aprobada</span>';
                            } else if (res.status === 'pending') {
                                statusBadge = '<span class="badge badge-warning">Pendiente</span>';
                            } else if (res.status === 'rejected') {
                                statusBadge = '<span class="badge badge-danger">Rechazada</span>';
                            } else {
                                statusBadge = '<span class="badge badge-secondary">Cancelada</span>';
                            }
                            
                            reservationsList.innerHTML += `
                                <tr>
                                    <td>${res.start} - ${res.end}</td>
                                    <td>${res.user}</td>
                                    <td>${res.purpose}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                        });
                    } else {
                        noAvailabilityResults.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                    availabilityLoading.classList.add('d-none');
                    dateInput.textContent = 'Error al verificar disponibilidad';
                    dateInput.classList.add('text-danger');
                });
        }
        
        spaceSelect.addEventListener('change', checkAvailability);
        dateField.addEventListener('change', checkAvailability);
        
        // Validate end time is after start time
        function validateTimes() {
            const startTime = startTimeField.value;
            const endTime = endTimeField.value;
            
            if (startTime && endTime && startTime >= endTime) {
                endTimeField.setCustomValidity('La hora de fin debe ser posterior a la hora de inicio');
            } else {
                endTimeField.setCustomValidity('');
            }
        }
        
        // Función para mostrar/ocultar sección de bibliotecóloga según el espacio seleccionado
        function toggleLibrarianSection() {
            const spaceId = spaceSelect.value;
            const librarianSection = document.getElementById('librarian-assistance-section');
            
            if (!spaceId) {
                librarianSection.classList.add('d-none');
                return;
            }
            
            // Hacer una petición para obtener los detalles del espacio
            fetch(`{{ url('spaces') }}/${spaceId}/details`)
                .then(response => response.json())
                .then(space => {
                    if (space.is_library) {
                        librarianSection.classList.remove('d-none');
                    } else {
                        librarianSection.classList.add('d-none');
                        // Deseleccionar el checkbox si cambia a un espacio que no es biblioteca
                        document.getElementById('requires_librarian').checked = false;
                    }
                })
                .catch(error => {
                    console.error('Error al cargar detalles del espacio:', error);
                    librarianSection.classList.add('d-none');
                });
        }
        
        startTimeField.addEventListener('change', validateTimes);
        endTimeField.addEventListener('change', validateTimes);
        spaceSelect.addEventListener('change', toggleLibrarianSection);
        
        // Initial check
        checkAvailability();
        toggleLibrarianSection();
    });
</script>
@endsection