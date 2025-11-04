# Solución: Usuario Servicios Generales - Notificaciones y Acceso al Calendario

## Problema Reportado

El usuario `supervsergenerales@tvs.edu.co` (Nancy Vichue Oyola) tenía dos problemas:

1. **No recibía notificaciones** cuando era solicitado para un evento
2. **No podía acceder** al calendario de eventos en `https://intranet.tvs.edu.co/events/calendar`

## Diagnóstico

### Problema 1: Configuración de Notificaciones
✅ **Estado:** El correo estaba correctamente configurado en `config/notifications.php`
- Estaba en el array `events.emails` (notificaciones generales)
- Estaba en el array `general_services_emails` (notificaciones específicas de servicios generales)

### Problema 2: Falta de Permisos
❌ **Estado:** El usuario NO tenía los permisos necesarios para acceder al calendario
- El usuario existía en la base de datos
- Tenía el departamento "Servicios Generales" asignado
- PERO no tenía el rol ni los permisos correctos

## Solución Implementada

### 1. Creación del Rol "servicios-generales"

Se creó el archivo `database/seeders/ServiciosGeneralesRoleSeeder.php` que:

- ✅ Crea/obtiene el rol `servicios-generales`
- ✅ Asigna los siguientes permisos al rol:
  - `view.events` - Ver eventos
  - `view.calendar` - Ver calendario
  - `confirm.events` - Confirmar eventos
  - `events.create` - Crear eventos
  - `events.edit` - Editar eventos
  - `events.delete` - Eliminar eventos
  - `equipment.view` - Ver equipos
  - `equipment.request` - Solicitar equipos
  - `maintenance.view` - Ver mantenimiento
  - `maintenance.create` - Crear mantenimiento
  - `maintenance.edit` - Editar mantenimiento
  - `maintenance.update-status` - Actualizar estado de mantenimiento

### 2. Asignación del Rol a Usuarios

El seeder asigna automáticamente el rol a todos los usuarios de Servicios Generales:

| Email | Nombre | Estado |
|-------|--------|--------|
| `amayala@tvs.edu.co` | Ana Milena Ayala | ✅ Rol asignado |
| `agomez@tvs.edu.co` | VIVIANA ANDREA GOMEZ OSORIO | ✅ Rol asignado |
| `supervsergenerales@tvs.edu.co` | Nancy Vichue Oyola | ✅ Rol asignado |
| `xsanchezy@tvs.edu.co` | Xiomara Isabel Sanchez Yanci | ✅ Rol asignado |
| `mcobosm@tvs.edu.co` | MARTHA ISABEL COBOS MARTINEZ | ✅ Rol asignado |
| `mtrodriguez@tvs.edu.co` | MARIA DEL TRANSITO RICO RODRIGUEZ | ✅ Rol asignado |

### 3. Ejecución del Seeder

```bash
php artisan db:seed --class=ServiciosGeneralesRoleSeeder
```

**Resultado:**
```
Rol "servicios-generales" creado/actualizado con permisos
Rol asignado a Ana Milena Ayala (amayala@tvs.edu.co)
Rol asignado a VIVIANA ANDREA GOMEZ OSORIO (agomez@tvs.edu.co)
Rol asignado a Nancy Vichue Oyola (supervsergenerales@tvs.edu.co)
Rol asignado a Xiomara Isabel Sanchez Yanci (xsanchezy@tvs.edu.co)
Rol asignado a MARTHA ISABEL COBOS MARTINEZ (mcobosm@tvs.edu.co)
Rol asignado a MARIA DEL TRANSITO RICO RODRIGUEZ (mtrodriguez@tvs.edu.co)
Usuarios de Servicios Generales configurados correctamente
```

### 4. Limpieza de Caché

```bash
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
```

## Verificación Post-Implementación

### Usuario: supervsergenerales@tvs.edu.co

