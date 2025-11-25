# 🔧 Configuración de Integración con Sistema de Presupuesto

## 📋 Descripción General

Este documento describe cómo configurar la integración de auto-login entre la Intranet TVS y el Sistema de Presupuesto externo.

---

## 🌍 Configuración de Entornos

### **Entorno LOCAL (Desarrollo)**

En tu archivo `.env` local, configura:

```env
# Presupuesto External App Integration
PRESUPUESTO_URL=http://127.0.0.1:9000
PRESUPUESTO_API_SECRET=tu_clave_secreta_compartida_aqui
```

### **Entorno PRODUCCIÓN**

En tu archivo `.env` de producción, configura:

```env
# Presupuesto External App Integration
PRESUPUESTO_URL=https://presupuesto.tvs.edu.co
PRESUPUESTO_API_SECRET=clave_secreta_real_de_produccion
```

> ⚠️ **IMPORTANTE**: 
> - En producción DEBES usar HTTPS (`https://`)
> - La clave secreta debe ser diferente en cada entorno
> - Nunca compartas la clave secreta de producción en repositorios públicos

---

## 🔐 Generación de Clave Secreta

Puedes generar una clave secreta segura con cualquiera de estos métodos:

### **Método 1: Laravel Tinker**
```bash
php artisan tinker
>>> Str::random(64)
```

### **Método 2: OpenSSL**
```bash
openssl rand -base64 48
```

### **Método 3: PHP**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

## 🔄 Flujo de Auto-Login

### **1. Usuario hace clic en "💰 Presupuesto"**
- Se carga la vista `presupuesto.autologin`
- Muestra spinner de carga

### **2. JavaScript envía petición POST**
**Endpoint**: `{PRESUPUESTO_URL}/api/autologin/generate-token`

**Payload**:
```json
{
    "user_id": 123,
    "user_email": "usuario@tvs.edu.co",
    "user_name": "Nombre Usuario",
    "secret": "clave_secreta_compartida"
}
```

### **3. Sistema de Presupuesto responde**
**Respuesta exitosa**:
```json
{
    "success": true,
    "url": "https://presupuesto.tvs.edu.co/autologin/abc123xyz"
}
```

**Respuesta con error**:
```json
{
    "success": false,
    "message": "Secret inválido"
}
```

### **4. Intranet abre nueva pestaña**
- Si hay URL: abre `data.url` en nueva pestaña
- Si hay error: abre login normal
- Regresa automáticamente al dashboard

---

## 🛠️ Implementación en Sistema de Presupuesto

### **Ruta 1: Generar Token** (`/api/autologin/generate-token`)

```php
// routes/api.php o web.php
Route::post('/api/autologin/generate-token', [AutoLoginController::class, 'generateToken']);
```

```php
// app/Http/Controllers/AutoLoginController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AutoLoginToken;

class AutoLoginController extends Controller
{
    public function generateToken(Request $request)
    {
        // 1. Validar secret compartido
        if ($request->secret !== config('app.intranet_secret')) {
            return response()->json([
                'success' => false,
                'message' => 'Secret inválido'
            ], 401);
        }

        // 2. Buscar o crear usuario
        $user = User::firstOrCreate(
            ['email' => $request->user_email],
            [
                'name' => $request->user_name,
                'intranet_id' => $request->user_id,
                'password' => bcrypt(Str::random(32)), // Password aleatorio
            ]
        );

        // 3. Generar token temporal (válido 60 segundos)
        $token = Str::random(64);
        AutoLoginToken::create([
            'token' => hash('sha256', $token),
            'user_id' => $user->id,
            'expires_at' => now()->addSeconds(60),
            'used' => false
        ]);

        // 4. Retornar URL con token
        return response()->json([
            'success' => true,
            'url' => config('app.url') . '/autologin/' . $token
        ]);
    }

    public function autoLogin($token)
    {
        // 1. Buscar token
        $hashedToken = hash('sha256', $token);
        $tokenRecord = AutoLoginToken::where('token', $hashedToken)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return redirect('/login')->with('error', 'Token inválido o expirado');
        }

        // 2. Marcar como usado
        $tokenRecord->update(['used' => true]);

        // 3. Autenticar usuario
        auth()->loginUsingId($tokenRecord->user_id);

        // 4. Redirigir al dashboard
        return redirect('/dashboard');
    }
}
```

### **Migración para Tabla de Tokens**

```php
// database/migrations/xxxx_create_auto_login_tokens_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auto_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 255)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
            
            $table->index(['token', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auto_login_tokens');
    }
};
```

### **Ruta 2: Auto-Login con Token**

```php
// routes/web.php
Route::get('/autologin/{token}', [AutoLoginController::class, 'autoLogin'])->name('autologin');
```

---

## 🧹 Limpieza de Tokens Expirados

### **Comando Artisan**

```php
// app/Console/Commands/CleanExpiredTokens.php
<?php

namespace App\Console\Commands;

use App\Models\AutoLoginToken;
use Illuminate\Console\Command;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Eliminar tokens de auto-login expirados';

    public function handle()
    {
        $deleted = AutoLoginToken::where('expires_at', '<', now())
            ->orWhere('used', true)
            ->delete();

        $this->info("Tokens eliminados: {$deleted}");
    }
}
```

### **Programar en Task Scheduler**

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tokens:clean')->hourly();
}
```

---

## 🔒 Consideraciones de Seguridad

1. **HTTPS Obligatorio en Producción**
   - Nunca uses HTTP en producción
   - Configura certificado SSL válido

2. **Secret Compartido**
   - Debe ser diferente en cada entorno
   - Mínimo 32 caracteres
   - Nunca exponerlo en código frontend

3. **Tokens Temporales**
   - Validez máxima: 60 segundos
   - Un solo uso
   - Hash SHA-256 en base de datos

4. **Validación de Usuario**
   - Verificar que el email pertenece al dominio `@tvs.edu.co`
   - Opcional: validar lista blanca de IPs

5. **Rate Limiting**
   - Limitar peticiones por IP
   - Laravel: usar middleware `throttle`

---

## ✅ Checklist de Implementación

### **En Intranet (Laravel)**
- [x] Agregar variables `PRESUPUESTO_URL` y `PRESUPUESTO_API_SECRET` en `.env`
- [x] Crear vista `presupuesto/autologin.blade.php`
- [x] Agregar ruta protegida con middleware
- [x] Modificar menú AdminLTE
- [x] Limpiar cachés

### **En Sistema de Presupuesto**
- [ ] Agregar variable `INTRANET_SECRET` en `.env`
- [ ] Crear migración `auto_login_tokens`
- [ ] Crear controlador `AutoLoginController`
- [ ] Agregar rutas API y web
- [ ] Implementar limpieza de tokens
- [ ] Configurar HTTPS en producción

---

## 🧪 Pruebas

### **Test en Local**

1. Configurar `.env` con URLs locales
2. Iniciar ambas aplicaciones
3. Hacer clic en "💰 Presupuesto"
4. Verificar que se abre nueva pestaña
5. Confirmar auto-login exitoso

### **Test en Producción**

1. Verificar HTTPS activo
2. Probar con usuario real
3. Verificar logs de errores
4. Confirmar que tokens expiran

---

## 📞 Soporte

Para dudas o problemas:
- **Email**: sistemas@tvs.edu.co
- **Documentación**: Este archivo
- **Laravel Docs**: https://laravel.com/docs
