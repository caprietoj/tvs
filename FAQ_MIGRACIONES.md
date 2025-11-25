# ❓ Preguntas Frecuentes (FAQ)

## 🔍 Sobre el Problema

### ¿Por qué aparece el error "Table already exists"?
Esto sucede cuando:
- La tabla existe físicamente en la base de datos
- Pero no está registrada en la tabla `migrations` de Laravel
- Laravel intenta crearla de nuevo y falla

### ¿Es peligroso este error?
No, es solo un problema de sincronización entre:
- El estado real de la base de datos
- El registro de migraciones de Laravel

---

## 🛠️ Sobre las Soluciones

### ¿Cuál solución debo usar?
Depende de tu preferencia:

**Script bash (`migrate-safe.sh`)** ← RECOMENDADO
- ✅ Todo automatizado
- ✅ Backup automático
- ✅ Más fácil de usar
- ❌ Requiere bash

**Comando Artisan** ← Para desarrolladores
- ✅ Integrado en Laravel
- ✅ Más control
- ✅ Funciona en Windows
- ⚠️ Requiere más pasos

**Manual con Tinker** ← Para casos específicos
- ✅ Máximo control
- ✅ Para una migración puntual
- ❌ Más manual

### ¿Puedo usar esto en producción?
**Sí**, está diseñado específicamente para producción:
- ✅ No borra datos
- ✅ Hace backup
- ✅ Modo dry-run
- ✅ Reversible

### ¿Qué hace el modo `--dry-run`?
Muestra qué se haría **sin hacer cambios reales**:
- Lista las migraciones a registrar
- Muestra las tablas detectadas
- No modifica la base de datos
- No inserta registros

---

## 🔒 Sobre Seguridad

### ¿Se perderán datos?
**No**. Esta solución:
- ❌ NO borra tablas
- ❌ NO modifica datos
- ❌ NO hace DROP
- ✅ Solo registra migraciones
- ✅ Hace backup antes

### ¿Qué pasa si algo sale mal?
Tienes múltiples capas de protección:
1. **Backup automático** creado antes
2. **Modo mantenimiento** (opcional)
3. **Logs detallados** de todo
4. **Plan de reversión** documentado

### ¿Es reversible?
**Sí**, puedes:
1. Restaurar el backup creado
2. Borrar registros de la tabla `migrations`
3. Volver al estado anterior

---

## 📦 Sobre el Script

### ¿Cómo funciona `migrate-safe.sh`?
```
1. Verifica conexión a BD
2. Crea backup con timestamp
3. Comprime el backup (gzip)
4. Ejecuta migrate:register-existing
5. Ejecuta migrate
6. Muestra estado final
```

### ¿Qué opciones tiene el script?
```bash
--dry-run      # Ver qué haría sin hacer cambios
--force        # No pedir confirmación
--no-backup    # No crear backup (no recomendado)
--help         # Mostrar ayuda
```

### ¿Dónde se guardan los backups?
En `backups/backup_[nombre_bd]_[fecha].sql.gz`

Ejemplo: `backups/backup_tvs_20251006_143000.sql.gz`

### ¿Puedo restaurar un backup automático?
**Sí**:
```bash
# Descomprimir
gunzip backups/backup_tvs_20251006_143000.sql.gz

# Restaurar
mysql -u root -p tvs < backups/backup_tvs_20251006_143000.sql
```

---

## 🎨 Sobre el Comando Artisan

### ¿Cómo funciona `migrate:register-existing`?
1. Lee todos los archivos de migración
2. Compara con tabla `migrations`
3. Detecta las pendientes
4. Extrae nombre de tabla del archivo
5. Verifica si la tabla existe
6. Registra solo las que existen

### ¿Qué patrones de nombres detecta?
```php
'create_users_table'                    → users
'add_column_to_users_table'             → users
'modify_column_in_users'                → users
'update_users_table'                    → users
'alter_users_table'                     → users
```

### ¿Puedo agregar más patrones?
**Sí**, edita el método `extractTableName()` en:
`app/Console/Commands/RegisterExistingMigrations.php`

### ¿Funciona en Windows?
**Sí**, es un comando Artisan de Laravel, funciona en todos los OS.

---

## 📊 Sobre Migraciones

### ¿Qué es un "batch"?
Es un número que agrupa migraciones ejecutadas juntas:
```
Batch 1: Migraciones iniciales
Batch 2: Primera actualización
Batch 3: Segunda actualización
```

### ¿Importa el número de batch?
No mucho, pero:
- ✅ Ayuda a saber cuándo se ejecutó
- ✅ Útil para hacer rollback
- ⚠️ El script usa `max(batch) + 1`

### ¿Puedo hacer rollback después?
**Sí**, pero:
- El rollback solo deshace migraciones normales
- No deshace las registradas manualmente
- Usa `php artisan migrate:rollback --step=1`

