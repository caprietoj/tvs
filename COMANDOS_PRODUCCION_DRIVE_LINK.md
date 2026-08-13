# Comandos para Agregar Columna drive_link en Producción

## Problema
La tabla `previsita_consolidados` en producción causa el error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'drive_link' in 'field list'
```

**CAUSA:** La migración está ejecutada y la columna existe en la BD, pero Laravel tiene el esquema en caché.

## Solución - LIMPIAR CACHÉS (EJECUTAR EN PRODUCCIÓN)

```bash
# 1. Limpiar TODOS los cachés (OBLIGATORIO)
php artisan optimize:clear

# 2. Verificar que la columna existe
php artisan tinker --execute="echo implode(', ', Schema::getColumnListing('previsita_consolidados'));"

# 3. Si ves 'drive_link' en la lista, reiniciar el servidor web
# Para Apache:
sudo systemctl restart apache2
# O para Nginx con PHP-FPM:
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Estado de la Migración

✅ La migración **YA ESTÁ EJECUTADA** en producción (Batch 46)
✅ La columna **YA EXISTE** en la base de datos
❌ El problema es **CACHÉ DE LARAVEL**

## Verificación Post-Limpieza

Después de ejecutar `php artisan optimize:clear`, verifica:

```bash
# Verificar columnas de la tabla
php artisan tinker --execute="print_r(DB::select('SHOW COLUMNS FROM previsita_consolidados WHERE Field = \"drive_link\"'));"
```

Deberías ver algo como:
```
Array
(
    [0] => stdClass Object
        (
            [Field] => drive_link
            [Type] => varchar(191)
            [Null] => YES
        )
)
```

## ⚠️ IMPORTANTE: Reiniciar Servidor Web

Después de limpiar cachés, **DEBES reiniciar** el servidor web para que PHP recargue el código:

```bash
# Para Apache (más común)
sudo systemctl restart apache2

# O para Nginx con PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

**Sin reiniciar el servidor, el error puede persistir** debido a OPcache de PHP.

### Opción 2: Ejecutar Solo la Migración Específica

Si solo quieres ejecutar la migración de drive_link:

```bash
# Ejecutar la migración específica
php artisan migrate --path=/database/migrations/2025_09_29_114623_add_drive_link_to_previsita_consolidados_table.php --force

# Limpiar cachés
php artisan optimize:clear
```

### Opción 3: SQL Directo (Solo si las opciones anteriores fallan)

Si por alguna razón las migraciones no funcionan, puedes ejecutar este SQL directamente:

```sql
-- Verificar si la columna existe
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'nombre_base_datos' 
  AND TABLE_NAME = 'previsita_consolidados' 
  AND COLUMN_NAME = 'drive_link';

-- Si no existe, agregarla
ALTER TABLE previsita_consolidados 
ADD COLUMN drive_link VARCHAR(191) NULL 
AFTER novedades_visita_archivo;
```

## Verificación

Después de ejecutar los comandos, verificar que la columna existe:

```bash
php artisan tinker --execute="echo implode(', ', Schema::getColumnListing('previsita_consolidados'));"
```

Deberías ver `drive_link` en la lista de columnas.

## Migración Involucrada

**Archivo:** `database/migrations/2025_09_29_114623_add_drive_link_to_previsita_consolidados_table.php`

**Columna agregada:**
- Nombre: `drive_link`
- Tipo: VARCHAR(191)
- Nullable: Sí
- Posición: Después de `novedades_visita_archivo`

## Fecha de Creación de la Migración
29 de septiembre de 2025

## Fecha del Error
20 de octubre de 2025
