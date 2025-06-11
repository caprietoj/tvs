@extends('adminlte::page')

@section('title', 'Nueva Reserva de Espacio')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Nueva Reserva de Espacio</h1>
        <div>
            <a href="{{ route('space-reservations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a mis reservas
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
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

                    <form action="{{ route('space-reservations.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="space_id">Espacio <span class="text-danger">*</span></label>
                                <select name="space_id" id="space_id" class="form-control @error('space_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un espacio</option>
                                    @foreach ($spaces as $space)
                                        <option value="{{ $space->id }}" {{ old('space_id') == $space->id ? 'selected' : '' }}>
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
                                    name="date" value="{{ old('date', request('date', now()->format('Y-m-d'))) }}" required>
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
                                    id="start_time" name="start_time" value="{{ old('start_time', '08:00') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="end_time">Hora de Fin <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                    id="end_time" name="end_time" value="{{ old('end_time', '09:00') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="purpose">Propósito de la Reserva <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" 
                                name="purpose" rows="3" required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notas Adicionales</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" 
                                name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Sección para solicitar acompañamiento de bibliotecóloga (solo visible para bibliotecas) -->
                        <div id="librarian-assistance-section" class="form-group d-none">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-graduate"></i> Acompañamiento de Bibliotecóloga
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="requires_librarian" 
                                            name="requires_librarian" value="1" {{ old('requires_librarian') ? 'checked' : '' }}>
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
                        
                        <!-- Sección de implementos para préstamo -->
                        <div id="space-items-section" class="form-group d-none">
                            <label>Implementos para Préstamo</label>
                            <div class="card">
                                <div class="card-body">
                                    <div id="space-items-loading" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando...</span>
                                        </div>
                                        <p>Cargando implementos disponibles...</p>
                                    </div>
                                    <div id="space-items-content" class="d-none">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            Seleccione los implementos que necesita para esta reserva.
                                        </div>
                                        <div id="space-items-list">
                                            <!-- Aquí se cargarán dinámicamente los implementos disponibles -->
                                        </div>
                                    </div>
                                    <div id="no-space-items" class="d-none">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Este espacio no tiene implementos disponibles para préstamo.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de habilidades a trabajar -->
                        <div id="space-skills-section" class="form-group d-none">
                            <label>Habilidades a Trabajar</label>
                            <div class="card">
                                <div class="card-body">
                                    <div id="space-skills-loading" class="text-center d-none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando...</span>
                                        </div>
                                        <p>Cargando habilidades disponibles...</p>
                                    </div>
                                    <div id="space-skills-content" class="">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            Seleccione las habilidades que trabajará durante la reserva.
                                        </div>
                                        <div id="space-skills-list"></div>
                                    </div>
                                    <div id="no-space-skills" class="d-none">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Este espacio no tiene habilidades configuradas.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Reserva
                            </button>
                            <a href="{{ route('space-reservations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>

                        <div id="availability-info" class="mt-4 d-none">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-calendar-check"></i> Información de Disponibilidad
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool text-white" data-toggle="collapse" data-target="#availabilityInfoBody" aria-expanded="true">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body collapse show" id="availabilityInfoBody">
                                    <div id="availability-loading" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Verificando disponibilidad...</span>
                                        </div>
                                        <p class="mt-2">Verificando disponibilidad...</p>
                                    </div>
                                    
                                    <!-- Visualización de horario -->
                                    <div id="time-schedule-view" class="d-none mb-4">
                                        <h5><i class="fas fa-clock"></i> Vista de Horarios del Día</h5>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> El siguiente diagrama muestra la ocupación del espacio para el día seleccionado:
                                        </div>
                                        <div class="time-schedule-container">
                                            <div class="time-labels">
                                                <div>07:00</div>
                                                <div>08:00</div>
                                                <div>09:00</div>
                                                <div>10:00</div>
                                                <div>11:00</div>
                                                <div>12:00</div>
                                                <div>13:00</div>
                                                <div>14:00</div>
                                                <div>15:00</div>
                                                <div>16:00</div>
                                                <div>17:00</div>
                                                <div>18:00</div>
                                            </div>
                                            <div class="time-grid" id="time-grid">
                                                <!-- Aquí se cargarán dinámicamente los bloques de horario -->
                                            </div>
                                        </div>
                                        <div class="time-schedule-legend mt-2">
                                            <span class="legend-item"><span class="legend-color bg-success"></span> Disponible</span>
                                            <span class="legend-item"><span class="legend-color bg-danger"></span> Bloqueado</span>
                                            <span class="legend-item"><span class="legend-color bg-warning"></span> Reservado</span>
                                            <span class="legend-item"><span class="legend-color bg-info"></span> Su selección</span>
                                        </div>
                                    </div>
                                    
                                    <div id="availability-results" class="d-none">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5><i class="fas fa-list"></i> Reservas Existentes</h5>
                                            <span id="reservation-count" class="badge badge-primary">0 reservas</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 23%">Horario</th>
                                                        <th style="width: 25%">Usuario</th>
                                                        <th style="width: 40%">Propósito</th>
                                                        <th style="width: 12%">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="reservations-list">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="no-availability-results" class="d-none">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i> No hay reservas existentes para la fecha seleccionada.
                                        </div>
                                    </div>
                                    
                                    <!-- Sección para mostrar bloqueos semanales con mejoras visuales -->
                                    <div id="weekly-blocks-section" class="mt-4 d-none">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5><i class="fas fa-ban"></i> Bloqueos Semanales</h5>
                                            <span id="block-count" class="badge badge-danger">0 bloqueos</span>
                                        </div>
                                        <div id="weekly-blocks-content">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th style="width: 20%">Horario</th>
                                                            <th style="width: 50%">Motivo</th>
                                                            <th style="width: 15%">Estado</th>                                            @if(auth()->user()->hasRole(['admin','reservation_manager','admin-espacios']))
                                                <th style="width: 15%">Acciones</th>
                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody id="weekly-blocks-list">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="no-weekly-blocks" class="d-none">
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle"></i> No hay bloqueos semanales para este día.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información del Espacio
                    </h3>
                </div>
                <div class="card-body" id="space-details">
                    <div class="text-center text-muted">
                        <i class="fas fa-building fa-4x mb-3"></i>
                        <p>Seleccione un espacio para ver su información.</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal para crear excepciones -->
            <div class="modal fade" id="createExceptionModal" tabindex="-1" role="dialog" aria-labelledby="createExceptionModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="createExceptionModalLabel">Crear Excepción de Bloqueo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="exceptionForm">
                                <input type="hidden" id="exception_space_block_id" name="space_block_id">
                                <input type="hidden" id="exception_date" name="exception_date">
                                
                                <div class="form-group">
                                    <label for="exception_reason">Motivo de la excepción:</label>
                                    <input type="text" class="form-control" id="exception_reason" name="reason" 
                                           placeholder="Ej: Evento especial, clase cancelada" required>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    Al crear esta excepción, el espacio estará disponible para ser reservado en este horario 
                                    específicamente para la fecha seleccionada, a pesar del bloqueo semanal.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="saveExceptionBtn">Guardar Excepción</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    /* Estilos para la visualización de horarios */
    .time-schedule-container {
        display: flex;
        margin: 20px 0;
    }
    
    .time-labels {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding-right: 10px;
        font-size: 12px;
        color: #666;
    }
    
    .time-labels div {
        height: 20px;
        line-height: 20px;
    }
    
    .time-grid {
        position: relative;
        flex-grow: 1;
        height: 240px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .time-block {
        position: absolute;
        left: 0;
        width: 100%;
        border-radius: 3px;
        padding: 2px 5px;
        font-size: 12px;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .time-block:hover {
        opacity: 0.8;
        z-index: 100;
    }
    
    .time-block.reserved {
        background-color: #ffc107;
        border: 1px solid #e0a800;
    }
    
    .time-block.blocked {
        background-color: #dc3545;
        border: 1px solid #bd2130;
    }
    
    .time-block.selected {
        background-color: #17a2b8;
        border: 1px solid #138496;
    }
    
    .time-block.exception {
        background-color: #28a745;
        border: 1px solid #1e7e34;
    }
    
    .time-grid-hour-lines {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
    }
    
    .time-grid-hour-line {
        position: absolute;
        width: 100%;
        border-top: 1px dashed #ccc;
    }
    
    .time-schedule-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
    }
    
    .legend-color {
        display: inline-block;
        width: 16px;
        height: 16px;
        margin-right: 5px;
        border-radius: 3px;
    }
    
    /* Estilos para las sugerencias de horarios */
    .time-suggestion-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .time-suggestion-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const spaceSelect = document.getElementById('space_id');
        const dateInput = document.getElementById('date_help');
        const dateField = document.getElementById('date');
        const startTimeField = document.getElementById('start_time');
        const endTimeField = document.getElementById('end_time');
        const spaceDetails = document.getElementById('space-details');
        const availabilityInfo = document.getElementById('availability-info');
        const availabilityLoading = document.getElementById('availability-loading');
        const availabilityResults = document.getElementById('availability-results');
        const noAvailabilityResults = document.getElementById('no-availability-results');
        const reservationsList = document.getElementById('reservations-list');
        const reservationCount = document.getElementById('reservation-count');
        const blockCount = document.getElementById('block-count');
        const timeScheduleView = document.getElementById('time-schedule-view');
        const timeGrid = document.getElementById('time-grid');
        const availableHours = document.getElementById('available-hours');
        const reservedHours = document.getElementById('reserved-hours');
        const timeSuggestions = document.getElementById('time-suggestions');
        const suggestedTimes = document.getElementById('suggested-times');
        
        // Elementos para bloqueos semanales
        const weeklyBlocksSection = document.getElementById('weekly-blocks-section');
        const weeklyBlocksList = document.getElementById('weekly-blocks-list');
        const noWeeklyBlocks = document.getElementById('no-weekly-blocks');
        
        // Elementos para implementos de préstamo
        const spaceItemsSection = document.getElementById('space-items-section');
        const spaceItemsLoading = document.getElementById('space-items-loading');
        const spaceItemsContent = document.getElementById('space-items-content');
        const noSpaceItems = document.getElementById('no-space-items');
        const spaceItemsList = document.getElementById('space-items-list');
        
        // Elementos para habilidades a trabajar
        const spaceSkillsSection = document.getElementById('space-skills-section');
        const spaceSkillsLoading = document.getElementById('space-skills-loading');
        const spaceSkillsContent = document.getElementById('space-skills-content');
        const noSpaceSkills = document.getElementById('no-space-skills');
        const spaceSkillsList = document.getElementById('space-skills-list');
        
        // Set minimum date to today
        dateField.min = new Date().toISOString().split('T')[0];
        
        // Función para convertir formato de hora a minutos desde las 07:00
        function timeToMinutes(time) {
            const [hours, minutes] = time.split(':').map(Number);
            return (hours * 60 + minutes) - (7 * 60); // Restar 7:00 para empezar desde 0
        }
        
        // Función para generar la visualización del horario
        function generateTimeGrid(data) {
            // Limpiar el grid
            timeGrid.innerHTML = '';
            
            // Agregar líneas de hora
            const hourLinesContainer = document.createElement('div');
            hourLinesContainer.className = 'time-grid-hour-lines';
            
            for (let i = 0; i < 12; i++) {
                const hourLine = document.createElement('div');
                hourLine.className = 'time-grid-hour-line';
                hourLine.style.top = `${i * (100 / 11)}%`;
                hourLinesContainer.appendChild(hourLine);
            }
            
            timeGrid.appendChild(hourLinesContainer);
            
            // Agregar bloques de reserva
            if (data.reservations && data.reservations.length > 0) {
                data.reservations.forEach(res => {
                    if (res.status === 'approved' || res.status === 'pending') {
                        const startMinutes = timeToMinutes(res.start);
                        const endMinutes = timeToMinutes(res.end);
                        const duration = endMinutes - startMinutes;
                        const startPercentage = (startMinutes / (11 * 60)) * 100;
                        const heightPercentage = (duration / (11 * 60)) * 100;
                        
                        const reservationBlock = document.createElement('div');
                        reservationBlock.className = 'time-block reserved';
                        reservationBlock.style.top = `${startPercentage}%`;
                        reservationBlock.style.height = `${heightPercentage}%`;
                        reservationBlock.title = `${res.start} - ${res.end}: ${res.purpose} (${res.user})`;
                        reservationBlock.innerHTML = `${res.start} - ${res.end}: ${res.purpose.substring(0, 20)}${res.purpose.length > 20 ? '...' : ''}`;
                        
                        reservationBlock.addEventListener('click', function() {
                            Swal.fire({
                                title: `Reserva: ${res.start} - ${res.end}`,
                                html: `<p><strong>Usuario:</strong> ${res.user}</p>
                                       <p><strong>Propósito:</strong> ${res.purpose}</p>
                                       <p><strong>Estado:</strong> ${res.status === 'approved' ? 'Aprobada' : 'Pendiente'}</p>`,
                                icon: 'info'
                            });
                        });
                        
                        timeGrid.appendChild(reservationBlock);
                    }
                });
            }
            
            // Agregar bloques de bloqueo semanal
            if (data.weekly_blocks && data.weekly_blocks.length > 0) {
                data.weekly_blocks.forEach(block => {
                    const startMinutes = timeToMinutes(block.start);
                    const endMinutes = timeToMinutes(block.end);
                    const duration = endMinutes - startMinutes;
                    const startPercentage = (startMinutes / (11 * 60)) * 100;
                    const heightPercentage = (duration / (11 * 60)) * 100;
                    
                    const blockEl = document.createElement('div');
                    blockEl.className = block.has_exception ? 'time-block exception' : 'time-block blocked';
                    blockEl.style.top = `${startPercentage}%`;
                    blockEl.style.height = `${heightPercentage}%`;
                    blockEl.title = `${block.start} - ${block.end}: ${block.reason} ${block.has_exception ? '(Excepción Aplicada)' : ''}`;
                    blockEl.innerHTML = `${block.start} - ${block.end}: ${block.reason.substring(0, 20)}${block.reason.length > 20 ? '...' : ''}`;
                    
                    blockEl.addEventListener('click', function() {
                        Swal.fire({
                            title: `Bloqueo: ${block.start} - ${block.end}`,
                            html: `<p><strong>Motivo:</strong> ${block.reason}</p>
                                   <p><strong>Estado:</strong> ${block.has_exception ? 'Excepción Aplicada' : 'Bloqueado'}</p>`,
                            icon: 'info'
                        });
                    });
                    
                    timeGrid.appendChild(blockEl);
                });
            }
            
            // Agregar bloque de selección actual
            const currentStartTime = startTimeField.value;
            const currentEndTime = endTimeField.value;
            
            if (currentStartTime && currentEndTime) {
                const startMinutes = timeToMinutes(currentStartTime);
                const endMinutes = timeToMinutes(currentEndTime);
                const duration = endMinutes - startMinutes;
                const startPercentage = (startMinutes / (11 * 60)) * 100;
                const heightPercentage = (duration / (11 * 60)) * 100;
                
                const selectionBlock = document.createElement('div');
                selectionBlock.className = 'time-block selected';
                selectionBlock.style.top = `${startPercentage}%`;
                selectionBlock.style.height = `${heightPercentage}%`;
                selectionBlock.title = `Su selección: ${currentStartTime} - ${currentEndTime}`;
                selectionBlock.innerHTML = `${currentStartTime} - ${currentEndTime}: Su selección`;
                
                timeGrid.appendChild(selectionBlock);
            }
            
            // Mostrar la visualización de horario
            timeScheduleView.classList.remove('d-none');
        }
        
        // Check availability and load space details when space or date changes
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
            weeklyBlocksSection.classList.add('d-none');
            timeScheduleView.classList.add('d-none');
            
            fetch(`{{ url('space-reservations/check-availability') }}/${spaceId}/${date}`)
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
                    
                    // Generar visualización de horario
                    generateTimeGrid(data);
                    
                    // Show existing reservations if any
                    if (data.reservations && data.reservations.length > 0) {
                        const activeReservations = data.reservations.filter(res => 
                            res.status === 'approved' || res.status === 'pending'
                        );
                        
                        if (activeReservations.length > 0) {
                            availabilityResults.classList.remove('d-none');
                            reservationsList.innerHTML = '';
                            reservationCount.textContent = `${activeReservations.length} reserva(s)`;
                            
                            activeReservations.forEach(res => {
                                let statusBadge = '';
                                let rowClass = '';
                                
                                if (res.status === 'approved') {
                                    statusBadge = '<span class="badge badge-success">Aprobada</span>';
                                    rowClass = 'table-success';
                                } else if (res.status === 'pending') {
                                    statusBadge = '<span class="badge badge-warning">Pendiente</span>';
                                    rowClass = 'table-warning';
                                } else if (res.status === 'rejected') {
                                    statusBadge = '<span class="badge badge-danger">Rechazada</span>';
                                    rowClass = 'table-danger';
                                } else {
                                    statusBadge = '<span class="badge badge-secondary">Cancelada</span>';
                                    rowClass = 'table-secondary';
                                }
                                
                                const row = document.createElement('tr');
                                row.className = rowClass;
                                row.innerHTML = `
                                    <td><strong>${res.start} - ${res.end}</strong></td>
                                    <td>${res.user}</td>
                                    <td>${res.purpose}</td>
                                    <td>${statusBadge}</td>
                                `;
                                
                                row.style.cursor = 'pointer';
                                row.title = 'Click para ver detalles';
                                
                                row.addEventListener('click', function() {
                                    Swal.fire({
                                        title: `Reserva: ${res.start} - ${res.end}`,
                                        html: `<p><strong>Usuario:</strong> ${res.user}</p>
                                               <p><strong>Propósito:</strong> ${res.purpose}</p>
                                               <p><strong>Estado:</strong> ${statusBadge}</p>`,
                                        icon: 'info'
                                    });
                                });
                                
                                reservationsList.appendChild(row);
                            });
                        } else {
                            noAvailabilityResults.classList.remove('d-none');
                        }
                    } else {
                        noAvailabilityResults.classList.remove('d-none');
                    }
                    
                    // Mostrar bloqueos semanales si existen
                    weeklyBlocksSection.classList.remove('d-none');
                    
                    if (data.weekly_blocks && data.weekly_blocks.length > 0) {
                        weeklyBlocksList.innerHTML = '';
                        blockCount.textContent = `${data.weekly_blocks.length} bloqueo(s)`;
                        
                        data.weekly_blocks.forEach(block => {
                            let blockRow = document.createElement('tr');
                            
                            // Verificar si el bloqueo tiene una excepción
                            if (block.has_exception) {
                                // Bloqueo con excepción (se puede reservar)
                                blockRow.className = 'table-success';
                                
                                // Verificar si el usuario es administrador
                                const isAdmin = {{ auth()->user()->hasRole(['admin', 'admin-espacios']) ? 'true' : 'false' }};
                                
                                blockRow.innerHTML = `
                                    <td class="text-success"><strong>${block.start} - ${block.end}</strong></td>
                                    <td>${block.reason}</td>
                                    <td><span class="badge badge-success">Excepción</span></td>
                                    ${isAdmin ? '<td></td>' : ''}
                                `;
                            } else {
                                // Bloqueo normal (no se puede reservar)
                                blockRow.className = 'table-danger';
                                
                                // Añadir el botón "Crear Excepción" solo para administradores
                                const isAdmin = {{ auth()->user()->hasRole(['admin', 'admin-espacios']) ? 'true' : 'false' }};
                                const exceptionButton = isAdmin ? 
                                    `<button type="button" class="btn btn-sm btn-outline-success create-exception-btn" 
                                        data-block-id="${block.id}" data-date="${data.date}" data-time="${block.start} - ${block.end}">
                                        <i class="fas fa-unlock"></i> Crear Excepción
                                    </button>` : '';
                                
                                blockRow.innerHTML = `
                                    <td class="text-danger"><strong>${block.start} - ${block.end}</strong></td>
                                    <td>${block.reason}</td>
                                    <td><span class="badge badge-danger">Bloqueado</span></td>
                                    ${isAdmin ? `<td>${exceptionButton}</td>` : ''}
                                `;
                            }
                            
                            // Evento para mostrar detalles al hacer clic
                            blockRow.style.cursor = 'pointer';
                            blockRow.title = 'Click para ver detalles';
                            
                            blockRow.addEventListener('click', function(e) {
                                // No lanzar el evento para el botón
                                if (e.target.closest('.create-exception-btn')) {
                                    return;
                                }
                                
                                Swal.fire({
                                    title: `Bloqueo: ${block.start} - ${block.end}`,
                                    html: `<p><strong>Motivo:</strong> ${block.reason}</p>
                                           <p><strong>Estado:</strong> ${block.has_exception ? 'Excepción Aplicada' : 'Bloqueado'}</p>`,
                                    icon: 'info'
                                });
                            });
                            
                            weeklyBlocksList.appendChild(blockRow);
                        });
                        
                        // Agregar event listeners a los botones de crear excepción
                        document.querySelectorAll('.create-exception-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                createBlockException(
                                    this.dataset.blockId,
                                    this.dataset.date,
                                    this.dataset.time
                                );
                            });
                        });
                        
                        noWeeklyBlocks.classList.add('d-none');
                    } else {
                        noWeeklyBlocks.classList.remove('d-none');
                    }
                    
                    // Mostrar advertencia de validación de horario
                    if (data.weekly_blocks && data.weekly_blocks.length > 0) {
                        // Verificar si los horarios seleccionados entran en conflicto con bloqueos
                        checkTimeConflicts(data.weekly_blocks);
                    }
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                    availabilityLoading.classList.add('d-none');
                    dateInput.textContent = 'Error al verificar disponibilidad';
                    dateInput.classList.add('text-danger');
                });
        }
        
        // Función para crear una excepción de bloqueo
        function createBlockException(blockId, date, time) {
            // Mostrar modal de confirmación con formulario
            Swal.fire({
                title: 'Crear Excepción',
                html: `
                    <p>¿Está seguro que desea crear una excepción para el bloqueo?</p>
                    <p><strong>Fecha:</strong> ${date}</p>
                    <p><strong>Horario:</strong> ${time}</p>
                    <div class="form-group">
                        <label for="exception-reason">Motivo de la excepción:</label>
                        <input type="text" id="exception-reason" class="form-control" 
                                placeholder="Indique el motivo de la excepción">
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Crear Excepción',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    return {
                        reason: document.getElementById('exception-reason').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar indicador de carga
                    Swal.fire({
                        title: 'Procesando',
                        text: 'Creando excepción...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Obtener el token CSRF directamente de la meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Crear el objeto de datos para enviar
                    const requestData = {
                        space_block_id: blockId,
                        exception_date: date,
                        reason: result.value.reason
                    };
                    
                    // Realizar la petición para crear la excepción
                    fetch('{{ route("space-block-exceptions.quick-create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'  // Importante: indicar que esperamos JSON
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            // Si la respuesta no es exitosa, intentamos leer el mensaje de error
                            return response.text().then(text => {
                                try {
                                    // Intentar parsear como JSON
                                    return JSON.parse(text);
                                } catch (e) {
                                    // Si no es JSON, crear un objeto con el texto del error
                                    console.error('Respuesta no válida del servidor:', text);
                                    throw new Error('Error del servidor: Respuesta inesperada');
                                }
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                title: '¡Éxito!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                // Recargar la información de disponibilidad
                                checkAvailability();
                            });
                        } else {
                            // Mostrar mensaje de error
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Error al crear la excepción',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error creating exception:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ha ocurrido un error al procesar la solicitud: ' + error.message,
                            icon: 'error'
                        });
                    });
                }
            });
        }
        
        // Comprueba si el horario seleccionado entra en conflicto con bloqueos semanales
        function checkTimeConflicts(blocks) {
            const startTime = startTimeField.value;
            const endTime = endTimeField.value;
            
            if (!startTime || !endTime) return;
            
            // Verificar cada bloque por conflicto de horario
            for (const block of blocks) {
                if ((startTime < block.end) && (endTime > block.start)) {
                    // Hay conflicto de horario
                    startTimeField.setCustomValidity(`El horario seleccionado entra en conflicto con un bloqueo (${block.start} - ${block.end})`);
                    endTimeField.setCustomValidity(`El horario seleccionado entra en conflicto con un bloqueo (${block.start} - ${block.end})`);
                    
                    // Mostrar advertencia
                    const warningMsg = `<div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle"></i> El horario seleccionado (${startTime}-${endTime}) 
                        entra en conflicto con un bloqueo existente (${block.start} - ${block.end}): ${block.reason}
                    </div>`;
                    
                    // Si ya existe una advertencia, eliminarla primero
                    const existingWarning = document.querySelector('.time-conflict-warning');
                    if (existingWarning) {
                        existingWarning.remove();
                    }
                    
                    // Insertar la advertencia después de los campos de horario
                    const timeFields = document.querySelector('.form-row:nth-of-type(2)');
                    const warningDiv = document.createElement('div');
                    warningDiv.className = 'col-12 time-conflict-warning';
                    warningDiv.innerHTML = warningMsg;
                    timeFields.appendChild(warningDiv);
                    
                    return; // Salir al primer conflicto encontrado
                }
            }
            
            // No hay conflictos, limpiar validaciones
            startTimeField.setCustomValidity('');
            endTimeField.setCustomValidity('');
            
            // Eliminar advertencias si existen
            const existingWarning = document.querySelector('.time-conflict-warning');
            if (existingWarning) {
                existingWarning.remove();
            }
        }
        
        // Load space details and items
        function loadSpaceDetails() {
            const spaceId = spaceSelect.value;
            const librarianAssistanceSection = document.getElementById('librarian-assistance-section');
            
            if (!spaceId) {
                spaceDetails.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-building fa-4x mb-3"></i>
                        <p>Seleccione un espacio para ver su información.</p>
                    </div>
                `;
                
                // Ocultar secciones
                spaceItemsSection.classList.add('d-none');
                spaceSkillsSection.classList.add('d-none');
                librarianAssistanceSection.classList.add('d-none');
                return;
            }
            
            spaceDetails.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando información...</p>
                </div>
            `;
            
            // Mostrar secciones y cargar datos
            spaceItemsSection.classList.remove('d-none');
            spaceSkillsSection.classList.remove('d-none');
            spaceItemsLoading.classList.remove('d-none');
            spaceSkillsLoading.classList.remove('d-none');
            spaceItemsContent.classList.add('d-none');
            spaceSkillsContent.classList.add('d-none');
            noSpaceItems.classList.add('d-none');
            noSpaceSkills.classList.add('d-none');
            librarianAssistanceSection.classList.add('d-none'); // Ocultar inicialmente
            
            fetch(`{{ url('spaces') }}/${spaceId}/details`)
                .then(response => response.json())
                .then(space => {
                    let resourcesList = '';
                    if (space.resources) {
                        const resources = space.resources.split(',').map(item => item.trim());
                        resourcesList = `
                            <ul class="pl-3">
                                ${resources.map(resource => `<li>${resource}</li>`).join('')}
                            </ul>
                        `;
                    } else {
                        resourcesList = '<p class="text-muted">No especificados</p>';
                    }
                    
                    // Construir contenido HTML with image if exists
                    let imageHtml = '';
                    if (space.image_path) {
                        imageHtml = `
                            <div class="text-center mb-3">
                                <img src="{{ asset('') }}${space.image_path}" alt="${space.name}" 
                                     class="img-fluid img-thumbnail" style="max-height: 200px;">
                            </div>
                        `;
                    }
                    
                    spaceDetails.innerHTML = `
                        ${imageHtml}
                        <h4>${space.name}</h4>
                        ${space.location ? `<p><strong>Ubicación:</strong> ${space.location}</p>` : ''}
                        <p><strong>Capacidad:</strong> ${space.capacity || 'No especificada'}</p>
                        
                        ${space.description ? `
                            <div class="mt-3">
                                <strong>Descripción:</strong>
                                <p>${space.description}</p>
                            </div>
                        ` : ''}`;
                    
                    // Procesar implementos para préstamo
                    spaceItemsLoading.classList.add('d-none');
                    
                    if (space.items && space.items.length > 0) {
                        spaceItemsContent.classList.remove('d-none');
                        spaceItemsList.innerHTML = '';
                        
                        // Crear elementos para cada implemento disponible
                        space.items.forEach(item => {
                            if (item.available && item.quantity > 0) {
                                const itemElement = document.createElement('div');
                                itemElement.className = 'form-check mb-2';
                                
                                const itemHtml = `
                                    <input class="form-check-input" type="checkbox" 
                                        id="item-${item.id}" name="items[${item.id}][selected]" value="1">
                                    <label class="form-check-label" for="item-${item.id}">
                                        <strong>${item.name}</strong>
                                        ${item.description ? `- ${item.description}` : ''}
                                        (Disponibles: ${item.quantity})
                                    </label>
                                    <div class="ml-4 mt-1 d-none item-quantity-input" id="quantity-container-${item.id}">
                                        <div class="input-group input-group-sm" style="max-width: 150px;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Cantidad</span>
                                            </div>
                                            <input type="number" class="form-control" 
                                                name="items[${item.id}][quantity]" value="1" 
                                                min="1" max="${item.quantity}" disabled>
                                        </div>
                                    </div>
                                `;
                                
                                itemElement.innerHTML = itemHtml;
                                spaceItemsList.appendChild(itemElement);
                                
                                // Añadir evento para mostrar/ocultar el input de cantidad
                                const checkbox = itemElement.querySelector(`#item-${item.id}`);
                                const quantityContainer = itemElement.querySelector(`#quantity-container-${item.id}`);
                                const quantityInput = quantityContainer.querySelector('input');
                                
                                checkbox.addEventListener('change', function() {
                                    if (this.checked) {
                                        quantityContainer.classList.remove('d-none');
                                        quantityInput.disabled = false;
                                    } else {
                                        quantityContainer.classList.add('d-none');
                                        quantityInput.disabled = true;
                                    }
                                });
                            }
                        });
                        
                        // Si no hay implementos disponibles, mostrar mensaje
                        if (spaceItemsList.children.length === 0) {
                            spaceItemsContent.classList.add('d-none');
                            noSpaceItems.classList.remove('d-none');
                        }
                    } else {
                        spaceItemsContent.classList.add('d-none');
                        noSpaceItems.classList.remove('d-none');
                    }
                    
                    // Mostrar sección de bibliotecóloga solo si el espacio es una biblioteca
                    const librarianAssistanceSection = document.getElementById('librarian-assistance-section');
                    if (space.is_library) {
                        librarianAssistanceSection.classList.remove('d-none');
                    } else {
                        librarianAssistanceSection.classList.add('d-none');
                        // Deseleccionar el checkbox si cambia de espacio a uno que no es biblioteca
                        document.getElementById('requires_librarian').checked = false;
                    }
                    
                    // Procesar habilidades a trabajar
                    spaceSkillsLoading.classList.add('d-none');
                    
                    if (space.skills && space.skills.length > 0) {
                        spaceSkillsContent.classList.remove('d-none');
                        spaceSkillsList.innerHTML = '';
                        
                        // Agrupar las habilidades por su categoría padre y subcategoría
                        const skillsByCategory = {};
                        space.skills.forEach(skill => {
                            const categoryId = skill.category_id;
                            const subcategoryName = skill.subcategory_name;
                            
                            if (!skillsByCategory[categoryId]) {
                                skillsByCategory[categoryId] = {
                                    name: skill.category_name,
                                    subcategories: {}
                                };
                            }
                            
                            if (!skillsByCategory[categoryId].subcategories[subcategoryName]) {
                                skillsByCategory[categoryId].subcategories[subcategoryName] = {
                                    name: subcategoryName,
                                    description: skill.subcategory ? skill.subcategory.description : '',
                                    skills: []
                                };
                            }
                            
                            skillsByCategory[categoryId].subcategories[subcategoryName].skills.push({
                                id: skill.id,
                                name: skill.name,
                                description: skill.description
                            });
                        });

                        // Crear elementos para cada categoría de habilidades
                        Object.values(skillsByCategory).forEach(category => {
                            // Crear el encabezado de la categoría
                            const categoryHeader = document.createElement('div');
                            categoryHeader.className = 'mb-3';
                            categoryHeader.innerHTML = `
                                <h6 class="text-primary font-weight-bold">
                                    <i class="fas fa-layer-group"></i> ${category.name}
                                </h6>
                            `;
                            spaceSkillsList.appendChild(categoryHeader);

                            // Crear elementos para cada subcategoría
                            Object.values(category.subcategories).forEach(subcategory => {
                                // Crear el encabezado de la subcategoría
                                const subcategoryHeader = document.createElement('div');
                                subcategoryHeader.className = 'mb-2 ml-3';
                                subcategoryHeader.innerHTML = `
                                    <div class="text-secondary">
                                        <strong>${subcategory.name}</strong>
                                        ${subcategory.description ? ` (${subcategory.description})` : ''}
                                    </div>
                                `;
                                spaceSkillsList.appendChild(subcategoryHeader);

                                // Crear elementos para cada habilidad de la subcategoría
                                subcategory.skills.forEach(skill => {
                                    const skillElement = document.createElement('div');
                                    skillElement.className = 'form-check mb-2 ml-4';
                                    
                                    const skillHtml = `
                                        <input class="form-check-input" type="checkbox" 
                                            id="skill-${skill.id}" 
                                            name="skills[${skill.id}][selected]" 
                                            value="1">
                                        <label class="form-check-label" for="skill-${skill.id}">
                                            ${skill.name}
                                            ${skill.description ? `
                                                <p class="mb-0 text-muted small">${skill.description}</p>
                                            ` : ''}
                                        </label>
                                    `;
                                    
                                    skillElement.innerHTML = skillHtml;
                                    spaceSkillsList.appendChild(skillElement);
                                });
                            });
                        });
                    } else {
                        spaceSkillsContent.classList.add('d-none');
                        noSpaceSkills.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error loading space details:', error);
                    spaceDetails.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error al cargar la información del espacio.
                        </div>
                    `;
                    
                    // Ocultar secciones en caso de error
                    spaceItemsLoading.classList.add('d-none');
                    spaceSkillsLoading.classList.add('d-none');
                    spaceItemsContent.classList.add('d-none');
                    spaceSkillsContent.classList.add('d-none');
                    noSpaceItems.classList.remove('d-none');
                    noSpaceSkills.classList.remove('d-none');
                    noSpaceItems.querySelector('div').textContent = 'Error al cargar los implementos disponibles.';
                    noSpaceSkills.querySelector('div').textContent = 'Error al cargar las habilidades disponibles.';
                });
        }
        
        spaceSelect.addEventListener('change', function() {
            checkAvailability();
            loadSpaceDetails();
        });
        
        dateField.addEventListener('change', checkAvailability);
        
        // Validate end time is after start time
        function validateTimes() {
            const startTime = startTimeField.value;
            const endTime = endTimeField.value;
            
            if (startTime && endTime && startTime >= endTime) {
                endTimeField.setCustomValidity('La hora de fin debe ser posterior a la hora de inicio');
            } else {
                endTimeField.setCustomValidity('');
                // Volver a verificar conflictos con bloqueos si es necesario
                checkAvailability();
            }
        }
        
        startTimeField.addEventListener('change', validateTimes);
        endTimeField.addEventListener('change', validateTimes);
        
        // Initial check if space is already selected
        if (spaceSelect.value) {
            loadSpaceDetails();
            checkAvailability();
        }
    });
</script>
@endsection