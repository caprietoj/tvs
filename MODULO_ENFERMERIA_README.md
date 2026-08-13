# 🏥 Módulo de Enfermería - The Victoria School (TVS)

## 📋 Índice
- [Descripción General](#descripción-general)
- [Características Principales](#características-principales)
- [Funcionalidad de Envío de Reportes](#funcionalidad-de-envío-de-reportes)
- [Rutas y Endpoints](#rutas-y-endpoints)
- [Archivos Principales](#archivos-principales)
- [Uso del Sistema](#uso-del-sistema)

---

## Descripción General

El **Módulo de Enfermería** es un sistema integral de gestión de atenciones médicas para estudiantes y colaboradores de The Victoria School. Permite registrar, consultar y reportar todas las atenciones de enfermería con seguimiento detallado.

### Funcionalidades Base
- ✅ Registro de ingreso de estudiantes
- ✅ Registro de ingreso de colaboradores
- ✅ Historial de atenciones
- ✅ Reportes estadísticos
- ✅ Sistema de filtros avanzados
- ✅ Exportación a Excel profesional
- ✅ **Envío automático de reportes por correo electrónico** (Nuevo)

---

## Características Principales

### 1. Registro de Atenciones

#### Para Estudiantes
- **Datos personales**: Nombre, curso, documento, sección
- **Información médica**: EPS, tipo de sangre, sexo
- **Motivo de consulta**: Categorizado y detallado
- **Descripción del evento**: Con reconocimiento de voz (es-CO)
- **Acción de enfermería**: Tratamiento y procedimientos realizados
- **Seguimiento**: Observaciones y evolución
- **Derivación**: Salida al médico, casa, etc.

#### Para Colaboradores
- **Datos del empleado**: Nombre, área, documento
- **Información médica**: EPS, tipo de sangre
- **Motivo y descripción**: Similar a estudiantes
- **Encuesta de satisfacción**: Opcional

### 2. Sistema de Reportes

#### Reporte de Estudiantes
- **Agrupación por fecha**
- **Clasificación por sección**:
  - Preescolar
  - Primaria
  - Bachillerato
  - Actividades Deportivas
  - Casos Especiales
- **Conteo de salidas**: Médico/Casa
- **Observaciones y novedades**: Concatenadas por fecha

#### Reporte de Colaboradores
- **Agrupación por fecha**
- **Clasificación por área**:
  - Profesores
  - Administrativos
- **Observaciones y novedades**

### 3. Sistema de Filtros Avanzados

#### Filtros Disponibles
- **📅 Fecha Desde/Hasta**: Rango de fechas personalizado
- **🎓 Sección**: Preescolar, Primaria, Bachillerato, Deportivas, Especiales
- **📊 Cantidad Mínima**: Filtrar por número mínimo de atenciones
- **⚡ Filtros Rápidos**:
  - Hoy
  - Esta Semana
  - Este Mes

#### Características de Filtros
- ✅ Aplicación en tiempo real con DataTables
- ✅ Recálculo automático de totales
- ✅ Badge indicador de filtro activo
- ✅ Los filtros se aplican tanto a la visualización como a la exportación

### 4. Exportación a Excel Profesional

#### Características del Excel
- **📊 Formato Profesional**:
  - Headers en azul institucional (#314569)
  - Texto blanco en encabezados
  - Bordes y alineación profesional
  - Fila de totales automática
  
- **📋 Información Incluida**:
  - Título del reporte
  - Filtros aplicados (si existen)
  - Fecha de generación
  - Totales calculados
  
- **🎨 Colores Institucionales**:
  - Azul TVS: #314569
  - Blanco TVS: #FEFEFE
  
- **📏 Ajuste Automático**:
  - Anchos de columna optimizados
  - Altura de filas ajustada
  - Wrap text en observaciones

---

## 🆕 Funcionalidad de Envío de Reportes

### Descripción
Sistema automatizado para enviar reportes de enfermería por correo electrónico a destinatarios predefinidos, con adjunto Excel profesional y aplicación de filtros activos.

### Características

#### 1. Botón "Enviar Reporte"
- **Ubicación**: Header de la tabla de reportes (junto a "Exportar a Excel")
- **Color**: Azul primario con ícono de avión de papel
- **Función**: Abre modal de configuración de envío

#### 2. Modal de Selección de Destinatarios

**Destinatarios Predefinidos** (9 contactos):
1. **María del Pilar Robles** - Dirección General
   - `generaldirector@tvs.edu.co`

2. **Juliana Pérez López** - Dirección Administrativa
   - `administrativedirector@tvs.edu.co`

3. **Ana María Grisales** - Preescolar
   - `preschool@tvs.edu.co`

4. **Helena Ortiz** - Coordinación PEP
   - `coordpep@tvs.edu.co`

5. **Gina Lorena Hurtado**
   - `glhurtadog@tvs.edu.co`

6. **Andrea Carolina Flórez**
   - `aflorez@tvs.edu.co`

7. **María Constanza Bernal** - Dirección de Programa
   - `dp@tvs.edu.co`

8. **Johanna Gavidia** - Psicología
   - `psicologia2@tvs.edu.co`

9. **Sistemas**
   - `sistemas@tvs.edu.co`

**Campos del Modal**:
- ✅ Selector dropdown de destinatarios
- ✅ Nombre del destinatario (autocompletado)
- ✅ Correo electrónico (autocompletado)
- ✅ Resumen de filtros activos
- ✅ Botones: Cancelar / Enviar Reporte

#### 3. Generación de Excel para Envío

**Clase Export**: `EnfermeriaEstudiantesExport`

**Características del Excel**:
- 📊 Formato profesional con colores institucionales
- 📋 Información de filtros en las primeras filas
- 📈 Fila de totales automática
- 📅 Fecha y hora de generación
- 🎨 Estilos condicionales por columna
- 📏 Ajuste automático de columnas
- 🔄 Aplicación de filtros activos

**Estructura del Excel**:
```
┌─────────────────────────────────────────────────────────────┐
│ REPORTE DE ENFERMERÍA - ESTUDIANTES                         │
├─────────────────────────────────────────────────────────────┤
│ FILTROS APLICADOS:                                          │
│ Fecha Desde: DD/MM/YYYY                                     │
│ Fecha Hasta: DD/MM/YYYY                                     │
│ Sección: [Sección]                                          │
│ Generado: DD/MM/YYYY HH:MM                                  │
├─────────────────────────────────────────────────────────────┤
│ FECHA | PREESCOLAR | PRIMARIA | ... | OBSERVACIONES | ...  │
├─────────────────────────────────────────────────────────────┤
│ [Datos...]                                                  │
├─────────────────────────────────────────────────────────────┤
│ TOTALES: | [Sumas automáticas]                             │
└─────────────────────────────────────────────────────────────┘
```

#### 4. Correo Electrónico Profesional

**Mailable**: `EnfermeriaReporteSent`

**Vista**: `resources/views/emails/enfermeria-reporte.blade.php`

**Diseño del Correo**:
- 🎨 **Colores institucionales TVS**:
  - Header con gradiente azul (#314569 → #4a6491)
  - Texto blanco en header
  - Elementos interactivos en azul institucional
  
- 📧 **Estructura**:
  ```
  ┌─────────────────────────────────────┐
  │  📋 Reporte de Enfermería           │
  │  The Victoria School (TVS)          │
  ├─────────────────────────────────────┤
  │  Estimado/a [Nombre],               │
  │                                     │
  │  📊 Tipo: Ingresos de Estudiantes  │
  │  📅 Período: [Rango de fechas]     │
  │  📈 Total Registros: [Número]      │
  │  🕐 Generado: [Fecha/Hora]         │
  │                                     │
  │  📎 Archivo Excel Adjunto           │
  └─────────────────────────────────────┘
  ```

- 📱 **Responsive**: Adapta diseño en dispositivos móviles
- 🔒 **Seguro**: Correo automático sin respuesta directa

**Información Incluida**:
- ✅ Nombre personalizado del destinatario
- ✅ Tipo de reporte
- ✅ Período/rango de fechas
- ✅ Total de registros
- ✅ Fecha y hora de generación
- ✅ Archivo Excel adjunto

#### 5. Proceso de Envío

**Flujo Completo**:
1. Usuario hace clic en "Enviar Reporte"
2. Se abre modal con destinatarios
3. Usuario selecciona destinatario
4. Se muestra resumen de filtros activos
5. Usuario confirma envío
6. Sistema genera Excel con filtros aplicados
7. Sistema envía correo con adjunto
8. Modal se cierra automáticamente
9. Notificación de éxito/error
10. Archivo temporal se elimina

**Características Técnicas**:
- ⚡ Envío asíncrono con AJAX/Fetch API
- 🔄 Indicador de carga ("Enviando...")
- ✅ Validación de destinatario requerido
- 🗑️ Limpieza automática de archivos temporales
- 📂 Almacenamiento temporal: `storage/app/temp/`
- 🔒 Token CSRF para seguridad
- 📊 Logging de respuestas en consola

#### 6. Manejo de Errores

**Validaciones**:
- ✅ Destinatario obligatorio
- ✅ Formato de email válido
- ✅ Filtros opcionales
- ✅ Verificación de respuesta HTTP

**Mensajes de Error**:
- ❌ "Por favor seleccione un destinatario"
- ❌ "Error al enviar el reporte: [detalle]"
- ❌ "Error en la respuesta del servidor"

**Recuperación de Errores**:
- 🔄 Botón se restaura en caso de error
- 🔄 Modal permanece abierto para reintentar
- 📝 Error detallado en consola para debugging

---

## Rutas y Endpoints

### Rutas Web (Estudiantes)

```php
// Listar ingresos de estudiantes
Route::get('enfermeria/ingreso-estudiantes', 'EnfermeriaController@ingresoEstudiantes')
    ->name('enfermeria.ingreso_estudiantes.index');

// Crear nuevo ingreso
Route::get('enfermeria/ingreso-estudiantes/create', 'EnfermeriaController@createIngresoEstudiante')
    ->name('enfermeria.ingreso_estudiantes.create');

// Guardar ingreso
Route::post('enfermeria/ingreso-estudiantes', 'EnfermeriaController@storeIngresoEstudiante')
    ->name('enfermeria.ingreso_estudiantes.store');

// Ver reporte
Route::get('enfermeria/reporte-estudiantes', 'EnfermeriaController@reporteEstudiantes')
    ->name('enfermeria.reporte_estudiantes');

// 🆕 Enviar reporte por email
Route::post('enfermeria/reporte-estudiantes/enviar', 'EnfermeriaController@enviarReporteEstudiantes')
    ->name('enfermeria.reporte_estudiantes.enviar');
```

### Endpoint de Envío de Reporte

**URL**: `POST /enfermeria/reporte-estudiantes/enviar`

**Headers**:
```json
{
  "Content-Type": "application/json",
  "X-CSRF-TOKEN": "{{ csrf_token() }}"
}
```

**Request Body**:
```json
{
  "destinatario_email": "generaldirector@tvs.edu.co",
  "destinatario_nombre": "María del Pilar Robles",
  "filtros": {
    "fecha_desde": "2025-11-01",
    "fecha_hasta": "2025-11-07",
    "seccion": "preescolar",
    "cantidad": "5"
  }
}
```

**Response Success** (200):
```json
{
  "success": true,
  "message": "Reporte enviado exitosamente a generaldirector@tvs.edu.co"
}
```

**Response Error** (500):
```json
{
  "success": false,
  "message": "Error al enviar el reporte: [detalle del error]"
}
```

---

## Archivos Principales

### Controlador
**Archivo**: `app/Http/Controllers/EnfermeriaController.php`

**Métodos Principales**:
```php
// Reportes
public function reporteEstudiantes()
public function reporteColaboradores()

// 🆕 Envío de reportes
public function enviarReporteEstudiantes(Request $request)
```

### Modelos
**Archivo**: `app/Models/IngresoEstudiante.php`
**Archivo**: `app/Models/IngresoColaborador.php`

### Vistas

#### Reportes
- `resources/views/enfermeria/reporte-estudiantes.blade.php`
- `resources/views/enfermeria/reporte-colaboradores.blade.php`

#### Formularios
- `resources/views/enfermeria/ingreso-estudiantes/create.blade.php`
- `resources/views/enfermeria/ingreso-colaboradores/create.blade.php`

#### 🆕 Email
- `resources/views/emails/enfermeria-reporte.blade.php`

### 🆕 Clases de Exportación
**Archivo**: `app/Exports/EnfermeriaEstudiantesExport.php`

**Características**:
- Implementa múltiples interfaces de Maatwebsite Excel
- Formato profesional con estilos institucionales
- Información de filtros en primeras filas
- Totales automáticos
- Anchos de columna optimizados

### 🆕 Clases de Mail
**Archivo**: `app/Mail/EnfermeriaReporteSent.php`

**Características**:
- Mailable con adjunto automático
- Información personalizada por destinatario
- Diseño responsive
- Colores institucionales TVS

---

## Uso del Sistema

### Acceso al Módulo

**Ruta**: `http://127.0.0.1:8000/enfermeria/reporte-estudiantes`

**Permisos Requeridos**: `enfermeria.ingreso_estudiantes`

### Flujo de Trabajo Completo

#### 1. Visualizar Reporte
1. Acceder a la ruta de reportes
2. Ver tabla con resumen por fecha y área
3. Observar totales automáticos en el footer

#### 2. Aplicar Filtros (Opcional)
1. Expandir card de filtros (click en el ➕)
2. Configurar filtros deseados:
   - Fecha desde/hasta
   - Sección específica
   - Cantidad mínima
3. Click en "Aplicar Filtros"
4. Ver badge "Filtro Activo"
5. Los totales se recalculan automáticamente

#### 3. Exportar a Excel (Método Tradicional)
1. Click en botón "Exportar a Excel"
2. El archivo se descarga automáticamente
3. Nombre del archivo incluye:
   - Fecha actual
   - Filtros aplicados (si existen)
   - Sufijo "_FILTRADO" si hay filtros

#### 4. 🆕 Enviar Reporte por Email
1. **Abrir Modal**:
   - Click en botón "Enviar Reporte" (azul)
   
2. **Seleccionar Destinatario**:
   - Elegir del dropdown (9 opciones)
   - Campos de nombre y email se autocompletan
   
3. **Revisar Filtros**:
   - Verificar resumen de filtros en el card
   - Los filtros activos se aplicarán al reporte
   
4. **Confirmar Envío**:
   - Click en "Enviar Reporte" (modal)
   - Botón cambia a "Enviando..." con spinner
   
5. **Resultado**:
   - ✅ Éxito: Modal se cierra automáticamente
   - ✅ Notificación: "Reporte enviado exitosamente"
   - ✅ Formulario se limpia para próximo uso
   - ❌ Error: Modal permanece abierto
   - ❌ Notificación: Mensaje de error detallado
   - ❌ Botón se restaura para reintentar

### Consejos de Uso

#### Para Filtros
- 💡 Usar filtros rápidos para consultas comunes
- 💡 Limpiar filtros antes de exportar reporte completo
- 💡 El badge indica cuando hay filtros activos
- 💡 Los totales siempre reflejan solo las filas visibles

#### Para Excel
- 💡 El Excel exportado respeta los filtros activos
- 💡 Incluye información de filtros aplicados
- 💡 Los totales son fórmulas (se actualizan si editas)
- 💡 El formato profesional está optimizado para impresión

#### Para Envío de Reportes
- 💡 Aplicar filtros antes de enviar para reportes específicos
- 💡 Verificar el resumen de filtros en el modal
- 💡 El correo incluye toda la información del período
- 💡 El Excel adjunto es profesional y listo para compartir
- 💡 Revisar la consola del navegador si hay problemas

---

## Tecnologías Utilizadas

### Backend
- **Laravel 10+**: Framework PHP
- **MySQL 8.0+**: Base de datos
- **Maatwebsite Excel**: Exportación Excel
- **Laravel Mail**: Sistema de correos
- **PhpSpreadsheet**: Manipulación de Excel

### Frontend
- **AdminLTE 3**: Template admin
- **Bootstrap 4**: Framework CSS
- **jQuery**: Manipulación DOM
- **DataTables**: Tablas interactivas
- **SheetJS (xlsx)**: Exportación Excel en navegador
- **Toastr**: Notificaciones
- **Web Speech API**: Reconocimiento de voz
- **Fetch API**: Peticiones AJAX

### Librerías Adicionales
- **Carbon**: Manipulación de fechas
- **Font Awesome**: Iconos
- **Select2**: Selectores mejorados (opcional)

---

## Colores Institucionales

### Paleta TVS
- **Azul Primario**: `#314569`
- **Blanco**: `#FEFEFE`
- **Azul Claro**: `#4a6491`
- **Azul Oscuro**: `#2c3e61`

### Aplicación en el Sistema
- ✅ Headers de Excel
- ✅ Encabezado del correo electrónico
- ✅ Botones primarios
- ✅ Enlaces y elementos interactivos
- ✅ Badges y etiquetas
- ✅ Bordes y separadores

---

## Notas Técnicas

### Seguridad
- ✅ CSRF Token en todas las peticiones POST
- ✅ Validación de emails
- ✅ Sanitización de inputs
- ✅ Permisos de usuario verificados
- ✅ Archivos temporales eliminados después de envío

### Performance
- ✅ Archivos Excel generados en `storage/app/temp/`
- ✅ Limpieza automática de archivos temporales
- ✅ Queries optimizadas con GROUP BY
- ✅ Paginación en listados
- ✅ Carga asíncrona para envío de reportes

### Compatibilidad
- ✅ Chrome/Edge (reconocimiento de voz)
- ✅ Firefox (sin reconocimiento de voz)
- ✅ Safari (reconocimiento de voz)
- ✅ Responsive en móviles y tablets
- ✅ Excel compatible con Microsoft Office y LibreOffice

---

## Troubleshooting

### El botón "Enviar Reporte" no funciona
**Solución**: 
1. Abrir consola del navegador (F12)
2. Recargar página (Ctrl + F5)
3. Verificar que no haya errores JavaScript
4. Los event listeners deben cargarse después del DOM

### El modal no se cierra
**Solución**:
1. Verificar respuesta del servidor en consola
2. Debe ser exactamente `{ success: true, message: "..." }`
3. Verificar que no haya errores en el envío de correo
4. El modal se cierra automáticamente solo si `success === true`

### Error al enviar el correo
**Soluciones**:
1. Verificar configuración de mail en `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=tu-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=tu-email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```
2. Verificar que el directorio `storage/app/temp/` existe
3. Revisar permisos de escritura en `storage/`
4. Verificar logs en `storage/logs/laravel.log`

### El Excel adjunto no llega
**Soluciones**:
1. Verificar que el archivo se genera correctamente
2. Revisar límite de tamaño de adjuntos del servidor de correo
3. Verificar que el archivo temporal no se elimine antes de tiempo
4. Revisar logs del servidor de correo

### Filtros no se aplican al Excel enviado
**Solución**:
- Los filtros se envían en el request JSON
- Verificar en consola que `filtros` tiene valores
- El controlador aplica filtros a la query antes de exportar

---

## Changelog

### Versión 2.1.0 (7 de Noviembre de 2025)
- ✅ **[NUEVO]** Sistema de envío de reportes por correo electrónico
- ✅ **[NUEVO]** Modal de selección de destinatarios predefinidos
- ✅ **[NUEVO]** Clase EnfermeriaEstudiantesExport para Excel profesional
- ✅ **[NUEVO]** Clase EnfermeriaReporteSent Mailable
- ✅ **[NUEVO]** Vista de correo electrónico con colores institucionales
- ✅ **[MEJORA]** Aplicación de filtros activos al reporte enviado
- ✅ **[MEJORA]** Cierre automático del modal después de envío exitoso
- ✅ **[MEJORA]** Limpieza automática de archivos temporales
- ✅ **[FIX]** Event listeners envueltos en document.ready
- ✅ **[FIX]** Eliminación forzada de backdrop de Bootstrap
- ✅ **[FIX]** Validación estricta de respuesta del servidor

### Versión 2.0.0 (4 de Noviembre de 2025)
- ✅ Sistema de filtros avanzados
- ✅ Exportación a Excel con SheetJS
- ✅ Reconocimiento de voz en formularios
- ✅ Colores institucionales aplicados

---

## Soporte

Para soporte técnico o consultas:
- **Sistemas TVS**: `sistemas@tvs.edu.co`
- **Documentación**: Este archivo README
- **Logs**: `storage/logs/laravel.log`

---

## Licencia

© 2025 The Victoria School (TVS). Todos los derechos reservados.