### ¿Afecta a otras migraciones?
**No**, solo:
- Registra las que ya están ejecutadas
- Las nuevas se ejecutan normalmente

---

## 🚀 Sobre Uso en Producción

### ¿Debo poner el sitio en mantenimiento?
**Recomendado pero no obligatorio**:
```bash
# Antes
php artisan down

# Después
php artisan up
```

### ¿Cuánto tiempo tarda?
Depende del tamaño de la BD:
- Backup: 1-5 minutos
- Registro: < 1 segundo
- Migraciones: 1-10 segundos
- **Total: 2-15 minutos típicamente**

### ¿En qué horario debo hacerlo?
**Baja actividad**:
- Madrugada (2-5 AM)
- Fines de semana
- Horarios no laborales

### ¿Debo notificar a usuarios?
**Sí, si pones en modo mantenimiento**:
- Aviso 24-48 horas antes
- Recordatorio 1 hora antes
- Notificación durante
- Confirmación después

---

## 🆘 Troubleshooting

### El script no se ejecuta
```bash
# Dar permisos
chmod +x migrate-safe.sh

# Verificar bash
which bash
```

### Error "Connection refused"
```bash
# Limpiar config
php artisan config:clear

# Verificar .env
cat .env | grep DB_

# Probar conexión
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

### No detecta mi migración
**Posibles causas**:
1. Nombre de archivo no sigue convención
2. Tabla tiene nombre diferente al esperado
3. Migración no es de tipo `create_*_table`

**Solución**:
- Usa Tinker manual para esa migración específica
- O edita el comando para agregar el patrón

### Error "Batch cannot be null"
El comando usa `max(batch) + 1`, si falla:
```php
// En Tinker
DB::table('migrations')->max('batch');  // Debe retornar número
```

### Backup muy grande
```bash
# Ver tamaño de BD
du -sh /var/lib/mysql/nombre_bd

# Usar --no-backup y hacer backup manual antes
mysqldump nombre_bd | gzip > backup_manual.sql.gz
```

---

## 📈 Mejores Prácticas

### Antes de ejecutar
- [ ] Leer documentación completa
- [ ] Probar en desarrollo
- [ ] Hacer backup manual adicional
- [ ] Usar `--dry-run` primero
- [ ] Definir plan B

### Durante ejecución
- [ ] Modo mantenimiento
- [ ] Monitorear logs
- [ ] No interrumpir proceso
- [ ] Guardar output completo

### Después de ejecutar
- [ ] Verificar estado
- [ ] Probar funcionalidad
- [ ] Revisar logs
- [ ] Monitorear rendimiento
- [ ] Documentar en checklist

---

## 🔧 Personalización

### ¿Puedo modificar el script?
**Sí**, es un archivo bash normal:
- Editar rutas de backup
- Cambiar colores de output
- Agregar validaciones
- Integrar con sistemas de monitoreo

### ¿Puedo modificar el comando?
**Sí**, es un comando Artisan estándar:
- Agregar más patrones de detección
- Cambiar lógica de registro
- Agregar notificaciones
- Integrar con logs externos

---

## 📞 Soporte

### ¿Dónde reporto problemas?
1. Revisar logs: `storage/logs/laravel.log`
2. Verificar estado: `php artisan migrate:status`
3. Consultar FAQ (este archivo)
4. Contactar equipo de desarrollo

### ¿Hay soporte 24/7?
Según tu organización. Define contactos en:
`CHECKLIST_MIGRACION.md` → Contactos de Emergencia

---

## 📚 Referencias Adicionales

- [Documentación Laravel - Migrations](https://laravel.com/docs/migrations)
- [MySQL Backup Best Practices](https://dev.mysql.com/doc/refman/8.0/en/backup-and-recovery.html)
- Archivos locales:
  - `RESUMEN_MIGRACION.md`
  - `MIGRACION_PRODUCCION.md`
  - `CHECKLIST_MIGRACION.md`

---

## 💡 Tips Adicionales

### Automatizar en CI/CD
```yaml
# .github/workflows/deploy.yml
- name: Run migrations safely
  run: |
    ./migrate-safe.sh --force --no-backup
```

### Slack notifications
Agregar al script:
```bash
# Después de migración exitosa
curl -X POST -H 'Content-type: application/json' \
  --data '{"text":"✅ Migraciones completadas"}' \
  $SLACK_WEBHOOK_URL
```

### Monitoreo con logs
```bash
# Agregar al final del script
php artisan migrate:status > /var/log/migrations_$(date +%Y%m%d).log
```

---

**¿No encontraste tu pregunta?**
Agrega tu caso al final de este archivo y compártelo con el equipo.

═══════════════════════════════════════════════════════════════════════════════
Última actualización: Octubre 2025
Versión: 1.0
═══════════════════════════════════════════════════════════════════════════════
