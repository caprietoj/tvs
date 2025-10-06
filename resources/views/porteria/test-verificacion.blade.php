<!DOCTYPE html>
<html>
<head>
    <title>Test Verificación</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test de Verificación de Documento</h1>
    
    <form id="test-form">
        <input type="text" id="documento" placeholder="Documento" value="4552">
        <button type="submit">Verificar</button>
    </form>
    
    <div id="resultado" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;">
        <h3>Resultado:</h3>
        <pre id="json-resultado"></pre>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#test-form').on('submit', function(e) {
            e.preventDefault();
            
            const documento = $('#documento').val();
            
            console.log('Enviando documento:', documento);
            
            $.ajax({
                url: '{{ route('porteria.registro.verificar') }}',
                type: 'POST',
                data: {
                    documento: documento
                },
                success: function(response) {
                    console.log('Respuesta:', response);
                    $('#json-resultado').text(JSON.stringify(response, null, 2));
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr, status, error);
                    $('#json-resultado').text('ERROR: ' + JSON.stringify(xhr.responseJSON, null, 2));
                }
            });
        });
    </script>
</body>
</html>
