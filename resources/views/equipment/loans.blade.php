@extends('adminlte::page')

@section('title', 'Préstamos de Equipos')

@section('content_header')
    <h1 class="text-primary">Gestión de Préstamos de Equipos</h1>
    @if(auth()->user()->can('equipment.loans.manage'))
        <div class="alert alert-info alert-sm py-2 mt-2" style="font-size: 0.9rem;">
            <i class="fas fa-info-circle mr-2"></i> Tienes permisos para entregar y eliminar reservas de equipos.
        </div>
    @else
        <div class="alert alert-warning alert-sm py-2 mt-2" style="font-size: 0.9rem;">
            <i class="fas fa-exclamation-triangle mr-2"></i> No tienes permisos para entregar o eliminar reservas. Contacta al administrador si necesitas realizar estas acciones.
        </div>
    @endif
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="viewTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="list-tab" data-toggle="tab" href="#list-view" role="tab" aria-controls="list-view" aria-selected="true">
                    <i class="fas fa-list"></i> Vista de Lista
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar-view" role="tab" aria-controls="calendar-view" aria-selected="false">
                    <i class="fas fa-calendar-alt"></i> Vista de Calendario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline-view" role="tab" aria-controls="timeline-view" aria-selected="false">
                    <i class="fas fa-stream"></i> Línea de Tiempo
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="card custom-card mb-3">
    <div class="card-header bg-light py-2">
        <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-filter"></i> Filtros</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body filter-card-body">
        <form id="filterForm" class="row">
            <div class="col-md-3 mb-2">
                <label for="sectionFilter">Sección</label>
                <select class="form-control form-control-sm" id="sectionFilter" name="section">
                    <option value="">Todas las secciones</option>
                    <option value="bachillerato">Bachillerato</option>
                    <option value="preescolar_primaria">Preescolar y Primaria</option>
                    <option value="administrativo">Administrativo</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label for="equipmentTypeFilter">Tipo de Equipo</label>
                <select class="form-control form-control-sm" id="equipmentTypeFilter" name="equipment_type">
                    <option value="">Todos los equipos</option>
                    <option value="laptop">Portátiles</option>
                    <option value="ipad">iPads</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label for="dateFrom">Fecha desde</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    </div>
                    <input type="date" class="form-control" id="dateFrom" name="date_from">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label for="dateTo">Fecha hasta</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    </div>
                    <input type="date" class="form-control" id="dateTo" name="date_to">
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <label for="statusFilter">Estado</label>
                <div class="d-flex">
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" class="custom-control-input status-checkbox" id="status-pending" value="pending" checked>
                        <label class="custom-control-label" for="status-pending">Pendientes</label>
                    </div>
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" class="custom-control-input status-checkbox" id="status-delivered" value="delivered" checked>
                        <label class="custom-control-label" for="status-delivered">Entregados</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input status-checkbox" id="status-returned" value="returned" checked>
                        <label class="custom-control-label" for="status-returned">Devueltos</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <label for="quickDateFilter">Filtros rápidos</label>
                <div class="d-flex">
                    <button type="button" class="btn btn-xs btn-outline-primary mr-2 quick-date-filter" data-days="0">Hoy</button>
                    <button type="button" class="btn btn-xs btn-outline-primary mr-2 quick-date-filter" data-days="1">Mañana</button>
                    <button type="button" class="btn btn-xs btn-outline-primary mr-2 quick-date-filter" data-days="7">Próximos 7 días</button>
                    <button type="button" class="btn btn-xs btn-outline-primary mr-2 quick-date-filter" data-days="30">Próximos 30 días</button>
                </div>
            </div>
            <div class="col-12 text-right">
                <a href="{{ route('equipment.loans.export') }}" id="exportExcel" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
                <button type="button" id="applyFilters" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter"></i> Aplicar filtros
                </button>
                <button type="button" id="resetFilters" class="btn btn-sm btn-secondary">
                    <i class="fas fa-sync"></i> Limpiar filtros
                </button>
            </div>
        </form>
    </div>
</div>

