<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>404 - Página no encontrada</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Bootstrap 3.3.7 -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- AdminLTE style -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
	<style>
		body { background-color: #ecf0f5; }
		.error-page { margin: 5% auto; text-align: center; }
		.error-page h2 { font-size: 120px; font-weight: bold; color: #f39c12; }
		.error-page h3 { font-size: 24px; margin-bottom: 20px; }
		.error-page p { font-size: 18px; }
	</style>
</head>
<body>
<div class="error-page">
	<h2 class="headline">404</h2>
	<div class="error-content">
		<h3><i class="fa fa-warning text-yellow"></i> Ooops! Página no encontrada.</h3>
		<p>
			No pudimos encontrar la página que estabas buscando.<br>
			Mientras tanto, puedes <a href="{{ url('/') }}">regresar al inicio</a>.
		</p>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
