# ✅ Checklist de Migración para Producción

## 📋 ANTES de la migración

### Preparación
- [ ] Leer `RESUMEN_MIGRACION.md` y `MIGRACION_PRODUCCION.md`
- [ ] Notificar al equipo sobre el mantenimiento
- [ ] Definir ventana de mantenimiento (horario de baja actividad)
- [ ] Tener acceso SSH/terminal al servidor
- [ ] Tener credenciales de base de datos

### Backups
- [ ] Backup completo de la base de datos
- [ ] Backup de archivos del proyecto (opcional)
- [ ] Verificar que el backup se completó correctamente
- [ ] Guardar backup en ubicación segura (fuera del servidor)
- [ ] Probar que el backup es válido (opcional pero recomendado)

### Verificaciones
- [ ] Verificar espacio en disco disponible (`df -h`)
- [ ] Verificar que el sitio está funcionando
- [ ] Verificar conexión a base de datos: `php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"`
- [ ] Verificar estado actual: `php artisan migrate:status`
- [ ] Anotar el número del último batch

---

## 🔄 DURANTE la migración

### Ejecución del Script (Método Recomendado)
- [ ] Poner sitio en modo mantenimiento: `php artisan down`
- [ ] Ejecutar dry-run: `./migrate-safe.sh --dry-run`
- [ ] Revisar la salida del dry-run cuidadosamente
- [ ] Confirmar que las tablas detectadas son correctas
- [ ] Ejecutar el script: `./migrate-safe.sh`
- [ ] Revisar la salida para errores
- [ ] Verificar que el backup se creó
- [ ] Anotar el nuevo número de batch

### Alternativa: Comando Artisan
- [ ] Poner sitio en modo mantenimiento: `php artisan down`
- [ ] Ejecutar: `php artisan migrate:register-existing --dry-run`
- [ ] Revisar la salida
- [ ] Ejecutar: `php artisan migrate:register-existing`
- [ ] Ejecutar: `php artisan migrate`
- [ ] Revisar salida para errores

---

## ✅ DESPUÉS de la migración

### Verificaciones Inmediatas
- [ ] Verificar estado: `php artisan migrate:status`
- [ ] Confirmar que no hay migraciones pendientes inesperadas
- [ ] Verificar logs: `tail -100 storage/logs/laravel.log`
- [ ] Buscar errores en logs: `grep -i error storage/logs/laravel.log | tail -20`
- [ ] Limpiar cache: `php artisan cache:clear`
- [ ] Limpiar config: `php artisan config:clear`
- [ ] Limpiar vistas: `php artisan view:clear`

### Pruebas Funcionales
- [ ] Sacar sitio de modo mantenimiento: `php artisan up`
- [ ] Probar login al sistema
- [ ] Probar módulo de portería (registro/personas)
- [ ] Verificar que las tablas tienen datos esperados
- [ ] Probar funcionalidades críticas del sistema
- [ ] Revisar errores en navegador (consola JavaScript)

### Monitoreo
- [ ] Monitorear logs en tiempo real: `tail -f storage/logs/laravel.log`
- [ ] Verificar rendimiento del sitio
- [ ] Revisar métricas del servidor (CPU, RAM, disco)
- [ ] Estar atento a reportes de usuarios

---

## 🆘 Si algo sale MAL

### Pasos de Reversión
1. [ ] Poner sitio en modo mantenimiento: `php artisan down`
2. [ ] Restaurar backup de BD:
   ```bash
   # Descomprimir backup si está comprimido
   gunzip backup_[fecha].sql.gz
   
   # Restaurar
   mysql -u root -p nombre_bd < backup_[fecha].sql
   ```
3. [ ] Verificar que la restauración funcionó
4. [ ] Probar funcionalidad básica
5. [ ] Sacar de modo mantenimiento: `php artisan up`
6. [ ] Analizar qué salió mal
7. [ ] Contactar al equipo de desarrollo

---

## 📊 Registro de la Migración

**Fecha y Hora de Inicio**: _______________

**Ejecutado por**: _______________

**Método utilizado**: 
- [ ] Script `migrate-safe.sh`
- [ ] Comando `migrate:register-existing`
- [ ] Manual con Tinker

**Migraciones registradas**:
```
[Anotar aquí las migraciones que se registraron]
```

**Migraciones ejecutadas**:
```
[Anotar aquí las migraciones nuevas que se ejecutaron]
```

**Batch anterior**: _______________

**Batch nuevo**: _______________

**Backup creado en**: _______________

**Tamaño del backup**: _______________

**Duración total**: _______________ minutos

**Incidencias**:
```
[Anotar aquí cualquier problema o incidencia]
```

**Estado final**:
- [ ] ✅ Exitoso sin problemas
- [ ] ⚠️ Exitoso con advertencias
- [ ] ❌ Fallido - revertido
- [ ] ❌ Fallido - pendiente de revisión

**Fecha y Hora de Fin**: _______________

**Observaciones adicionales**:
```
[Anotar aquí cualquier observación relevante]
```

---

## 📞 Contactos de Emergencia

**Desarrollador Principal**: _______________

**DBA**: _______________

**Administrador de Sistemas**: _______________

**Soporte Técnico**: _______________

---

## 📚 Referencias

- `RESUMEN_MIGRACION.md` - Resumen rápido
- `MIGRACION_PRODUCCION.md` - Guía completa
- `migrate-safe.sh` - Script de migración
- `app/Console/Commands/RegisterExistingMigrations.php` - Comando Artisan

---

**✅ Al completar este checklist, tendrás una migración segura y documentada**
