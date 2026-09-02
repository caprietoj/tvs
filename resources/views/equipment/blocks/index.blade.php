@extends('adminlte::page')

@section('title', 'Bloqueos de Equipos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🔒 Bloqueos de Equipos</h1>
        <a href="{{ route('equipment.blocks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Bloqueo
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Bloqueos</h3>
        </div>
        <div class="card-body">
            @if($blocks->count() > 0)

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="blocks-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equipo</th>
                                <th>Ciclo Escolar</th>
                                <th>Tipo</th>
                                <th>Horario</th>
                                <th>Unidades Bloqueadas</th>
                                <th>Razón</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blocks as $block)
                                <tr>
                                    <td>{{ $block->id }}</td>
                                    <td>
                                        <strong>{{ $block->equipment->type }}</strong><br>
                                        <small class="text-muted">{{ $block->equipment->section }}</small>
                                    </td>
                                    <td>{{ $block->schoolCycle->name }}</td>
                                    <td>
                                        @if($block->is_weekday_block)
                                            <span class="badge badge-info">Semanal</span><br>
                                            <small>
                                                @php
                                                    $days = [];
                                                    $dayNames = [
                                                        'monday' => 'Lun',
                                                        'tuesday' => 'Mar',
                                                        'wednesday' => 'Mié',
                                                        'thursday' => 'Jue',
                                                        'friday' => 'Vie',
                                                        'saturday' => 'Sáb',
                                                        'sunday' => 'Dom'
                                                    ];
                                                    foreach($dayNames as $day => $name) {
                                                        if($block->$day) $days[] = $name;
                                                    }
                                                @endphp
                                                {{ implode(', ', $days) }}
                                            </small>
                                        @else
                                            <span class="badge badge-warning">Día de Ciclo</span><br>
                                            <small>Día {{ $block->cycle_day }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($block->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($block->end_time)->format('H:i') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">{{ $block->blocked_units }}</span>
                                        <small class="text-muted">/ {{ $block->equipment->total_units }}</small>
                                    </td>
                                    <td>
                                        {{ $block->reason ?: 'Sin especificar' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('equipment.blocks.show', $block) }}" 
                                               class="btn btn-outline-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('equipment.blocks.edit', $block) }}" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('equipment.blocks.destroy', $block) }}" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar este bloqueo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


            @else
                <div class="text-center py-4">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay bloqueos registrados</h4>
                    <p class="text-muted">Crea tu primer bloqueo de equipos haciendo clic en "Nuevo Bloqueo"</p>
                    <a href="{{ route('equipment.blocks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Bloqueo
                    </a>
                </div>
            @endif
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
            $('#blocks-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "responsive": true,
                "order": [[0, "desc"]]
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@stop
