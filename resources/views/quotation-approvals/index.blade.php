@extends('adminlte::page')

@section('title', 'Preaprobación de Cotizaciones')

@section('adminlte_css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <h1 class="m-0 text-dark">Preaprobación de Cotizaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">Solicitudes Pendientes de Preaprobación</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
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

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-12">
                        <form method="GET" action="{{ route('quotation-approvals.index') }}" class="form-inline" id="filter-form">
                            <div class="form-group mr-3">
                                <label for="request_number" class="mr-2"><strong>No. Solicitud:</strong></label>
                                <input type="text" name="request_number" id="request_number" class="form-control" 
                                       value="{{ $requestNumberFilter ?? '' }}" placeholder="Buscar por número...">
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
                                <a href="{{ route('quotation-approvals.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Indicadores de filtros activos -->
                <div class="row mb-2">
                    <div class="col-md-12">
                        @if($sectionFilter || $requestNumberFilter)
                            <div class="mb-2">
                                <strong>Filtros activos:</strong>
                                @if($requestNumberFilter)
                                    <span class="badge badge-primary mr-1">
                                        No. Solicitud: {{ $requestNumberFilter }}
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

                <!-- Contenedor que se actualizará con AJAX -->
                <div id="table-container">
                    @include('quotation-approvals.partials.table')
                </div>

                <div class="mt-3" id="pagination-container">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
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
                url: '{{ route("quotation-approvals.search") }}',
                type: 'GET',
                data: formData,
                beforeSend: function() {
                    $('#table-container').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Buscando...</small></div>');
                },
                success: function(response) {
                    $('#table-container').html(response.html);
                    $('#pagination-container').html(response.pagination);
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
        $('#section').on('change', function() {
            performAjaxSearch();
        });
        
        // Manejar envío del formulario para evitar recarga de página
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            performAjaxSearch();
        });
    });
</script>
@stop