@extends('adminlte::page')

@section('title', 'Detalles del Ciclo Escolar')

@section('content_header')
    <h1>Detalles del Ciclo Escolar</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">{{ $schoolCycle->name }}</h3>
                <div>
                    <a href="{{ route('school-cycles.edit', $schoolCycle) }}" class="btn btn-warning mr-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('school-cycles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
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

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información del Ciclo</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->id }}</dd>
                                
                                <dt class="col-sm-4">Nombre:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->name }}</dd>
                                
                                <dt class="col-sm-4">Descripción:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->description ?? 'No disponible' }}</dd>
                                
                                <dt class="col-sm-4">Fecha de inicio:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->start_date->format('d/m/Y') }}</dd>
                                
                                <dt class="col-sm-4">Longitud de ciclo:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->cycle_length }} días</dd>
                                
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    @if($schoolCycle->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Creado:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->created_at->format('d/m/Y H:i') }}</dd>
                                
                                <dt class="col-sm-4">Última actualización:</dt>
                                <dd class="col-sm-8">{{ $schoolCycle->updated_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Generación de Días de Ciclo</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Total de días generados:</strong> {{ $cycleDays->count() }}</p>
                            <p><strong>Rango de fechas:</strong>
                                @if($cycleDays->count() > 0)
                                    {{ $cycleDays->first()->date->format('d/m/Y') }} al {{ $cycleDays->last()->date->format('d/m/Y') }}
                                @else
                                    No hay días generados
                                @endif
                            </p>
                            
                            <form action="{{ route('school-cycles.generate-days', $schoolCycle) }}" method="POST" class="mt-3">
                                @csrf
                                <div class="form-group">
                                    <label for="end_date">Generar días hasta:</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" required
                                           value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="reset_days" name="reset_days">
                                        <label class="custom-control-label" for="reset_days">Reiniciar días (elimina todos los días existentes)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync"></i> Generar Días de Ciclo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Días del Ciclo</h5>
                </div>
                <div class="card-body">
                    @if($cycleDays->count() > 0)
                        <div class="table-responsive">
                            <table id="cycle-days-table" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Día de la Semana</th>
                                        <th>Día del Ciclo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cycleDays->take(100) as $cycleDay)
                                        <tr>
                                            <td>{{ $cycleDay->date->format('d/m/Y') }}</td>
                                            <td>{{ $cycleDay->date->locale('es')->isoFormat('dddd') }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    Día {{ $cycleDay->cycle_day }} de {{ $schoolCycle->cycle_length }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($cycleDays->count() > 100)
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Se muestran solo los primeros 100 días. El ciclo tiene un total de {{ $cycleDays->count() }} días generados.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No hay días de ciclo generados. Use el formulario de generación para crear los días.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable para los días de ciclo
            $('#cycle-days-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[0, "asc"]],
                "pageLength": 25
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
    </script>
@stop