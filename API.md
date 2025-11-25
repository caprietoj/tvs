# 📖 Documentación de API

## Índice
- [Autenticación](#autenticación)
- [Enfermería](#enfermería)
- [Equipos](#equipos)
- [Compras](#compras)
- [Colaboradores](#colaboradores)
- [Códigos de Estado](#códigos-de-estado)

---

## Autenticación

### Login
Autentica un usuario y retorna un token de sesión.

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "usuario@tvs.edu.co",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@tvs.edu.co",
    "role": "enfermeria"
  }
}
```

### Logout
Cierra la sesión del usuario actual.

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## Enfermería

### Listar Ingresos de Estudiantes
Obtiene una lista paginada de ingresos de estudiantes.

**Endpoint:** `GET /api/enfermeria/estudiantes`

**Query Parameters:**
- `page` (opcional): Número de página (default: 1)
- `per_page` (opcional): Registros por página (default: 20)
- `fecha_desde` (opcional): Filtro de fecha desde (YYYY-MM-DD)
- `fecha_hasta` (opcional): Filtro de fecha hasta (YYYY-MM-DD)
- `seccion` (opcional): Filtro por sección

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fecha": "2025-11-04",
      "estudiante_nombre": "María González",
      "estudiante_grado": "5°A",
      "descripcion_evento": "Dolor de cabeza",
      "accion_enfermeria": "Suministro de acetaminofén",
      "seguimiento": "Sí",
      "created_at": "2025-11-04T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "per_page": 20,
    "total": 95
  }
}
```

### Crear Ingreso de Estudiante
Registra un nuevo ingreso de estudiante.

**Endpoint:** `POST /api/enfermeria/estudiantes`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "fecha": "2025-11-04",
  "hora": "10:30",
  "estudiante_id": 123,
  "descripcion_evento": "Dolor de estómago. Náuseas leves.",
  "accion_enfermeria": "Reposo de 30 minutos. Suministro de agua.",
  "seguimiento": "Sí",
  "seguimiento_observaciones": "Llamar a padres si no mejora en 1 hora",
  "encuesta": "Sí",
  "estado": "Activo"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Ingreso registrado exitosamente",
  "data": {
    "id": 256,
    "fecha": "2025-11-04",
    "estudiante_nombre": "Carlos Rodríguez",
    "created_at": "2025-11-04T10:35:00.000000Z"
  }
}
```

### Exportar Reporte de Estudiantes
Genera un archivo Excel con el reporte filtrado.

**Endpoint:** `GET /api/enfermeria/estudiantes/export`

**Query Parameters:**
- `fecha_desde` (opcional): Fecha desde (YYYY-MM-DD)
- `fecha_hasta` (opcional): Fecha hasta (YYYY-MM-DD)
- `seccion` (opcional): Filtro por sección
- `cantidad_minima` (opcional): Cantidad mínima de ingresos

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="Reporte_Estudiantes_2025-11-04.xlsx"

[Binary Excel file]
```

### Reconocimiento de Voz
Procesa transcripción de voz a texto.

**Endpoint:** `POST /api/enfermeria/voice-transcription`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "field_id": "descripcion_evento",
  "transcript": "El estudiante presentó dolor de cabeza y náuseas",
  "is_final": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Transcripción procesada",
  "data": {
    "field_id": "descripcion_evento",
    "transcript": "El estudiante presentó dolor de cabeza y náuseas"
  }
}
```

---

## Equipos

### Listar Equipos Disponibles
Obtiene lista de equipos disponibles para préstamo.

**Endpoint:** `GET /api/equipment/available`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Proyector Sony",
      "codigo": "PROY-001",
      "categoria": "Proyectores",
      "estado": "Disponible",
      "ubicacion": "Sala de Profesores"
    }
  ]
}
```

### Crear Solicitud de Préstamo
Crea una nueva solicitud de préstamo de equipo.

**Endpoint:** `POST /api/equipment/loans`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "equipment_id": 1,
  "loan_date": "2025-11-06",
  "return_date": "2025-11-08",
  "purpose": "Clase de matemáticas",
  "location": "Aula 301"
}
```

**Validaciones:**
- `loan_date` debe ser posterior a hoy (mínimo 1 día de anticipación)
- `return_date` debe ser posterior a `loan_date`
- Restricciones por día de la semana:
  - Viernes: Máximo hasta domingo siguiente semana (+9 días)
  - Sábado/Domingo: Máximo hasta domingo siguiente semana
  - Lunes-Jueves: Máximo hasta domingo misma semana

**Response (201):**
```json
{
  "success": true,
  "message": "Solicitud de préstamo creada exitosamente",
  "data": {
    "id": 45,
    "equipment_name": "Proyector Sony",
    "loan_date": "2025-11-06",
    "return_date": "2025-11-08",
    "status": "Pendiente"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "loan_date": [
      "Los préstamos deben solicitarse con al menos un día de anticipación"
    ]
  }
}
```

---

## Compras

### Crear Solicitud de Compra
Crea una nueva solicitud de compra.

**Endpoint:** `POST /api/purchase-requests`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "descripcion": "Resmas de papel carta",
  "cantidad": 10,
  "unidad": "Resma",
  "precio_estimado": 12500,
  "total_estimado": 125000,
  "justificacion": "Necesario para impresiones del mes",
  "seccion": "Administrativa",
  "centro_costo": "Papelería"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Solicitud de compra creada exitosamente",
  "data": {
    "id": 78,
    "codigo": "SOL-2025-078",
    "descripcion": "Resmas de papel carta",
    "total_estimado": 125000,
    "estado": "Pendiente",
    "created_at": "2025-11-04T11:00:00.000000Z"
  }
}
```

---

## Colaboradores

### Buscar Colaborador
Busca colaboradores por nombre o documento.

**Endpoint:** `GET /api/colaboradores/search`

**Query Parameters:**
- `q` (requerido): Término de búsqueda

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "nombre": "Ana María Torres",
      "documento": "1234567890",
      "tipo": "Docente",
      "area": "Matemáticas",
      "email": "atorres@tvs.edu.co"
    }
  ]
}
```

---

## Códigos de Estado

### Códigos de Éxito
- **200 OK**: Solicitud exitosa
- **201 Created**: Recurso creado exitosamente
- **204 No Content**: Solicitud exitosa sin contenido de respuesta

### Códigos de Error del Cliente
- **400 Bad Request**: Solicitud malformada
- **401 Unauthorized**: No autenticado
- **403 Forbidden**: No autorizado
- **404 Not Found**: Recurso no encontrado
- **422 Unprocessable Entity**: Error de validación

### Códigos de Error del Servidor
- **500 Internal Server Error**: Error interno del servidor
- **503 Service Unavailable**: Servicio no disponible

---

## Formato de Errores

Todos los errores siguen este formato:

```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {
    "campo": [
      "Mensaje de error específico"
    ]
  },
  "code": "ERROR_CODE"
}
```

### Ejemplos de Errores

#### Error de Autenticación
```json
{
  "success": false,
  "message": "No autenticado",
  "code": "UNAUTHENTICATED"
}
```

#### Error de Validación
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "loan_date": [
      "Los préstamos deben solicitarse con al menos un día de anticipación"
    ],
    "equipment_id": [
      "El equipo seleccionado no está disponible"
    ]
  },
  "code": "VALIDATION_ERROR"
}
```

#### Error de Permisos
```json
{
  "success": false,
  "message": "No tienes permisos para realizar esta acción",
  "code": "FORBIDDEN"
}
```

---

## Rate Limiting

Las peticiones a la API están limitadas a:
- **60 peticiones por minuto** para usuarios autenticados
- **10 peticiones por minuto** para peticiones sin autenticar

Headers de respuesta:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1699104000
```

---

## Versionado

La API usa versionado semántico. La versión actual es `v1`.

Todas las URLs de la API comienzan con `/api/v1/`.

---

## Soporte

Para soporte de la API:
- Email: dev@tvs.edu.co
- Documentación: https://github.com/caprietoj/tvs/wiki
