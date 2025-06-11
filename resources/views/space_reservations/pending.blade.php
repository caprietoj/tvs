@extends('adminlte::page')

@section('title', 'Reservas Pendientes de Aprobación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Reservas Pendientes de Aprobación</h1>
        <div>
            <a href="{{ route('space-reservations.calendar') }}" class="btn btn-info">
                <i class="far fa-calendar-alt"></i> Ver Calendario
            </a>
            <a href="{{ route('space-reservations.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-list"></i> Todas las Reservas
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
                    No hay reservas pendientes de aprobación.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Solicitante</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Propósito</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td>{{ $reservation->space->name }}</td>
                                    <td>{{ $reservation->user->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($reservation->purpose, 40) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('space-reservations.show', $reservation) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('space-reservations.edit', $reservation) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('space-reservations.update', $reservation) }}" method="POST" style="display: inline;" class="approve-form">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="space_id" value="{{ $reservation->space_id }}">
                                                <input type="hidden" name="date" value="{{ $reservation->date }}">
                                                <input type="hidden" name="start_time" value="{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}">
                                                <input type="hidden" name="end_time" value="{{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}">
                                                <input type="hidden" name="purpose" value="{{ $reservation->purpose }}">
                                                <input type="hidden" name="notes" value="{{ $reservation->notes }}">
                                                <input type="hidden" name="status" value="approved">
                                                
                                                <button type="submit" class="btn btn-sm btn-success" title="Aprobar" onclick="return confirm('¿Está seguro de que desea aprobar esta reserva?')">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-sm btn-danger reject-btn" title="Rechazar" data-toggle="modal" data-target="#rejectModal" data-reservation-id="{{ $reservation->id }}">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                            
                                            <form action="{{ route('space-reservations.destroy', $reservation) }}" method="POST" style="display: inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta reserva?')">
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
                
                {{ $reservations->links() }}
            @endif
        </div>
    </div>
    
    <!-- Modal de Rechazo -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comments">Motivo del rechazo:</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
                            <input type="hidden" name="status" value="rejected">
                            <input type="hidden" name="space_id" id="space_id">
                            <input type="hidden" name="date" id="date">
                            <input type="hidden" name="start_time" id="start_time">
                            <input type="hidden" name="end_time" id="end_time">
                            <input type="hidden" name="purpose" id="purpose">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar</button>
                    </div>
                </form>
            </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            // DataTable initialization
            const table = $('.datatable').DataTable({
                "language": {
                    "url": "/js/datatables/i18n/Spanish.json"
                },
                "order": [[ 2, "asc" ]]
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Configurar el modal de rechazo
            $('.reject-btn').click(function() {
                var reservationId = $(this).data('reservation-id');
                var reservation = {!! json_encode($reservations->keyBy('id')) !!}[reservationId];
                
                $('#rejectForm').attr('action', '/space-reservations/' + reservationId);
                $('#space_id').val(reservation.space_id);
                $('#date').val(reservation.date);
                $('#start_time').val(moment(reservation.start_time).format('HH:mm'));
                $('#end_time').val(moment(reservation.end_time).format('HH:mm'));
                $('#purpose').val(reservation.purpose);
            });
            
            // Manejar la presentación del formulario de aprobación
            $('.approve-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var loadingBtn = form.find('button[type="submit"]');
                var originalContent = loadingBtn.html();
                var rowToRemove = form.closest('tr');
                
                // Mostrar indicador de carga
                loadingBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                
                // Obtener el token CSRF
                var token = $('meta[name="csrf-token"]').attr('content');
                
                // Preparar los datos del formulario
                var formData = new FormData(form[0]);
                
                // Enviar formulario vía AJAX
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Respuesta exitosa:', response);
                        
                        // Remover la fila de la tabla
                        table.row(rowToRemove).remove().draw();
                        
                        // Mostrar mensaje de éxito
                        var alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            'Reserva aprobada exitosamente.' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '</div>');
                        
                        // Insertar mensaje al principio del cuerpo de la tarjeta
                        $('.card-body').prepend(alert);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(function() {
                            alert.alert('close');
                        }, 5000);
                        
                        // Verificar si no hay más reservas pendientes
                        if (table.rows().count() === 0) {
                            $('.table-responsive').html(
                                '<div class="alert alert-info">No hay reservas pendientes de aprobación.</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud:', error);
                        console.error('Respuesta del servidor:', xhr.responseText);
                        
                        // Restaurar el botón
                        loadingBtn.html(originalContent).prop('disabled', false);
                        
                        // Mostrar mensaje de error
                        alert('Error al aprobar la reserva. Por favor, inténtelo de nuevo.');
                    }
                });
            });
            
            // También configurar el rechazo para usar AJAX
            $('#rejectForm').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitBtn = form.find('button[type="submit"].btn-danger');
                var originalContent = submitBtn.html();
                var reservationId = form.attr('action').split('/').pop();
                var rowToRemove = $('button[data-reservation-id="'+reservationId+'"]').closest('tr');
                
                // Validar comentarios
                if ($('#comments').val().trim() === '') {
                    alert('Debe proporcionar un comentario al rechazar una reserva.');
                    return;
                }
                
                // Mostrar indicador de carga
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                
                // Obtener el token CSRF
                var token = $('meta[name="csrf-token"]').attr('content');
                
                // Preparar los datos del formulario
                var formData = new FormData(form[0]);
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Rechazo exitoso:', response);
                        
                        // Cerrar modal
                        $('#rejectModal').modal('hide');
                        
                        // Remover la fila de la tabla
                        table.row(rowToRemove).remove().draw();
                        
                        // Mostrar mensaje de éxito
                        var alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            'Reserva rechazada exitosamente.' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '</div>');
                        
                        // Insertar mensaje
                        $('.card-body').prepend(alert);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(function() {
                            alert.alert('close');
                        }, 5000);
                        
                        // Verificar si no hay más reservas pendientes
                        if (table.rows().count() === 0) {
                            $('.table-responsive').html(
                                '<div class="alert alert-info">No hay reservas pendientes de aprobación.</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en el rechazo:', error);
                        console.error('Respuesta del servidor:', xhr.responseText);
                        
                        // Restaurar el botón
                        submitBtn.html(originalContent).prop('disabled', false);
                        
                        // Mostrar mensaje de error
                        alert('Error al rechazar la reserva. Por favor, inténtelo de nuevo.');
                    }
                });
            });
            
            // Limpiar el formulario de rechazo cuando se cierra el modal
            $('#rejectModal').on('hidden.bs.modal', function () {
                $('#comments').val('');
                $('#rejectForm').attr('action', '');
                $('#space_id').val('');
                $('#date').val('');
                $('#start_time').val('');
                $('#end_time').val('');
                $('#purpose').val('');
            });
        });
    </script>
@stop