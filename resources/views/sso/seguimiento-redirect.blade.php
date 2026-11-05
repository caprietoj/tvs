<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Redirigiendo a Seguimiento…</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
            padding: 32px 40px;
            text-align: center;
            max-width: 420px;
        }
        .spinner {
            width: 42px;
            height: 42px;
            margin: 0 auto 18px;
            border: 4px solid #e3e8ef;
            border-top-color: #364e76;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { color: #6c757d; font-size: 14px; margin: 0 0 20px; }
        button {
            background: #364e76;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            padding: 10px 18px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>Redirigiendo a Seguimiento</h1>
        <p>Estamos iniciando tu sesión de forma segura. Si la redirección no ocurre automáticamente, pulsa el botón.</p>

        <form id="sso-form" action="{{ $target }}" method="POST">
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit">Continuar a Seguimiento</button>
        </form>
    </div>

    <script>
        document.getElementById('sso-form').submit();
    </script>
</body>
</html>
