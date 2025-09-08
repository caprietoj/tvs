@extends('adminlte::page')

@section('title', 'Detalles del Bloqueo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🔒 Detalles del Bloqueo</h1>
        <div>
            <a href="{{ route('equipment.blocks.edit', $equipmentBlock) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('equipment.blocks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Bloqueo</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>📱 Equipo</h5>
                            <p><strong>{{ $equipmentBlock->equipment->type }}</strong></p>
                            <p class="text-muted">Sección: {{ $equipmentBlock->equipment->section }}</p>
                            <p class="text-muted">Total de unidades: {{ $equipmentBlock->equipment->total_units }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>🎓 Ciclo Escolar</h5>
                            <p><strong>{{ $equipmentBlock->schoolCycle->name }}</strong></p>
                            @if($equipmentBlock->schoolCycle->description)
                                <p class="text-muted">{{ $equipmentBlock->schoolCycle->description }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>🗓️ Tipo de Bloqueo</h5>
                            @if($equipmentBlock->is_weekday_block)
                                <span class="badge badge-info badge-lg">Bloqueo Semanal</span>
                                <div class="mt-2">
                                    <strong>Días de la semana:</strong><br>
                                    @php
                                        $days = [];
                                        $dayNames = [
                                            'monday' => 'Lunes',
                                            'tuesday' => 'Martes',
                                            'wednesday' => 'Miércoles',
                                            'thursday' => 'Jueves',
                                            'friday' => 'Viernes',
                                            'saturday' => 'Sábado',
                                            'sunday' => 'Domingo'
                                        ];
                                        foreach($dayNames as $day => $name) {
                                            if($equipmentBlock->$day) $days[] = $name;
                                        }
                                    @endphp
                                    <ul class="list-unstyled">
                                        @foreach($days as $day)
                                            <li><i class="fas fa-check text-success"></i> {{ $day }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <span class="badge badge-warning badge-lg">Bloqueo por Día de Ciclo</span>
                                <div class="mt-2">
                                    <strong>Día del ciclo:</strong> {{ $equipmentBlock->cycle_day }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>⏰ Horario</h5>
                            <p>
                                <i class="fas fa-clock"></i>
                                {{ \Carbon\Carbon::parse($equipmentBlock->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($equipmentBlock->end_time)->format('H:i') }}
                            </p>
                            
                            <h5>🚫 Unidades Bloqueadas</h5>
                            <p>
                                <span class="badge badge-danger badge-lg">{{ $equipmentBlock->blocked_units }}</span>
                                <span class="text-muted">de {{ $equipmentBlock->equipment->total_units }} totales</span>
                            </p>
                        </div>
                    </div>

                    @if($equipmentBlock->reason)
                        <hr>
                        <h5>📝 Razón del Bloqueo</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ $equipmentBlock->reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Información Adicional</h3>
                </div>
                <div class="card-body">
                    <h6>Creado:</h6>
                    <p class="text-muted">{{ $equipmentBlock->created_at->format('d/m/Y H:i') }}</p>

                    @if($equipmentBlock->updated_at != $equipmentBlock->created_at)
                        <h6>Última modificación:</h6>
                        <p class="text-muted">{{ $equipmentBlock->updated_at->format('d/m/Y H:i') }}</p>
                    @endif

                    <h6>Impacto:</h6>
                    <p>
                        <span class="badge badge-warning">
                            {{ number_format(($equipmentBlock->blocked_units / $equipmentBlock->equipment->total_units) * 100, 1) }}%
                        </span>
                        del inventario bloqueado
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">⚙️ Acciones</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('equipment.blocks.edit', $equipmentBlock) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Editar Bloqueo
                    </a>
                    
                    <form method="POST" action="{{ route('equipment.blocks.destroy', $equipmentBlock) }}" 
                          onsubmit="return confirm('¿Estás seguro de que quieres eliminar este bloqueo?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Eliminar Bloqueo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
