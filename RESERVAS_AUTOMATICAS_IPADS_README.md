# Reservas Automáticas de iPads para Cursos 4° (4a y 4b)

## Descripción

El sistema crea automáticamente reservas de **10 iPads por curso** (20 iPads en total) para los cursos **4a** y **4b** en todos los días de ciclo escolar número **5**.

## Funcionamiento

### Creación Automática

Las reservas se crean automáticamente en los siguientes casos:

1. **Al crear un nuevo ciclo escolar** con la opción "Generar días de ciclo automáticamente" activada
2. **Al generar o regenerar días de ciclo** desde la vista de detalle del ciclo escolar

### Especificaciones de las Reservas

- **Cursos:** 4a y 4b
- **Cantidad:** 10 iPads por curso (20 iPads en total)
- **Día del ciclo:** Día 5
- **Horario:** 08:00 - 09:30 (1 hora 30 minutos)
- **Sección:** Preescolar y Primaria
- **Subsección:** Primaria
- **Estado inicial:** Pendiente
- **Devolución:** Automática

### Detalles Técnicos

- Las reservas se crean con `teacher_name = "Reserva Automática - Cursos 4°"`
- Se marca `auto_return = true` para devolución automática
- El sistema verifica que no existan reservas duplicadas antes de crear nuevas

## Comando Artisan

### Ejecución Manual

Puedes ejecutar el comando manualmente para crear las reservas:

```bash
php artisan equipment:create-automatic-ipad-reservations
```

### Con Ciclo Específico

```bash
php artisan equipment:create-automatic-ipad-reservations --school-cycle-id=1
```

### Parámetros

- `--school-cycle-id` (opcional): ID del ciclo escolar específico. Si no se proporciona, usa el ciclo activo.

## Salida del Comando

El comando muestra información detallada:

```
Procesando ciclo escolar: 2026
Equipo encontrado: 40 iPads disponibles
Se encontraron 15 días de ciclo 5
  ✓ Creada: Reserva de 10 iPads para 4a el 2026-02-05
  ✓ Creada: Reserva de 10 iPads para 4b el 2026-02-05
  - Omitida: Ya existe reserva para 4a el 2026-02-12
  ✓ Creada: Reserva de 10 iPads para 4b el 2026-02-12
  ...

=== Resumen ===
Reservas creadas: 28
Reservas omitidas (ya existían): 2
Errores: 0
```

## Requisitos del Sistema

### Equipment (iPads)

Debe existir un equipo de tipo `ipad` para la sección `preescolar_primaria` en la tabla `equipment`:

```sql
SELECT * FROM equipment 
WHERE type = 'ipad' 
AND section = 'preescolar_primaria';
```

### Usuario Administrador

El sistema usa un usuario con rol `admin` para crear las reservas. Si no existe, el comando fallará.

### Ciclo Escolar con Días Generados

El ciclo escolar debe tener días generados en la tabla `cycle_days` con `cycle_day = 5`.

## Modificaciones en el Sistema

### Archivos Creados

1. **`app/Console/Commands/CreateAutomaticIpadReservations.php`**
   - Comando Artisan que crea las reservas automáticas

### Archivos Modificados

1. **`app/Http/Controllers/SchoolCycleController.php`**
   - Método `store()`: Llama al comando al crear un ciclo con días generados
   - Método `generateCycleDays()`: Llama al comando al generar/regenerar días

2. **`app/Models/EquipmentLoan.php`**
   - Agregados campos `subsection` y `teacher_name` al array `$fillable`

## Logs

El sistema registra todas las acciones en los logs de Laravel:

```php
Log::info('Reservas automáticas de iPads creadas', [
    'school_cycle' => 'Ciclo 2026',
    'created' => 28,
    'skipped' => 2,
    'errors' => 0
]);
```

## Personalización

### Cambiar el Día del Ciclo

Para cambiar del día 5 a otro día (por ejemplo, día 3):

```php
// En CreateAutomaticIpadReservations.php, línea ~61
$day5Dates = CycleDay::where('school_cycle_id', $schoolCycle->id)
    ->where('cycle_day', 3) // Cambiar de 5 a 3
    ->orderBy('date')
    ->get();
```

### Cambiar la Cantidad de iPads

```php
// En CreateAutomaticIpadReservations.php, línea ~161
'units_requested' => 15, // Cambiar de 10 a 15 por curso
```

### Cambiar el Horario

```php
// En CreateAutomaticIpadReservations.php, líneas ~93-100
$result4a = $this->createReservation(
    $ipadEquipment,
    $adminUser,
    $date,
    '4a',
    '10:00', // Cambiar hora de inicio
    '11:30'  // Cambiar hora de fin
);
```

### Agregar Más Cursos

Para agregar otro curso (por ejemplo, 5a):

```php
// Después de la creación de reservas para 4b
$result5a = $this->createReservation(
    $ipadEquipment,
    $adminUser,
    $date,
    '5a',
    '08:00',
    '09:30'
);

if ($result5a === 'created') {
    $created++;
} elseif ($result5a === 'skipped') {
    $skipped++;
} else {
    $errors++;
}
```

## Gestión de Reservas

Las reservas creadas automáticamente pueden ser:

1. **Visualizadas** en el módulo de Préstamos de Equipos
2. **Modificadas** por usuarios con permisos de administración de equipos
3. **Eliminadas** si es necesario
4. **Entregadas** cuando se retiran los iPads
5. **Devueltas** automáticamente al finalizar el horario

## Verificación

Para verificar que las reservas se crearon correctamente:

```sql
SELECT 
    el.*,
    cd.date,
    cd.cycle_day
FROM equipment_loans el
JOIN cycle_days cd ON el.loan_date = cd.date
WHERE el.grade IN ('4a', '4b')
AND cd.cycle_day = 5
AND el.teacher_name = 'Reserva Automática - Cursos 4°'
ORDER BY el.loan_date;
```

## Solución de Problemas

### Error: "No se encontró equipo de iPads para preescolar y primaria"

**Solución:** Crear un equipo de iPads:

```sql
INSERT INTO equipment (type, section, total_units, available_units, created_at, updated_at)
VALUES ('ipad', 'preescolar_primaria', 40, 40, NOW(), NOW());
```

### Error: "No se encontró un usuario administrador"

**Solución:** Asegurarse de que existe al menos un usuario con rol `admin`.

### Las reservas no se crean

**Verificar:**
1. Que existan días 5 en el ciclo escolar
2. Que el equipo de iPads exista
3. Revisar los logs de Laravel para errores

## Fecha de Implementación

- **Fecha:** 22 de enero de 2026
- **Versión:** 1.0
- **Autor:** Sistema TVS

## Notas Adicionales

- El sistema es idempotente: puede ejecutarse múltiples veces sin crear duplicados
- Las reservas se crean en estado "pendiente" y deben ser entregadas manualmente
- La devolución está configurada como automática según el horario
