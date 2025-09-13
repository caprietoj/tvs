@extends('adminlte::page')

@section('title', 'Solicitudes de Compra y Materiales')

@section('adminlte_css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Solicitudes de Compra y Materiales</h1>
        <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Solicitud
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Solicitudes</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    @if(session('partial_success') && session('out_of_stock_warning'))
                        <br><br>
                        <button type="button" class="btn btn-warning btn-sm" id="showPartialSuccessModal">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Ver productos sin stock
                        </button>
                    @endif
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Modal para éxito parcial -->
            @if(session('partial_success') && session('out_of_stock_warning'))
            <div class="modal fade" id="partialSuccessModal" tabindex="-1" role="dialog" aria-labelledby="partialSuccessModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #ffc107; color: #212529;">
                            <h5 class="modal-title" id="partialSuccessModalLabel">
                                <i class="fas fa-check-circle mr-2"></i>Solicitud Creada con Productos Excluidos
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check mr-2"></i>
                                <strong>¡Solicitud creada exitosamente!</strong> Se guardaron únicamente los productos con stock disponible.
                            </div>
                            <div class="alert alert-warning">
                                <strong>Los siguientes productos fueron excluidos por falta de stock:</strong>
                            </div>
                            <ul class="list-group mb-3">
                                @foreach(session('out_of_stock_warning') as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><strong>{{ $item }}</strong></span>
                                    <span class="badge badge-warning badge-pill">Sin Stock</span>
                                </li>
                                @endforeach
                            </ul>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Recomendación:</strong> Para obtener estos productos, deberá crear una Solicitud de Compra siguiendo el procedimiento establecido.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="{{ route('purchase-requests.create-purchase') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart mr-2"></i>Crear Solicitud de Compra
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('purchase-requests.index') }}" class="form-inline" id="filter-form">
                        <div class="form-group mr-3">
                            <label for="request_number" class="mr-2"><strong>No. Solicitud:</strong></label>
                            <input type="text" name="request_number" id="request_number" class="form-control" 
                                   value="{{ $requestNumberFilter ?? '' }}" placeholder="Buscar por número...">
                        </div>
                        <div class="form-group mr-3">
                            <label for="type" class="mr-2"><strong>Tipo:</strong></label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>
                                    Compra
                                </option>
                                <option value="services" {{ $typeFilter === 'services' ? 'selected' : '' }}>
                                    Servicios
                                </option>
                                <option value="materials" {{ $typeFilter === 'materials' ? 'selected' : '' }}>
                                    Materiales
                                </option>
                                <option value="copies" {{ $typeFilter === 'copies' ? 'selected' : '' }}>
                                    Fotocopias
                                </option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="section" class="mr-2"><strong>Área/Sección:</strong></label>
                            <select name="section" id="section" class="form-control">
                                <option value="all">Todas las secciones</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section }}" {{ $sectionFilter === $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Indicadores de filtros activos -->
            <div class="row mb-2">
                <div class="col-md-12">
                    @if($typeFilter || $sectionFilter || $requestNumberFilter)
                        <div class="mb-2">
                            <strong>Filtros activos:</strong>
                            @if($requestNumberFilter)
                                <span class="badge badge-primary mr-1">
                                    No. Solicitud: {{ $requestNumberFilter }}
                                </span>
                            @endif
                            @if($typeFilter)
                                <span class="badge badge-info mr-1">
                                    Tipo: 
                                    @if($typeFilter === 'purchase') Compras
                                    @elseif($typeFilter === 'services') Servicios
                                    @elseif($typeFilter === 'materials') Materiales
                                    @elseif($typeFilter === 'copies') Fotocopias
                                    @endif
                                </span>
                            @endif
                            @if($sectionFilter && $sectionFilter !== 'all')
                                <span class="badge badge-success">
                                    Sección: {{ $sectionFilter }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Botón de aprobación masiva para fotocopias -->
            @if($canBulkApproveCopies)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Aprobación masiva disponible:</strong> 
                                Hay {{ $copiesCount }} solicitudes de fotocopias pendientes en la sección "{{ $sectionFilter }}"
                            </div>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#bulkApproveModal">
                                <i class="fas fa-check-double mr-1"></i>
                                Aprobar todas las fotocopias
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Contenedor que se actualizará con AJAX -->
            <div id="table-container">
                @include('purchase-requests.partials.table', [
                    'requests' => $requests,
                    'typeFilter' => $typeFilter,
                    'sectionFilter' => $sectionFilter,
                    'canBulkApproveCopies' => $canBulkApproveCopies,
                    'copiesCount' => $copiesCount
                ])
            </div>

            <div class="mt-3 d-flex justify-content-center" id="pagination-container">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar esta solicitud?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .badge {
        font-size: 90%;
    }
    
    /* Estilos para la paginación */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #3490dc;
        border-color: #3490dc;
    }
    
    .page-link {
        color: #3490dc;
    }
    
    .page-link:hover {
        color: #1d68a7;
    }
    
    /* Asegurar que la tabla sea responsive */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ajustar el ancho de las columnas */
    #requestsTable th:nth-child(1), 
    #requestsTable td:nth-child(1) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(2), 
    #requestsTable td:nth-child(2) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(3), 
    #requestsTable td:nth-child(3) {
        width: 20%;
    }
    
    #requestsTable th:nth-child(4), 
    #requestsTable td:nth-child(4) {
        width: 20%;
    }
    
    #requestsTable th:nth-child(5), 
    #requestsTable td:nth-child(5) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(6), 
    #requestsTable td:nth-child(6) {
        width: 10%;
    }
    
    #requestsTable th:nth-child(7), 
    #requestsTable td:nth-child(7) {
        width: 20%;
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // Configurar token CSRF para todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Variables para debounce
        let searchTimeout;
        
        // Función para realizar búsqueda AJAX
        function performAjaxSearch() {
            const formData = $('#filter-form').serialize();
            
            $.ajax({
                url: '{{ route("purchase-requests.search") }}',
                type: 'GET',
                data: formData,
                beforeSend: function() {
                    $('#table-container').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Buscando...</small></div>');
                },
                success: function(response) {
                    $('#table-container').html(response.html);
                    $('#pagination-container').html(response.pagination);
                    
                    // Reinicializar popovers después de actualizar la tabla
                    $('[data-toggle="popover"]').popover({
                        html: true,
                        container: 'body'
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText);
                    let errorMessage = 'Error al realizar la búsqueda. Por favor, intente nuevamente.';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Si no es JSON, usar el mensaje por defecto
                    }
                    
                    if (xhr.status === 401) {
                        errorMessage = 'Sesión expirada. Por favor, recargue la página e inicie sesión nuevamente.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'No tiene permisos para realizar esta acción.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error interno del servidor. Por favor, contacte al administrador.';
                    }
                    
                    $('#table-container').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>' + errorMessage + '</div>');
                }
            });
        }
        
        // Búsqueda en tiempo real para el campo de número de solicitud
        $('#request_number').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performAjaxSearch();
            }, 500); // Esperar 500ms después de que el usuario deje de escribir
        });
        
        // Búsqueda inmediata para selects
        $('#type, #section').on('change', function() {
            performAjaxSearch();
        });
        
        // Manejar clics en los enlaces de paginación
        $(document).on('click', '#pagination-container .page-link', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            
            if (url) {
                // Extraer los parámetros de la URL
                const urlParams = new URLSearchParams(url.split('?')[1]);
                const page = urlParams.get('page');
                
                // Agregar el número de página al formulario actual
                const formData = $('#filter-form').serialize() + (page ? '&page=' + page : '');
                
                $.ajax({
                    url: '{{ route("purchase-requests.search") }}',
                    type: 'GET',
                    data: formData,
                    beforeSend: function() {
                        $('#table-container').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Cargando...</small></div>');
                    },
                    success: function(response) {
                        $('#table-container').html(response.html);
                        $('#pagination-container').html(response.pagination);
                        
                        // Reinicializar popovers después de actualizar la tabla
                        $('[data-toggle="popover"]').popover({
                            html: true,
                            container: 'body'
                        });
                        
                        // Scroll hacia arriba para ver los resultados
                        $('html, body').animate({
                            scrollTop: $('#table-container').offset().top - 100
                        }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX en paginación:', xhr.responseText);
                        let errorMessage = 'Error al cargar la página. Por favor, intente nuevamente.';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            // Si no es JSON, usar el mensaje por defecto
                        }
                        
                        $('#table-container').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>' + errorMessage + '</div>');
                    }
                });
            }
        });
        
        // Manejar envío del formulario para evitar recarga de página
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            performAjaxSearch();
        });
        
        // Modal de eliminación
        $(document).on('show.bs.modal', '#deleteModal', function (event) {
            const button = $(event.relatedTarget);
            const requestId = button.data('request-id');
            const form = document.getElementById('deleteForm');
            form.action = `/purchase-requests/${requestId}`;
        });
        
        // Inicializar popovers para observaciones
        $('[data-toggle="popover"]').popover({
            html: true,
            container: 'body'
        });
        
        // Manejar modal de éxito parcial
        $('#showPartialSuccessModal').on('click', function() {
            $('#partialSuccessModal').modal('show');
        });
        
        // Si hay un éxito parcial, mostrar automáticamente después de 2 segundos
        @if(session('partial_success'))
            setTimeout(function() {
                $('#partialSuccessModal').modal('show');
            }, 2000);
        @endif
        
        // Verificar si hay parámetros de filtro en la URL al cargar la página
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('type') || urlParams.has('section') || urlParams.has('request_number')) {
            // Sincronizar los filtros con la URL
            if (urlParams.has('type')) {
                $('#type').val(urlParams.get('type'));
            }
            if (urlParams.has('section')) {
                $('#section').val(urlParams.get('section'));
            }
            if (urlParams.has('request_number')) {
                $('#request_number').val(urlParams.get('request_number'));
            }
            
            // Realizar búsqueda automática si hay filtros
            performAjaxSearch();
        }
    });
</script>
@stop