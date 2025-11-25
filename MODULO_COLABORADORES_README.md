# Módulo de Ingreso de Colaboradores - Guía de Implementación

## ✅ Ya Completado

1. ✅ Migración `create_empleados_table` (ejecutada)
2. ✅ Migración `create_ingreso_colaboradores_table` (ejecutada)
3. ✅ Modelo `Empleado` con métodos y relaciones
4. ✅ Modelo `IngresoColaborador` con relaciones
5. ✅ Controlador `EmpleadosController` completo con CRUD y API
6. ✅ Rutas agregadas en `routes/web.php`
7. ✅ Menú actualizado en `config/adminlte.php`

## 📋 Pendiente por Crear

### 1. Vistas de Parametrización de Empleados

Necesitas crear las siguientes vistas en `resources/views/parametrizacion/empleados/`:

#### `index.blade.php`
- Copiar desde `resources/views/parametrizacion/estudiantes/index.blade.php`
- Cambiar "Estudiantes" por "Empleados"
- Remover columna "Curso"
- Agregar columna "Email"
- Cambiar referencias de `$estudiantes` a `$empleados`

#### `create.blade.php`
- Copiar desde `resources/views/parametrizacion/estudiantes/create.blade.php`
- Campos del formulario:
  - Nombre Completo (text)
  - Documento (text, unique)
  - Email (email, optional)
  - EPS (text, optional)
  - Sexo (select: M/F)
  - Tipo de Sangre (text, optional)
- Incluir sección para importación masiva (textarea)
- Formato esperado: `Nombre Completo [TAB] Documento [TAB] Email [TAB] EPS [TAB] Sexo [TAB] Tipo Sangre`

#### `edit.blade.php`
- Similar a create pero con datos pre-llenos
- Agregar checkbox "Activo"

### 2. Métodos en EnfermeriaController

Agregar los siguientes métodos al archivo `app/Http/Controllers/EnfermeriaController.php`:

```php
// Método para listar ingresos de colaboradores
public function ingresoColaboradores()
{
    $ingresos = IngresoColaborador::with(['empleado', 'usuario'])
        ->orderBy('fecha', 'desc')
        ->orderBy('hora', 'desc')
        ->paginate(20);
        
    return view('enfermeria.ingreso-colaboradores.index', compact('ingresos'));
}

// Método para mostrar formulario de creación
public function createIngresoColaborador()
{
    $motivos = MotivosEnfermeria::where('activo', true)
        ->orderBy('orden')
        ->get();
        
    return view('enfermeria.ingreso-colaboradores.create', compact('motivos'));
}

// Método para guardar ingreso de colaborador
public function storeIngresoColaborador(Request $request)
{
    $request->validate([
        'empleado_id' => 'nullable|exists:empleados,id',
        'fecha' => 'required|date',
        'hora' => 'required',
        'nombre_completo' => 'required|string|max:255',
        'documento_colaborador' => 'required|string|max:50',
        'email' => 'nullable|email|max:255',
        'eps_colaborador' => 'nullable|string|max:255',
        'sexo_colaborador' => 'nullable|in:M,F',
        'tipo_sangre_colaborador' => 'nullable|string|max:10',
        'motivo' => 'required|string|max:500',
        'descripcion_evento' => 'required|string',
        'accion_enfermeria' => 'required|string',
        'seguimiento' => 'nullable|string|max:1000',
        'derivacion_colaborador' => 'nullable|string|max:500',
        'encuesta' => 'nullable|string|max:500',
        'encuesta_observaciones' => 'nullable|string',
    ]);

    $data = $request->all();
    $data['user_id'] = auth()->id();

    IngresoColaborador::create($data);

    return redirect()
        ->route('enfermeria.ingreso_colaboradores.index')
        ->with('success', 'Ingreso de colaborador registrado exitosamente.');
}
```

### 3. Vista de Ingreso de Colaboradores

Crear `resources/views/enfermeria/ingreso-colaboradores/create.blade.php`:

**Basarse en**: `resources/views/enfermeria/ingreso-estudiantes/create.blade.php`

