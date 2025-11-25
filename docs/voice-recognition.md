---
layout: page
title: Reconocimiento de Voz
description: Sistema de transcripción de voz a texto en formularios de enfermería
---

# 🎤 Sistema de Reconocimiento de Voz

El Sistema TVS incluye una funcionalidad avanzada de reconocimiento de voz que permite transcribir audio a texto en tiempo real, facilitando el registro rápido de información en formularios de enfermería.

## 📋 Características

### ✅ Funcionalidades Principales

- **Transcripción en tiempo real**: Convierte voz a texto mientras hablas
- **Reconocimiento continuo**: Sigue escuchando sin necesidad de pausar
- **Idioma español de Colombia**: Optimizado para acento colombiano
- **Resultados intermedios**: Muestra transcripción provisional mientras hablas
- **Indicadores visuales**: Animaciones que indican cuando está grabando
- **Manejo de errores**: Mensajes claros cuando hay problemas

### 🎯 Campos Soportados

El reconocimiento de voz está disponible en:

#### Formulario de Estudiantes
- **Descripción del Evento**: Campo de texto largo para describir el incidente
- **Acción de Enfermería**: Campo para registrar las acciones tomadas

#### Formulario de Colaboradores
- **Descripción del Evento**: Campo de texto largo para describir el incidente
- **Acción de Enfermería**: Campo para registrar las acciones tomadas

## 🚀 Cómo Usar

### Paso 1: Acceder al Formulario

1. Ingresa al sistema TVS
2. Navega a **Enfermería** → **Ingreso Estudiantes** o **Ingreso Colaboradores**
3. Completa los campos iniciales (fecha, hora, persona)

### Paso 2: Activar Reconocimiento de Voz

1. Localiza el campo donde quieres usar voz (Descripción del Evento o Acción de Enfermería)
2. Haz clic en el **botón del micrófono** 🎤 al lado derecho del campo
3. Tu navegador solicitará permiso para usar el micrófono
4. Haz clic en **"Permitir"**

### Paso 3: Hablar

1. El botón del micrófono se pondrá **rojo** indicando que está grabando
2. Habla claramente hacia el micrófono
3. El texto aparecerá en tiempo real en el campo
4. Puedes hacer pausas naturales mientras hablas

### Paso 4: Detener

1. Haz clic nuevamente en el **botón del micrófono**
2. El botón volverá a su color original (azul)
3. La transcripción quedará guardada en el campo

## 💡 Consejos para Mejor Reconocimiento

### Ambiente
- 🔇 **Ambiente silencioso**: Minimiza ruido de fondo
- 🎧 **Micrófono de calidad**: Usa un buen micrófono si es posible
- 📏 **Distancia adecuada**: Habla a 10-15 cm del micrófono

### Dicción
- 🗣️ **Habla claramente**: Pronuncia bien cada palabra
- ⏱️ **Velocidad moderada**: No hables muy rápido ni muy lento
- 🎯 **Términos médicos**: Pronuncia términos médicos con precisión
- ✂️ **Pausas naturales**: Haz pausas entre ideas

### Corrección
- ✏️ **Revisa el texto**: Siempre verifica la transcripción
- 🔧 **Edita si es necesario**: Corrige errores manualmente
- 🔄 **Vuelve a grabar**: Si hay muchos errores, intenta de nuevo

## 🌐 Compatibilidad de Navegadores

### ✅ Totalmente Compatible

| Navegador | Versión Mínima | Notas |
|-----------|---------------|-------|
| Chrome    | 25+           | Mejor rendimiento |
| Edge      | 79+           | Excelente soporte |
| Safari    | 14.1+         | Requiere permisos |
| Opera     | 27+           | Buen soporte |

### ❌ No Compatible

| Navegador | Razón |
|-----------|-------|
| Firefox   | No soporta Web Speech API |
| IE 11     | Navegador obsoleto |

### 📱 Dispositivos Móviles

- **Android Chrome**: ✅ Compatible
- **iOS Safari**: ✅ Compatible (iOS 14.5+)
- **Android Firefox**: ❌ No compatible

## 🔧 Configuración Técnica

### Permisos del Navegador

El sistema necesita acceso al micrófono:

1. **Primera vez**: El navegador pedirá permiso
2. **Permitir siempre**: Marca "Recordar esta decisión"
3. **Revocar**: Puedes cambiar en configuración del navegador

### Configuración del Idioma

