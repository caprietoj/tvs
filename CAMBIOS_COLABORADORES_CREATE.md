# Cambios Pendientes en create.blade.php de Ingreso Colaboradores

## ⚠️ IMPORTANTE
El archivo `resources/views/enfermeria/ingreso-colaboradores/create.blade.php` fue copiado desde el de estudiantes.
Necesita los siguientes cambios para funcionar correctamente:

## 📝 Cambios en el HTML del Formulario

### 1. Cambiar los nombres de campos de estudiante a colaborador

**Buscar y reemplazar**:
```
name="estudiante"          → name="nombre_completo"
name="codigo_estudiante"   → ELIMINAR (no existe para colaboradores)
name="documento_estudiante" → name="documento_colaborador"
name="apellidos_estudiante" → ELIMINAR (usar nombre_completo)
name="eps_estudiante"      → name="eps_colaborador"
name="sexo_estudiante"     → name="sexo_colaborador"
name="tipo_sangre_estudiante" → name="tipo_sangre_colaborador"
name="curso"               → name="email"
name="derivacion_estudiante" → name="derivacion_colaborador"
```

### 2. Modificar el campo "Nombre del Estudiante" (líneas ~110-120)

**CAMBIAR DE**:
```html
<label for="estudiante">
    <i class="fas fa-user-graduate mr-1"></i>Nombre del Estudiante
</label>
<input type="text" id="estudiante" name="estudiante" ...>
```

**A**:
```html
<label for="nombre_completo">
    <i class="fas fa-user-tie mr-1"></i>Nombre Completo del Colaborador
</label>
<input type="text" id="nombre_completo" name="nombre_completo" ...>
```

### 3. ELIMINAR el campo "Código" completo

Buscar y eliminar todo el div con `id="codigo_estudiante"` (líneas ~135-150)

### 4. ELIMINAR el campo "Apellidos del Estudiante" completo

Buscar y eliminar todo el div con apellidos (líneas ~155-170)

### 5. Cambiar campo "Curso" por "Email" (líneas ~180-195)

**CAMBIAR DE**:
```html
<label for="curso">
    <i class="fas fa-graduation-cap mr-1"></i>Curso
</label>
<input type="text" id="curso" name="curso" ...>
```

**A**:
```html
<label for="email">
    <i class="fas fa-envelope mr-1"></i>Email
</label>
<input type="email" id="email" name="email" placeholder="correo@ejemplo.com" ...>
```

### 6. Actualizar el campo "Documento" (línea ~175)

**CAMBIAR**:
```html
id="documento_estudiante" name="documento_estudiante"
```

**A**:
```html
id="documento_colaborador" name="documento_colaborador"
```

### 7. Actualizar campos EPS, Sexo y Tipo de Sangre

Cambiar los `id` y `name`:
```html
id="eps_estudiante" → id="eps_colaborador"
name="eps_estudiante" → name="eps_colaborador"

id="sexo_estudiante" → id="sexo_colaborador"
name="sexo_estudiante" → name="sexo_colaborador"
id="sexo_estudiante_display" → id="sexo_colaborador_display"

id="tipo_sangre_estudiante" → id="tipo_sangre_colaborador"
name="tipo_sangre_estudiante" → name="tipo_sangre_colaborador"
```

### 8. Actualizar campo "Derivación" (línea ~600)

```html
id="derivacion_estudiante" → id="derivacion_colaborador"
name="derivacion_estudiante" → name="derivacion_colaborador"
```

## 🎨 Cambios en el CSS

No requiere cambios. Los estilos `.estudiantes-dropdown` funcionarán también como `.empleados-dropdown`.

## 🔧 Cambios en el JavaScript

### 1. Cambiar IDs de elementos DOM (línea ~1172)

**CAMBIAR**:
```javascript
const $searchInput = $('#buscar_estudiante');
const $dropdown = $('#estudiantes-dropdown');
const $hiddenStudentId = $('#estudiante_id');
const $nombreEstudiante = $('#estudiante');
const $cursoEstudiante = $('#curso');
```

**A**:
```javascript
const $searchInput = $('#buscar_empleado');
const $dropdown = $('#empleados-dropdown');
const $hiddenEmpleadoId = $('#empleado_id');
const $nombreCompleto = $('#nombre_completo');
const $emailColaborador = $('#email');
```

### 2. Eliminar referencias a campos que no existen

**ELIMINAR estas líneas** (líneas ~1192-1194):
```javascript
const $codigoEstudiante = $('#codigo_estudiante');
const $apellidosEstudiante = $('#apellidos_estudiante');
const $cursoEstudiante = $('#curso');
```

### 3. Actualizar referencias de campos (líneas ~1196-1198)

**CAMBIAR**:
```javascript
const $documentoEstudiante = $('#documento_estudiante');
const $epsEstudiante = $('#eps_estudiante');
const $sexoEstudiante = $('#sexo_estudiante');
const $tipoSangreEstudiante = $('#tipo_sangre_estudiante');
```

**A**:
```javascript
const $documentoColaborador = $('#documento_colaborador');
const $epsColaborador = $('#eps_colaborador');
const $sexoColaborador = $('#sexo_colaborador');
const $tipoSangreColaborador = $('#tipo_sangre_colaborador');
```

### 4. Cambiar la URL del API (línea ~1233)

**CAMBIAR**:
```javascript
url: '/api/estudiantes/buscar',
```

**A**:
```javascript
url: '/api/empleados/buscar',
```

### 5. Actualizar función `seleccionarEstudiante` a `seleccionarEmpleado` (líneas ~1315-1340)

