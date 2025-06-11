<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Solicitud Resuelta - Certificado Adjuntado</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7f7f7; padding: 20px; }
        .container { background-color: #ffffff; border-radius: 8px; padding: 30px; max-width: 600px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        p { color: #34495e; line-height: 1.6; }
        .button { display: inline-block; background-color: #27ae60; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 0.9em; color: #95a5a6; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Solicitud Resuelta Exitosamente</h1>
        <p>Estimado/a {{ $documentRequest->user->name }},</p>
        <p>Nos complace informarle que su solicitud de documento ha sido resuelta satisfactoriamente. Se adjunta a este correo el archivo certificado solicitado.</p>
        <p>Por favor revise el documento adjunto. Si tiene alguna duda o requiere asistencia adicional, no dude en contactarnos.</p>
        <p>Atentamente,<br>Equipo de Soporte</p>
        <div class="footer">
            <p>© {{ date('Y') }} The Victoria School. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