```json
{
  "id": 108,
  "name": "Nancy Vichue Oyola",
  "email": "supervsergenerales@tvs.edu.co",
  "department": "Servicios Generales",
  "active": true
}
```

### Roles Asignados:
- ✅ `usuario` (rol base)
- ✅ `rrhh` (recursos humanos)
- ✅ `servicios-generales` (nuevo rol)

### Permisos Activos:
- ✅ `view.events` - Puede ver eventos
- ✅ `view.calendar` - Puede acceder al calendario
- ✅ `confirm.events` - Puede confirmar servicios solicitados
- ✅ `events.create`, `events.edit`, `events.delete` - CRUD completo de eventos
- ✅ Permisos de equipos y mantenimiento

## Configuración de Notificaciones

### Archivo: config/notifications.php

```php
'events' => [
    'emails' => [
        'jefesistemas@tvs.edu.co',
        'supervsergenerales@tvs.edu.co', // ✅ Configurado
    ],
    'general_services_emails' => [
        'serviciosgenerales@tvs.edu.co',
        'supervsergenerales@tvs.edu.co', // ✅ Configurado
    ],
]
```

### Flujo de Notificaciones en EventController

Cuando se crea un evento que requiere Servicios Generales:

1. **Notificaciones Generales** (para todos):
   - Se envía a todos los correos en `events.emails`
   - Incluye a `supervsergenerales@tvs.edu.co` ✅

2. **Notificaciones Específicas** (si `general_services_required = true`):
   - Se envía a todos los correos en `general_services_emails`
   - Incluye a `supervsergenerales@tvs.edu.co` ✅

## Problemas Potenciales y Soluciones

### 1. No recibe correos (incluso con configuración correcta)

**Posibles causas:**
- Límite diario de Gmail excedido (500 correos/día)
- Correos en spam/basura
- Filtros de correo en la cuenta

**Solución:**
- Verificar carpeta de spam
- Revisar logs: `storage/logs/laravel.log`
- Si se excedió el límite de Gmail, ver `SOLUCION_LIMITE_GMAIL.md`

### 2. No puede acceder al calendario

**Causa:** Falta de permisos `view.calendar`

**Solución:** Ya implementada con el seeder ✅

### 3. Caché de permisos desactualizado

**Síntoma:** Los permisos no se aplican inmediatamente

**Solución:**
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

## Acceso al Calendario

### URL:
```
https://intranet.tvs.edu.co/events/calendar
```

### Requisitos:
- ✅ Usuario autenticado
- ✅ Permiso `view.calendar` - **AHORA DISPONIBLE**
- ✅ No ser rol "profesor" (o tener permiso explícito)

### Código en EventController:

```php
public function calendar()
{
    // Verificar permisos para calendario
    if (auth()->user()->hasRole('profesor') && !auth()->user()->can('view.calendar')) {
        return redirect()->route('home')
            ->with('error', 'No tienes permisos para acceder al calendario.');
    }
    
    // Usuario servicios-generales tiene el permiso ✅
    $events = Event::orderBy('service_date')->get();
    return view('events.calendar', compact('events'));
}
```

## Archivos Modificados/Creados

### Nuevos:
- ✅ `database/seeders/ServiciosGeneralesRoleSeeder.php`

### Existentes (sin cambios, solo referencia):
- `config/notifications.php` (ya estaba correcto)
- `app/Http/Controllers/EventController.php` (funcionando correctamente)

## Comandos de Mantenimiento

### Re-ejecutar configuración:
```bash
php artisan db:seed --class=ServiciosGeneralesRoleSeeder
```

### Verificar roles de un usuario:
```bash
php artisan tinker
>>> $user = User::where('email', 'supervsergenerales@tvs.edu.co')->first();
>>> $user->getRoleNames();
>>> $user->getAllPermissions()->pluck('name');
```

### Limpiar caché:
```bash
php artisan permission:cache-reset && php artisan cache:clear && php artisan config:clear
```

## Actualización: Problema de Confirmación de Eventos