**CAMBIAR**:
```javascript
function seleccionarEstudiante(estudiante) {
    $hiddenStudentId.val(estudiante.id);
    $nombreEstudiante.val(estudiante.nombre || '').addClass('auto-filled');
    $codigoEstudiante.val(estudiante.codigo || '').addClass('auto-filled');
    $documentoEstudiante.val(estudiante.documento || '').addClass('auto-filled');
    $apellidosEstudiante.val(estudiante.apellidos_completos || '').addClass('auto-filled');
    $epsEstudiante.val(estudiante.eps || '').addClass('auto-filled');
    
    if (estudiante.sexo) {
        $sexoEstudiante.val(estudiante.sexo).addClass('auto-filled');
        $('#sexo_estudiante_display').val(estudiante.sexo).addClass('auto-filled');
    }
    
    $tipoSangreEstudiante.val(estudiante.tipo_sangre || '').addClass('auto-filled');
    
    if (estudiante.curso) {
        $cursoEstudiante.val(estudiante.curso).addClass('auto-filled');
    }
    
    $('#estudiante-seleccionado').show();
    $('#mensaje-seleccionar').hide();
    $searchInput.val(estudiante.nombre_completo);
    ocultarDropdown();
    console.log('Estudiante seleccionado:', estudiante);
}
```

**A**:
```javascript
function seleccionarEmpleado(empleado) {
    $hiddenEmpleadoId.val(empleado.id);
    $nombreCompleto.val(empleado.nombre_completo || '').addClass('auto-filled');
    $documentoColaborador.val(empleado.documento || '').addClass('auto-filled');
    $epsColaborador.val(empleado.eps || '').addClass('auto-filled');
    
    if (empleado.sexo) {
        $sexoColaborador.val(empleado.sexo).addClass('auto-filled');
        $('#sexo_colaborador_display').val(empleado.sexo).addClass('auto-filled');
    }
    
    $tipoSangreColaborador.val(empleado.tipo_sangre || '').addClass('auto-filled');
    
    if (empleado.email) {
        $emailColaborador.val(empleado.email).addClass('auto-filled');
    }
    
    $('#empleado-seleccionado').show();
    $('#mensaje-seleccionar').hide();
    $searchInput.val(empleado.nombre_completo);
    ocultarDropdown();
    console.log('Empleado seleccionado:', empleado);
}
```

### 6. Actualizar función `limpiarSeleccion` (líneas ~1350-1355)

**CAMBIAR**:
```javascript
function limpiarSeleccion() {
    $hiddenStudentId.val('');
    $('.auto-filled').removeClass('auto-filled').val('');
    $('#estudiante-seleccionado').hide();
    $('#mensaje-seleccionar').show();
}
```

**A**:
```javascript
function limpiarSeleccion() {
    $hiddenEmpleadoId.val('');
    $('.auto-filled').removeClass('auto-filled').val('');
    $('#empleado-seleccionado').hide();
    $('#mensaje-seleccionar').show();
}
```

### 7. Actualizar el click handler (línea ~1410)

**CAMBIAR**:
```javascript
$(document).on('click', '.estudiante-item', function() {
    const index = $(this).data('index');
    if (index !== undefined && estudiantes[index]) {
        seleccionarEstudiante(estudiantes[index]);
    }
});
```

**A**:
```javascript
$(document).on('click', '.empleado-item', function() {
    const index = $(this).data('index');
    if (index !== undefined && empleados[index]) {
        seleccionarEmpleado(empleados[index]);
    }
});
```

### 8. Actualizar las variables y referencias en `mostrarDropdown` (líneas ~1280-1310)

**CAMBIAR**:
```javascript
let estudiantes = [];
...
estudiantes = data;
...
<div class="estudiante-item" data-index="${index}" data-id="${estudiante.id}">
```

**A**:
```javascript
let empleados = [];
...
empleados = data;
...
<div class="empleado-item" data-index="${index}" data-id="${empleado.id}">
```

### 9. Actualizar `manejarNavegacion` (línea ~1365)

**CAMBIAR**:
```javascript
const estudianteIndex = $(items[selectedIndex]).data('index');
seleccionarEstudiante(estudiantes[estudianteIndex]);
```

**A**:
```javascript
const empleadoIndex = $(items[selectedIndex]).data('index');
seleccionarEmpleado(empleados[empleadoIndex]);
```

### 10. Actualizar selector en `manejarNavegacion` (línea ~1361)

**CAMBIAR**:
```javascript
const items = $('.estudiante-item');
```

**A**:
```javascript
const items = $('.empleado-item');
```

## ✅ Verificación Final

Después de hacer todos los cambios, verificar que:

1. ✅ El campo de búsqueda tenga `id="buscar_empleado"`
2. ✅ El dropdown tenga `id="empleados-dropdown"`
3. ✅ El hidden input tenga `id="empleado_id" name="empleado_id"`
4. ✅ Los campos visibles sean: Nombre Completo, Documento, Email, EPS, Sexo, Tipo de Sangre
5. ✅ NO existan campos: Código, Apellidos, Curso
6. ✅ La API apunte a `/api/empleados/buscar`
7. ✅ El formulario envíe a `route('enfermeria.ingreso_colaboradores.store')`

## 🧪 Prueba

```bash
# Navegar a la página
http://localhost/tvs-final/tvs/public/enfermeria/ingreso-colaboradores/create

# Escribir en el buscador y verificar:
# - Que aparezcan empleados del sistema
# - Que al seleccionar se llenen todos los campos
# - Que NO aparezcan campos de código, apellidos o curso
```
