# 📋 Changelog

Todos los cambios notables en el Sistema TVS serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [No Publicado]

### Pendiente
- Integración con sistema de calificaciones
- Dashboard administrativo mejorado
- App móvil para enfermería

---

## [2.0.0] - 2025-11-04

### 🎉 Agregado

#### Módulo de Enfermería
- **Reconocimiento de Voz**: Sistema de transcripción de voz a texto en formularios
  - Implementado en formulario de ingreso de estudiantes
  - Implementado en formulario de ingreso de colaboradores
  - Idioma: Español de Colombia (es-CO)
  - Transcripción en tiempo real con resultados intermedios
  - Indicadores visuales con animaciones
  - Manejo robusto de errores
  - Compatibilidad con Chrome, Edge y Safari

- **Sistema de Filtros Avanzados**: Filtros dinámicos en reportes
  - Filtro por rango de fechas (desde/hasta)
  - Filtro por sección (estudiantes) / tipo (colaboradores)
  - Filtro por cantidad mínima de ingresos
  - Filtros rápidos: Hoy, Esta Semana, Este Mes
  - Recalculación automática de totales
  - Badge visual indicador de filtros activos
  - Integración completa con exportación Excel

- **Exportación Excel Profesional**: Generación de archivos Excel con SheetJS
  - Formato profesional con colores institucionales
  - Encabezados con estilo (azul, texto blanco, centrado)
  - Columnas con anchos optimizados
  - Totales con formato especial
  - Exportación solo de datos filtrados/visibles
  - Información de filtros aplicados en el archivo
  - Nombres de archivo dinámicos con fecha y filtros aplicados
  - Extracción limpia de texto sin caracteres HTML

#### Módulo de Equipos
- **Restricción de Préstamos para el Mismo Día**
  - Validación en 3 capas (HTML, JavaScript, Backend)
  - Fecha mínima establecida en "mañana"
  - Mensaje de error personalizado
  - Previene préstamos de última hora

#### Sistema de Notificaciones
- **Redirección de Notificaciones**
  - Todas las notificaciones dirigidas a `administrativedirector@tvs.edu.co`
  - Exclusión completa de `generaldirector@tvs.edu.co`
  - Sin distinción por montos

### 🐛 Corregido

- **Excel con Caracteres HTML**: Solucionado problema de tags HTML en archivos exportados
  - Cambiado de `.innerHTML` a jQuery `.text()` para extracción limpia
  - Normalización de espacios múltiples y saltos de línea
  - Manejo especial de "Sin observaciones" y "Sin novedades"

- **Filtros No Aplicados en Excel**: Corregida integración de filtros con exportación
  - Uso de `tablaOriginal.rows({ search: 'applied' }).nodes()`
  - Procesamiento solo de filas visibles/filtradas
  - Información de filtros agregada al archivo Excel

### 🔄 Cambiado

- **Reporte de Estudiantes**: Completamente rediseñado
  - Nueva UI con card de filtros colapsable
  - Sistema de exportación mejorado
  - Mejores indicadores visuales

- **Reporte de Colaboradores**: Actualizado con paridad de características
  - Mismos filtros que reporte de estudiantes
  - Misma funcionalidad de exportación Excel
  - UI consistente

### 📚 Documentación

- Agregado README.md completo
- Agregado CONTRIBUTING.md con guías de contribución
- Agregado CHANGELOG.md
- Agregado documentación de API
- Agregado guías de instalación y configuración

---

## [1.5.0] - 2025-10-29

### 🎉 Agregado

- **Restricciones de Fecha por Día de la Semana** en préstamos de equipos
  - Viernes: Hasta domingo de la semana siguiente (+9 días)
  - Sábado/Domingo: Hasta domingo de la semana siguiente
  - Lunes-Jueves: Hasta el domingo de la misma semana
  - Validación en 3 capas (Blade, JavaScript, PHP)

### 🔄 Cambiado

- **Sistema de Notificaciones Mejorado**
  - Modo de prueba para desarrollo local
  - Interceptación de emails en testing
  - Configuración dinámica por archivo

### 🐛 Corregido

- Problema de caché en JavaScript de equipos
- Validaciones de fecha inconsistentes

---

## [1.4.0] - 2025-10-15

### 🎉 Agregado

- **Módulo de Portería Completo**
  - Sistema de registro de ingresos y salidas
  - Gestión de visitantes con modal
  - Importación masiva desde Excel
  - Plantillas de importación
  - Reportes de acceso

### 📚 Documentación

- Documentación completa del módulo de portería
- Guías de importación de datos
- Solución de problemas comunes

---

## [1.3.0] - 2025-09-20

### 🎉 Agregado

- **Sistema de Roles y Permisos** con Spatie
  - Roles: Administrador, Director, Coordinador, Docente, Enfermería
  - Permisos granulares por módulo
  - Middleware de autorización

### 🔄 Cambiado

- UI mejorada con colores institucionales
- Navegación optimizada en AdminLTE

---

## [1.2.0] - 2025-08-15

### 🎉 Agregado

- **Módulo de Compras y Servicios**
  - Solicitudes de compra
  - Sistema de cotizaciones
  - Control presupuestal
  - Aprobaciones multinivel
  - Formularios externos personalizables

### 🐛 Corregido

- Problema de límite de emails en Gmail
- Validación de presupuestos

---

## [1.1.0] - 2025-07-10

### 🎉 Agregado

- **Módulo de Colaboradores**
  - Gestión de empleados
  - Importación masiva desde Excel
  - Perfiles con foto
  - Historial laboral

### 🔄 Cambiado

- Optimización de consultas a base de datos
- Mejoras en rendimiento de búsquedas

---

## [1.0.0] - 2025-06-01

### 🎉 Lanzamiento Inicial

- **Módulo de Préstamos de Equipos**
  - Solicitud de préstamos
  - Historial de préstamos
  - Notificaciones por email

- **Módulo de Enfermería Básico**
  - Registro de ingresos de estudiantes
  - Registro de ingresos de colaboradores
  - Reportes básicos

- **Sistema de Autenticación**
  - Login con email
  - Recuperación de contraseña
  - OAuth con Google

- **Panel Administrativo**
  - Dashboard básico
  - Gestión de usuarios
  - Configuración del sistema

---

## Tipos de Cambios

- **🎉 Agregado**: Para nuevas funcionalidades
- **🔄 Cambiado**: Para cambios en funcionalidades existentes
- **🗑️ Deprecado**: Para funcionalidades que serán removidas
- **🚫 Removido**: Para funcionalidades removidas
- **🐛 Corregido**: Para correcciones de bugs
- **🔒 Seguridad**: Para correcciones de vulnerabilidades
- **📚 Documentación**: Para cambios en documentación
- **⚡ Rendimiento**: Para mejoras de rendimiento

---

## [Enlaces]

[No Publicado]: https://github.com/caprietoj/tvs/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/caprietoj/tvs/compare/v1.5.0...v2.0.0
[1.5.0]: https://github.com/caprietoj/tvs/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/caprietoj/tvs/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/caprietoj/tvs/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/caprietoj/tvs/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/caprietoj/tvs/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/caprietoj/tvs/releases/tag/v1.0.0
