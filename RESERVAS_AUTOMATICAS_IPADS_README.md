# Bloqueos Automáticos de iPads para Cursos 4° (4a y 4b)

## Descripción

El sistema crea automáticamente **bloqueos de equipos** de 20 iPads (10 para 4a + 10 para 4b) en todos los **días 5 del ciclo escolar** en el horario de **08:00 - 09:30**. Esto impide que otros usuarios reserven estos iPads durante ese horario en los días 5.

## Funcionamiento

### Creación Automática

Los bloqueos se crean automáticamente en los siguientes casos:

1. **Al crear un nuevo ciclo escolar** con la opción "Generar días de ciclo automáticamente" activada
2. **Al generar o regenerar días de ciclo** desde la vista de detalle del ciclo escolar

### Especificaciones de los Bloqueos

- **Cursos:** 4a y 4b
- **Cantidad bloqueada:** 20 iPads (10 para 4a + 10 para 4b)
- **Día del ciclo:** Día 5
- **Horario:** 08:00 - 09:30 (1 hora 30 minutos)
- **Sección:** Preescolar y Primaria
- **Razón:** "Cursos 4a y 4b (10 iPads por curso)"
- **Tipo:** Bloqueo por día de ciclo (no semanal)

### Cómo Funciona

Cuando se generan los días del ciclo escolar, el sistema:

1. Identifica todos los días marcados como **día 5** del ciclo
2. Crea UN SOLO bloqueo de equipos que aplica a TODOS los días 5
3. El bloqueo reserva 20 iPads de 08:00 a 09:30
4. El sistema automáticamente impedirá reservas de otros usuarios en ese horario para los días 5

### Detalles Técnicos

- Se crea un registro en la tabla `equipment_blocks` con `cycle_day = 5`
- El bloqueo es persistente y aplica automáticamente a todos los días 5 del ciclo
- No se requiere crear reservas individuales por fecha
- El sistema calcula automáticamente qué fechas corresponden al día 5 del ciclo
- Los usuarios verán los iPads como "no disponibles" en ese horario para días 5

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

✓ Bloqueo creado exitosamente
  - 20 iPads bloqueados en todos los días 5 del ciclo
  - Horario: 08:00 - 09:30
  - Razón: Cursos 4a y 4b (10 iPads por curso)readas: 28
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


## Verificación del Bloqueo

Para verificar que el bloqueo se creó correctamente:

```sql
SELECT el bloqueo automático de equipo
    eb.*,
    e.type,
    e.section,
    sc.name as cycle_name

## Diferencia entre Bloqueos y Reservas

### Bloqueos (EquipmentBlock) - IMPLEMENTADO
- **Propósito:** Impedir que los equipos sean reservados
- **Alcance:** Aplica automáticamente a todos los días 5 del ciclo
- **Ventaja:** Un solo registro bloquea múltiples fechas
- **Uso:** Protege el horario para los cursos 4a y 4b

### Reservas (EquipmentLoan) - NO IMPLEMENTADO
- **Propósito:** Registrar el préstamo de equipos
- **Alcance:** Una reserva por fecha específica
- **Desventaja:** Requiere múltiples registros (uno por cada día 5)
- **Uso:** Para préstamos normales de usuarios
FROM equipment_blocks eb
JOIN equipment e ON eb.equipment_id = e.id
JOIN school_cycles sc ON eb.school_cycle_id = sc.id
WHERE eb.cycle_day = 5
AND e.type = 'ipad'
AND e.section = 'preescolar_primaria';
```

Resultado esperado:
- `cycle_day`: 5
- `start_time`: 08:00:00
- `end_time`: 09:30:00
- `blocked_units`: 20
- `reason`: "Cursos 4a y 4b (10 iPads por curso)"
- `is_weekday_block`: 0 (false)
1. **`app/Console/Commands/CreateAutomaticIpadReservations.php`**
   - Comando Artisan que crea las reservas automáticas