El sistema está configurado para español de Colombia (`es-CO`):

```javascript
recognition.lang = 'es-CO';
```

Para cambiar el idioma, modifica en el archivo:
```
resources/views/enfermeria/ingreso-estudiantes/create.blade.php
resources/views/enfermeria/ingreso-colaboradores/create.blade.php
```

### Parámetros de Reconocimiento

```javascript
recognition.continuous = true;        // Reconocimiento continuo
recognition.interimResults = true;    // Resultados intermedios
recognition.maxAlternatives = 1;      // Número de alternativas
```

## 🐛 Solución de Problemas

### El botón no aparece

**Problema**: No veo el botón del micrófono  
**Solución**: 
- Verifica que estés usando un navegador compatible
- Actualiza la página (Ctrl + F5)
- Limpia caché del navegador

### No se escucha nada

**Problema**: El botón está rojo pero no transcribe  
**Solución**:
- Verifica que el micrófono esté conectado
- Revisa configuración de audio del sistema
- Prueba hablar más cerca del micrófono
- Verifica permisos del navegador

### Errores de transcripción

**Problema**: El texto transcrito tiene muchos errores  
**Solución**:
- Habla más despacio y claro
- Reduce ruido de fondo
- Usa un mejor micrófono
- Corrige manualmente después

### Error "Permiso denegado"

**Problema**: Mensaje de permisos denegados  
**Solución**:
1. Click en el icono de candado/información en la barra de direcciones
2. Busca "Micrófono"
3. Cambia a "Permitir"
4. Recarga la página

### Error "No se pudo iniciar"

**Problema**: Mensaje de error al iniciar reconocimiento  
**Solución**:
- Cierra otras aplicaciones que usen el micrófono
- Verifica que el micrófono funcione en otras apps
- Reinicia el navegador
- Reinicia el sistema

## 📊 Ejemplos de Uso

### Ejemplo 1: Descripción de Evento

**Usuario habla:**
> "El estudiante Juan Pérez de grado quinto A presentó dolor abdominal intenso acompañado de náuseas. Inició hace aproximadamente treinta minutos durante la clase de educación física. No presenta fiebre ni otros síntomas."

**Sistema transcribe:**
```
El estudiante Juan Pérez de grado quinto A presentó dolor 
abdominal intenso acompañado de náuseas. Inició hace 
aproximadamente treinta minutos durante la clase de educación 
física. No presenta fiebre ni otros síntomas.
```

### Ejemplo 2: Acción de Enfermería

**Usuario habla:**
> "Se realizó toma de signos vitales. Temperatura treinta y seis punto cinco grados celsius. Presión arterial ciento diez sobre setenta. Se administró acetaminofén quinientos miligramos vía oral. Se dejó en reposo durante treinta minutos."

**Sistema transcribe:**
```
Se realizó toma de signos vitales. Temperatura treinta y seis 
punto cinco grados celsius. Presión arterial ciento diez sobre 
setenta. Se administró acetaminofén quinientos miligramos vía 
oral. Se dejó en reposo durante treinta minutos.
```

## 🔒 Privacidad y Seguridad

### Datos de Audio

- **No se almacena audio**: Solo se guarda el texto transcrito
- **Procesamiento local**: El navegador procesa el audio
- **Sin grabaciones**: No se generan archivos de audio
- **Confidencialidad**: Cumple con normativas de privacidad

### API Utilizada

El sistema usa **Web Speech API** del navegador:
- API estándar del navegador
- No envía datos a servidores externos
- Procesamiento en el dispositivo
- Privacidad garantizada

## 🚀 Futuras Mejoras

Próximas características planeadas:

- [ ] Comandos de voz para navegación
- [ ] Puntuación automática inteligente
- [ ] Detección de términos médicos
- [ ] Múltiples idiomas
- [ ] Transcripción offline
- [ ] Corrección ortográfica automática

## 📚 Recursos Adicionales

- [Web Speech API Documentation](https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API)
- [Browser Compatibility](https://caniuse.com/speech-recognition)
- [Best Practices](https://web.dev/articles/using-the-web-speech-api)

## 🤝 Soporte

¿Necesitas ayuda con reconocimiento de voz?

- **Email**: soporte@tvs.edu.co
- **Issue**: [Crear issue](https://github.com/caprietoj/tvs/issues/new?labels=voice-recognition)
- **Documentación**: Esta página

---

Última actualización: 4 de noviembre de 2025
