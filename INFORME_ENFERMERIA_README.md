# 📊 Informe de Enfermería - Guía de Uso

## 🎯 Descripción
El informe de enfermería es una herramienta completa de análisis y visualización de datos que permite obtener estadísticas detalladas sobre los ingresos de estudiantes y colaboradores al servicio de enfermería.

## 📍 Ubicación
El informe se encuentra en el menú principal:
```
📊 Informes y Reportes > 🏥 Informe Enfermería
```

## 🔍 Características

### Filtros Disponibles
1. **Fecha Inicio**: Selecciona la fecha inicial del período a analizar (por defecto: primer día del mes actual)
2. **Fecha Fin**: Selecciona la fecha final del período (por defecto: día actual)
3. **Tipo**: Filtra por tipo de usuario
   - **Ambos**: Muestra estadísticas de estudiantes y colaboradores
   - **Estudiantes**: Solo muestra datos de estudiantes
   - **Colaboradores**: Solo muestra datos de colaboradores

### Estadísticas para Estudiantes 👨‍🎓

#### Tarjetas Resumen
- **Total Ingresos**: Cantidad total de ingresos de estudiantes en el período
- **Derivados al Médico**: Casos que requirieron atención médica especializada
- **Retorno a Salón**: Estudiantes que pudieron volver a clase
- **Salida a Casa**: Casos en los que el estudiante debió retirarse
- **En Seguimiento**: Casos que requieren monitoreo continuo
- **Derivados a Psicología**: Casos derivados al departamento de psicología

#### Gráficos de Análisis
1. **Ingresos por Motivo** (Gráfico de pastel)
   - Muestra la distribución de los motivos de consulta más frecuentes
   - Ejemplos: Dolor de cabeza, Dolor abdominal, Fiebre, Mareo, etc.

2. **Ingresos por Curso** (Gráfico de barras)
   - Identifica los cursos con mayor frecuencia de visitas a enfermería
   - Permite detectar patrones por nivel educativo

3. **Origen del Estudiante** (Gráfico de dona)
   - Muestra de dónde venían los estudiantes al momento del ingreso:
     * 📚 Clase Magistral
     * ⚽ Educación Física
     * 🎨 Extracurricular
     * ☕ Descanso

4. **Derivaciones** (Gráfico de pastel)
   - Distribución de las derivaciones realizadas
   - Permite evaluar la complejidad de los casos atendidos

5. **Tendencia Diaria** (Gráfico de líneas)
   - Muestra la evolución diaria de ingresos en el período seleccionado
   - Útil para identificar días con mayor demanda

### Estadísticas para Colaboradores 👨‍💼

#### Tarjetas Resumen
- **Total Ingresos**: Cantidad total de ingresos de colaboradores
- **Derivados al Médico**: Casos derivados a atención médica
- **Retorno al Trabajo**: Colaboradores que pudieron continuar trabajando
- **Incapacidad**: Casos que resultaron en incapacidad laboral
- **En Seguimiento**: Casos que requieren monitoreo

#### Gráficos de Análisis
1. **Ingresos por Motivo** (Gráfico de pastel)
   - Motivos de consulta más frecuentes en colaboradores
   - Ejemplos: Hipertensión, Dolor lumbar, Estrés laboral, etc.

2. **Ingresos por Área** (Gráfico de barras)
   - Áreas de trabajo con mayor incidencia de visitas a enfermería
   - Ayuda a identificar áreas de riesgo o que requieren intervención

3. **Derivaciones** (Gráfico de pastel)
   - Resultados de las atenciones realizadas

4. **Tendencia Diaria** (Gráfico de líneas)
   - Evolución diaria de ingresos de colaboradores

## 📊 Datos de Ejemplo

Se han creado **80 registros de ejemplo**:
- **50 registros de estudiantes** distribuidos en los últimos 30 días
- **30 registros de colaboradores** distribuidos en los últimos 30 días

Estos datos incluyen:
- Diversos motivos de consulta realistas
- Distribución por cursos (estudiantes) y áreas (colaboradores)
- Diferentes tipos de derivaciones
- Variedad en el origen de los estudiantes (viene_de)
- Tendencia temporal realista

## 🎨 Características Visuales

- **Colores distintivos**: Cada tipo de dato tiene un código de colores específico
- **Iconos intuitivos**: Cada métrica tiene un icono representativo
- **Gráficos interactivos**: Utiliza Chart.js para visualizaciones dinámicas
- **Responsive**: Se adapta a diferentes tamaños de pantalla
- **Tabs organizadas**: Información separada por tipo de usuario

## 🔐 Permisos

Para acceder al informe se requiere el permiso:
```
enfermeria.ingreso_estudiantes
```

## 💡 Casos de Uso

1. **Análisis Mensual**: Configura las fechas para ver estadísticas del mes
2. **Identificación de Patrones**: Detecta cursos o áreas con mayor incidencia
3. **Planificación de Recursos**: Identifica días con mayor demanda
4. **Reportes de Gestión**: Genera información para reportes administrativos
5. **Seguimiento de Indicadores**: Monitorea derivaciones y seguimientos

## 🚀 Próximas Mejoras Sugeridas

- [ ] Exportación a PDF/Excel
- [ ] Comparación entre períodos
- [ ] Alertas automáticas por alta incidencia
- [ ] Filtros adicionales (por género, edad, etc.)
- [ ] Gráficos de calor por hora del día
- [ ] Integración con sistema de alertas

## 📝 Notas Técnicas

- Los gráficos se generan dinámicamente con Chart.js
- Las consultas están optimizadas con agregaciones SQL
- Los datos se cargan mediante filtros GET
- La vista utiliza tabs de Bootstrap para organización
- Compatible con AdminLTE 3.x

---

**Desarrollado para**: Sistema de Gestión TVS  
**Módulo**: Enfermería  
**Versión**: 1.0  
**Última actualización**: Diciembre 2025
