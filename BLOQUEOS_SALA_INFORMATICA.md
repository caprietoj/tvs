# Bloqueos de Horarios para Salas de Informática (IMAC)

## Descripción

Se han implementado bloqueos automáticos para los equipos IMAC de ambas salas de informática basados en el horario de clases del ciclo escolar.

- **Sala de Informática - Segundo Piso** (`sala_informatica`): Profesores Jose Luis, Adriana Fernández, Leidy Latorre
- **Sala de Informática - Primer Piso** (`sala_informatica_primer_piso`): Profesor Marcell

## IMPORTANTE: Sistema de Días del Ciclo Escolar

Los días (1, 2, 3, 4, 5, 6) **NO son días de la semana**, son **días del ciclo escolar rotativo**.

- El ciclo escolar rota cada día hábil
- El sistema consulta automáticamente qué día del ciclo corresponde a cada fecha
- Los bloqueos se aplican dinámicamente según el día del ciclo

---

## Sala de Informática - Segundo Piso

### Día 1 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Leidy Latorre |
| 08:20 - 09:10 | Adriana Fernandez |
| 09:10 - 10:00 | Horario bloqueado |
| 11:20 - 12:10 | Horario bloqueado |

### Día 2 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Jose Luis |
| 08:20 - 09:10 | Horario bloqueado |
| 10:30 - 11:20 | Jose Luis |
| 12:10 - 13:00 | Horario bloqueado |
| 13:50 - 14:40 | Horario bloqueado |

### Día 3 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Jose Luis |
| 08:20 - 09:10 | Jose Luis |
| 09:10 - 10:00 | Horario bloqueado |
| 10:30 - 11:20 | Jose Luis |
| 11:20 - 12:10 | Horario bloqueado |
| 12:10 - 13:00 | Horario bloqueado |
| 13:50 - 14:40 | Horario bloqueado |

### Día 4 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Jose Luis |
| 08:20 - 09:10 | Horario bloqueado |
| 10:30 - 11:20 | Jose Luis |
| 11:20 - 12:10 | Horario bloqueado |
| 13:50 - 14:40 | Horario bloqueado |

### Día 5 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Adriana Fernandez |
| 08:20 - 09:10 | Jose Luis |
| 09:10 - 10:00 | Horario bloqueado |
| 10:30 - 11:20 | Jose Luis |
| 11:20 - 12:10 | Jose Luis |
| 12:10 - 13:00 | Horario bloqueado |

### Día 6 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Jose Luis |
| 08:20 - 09:10 | Jose Luis |
| 09:10 - 10:00 | Horario bloqueado |
| 11:20 - 12:10 | Horario bloqueado |
| 12:10 - 13:00 | Horario bloqueado |

---

## Sala de Informática - Primer Piso

### Día 1 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Marcell |
| 08:20 - 09:10 | Marcell |
| 11:20 - 12:10 | Marcell |
| 12:10 - 13:00 | Marcell |
| 13:50 - 14:40 | Marcell |

### Día 2 del Ciclo
| Horario | Razón |
|---------|-------|
| 08:20 - 09:10 | Marcell |
| 09:10 - 10:00 | Marcell |
| 10:30 - 11:20 | Marcell |
| 12:10 - 13:00 | Marcell |
| 13:50 - 14:40 | Marcell |

### Día 3 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Marcell |
| 08:20 - 09:10 | Marcell |
| 09:10 - 10:00 | Marcell |
| 10:30 - 11:20 | Marcell |
| 11:20 - 12:10 | Marcell |
| 12:10 - 13:00 | Marcell |
| 13:50 - 14:40 | Marcell |

### Día 4 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Marcell |
| 09:10 - 10:00 | Marcell |
| 10:30 - 11:20 | Marcell |
| 11:20 - 12:10 | Marcell |
| 12:10 - 13:00 | Marcell |
| 13:50 - 14:40 | Marcell |

### Día 5 del Ciclo
| Horario | Razón |
|---------|-------|
| 07:30 - 08:20 | Marcell |
| 10:30 - 11:20 | Marcell |
| 12:10 - 13:00 | Marcell |

### Día 6 del Ciclo
| Horario | Razón |
|---------|-------|
| 08:20 - 09:10 | Marcell |
| 10:30 - 11:20 | Marcell |
| 12:10 - 13:00 | Marcell |
| 13:50 - 14:40 | Marcell |

---

## Funcionamiento

- El sistema identifica automáticamente qué día del ciclo escolar es hoy
- Durante los horarios bloqueados, las 22 unidades de IMAC de la sala correspondiente estarán reservadas
- Los usuarios NO podrán solicitar préstamos en esos horarios
- Cada sala (primer y segundo piso) gestiona sus bloques independientemente

## Gestión de Bloqueos

### Comandos Rápidos

#### Ver horario completo (ambas salas)
```bash
php artisan imac:schedule show
```

#### Ver horario de una sala específica
```bash
php artisan imac:schedule show --sala=sala_informatica            # Segundo Piso
php artisan imac:schedule show --sala=sala_informatica_primer_piso  # Primer Piso
```

#### Recrear todos los bloqueos
```bash
php artisan imac:schedule recreate
```

#### Eliminar todos los bloqueos
```bash
php artisan imac:schedule clear
```

### Seeder Directo
```bash
php artisan db:seed --class=ImacScheduleBlocksSeeder
```

## Modificar Horarios

1. Edita: `database/seeders/ImacScheduleBlocksSeeder.php`
2. Modifica el array de la sala correspondiente
3. Ejecuta: `php artisan db:seed --class=ImacScheduleBlocksSeeder`

## Notas Importantes

- Los bloqueos están vinculados al ciclo escolar activo
- Si se cambia de ciclo escolar, será necesario recrear los bloqueos
- Los horarios bloqueados tienen prioridad sobre cualquier solicitud de préstamo
- Primer Piso y Segundo Piso son independientes
