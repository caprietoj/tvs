# 📋 Comandos para Aplicar en Producción

## 🔒 Pre-requisitos

Antes de ejecutar estos comandos:
- [ ] Tener acceso SSH al servidor de producción
- [ ] Tener permisos para ejecutar comandos de base de datos
- [ ] Avisar a usuarios que habrá mantenimiento breve

## 📝 Comandos Paso a Paso

### 1️⃣ Conectar al Servidor de Producción

```bash
ssh usuario@intranet.tvs.edu.co
```

### 2️⃣ Ir al Directorio del Proyecto

```bash
cd /var/www/html/intranet
# O la ruta donde esté instalado el proyecto
```

### 3️⃣ Hacer Backup de la Base de Datos (IMPORTANTE)

```bash
# Opción 1: Backup completo
mysqldump -u usuario_db -p nombre_base_datos > backup_antes_cambios_$(date +%Y%m%d_%H%M%S).sql

# Opción 2: Backup solo de la tabla personas
mysqldump -u usuario_db -p nombre_base_datos personas > backup_personas_$(date +%Y%m%d_%H%M%S).sql
```

### 4️⃣ Verificar Estructura Actual (Opcional pero Recomendado)

```bash
# Ver si la columna apellido existe
php artisan tinker
>>> Schema::hasColumn('personas', 'apellido')
>>> exit
```

Resultado esperado:
- `true` = La columna existe (será eliminada)
- `false` = La columna NO existe (migración se saltará sin errores)

### 5️⃣ Descargar Últimos Cambios del Repositorio

```bash
git status
git pull origin main
```

### 6️⃣ Poner Aplicación en Modo Mantenimiento

```bash
php artisan down --message="Actualización en progreso, volveremos en 5 minutos" --retry=60
```

### 7️⃣ Ejecutar Migraciones

```bash
php artisan migrate --force
```

**Resultado esperado:**
```
Running migrations.
2025_10_17_091928_make_apellido_nullable_in_personas_table ... DONE
```

### 8️⃣ Limpiar y Regenerar Cachés

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9️⃣ Verificar que Todo Está Correcto

```bash
# Verificar que la columna fue eliminada
php artisan tinker
>>> Schema::hasColumn('personas', 'apellido')
# Debe retornar: false

>>> Schema::getColumnListing('personas')
# Debe mostrar todos los campos EXCEPTO 'apellido'

>>> App\Models\Persona::count()
# Debe mostrar el número total de personas

>>> exit
```

### 🔟 Sacar Aplicación del Modo Mantenimiento

```bash
php artisan up
```

## ✅ Verificaciones Post-Despliegue

### Verificar en el Navegador

1. **Ir a:** https://intranet.tvs.edu.co/porteria/personas
   - Debe cargar sin errores
   - Debe mostrar las personas existentes

2. **Ir a:** https://intranet.tvs.edu.co/porteria/personas/create
   - Debe mostrar el formulario
   - Solo debe aparecer el campo "Nombre Completo" (no apellido)

3. **Ir a:** https://intranet.tvs.edu.co/porteria/personas/import
   - Debe mostrar la página de importación
   - Debe mencionar "3 columnas"

### Prueba de Importación

Copiar estos datos de prueba:
```
1234567890	Prueba Usuario	Primero 1A
```

Pegar en el formulario de importación y verificar que:
- ✅ Se importa sin errores
- ✅ El nombre se guarda completo: "Prueba Usuario"
- ✅ El grado se guarda: "Primero 1A"
- ✅ El tipo se detecta como: "estudiante"

### Verificar en Base de Datos

```bash
mysql -u usuario_db -p nombre_base_datos

mysql> DESCRIBE personas;
# No debe aparecer la columna 'apellido'

mysql> SELECT id, documento, nombre, tipo_persona, grado FROM personas LIMIT 5;
# Verificar que los datos se ven correctos

mysql> exit
```

## 🚨 Si Algo Sale Mal

### Restaurar Backup

```bash
# Detener aplicación
php artisan down

# Restaurar backup
mysql -u usuario_db -p nombre_base_datos < backup_antes_cambios_XXXXXX.sql

# Revertir código
git reset --hard HEAD~1

# Limpiar cachés
php artisan cache:clear
php artisan config:clear

# Activar aplicación
php artisan up
```

### Marcar Migración como Ejecutada Manualmente

Si la migración falla pero los cambios ya están aplicados:

```sql
INSERT INTO migrations (migration, batch) 
VALUES ('2025_10_17_091928_make_apellido_nullable_in_personas_table', 
        (SELECT MAX(batch) + 1 FROM migrations));
```

## 📞 Contactos de Soporte

- **Desarrollador:** [Nombre]
- **DBA:** [Nombre]
- **Hora estimada:** 5-10 minutos

## ⏱️ Ventana de Mantenimiento Sugerida

- **Mejor momento:** Fuera de horario laboral (después de 6:00 PM)
- **Duración estimada:** 10 minutos
- **Impacto:** Mínimo (solo módulo de portería)

---

**Fecha de creación:** 17 de octubre de 2025
**Archivo de migración:** `2025_10_17_091928_make_apellido_nullable_in_personas_table.php`
