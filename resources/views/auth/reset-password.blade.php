<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Intranet TVS - Reset Password</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Bootstrap 3.3.7 -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- AdminLTE style -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
	<style>
		body { background-color: #ecf0f5; }
		.center-box { margin: 7% auto; width: 100%; max-width: 400px; }
	</style>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="center-box">
			<div class="box box-primary">
				<div class="box-header with-border text-center">
					<h3 class="box-title">Restablecer Contraseña</h3>
				</div>
				<div class="box-body">
					@if ($errors->any())
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
					@endif
					<form method="POST" action="{{ route('password.store') }}">
						@csrf
						<!-- Password Reset Token -->
						<input type="hidden" name="token" value="{{ $request->route('token') }}">
						<div class="form-group has-feedback">
							<input type="email" name="email" class="form-control" placeholder="Correo electrónico" value="{{ old('email', $request->email) }}" required autofocus>
							<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
						</div>
						<div class="form-group has-feedback">
							<input type="password" name="password" class="form-control" placeholder="Nueva contraseña" required autocomplete="new-password">
							<span class="glyphicon glyphicon-lock form-control-feedback"></span>
						</div>
						<div class="form-group has-feedback">
							<input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar contraseña" required autocomplete="new-password">
							<span class="glyphicon glyphicon-lock form-control-feedback"></span>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-primary btn-block btn-flat">Restablecer Contraseña</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
