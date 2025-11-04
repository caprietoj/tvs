# 🔗 Guía de Integración - Sistema de Solicitudes de Compra

## 📌 Descripción

Este sistema está diseñado para integrarse con tu aplicación principal de TVS mediante autenticación por token o user_id en la URL.

## 🔐 Métodos de Autenticación

### Opción 1: Autenticación por Token (Recomendado para Producción)

Desde tu aplicación principal, redirige al usuario con un token:

**Estructura de URL:**
```
http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?token=TOKEN_DEL_USUARIO
```

**Ejemplo en Laravel (tu aplicación principal):**
```php
// En tu controlador o vista
$token = auth()->user()->remember_token; // o api_token
$formUrl = "http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?token=" . $token;

// Redirigir
return redirect($formUrl);

// O en un enlace
<a href="{{ $formUrl }}" class="btn btn-primary">
    <i class="fas fa-shopping-cart"></i> Nueva Solicitud de Compra
</a>
```

### Opción 2: Autenticación por User ID (Para Desarrollo)

Para pruebas locales, puedes usar el ID de usuario:

```
http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?user_id=1
```

## 🌐 Configuración de URLs de Redirección

El sistema detecta automáticamente si está en local o producción y redirige a la URL correcta:

### En el archivo `config/database.php`:

```php
// URL para ambiente local
define('LOCAL_REDIRECT_URL', 'http://127.0.0.1:8000/purchase-requests');

// URL para ambiente de producción
define('PRODUCTION_REDIRECT_URL', 'https://intranet.tvs.edu.co/purchase-requests');
```

### Detección Automática:
- **Local**: Se detecta si el host es `127.0.0.1`, `localhost` o `::1`
- **Producción**: Cualquier otro host

## 📋 Flujo de Integración

### 1. Usuario hace clic en tu aplicación principal

```php
// Ejemplo en Laravel - routes/web.php
Route::get('/purchase-requests/create/external', function() {
    $token = auth()->user()->remember_token;
    $formUrl = env('APP_ENV') === 'local' 
        ? "http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?token=" . $token
        : "https://formularios.tvs.edu.co/create_purchase_request.php?token=" . $token;
    
    return redirect($formUrl);
})->name('purchase.external');
```

### 2. Sistema valida el token

El sistema automáticamente:
- ✅ Verifica el token en la tabla `users`
- ✅ Busca en campos `remember_token` o `api_token`
- ✅ Carga los datos del usuario (id, nombre, email)
- ✅ Crea una sesión temporal
- ✅ Muestra el nombre del usuario en el formulario

### 3. Usuario completa el formulario

- El nombre del usuario aparece automáticamente en "DOCENTE Y/O SOLICITANTE"
- El nombre también aparece en la barra superior

### 4. Usuario guarda la solicitud

Al guardar, el sistema:
- ✅ Guarda la solicitud en la BD
- ✅ Genera un número único de solicitud
- ✅ Guarda el mensaje de éxito en sesión
- ✅ **Redirige automáticamente** a tu aplicación principal según el entorno:
  - Local: `http://127.0.0.1:8000/purchase-requests`
  - Producción: `https://intranet.tvs.edu.co/purchase-requests`

## 🔄 Ejemplo Completo de Integración en Laravel

### En tu aplicación principal (Laravel):

#### 1. Botón para crear solicitud:

```blade
{{-- resources/views/purchase-requests/index.blade.php --}}
<a href="{{ route('purchase.create.external') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i> Nueva Solicitud de Compra
</a>
```

#### 2. Ruta en web.php:

```php
// routes/web.php
Route::get('/purchase-requests/create/external', [PurchaseRequestController::class, 'createExternal'])
    ->name('purchase.create.external')
    ->middleware('auth');
```

#### 3. Método en controlador:

```php
// app/Http/Controllers/PurchaseRequestController.php
public function createExternal()
{
    $user = auth()->user();
    
    // Generar o usar token existente
    if (!$user->remember_token) {
        $user->remember_token = Str::random(60);
        $user->save();
    }
    
    // URL del formulario externo
    $baseUrl = config('app.env') === 'local'
        ? 'http://localhost/tvs-final/Ordenes%20de%20compra'
        : 'https://formularios.tvs.edu.co';
    
    $formUrl = $baseUrl . '/create_purchase_request.php?token=' . $user->remember_token;
    
    return redirect()->away($formUrl);
}
```

