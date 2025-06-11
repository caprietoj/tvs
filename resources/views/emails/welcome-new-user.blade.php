@component('mail::message')
# Bienvenido/a {{ $name }}

Se ha creado una cuenta para ti en {{ config('app.name') }}.

Aquí están tus credenciales de acceso:

**Email:** {{ $email }}  
**Contraseña:** {{ $password }}

Por motivos de seguridad, deberás cambiar tu contraseña en el primer inicio de sesión.

@component('mail::button', ['url' => route('login')])
Iniciar Sesión
@endcomponent

Si tienes alguna pregunta, no dudes en contactar al equipo de soporte.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