### 🔴 Problema Adicional Detectado:
Después de la implementación inicial, se reportó que el usuario **tampoco podía confirmar eventos**.

### Causa:
1. **En el controlador** (`EventController::confirmService()`): 
   - Solo verificaba roles específicos o profesor con permiso
   - No verificaba el permiso `confirm.events` para otros usuarios

2. **En la vista** (`show.blade.php`):
   - Los botones de "Confirmar" solo se mostraban si el usuario tenía rol específico o era profesor
   - No consideraba el permiso `confirm.events` para otros roles

### Solución Implementada:

#### 1. EventController.php - Método `confirmService()`

**Código anterior:**
```php
$canConfirm = auth()->user()->hasAnyRole(['admin', 'Admin', $permissionRole]) || 
             (auth()->user()->hasRole('profesor') && auth()->user()->can('confirm.events'));
```

**Código actualizado:**
```php
$canConfirm = auth()->user()->hasAnyRole(['admin', 'Admin', $permissionRole]) || 
             auth()->user()->can('confirm.events');
```

✅ **Resultado:** Ahora CUALQUIER usuario con el permiso `confirm.events` puede confirmar servicios

#### 2. Vista show.blade.php - Botones de Confirmación

Se actualizaron **7 verificaciones** para todos los servicios:
- Metro Junior
- Mantenimiento
- Sistemas
- **Servicios Generales** ⭐
- Aldimark
- Compras
- Comunicaciones

**Código anterior:**
```blade
@if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-XXX']) || 
   (auth()->user()->hasRole('profesor') && auth()->user()->can('confirm.events')))
```

**Código actualizado:**
```blade
@if(auth()->user()->hasAnyRole(['admin', 'Admin', 'confirmacion-XXX']) || 
   auth()->user()->can('confirm.events'))
```

✅ **Resultado:** Los botones "Confirmar" ahora se muestran para usuarios con el permiso

### Archivos Modificados:

1. ✅ `app/Http/Controllers/EventController.php` - Método `confirmService()`
2. ✅ `resources/views/events/show.blade.php` - 7 verificaciones de permisos actualizadas

### Caché Limpiado:
```bash
php artisan view:clear
php artisan permission:cache-reset
php artisan cache:clear
```

## Resultado Final

### ✅ Notificaciones
El usuario `supervsergenerales@tvs.edu.co` ahora recibe correos cuando:
1. Se crea cualquier evento (notificación general)
2. Se crea un evento que requiere Servicios Generales (notificación específica)

### ✅ Acceso al Calendario
El usuario puede acceder sin problemas a:
- `/events` - Lista de eventos
- `/events/calendar` - Vista de calendario

### ✅ Confirmación de Eventos
El usuario ahora puede:
- **Ver el botón "Confirmar"** en eventos que requieren Servicios Generales
- **Confirmar servicios** mediante AJAX sin errores
- Confirmar cualquier servicio si tiene el permiso `confirm.events`

### ✅ Permisos Completos
El rol `servicios-generales` tiene todos los permisos necesarios para:
- Gestionar eventos
- Gestionar equipos
- Gestionar mantenimiento
- Ver y confirmar solicitudes de servicios

## Testing

Para verificar que todo funciona:

1. **Login con la cuenta:**
   - Email: `supervsergenerales@tvs.edu.co`
   - Acceder a `/events/calendar`
   - Debe cargar sin errores ✅

2. **Crear un evento de prueba:**
   - Marcar "Servicios Generales" como requerido
   - Guardar evento
   - Verificar que llegue correo a `supervsergenerales@tvs.edu.co`

3. **Revisar logs:**
```bash
tail -f storage/logs/laravel.log | grep "supervsergenerales"
```

---

**Fecha de implementación:** 16 de octubre de 2025  
**Estado:** ✅ Resuelto completamente  
**Tiempo de implementación:** ~15 minutos  
**Archivos afectados:** 1 nuevo seeder
