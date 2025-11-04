# Comandos para Desplegar Cambios en Producción - Sistema de Equipos

## Problema
El formulario de reserva de equipos no habilita la siguiente semana en viernes en producción, aunque funciona en local.

## Causa Raíz
Las vistas Blade están cacheadas en producción y no se han recompilado con el nuevo código PHP.

## Solución - Ejecutar en Producción

### 1. Conectarse al servidor de producción
```bash
ssh usuario@servidor-produccion
cd /ruta/del/proyecto/tvs
```

### 2. Limpiar TODOS los caches
```bash
# Limpiar cache de vistas compiladas (CRÍTICO)
php artisan view:clear

# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de rutas
php artisan route:clear

# Limpiar cache de aplicación
php artisan cache:clear

# Limpiar cache compilado de archivos
php artisan clear-compiled
```

### 3. Recompilar optimizaciones (OPCIONAL - solo si usa cache en producción)
```bash
# SOLO si producción usa cache de config
php artisan config:cache

# SOLO si producción usa cache de rutas
php artisan route:cache

# SOLO si producción usa cache de vistas
php artisan view:cache
```

### 4. Verificar permisos (si hay errores)
```bash
# Asegurar que Laravel pueda escribir en storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Verificación Rápida

### Confirmar que el cambio está aplicado
1. Abrir el navegador en producción
2. Ir a `/equipment/request`
3. Abrir DevTools (F12)
4. Inspeccionar el input de fecha:
   ```html
   <input type="date" name="loan_date" 
          min="2025-01-18" 
          max="2025-01-26">
   ```
5. Si hoy es viernes 17 de enero, el `max` debe ser **26 de enero** (domingo siguiente)

### Si sigue sin funcionar

#### A. Verificar que el archivo se subió correctamente
```bash
# Ver las últimas líneas del archivo request.blade.php
tail -50 /ruta/proyecto/resources/views/equipment/request.blade.php | grep -A 20 "@php"
```

Debe mostrar:
```php
@php
    $today = \Carbon\Carbon::now();
    $tomorrow = $today->copy()->addDay()->format('Y-m-d');
    
    // Determinar la fecha máxima según el día de la semana
    if ($today->dayOfWeek === 5 || $today->dayOfWeek === 6 || $today->dayOfWeek === 0) {
```

#### B. Verificar la zona horaria
```bash
php artisan tinker
```
Luego dentro de tinker:
```php
echo config('app.timezone');  // Debe ser: America/Bogota o America/Lima
echo now()->format('Y-m-d H:i:s l');  // Ver fecha y día actual del servidor
echo now()->dayOfWeek;  // Ver número del día (5 = viernes)
exit
```

#### C. Verificar versión de PHP
```bash
php -v  # Debe ser >= 7.4
```

#### D. Ver logs de errores
```bash
tail -100 storage/logs/laravel.log
```

## Comando Todo-en-Uno (Copiar y Pegar)

```bash
cd /ruta/del/proyecto/tvs && \
php artisan view:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan cache:clear && \
php artisan clear-compiled && \
echo "✅ Todos los caches limpiados correctamente"
```

## Configuración Recomendada para Producción

### Verificar en `config/app.php`:
```php
'timezone' => 'America/Bogota',  // o tu zona horaria
'locale' => 'es',
```

### Verificar en `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Bogota
```

## Notas Importantes

1. **NO** ejecutar `view:cache` a menos que sea absolutamente necesario para rendimiento
2. **SIEMPRE** ejecutar `view:clear` después de modificar archivos `.blade.php`
3. Si usas un servidor web como Nginx/Apache, puede ser necesario reiniciar:
   ```bash
   sudo systemctl restart nginx
   # o
   sudo systemctl restart apache2
   ```

## Comparación Local vs Producción

| Aspecto | Local | Producción |
|---------|-------|------------|
| Cache de vistas | Desactivado | **Activado** |
| APP_DEBUG | true | false |
| Recompilación automática | ✅ Sí | ❌ No (requiere clear) |
| Zona horaria | Verificar | **Verificar** |

## Después de Aplicar los Cambios

### Probar el formulario:
1. Ir a: `https://tu-dominio.com/equipment/request`
2. Verificar que hoy sea viernes
3. Abrir el selector de fecha
4. **DEBE** permitir seleccionar fechas hasta el **domingo de la próxima semana**
5. No debe limitarse al domingo de esta semana

### Ejemplo de fechas (si hoy es viernes 17 de enero):
- ✅ Permitido: 18/01 (sábado), 19/01 (domingo), 20/01 (lunes), ... hasta 26/01 (domingo siguiente)
- ❌ NO debe limitarse solo hasta: 19/01 (domingo de esta semana)

## Troubleshooting Avanzado

Si después de limpiar cache sigue sin funcionar:

### 1. Verificar compilación de la vista
```bash
# Ver la vista compilada
cat storage/framework/views/*.php | grep -A 30 "loan_date"
```

### 2. Forzar recompilación manual
```bash
rm -rf storage/framework/views/*.php
php artisan view:clear
```

### 3. Verificar permisos de escritura
```bash
ls -la storage/framework/views/
# Debe ser escribible por el usuario web (www-data, nginx, apache)
```

### 4. Modo de depuración temporal (CUIDADO)
En `.env` cambiar temporalmente:
```env
APP_DEBUG=true
```
Recargar la página y ver si hay errores de PHP en pantalla.
**NO OLVIDAR** volver a `APP_DEBUG=false` cuando termines.

## Contacto de Soporte
Si ninguna de estas soluciones funciona, revisar:
- Logs del servidor web: `/var/log/nginx/error.log` o `/var/log/apache2/error.log`
- Logs de PHP-FPM: `/var/log/php-fpm/error.log`
- Logs de Laravel: `storage/logs/laravel.log`