### ArchivoBloqueo automático de iPads para días 5', [
    'school_cycle' => 'Ciclo 2026',
    'result' => 'created'rateCycleDays()`: Llama al comando al generar/regenerar días

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

### Cambiar el Día del Ciclo Bloqueados

```php
// En CreateAutomaticIpadReservations.php, línea ~88
$result = $this->createEquipmentBlock(
    $ipadEquipment,
    $schoolCycle,
    '08:00', del Bloqueo113
'cycle_day' => 3,  // Cambiar de 5 a 3his->createEquipmentBlock(
    $ipadEquipment,
    $schoolCycle,
    '10:00',  // Cambiar hora de inicio
    '11:30',  // Cambiar hora de fin
    20
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

### Agregar Bloqueos para Otros Días

Para agregar bloqueos en día 3 también:

```php
// Después del bloqueo del día 5
$result3 = $this->createEquipmentBlock(
    $ipadEquipment,
    $schoolCycle,
    '08:00',
    '09:30',
    20
);
// Y cambiar el cycle_day a 3 en el método createEquipmentBlock
```

## Gestión de Reservas

Las reservas creadas automáticamente pueden ser:

1. **Visualizadas** en el módulo de Préstamos de Equipos
2. **Modificadas** por usuarios con permisos de administración de equipos
3. **EliminadaBloqueos

Los bloqueos creados automáticamente pueden ser:

1. **Visualizados** en el módulo de Bloqueos de Equipos
2. **Modificados** por usuarios con permisos de administración de equipos
3. **Eliminados** si es necesario (liberará los iPads para reserva)
4. El sistema **automáticamente** considera estos bloqueos al calcular disponibilidad
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
```el bloqueo se creó correctamente:

```sql
SELECT 
    eb.*,
    sc.name as cycle_name,
    e.type,
    e.section
FROM equipment_blocks eb
JOIN school_cycles sc ON eb.school_cycle_id = sc.id
JOIN equipment e ON eb.equipment_id = e.id
WHERE eb.cycle_day = 5
AND e.type = 'ipad'
AND e.section = 'preescolar_primaria';
```

### Probar la Disponibilidad

Para verificar que el sistema bloquea correctamente:

1. Ir a la página de solicitud de equipos
2. Seleccionar **Preescolar y Primaria** como sección
3. Seleccionar **iPad** como tipo de equipo
4. Elegir una fecha que corresponda a un **día 5** del ciclo escolar
5. Intentar reservar en el horario **08:00 - 09:30**
6. El sistema debe mostrar que hay **menos unidades disponibles** (máximo 24 de 44 si tienes 44 iPads totales) Error: "No se encontró un usuario administrador"

**Solución:** Asegurarse de que existe al menos un usuario con rol `admin`.
Este error ya no aplica** - El comando ya no necesita un usuario administrador porque crea bloqueos, no reservas.

### Los bloqueos no se aplican

**Verificar:**
1. Que el bloqueo se creó en la tabla `equipment_blocks`
2. Que el `cycle_day = 5` y el `school_cycle_id` sea el correcto
3. Que las fechas en `cycle_days` tengan `cycle_day = 5`
4. Revisar los logs de Laravel para errores

### El sistema aún permite reservar en días 5

**Verificar:**
1. Que el campo `blocked_units` sea 20 o más
2. Que los horarios `start_time` y `end_time` sean correctos
3. Que el `equipment_id` corresponda a los iPads de preescolar_primaria
4. Verificar que el código de disponibilidad esté considerando los bloqueos por `cycle_day`
## Fecha de Implementación

- **Fecha:** 22 de enero de 2026
- **Versión:** 1.0
- **Autor:** Sistema TVS

## Notas Adicionales

- El sistema es idempotente: puede ejecutarse múltiples veces sin crear duplicados
- Las reservas se crean en estado "pendiente" y deben ser entregadas manualmente
- La devolución está configurada como automática según el horario
