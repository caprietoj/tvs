@extends('adminlte::page')

@section('title', 'Gestión de Cotizaciones')

@section('content_header')
    <h1>Gestión de Cotizaciones</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
    <style>
        /* Mejoras para la tabla responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table th,
        .table td {
            white-space: nowrap;
            vertical-align: middle;
        }
        
        /* Botones más compactos en móvil */
        @media (max-width: 768px) {
            .btn-group-vertical .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
            
            .table th,
            .table td {
                font-size: 0.875rem;
                padding: 0.5rem 0.25rem;
            }
        }
        
        /* Mejorar visualización de la paginación */
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
        }
        
        /* Mejorar el filtro de DataTables */
        .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dataTables_filter input {
            margin-left: 0.5rem;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        
        /* Evitar que los badges se rompan */
        .badge {
            white-space: nowrap;
        }
        
        /* Asegurar que los tooltips funcionen */
        [title] {
            cursor: help;
        }
        
        /* Estilos para los filtros */
        .form-label-sm {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #495057;
        }
        
        .card-header.bg-light {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6;
        }
        
        .filter-section {
            transition: all 0.3s ease;
        }
        
        .filter-section.collapsed {
            display: none;
        }
        
        /* Mejorar el diseño responsive de filtros */
        @media (max-width: 768px) {
            .form-group {
                margin-bottom: 0.75rem;
            }
            
            .d-flex {
                flex-direction: column;
            }
            
            .d-flex .btn {
                margin-bottom: 0.25rem;
                margin-right: 0 !important;
            }
        }
    </style>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Solicitudes de Compra Pendientes de Cotización</h3>
        </div>
        
        <!-- Filtros -->
        <div class="card-header bg-light filter-section" id="filtersSection">
            <h5 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h5>
            <form method="GET" action="{{ route('quotations.index') }}" id="filtersForm">
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label for="status" class="form-label-sm">Estado</label>
                            <select class="form-control form-control-sm" name="status" id="status">
                                <option value="all" {{ $statusFilter === 'all' || !$statusFilter ? 'selected' : '' }}>Todos</option>
                                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="En Cotización" {{ $statusFilter === 'En Cotización' ? 'selected' : '' }}>En Cotización</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label for="type" class="form-label-sm">Tipo</label>
                            <select class="form-control form-control-sm" name="type" id="type">
                                <option value="all" {{ $typeFilter === 'all' || !$typeFilter ? 'selected' : '' }}>Todos</option>
                                <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>Compras</option>
                                <option value="services" {{ $typeFilter === 'services' ? 'selected' : '' }}>Servicios</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="section" class="form-label-sm">Sección</label>
                            <select class="form-control form-control-sm" name="section" id="section">
                                <option value="all">Todas las secciones</option>
                                @foreach($allSections as $section)
                                    <option value="{{ $section }}" {{ $sectionFilter === $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="requester" class="form-label-sm">Solicitante</label>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="requester" 
                                   id="requester" 
                                   placeholder="Buscar por solicitante..."
                                   value="{{ $requesterFilter }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label for="date_from" class="form-label-sm">Desde</label>
                            <input type="date" 
                                   class="form-control form-control-sm" 
                                   name="date_from" 
                                   id="date_from"
                                   value="{{ $dateFromFilter }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label for="date_to" class="form-label-sm">Hasta</label>
                            <input type="date" 
                                   class="form-control form-control-sm" 
                                   name="date_to" 
                                   id="date_to"
                                   value="{{ $dateToFilter }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label-sm">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm mr-2">
                                    <i class="fas fa-search mr-1"></i>Buscar
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm mr-2" id="clearFilters">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </button>
                                <button type="button" class="btn btn-info btn-sm" id="toggleFilters">
                                    <i class="fas fa-eye mr-1"></i>Ocultar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label-sm">&nbsp;</label>
                            <div class="text-right">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Mostrando {{ $purchaseRequests->count() }} de {{ $purchaseRequests->total() }} resultados
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="quotationsTable">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">Solicitud</th>
                            <th style="min-width: 150px;">Solicitante</th>
                            <th style="min-width: 130px;">Sección/Área</th>
                            <th style="min-width: 100px;">Fecha</th>
                            <th style="min-width: 100px;">Estado</th>
                            <th style="min-width: 120px;">Cotizaciones</th>
                            <th style="min-width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseRequests as $request)
                            <tr>
                                <td><span class="text-nowrap">{{ $request->request_number }}</span></td>
                                <td><span title="{{ $request->requester }}">{{ Str::limit($request->requester, 20) }}</span></td>
                                <td><span title="{{ $request->section_area }}">{{ Str::limit($request->section_area, 15) }}</span></td>
                                <td><span class="text-nowrap">{{ $request->request_date->format('d/m/Y') }}</span></td>
                                <td><span class="badge badge-secondary">{{ $request->status }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $request->hasRequiredQuotations() ? 'success' : 'warning' }}">
                                        {{ $request->getQuotationProgress() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm d-block d-md-none">
                                        <a href="{{ route('purchase-requests.show', $request->id) }}" class="btn btn-info btn-sm mb-1">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        @if (!$request->hasRequiredQuotations())
                                            <a href="{{ route('quotations.create', $request->id) }}" class="btn btn-primary btn-sm mb-1">
                                                <i class="fas fa-plus"></i> Agregar
                                            </a>
                                            @if ($request->quotations->count() > 0)
                                                <a href="{{ route('quotations.ask-for-more', $request->id) }}" class="btn btn-warning btn-sm mb-1">
                                                    <i class="fas fa-question-circle"></i> Más
                                                </a>
                                            @endif
                                        @endif
                                        @if(in_array($request->status, ['pending', 'En Cotización']))
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#cancelDescriptionModal"
                                                    data-request-id="{{ $request->id }}"
                                                    data-request-number="{{ $request->request_number }}"
                                                    title="Anular por falta de descripción">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        @if($request->status === 'En Cotización')
                                            <a href="{{ route('quotations.mark-completed', $request->id) }}" 
                                               class="btn btn-success btn-sm mb-1"
                                               onclick="return confirm('¿Está seguro de marcar esta solicitud como hecho cumplido y enviarla a preaprobación?')"
                                               title="Hecho Cumplido - Enviar a Preaprobación">
                                                <i class="fas fa-check-circle"></i> Cumplido
                                            </a>
                                        @endif
                                    </div>
                                    <div class="btn-group d-none d-md-flex">
                                        <a href="{{ route('purchase-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        @if (!$request->hasRequiredQuotations())
                                            <a href="{{ route('quotations.create', $request->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Agregar
                                            </a>
                                            @if ($request->quotations->count() > 0)
                                                <a href="{{ route('quotations.ask-for-more', $request->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-question-circle"></i> Más
                                                </a>
                                            @endif
                                        @endif
                                        @if(in_array($request->status, ['pending', 'En Cotización']))
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-toggle="modal" 
                                                    data-target="#cancelDescriptionModal"
                                                    data-request-id="{{ $request->id }}"
                                                    data-request-number="{{ $request->request_number }}"
                                                    title="Anular por falta de descripción">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        @if(in_array($request->status, ['pending', 'En Cotización']))
                                            <a href="{{ route('quotations.mark-completed', $request->id) }}" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('¿Está seguro de marcar esta solicitud como hecho cumplido y enviarla a preaprobación?')"
                                               title="Hecho Cumplido - Enviar a Preaprobación">
                                                <i class="fas fa-check-circle"></i> Cumplido
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay solicitudes de compra pendientes de cotización.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación de Laravel -->
            @if($purchaseRequests->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    <nav aria-label="Paginación de cotizaciones">
                        {{ $purchaseRequests->links('pagination::bootstrap-4') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para anular por falta de descripción -->
    <div class="modal fade" id="cancelDescriptionModal" tabindex="-1" role="dialog" aria-labelledby="cancelDescriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="cancelDescriptionModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Anular solicitud por falta de descripción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cancelDescriptionForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Importante:</strong> Esta acción anulará la solicitud <span id="modalRequestNumber"></span> 
                            y notificará al solicitante que debe proporcionar una descripción más detallada.
                        </div>
                        
                        <div class="form-group">
                            <label for="cancel_reason">Motivo de la anulación *</label>
                            <textarea name="reason" id="cancel_reason" class="form-control" rows="4" 
                                      placeholder="Explique específicamente qué información adicional necesita en la descripción..." 
                                      maxlength="500" required></textarea>
                            <small class="form-text text-muted">
                                Máximo 500 caracteres. Sea específico sobre qué información falta.
                            </small>
                            <div class="text-right">
                                <small class="text-muted">
                                    <span id="char-count">0</span>/500 caracteres
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_cancel" required>
                                <label class="custom-control-label" for="confirm_cancel">
                                    Confirmo que he revisado la solicitud y considero que necesita más información
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning" id="confirmCancelBtn" disabled>
                            <i class="fas fa-ban"></i> Anular solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(function() {
            // Usar DataTables solo para búsqueda y ordenamiento, no para paginación
            $('#quotationsTable').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "dom": 'ft', // Solo mostrar filtro y tabla
                "language": {
                    "url": "{{ asset('vendor/datatables/i18n/Spanish.json') }}",
                    "search": "Buscar:",
                    "searchPlaceholder": "Filtrar registros..."
                },
                "columnDefs": [
                    {
                        "targets": [6], // Columna de acciones
                        "orderable": false,
                        "searchable": false
                    }
                ]
            });

            // Manejo del modal de anulación por descripción
            $('#cancelDescriptionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var requestId = button.data('request-id');
                var requestNumber = button.data('request-number');
                
                var modal = $(this);
                modal.find('#modalRequestNumber').text('#' + requestNumber);
                modal.find('#cancelDescriptionForm').attr('action', '/quotations/cancel-description/' + requestId);
            });

            // Contador de caracteres
            $('#cancel_reason').on('input', function() {
                const length = $(this).val().length;
                $('#char-count').text(length);
                
                if (length > 500) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
                
                // Verificar si se puede habilitar el botón
                const isChecked = $('#confirm_cancel').is(':checked');
                const hasReason = $(this).val().trim().length > 0;
                $('#confirmCancelBtn').prop('disabled', !(isChecked && hasReason));
            });

            // Habilitar/deshabilitar botón de confirmación
            $('#confirm_cancel').on('change', function() {
                const isChecked = $(this).is(':checked');
                const hasReason = $('#cancel_reason').val().trim().length > 0;
                $('#confirmCancelBtn').prop('disabled', !(isChecked && hasReason));
            });

            // Confirmación antes de enviar anulación
            $('#cancelDescriptionForm').on('submit', function(e) {
                e.preventDefault();
                
                const reason = $('#cancel_reason').val().trim();
                if (reason.length === 0) {
                    alert('Por favor, proporcione un motivo para la anulación.');
                    return false;
                }
                
                if (reason.length > 500) {
                    alert('El motivo no puede exceder 500 caracteres.');
                    return false;
                }
                
                if (!$('#confirm_cancel').is(':checked')) {
                    alert('Debe confirmar que ha revisado la solicitud.');
                    return false;
                }
                
                if (confirm('¿Está seguro de que desea anular esta solicitud por falta de descripción? Esta acción no se puede deshacer.')) {
                    $('#confirmCancelBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                    this.submit();
                }
            });

            // Limpiar modal al cerrar
            $('#cancelDescriptionModal').on('hidden.bs.modal', function () {
                $('#cancel_reason').val('');
                $('#char-count').text('0');
                $('#confirm_cancel').prop('checked', false);
                $('#confirmCancelBtn').prop('disabled', true).html('<i class="fas fa-ban"></i> Anular solicitud');
                $('#cancel_reason').removeClass('is-invalid');
            });

            // === FUNCIONALIDAD DE FILTROS ===
            
            // Auto-submit del formulario cuando cambian los filtros principales
            $('#status, #section, #type').on('change', function() {
                $('#filterForm').submit();
            });
            
            // Función para limpiar todos los filtros
            $('#clearFilters').on('click', function() {
                $('#status, #section, #type').val('all');
                $('#requester').val('');
                $('#date_from, #date_to').val('');
                updateFilterCount();
                $('#filterForm').submit();
            });
            
            // Alternar visibilidad de filtros
            $('#toggleFilters').on('click', function() {
                $('#filtersSection').toggleClass('collapsed');
                const icon = $(this).find('i');
                const text = $(this).contents().filter(function() {
                    return this.nodeType === 3; // Text node
                }).first();
                
                if (icon.hasClass('fa-eye')) {
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    text[0].textContent = 'Mostrar';
                } else {
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    text[0].textContent = 'Ocultar';
                }
            });
            
            // Validación de fechas
            $('#date_from, #date_to').on('change', function() {
                const dateFrom = $('#date_from').val();
                const dateTo = $('#date_to').val();
                
                if (dateFrom && dateTo && dateFrom > dateTo) {
                    alert('La fecha de inicio no puede ser mayor que la fecha de fin');
                    $(this).val('');
                }
            });
            
            // Aplicar filtros con Enter en campos de texto
            $('#requester').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#filterForm').submit();
                }
            });
            
            // Mostrar contador de filtros activos
            function updateFilterCount() {
                let activeFilters = 0;
                const inputs = ['#status', '#section', '#type', '#requester', '#date_from', '#date_to'];
                
                inputs.forEach(selector => {
                    const val = $(selector).val();
                    if (val && val !== 'all' && val !== '') {
                        activeFilters++;
                    }
                });
                
                const filterTitle = $('h5:contains("Filtros de Búsqueda")');
                if (activeFilters > 0) {
                    const countText = filterTitle.find('.filter-count');
                    if (countText.length > 0) {
                        countText.text(`(${activeFilters} activos)`);
                    } else {
                        filterTitle.append(`<span class="filter-count text-primary ml-2">(${activeFilters} activos)</span>`);
                    }
                } else {
                    filterTitle.find('.filter-count').remove();
                }
            }
            
            // Actualizar contador al cargar la página y cuando cambien los filtros
            updateFilterCount();
            $('#status, #section, #type, #requester, #date_from, #date_to').on('change keyup', updateFilterCount);
        });
    </script>
@stop