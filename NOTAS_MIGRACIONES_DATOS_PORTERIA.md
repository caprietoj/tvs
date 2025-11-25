# 📝 Notas sobre Migraciones de Datos - Módulo Portería

## ❌ Migraciones Eliminadas (No Necesarias)

### Archivos Removidos:
- `2025_10_06_084500_fix_personas_data.php`
- `2025_10_06_102500_fix_personas_apellido_cargo_data.php`

---

## ❓ ¿Por qué se eliminaron?

Estas migraciones intentaban **corregir datos en la base de datos** que fueron importados incorrectamente:

### Problema Original:
- **Campo `apellido`**: contenía cargos como "Administracion", "Docentes", etc.
- **Campo `grado`**: estaba vacío o NULL
- **Campo `tipo_persona`**: indicaba "estudiante" cuando debería ser "empleado"

### Primera Solución Propuesta (RECHAZADA):
Usar migraciones para corregir los datos en la base de datos.

**Razón del rechazo**: El usuario prefirió **NO modificar los datos originales** en la base de datos.

---

## ✅ Solución Implementada (Sin Migraciones)

### Enfoque: Model Accessors (Getters Virtuales)

Se agregaron **accessors** en el modelo `Persona` que corrigen los datos **en tiempo real** al leer:

#### **`app/Models/Persona.php`**:

```php
/**
 * Accessor para obtener el grado correcto.
 * Si grado está vacío pero apellido contiene un cargo, usa apellido como grado.
 */
public function getGradoAttribute($value)
{
    // Si ya tiene grado, devolverlo
    if (!empty($value)) {
        return $value;
    }
    
    // Si no tiene grado pero el apellido parece un cargo, usar apellido como grado
    $apellido = $this->attributes['apellido'] ?? '';
    $cargosComunes = [
        'Administracion', 'Docente', 'Coordinacion', 'Asistente',
        'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento',
        'Sistemas', 'Contabilidad', 'Rectoría', 'Rectoria',
        'Secretaria', 'Pastoral', 'EMC', 'PRACTICANTE'
    ];
    
    foreach ($cargosComunes as $cargo) {
        if (stripos($apellido, $cargo) !== false) {
            return $apellido;
        }
    }
    
    return $value;
}

/**
 * Accessor para determinar automáticamente el tipo de persona.
 */
public function getTipoPersonaAttribute($value)
{
    $grado = $this->grado; // Usa el accessor
    
    // Si el grado contiene un cargo, es empleado
    $cargosComunes = [
        'Administracion', 'Docente', 'Coordinacion', 'Asistente',
        'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento',
        'Sistemas', 'Contabilidad', 'Rectoría', 'Rectoria',
        'Secretaria', 'Pastoral', 'EMC', 'PRACTICANTE'
    ];
    
    foreach ($cargosComunes as $cargo) {
        if (stripos($grado, $cargo) !== false) {
            return 'empleado';
        }
    }
    
    // Si el grado contiene números (como 1A, 2B, 11A), es estudiante
    if (preg_match('/\d/', $grado)) {
        return 'estudiante';
    }
    
    // Si no se puede determinar, devolver el valor original
    return $value;
}

/**
 * Accessor para obtener el nombre completo sin mostrar cargos como apellido.
 */
public function getNombreCompletoAttribute()
{
    $apellido = $this->attributes['apellido'] ?? '';
    
    // Lista de cargos que no deberían aparecer como apellido
    $cargosComunes = [
        'Administracion', 'Docente', 'Coordinacion', 'Asistente',
        'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento',
        'Sistemas', 'Contabilidad', 'Rectoría', 'Rectoria',
        'Secretaria', 'Pastoral', 'EMC', 'PRACTICANTE'
    ];
    
    // Si el apellido es un cargo, solo devolver el nombre
    foreach ($cargosComunes as $cargo) {
        if (stripos($apellido, $cargo) !== false) {
            return $this->attributes['nombre'];
        }
    }
    
    // Si no es un cargo, devolver nombre + apellido
    return trim($this->attributes['nombre'] . ' ' . $apellido);
}
```

---

## 💡 Ventajas de Esta Solución

### ✅ Pros:
1. **No modifica datos originales** - La base de datos mantiene los datos importados
2. **Corrección automática en tiempo real** - Los accessors corrigen al leer
3. **Sin migraciones de datos** - No hay riesgo de corrupción de datos
4. **Fácil de mantener** - Puedes ajustar los accessors sin tocar la BD
5. **Reversible** - Si quitas los accessors, vuelves a los datos originales

### ⚠️ Contras:
1. **Los datos originales siguen incorrectos** - Si exportas la BD, los datos estarán mal
2. **Performance mínima** - Los accessors se ejecutan cada vez que lees un registro
3. **No afecta búsquedas SQL directas** - Solo funciona a través de Eloquent

---

## 🎯 Resultado Final

### Antes (datos incorrectos en BD):
```
nombre: "Juan"
apellido: "Administracion"
grado: NULL
tipo_persona: "estudiante"
```

### Después (con accessors):
```php
$persona->nombre;            // "Juan"
$persona->apellido;          // "Administracion" (dato original)
$persona->grado;             // "Administracion" (corregido por accessor)
$persona->tipo_persona;      // "empleado" (corregido por accessor)
$persona->nombre_completo;   // "Juan" (sin el cargo como apellido)
```

---

## 📋 Registros de Portería

El controlador `PorteriaController` también fue ajustado:

```php
public function buscarPersona(Request $request)
{
    // ... código de búsqueda ...
    
    if ($persona) {
        return response()->json([
            'existe' => true,
            'nombre' => $persona->nombre,
            'apellido' => '', // Siempre vacío para evitar mostrar cargos
            'tipo_persona' => $persona->tipo_persona, // Usa el accessor
            'grado' => $persona->grado, // Usa el accessor
        ]);
    }
}
```

---

## 🔄 Si Algún Día Quieres Corregir los Datos en la BD

Si en el futuro decides corregir los datos permanentemente:

```bash
php artisan tinker
```

```php
// Corregir todos los registros con apellido = cargo
$personas = App\Models\Persona::whereIn('apellido', [
    'Administracion', 'Docente', 'Coordinacion', 'Asistente',
    'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento',
    'Sistemas', 'Contabilidad', 'Rectoría', 'Rectoria',
    'Secretaria', 'Pastoral', 'EMC', 'PRACTICANTE'
])->get();

foreach ($personas as $persona) {
    $persona->update([
        'grado' => $persona->apellido,
        'apellido' => '',
        'tipo_persona' => 'empleado'
    ]);
}
```

---

## 📝 Estado Actual del Sistema

- ✅ **Accessors implementados** en `app/Models/Persona.php`
- ✅ **Controlador ajustado** en `app/Http/Controllers/PorteriaController.php`
- ✅ **Migraciones de datos eliminadas** (no necesarias)
- ✅ **Vista funcionando correctamente** con datos corregidos en tiempo real
- ✅ **Sin modificaciones en la base de datos** (datos originales intactos)

---

## 🎉 Conclusión

Las migraciones mostraban **"N/A"** porque eran de corrección de datos y el sistema decidió usar **Model Accessors** en su lugar. Esta es una solución más elegante y segura que no modifica los datos originales.

**Fecha de decisión**: 6 de octubre de 2025  
**Módulo afectado**: Portería (Registro de entrada/salida)
