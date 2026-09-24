@extends('adminlte::page')

@section('title', 'Ceder / Reasignar Sala')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exchange-alt mr-2"></i> Ceder / Reasignar Sala</h1>
        <a href="{{ route('equipment.blocks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Bloqueos
        </a>
    </div>
@stop

@section('content')
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Seleccione la fecha en que un docente <strong>no usará</strong> la sala y ceda el espacio a otro docente.
        El cambio aplica <strong>solo a esa fecha</strong>; el bloqueo recurrente no se modifica.
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-day"></i> Seleccione la fecha</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('equipment.blocks.cede-form') }}" class="form-inline">
                <label class="mr-2 font-weight-bold">Fecha:</label>
                <input type="date" name="date" class="form-control mr-2" value="{{ $date }}" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Ver bloqueos del día
                </button>
                @if($activeCycle)
                    <span class="ml-3 text-muted">
                        Ciclo: <strong>{{ $activeCycle->name }}</strong>
                        @if($cycleDay)
                            &nbsp;·&nbsp; Día de ciclo: <strong>{{ $cycleDay->cycle_day }}</strong>
                        @endif
                    </span>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-door-open"></i>
                Bloqueos para el {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                <span class="badge badge-primary ml-2">{{ $blocks->count() }}</span>
            </h3>
        </div>
        <div class="card-body">
            @if($blocks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Sala / Equipo</th>
                                <th>Horario</th>
                                <th>Docente actual</th>
                                <th>Estado</th>
                                <th style="min-width: 320px;">Ceder a</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blocks as $block)
                                <tr>
                                    <td>
                                        <strong>{{ $block->equipment->space->name ?? strtoupper(str_replace('_', ' ', $block->equipment->section)) }}</strong><br>
                                        <small class="text-muted">
                                            {{ $block->is_weekday_block ? 'Bloqueo semanal' : 'Día de ciclo ' . $block->cycle_day }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($block->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($block->end_time)->format('H:i') }}
                                    </td>
                                    <td>{{ $block->reason ?: 'Sin asignar' }}</td>
                                    <td>
                                        @if($block->override)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i>
                                                Cedido a {{ $block->override->new_reason ?: 'otro docente' }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Bloqueado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('equipment.blocks.cede', $block) }}" class="form-inline">
                                            @csrf
                                            <input type="hidden" name="override_date" value="{{ $date }}">
                                            <select name="assigned_user_id" class="form-control form-control-sm select2-teacher mr-2" required style="min-width: 200px;">
                                                <option value="">Seleccione docente...</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}"
                                                        {{ $block->override && $block->override->assigned_user_id == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('¿Ceder este espacio al docente seleccionado solo para el {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}?');">
                                                <i class="fas fa-share"></i> Ceder
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay bloqueos para esta fecha</h4>
                    <p class="text-muted">No se encontraron bloqueos (semanales o de día de ciclo) para la fecha seleccionada.</p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2-teacher').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Seleccione docente...'
            });

            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 6000);
        });
    </script>
@stop