#### 4. Recibir respuesta al volver:

```php
// routes/web.php
Route::get('/purchase-requests', [PurchaseRequestController::class, 'index'])
    ->name('purchase.index')
    ->middleware('auth');

// app/Http/Controllers/PurchaseRequestController.class
public function index(Request $request)
{
    // Verificar si hay mensaje de éxito desde el formulario externo
    if (session('success_message')) {
        $request->session()->flash('success', session('success_message'));
    }
    
    // Cargar solicitudes desde la base de datos compartida
    $requests = DB::connection('mysql') // o tu conexión
        ->table('purchase_requests')
        ->where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();
    
    return view('purchase-requests.index', compact('requests'));
}
```

## 🗄️ Consultar Solicitudes desde Laravel

Puedes consultar las solicitudes creadas en el formulario externo:

```php
// En cualquier parte de tu aplicación Laravel
use Illuminate\Support\Facades\DB;

$requests = DB::table('purchase_requests')
    ->where('user_id', auth()->id())
    ->whereNull('deleted_at')
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($requests as $request) {
    echo $request->request_number; // REQ-20241018-ABC123
    echo $request->status; // pending, approved, etc.
    
    // Items en JSON
    $items = json_decode($request->purchase_items, true);
    foreach ($items as $item) {
        echo $item['description'];
        echo $item['quantity'];
    }
}
```

## 🔒 Seguridad

### Tokens Válidos:
El sistema busca el token en los siguientes campos de la tabla `users`:
- `remember_token`
- `api_token`

### Validaciones:
- ✅ Usuario debe existir y no estar eliminado (`deleted_at IS NULL`)
- ✅ Token debe coincidir exactamente
- ✅ Se crea sesión temporal solo para este formulario
- ✅ No afecta la sesión principal de tu aplicación

### Sin Autenticación:
Si un usuario intenta acceder directamente sin token, verá:
```
Acceso No Autorizado
Debe acceder a este formulario desde el sistema principal de TVS.
```

## 🧪 Pruebas

### Prueba Local:

```bash
# Con token (busca en tu BD un token válido)
http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?token=ABC123XYZ...

# Con user_id (solo para desarrollo)
http://localhost/tvs-final/Ordenes%20de%20compra/create_purchase_request.php?user_id=1
```

### Verificar Sesión:

```php
// Agregar temporalmente en create_purchase_request.php para debug
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
```

## 📊 Datos Guardados

Cuando el usuario guarda la solicitud, se almacena:

```json
{
  "id": 1,
  "request_number": "REQ-20241018-ABC123",
  "user_id": 1,
  "type": "purchase",
  "status": "pending",
  "requester": "Cristian Andres Prieto J.",
  "section_area": "Tecnología Institucional",
  "purchase_items": [
    {
      "item": 1,
      "quantity": 5,
      "description": "Marcadores permanentes",
      "unit": "Caja",
      "observations": "Color negro"
    }
  ],
  "created_at": "2024-10-18 10:30:00"
}
```

## 🎯 Ventajas de esta Integración

✅ **Sin modificar tu aplicación principal**: Solo agregas enlaces con token
✅ **Autenticación transparente**: El usuario no necesita login adicional
✅ **Redirección automática**: Vuelve a tu app al terminar
✅ **Base de datos compartida**: Ambos sistemas usan la misma BD
✅ **Detección de entorno**: Funciona en local y producción automáticamente
✅ **Seguro**: Validación de tokens y usuarios

## 🆘 Solución de Problemas

### Error: "Acceso No Autorizado"
**Causa**: Token inválido o usuario no encontrado
**Solución**: Verifica que el token existe en la tabla `users`

### No aparece el nombre del usuario
**Causa**: Token no coincide o sesión no se creó
**Solución**: Verifica la consulta SQL en `getUserNameByToken()`

### No redirige correctamente
**Causa**: URLs mal configuradas
**Solución**: Verifica `LOCAL_REDIRECT_URL` y `PRODUCTION_REDIRECT_URL` en `config/database.php`

### Error de base de datos
**Causa**: Conexión o tabla no existe
**Solución**: Ejecuta el script SQL de creación de tabla

## 📞 Contacto

Para soporte técnico, contactar al departamento de Sistemas del Colegio Victoria SAS.
