@extends('adminlte::page')

@section('title', 'Consolidado Previsitas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Consolidado Previsitas</h1>
        <a href="{{ route('previsitas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nueva Previsita
        </a>
    </div>
@stop

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header">
        <h3 class="card-title">Filtros de Búsqueda</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="filtros-form" method="GET" action="{{ route('previsitas.index') }}" class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="lugar">
                        Lugar 
                        <i class="fas fa-search text-muted" title="Búsqueda dinámica - escribe al menos 2 caracteres"></i>
                    </label>
                    <div class="autocomplete-container" style="position: relative;">
                        <input type="text" class="form-control" id="lugar" name="lugar" value="{{ request('lugar') }}" placeholder="Buscar por lugar..." autocomplete="off">
                        <div id="lugar-dropdown" class="autocomplete-dropdown" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="responsable">
                        Responsable 
                        <i class="fas fa-search text-muted" title="Búsqueda dinámica - escribe al menos 2 caracteres"></i>
                    </label>
                    <div class="autocomplete-container" style="position: relative;">
                        <input type="text" class="form-control" id="responsable" name="responsable" value="{{ request('responsable') }}" placeholder="Buscar por responsable..." autocomplete="off">
                        <div id="responsable-dropdown" class="autocomplete-dropdown" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="fecha_desde">Fecha Desde</label>
                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="fecha_hasta">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="aprobacion_sitio">Aprobación</label>
                    <select class="form-control" id="aprobacion_sitio" name="aprobacion_sitio">
                        <option value="">Todas</option>
                        <option value="si" {{ request('aprobacion_sitio') == 'si' ? 'selected' : '' }}>Aprobadas</option>
                        <option value="no" {{ request('aprobacion_sitio') == 'no' ? 'selected' : '' }}>No Aprobadas</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="{{ route('previsitas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card custom-card">
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

        <div class="table-responsive">
            <table id="previsitasTable" class="table table-hover">
                <thead class="thead-primary">
                    <tr>
                        <th>Lugar</th>
                        <th>Fecha de Visita</th>
                        <th>Vencimiento</th>
                        <th>Responsable</th>
                        <th>Aprobación del Sitio</th>
                        <th>Archivo PDF</th>
                        <th>Creado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($previsitas as $previsita)
                    <tr>
                        <td>{{ $previsita->lugar }}</td>
                        <td>{{ $previsita->fecha_visita->format('d/m/Y') }}</td>
                        <td>
                            @if($previsita->vencimiento)
                                <span class="badge {{ $previsita->vencimiento < now() ? 'badge-danger' : ($previsita->vencimiento <= now()->addDays(7) ? 'badge-warning' : 'badge-success') }}">
                                    {{ $previsita->vencimiento->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge badge-info">
                                    Sin vencimiento
                                </span>
                            @endif
                        </td>
                        <td>{{ $previsita->responsable }}</td>
                        <td>
                            <span class="badge {{ $previsita->aprobacion_sitio ? 'badge-success' : 'badge-danger' }}">
                                {{ $previsita->aprobacion_sitio ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td>
                            @if($previsita->novedades_visita_archivo)
                                <a href="{{ route('previsitas.download', $previsita) }}" class="btn btn-sm btn-outline-primary" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted">Sin archivo</span>
                            @endif
                        </td>
                        <td>{{ $previsita->user->name ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('previsitas.show', $previsita) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('previsitas.edit', $previsita) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form class="delete-form" action="{{ route('previsitas.destroy', $previsita) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de eliminar esta previsita?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron previsitas registradas.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($previsitas->hasPages())
            <div class="d-flex justify-content-center">
                {{ $previsitas->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .custom-card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }
    
    .thead-primary {
        background-color: #007bff;
        color: white;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.075);
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* Estilos para autocompletado */
    .autocomplete-container {
        position: relative;
    }

    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border-radius: 0 0 4px 4px;
    }

    .autocomplete-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background-color: #007bff;
        color: white;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item.text-muted:hover {
        background-color: #f8f9fa;
        color: #6c757d;
    }

    .autocomplete-item.text-danger:hover {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Estilo mejorado para inputs con autocompletado */
    .autocomplete-container input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Configurar token CSRF para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            xhrFields: {
                withCredentials: true
            }
        });

        // Configurar DataTable
        $('#previsitasTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "responsive": true,
            "autoWidth": false,
            "pageLength": 25,
            "order": [[ 1, "desc" ]], // Cambiado de 2 a 1 porque se quitó la columna ID
            "columnDefs": [
                { "orderable": false, "targets": [5, 7] } // Cambiado de [6, 8] a [5, 7] porque se quitó la columna ID
            ]
        });

        // Función para debounce
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Función para realizar búsqueda de autocompletado (simplificada)
        function performAutocomplete(inputElement, dropdownElement, url) {
            const query = $(inputElement).val();
            
            console.log('🔍 Búsqueda iniciada:', query, 'URL:', url);
            
            if (query.length < 2) {
                $(dropdownElement).hide();
                return;
            }

            // Mostrar indicador de carga
            $(dropdownElement).html('<div class="autocomplete-item text-muted"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();

            // Usar fetch en lugar de jQuery AJAX para mejor debugging
            fetch(url + '?term=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('📡 Respuesta status:', response.status);
                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('✅ Datos recibidos:', data);
                $(dropdownElement).empty().hide();
                
                if (data.length > 0) {
                    data.forEach(function(item) {
                        $(dropdownElement).append(
                            $('<div class="autocomplete-item">').text(item).click(function() {
                                $(inputElement).val(item);
                                $(dropdownElement).hide();
                                $('#filtros-form').submit();
                            })
                        );
                    });
                    $(dropdownElement).show();
                } else {
                    $(dropdownElement).html('<div class="autocomplete-item text-muted">No se encontraron coincidencias</div>').show();
                }
            })
            .catch(error => {
                console.error('❌ Error en la búsqueda:', error);
                $(dropdownElement).html('<div class="autocomplete-item text-danger">Error: ' + error.message + '</div>').show();
            });
        }

        // Crear funciones con debounce para cada campo
        const debouncedLugarSearch = debounce(function() {
            performAutocomplete('#lugar', '#lugar-dropdown', '/previsitas/suggestions/lugares');
        }, 300);

        const debouncedResponsableSearch = debounce(function() {
            performAutocomplete('#responsable', '#responsable-dropdown', '/previsitas/suggestions/responsables');
        }, 300);

        // Autocompletado para Lugar
        $('#lugar').on('input', debouncedLugarSearch);

        // Autocompletado para Responsable
        $('#responsable').on('input', debouncedResponsableSearch);

        // Ocultar dropdowns al hacer clic fuera
        $(document).click(function(e) {
            if (!$(e.target).closest('.autocomplete-container').length) {
                $('.autocomplete-dropdown').hide();
            }
        });

        // Navegación con teclado en dropdowns
        $('.autocomplete-container input').on('keydown', function(e) {
            const $dropdown = $(this).siblings('.autocomplete-dropdown');
            const $items = $dropdown.find('.autocomplete-item');
            const $active = $items.filter('.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if ($active.length === 0) {
                    $items.first().addClass('active');
                } else {
                    $active.removeClass('active').next().addClass('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if ($active.length === 0) {
                    $items.last().addClass('active');
                } else {
                    $active.removeClass('active').prev().addClass('active');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if ($active.length > 0) {
                    $active.click();
                }
            } else if (e.key === 'Escape') {
                $dropdown.hide();
            }
        });

        // Filtro automático en tiempo real para otros campos
        // Nota: Usamos #filtros-form específicamente para evitar conflictos con formularios de eliminación
        $('#fecha_desde, #fecha_hasta, #aprobacion_sitio').on('change', function() {
            $('#filtros-form').submit();
        });

        // Resaltar item activo en hover
        $(document).on('mouseenter', '.autocomplete-item', function() {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
    });
</script>
@stop