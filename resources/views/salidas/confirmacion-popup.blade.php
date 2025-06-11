<!DOCTYPE html>
<html>
<head>
    <title>Confirmación</title>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($success)
                Swal.fire({
                    title: '¡Confirmación Exitosa!',
                    html: `
                        <p>Se ha confirmado la participación del área de <strong>${'{{ ucfirst($area) }}'}</strong></p>
                        <p>Para la salida pedagógica: ${'{{ $salida->consecutivo }}'}</p>
                        <p class="text-muted">Fecha: ${'{{ $salida->fecha_salida->format('d/m/Y') }}'}</p>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    window.close();
                });
            @else
                Swal.fire({
                    title: 'Error',
                    text: '{{ $error }}',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Cerrar'
                }).then((result) => {
                    window.close();
                });
            @endif
        });
    </script>
</body>
</html>
