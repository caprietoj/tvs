# 🚀 Guía de Migración Segura para Producción

## Problema
Cuando una tabla ya existe en la base de datos pero la migración no está registrada en la tabla `migrations`, Laravel intenta crearla nuevamente y genera el error:
```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'nombre_tabla' already exists
```

## ✅ Solución Segura (SIN pérdida de datos)

### Opción 1: Script Automatizado (MÁS FÁCIL) 🎯

Se creó un script bash que hace todo el proceso automáticamente:

```bash
# Ver qué haría sin hacer cambios
./migrate-safe.sh --dry-run

# Ejecutar con backup automático (RECOMENDADO)
./migrate-safe.sh

# Ejecutar sin backup (no recomendado)
./migrate-safe.sh --no-backup

# Ejecutar sin confirmaciones (para CI/CD)
./migrate-safe.sh --force
```

**El script hace:**
1. ✅ Verifica conexión a la base de datos
2. ✅ Crea backup automático de la BD
3. ✅ Registra tablas existentes
4. ✅ Ejecuta migraciones pendientes
5. ✅ Muestra estado final

---

### Opción 2: Comando Artisan Personalizado

Se creó el comando `migrate:register-existing` que registra automáticamente las tablas que ya existen.

#### Uso:

**1. Modo Dry-Run (ver qué se haría sin hacer cambios):**
```bash
php artisan migrate:register-existing --dry-run
```

**2. Modo interactivo (te pide confirmación):**
```bash
php artisan migrate:register-existing
```

**3. Modo forzado (sin confirmación - para scripts automatizados):**
```bash
php artisan migrate:register-existing --force
```

**4. Después de registrar, ejecutar las migraciones restantes:**
```bash
php artisan migrate
```

#### Ejemplo de uso completo:
```bash
# Paso 1: Ver qué se va a hacer
php artisan migrate:register-existing --dry-run

# Paso 2: Si todo se ve bien, ejecutar
php artisan migrate:register-existing

# Paso 3: Ejecutar las migraciones restantes
php artisan migrate
```

---

### Opción 2: Registro Manual Vía Tinker

Si prefieres hacerlo manualmente para una migración específica:

```bash
php artisan tinker
```

Dentro de Tinker:
```php
// Verificar si la tabla existe
Schema::hasTable('nombre_tabla'); // debe retornar true

// Si existe, registrar la migración
DB::table('migrations')->insert([
    'migration' => '2025_05_06_000000_create_equipment_blocks_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);

// Salir
exit
```

Luego ejecutar:
```bash
php artisan migrate
```

---

### Opción 3: SQL Directo (MySQL/MariaDB)

```sql
-- Verificar si la tabla existe
SHOW TABLES LIKE 'equipment_blocks';

-- Si existe, registrar la migración
INSERT INTO migrations (migration, batch) 
VALUES (
    '2025_05_06_000000_create_equipment_blocks_table',
    (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as temp)
);
```

Luego ejecutar:
```bash
php artisan migrate
```

---

## 📋 Checklist para Producción

### Antes de ejecutar:
- [ ] Hacer backup completo de la base de datos
- [ ] Verificar qué migraciones están pendientes: `php artisan migrate:status`
- [ ] Verificar qué tablas existen: `php artisan tinker --execute="print_r(Schema::getTableListing());"`
- [ ] Usar modo `--dry-run` primero

### Durante la ejecución:
- [ ] Ejecutar `migrate:register-existing --dry-run`
- [ ] Revisar la salida cuidadosamente
- [ ] Ejecutar `migrate:register-existing` (con o sin `--force`)
- [ ] Ejecutar `php artisan migrate`
- [ ] Verificar estado: `php artisan migrate:status`

### Después de ejecutar:
- [ ] Verificar que no hay migraciones pendientes inesperadas
- [ ] Probar funcionalidad de los módulos afectados
- [ ] Revisar logs de Laravel: `storage/logs/laravel.log`

---

## 🔍 Verificaciones Útiles

### Ver estado de todas las migraciones:
```bash
php artisan migrate:status
```

### Ver solo las pendientes:
```bash
php artisan migrate:status | grep Pending
```

### Ver tablas específicas:
```bash
php artisan tinker --execute="
\$tables = ['equipment_blocks', 'registro_porteria', 'personas'];
foreach (\$tables as \$table) {
    \$exists = Schema::hasTable(\$table) ? '✓ Existe' : '✗ NO existe';
    echo \"\$table: \$exists\n\";
}"
```

### Ver último batch de migraciones:
```bash
php artisan tinker --execute="
echo 'Último batch: ' . DB::table('migrations')->max('batch');
"
```

---

## ⚠️ IMPORTANTE - NO HACER EN PRODUCCIÓN

**❌ NUNCA ejecutar estos comandos en producción:**
```bash
php artisan migrate:fresh      # BORRA TODAS LAS TABLAS
php artisan migrate:refresh    # HACE ROLLBACK Y RE-EJECUTA TODO
php artisan migrate:reset      # HACE ROLLBACK DE TODO
php artisan db:wipe           # BORRA TODO
```

Estos comandos **ELIMINAN TODOS LOS DATOS**.

---

## 🆘 Si algo sale mal

### Restaurar desde backup:
```bash
# MySQL/MariaDB
mysql -u usuario -p nombre_bd < backup.sql

# Luego verificar
php artisan migrate:status
```

### Ver logs de error:
```bash
tail -f storage/logs/laravel.log
```

### Verificar conexión a BD:
```bash
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexión OK';"
```

---

## 📞 Soporte

Si encuentras problemas:
1. Revisa `storage/logs/laravel.log`
2. Ejecuta `php artisan migrate:status`
3. Verifica permisos de la base de datos
4. Contacta al equipo de desarrollo con los logs

---

## 🎯 Resumen Rápido

```bash
# Solución completa en 3 pasos:
php artisan migrate:register-existing --dry-run  # Ver qué se hará
php artisan migrate:register-existing            # Registrar tablas existentes
php artisan migrate                              # Ejecutar pendientes
```

**✅ Esta solución NO borra datos, solo registra migraciones que ya se ejecutaron manualmente.**
