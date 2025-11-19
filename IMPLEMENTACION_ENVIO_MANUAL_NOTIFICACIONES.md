# Implementación de Envío Manual de Reportes - Atención en Enfermería

## Resumen
Se ha implementado una funcionalidad que permite enviar manualmente un reporte de atención en enfermería a un destinatario específico cuando un estudiante ha sido atendido con derivación "Salida a Casa" o "Salida al medico".

## Funcionalidad Implementada

### 1. Botón de Envío en la Tabla de Atención de Estudiantes

**Ubicación**: `resources/views/enfermeria/ingreso-estudiantes/index.blade.php`

- Se agregó un botón con ícono de sobre (📧) en la columna de "Acciones"
- El botón solo aparece para registros con derivación "Salida a Casa" o "Salida al medico"
- Al hacer clic, se abre un modal para seleccionar el destinatario
- El tooltip muestra "Enviar reporte de atención"

### 2. Modal de Selección de Destinatario

**Características**:
- Permite seleccionar un destinatario de una lista predefinida:
  - María del Pilar Robles (Dirección General) - generaldirector@tvs.edu.co
  - Juliana Pérez López (Dirección Administrativa) - administrativedirector@tvs.edu.co
  - Ana María Grisales (Preescolar) - preschool@tvs.edu.co
  - Helena Ortiz (Coordinación PEP) - coordpep@tvs.edu.co
  - Gina Lorena Hurtado - glhurtadog@tvs.edu.co
  - Andrea Carolina Flórez - aflorez@tvs.edu.co
  - María Constanza Bernal (Dirección de Programa) - dp@tvs.edu.co
  - Johanna Gavidia (Psicología) - psicologia2@tvs.edu.co
  - Asistente Bachillerato - asistentebachillerato@tvs.edu.co
  - Asistente PYP - asistentepyp@tvs.edu.co
  - Transporte - transporte@tvs.edu.co
  - Sistemas - sistemas@tvs.edu.co

- Funciona igual que el modal de envío de reportes en `/enfermeria/reporte-estudiantes`
- Al seleccionar un destinatario del dropdown, automáticamente se llenan los campos de nombre y correo
- Muestra información del estudiante (nombre y tipo de derivación)
- Valida que se haya seleccionado un destinatario antes de enviar

### 3. Método del Controlador

**Archivo**: `app/Http/Controllers/EnfermeriaController.php`
**Método**: `enviarNotificacionManual(Request $request, $id)`

**Funcionalidades**:
- Valida que el registro exista
- Verifica que la derivación sea "Salida a Casa" o "Salida al medico"
- Recibe el email y nombre del destinatario seleccionado
- Envía el reporte de atención usando `AtencionEnfermeriaNotification`
- Registra logs detallados con prefijo "DEBUG - Manual:"
- Retorna respuesta JSON con éxito o error

### 4. Ruta Agregada

**Archivo**: `routes/web.php`

```php
Route::post('ingreso-estudiantes/{id}/enviar-notificacion', 
    [App\Http\Controllers\EnfermeriaController::class, 'enviarNotificacionManual'])
    ->name('enfermeria.ingreso_estudiantes.enviar_notificacion')
    ->middleware('can:enfermeria.ingreso_estudiantes');
```

## Plantilla de Email

### Reporte de Atención en Enfermería
- **Mailable**: `App\Mail\AtencionEnfermeriaNotification`
- **Vista**: `resources/views/emails/atencion-enfermeria.blade.php`
- **Asunto**: "Reporte de Atención en Enfermería - [Nombre del Estudiante]"
- **Contenido**: 
  - 👤 Información del Estudiante (nombre, código, documento, curso)
  - 🩺 Detalles de la Atención (fecha, hora, motivo, descripción)
  - 💊 Información de Enfermería (temperatura, presión, medicamentos, procedimientos)
  - 📝 Seguimiento y Derivación
  - ℹ️ Información del Registro (quién registró, cuándo)

## Flujo de Uso

1. El usuario accede a **Enfermería > Atención de Estudiantes**
2. En la tabla, identifica un registro con derivación "Salida a Casa" o "Salida al medico"
3. Hace clic en el botón de sobre (📧) en la columna de acciones
4. Se abre el modal "Enviar Reporte de Atención en Enfermería"
5. Selecciona el destinatario del dropdown
6. Los campos de nombre y correo se llenan automáticamente
7. Hace clic en "Enviar Reporte"
8. El sistema muestra un indicador de carga
9. Al completarse:
   - **Éxito**: Muestra mensaje de confirmación con el nombre del destinatario y cierra el modal
   - **Error**: Muestra mensaje de error descriptivo

## Logs Generados

El sistema genera logs con el prefijo `DEBUG - Manual:` para facilitar el seguimiento:

```
DEBUG - Manual: Iniciando envío manual de reporte de atención para ingreso ID: X
DEBUG - Manual: Destinatario: [Nombre] ([Email])
DEBUG - Manual: Enviando reporte de atención a: [Email]
DEBUG - Manual: ✅ Reporte de atención enviado exitosamente
```

## Validaciones

1. **Permiso**: El usuario debe tener el permiso `enfermeria.ingreso_estudiantes`
2. **Derivación válida**: El registro debe tener derivación "Salida a Casa" o "Salida al medico"
3. **Destinatario seleccionado**: Debe seleccionar un destinatario del dropdown
4. **Email válido**: El email del destinatario debe ser válido
5. **Registro existente**: El ID del ingreso debe existir en la base de datos

## Archivos Creados/Modificados

### Creados
1. `app/Mail/AtencionEnfermeriaNotification.php` - Mailable para reporte de atención
2. `resources/views/emails/atencion-enfermeria.blade.php` - Plantilla HTML del reporte

### Modificados
1. `resources/views/enfermeria/ingreso-estudiantes/index.blade.php`
   - Botón de envío en columna de acciones
   - Modal de selección de destinatario (igual al de reporte-estudiantes)
   - Event listener para llenar automáticamente nombre y email al seleccionar
   - Función JavaScript para manejo del modal y envío AJAX
   - Textos actualizados: "Enviar Reporte", "Enviando reporte...", etc.

2. `app/Http/Controllers/EnfermeriaController.php`
   - Import del nuevo Mailable `AtencionEnfermeriaNotification`
   - Método `enviarNotificacionManual` actualizado para enviar reporte de atención
   - Logs actualizados para reflejar "reporte de atención" en lugar de "notificación"

3. `routes/web.php`
   - Nueva ruta POST para envío manual

## Ventajas

- **Claridad**: El reporte muestra exactamente qué pasó en enfermería (motivo, atención, medicamentos, etc.)
- **Completo**: Incluye todos los datos relevantes de la atención
- **Profesional**: Plantilla bien diseñada y estructurada
- **Flexibilidad**: Permite enviar a cualquier persona de la lista
- **Consistencia**: Modal idéntico al sistema de reportes existente
- **Auditoría**: Logs detallados para seguimiento
- **Independiente**: No interfiere con otros sistemas de notificación

