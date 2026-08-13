# Solución al Error de Límite de Correos de Gmail

## Problema Detectado

Al intentar marcar una solicitud como "Hecho Cumplido", el sistema mostraba el siguiente error:

```
Error al marcar la solicitud como completada: Expected response code "354" but got code "550", 
with message "550 5.4.5 Daily user sending limit exceeded. For more information on Gmail 550-
5.4.5 sending limits go to 550 5.4.5 https://support.google.com/a/answer/166852"
```

### Causa

Gmail tiene un límite diario de **500 correos por día** para cuentas de Google Workspace (G Suite). Cuando se excede este límite, Gmail rechaza todos los intentos de envío adicionales con el error `550 5.4.5`.

El sistema intentaba enviar correos electrónicos de notificación al marcar una solicitud como "Hecho Cumplido", y si fallaba el envío, **también fallaba el guardado de la solicitud**, dejándola en un estado inconsistente.

## Solución Implementada

Se modificó el código para que **el proceso de marcar como cumplido continúe incluso si falla el envío de correos**. Los cambios incluyen:

### 1. QuotationController.php - Método `markCompleted()`

**Cambios realizados:**
- Se envolvió todo el código de envío de correos en un `try-catch` independiente
- Si falla el envío de correos, se registra el error pero **el proceso continúa**
- La solicitud se marca como "Hecho Cumplido" y cambia a estado "En pre-aprobación" **independientemente** de si los correos se enviaron
- Se muestra un mensaje de advertencia al usuario informando que los correos no fueron enviados pero la solicitud se guardó correctamente

**Código antes:**
```php
// Si fallaba el envío de correos, TODO fallaba
$notificationWithButton = new QuotationsUploaded($purchaseRequest->fresh());
foreach ($sectionEmails as $email) {
    Notification::route('mail', $email)->notify($notificationWithButton);
}
```

**Código después:**
```php
// Ahora con manejo de errores
$emailsSent = true;
$emailError = null;

try {
    $notificationWithButton = new QuotationsUploaded($purchaseRequest->fresh());
    foreach ($sectionEmails as $email) {
        Notification::route('mail', $email)->notify($notificationWithButton);
    }
} catch (\Exception $emailException) {
    // Registrar error pero CONTINUAR con el proceso
    $emailsSent = false;
    $emailError = $emailException->getMessage();
    \Log::error('Error al enviar correos (continuando): ' . $emailError);
}

// El guardado continúa independientemente del resultado del envío
$purchaseRequest->update([
    'status' => 'En pre-aprobación',
    'hecho_cumplido' => true,
    // ... otros campos
]);
```

### 2. Mensajes de Usuario Mejorados

**Si los correos se envían correctamente:**
```
✓ Solicitud marcada como completada exitosamente. 
  Se han enviado las notificaciones de preaprobación a: [emails]
```

**Si falla el envío por límite de Gmail:**
```
⚠ Solicitud marcada como completada exitosamente. 
  ADVERTENCIA: Se ha excedido el límite diario de envío de correos de Gmail. 
  Los correos NO fueron enviados, pero la solicitud se ha marcado correctamente 
  como completada y está en estado "En pre-aprobación". Deberá notificar 
  manualmente a los aprobadores o esperar a que se restablezca el límite diario 
  de Gmail.
```

**Si falla por otro motivo:**
```
⚠ Solicitud marcada como completada exitosamente. 
  ADVERTENCIA: Hubo un error al enviar los correos de notificación ([error]), 
  pero la solicitud se ha marcado correctamente como completada y está en 
  estado "En pre-aprobación".
```

## Límites de Gmail

### Google Workspace (Cuentas @tvs.edu.co)
- **500 correos por día** por usuario
- **10,000 destinatarios por día** por usuario (incluyendo CC y BCC)
- El límite se restablece a las **12:00 AM hora del Pacífico (PST/PDT)**

