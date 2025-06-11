<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Creado</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background-color: #ffffff; border-radius: 8px; padding: 30px; max-width: 600px; margin: auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333333; }
        p { color: #555555; line-height: 1.6; }
        .ticket-details { border-collapse: collapse; width: 100%; margin: 20px 0; }
        .ticket-details th, .ticket-details td { text-align: left; padding: 10px; border-bottom: 1px solid #dddddd; }
        .ticket-details th { background-color: #f8f8f8; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #888888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ticket Creado</h1>
        <p>Hola {{ $ticket->user->name }},</p>
        <p>Tu ticket ha sido creado exitosamente con los siguientes detalles:</p>
        <table class="ticket-details">
            <tr>
                <th>Título</th>
                <td>{{ $ticket->titulo }}</td>
            </tr>
            <tr>
                <th>Descripción</th>
                <td>{{ $ticket->descripcion }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ $ticket->estado }}</td>
            </tr>
            <tr>
                <th>Prioridad</th>
                <td>{{ $ticket->prioridad }}</td>
            </tr>
            <tr>
                <th>Tipo de Solicitud</th>
                <td>{{ $ticket->tipo_requerimiento }}</td>
            </tr>
        </table>
        <p>En breve nos pondremos en contacto contigo.</p>
        <p>Saludos cordiales,</p>
        <p>Equipo de Soporte</p>
        <div class="footer">
            <p>© {{ date('Y') }} The Victoria School. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
