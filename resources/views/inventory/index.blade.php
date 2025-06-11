@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Inventario de Almacén</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    @can('inventario.create')
                    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                    @endcan
                    @can('inventario.import')
                    <a href="{{ route('inventory.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Importar
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
                {{ session('error') }}
            </div>
        @endif

        @if (session('importErrors'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Errores de importación</h5>
                <ul>
                    @foreach(session('importErrors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Panel de Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros de búsqueda</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <form action="{{ route('inventory.index') }}" method="GET" id="filter-form">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="producto">Nombre del Producto</label>
                                        <input type="text" class="form-control" id="producto" name="producto" 
                                            value="{{ request('producto') }}" placeholder="Buscar por nombre">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="stock_status">Estado del Stock</label>
                                        <select class="form-control" id="stock_status" name="stock_status">
                                            <option value="">Todos</option>
                                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>
                                                Stock Bajo (Requiere Compra)
                                            </option>
                                            <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>
                                                Stock Suficiente
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Ordenar por</label>
                                        <div class="input-group">
                                            <select class="form-control" id="sort_by" name="sort_by">
                                                <option value="">Más recientes primero</option>
                                                <option value="producto" {{ request('sort_by') == 'producto' ? 'selected' : '' }}>
                                                    Nombre del Producto
                                                </option>
                                                <option value="cantidad_sugerida" {{ request('sort_by') == 'cantidad_sugerida' ? 'selected' : '' }}>
                                                    Cantidad Sugerida
                                                </option>
                                                <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>
                                                    Stock Actual
                                                </option>
                                            </select>
                                            <div class="input-group-append">
                                                <select class="form-control" id="sort_dir" name="sort_dir">
                                                    <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>
                                                        Ascendente
                                                    </option>
                                                    <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>
                                                        Descendente
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('inventory.index') }}" class="btn btn-default">
                                        <i class="fas fa-sync-alt"></i> Limpiar filtros
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Inventario -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Listado de productos en inventario</h3>
                        
                        <!-- Badge para mostrar el conteo de productos con stock bajo -->
                        @php
                            $lowStockCount = $inventoryItems->filter(function($item) {
                                return $item->stock < $item->cantidad_sugerida;
                            })->count();
                        @endphp
                        
                        @if($lowStockCount > 0)
                            <span class="badge badge-danger ml-2">{{ $lowStockCount }} productos con stock bajo</span>
                        @endif
                        
                        <!-- Badge para mostrar filtros activos -->
                        @if(request()->anyFilled(['producto', 'stock_status', 'sort_by']))
                            <span class="badge badge-info ml-2">Filtros activos</span>
                        @endif
                    </div>
                    <!-- ./card-header -->
                    <div class="card-body">
                        <table id="inventory-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Cantidad Sugerida</th>
                                    <th>Stock</th>
                                    <th>Sobre Stock</th>
                                    <th>Cantidad a Comprar</th>
                                    <th>Última Actualización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventoryItems as $item)
                                <tr class="{{ $item->stock < $item->cantidad_sugerida ? 'text-danger' : '' }}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->producto }}</td>
                                    <td>{{ $item->cantidad_sugerida }}</td>
                                    <td>{{ $item->stock }}</td>
                                    <td>{{ $item->sobre_stock }}</td>
                                    <td>{{ $item->cantidad_comprar }}</td>
                                    <td>{{ $item->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-sm btn-info" title="Ver detalles y movimientos">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('inventario.edit')
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-sm btn-default" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('inventario.delete')
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" style="display: inline"
                                                onsubmit="return confirm('¿Está seguro que desea eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#inventory-table').DataTable({
                "responsive": true, 
                "lengthChange": true, 
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json"
                },
                "columns": [
                    { "width": "5%" },
                    { "width": "25%" },
                    { "width": "12%" },
                    { "width": "10%" },
                    { "width": "10%" },
                    { "width": "12%" },
                    { "width": "15%" },
                    { "width": "11%" }
                ]
            });

            // Si hay filtros activos, expandir el panel de filtros
            @if(request()->anyFilled(['producto', 'stock_status', 'sort_by']))
                $('.card-header button[data-card-widget="collapse"]').trigger('click');
            @endif
        });
    </script>
@stop