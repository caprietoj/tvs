# Configuración de Inicio de Sesión con Google OAuth

## Descripción
Se ha implementado la autenticación con Google OAuth en el sistema de login de la Intranet TVS. Los usuarios pueden ahora iniciar sesión usando su cuenta de Google.

## Archivos Modificados

### 1. Dependencias
- **composer.json**: Se agregó `laravel/socialite ^5.23`

### 2. Configuración
- **config/services.php**: Configuración de credenciales de Google OAuth
- **.env**: Variables de entorno para Client ID, Client Secret y Redirect URI

### 3. Base de Datos
- **Migration**: `2025_10_15_142522_add_google_id_to_users_table.php`
  - Agrega columna `google_id` a la tabla `users`
- **Model**: `app/Models/User.php`
  - Se agregó `google_id` al array `$fillable`

### 4. Controlador
- **app/Http/Controllers/Auth/GoogleAuthController.php**: Nuevo controlador
  - `redirectToGoogle()`: Redirige al usuario a Google para autenticación
  - `handleGoogleCallback()`: Maneja el callback de Google y crea/vincula usuarios

### 5. Rutas
- **routes/web.php**: Se agregaron dos rutas
  - `GET /auth/google` → Inicia el flujo de OAuth
  - `GET /auth/google/callback` → Callback después de la autenticación

### 6. Vista
- **resources/views/auth/login.blade.php**: 
  - Botón "Iniciar sesión con Google" con logo de Google
  - Divisor visual "O" entre métodos de autenticación
  - Estilos consistentes con el diseño actual

## Configuración en Google Cloud Console

Para que el inicio de sesión con Google funcione, necesitas configurar las credenciales en Google Cloud Console:

### Paso 1: Crear Proyecto en Google Cloud Console
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Nombre sugerido: "Intranet TVS"

### Paso 2: Habilitar Google+ API
1. En el menú lateral, ve a **APIs y Servicios** > **Biblioteca**
2. Busca "Google+ API"
3. Haz clic en **Habilitar**

### Paso 3: Crear Credenciales OAuth 2.0
1. Ve a **APIs y Servicios** > **Credenciales**
2. Haz clic en **+ CREAR CREDENCIALES**
3. Selecciona **ID de cliente de OAuth**
4. Tipo de aplicación: **Aplicación web**
5. Nombre: "Intranet TVS Login"

### Paso 4: Configurar URLs de Redirección
Agrega las siguientes URIs de redirección autorizadas:

**Para desarrollo local:**
```
http://127.0.0.1:8000/auth/google/callback
http://localhost:8000/auth/google/callback
```

**Para producción:**
```
https://intranet.tvs.edu.co/auth/google/callback
```

### Paso 5: Obtener Credenciales
Después de crear el cliente OAuth, obtendrás:
- **Client ID**: Algo como `123456789-abc123def456.apps.googleusercontent.com`
- **Client Secret**: Una cadena alfanumérica

### Paso 6: Configurar Variables de Entorno
Edita el archivo `.env` y agrega las credenciales:

