<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Solicitud Recibida</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px; }
        .container { background-color: #ffffff; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { background-color: #3c8dbc; color: #ffffff; text-align: center; padding: 20px; }
        .content { padding: 20px; }
        .content h2 { color: #3c8dbc; }
        .details { border: 1px solid #e0e0e0; padding: 15px; margin-top: 10px; }
        .details li { margin-bottom: 10px; }
        .footer { background-color: #f0f0f0; text-align: center; padding: 10px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Solicitud Recibida</h1>
        </div>
        <div class="content">
            <h2>¡Su solicitud fue recibida con éxito!</h2>
            <p>A continuación se muestran los detalles de su solicitud:</p>
            <ul class="details">
                <li><strong>Usuario:</strong> {{ $documentRequest->user->name ?? 'N/A' }}</li>
                <li><strong>Documento:</strong> {{ $documentRequest->document->name ?? 'N/A' }}</li>
                <li><strong>Descripción:</strong> {{ $documentRequest->description }}</li>
                <!-- <li><strong>Estado de la Solicitud:</strong> {{ $documentRequest->status ?? 'N/A' }}</li> -->
            </ul>
        </div>
        <div class="footer">
            Recursos Humanos
        </div>
    </div>
</body>
</html>