<div class="tab-content" id="viewTabsContent">
    <!-- Vista de Lista -->
    <div class="tab-pane fade show active" id="list-view" role="tabpanel" aria-labelledby="list-tab">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-loans" id="loansTable">
                        <thead class="thead-primary">
                            <tr>
                                <th>ID</th>
                                <th>Docente</th>
                                <th>Sección</th>
                                <th>Salón</th>
                                <th>Equipo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                                <tr class="loan-row" 
                                    data-loan-id="{{ $loan->id }}"
                                    data-loan-date="{{ $loan->loan_date->format('Y-m-d') }}" 
                                    data-section="{{ $loan->section }}" 
                                    data-status="{{ $loan->status }}"
                                    data-equipment-type="{{ $loan->equipment->type }}"
                                    data-auto-return="{{ $loan->auto_return ? '1' : '0' }}">
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ $loan->user->name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $loan->section)) }}</td>
                                    <td>{{ $loan->grade }}</td>
                                    <td>
                                        <span class="badge badge-{{ $loan->equipment->type === 'laptop' ? 'info' : 'warning' }}">
                                            {{ $loan->equipment->type === 'laptop' ? 'Portátil' : 'iPad' }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $loan->units_requested }}</span></td>
                                    <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="time-badge">
                                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($loan->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($loan->end_time)->format('H:i') }}
                                        </span>
                                        @if($loan->period_id)
                                            @if(strpos($loan->period_id, ':') !== false)
                                                <span class="badge bg-secondary mt-1" data-toggle="tooltip" title="Bloque de períodos de clase reservado">
                                                    <i class="fas fa-layer-group"></i> Bloque
                                                </span>
                                            @else
                                                <span class="badge bg-secondary mt-1" data-toggle="tooltip" title="Período de clase reservado">
                                                    <i class="fas fa-book"></i> Período
                                                </span>
                                            @endif
                                        @endif
                                        @if($loan->auto_return)
                                            <span class="badge bg-success mt-1" data-toggle="tooltip" title="Este equipo se devolverá automáticamente al finalizar el horario">
                                                <i class="fas fa-sync-alt"></i> Auto
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $loan->status === 'returned' ? 'success' : ($loan->status === 'delivered' ? 'info' : 'warning') }}">
                                            {{ $loan->status === 'returned' ? 'Devuelto' : ($loan->status === 'delivered' ? 'Entregado' : 'Pendiente') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($loan->delivery_observations || $loan->return_observations)
                                            <div class="d-flex flex-column gap-1">
                                                @if($loan->delivery_observations)
                                                    <span class="badge bg-info" 
                                                        data-toggle="tooltip" 
                                                        data-placement="top" 
                                                        data-html="true"
                                                        title="<strong>Observación de entrega:</strong><br>{{ $loan->delivery_observations }}">
                                                        <i class="fas fa-clipboard-check"></i> Entrega
                                                    </span>
                                                @endif
                                                @if($loan->return_observations)
                                                    <span class="badge bg-primary mt-1" 
                                                        data-toggle="tooltip" 
                                                        data-placement="top" 
                                                        data-html="true"
                                                        title="<strong>Observación de devolución:</strong><br>{{ $loan->return_observations }}">
                                                        <i class="fas fa-clipboard-list"></i> Devolución
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Sin observaciones</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($loan->status === 'pending')
                                            @if(auth()->user()->can('equipment.loans.manage'))
                                            <button class="btn btn-xs btn-success action-icon-btn deliver-btn" 
                                                    data-loan-id="{{ $loan->id }}"
                                                    data-units-requested="{{ $loan->units_requested }}"
                                                    data-equipment-id="{{ $loan->equipment_id }}"
                                                    data-toggle="tooltip" 
                                                    title="Entregar"
                                                    data-target="#deliveryModal">
                                                <i class="fas fa-hand-holding"></i>
                                            </button>
                                            @endif
                                            @if(auth()->user()->can('equipment.loans.manage'))
                                            <button class="btn btn-xs btn-primary action-icon-btn edit-btn"
                                                    data-loan-id="{{ $loan->id }}"
                                                    data-toggle="modal"
                                                    title="Editar"
                                                    data-target="#editModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @endif
                                            @if(auth()->user()->can('equipment.loans.manage'))
                                            <button class="btn btn-xs btn-danger action-icon-btn delete-btn"
                                                    data-loan-id="{{ $loan->id }}"
                                                    data-toggle="tooltip"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        @elseif($loan->status === 'delivered')
                                            @if(auth()->user()->can('equipment.loans.manage'))
                                            <button class="btn btn-xs btn-info action-icon-btn return-btn" 
                                                    data-loan-id="{{ $loan->id }}"
                                                    data-toggle="tooltip"
                                                    title="Devolver" 
                                                    data-target="#returnModal">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            @endif
                                        @else
                                            <button class="btn btn-xs btn-secondary action-icon-btn" 
                                                    data-toggle="tooltip"
                                                    title="Completado" 
                                                    disabled>
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vista de Calendario -->
    <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
        <div class="card custom-card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <!-- Vista de Línea de Tiempo -->
    <div class="tab-pane fade" id="timeline-view" role="tabpanel" aria-labelledby="timeline-tab">
        <div class="card custom-card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title"><i class="fas fa-clock"></i> Horarios de Préstamos</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="input-group date-selector">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            </div>
                            <input type="date" class="form-control" id="timelineDate" name="timeline_date" value="{{ date('Y-m-d') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="prevDay"><i class="fas fa-chevron-left"></i></button>
                                <button class="btn btn-outline-secondary" type="button" id="nextDay"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info timeline-info d-none">
                    <i class="fas fa-info-circle"></i> Seleccione una fecha para ver los préstamos programados.
                </div>
                <div id="timeline-container" class="mb-4">
                    <div class="timeline-hours">
                        <div>7:00</div>
                        <div>8:00</div>
                        <div>9:00</div>
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
                    <div id="timeline-equipment-container">
                        <!-- Los equipos y horarios se cargarán aquí dinámicamente -->
                    </div>
                </div>
                <div class="timeline-legend">
                    <span class="badge badge-warning"><i class="fas fa-circle"></i> iPads</span>
                    <span class="badge badge-info"><i class="fas fa-circle"></i> Portátiles</span>
                    <span class="badge badge-success"><i class="fas fa-circle"></i> Devuelto</span>
                    <span class="badge badge-info"><i class="fas fa-circle"></i> Entregado</span>
                    <span class="badge badge-warning"><i class="fas fa-circle"></i> Pendiente</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog" aria-labelledby="deliveryModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryModalTitle">Entrega de Equipos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deliveryForm">
                <div class="modal-body">
                    <div class="equipment-info mb-3">
                        <!-- Aquí se mostrará dinámicamente la información del equipo -->
                    </div>
                    
                    <div class="form-group units-group d-none">
                        <label>Cantidad de Equipos a Entregar</label>
                        <input type="number" name="units_delivered" class="form-control" min="1">
                        <small class="text-muted">
                            Unidades disponibles: <span class="available-units"></span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="delivery_observations" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Firma Digital</label>
                        <canvas id="deliverySignaturePad" width="400" height="200" style="border: 1px solid #ddd; border-radius: 4px;"></canvas>
                        <input type="hidden" name="delivery_signature">
                        <button type="button" class="btn btn-sm btn-secondary clear-signature">Limpiar</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalTitle">Devolución de Equipos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="returnForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="return_observations" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Firma Digital (Técnico)</label>
                        <canvas id="returnSignaturePad" width="400" height="200" style="border: 1px solid #ddd; border-radius: 4px;"></canvas>
                        <input type="hidden" name="return_signature">
                        <button type="button" class="btn btn-sm btn-secondary clear-signature">Limpiar</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Devolución</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Loan Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalTitle">Editar Préstamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLoanForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fecha del préstamo</label>
                        <input type="date" name="loan_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de inicio</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de fin</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad de unidades</label>
                        <input type="number" name="units_requested" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Salón</label>
                        <input type="text" name="grade" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loan Details Modal -->
<div class="modal fade" id="loanDetailsModal" tabindex="-1" role="dialog" aria-labelledby="loanDetailsModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanDetailsModalTitle">Detalles del Préstamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="loan-details-content">
                    <!-- Contenido dinámico -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="#" id="loan-action-button" class="btn btn-primary d-none">Acción</a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<link rel="stylesheet" href="{{ asset('css/compact-loans.css') }}">
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --warning: #ffc107;
        --info: #17a2b8;
    }

    /* Header Styles */
    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Card Styles */
    .custom-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        background-color: #ffffff;
    }

    /* Table Styles */
    .table {
        margin-bottom: 0;
        font-size: 0.85rem; /* Reducir tamaño general de la fuente */
    }

    .thead-primary {
        background-color: var(--primary);
        color: white;
    }

    .thead-primary th {
        border: none;
        padding: 0.6rem 0.5rem; /* Reducir el padding de los encabezados */
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.8rem; /* Reducir tamaño de fuente en cabeceras */
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        padding: 0.4rem 0.5rem; /* Reducir el padding de las celdas */
        color: #495057;
        line-height: 1.4; /* Reducir altura de línea */
    }

    .table-hover tbody tr:hover {
        background-color: rgba(54, 78, 118, 0.05);
        transition: background-color 0.3s ease;
    }

    /* Estilos para los botones de acción */
    .action-icon-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        line-height: 28px;
        text-align: center;
        border-radius: 4px;
        margin-right: 3px;
    }
    
    .action-icon-btn i {
        font-size: 0.875rem;
        line-height: 28px;
        display: inline-block;
        vertical-align: middle;
    }
    
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
        line-height: 1.5;
    }
    
    /* Ajuste del espacio en las cards */
    .custom-card .card-body {
        padding: 0.75rem;
    }
    
    .card-header + .card-body {
        padding: 0.75rem 0.75rem 0.5rem;
    }
    
    /* Estilos específicos para la tarjeta de filtros */
    .filter-card-body {
        padding: 0.6rem 0.7rem 0.3rem !important;
    }
    
    /* Ajustar el espacio entre las tarjetas */
    .mb-3 {
        margin-bottom: 0.75rem !important;
    }
    
    /* Ajustes para la tabla responsive */
    .table-responsive {
        margin-bottom: 0;
    }
    
    /* Estilos para el formulario de filtros */
    #filterForm label {
        font-size: 0.85rem;
        margin-bottom: 0.2rem;
    }
    
    #filterForm .form-control,
    #filterForm .input-group .input-group-text {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        height: calc(1.5em + 0.5rem + 2px);
    }
    
    #filterForm .mb-3 {
        margin-bottom: 0.5rem !important;
    }
    
    #filterForm .custom-control-label {
        font-size: 0.85rem;
    }
    
    #filterForm .custom-control-label::before,
    #filterForm .custom-control-label::after {
        width: 0.85rem;
        height: 0.85rem;
        top: 0.15rem;
    }
    
    #filterForm .btn-sm {
        padding: 0.15rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Badge Styles */
    .badge {
        padding: 0.5em 1em;
        font-size: 0.85em;
        font-weight: 500;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
    }

    .badge-pending, .badge-warning {
        background-color: var(--warning);
        color: #000;
    }

    .badge-active {
        background-color: var(--success);
        color: white;
    }

    .badge-completed, .badge-info {
        background-color: var(--info);
        color: white;
    }

    .badge-success {
        background-color: #28a745 !important;
        color: white;
    }

    .ml-1 {
        margin-left: 0.25rem;
    }

    .mt-1 {
        margin-top: 0.25rem !important;
    }

    .d-flex {
        display: flex !important;
    }

    .flex-column {
        flex-direction: column !important;
    }

    .gap-1 {
        gap: 0.25rem !important;
    }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px 8px;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: white !important;
        border: 1px solid var(--primary) !important;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #2a3d5d !important;
        color: white !important;
        border: 1px solid #2a3d5d !important;
    }

    /* Filter card styles */
    .card-header {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .card-title {
        margin: 0.5rem 0;
        font-size: 1.1rem;
        font-weight: 500;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .col-md-4, .col-12 {
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
    }

    @media (min-width: 768px) {
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        .col-md-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    .text-right {
        text-align: right !important;
    }
    
    /* Time badge */
    .time-badge {
        padding: 0.25rem 0.5rem;
        background-color: #f8f9fa;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 500;
    }
    
    .time-badge i {
        color: var(--primary);
    }
    
    /* Calendar styles */
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        border: none;
        padding: 2px 4px;
        margin-bottom: 2px;
        font-size: 0.8rem;
    }
    
    .fc-day-today {
        background-color: rgba(54, 78, 118, 0.05) !important;
    }
    
    .fc-toolbar-title {
        color: var(--primary);
        font-weight: 600;
    }
    
    /* Timeline styles */
    #timeline-container {
        position: relative;
        margin-top: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        overflow-x: auto;
    }
    
    .timeline-hours {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 0 10px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
    }
    
    .timeline-hours div {
        font-size: 12px;
        color: #666;
        flex: 1;
        text-align: center;
        font-weight: 500;
    }
    
    .timeline-equipment {
        position: relative;
        height: 40px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .timeline-equipment-name {
        width: 150px;
        padding-right: 15px;
        font-weight: 500;
        color: #333;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    
    .timeline-slots {
        position: relative;
        flex: 1;
        height: 30px;
    }
    
    .timeline-slot {
        position: absolute;
        height: 25px;
        top: 0;
        border-radius: 4px;
        color: white;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    
    .timeline-slot-laptop {
        background-color: rgba(23, 162, 184, 0.7);
    }
    
    .timeline-slot-ipad {
        background-color: rgba(255, 193, 7, 0.7);
    }
    
    .timeline-slot-pending {
        border: 2px solid rgba(255, 193, 7, 1);
    }
    
    .timeline-slot-delivered {
        border: 2px solid rgba(23, 162, 184, 1);
    }
    
    .timeline-slot-returned {
        border: 2px solid rgba(40, 167, 69, 1);
    }
    
    .timeline-slot:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 100;
    }
    
    .timeline-legend {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        justify-content: center;
    }
    
    .timeline-legend .badge {
        padding: 0.25rem 0.5rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    /* Nav tabs */
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary);
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 600;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        color: var(--primary);
    }
    
    /* Date selector */
    .date-selector {
        max-width: 300px;
        margin-left: auto;
    }
    
    /* Quick date filters */
    .quick-date-filter {
        border-radius: 20px;
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        
        .badge {
            display: inline-block;
            margin: 2px 0;
        }
        
        .timeline-equipment-name {
            width: 100px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/equipment-timeline-fix.js') }}"></script>
<script src="{{ asset('js/check-availability-fix.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicialización de DataTables con drawCallback
    const loansTable = $('#loansTable').DataTable({
        "order": [[4, 'desc']], // Ordenar por fecha de préstamo de forma descendente
        "pageLength": 25,
        "drawCallback": function(settings) {
            // Adjuntar manejadores de eventos después de cada redibujado
            attachActionButtonHandlers();
            // Reinicializar tooltips
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                container: 'body'
            });
        },
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            }
        },
        "order": [[6, "desc"], [7, "desc"]],
        "pageLength": 10,
        "responsive": true
    });

    // Variables globales para los datos de préstamos
    let allLoans = [];
    
    // Limpiar array de préstamos para evitar duplicados en caso de recarga parcial
    allLoans = [];
    
    console.log("Iniciando recopilación de datos de préstamos...");
    
    // Recopilar datos de préstamos de la tabla para reutilizarlos
    $('.loan-row').each(function() {
        const row = $(this);
        const id = row.find('td:first').text().trim();
        const user = row.find('td:eq(1)').text().trim();
        const section = row.attr('data-section');
        const sectionName = row.find('td:eq(2)').text().trim();
        const grade = row.find('td:eq(3)').text().trim();
        const equipmentType = row.attr('data-equipment-type');
        const equipmentName = row.find('td:eq(4) .badge').text().trim();
        const units = row.find('td:eq(5) .badge').text().trim();
        const date = row.attr('data-loan-date');
        const dateFormatted = row.find('td:eq(6)').text().trim();
        
        // Extraer información de horario y períodos de manera más robusta
        const timeCell = row.find('td:eq(7)');
        const timeRange = timeCell.find('.time-badge').text().trim();
        let startTime = '';
        let endTime = '';
        
        // Extraer tiempos de forma más confiable usando regex
        const timeRegex = /(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/;
        const timeMatch = timeRange.match(timeRegex);
        
        if (timeMatch && timeMatch.length >= 3) {
            startTime = timeMatch[1];
            endTime = timeMatch[2];
        }
        
        // Verificar si hay período o bloque de períodos de forma más confiable
        let periodId = null;
        let isPeriodBlock = false;
        
        if (timeCell.find('.badge:contains("Bloque")').length > 0) {
            periodId = '1:2'; // Valor genérico ya que no tenemos los IDs reales
            isPeriodBlock = true;
        } else if (timeCell.find('.badge:contains("Período")').length > 0) {
            periodId = '1'; // Valor genérico ya que no tenemos el ID real
            isPeriodBlock = false;
        }
        
        // Verificar si tiene devolución automática
        const autoReturn = row.attr('data-auto-return') === '1';
        
        const status = row.attr('data-status');
        const statusText = row.find('td:eq(8) .badge').text().trim();
        
        // Crear objeto de préstamo y agregarlo al array
        const loan = {
            id,
            user,
            section,
            sectionName,
            grade,
            equipmentType,
            equipmentName,
            units,
            date,
            dateFormatted,
            startTime,
            endTime,
            status,
            statusText,
            autoReturn,
            periodId,
            isPeriodBlock
        };
        
        allLoans.push(loan);
        
        // Verificar los datos recopilados para debugging
        console.log(`Préstamo ${id} procesado: ${date} ${startTime}-${endTime}`);
    });
    
    console.log(`Total de préstamos procesados: ${allLoans.length}`);
    if (allLoans.length > 0) {
        console.log("Ejemplo del primer préstamo:", allLoans[0]);
    }

    // Funciones de filtrado
    function applyFilters() {
        const selectedSection = $('#sectionFilter').val();
        const selectedEquipmentType = $('#equipmentTypeFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        // Obtener estados seleccionados
        const selectedStatuses = [];
        $('.status-checkbox:checked').each(function() {
            selectedStatuses.push($(this).val());
        });
        
        // Filtrar filas de la tabla usando API de DataTables correctamente
        loansTable.rows().every(function(rowIdx) {
            const rowNode = this.node();
            const $row = $(rowNode);
            
            const rowSection = $row.attr('data-section');
            const rowDate = $row.attr('data-loan-date');
            const rowStatus = $row.attr('data-status');
            const rowEquipmentType = $row.attr('data-equipment-type');
            
            let showRow = true;
            
            // Filtro de sección
            if (selectedSection && rowSection !== selectedSection) {
                showRow = false;
            }
            
            // Filtro de tipo de equipo
            if (selectedEquipmentType && rowEquipmentType !== selectedEquipmentType) {
                showRow = false;
            }
            
            // Filtro de fecha desde
            if (dateFrom && rowDate < dateFrom) {
                showRow = false;
            }
            
            // Filtro de fecha hasta
            if (dateTo && rowDate > dateTo) {
                showRow = false;
            }
            
            // Filtro de estado
            if (selectedStatuses.length > 0 && !selectedStatuses.includes(rowStatus)) {
                showRow = false;
            }
            
            // Aplicar visibilidad utilizando DataTables API correctamente
            if (showRow) {
                $(rowNode).removeClass('d-none');
            } else {
                $(rowNode).addClass('d-none');
            }
        });
        
        // Redibujar la tabla después de aplicar filtros
        loansTable.draw();
        
        // Actualizar calendario con los mismos filtros
        updateCalendarEvents();
        
        // Actualizar línea de tiempo si es el día seleccionado
        const timelineDate = $('#timelineDate').val();
        if (timelineDate) {
            updateTimeline(timelineDate);
        }
        
        // Actualizar la URL de exportación
        updateExportUrl();

        // Resaltar visualmente los filtros activos
        highlightActiveFilters();
    }

    // Resaltar filtros activos para mejor retroalimentación visual
    function highlightActiveFilters() {
        // Resaltar filtros de fecha
        if ($('#dateFrom').val()) {
            $('#dateFrom').addClass('border-primary');
        } else {
            $('#dateFrom').removeClass('border-primary');
        }
        
        if ($('#dateTo').val()) {
            $('#dateTo').addClass('border-primary');
        } else {
            $('#dateTo').removeClass('border-primary');
        }

        // Resaltar filtros rápidos
        $('.quick-date-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        
        const today = new Date().toISOString().split('T')[0];
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        
        // Fecha para próximos 7 días
        const next7Days = new Date();
        next7Days.setDate(next7Days.getDate() + 6); // +6 porque today ya es el día 1
        const next7DaysStr = next7Days.toISOString().split('T')[0];
        
        // Fecha para próximos 30 días
        const next30Days = new Date();
        next30Days.setDate(next30Days.getDate() + 29); // +29 porque today ya es el día 1
        const next30DaysStr = next30Days.toISOString().split('T')[0];
        
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (dateFrom === today && (!dateTo || dateTo === today)) {
            $('.quick-date-filter[data-days="0"]').removeClass('btn-outline-primary').addClass('btn-primary');
        } else if (dateFrom === tomorrowStr && dateTo === tomorrowStr) {
            $('.quick-date-filter[data-days="1"]').removeClass('btn-outline-primary').addClass('btn-primary');
        } else if (dateFrom === today && dateTo === next7DaysStr) {
            $('.quick-date-filter[data-days="7"]').removeClass('btn-outline-primary').addClass('btn-primary');
        } else if (dateFrom === today && dateTo === next30DaysStr) {
            $('.quick-date-filter[data-days="30"]').removeClass('btn-outline-primary').addClass('btn-primary');
        }
        
        // Resaltar otros filtros activos
        if ($('#sectionFilter').val()) {
            $('#sectionFilter').addClass('border-primary');
        } else {
            $('#sectionFilter').removeClass('border-primary');
        }
        
        if ($('#equipmentTypeFilter').val()) {
            $('#equipmentTypeFilter').addClass('border-primary');
        } else {
            $('#equipmentTypeFilter').removeClass('border-primary');
        }
    }

    // Función para formatear fechas a ISO (YYYY-MM-DD)
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Añadir función para filtrar por sección
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            return true; // Ya se maneja con applyFilters
        }
    );

    // Actualizar el enlace del botón de exportación para incluir los filtros
    function updateExportUrl() {
        const section = $('#sectionFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const equipmentType = $('#equipmentTypeFilter').val();
        
        let exportUrl = "{{ route('equipment.loans.export') }}";
        const params = [];
        
        if (section) {
            params.push(`section=${encodeURIComponent(section)}`);
        }
        
        if (dateFrom) {
            params.push(`date_from=${encodeURIComponent(dateFrom)}`);
        }
        
        if (dateTo) {
            params.push(`date_to=${encodeURIComponent(dateTo)}`);
        }
        
        if (equipmentType) {
            params.push(`equipment_type=${encodeURIComponent(equipmentType)}`);
        }
        
        if (params.length > 0) {
            exportUrl += `?${params.join('&')}`;
        }
        
        $('#exportExcel').attr('href', exportUrl);
    }

    // Inicializar calendario
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: [],
        eventClick: function(info) {
            showLoanDetails(info.event.id);
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        nowIndicator: true,
        firstDay: 1 // Lunes como primer día de la semana
    });
    
    calendar.render();
    
    // Función para actualizar los eventos del calendario según los filtros
    function updateCalendarEvents() {
        calendar.removeAllEvents();
        
        const selectedSection = $('#sectionFilter').val();
        const selectedEquipmentType = $('#equipmentTypeFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        // Obtener estados seleccionados
        const selectedStatuses = [];
        $('.status-checkbox:checked').each(function() {
            selectedStatuses.push($(this).val());
        });
        
        // Filtrar préstamos y crear eventos
        const filteredLoans = allLoans.map(loan => {
            if (selectedSection && loan.section !== selectedSection) return false;
            if (selectedEquipmentType && loan.equipmentType !== selectedEquipmentType) return false;
            if (dateFrom && loan.date < dateFrom) return false;
            if (dateTo && loan.date > dateTo) return false;
            if (selectedStatuses.length > 0 && !selectedStatuses.includes(loan.status)) return false;
            return loan;
        }).filter(Boolean);
        
        const events = filteredLoans.map(loan => {
            // Determinar color según tipo de equipo y estado
            let backgroundColor = loan.equipmentType === 'laptop' ? '#17a2b8' : '#ffc107';
            let textColor = loan.equipmentType === 'laptop' ? '#fff' : '#000';
            
            if (loan.status === 'returned') {
                backgroundColor = '#28a745';
                textColor = '#fff';
            } else if (loan.status === 'pending') {
                backgroundColor = '#ffc107';
                textColor = '#000';
            }
            
            return {
                id: loan.id,
                title: `${loan.equipmentName} (${loan.units}) - ${loan.grade}`,
                start: `${loan.date}T${loan.startTime}`,
                end: `${loan.date}T${loan.endTime}`,
                backgroundColor: backgroundColor,
                textColor: textColor,
                borderColor: backgroundColor,
                extendedProps: {
                    loanId: loan.id,
                    user: loan.user,
                    section: loan.sectionName,
                    grade: loan.grade,
                    status: loan.statusText,
                    equipmentType: loan.equipmentType,
                    autoReturn: loan.autoReturn
                }
            };
        });
        
        calendar.addEventSource(events);
        calendar.refetchEvents();
        
        // Si hay un rango de fechas filtrado, navegar a esa fecha en el calendario
        if (dateFrom) {
            calendar.gotoDate(dateFrom);
        }
    }
    
    // Función para actualizar la línea de tiempo
    function updateTimeline(date) {
        const container = $('#timeline-equipment-container');
        container.empty();
        
        // Obtener préstamos para la fecha seleccionada con los filtros aplicados
        const selectedSection = $('#sectionFilter').val();
        const selectedEquipmentType = $('#equipmentTypeFilter').val();
        
        // Obtener estados seleccionados
        const selectedStatuses = [];
        $('.status-checkbox:checked').each(function() {
            selectedStatuses.push($(this).val());
        });
        
        // Filtrar préstamos para la fecha seleccionada
        const dayLoans = allLoans.filter(loan => {
            if (loan.date !== date) return false;
            if (selectedSection && loan.section !== selectedSection) return false;
            if (selectedEquipmentType && loan.equipmentType !== selectedEquipmentType) return false;
            if (selectedStatuses.length > 0 && !selectedStatuses.includes(loan.status)) return false;
            return true;
        });
        
        if (dayLoans.length === 0) {
            $('.timeline-info').removeClass('d-none').text('No hay préstamos para la fecha seleccionada con los filtros aplicados.');
            return;
        }
        
        $('.timeline-info').addClass('d-none');
        
        // Agrupar préstamos por tipo de equipo
        const groupedByType = {};
        dayLoans.forEach(loan => {
            if (!groupedByType[loan.equipmentType]) {
                groupedByType[loan.equipmentType] = [];
            }
            groupedByType[loan.equipmentType].push(loan);
        });
        
        // Crear la línea de tiempo para cada tipo de equipo
        Object.keys(groupedByType).forEach(type => {
            const typeName = type === 'laptop' ? 'Portátil' : 'iPad';
            const typeIcon = type === 'laptop' ? 'fas fa-laptop' : 'fas fa-tablet-alt';
            
            // Crear contenedor por tipo
            const typeContainer = $(`<div class="timeline-equipment-type">
                <h6 class="mt-3"><i class="${typeIcon}"></i> ${typeName}s</h6>
            </div>`);
            
            // Añadir cada préstamo
            groupedByType[type].forEach((loan, index) => {
                const equipmentRow = $(`<div class="timeline-equipment">
                    <div class="timeline-equipment-name">
                        <span class="badge badge-${type === 'laptop' ? 'info' : 'warning'} mr-2">${loan.units}</span> ${loan.grade}
                    </div>
                    <div class="timeline-slots" id="timeline-${loan.id}"></div>
                </div>`);
                
                typeContainer.append(equipmentRow);
                
                // Una vez añadido al DOM, calcular las posiciones
                setTimeout(() => {
                    const slotsContainer = $(`#timeline-${loan.id}`);
                    const containerWidth = slotsContainer.width();
                    
                    // Convertir horas a porcentaje del ancho
                    const startParts = loan.startTime.split(':');
                    const endParts = loan.endTime.split(':');
                    
                    const startHour = parseInt(startParts[0]) + parseInt(startParts[1]) / 60;
                    const endHour = parseInt(endParts[0]) + parseInt(endParts[1]) / 60;
                    
                    // Calcular posición relativa (de 7 a 18 horas -> 11 horas totales)
                    const left = ((startHour - 7) / 11) * 100;
                    const width = ((endHour - startHour) / 11) * 100;
                    
                    const slot = $(`<div class="timeline-slot timeline-slot-${type} timeline-slot-${loan.status}" 
                                    style="left: ${left}%; width: ${width}%;" 
                                    data-loan-id="${loan.id}"
                                    title="${loan.startTime} - ${loan.endTime}">
                        ${loan.startTime}-${loan.endTime}
                    </div>`);
                    
                    slotsContainer.append(slot);
                    
                    // Añadir evento click
                    slot.on('click', function() {
                        showLoanDetails(loan.id);
                    });
                    
                }, 100);
            });
            
            container.append(typeContainer);
        });
    }
    
    // Función para mostrar detalles del préstamo
    function showLoanDetails(loanId) {
        // Buscar el préstamo por ID
        const loan = allLoans.find(l => l.id === loanId);
        if (!loan) return;
        
        // Formato de estado
        const statusClass = loan.status === 'returned' ? 'success' : 
                          (loan.status === 'delivered' ? 'info' : 'warning');
        
        // Determinar si es un bloque de períodos
        const isPeriodBlock = loan.periodId && loan.periodId.includes(':');
        
        // Crear contenido HTML para el modal
        const content = `
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%">Docente</th>
                                <td>${loan.user}</td>
                            </tr>
                            <tr>
                                <th>Sección</th>
                                <td>${loan.sectionName}</td>
                            </tr>
                            <tr>
                                <th>Salón</th>
                                <td>${loan.grade}</td>
                            </tr>
                            <tr>
                                <th>Equipo</th>
                                <td>${loan.equipmentName}</td>
                            </tr>
                            <tr>
                                <th>Cantidad</th>
                                <td>${loan.units}</td>
                            </tr>
                            <tr>
                                <th>Fecha</th>
                                <td>${loan.dateFormatted}</td>
                            </tr>
                            <tr>
                                <th>Horario</th>
                                <td>
                                    <span class="time-badge">
                                        <i class="far fa-clock"></i> ${loan.startTime} - ${loan.endTime}
                                    </span>
                                    ${loan.periodId ? 
                                        (isPeriodBlock ? 
                                            '<span class="badge bg-secondary ml-2" title="Bloque de períodos de clase reservado"><i class="fas fa-layer-group"></i> Bloque</span>' : 
                                            '<span class="badge bg-secondary ml-2" title="Período de clase reservado"><i class="fas fa-book"></i> Período</span>'
                                        ) : ''
                                    }
                                </td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td><span class="badge badge-${statusClass}">${loan.statusText}</span></td>
                            </tr>
                            ${loan.autoReturn !== undefined ? `
                            <tr>
                                <th>Devolución automática</th>
                                <td>
                                    ${loan.autoReturn ? 
                                        '<span class="badge badge-success"><i class="fas fa-sync-alt"></i> Activada</span>' : 
                                        '<span class="badge badge-secondary">No activada</span>'}
                                    ${loan.status !== 'returned' ? 
                                        `<button type="button" class="btn btn-sm btn-outline-primary ml-2 toggle-auto-return" data-loan-id="${loan.id}" data-auto-return="${loan.autoReturn}">
                                            ${loan.autoReturn ? '<i class="fas fa-toggle-off"></i> Desactivar' : '<i class="fas fa-toggle-on"></i> Activar'}
                                        </button>` : ''
                                    }
                                </td>
                            </tr>
                            ` : ''}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        // Actualizar contenido del modal
        $('#loan-details-content').html(content);
        
        // Configurar botón de acción según el estado
        const actionButton = $('#loan-action-button');
        if (loan.status === 'pending') {
            actionButton.text('Entregar');
            actionButton.removeClass('d-none btn-info').addClass('btn-success');
            actionButton.attr('href', '#');
            actionButton.off('click').on('click', function(e) {
                e.preventDefault();
                $('#loanDetailsModal').modal('hide');
                // Abrir modal de entrega
                $('.deliver-btn[data-loan-id="'+loanId+'"]').click();
            });
        } else if (loan.status === 'delivered') {
            actionButton.text('Devolver');
            actionButton.removeClass('d-none btn-success').addClass('btn-info');
            actionButton.attr('href', '#');
            actionButton.off('click').on('click', function(e) {
                e.preventDefault();
                $('#loanDetailsModal').modal('hide');
                // Abrir modal de devolución
                $('.return-btn[data-loan-id="'+loanId+'"]').click();
            });
        } else {
            actionButton.addClass('d-none');
        }
        
        // Mostrar modal
        $('#loanDetailsModal').modal('show');
    }

    // Manejar filtros de fecha rápidos
    $('.quick-date-filter').on('click', function() {
        const days = parseInt($(this).data('days'));
        
        // Resaltar el botón activo
        $('.quick-date-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        
        // Obtener la fecha actual para la zona horaria local
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Establecer a medianoche
        
        let fromDate, toDate;
        
        if (days === 1) {
            // Para "Mañana", establecer tanto la fecha desde como hasta a mañana
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            fromDate = tomorrow;
            toDate = tomorrow;
        } else {
            // Para el resto de casos (hoy, próximos 7 días, próximos 30 días)
            fromDate = new Date(today);
            toDate = new Date(today);
            if (days > 1) {
                toDate.setDate(today.getDate() + days - 1); // -1 porque incluimos el día de hoy
            }
        }
        
        // Formatear fechas a YYYY-MM-DD
        const fromStr = formatDate(fromDate);
        const toStr = formatDate(toDate);
        
        // Establecer valores en los inputs y darles estilo
        $('#dateFrom').val(fromStr).addClass('border-primary');
        $('#dateTo').val(toStr).addClass('border-primary');
        
        // Actualizar la vista del calendario si estamos mostrando esa pestaña
        if ($('#calendar-tab').hasClass('active')) {
            if (days <= 7) {
                calendar.changeView('timeGridWeek');
            } else {
                calendar.changeView('dayGridMonth');
            }
            calendar.gotoDate(fromStr);
        }
        
        // Actualizar la línea de tiempo si estamos mostrando esa pestaña y estamos filtrando por hoy
        if ($('#timeline-tab').hasClass('active') && days <= 1) {
            $('#timelineDate').val(fromStr);
            updateTimeline(fromStr);
        }
        
        // Aplicar filtros
        applyFilters();
        
        // Mostrar mensaje de filtro aplicado
        const filterMsg = days === 0 ? 'Mostrando préstamos de hoy' : 
                         days === 1 ? 'Mostrando préstamos de mañana' :
                         'Mostrando préstamos de los próximos ' + days + ' días';
        
        // Crear y mostrar toast informativo
        showToast(filterMsg, 'success');
    });
    
    // Función para mostrar mensajes toast
    function showToast(message, type = 'info') {
        // Verificar si ya existe el contenedor de toast
        let toastContainer = $('.toast-container');
        if (toastContainer.length === 0) {
            toastContainer = $('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1500; right: 20px; bottom: 20px;"></div>');
            $('body').append(toastContainer);
        }
        
        // Crear el toast
        const toast = $(`
            <div class="toast bg-${type}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body text-white">
                    ${message}
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        `);
        
        // Agregar al contenedor y mostrarlo
        toastContainer.append(toast);
        toast.toast({delay: 3000, animation: true}).toast('show');
        
        // Eliminar después de ocultarse
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Manejar eventos de fechas
    $('#dateFrom, #dateTo').on('change', function() {
        // Al cambiar manualmente las fechas, quitar la selección de filtros rápidos
        $('.quick-date-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        
        // Si se establece una fecha, resaltarla
        if ($(this).val()) {
            $(this).addClass('border-primary');
        } else {
            $(this).removeClass('border-primary');
        }
        
        // Si la fecha "hasta" es anterior a la fecha "desde", corregirla
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (dateFrom && dateTo && dateTo < dateFrom) {
            $('#dateTo').val(dateFrom);
            showToast('La fecha "hasta" no puede ser anterior a la fecha "desde". Se ha ajustado automáticamente.', 'warning');
        }
        
        // Aplicar filtros automáticamente cuando se cambian las fechas
        applyFilters();
    });

    // Manejar filtros
    $('#applyFilters').on('click', function() {
        applyFilters();
        showToast('Filtros aplicados correctamente', 'success');
    });

    // Limpiar filtros
    $('#resetFilters').on('click', function() {
        $('#sectionFilter').val("").removeClass('border-primary');
        $('#equipmentTypeFilter').val("").removeClass('border-primary');
        $('#dateFrom').val("").removeClass('border-primary');
        $('#dateTo').val("").removeClass('border-primary');
        $('.status-checkbox').prop('checked', true);
        $('.quick-date-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        
        // Aplicar filtros (sin filtros)
        applyFilters();
        
        // Mostrar mensaje
        showToast('Filtros restablecidos', 'info');
    });

    // Inicializar la URL de exportación
    updateExportUrl();

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        container: 'body'
    });
    
    // Inicializar calendario con los datos actuales
    updateCalendarEvents();
    
    // Inicializar línea de tiempo con la fecha actual
    const today = new Date().toISOString().split('T')[0];
    $('#timelineDate').val(today);
    updateTimeline(today);
    
    // Manejar cambio de fecha en la línea de tiempo
    $('#timelineDate').on('change', function() {
        const selectedDate = $(this).val();
        updateTimeline(selectedDate);
    });
    
    // Manejar botones de navegación de fecha
    $('#prevDay').on('click', function() {
        const currentDate = new Date($('#timelineDate').val());
        currentDate.setDate(currentDate.getDate() - 1);
        const prevDay = formatDate(currentDate);
        $('#timelineDate').val(prevDay);
        updateTimeline(prevDay);
    });
    
    // Botón para día siguiente con implementación mejorada
    $('#nextDay').on('click', function(e) {
        e.preventDefault(); // Prevenir comportamiento por defecto
        
        try {
            // Obtener fecha actual del selector de fechas
            const timelineDateValue = $('#timelineDate').val();
            if (!timelineDateValue) {
                console.error('Valor de fecha no disponible');
                return;
            }
            
            // Crear objeto de fecha y avanzar un día
            const currentDate = new Date(timelineDateValue + 'T00:00:00'); // Asegurar que se interprete como fecha local
            currentDate.setDate(currentDate.getDate() + 1); // Avanzar al día siguiente
            
            // Formatear la nueva fecha
            const nextDay = formatDate(currentDate);
            
            // Actualizar el valor en el campo de fecha
            $('#timelineDate').val(nextDay);
            
            // Mostrar mensaje de depuración
            console.log('Navegando al día siguiente:', nextDay, 'desde:', timelineDateValue);
            
            // Actualizar la línea de tiempo con la nueva fecha
            updateTimeline(nextDay);
            
            // Actualizar la UI para reflejar el cambio de fecha
            showToast('Mostrando horario para: ' + currentDate.toLocaleDateString(), 'info');
        } catch (error) {
            console.error('Error al navegar al día siguiente:', error);
        }
    });
    
    // Manejar cambio entre pestañas
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetId = $(e.target).attr("href");
        
        if (targetId === '#calendar-view') {
            // Cuando se muestra la vista de calendario, refrescar para que se ajuste al contenedor
            calendar.updateSize();
            
            // Si hay fechas filtradas, ajustar la vista
            const dateFrom = $('#dateFrom').val();
            const dateTo = $('#dateTo').val();
            
            if (dateFrom && dateTo) {
                const from = new Date(dateFrom);
                const to = new Date(dateTo);
                const diffDays = Math.ceil((to - from) / (1000 * 60 * 60 * 24)) + 1;
                
                if (diffDays <= 7) {
                    calendar.changeView('timeGridWeek');
                    calendar.gotoDate(dateFrom);
                }
            }
        }
    });

    let currentLoanId = null;
    let deliveryPad = null;
    let returnPad = null;

    // Initialize signature pads when modals are shown
        // Variable para almacenar el elemento que tenía el foco antes de abrir el modal
    let lastFocusedElement;
    
    // Función para manejar la accesibilidad del modal
    function setupModalAccessibility(modalId) {
        const $modal = $(modalId);
        
        // Cuando el modal está a punto de mostrarse
        $modal.on('show.bs.modal', function() {
            // Guardamos el elemento que tiene el foco actualmente
            lastFocusedElement = document.activeElement;
        });
        
        // Cuando el modal se ha mostrado
        $modal.on('shown.bs.modal', function() {
            // Enfocamos el primer elemento interactivo dentro del modal
            const $firstFocusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first();
            $firstFocusable.focus();
            
            // Inicializamos los pads de firma según corresponda
            if (modalId === '#deliveryModal') {
                const canvas = document.getElementById('deliverySignaturePad');
                deliveryPad = new SignaturePad(canvas);
            } else if (modalId === '#returnModal') {
                const canvas = document.getElementById('returnSignaturePad');
                returnPad = new SignaturePad(canvas);
            }
        });
        
        // Cuando el modal está a punto de ocultarse
        $modal.on('hide.bs.modal', function() {
            // Removemos el foco de cualquier elemento dentro del modal
            if (document.activeElement && $modal.has(document.activeElement).length > 0) {
                document.activeElement.blur();
            }
        });
        
        // Cuando el modal se ha ocultado
        $modal.on('hidden.bs.modal', function() {
            // Devolvemos el foco al elemento que lo tenía antes
            if (lastFocusedElement) {
                lastFocusedElement.focus();
            }
            
            // Limpiamos los pads de firma si existen
            if (modalId === '#deliveryModal' && deliveryPad) deliveryPad.clear();
            if (modalId === '#returnModal' && returnPad) returnPad.clear();
        });
        
        // Atrapamos el foco dentro del modal (bucle de foco)
        $modal.on('keydown', function(e) {
            // Si la tecla presionada no es Tab, no hacemos nada
            if (e.key !== 'Tab') return;
            
            // Obtenemos todos los elementos enfocables dentro del modal
            const $focusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const $firstFocusable = $focusable.first();
            const $lastFocusable = $focusable.last();
            
            // Si se presiona Shift+Tab en el primer elemento, movemos el foco al último
            if (e.shiftKey && document.activeElement === $firstFocusable[0]) {
                e.preventDefault();
                $lastFocusable.focus();
            }
            // Si se presiona Tab en el último elemento, movemos el foco al primero
            else if (!e.shiftKey && document.activeElement === $lastFocusable[0]) {
                e.preventDefault();
                $firstFocusable.focus();
            }
        });
    }

    // Configuramos la accesibilidad para los modales
    setupModalAccessibility('#deliveryModal');
    setupModalAccessibility('#returnModal');
    setupModalAccessibility('#editModal');
    setupModalAccessibility('#loanDetailsModal');

    // Función para adjuntar los manejadores de eventos a los botones de acción
    function attachActionButtonHandlers() {
        // Manejador para botones de entrega
        $('.deliver-btn').off('click').on('click', function() {
            const loanId = $(this).data('loanId');
            const unitsRequested = $(this).data('unitsRequested');
            const equipmentId = $(this).data('equipmentId');
            currentLoanId = loanId;

            // Fetch current equipment availability
            fetch(`/equipment/loans/check-availability?equipment_id=${equipmentId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const unitsGroup = $('.units-group');
                const equipmentInfo = $('.equipment-info');
                const availableUnits = $('.available-units');
                const unitsInput = $('input[name="units_delivered"]');

                equipmentInfo.html(`
                    <div class="alert alert-info">
                        <strong>Unidades Solicitadas:</strong> ${unitsRequested}<br>
                        <strong>Unidades Disponibles:</strong> ${data.available_units}
                    </div>
                `);

                if (data.available_units > 0) {
                    unitsGroup.removeClass('d-none');
                    availableUnits.text(data.available_units);
                    unitsInput.attr('max', Math.min(data.available_units, unitsRequested));
                    unitsInput.val(Math.min(data.available_units, unitsRequested));
                } else {
                    unitsGroup.addClass('d-none');
                    equipmentInfo.append(`
                        <div class="alert alert-warning">
                            No hay unidades disponibles en inventario
                        </div>
                    `);
                }
                
                $('#deliveryModal').modal('show');
            });
        });

        // Manejador para botones de devolución
        $('.return-btn').off('click').on('click', function() {
            currentLoanId = $(this).data('loanId');
            $('#returnModal').modal('show');
            if (returnPad) {
                returnPad.clear();
            }
        });

        // Formulario de devolución
        $('#returnForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            const signature = returnPad ? returnPad.toDataURL() : '';
            const observations = $(this).find('textarea[name="return_observations"]').val();

            fetch(`/equipment/loans/${currentLoanId}/return`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    signature: signature,
                    return_observations: observations
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#returnModal').modal('hide');
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Equipo devuelto correctamente',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Error al devolver el equipo',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al procesar la devolución',
                    icon: 'error'
                });
            });
        });

        // Manejador para botones de edición
        $('.edit-btn').off('click').on('click', function() {
            const loanId = $(this).data('loanId');
            currentLoanId = loanId;
            
            $('#editModal').modal('show');
            
            fetch(`/equipment/loans/${loanId}/details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const loan = data.data;
                        const form = $('#editLoanForm');
                        form.find('input[name="loan_date"]').val(loan.loan_date);
                        form.find('input[name="start_time"]').val(loan.start_time);
                        form.find('input[name="end_time"]').val(loan.end_time);
                        form.find('input[name="units_requested"]').val(loan.units_requested);
                        form.find('input[name="grade"]').val(loan.grade);
                    }
                });
        });

        // Formulario de edición
        $('#editLoanForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            const formData = {
                loan_date: $(this).find('input[name="loan_date"]').val(),
                start_time: $(this).find('input[name="start_time"]').val(),
                end_time: $(this).find('input[name="end_time"]').val(),
                units_requested: $(this).find('input[name="units_requested"]').val(),
                grade: $(this).find('input[name="grade"]').val(),
            };

            fetch(`/equipment/loans/${currentLoanId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Préstamo actualizado correctamente',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Error al actualizar el préstamo',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al procesar la actualización',
                    icon: 'error'
                });
            });
        });

        // Manejador para botones de eliminación
        $('.delete-btn').off('click').on('click', function() {
            const loanId = $(this).data('loanId');
            
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción eliminará el préstamo de forma permanente",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/equipment/loans/${loanId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Eliminado',
                                'El préstamo ha sido eliminado correctamente',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'No se pudo eliminar el préstamo'
                            });
                        }
                    });
                }
            });
        });

        // Formulario de entrega
        $('#deliveryForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            const signature = deliveryPad ? deliveryPad.toDataURL() : '';
            const observations = $(this).find('textarea[name="delivery_observations"]').val();
            const unitsDelivered = $(this).find('input[name="units_delivered"]').val();

            fetch(`/equipment/loans/${currentLoanId}/deliver`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    signature: signature,
                    delivery_observations: observations,
                    units_delivered: unitsDelivered
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#deliveryModal').modal('hide');
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Equipo entregado correctamente',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Error al entregar el equipo',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al procesar la entrega',
                    icon: 'error'
                });
            });
        });

        // Reinicializar tooltips
        $('[data-toggle="tooltip"]').tooltip({
            html: true,
            container: 'body'
        });
    }
});
</script>
@stop