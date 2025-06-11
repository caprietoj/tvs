@extends('adminlte::page')

@section('title', 'Mis Reservas de Espacios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Mis Reservas de Espacios</h1>
        <div>
            <a href="{{ route('space-reservations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Reserva
            </a>
            <a href="{{ route('space-reservations.calendar') }}" class="btn btn-info ml-2">
                <i class="far fa-calendar-alt"></i> Ver Calendario
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
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
            
            @if($reservations->isEmpty())
                <div class="alert alert-info">
                    No tiene reservas registradas. Cree una nueva reserva para comenzar.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Propósito</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td>{{ $reservation->space->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($reservation->purpose, 40) }}</td>
                                    <td>
                                        @if($reservation->status == 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($reservation->status == 'approved')
                                            <span class="badge badge-success">Aprobada</span>
                                        @elseif($reservation->status == 'rejected')
                                            <span class="badge badge-danger">Rechazada</span>
                                        @else
                                            <span class="badge badge-secondary">Cancelada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('space-reservations.show', $reservation) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($reservation->status == 'pending')
                                                <a href="{{ route('space-reservations.edit', $reservation) }}" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('space-reservations.cancel', $reservation) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Cancelar" onclick="return confirm('¿Está seguro de que desea cancelar esta reserva?')">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(in_array($reservation->status, ['approved', 'rejected', 'cancelled']) && \Carbon\Carbon::parse($reservation->date) > now())
                                                <form action="{{ route('space-reservations.copy', $reservation) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[ 1, "desc" ]]
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
@stop