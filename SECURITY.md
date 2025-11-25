# Security Policy

## Versiones Soportadas

Las siguientes versiones del Sistema TVS están actualmente soportadas con actualizaciones de seguridad:

| Versión | Soportada          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| 1.5.x   | :white_check_mark: |
| 1.4.x   | :x:                |
| < 1.4   | :x:                |

## Reportar una Vulnerabilidad

La seguridad del Sistema TVS es una prioridad. Si descubres una vulnerabilidad de seguridad, por favor sigue estos pasos:

### 1. NO crear un issue público

No reportes vulnerabilidades de seguridad a través de issues públicos de GitHub. Esto podría poner en riesgo a todos los usuarios del sistema.

### 2. Envía un reporte privado

Envía un email a **security@tvs.edu.co** con:

- Descripción detallada de la vulnerabilidad
- Pasos para reproducir el problema
- Impacto potencial
- Sugerencias de solución (si las tienes)

### 3. Espera nuestra respuesta

Nos comprometemos a:

- Responder en **48 horas** confirmando la recepción
- Evaluar la vulnerabilidad en **5 días hábiles**
- Mantente informado del progreso
- Notificarte cuando se publique una solución

### 4. Divulgación Coordinada

Solicitamos que:

- No divulgues públicamente la vulnerabilidad hasta que hayamos publicado una solución
- Nos des tiempo razonable para corregir el problema (típicamente 90 días)
- Trabajes con nosotros para entender mejor el problema

## Prácticas de Seguridad

### Para Desarrolladores

El proyecto implementa las siguientes prácticas de seguridad:

#### Autenticación y Autorización
- Autenticación con Laravel Sanctum
- Sistema de roles y permisos con Spatie
- Protección contra CSRF
- Validación de sesiones

#### Validación de Datos
- Validación en múltiples capas (Frontend, Backend)
- Sanitización de entradas
- Prepared statements para prevenir SQL injection
- Escape de salidas para prevenir XSS

#### Protección de Datos Sensibles
- Contraseñas hasheadas con bcrypt
- Variables de entorno para credenciales
- .env excluido del control de versiones
- Tokens de API seguros

#### Seguridad en Producción
- HTTPS obligatorio en producción
- Headers de seguridad configurados
- Rate limiting en APIs
- Logs de auditoría

### Para Usuarios

#### Contraseñas Seguras
- Mínimo 8 caracteres
- Incluir mayúsculas, minúsculas, números y símbolos
- No reutilizar contraseñas
- Cambiar contraseñas regularmente

#### Manejo de Sesiones
- Cerrar sesión al terminar
- No compartir credenciales
- Reportar actividad sospechosa

## Vulnerabilidades Conocidas

Mantemos un registro de vulnerabilidades conocidas y sus soluciones:

### [CVE-YYYY-XXXXX] - Descripción
**Severidad:** Alta  
**Versiones afectadas:** < 2.0.0  
**Solucionado en:** 2.0.0  
**Descripción:** [Descripción de la vulnerabilidad]  
**Solución:** Actualizar a versión 2.0.0 o superior

## Actualizaciones de Seguridad

Las actualizaciones de seguridad se publican de acuerdo a:

- **Críticas:** Inmediatamente
- **Altas:** Dentro de 7 días
- **Medias:** Próxima versión menor
- **Bajas:** Próxima versión mayor

## Configuraciones de Seguridad Recomendadas

### Producción

```env
# .env para producción
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tvs.edu.co

# Sesiones seguras
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# HTTPS
FORCE_HTTPS=true
```

### Headers de Seguridad

```php
// config/secure-headers.php
return [
    'x-frame-options' => 'SAMEORIGIN',
    'x-content-type-options' => 'nosniff',
    'x-xss-protection' => '1; mode=block',
    'strict-transport-security' => 'max-age=31536000; includeSubDomains',
];
```

## Checklist de Seguridad

### Antes de Desplegar

- [ ] Variables de entorno configuradas
- [ ] Debug mode desactivado
- [ ] HTTPS configurado
- [ ] Firewall configurado
- [ ] Backups automatizados
- [ ] Permisos de archivos correctos
- [ ] Logs de auditoría activados
- [ ] Rate limiting configurado

### Mantenimiento Regular

- [ ] Actualizar dependencias regularmente
- [ ] Revisar logs de seguridad
- [ ] Auditar permisos de usuarios
- [ ] Verificar certificados SSL
- [ ] Realizar backups periódicos
- [ ] Probar plan de recuperación

## Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/10.x/security)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

## Contacto

Para consultas de seguridad:

- **Email:** security@tvs.edu.co
- **PGP Key:** [Enlace a clave PGP pública]

---

Última actualización: 4 de noviembre de 2025
