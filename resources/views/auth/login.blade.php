<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Intranet TVS - Login</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Bootstrap 3.3.7 -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- AdminLTE style -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
	<style>
		body { 
			background-color: #ecf0f5; 
		}
		.login-box { 
			margin: 3% auto;
			width: 360px;
		}
		.login-box-body { 
			box-shadow: 0 2px 5px rgba(0,0,0,0.3);
			padding: 20px;
			border-radius: 5px;
			background: #fff;
		}
		.login-logo {
			margin-bottom: 20px;
		}
		.login-logo img {
			max-width: 200px;
			height: auto;
			margin-bottom: 10px;
		}
		.login-box-msg {
			color: #364E76;
			font-size: 18px;
			margin-bottom: 20px;
		}
		.btn-primary {
			background-color: #364E76;
			border-color: #364E76;
		}
		.btn-primary:hover {
			background-color: #2a3d5d;
			border-color: #2a3d5d;
		}
		.form-control:focus {
			border-color: #364E76;
			box-shadow: none;
		}
	</style>
</head>
<body>
<div class="login-box">
	<div class="login-logo">
		<img src="{{ asset('img/the_victoria.png') }}" alt="Logo Victoria School">
	</div>
	<div class="login-box-body">
		<p class="login-box-msg">Ingresa tus credenciales</p>
		@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
		@endif
		<form method="POST" action="{{ route('login') }}">
			@csrf
			<div class="form-group has-feedback">
				<input type="email" name="email" class="form-control" placeholder="Correo electrónico" required autofocus>
				<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
			</div>
			<div class="form-group has-feedback">
				<input type="password" name="password" class="form-control" placeholder="Contraseña" required>
				<span class="glyphicon glyphicon-lock form-control-feedback"></span>
			</div>
			<div class="row">
				<div class="col-xs-8">
					<div class="checkbox icheck">
							<label style="margin-left:15px;">
								<input type="checkbox" name="remember"> Recordarme
							</label>
					</div>
				</div>
				<div class="col-xs-4">
					<button type="submit" class="btn btn-primary btn-block btn-flat">Ingresar</button>
				</div>
			</div>
		</form>
		<a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>