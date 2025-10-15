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
		.divider {
			text-align: center;
			margin: 20px 0;
			position: relative;
		}
		.divider:before {
			content: "";
			position: absolute;
			left: 0;
			top: 50%;
			width: 40%;
			height: 1px;
			background: #ddd;
		}
		.divider:after {
			content: "";
			position: absolute;
			right: 0;
			top: 50%;
			width: 40%;
			height: 1px;
			background: #ddd;
		}
		.divider span {
			padding: 0 10px;
			color: #999;
		}
		.btn-google {
			background-color: #fff;
			border: 1px solid #ddd;
			color: #444;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			transition: all 0.3s;
		}
		.btn-google:hover {
			background-color: #f7f7f7;
			border-color: #ccc;
			color: #222;
		}
		.btn-google img {
			width: 20px;
			height: 20px;
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
		
		<div class="divider">
			<span>O</span>
		</div>
		
		<a href="{{ route('auth.google') }}" class="btn btn-google btn-block btn-flat">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
				<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
				<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
				<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
				<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
				<path fill="none" d="M0 0h48v48H0z"/>
			</svg>
			<span>Iniciar sesión con Google</span>
		</a>
		
		<a href="{{ route('password.request') }}" style="display: block; margin-top: 15px;">¿Olvidaste tu contraseña?</a>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>