**Cambios importantes**:
- Cambiar "Buscar Estudiante" por "Buscar Empleado"
- Campo de búsqueda: `id="buscar_empleado"` (en lugar de buscar_estudiante)
- Dropdown: `id="empleados-dropdown"`
- Hidden ID: `id="empleado_id"`
- Campos del colaborador:
  - `nombre_completo` (text, auto-llenado)
  - `documento_colaborador` (text, auto-llenado, SIN código)
  - `email` (email, auto-llenado, EN LUGAR DE curso)
  - `eps_colaborador` (text, auto-llenado)
  - `sexo_colaborador` (select, auto-llenado)
  - `tipo_sangre_colaborador` (text, auto-llenado)
- **NO incluir**: código_estudiante, apellidos, curso
- JavaScript de autocompletado:
  ```javascript
  const $searchInput = $('#buscar_empleado');
  const $dropdown = $('#empleados-dropdown');
  const $hiddenEmpleadoId = $('#empleado_id');
  const $nombreCompleto = $('#nombre_completo');
  const $documentoColaborador = $('#documento_colaborador');
  const $emailColaborador = $('#email');
  const $epsColaborador = $('#eps_colaborador');
  const $sexoColaborador = $('#sexo_colaborador');
  const $tipoSangreColaborador = $('#tipo_sangre_colaborador');
  
  // API endpoint
  url: '/api/empleados/buscar'
  
  // Llenar campos
  function seleccionarEmpleado(empleado) {
      $hiddenEmpleadoId.val(empleado.id);
      $nombreCompleto.val(empleado.nombre_completo).addClass('auto-filled');
      $documentoColaborador.val(empleado.documento).addClass('auto-filled');
      $emailColaborador.val(empleado.email || '').addClass('auto-filled');
      $epsColaborador.val(empleado.eps || '').addClass('auto-filled');
      $sexoColaborador.val(empleado.sexo || '').addClass('auto-filled');
      $('#sexo_colaborador_display').val(empleado.sexo || '').addClass('auto-filled');
      $tipoSangreColaborador.val(empleado.tipo_sangre || '').addClass('auto-filled');
  }
  ```

### 4. Vista Index de Ingreso de Colaboradores

Crear `resources/views/enfermeria/ingreso-colaboradores/index.blade.php`:

**Basarse en**: `resources/views/enfermeria/ingreso-estudiantes/index.blade.php`

**Cambios**:
- Título: "Registros de Ingreso de Colaboradores"
- Columnas de la tabla:
  - Fecha y Hora
  - Nombre Completo (del colaborador)
  - Documento
  - Email
  - Motivo
  - Acciones (Ver/Editar)
- Cambiar `ingreso_estudiantes` por `ingreso_colaboradores` en rutas

## 🚀 Pasos para Completar

1. Crear carpeta: `resources/views/parametrizacion/empleados/`
2. Copiar vistas de estudiantes y adaptar para empleados
3. Crear carpeta: `resources/views/enfermeria/ingreso-colaboradores/`
4. Copiar vistas de ingreso de estudiantes y adaptar para colaboradores
5. Agregar métodos al `EnfermeriaController.php`
6. Probar la funcionalidad:
   ```bash
   php artisan route:list | grep empleados
   php artisan route:list | grep colaboradores
   ```

## 📝 Notas Importantes

- El autocompletado usa el mismo patrón que estudiantes
- La API `/api/empleados/buscar` ya está implementada y funcionando
- Los permisos usan `can:enfermeria.ingreso_estudiantes` (mismo que estudiantes)
- La importación masiva de empleados ya está implementada en el controlador
- Formato de importación: columnas separadas por TAB desde Excel

## 🧪 Testing

1. Probar API: `curl "http://localhost/tvs-final/tvs/public/api/empleados/buscar?q=test"`
2. Probar rutas en navegador
3. Verificar que el menú muestra las nuevas opciones
4. Probar autocompletado en el formulario
5. Probar importación masiva de empleados

## ✨ Mejoras Futuras

- Agregar filtros de búsqueda en index
- Exportar a Excel
- Estadísticas de ingresos de colaboradores
- Dashboard de colaboradores más atendidos
