# Bloqueos de Horarios para Sala de Informática (IMAC)

## Descripción

Se han implementado bloqueos automáticos para los equipos IMAC de la sala de informática basados en el horario de clases del ciclo escolar.

## ⚠️ IMPORTANTE: Sistema de Días del Ciclo Escolar

Los días (1, 2, 3, 4, 5, 6) **NO son días de la semana**, son **días del ciclo escolar rotativo**.

- El ciclo escolar rota cada día hábil
- **Ejemplo**: Si hoy 14 de enero de 2026 es "Día 4", los bloqueos del Día 4 se aplicarán hoy
- Mañana será "Día 5" (si es día hábil), y se aplicarán los bloqueos del Día 5
- El sistema consulta automáticamente qué día del ciclo corresponde a cada fecha
- Los bloqueos se aplican dinámicamente según el día del ciclo, no según el día de la semana

## Horarios Bloqueados

Los siguientes horarios están bloqueados y **NO estarán disponibles** para reservas según el día del ciclo escolar.
Solo los horarios marcados en **AZUL** en la tabla están disponibles para reserva.

### 📅 Día 1 del Ciclo Escolar
- **07:30 - 08:20** | 7B - Clase programada
- **08:20 - 09:10** | 8B - Clase programada
- **09:10 - 10:00** | Horario bloqueado
- **11:20 - 12:10** | 8A - Clase programada

### 📅 Día 2 del Ciclo Escolar
- **07:30 - 08:20** | 9B - Clase programada
- **10:30 - 11:20** | Horario bloqueado
- **12:10 - 13:00** | 9A - Clase programada
- **13:50 - 14:40** | 7A - Clase programada

### 📅 Día 3 del Ciclo Escolar
- **08:20 - 09:10** | 8B - Clase programada
- **09:10 - 10:00** | 8A - Clase programada
- **10:30 - 11:20** | 6A - Clase programada
- **12:10 - 13:00** | 9A - Clase programada

### 📅 Día 4 del Ciclo Escolar
- **08:20 - 09:10** | 8A - Clase programada
- **09:10 - 10:00** | 7A - Clase programada
- **10:30 - 11:20** | Horario bloqueado
- **11:20 - 12:10** | Horario bloqueado
- **13:50 - 14:40** | 5A - Clase programada

### 📅 Día 5 del Ciclo Escolar
- **09:10 - 10:00** | 9B - Clase programada
- **12:10 - 13:00** | 5A - Clase programada
- **13:50 - 14:40** | Horario bloqueado

### 📅 Día 6 del Ciclo Escolar
- **07:30 - 08:20** | Horario bloqueado
- **08:20 - 09:10** | 7B - Clase programada
- **09:10 - 10:00** | Horario bloqueado

## Funcionamiento

- El sistema identifica automáticamente qué día del ciclo escolar es hoy
- **Ejemplo**: Si hoy es Día 4 del ciclo, se aplican los bloqueos del Día 4
- Durante los horarios bloqueados de ese día, **las 22 unidades de IMAC estarán reservadas** automáticamente
- Los usuarios NO podrán solicitar préstamos en esos horarios
- El sistema mostrará la disponibilidad actualizada en tiempo real
- Los bloqueos rotan automáticamente cada día según el ciclo escolar

## Consultar Día Actual del Ciclo

Para saber qué día del ciclo escolar es hoy:

```bash
php artisan tinker --execute="
\$cycle = \App\Models\SchoolCycle::where('active', true)->first();
\$today = \App\Models\CycleDay::getCycleDayForDate(date('Y-m-d'), \$cycle->id);
if (\$today) {
    echo 'Hoy ' . date('d/m/Y') . ' es el DÍA ' . \$today->cycle_day . ' del ciclo escolar' . PHP_EOL;
} else {
    echo 'Hoy no es un día hábil del ciclo escolar' . PHP_EOL;
}
"
```

## Gestión de Bloqueos

### Comandos Rápidos (Recomendados)

#### Ver el horario completo de bloqueos
```bash
php artisan imac:schedule show
```

#### Recrear todos los bloqueos
```bash
php artisan imac:schedule recreate
```

#### Eliminar todos los bloqueos
```bash
php artisan imac:schedule clear
```

### Comandos Avanzados

#### Ver todos los bloqueos en detalle
```bash
php artisan tinker --execute="App\Models\EquipmentBlock::where('equipment_id', 3)->get()->each(function(\$b) { echo 'Día ' . \$b->cycle_day . ': ' . \$b->start_time . '-' . \$b->end_time . ' - ' . \$b->reason . PHP_EOL; })"
```

### Recrear todos los bloqueos
```bash
php artisan db:seed --class=ImacScheduleBlocksSeeder
```

### Eliminar todos los bloqueos de IMAC
```bash
php artisan tinker --execute="App\Models\EquipmentBlock::where('equipment_id', 3)->delete(); echo 'Bloqueos eliminados';"
```

### Agregar un bloqueo manual
```bash
php artisan tinker --execute="
\$imac = App\Models\Equipment::where('type', 'imac')->first();
\$cycle = App\Models\SchoolCycle::where('active', true)->first();
App\Models\EquipmentBlock::create([
    'equipment_id' => \$imac->id,
    'school_cycle_id' => \$cycle->id,
    'cycle_day' => 1,
    'start_time' => '14:00',
    'end_time' => '15:00',
    'blocked_units' => 22,
    'reason' => 'Mantenimiento',
    'is_weekday_block' => false
]);
echo 'Bloqueo creado';
"
```

## Modificar Horarios

Si necesitas modificar los horarios bloqueados:

1. Edita el archivo: `database/seeders/ImacScheduleBlocksSeeder.php`
2. Modifica el array `$blockedSchedule` con los nuevos horarios
3. Ejecuta: `php artisan db:seed --class=ImacScheduleBlocksSeeder`

## Notas Importantes

- Los bloqueos están vinculados al **ciclo escolar activo**
- Si se cambia de ciclo escolar, será necesario recrear los bloqueos
- Los horarios bloqueados tienen prioridad sobre cualquier solicitud de préstamo
- El sistema verifica automáticamente la disponibilidad antes de permitir reservas
