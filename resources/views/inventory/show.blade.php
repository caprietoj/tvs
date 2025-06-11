@extends('adminlte::page')

@section('title', 'Detalle de Inventario')

@section('content_header')
    <h1>{{ $inventory->producto }}</h1>
    <div class="mt-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al inventario
        </a>
        @can('inventario.edit')
            <a href="{{ route('inventory.edit', $inventory->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Editar
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Producto</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width:40%">Producto</th>
                            <td>{{ $inventory->producto }}</td>
                        </tr>
                        <tr>
                            <th>Cantidad Sugerida</th>
                            <td>{{ $inventory->cantidad_sugerida }}</td>
                        </tr>
                        <tr>
                            <th>Stock Actual</th>
                            <td>
                                @if($inventory->stock < $inventory->cantidad_sugerida)
                                    <span class="badge badge-danger">{{ $inventory->stock }}</span>
                                @else
                                    <span class="badge badge-success">{{ $inventory->stock }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($inventory->stock < $inventory->cantidad_sugerida)
                                    <span class="badge badge-warning">Stock bajo</span>
                                @else
                                    <span class="badge badge-info">Stock adecuado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Última actualización</th>
                            <td>{{ $inventory->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Registrado por</th>
                            <td>{{ $inventory->user->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @can('inventario.edit')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Devolver Productos al Inventario</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('inventory.return', $inventory->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="cantidad">Cantidad a devolver</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control @error('cantidad') is-invalid @enderror" min="1" required>
                                @error('cantidad')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="solicitante">Solicitante</label>
                                <input type="text" name="solicitante" id="solicitante" class="form-control @error('solicitante') is-invalid @enderror" required>
                                @error('solicitante')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="motivo">Motivo de la devolución</label>
                                <textarea name="motivo" id="motivo" rows="3" class="form-control @error('motivo') is-invalid @enderror" required></textarea>
                                @error('motivo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-reply"></i> Procesar Devolución
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de Movimientos</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Detalle</th>
                        <th>Solicitante</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($movement->tipo_movimiento == 'entrada')
                                    <span class="badge badge-success">Entrada</span>
                                @elseif($movement->tipo_movimiento == 'salida')
                                    <span class="badge badge-danger">Salida</span>
                                @elseif($movement->tipo_movimiento == 'devolucion')
                                    <span class="badge badge-info">Devolución</span>
                                @else
                                    <span class="badge badge-secondary">{{ $movement->tipo_movimiento }}</span>
                                @endif
                            </td>
                            <td>{{ $movement->cantidad }}</td>
                            <td>{{ $movement->detalle }}</td>
                            <td>{{ $movement->solicitante ?? 'N/A' }}</td>
                            <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay movimientos registrados para este producto</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $movements->links() }}
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            background-color: #f4f6f9;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            // Mensaje de éxito
            @if(session('success'))
                Swal.fire({
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            @endif

            // Mensaje de error
            @if(session('error'))
                Swal.fire({
                    title: 'Error',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            @endif
        });
    </script>
@stop