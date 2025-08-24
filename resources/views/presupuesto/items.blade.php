@extends('adminlte::page')

@section('title', 'Presupuesto - Items Procesados')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-excel text-success"></i>
                        Procesador de Presupuesto Excel
                    </h1>
                    <small class="text-muted">Gestión y análisis de datos presupuestarios</small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/home">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presupuesto.index') }}">Presupuesto</a></li>
                        <li class="breadcrumb-item active">Items Procesados</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['total_registros']) }}</h3>
                    <p>Total Registros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($estadisticas['total_valor'], 0) }}</h3>
                    <p>Valor Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['por_seccion']->count() }}</h3>
                    <p>Secciones Activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['por_rubro']->count() }}</h3>
                    <p>Rubros Diferentes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Controles y Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title">
                                <i class="fas fa-table"></i>
                                Datos Procesados
                            </h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                                <i class="fas fa-upload"></i> Subir Archivo Excel
                            </button>
                            <a href="{{ route('presupuesto.exportItems') }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Exportar Excel
                            </a>
                            <button class="btn btn-warning" data-toggle="modal" data-target="#clearModal">
                                <i class="fas fa-trash"></i> Limpiar Datos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card-body">
                    <form method="GET" action="{{ route('presupuesto.items') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="seccion">Sección:</label>
                                <select name="seccion" id="seccion" class="form-control">
                                    <option value="">Todas las secciones</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion }}" {{ request('seccion') == $seccion ? 'selected' : '' }}>
                                            {{ $seccion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="rubro">Rubro:</label>
                                <select name="rubro" id="rubro" class="form-control">
                                    <option value="">Todos los rubros</option>
                                    @foreach($rubros as $rubro)
                                        <option value="{{ $rubro }}" {{ request('rubro') == $rubro ? 'selected' : '' }}>
                                            {{ $rubro }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="fecha_inicio">Fecha Inicio:</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                                       value="{{ request('fecha_inicio') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="fecha_fin">Fecha Fin:</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                                       value="{{ request('fecha_fin') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla tipo Excel -->
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-hover" id="presupuesto-table">
                            <thead class="thead-dark sticky-top">
                                <tr>
                                    <th width="80">Fuente</th>
                                    <th width="100">Documento</th>
                                    <th width="100">Fecha</th>
                                    <th width="120">Cuenta</th>
                                    <th width="150">Sección</th>
                                    <th width="120">Rubro</th>
                                    <th>Descripción</th>
                                    <th width="120">Valor</th>
                                    <th width="120">Centro Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr class="{{ $item->es_total ? 'table-warning font-weight-bold' : '' }}">
                                        <td>{{ $item->fuente }}</td>
                                        <td>{{ $item->documento }}</td>
                                        <td>{{ $item->fecha?->format('Y-m-d') }}</td>
                                        <td>{{ $item->cuenta }}</td>
                                        <td>
                                            @if($item->seccion)
                                                <span class="badge badge-{{ $item->color_seccion }}">{{ $item->seccion }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $item->rubro ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $item->descripcion }}</td>
                                        <td class="text-right">
                                            @if($item->es_total)
                                                <strong class="text-success">${{ number_format($item->valor, 0) }}</strong>
                                            @else
                                                ${{ number_format($item->valor, 0) }}
                                            @endif
                                        </td>
                                        <td>{{ $item->centro_costo }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                            No hay datos procesados. <br>
                                            <small>Sube un archivo Excel para comenzar.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    @if($items->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $items->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Subida -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('presupuesto.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-upload"></i> Subir Archivo Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_file">Seleccionar archivo Excel:</label>
                        <input type="file" class="form-control-file" id="excel_file" name="excel_file" 
                               accept=".xlsx,.xls" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i>
                            Formatos permitidos: .xlsx, .xls (Máximo 10MB)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="replace_existing" 
                                   name="replace_existing" value="1">
                            <label class="custom-control-label" for="replace_existing">
                                <strong>Reemplazar datos existentes</strong>
                            </label>
                            <small class="form-text text-muted">
                                Si está marcado, se eliminarán todos los datos actuales antes de importar los nuevos.
                            </small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Importante:</strong> El archivo debe tener las columnas en el siguiente orden:
                        Fuente, Documento, Fecha, Cuenta, Descripción, Valor, Valor Moneda, Cliente/Proveedor, 
                        Nombre Cliente/Proveedor, Tercero, Nombre Tercero, Auxiliar, Centro Costo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Procesar Archivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Limpiar -->
<div class="modal fade" id="clearModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Limpieza
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>¿Está seguro de que desea eliminar todos los datos procesados?</strong></p>
                <p class="text-muted">Esta acción no se puede deshacer. Se eliminarán todos los registros de la base de datos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form action="{{ route('presupuesto.clearData') }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-trash"></i> Sí, Eliminar Todo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .table th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40 !important;
    }
    
    .small-box .inner h3 {
        font-size: 2.2rem;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .alert {
        border-left: 4px solid;
    }
    
    .alert-success {
        border-left-color: #28a745;
    }
    
    .alert-danger {
        border-left-color: #dc3545;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Form validation
    $('#uploadModal form').on('submit', function(e) {
        const fileInput = $('#excel_file')[0];
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Por favor seleccione un archivo Excel.');
            return false;
        }
        
        const file = fileInput.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            alert('El archivo es demasiado grande. El tamaño máximo es 10MB.');
            return false;
        }
        
        // Show loading
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    });
});
</script>
@stop