```env
GOOGLE_CLIENT_ID=tu-client-id-aqui.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret-aqui
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

**Ejemplo para desarrollo:**
```env
GOOGLE_CLIENT_ID=123456789-abc123def456.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xyz789abc123def456
GOOGLE_REDIRECT_URI="http://127.0.0.1:8000/auth/google/callback"
```

**Ejemplo para producción:**
```env
GOOGLE_CLIENT_ID=123456789-abc123def456.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xyz789abc123def456
GOOGLE_REDIRECT_URI="https://intranet.tvs.edu.co/auth/google/callback"
```

### Paso 7: Limpiar Caché
Después de configurar las variables de entorno, ejecuta:
```bash
php artisan config:clear
php artisan cache:clear
```

## Flujo de Autenticación

### Nuevo Usuario (Primera vez con Google)
1. Usuario hace clic en "Iniciar sesión con Google"
2. Es redirigido a Google para autorizar
3. Google devuelve información del usuario
4. Se crea una nueva cuenta en el sistema con:
   - `name`: Nombre de Google
   - `email`: Email de Google
   - `google_id`: ID único de Google
   - `password`: Password aleatorio (no usado para login con Google)
   - `email_verified_at`: Fecha actual
5. Usuario es autenticado automáticamente

### Usuario Existente (Vinculación)
1. Si existe un usuario con el mismo email pero sin `google_id`
2. Se vincula el `google_id` a la cuenta existente
3. Usuario puede usar tanto password tradicional como Google para iniciar sesión

### Usuario con Google ID
1. Si el usuario ya tiene `google_id` configurado
2. Se autentica directamente
3. Acceso inmediato al sistema

## Restricciones y Consideraciones

### Dominio Restringido (Opcional)
Si deseas que solo usuarios con email `@tvs.edu.co` puedan registrarse, modifica el controlador:

```php
// En handleGoogleCallback() después de obtener $googleUser
if (!str_ends_with($googleUser->getEmail(), '@tvs.edu.co')) {
    return redirect()->route('login')
        ->with('error', 'Solo se permiten correos institucionales @tvs.edu.co');
}
```

### Roles y Permisos
Los nuevos usuarios creados a través de Google:
- **NO** tienen roles asignados automáticamente
- Un administrador debe asignar roles después del primer login
- Considera agregar un rol por defecto si es necesario

### Email de Dominio Verificado en Google
Para usar emails `@tvs.edu.co` en producción:
1. Ve a la configuración de OAuth en Google Cloud Console
2. En "OAuth consent screen"
3. Agrega `tvs.edu.co` como dominio autorizado
4. Esto permite que usuarios con ese dominio inicien sesión sin aprobación manual

## Verificación de Instalación

### 1. Verificar Migración
```bash
php artisan migrate:status
```
Debe aparecer: `2025_10_15_142522_add_google_id_to_users_table`

### 2. Verificar Rutas
```bash
php artisan route:list | grep google
```
Debe mostrar:
```
GET|HEAD  auth/google ..................... auth.google › Auth\GoogleAuthController@redirectToGoogle
GET|HEAD  auth/google/callback ............ Auth\GoogleAuthController@handleGoogleCallback
```

### 3. Verificar Columna en Base de Datos
```sql
DESCRIBE users;
```
Debe aparecer la columna `google_id` (varchar, nullable, unique)

### 4. Probar el Botón de Login
1. Accede a `/login`
2. Verifica que aparezca el botón "Iniciar sesión con Google" con el logo
3. Verifica que tenga el divisor "O" entre los métodos

## Troubleshooting

### Error: "redirect_uri_mismatch"
- Verifica que la URL en `.env` coincida EXACTAMENTE con la configurada en Google Cloud Console
- Incluye el protocolo correcto (http:// o https://)
- No olvides `/auth/google/callback` al final

### Error: "Client ID no válido"
- Verifica que `GOOGLE_CLIENT_ID` en `.env` sea correcto
- Ejecuta `php artisan config:clear`

### El botón no aparece
- Limpia la caché de vistas: `php artisan view:clear`
- Verifica que no haya errores de sintaxis en `login.blade.php`

### Usuario creado pero sin acceso
- Asigna roles al usuario desde el panel de administración
- Verifica que el usuario esté activo en la base de datos

## Seguridad

### Recomendaciones
1. **NUNCA** subas el archivo `.env` a git
2. Usa credenciales diferentes para desarrollo y producción
3. Rota el Client Secret periódicamente (cada 6-12 meses)
4. Monitorea los intentos de login en Google Cloud Console
5. Implementa rate limiting si es necesario

### Producción
En producción, asegúrate de:
- Usar HTTPS para todas las URLs
- Configurar correctamente el `APP_URL` en `.env`
- Verificar el dominio en Google Cloud Console
- Habilitar "OAuth consent screen" en modo producción

## Mantenimiento

### Actualizar Socialite
```bash
composer update laravel/socialite
```

### Desactivar Google Login (Temporalmente)
Comenta las rutas en `web.php` y oculta el botón en `login.blade.php`

### Remover Google Login (Permanentemente)
1. Elimina las rutas de `web.php`
2. Elimina el botón de `login.blade.php`
3. Elimina `GoogleAuthController.php`
4. (Opcional) Crea una migración para remover la columna `google_id`

## Soporte
Para más información sobre Laravel Socialite:
- [Documentación Oficial](https://laravel.com/docs/11.x/socialite)
- [Google OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)