### Cómo Verificar el Uso Actual
No hay una forma directa de ver cuántos correos se han enviado, pero puedes:
1. Revisar los logs de Laravel en `storage/logs/laravel.log`
2. Buscar líneas que contengan "Enviando notificación"
3. Contar cuántos correos se enviaron en el día actual

### Recomendaciones

#### 1. Monitorear el Uso de Correos
```bash
# Ver correos enviados hoy
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "Enviando notificación" | wc -l
```

#### 2. Implementar Cola de Correos (Futuro)
Para evitar este problema, considera implementar un sistema de colas:
```php
// En lugar de enviar inmediatamente
Mail::to($email)->send(new Notification());

// Encolar para envío posterior
Mail::to($email)->queue(new Notification());
```

#### 3. Agrupar Notificaciones
En lugar de enviar un correo por cada acción, agrupa las notificaciones y envía resúmenes diarios.

#### 4. Usar Servicio de Correo Externo
Servicios como SendGrid, Mailgun o Amazon SES tienen límites mucho más altos:
- **SendGrid**: 100 correos/día (gratis), hasta 50,000/mes con planes pagos
- **Mailgun**: 5,000 correos/mes (gratis)
- **Amazon SES**: 62,000 correos/mes (gratis en el primer año)

#### 5. Múltiples Cuentas de Gmail
Si es necesario mantener Gmail, puedes configurar múltiples cuentas y rotar el envío entre ellas.

## Configuración Actual del Sistema

La configuración de correo está en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=intranet@tvs.edu.co
MAIL_PASSWORD=[password]
MAIL_FROM_ADDRESS="intranet@tvs.edu.co"
```

## Verificación de la Solución

Después de implementar estos cambios:

1. ✅ El sistema ya **NO falla** al marcar solicitudes como cumplidas si se excede el límite de Gmail
2. ✅ Las solicitudes se guardan correctamente en estado "En pre-aprobación"
3. ✅ Se marca `hecho_cumplido = true` y registra fecha/usuario
4. ✅ Se crea entrada en el historial de la solicitud
5. ✅ El usuario recibe un mensaje claro sobre el estado de los correos
6. ✅ Los administradores pueden ver en los logs qué correos no se enviaron

## Archivos Modificados

1. **app/Http/Controllers/QuotationController.php**
   - Método: `markCompleted()`
   - Cambio: Envío de correos envuelto en try-catch independiente

2. **app/Models/PurchaseRequest.php**
   - Método: `markDeliveryStatus()`
   - Cambio: Agregado logging de errores

3. **app/Http/Controllers/PurchaseRequestController.php**
   - Método: `markDeliveryStatus()`
   - Cambio: Manejo mejorado de excepciones con mensajes específicos para Gmail

## Monitoreo Continuo

Para monitorear este problema en el futuro:

```bash
# Ver errores de Gmail en los logs
tail -f storage/logs/laravel.log | grep "550 5.4.5"

# Ver cuántos correos se enviaron hoy
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "Enviando notificación" | wc -l
```

## Notas Importantes

- ⚠️ Los límites de Gmail se aplican **por cuenta**, no por aplicación
- ⚠️ Si se envían correos desde otros sistemas usando la misma cuenta, también cuentan para el límite
- ⚠️ El límite se restablece a medianoche PST (3 AM hora de Colombia aproximadamente)
- ✅ Con esta solución, el sistema **nunca perderá datos** por problemas de correo
- ✅ Los usuarios siempre son informados del estado real del envío de correos

## Contacto y Soporte

Si continúa habiendo problemas con el límite de correos:
1. Revisar los logs: `storage/logs/laravel.log`
2. Verificar el contador de correos enviados
3. Considerar migrar a un servicio de correo especializado (SendGrid/Mailgun)
4. Contactar a jefesistemas@tvs.edu.co

---
**Fecha de implementación:** 16 de octubre de 2025  
**Versión del sistema:** Laravel 11.46.1  
**Estado:** ✅ Implementado y probado
