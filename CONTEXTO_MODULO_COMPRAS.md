# 📋 CONTEXTO COMPLETO - MÓDULO DE COMPRAS Y SERVICIOS

## 🎯 VISIÓN GENERAL

El módulo de compras es un sistema completo de gestión de solicitudes de compra, servicios y materiales que incluye:
- Solicitudes de compra/servicios/materiales
- Sistema de cotizaciones
- Órdenes de compra
- Aprobaciones multinivel
- Gestión de presupuesto
- Control de entregas

---

## 📊 FLUJO GENERAL DEL PROCESO

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE COMPRAS TVS                          │
└─────────────────────────────────────────────────────────────────┘

1. CREACIÓN DE SOLICITUD
   │
   ├─► Usuario crea solicitud (Compra/Servicio/Material)
   │   └─► Estado: "pending"
   │
2. REVISIÓN Y COTIZACIÓN
   │
   ├─► Personal de Compras recibe notificación
   ├─► Se solicitan cotizaciones a proveedores
   │   └─► Mínimo: 3 cotizaciones (configurable)
   │
3. PRE-APROBACIÓN
   │
   ├─► Supervisor/Coordinador revisa cotizaciones
   ├─► Selecciona mejor cotización o selección mixta
   │   └─► Estado: "En pre-aprobación"
   │
4. APROBACIÓN FINAL
   │
   ├─► Administrador/Rector aprueba solicitud
   │   └─► Estado: "approved" o "in_process"
   │
5. GENERACIÓN DE ORDEN DE COMPRA
   │
   ├─► Sistema crea Orden de Compra automáticamente
   ├─► O creación manual (sin cotización)
   │   └─► Orden lista para revisión
   │
6. APROBACIÓN DE ORDEN
   │
   ├─► Administrador aprueba orden
   │   └─► Estado orden: "approved"
   │
7. PAGO Y SEGUIMIENTO
   │
   ├─► Se envía a contabilidad para pago
   ├─► Se registra información de pago
   │   └─► Estado orden: "Enviado a Contabilidad"
   │
8. ENTREGA
   │
   ├─► Se marca entrega (delivered/not_delivered)
   ├─► Opción: Hecho cumplido
   │   └─► Solicitud completada
```

---

## 🗂️ TIPOS DE SOLICITUDES

### 1. **COMPRA** (`purchase`)
- Adquisición de bienes/productos
- Requiere 3 cotizaciones (por defecto)
- Genera orden de compra
- Control de presupuesto por centro de costo
- **Estados:** pending → En Cotización → En pre-aprobación → approved → in_process

### 2. **SERVICIOS** (`services`)
- Contratación de servicios
- **Dos modalidades:**
  - **Con cotización:** Flujo normal de cotizaciones
  - **Sin cotización:** Para servicios urgentes o especiales
    - No requiere proceso de cotización
    - Autorizado directamente por administrador
    - Datos de proveedor ingresados manualmente
- **Estados:** pending → approved/rejected

### 3. **MATERIALES** (`materials`)
- Solicitud de materiales (papelería, fotocopias, etc.)
- Proceso más simple
- **Campos especiales:**
  - Tamaño de papel (A4, Carta, Oficio)
  - Tipo de papel (Bond, Reciclado)
  - Color (Blanco, Colores)
  - Encuadernación (Sí/No)
  - Plastificado (Sí/No)
  - Corte especial
- **Estados:** pending → approved/rejected

---

## 📂 MODELOS PRINCIPALES

### 1. **PurchaseRequest** (Solicitud de Compra)

**Campos principales:**
```php
- id
- request_number         // Número único de solicitud (auto-generado)
- user_id                // Usuario que crea la solicitud
- type                   // 'purchase', 'services', 'materials'
- status                 // 'pending', 'approved', 'rejected', 'in_process', 'En Cotización', 'En pre-aprobación'
- request_date           // Fecha de solicitud
- requester              // Nombre del solicitante
- section_area           // Sección/Área/Departamento
- code                   // Código de centro de costo
- grade                  // Grado (para educación)
- section                // Sección específica
- delivery_date          // Fecha de entrega esperada

// Para COMPRAS
- purchase_items         // JSON con items solicitados
- purchase_justification // Justificación de la compra
- budget                 // Presupuesto disponible

// Para SERVICIOS
- service_items          // JSON con servicios solicitados
- service_budget         // Presupuesto para servicios
- service_justification  // Justificación del servicio
- coordinator            // Coordinador del servicio

// Para MATERIALES
- material_items         // JSON con materiales
- copy_items             // Items para fotocopias
- observations           // Observaciones generales

// COTIZACIONES
- selected_quotation_id  // Cotización seleccionada
- required_quotations    // Número de cotizaciones requeridas (default: 3)
- can_proceed_early      // Permite continuar sin todas las cotizaciones

// PRE-APROBACIÓN
- pre_approved_by        // Usuario que pre-aprueba
- pre_approved_at        // Fecha de pre-aprobación
- pre_approval_comments  // Comentarios de pre-aprobación
- preapproval_sent_at    // Cuando se envió a pre-aprobación

// APROBACIÓN FINAL
- approved_by            // Usuario que aprueba
- approval_date          // Fecha de aprobación
- rejection_reason       // Razón de rechazo (si aplica)

// ENTREGA
- delivery_status        // 'pending', 'delivered', 'not_delivered'
- delivery_marked_at     // Fecha de marcado de entrega
- delivery_marked_by     // Usuario que marca entrega
- delivery_notes         // Notas de entrega

// HECHO CUMPLIDO
- hecho_cumplido         // Boolean
- hecho_cumplido_at      // Fecha
- hecho_cumplido_by      // Usuario

// SERVICIOS SIN COTIZACIÓN
- service_type           // Tipo de servicio
- provider_id            // ID del proveedor
- provider_name          // Nombre del proveedor
- provider_nit           // NIT
- provider_contact       // Contacto
- provider_email         // Email
- no_quotation_reason    // Razón para no cotizar
- quotation_file_path    // Archivo de cotización subido
- applied_taxes          // JSON con impuestos aplicados
- subtotal_amount        // Subtotal
- tax_amount             // Monto de impuestos
- total_amount           // Total

// COMPRA COMPARTIDA
- is_shared              // Boolean - si es compartida
- shared_section         // Sección con la que se comparte
- my_percentage          // Porcentaje de la sección solicitante
- shared_percentage      // Porcentaje de la otra sección
- third_shared_section   // Tercera sección (opcional)
- third_shared_percentage// Porcentaje tercera sección
- shared_budget          // Presupuesto compartido
- third_shared_budget    // Presupuesto tercera sección

// ARCHIVOS
- original_file          // Archivo original de la solicitud
- attached_files         // JSON con archivos adjuntos
```

**Relaciones:**
```php
- user()                 // Pertenece a un usuario
- approver()             // Usuario que aprobó
- quotations()           // Tiene muchas cotizaciones
- selectedQuotation()    // Cotización seleccionada
- quotationItemSelections() // Selección mixta de items
- purchaseOrders()       // Órdenes de compra generadas
- histories()            // Historial de cambios
- deliveryMarker()       // Usuario que marcó entrega
- hechoCumplidoUser()    // Usuario que marcó hecho cumplido
```

**Métodos importantes:**
```php
- markDeliveryStatus($status, $userId, $notes)
- isDelivered()
- isNotDelivered()
- isPendingDelivery()
- updateStatus($status, $userId, $notes)
- getRequiredQuotationsCount()
- hasAllQuotations()
- canProceedWithQuotations()
- isNoQuotationService()
- getTotalSharedPercentage()
```

---

### 2. **Quotation** (Cotización)

**Campos principales:**
```php
- id
- purchase_request_id    // Solicitud relacionada
- quotation_number       // Número de cotización
- provider_name          // Nombre del proveedor
- provider_nit           // NIT del proveedor
- provider_email         // Email del proveedor
- provider_phone         // Teléfono
- contact_person         // Persona de contacto
- quotation_date         // Fecha de la cotización
- validity_days          // Días de validez
- payment_terms          // Términos de pago
- delivery_time          // Tiempo de entrega
- observations           // Observaciones
- subtotal               // Subtotal
- tax_percentage         // Porcentaje de IVA
- tax_amount             // Monto de IVA
- total_amount           // Total de la cotización
- file_path              // Archivo PDF de la cotización
- items                  // JSON con items cotizados
- status                 // 'active', 'selected', 'rejected'
- is_selected            // Boolean - si fue seleccionada
- created_by             // Usuario que creó la cotización
```

**Estructura de items:**
```json
[
  {
    "description": "Laptop Dell Inspiron",
    "quantity": 5,
    "unit_price": 1500000,
    "total": 7500000
  }
]
```

**Relaciones:**
```php
- purchaseRequest()      // Pertenece a una solicitud
- creator()              // Usuario que creó
- itemSelections()       // Selecciones individuales de items
```

---

### 3. **PurchaseOrder** (Orden de Compra)

**Campos principales:**
```php
- id
- order_number           // Número de orden (auto-generado)
- purchase_request_id    // Solicitud relacionada
- provider_id            // ID del proveedor (de tabla proveedors)
- provider_name          // Nombre del proveedor
- provider_nit           // NIT
- provider_address       // Dirección
- provider_phone         // Teléfono
- provider_email         // Email
- provider_contact_person// Persona de contacto
- order_date             // Fecha de la orden
- delivery_date          // Fecha de entrega esperada
- payment_terms          // Términos de pago
- subtotal               // Subtotal
- tax_percentage         // Porcentaje de IVA
- tax                    // Monto de IVA
- total                  // Total de la orden
- observations           // Observaciones
- items                  // JSON con items de la orden
- additional_items       // JSON con items adicionales
- status                 // 'pending', 'approved', 'Enviado a Contabilidad', 'Pagado'
- approved_at            // Fecha de aprobación
- approved_by            // Usuario que aprobó
- pdf_path               // Ruta del PDF generado
- created_by             // Usuario que creó la orden

// INFORMACIÓN DE PAGO
- payment_method         // Método de pago
- payment_reference      // Referencia de pago
- payment_date           // Fecha de pago
- payment_observations   // Observaciones del pago
- payment_file           // Archivo de comprobante de pago
```

**Estructura de items:**
```json
[
  {
    "description": "Laptop Dell Inspiron 15",
    "quantity": 5,
    "unit_price": 1500000,
    "tax_rate": 19,
    "subtotal": 7500000,
    "tax_amount": 1425000,
    "total": 8925000
  }
]
```

**Relaciones:**
```php
- purchaseRequest()      // Pertenece a una solicitud
- approver()             // Usuario que aprobó
- creator()              // Usuario que creó
- provider()             // Proveedor (modelo Proveedor)
```

**Métodos:**
```php
- generatePDF()          // Genera PDF de la orden
- sendToAccounting()     // Envía a contabilidad
- markAsPaid()           // Marca como pagada
- registerPayment()      // Registra información de pago
```

---

### 4. **QuotationItemSelection** (Selección Mixta)

Permite seleccionar items de diferentes cotizaciones:

```php
- id
- purchase_request_id    // Solicitud
- quotation_id           // Cotización de donde se tomó el item
- item_index             // Índice del item en la cotización
- item_description       // Descripción del item
- quantity               // Cantidad
- unit_price             // Precio unitario
- total_price            // Precio total
- selected_by            // Usuario que seleccionó
- selected_at            // Fecha de selección
```

**Relaciones:**
```php
- purchaseRequest()
- quotation()
- selector()             // Usuario que seleccionó
```

---

### 5. **Proveedor** (Proveedores)

**Campos:**
```php
- id
- nit                    // NIT del proveedor (único)
- nombre                 // Nombre
- direccion              // Dirección
- telefono               // Teléfono
- email                  // Email
- contacto               // Persona de contacto
- activo                 // Boolean - activo/inactivo
```

---

### 6. **RequestHistory** (Historial)

**Campos:**
```php
- id
- purchase_request_id    // Solicitud relacionada
- user_id                // Usuario que realizó la acción
- action                 // Acción realizada
- notes                  // Notas adicionales
- created_at             // Fecha de la acción
```

**Ejemplos de acciones:**
- "Solicitud creada"
- "Cotización agregada"
- "Cambio de estado a: approved"
- "Orden de compra creada"
- "Orden de compra aprobada"
- "Entrega marcada como: delivered"

---

## 🔄 ESTADOS DEL SISTEMA

### Estados de PurchaseRequest:

1. **`pending`** - Pendiente de cotización/aprobación
2. **`En Cotización`** - Personal de compras añadiendo cotizaciones
3. **`En pre-aprobación`** - Supervisor revisando cotizaciones
4. **`approved`** - Aprobada y lista para orden de compra
5. **`in_process`** - En proceso (equivalente a approved)
6. **`rejected`** - Rechazada

### Estados de PurchaseOrder:

1. **`pending`** - Pendiente de aprobación
2. **`approved`** - Aprobada y lista para pago
3. **`Enviado a Contabilidad`** - En proceso de pago
4. **`Pagado`** - Pago completado

### Estados de Delivery:

1. **`pending`** - Pendiente de entrega
2. **`delivered`** - Entregado
3. **`not_delivered`** - No entregado

---

## 👥 ROLES Y PERMISOS

### 1. **Usuario Normal**
- ✅ Crear solicitudes de compra/servicios/materiales
- ✅ Ver sus propias solicitudes
- ✅ Ver historial de sus solicitudes
- ❌ No puede aprobar
- ❌ No puede ver solicitudes de otros

### 2. **Rol: `compras`** (Personal de Compras)
- ✅ Ver todas las solicitudes
- ✅ Agregar cotizaciones
- ✅ Subir archivos PDF de cotizaciones
- ✅ Enviar a pre-aprobación
- ✅ Crear órdenes de compra (automáticas y manuales)
- ✅ Gestionar proveedores
- ❌ No puede aprobar solicitudes
- ❌ No puede aprobar órdenes

### 3. **Rol: Supervisor/Coordinador**
- ✅ Pre-aprobar cotizaciones
- ✅ Seleccionar cotización ganadora
- ✅ Selección mixta (items de diferentes cotizaciones)
- ✅ Comentar cotizaciones
- ✅ Enviar a aprobación final
- ❌ No puede aprobar finalmente

### 4. **Rol: `admin`** (Administrador/Rector)
- ✅ Aprobar/Rechazar solicitudes
- ✅ Aprobar órdenes de compra
- ✅ Crear órdenes sin cotización
- ✅ Ver todas las solicitudes y órdenes
- ✅ Enviar órdenes a contabilidad
- ✅ Marcar entregas
- ✅ Marcar hecho cumplido
- ✅ Acceso completo al sistema

---

## 📧 NOTIFICACIONES POR EMAIL

### 1. **Solicitud Creada**
- **Para:** Personal de Compras (`compras` role)
- **Cuándo:** Usuario crea nueva solicitud
- **Contenido:** Detalles de la solicitud, link para ver

### 2. **Cotización Agregada**
- **Para:** Usuario solicitante
- **Cuándo:** Se agrega una nueva cotización
- **Contenido:** Información de la cotización

### 3. **Pre-aprobación Solicitada**
- **Para:** Supervisor/Coordinador correspondiente
- **Cuándo:** Compras envía a pre-aprobación
- **Contenido:** Cotizaciones para revisar, link para seleccionar

### 4. **Solicitud Pre-aprobada**
- **Para:** Administradores
- **Cuándo:** Supervisor pre-aprueba
- **Contenido:** Cotización seleccionada, link para aprobar

### 5. **Solicitud Aprobada**
- **Para:** Usuario solicitante y Personal de Compras
- **Cuándo:** Administrador aprueba
- **Contenido:** Notificación de aprobación

### 6. **Solicitud Rechazada**
- **Para:** Usuario solicitante
- **Cuándo:** Se rechaza la solicitud
- **Contenido:** Razón del rechazo

### 7. **Orden Creada**
- **Para:** Administrador
- **Cuándo:** Se crea una orden de compra
- **Contenido:** PDF de la orden, cotizaciones, link para aprobar

### 8. **Orden Aprobada**
- **Para:** Personal de Compras y Contabilidad
- **Cuándo:** Administrador aprueba orden
- **Contenido:** PDF de orden aprobada, datos para pago

### 9. **Orden Enviada a Contabilidad**
- **Para:** Departamento de Contabilidad
- **Cuándo:** Se envía orden para pago
- **Contenido:** Orden lista para procesar pago

---

## 🎨 FUNCIONALIDADES ESPECIALES

### 1. **Selección Mixta de Cotizaciones**

Permite elegir diferentes items de diferentes cotizaciones:

**Ejemplo:**
```
Item 1: Laptop → Proveedor A ($1,500,000)
Item 2: Mouse  → Proveedor B ($25,000)
Item 3: Teclado → Proveedor A ($45,000)

Total: Se crean 2 órdenes de compra:
- Orden 1: Proveedor A (Laptop + Teclado)
- Orden 2: Proveedor B (Mouse)
```

**Implementación:**
- Tabla: `quotation_item_selections`
- Vista: Checkbox por cada item en cada cotización
- Validación: Al menos un item debe ser seleccionado

### 2. **Servicios Sin Cotización**

Para servicios urgentes o especiales que no requieren cotizaciones:

**Proceso:**
1. Usuario marca "Sin cotización requerida"
2. Llena datos del proveedor manualmente
3. Sube archivo de cotización/presupuesto
4. Administrador autoriza directamente
5. Se crea orden de compra manual

**Ventajas:**
- Agiliza servicios urgentes
- No requiere esperar 3 cotizaciones
- Útil para proveedores exclusivos

### 3. **Compra Compartida**

Permite dividir costos entre 2 o 3 secciones:

**Ejemplo:**
```
Solicitud de Primaria por $1,000,000
- Primaria: 60% = $600,000
- Bachillerato: 40% = $400,000

Cada sección tiene su propio presupuesto afectado
```

**Validaciones:**
- La suma de porcentajes debe ser 100%
- Cada sección debe tener presupuesto disponible
- Se registra en ambos centros de costo

### 4. **Materiales con Especificaciones**

Para solicitudes de papelería y fotocopias:

**Campos especiales:**
- Tamaño de papel: A4, Carta, Oficio, Tabloide
- Tipo: Bond 75g, Bond 90g, Reciclado
- Color: Blanco, Colores (especificar)
- Encuadernación: Sí/No (tipo de encuadernación)
- Plastificado: Sí/No (grosor)
- Corte especial: Descripción

**Uso:**
- Facilita especificaciones exactas
- Evita errores en pedidos
- Estandariza solicitudes

### 5. **Generación Automática de PDF**

Las órdenes de compra se generan automáticamente con:

**Contenido del PDF:**
```
┌─────────────────────────────────────────┐
│         ORDEN DE COMPRA #OC-0001        │
├─────────────────────────────────────────┤
│ Fecha: 18/10/2025                       │
│ Proveedor: PROVEEDOR EJEMPLO SAS        │
│ NIT: 900.123.456-7                      │
│ Dirección: Calle 123 #45-67             │
│ Teléfono: 601 234 5678                  │
│ Email: ventas@proveedor.com             │
├─────────────────────────────────────────┤
│ ITEMS:                                  │
│ 1. Laptop Dell Inspiron                 │
│    Cant: 5 x $1,500,000 = $7,500,000   │
│ 2. Mouse Logitech                       │
│    Cant: 10 x $25,000 = $250,000       │
├─────────────────────────────────────────┤
│ Subtotal:           $7,750,000          │
│ IVA (19%):         $1,472,500          │
│ TOTAL:             $9,222,500          │
├─────────────────────────────────────────┤
│ Solicitado por: Juan Pérez              │
│ Aprobado por: Rector Admin              │
│                                         │
│ IMPORTANTE: ENVIAR FACTURA A:           │
│ facturacion@virtual.net.co              │
└─────────────────────────────────────────┘
```

**Características:**
- Logo del colegio
- Numeración automática
- Códigos QR (opcional)
- Firma digital (opcional)
- Múltiples formatos de visualización

### 6. **Control de Entregas**

Sistema para marcar cuando los productos/servicios fueron entregados:

**Estados:**
- `pending`: Pendiente de entrega
- `delivered`: Entregado satisfactoriamente
- `not_delivered`: No entregado / Problema

**Información adicional:**
- Fecha y hora de entrega
- Usuario que marca
- Notas sobre la entrega
- Opción de "Hecho Cumplido"

### 7. **Hecho Cumplido**

Marca que el proceso está completamente finalizado:

**Requiere:**
- Solicitud aprobada
- Orden de compra creada y aprobada
- Orden pagada
- Productos/servicios entregados

**Efectos:**
- Se considera proceso cerrado
- Se archiva automáticamente
- No permite más modificaciones

---

## 🗃️ ARCHIVOS Y DOCUMENTOS

### Archivos que se pueden adjuntar:

1. **En Solicitudes:**
   - Archivo original de la solicitud (Word, PDF, Excel)
   - Archivos adicionales (múltiples)
   - Cotización previa (si existe)

2. **En Cotizaciones:**
   - PDF de la cotización del proveedor (obligatorio)

3. **En Órdenes de Compra:**
   - PDF generado automáticamente
   - Comprobante de pago (al registrar pago)

4. **Servicios Sin Cotización:**
   - Archivo de cotización/presupuesto del proveedor

**Almacenamiento:**
- Directorio: `storage/app/public/`
- Subdirectorios organizados por tipo
- Links simbólicos configurados

---

## 🔍 BÚSQUEDAS Y FILTROS

### En Solicitudes:

- Por número de solicitud
- Por solicitante
- Por sección/área
- Por estado
- Por tipo (compra/servicio/material)
- Por rango de fechas
- Por centro de costo

### En Órdenes de Compra:

- Por número de orden
- Por proveedor
- Por monto
- Por estado
- Por fecha de creación
- Por solicitud relacionada

### En Cotizaciones:

- Por proveedor
- Por solicitud
- Por rango de precios
- Por fecha de validez

---

## 📊 REPORTES Y ESTADÍSTICAS

### Reportes disponibles:

1. **Solicitudes por período**
2. **Órdenes por proveedor**
3. **Gastos por centro de costo**
4. **Tiempo promedio de aprobación**
5. **Proveedores más utilizados**
6. **Solicitudes pendientes**
7. **Estado de entregas**

---

## 🛠️ CONTROLADORES PRINCIPALES

### 1. **PurchaseRequestsController**
```php
// Rutas principales
- index()                     // Lista solicitudes
- create()                    // Formulario de creación
- store()                     // Guarda nueva solicitud
- show($id)                   // Muestra detalles
- edit($id)                   // Formulario de edición
- update($id)                 // Actualiza solicitud
- destroy($id)                // Elimina solicitud
- approve($id)                // Aprueba solicitud
- reject($id)                 // Rechaza solicitud
- markDelivery($id)           // Marca entrega
- markHechoCumplido($id)      // Marca hecho cumplido
```

### 2. **QuotationController**
```php
- index($purchaseRequestId)   // Lista cotizaciones
- create($purchaseRequestId)  // Formulario nueva cotización
- store()                     // Guarda cotización
- show($id)                   // Muestra cotización
- destroy($id)                // Elimina cotización
- select($id)                 // Selecciona cotización
- sendPreApprovalEmail()      // Envía a pre-aprobación
- markCompleted()             // Marca como completado
- cancelForDescription()      // Anula por falta de info
```

### 3. **PurchaseOrdersController**
```php
- index()                     // Lista órdenes
- create($purchaseRequestId)  // Formulario nueva orden
- store()                     // Guarda orden
- show($id)                   // Muestra orden
- approve($id)                // Aprueba orden
- generatePDF($id)            // Genera PDF
- sendToAccounting($id)       // Envía a contabilidad
- registerPayment($id)        // Registra pago
- createFromQuotation()       // Orden desde cotización
- createForProvider()         // Orden para proveedor (mixta)
- createNoQuotationPurchase() // Orden manual sin cotización
- showCreateNoQuotationPurchase() // Formulario orden manual
```

---

## 🌐 RUTAS PRINCIPALES

```php
// SOLICITUDES
Route::resource('purchase-requests', PurchaseRequestsController::class);
Route::post('purchase-requests/{id}/approve', [PurchaseRequestsController::class, 'approve'])->name('purchase-requests.approve');
Route::post('purchase-requests/{id}/reject', [PurchaseRequestsController::class, 'reject'])->name('purchase-requests.reject');
Route::post('purchase-requests/{id}/mark-delivery', [PurchaseRequestsController::class, 'markDelivery'])->name('purchase-requests.mark-delivery');
Route::post('purchase-requests/{id}/hecho-cumplido', [PurchaseRequestsController::class, 'markHechoCumplido'])->name('purchase-requests.hecho-cumplido');

// COTIZACIONES
Route::get('quotations/purchase-request/{purchaseRequest}', [QuotationController::class, 'index'])->name('quotations.index');
Route::get('quotations/create/{purchaseRequest}', [QuotationController::class, 'create'])->name('quotations.create');
Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
Route::post('quotations/select/{quotation}', [QuotationController::class, 'select'])->name('quotations.select');
Route::post('quotations/send-preapproval/{purchaseRequest}', [QuotationController::class, 'sendPreApprovalEmail'])->name('quotations.send-preapproval-email');
Route::get('quotations/mark-completed/{purchaseRequest}', [QuotationController::class, 'markCompleted'])->name('quotations.mark-completed');

// ÓRDENES DE COMPRA
Route::resource('purchase-orders', PurchaseOrdersController::class);
Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrdersController::class, 'approve'])->name('purchase-orders.approve');
Route::get('purchase-orders/{purchaseOrder}/pdf', [PurchaseOrdersController::class, 'generatePDF'])->name('purchase-orders.pdf');
Route::post('purchase-orders/{purchaseOrder}/send-to-accounting', [PurchaseOrdersController::class, 'sendToAccounting'])->name('purchase-orders.send-to-accounting');
Route::post('purchase-orders/{purchaseOrder}/register-payment', [PurchaseOrdersController::class, 'registerPayment'])->name('purchase-orders.register-payment');
Route::get('purchase-orders/create-no-quotation/{purchaseRequest}', [PurchaseOrdersController::class, 'showCreateNoQuotationPurchase'])->name('purchase-orders.show-create-no-quotation-purchase');
Route::post('purchase-orders/create-no-quotation/{purchaseRequest}', [PurchaseOrdersController::class, 'createNoQuotationPurchase'])->name('purchase-orders.create-no-quotation-purchase');

// SELECCIÓN MIXTA
Route::post('quotation-item-selections/save/{purchaseRequest}', [QuotationItemSelectionController::class, 'saveSelections'])->name('quotation-item-selections.save');
Route::post('purchase-orders/create-for-provider/{purchaseRequest}', [PurchaseOrdersController::class, 'createForProvider'])->name('purchase-orders.create-for-provider');
```

---

## 💾 MIGRACIONES IMPORTANTES

```php
// Tabla: purchase_requests
Schema::create('purchase_requests', function (Blueprint $table) {
    $table->id();
    $table->string('request_number')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['purchase', 'services', 'materials']);
    $table->string('status')->default('pending');
    // ... más campos
    $table->timestamps();
    $table->softDeletes();
});

// Tabla: quotations
Schema::create('quotations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
    $table->string('quotation_number')->unique();
    // ... más campos
    $table->timestamps();
    $table->softDeletes();
});

// Tabla: purchase_orders
Schema::create('purchase_orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
    // ... más campos
    $table->timestamps();
    $table->softDeletes();
});

// Tabla: quotation_item_selections (selección mixta)
Schema::create('quotation_item_selections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
    $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
    $table->integer('item_index');
    // ... más campos
    $table->timestamps();
});

// Tabla: proveedors
Schema::create('proveedors', function (Blueprint $table) {
    $table->id();
    $table->string('nit')->unique();
    $table->string('nombre');
    // ... más campos
    $table->timestamps();
});

// Tabla: request_histories
Schema::create('request_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('action');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

## 🎨 VISTAS PRINCIPALES

### Solicitudes:
- `resources/views/purchase-requests/index.blade.php` - Listado
- `resources/views/purchase-requests/create.blade.php` - Crear
- `resources/views/purchase-requests/show.blade.php` - Ver detalles
- `resources/views/purchase-requests/edit.blade.php` - Editar

### Cotizaciones:
- `resources/views/purchases/quotations/index.blade.php` - Listado
- `resources/views/purchases/quotations/create.blade.php` - Crear
- `resources/views/purchases/quotations/show.blade.php` - Ver

### Órdenes:
- `resources/views/purchase-orders/index.blade.php` - Listado
- `resources/views/purchase-orders/create.blade.php` - Crear
- `resources/views/purchase-orders/show.blade.php` - Ver
- `resources/views/purchase-orders/no-quotation-purchase.blade.php` - Crear manual
- `resources/views/purchase-orders/pdf-template-fixed.blade.php` - Plantilla PDF

### Emails:
- `resources/views/emails/purchase-request-created-compras.blade.php`
- `resources/views/emails/purchases/order-approved.blade.php`
- `resources/views/emails/purchases/order-for-review.blade.php`

---

## 📱 INTEGRACIONES

### 1. **Email (SMTP)**
- Laravel Mail
- Notificaciones automáticas
- Adjuntos (PDFs, cotizaciones)
- Colas para envío asíncrono

### 2. **Almacenamiento**
- Laravel Storage
- Filesystem local
- Links simbólicos públicos
- Organización por carpetas

### 3. **PDF**
- Librería: DomPDF o similar
- Generación dinámica
- Plantillas personalizadas
- Descarga/Visualización inline

### 4. **DataTables**
- jQuery DataTables
- Filtros avanzados
- Paginación
- Exportación a Excel/PDF

---

## 🔐 SEGURIDAD

### 1. **Autenticación**
- Laravel Sanctum/Fortify
- Sesiones seguras
- CSRF protection

### 2. **Autorización**
- Spatie Laravel Permission
- Roles y permisos
- Gates y Policies
- Middleware de protección

### 3. **Validación**
- Form Requests
- Validación en backend
- Validación en frontend
- Mensajes de error personalizados

### 4. **Auditoría**
- RequestHistory para todos los cambios
- Log de acciones
- Timestamps automáticos
- Soft deletes

---

## 📌 CONFIGURACIONES IMPORTANTES

### 1. **Número de cotizaciones requeridas**
- Por defecto: 3 cotizaciones
- Configurable por solicitud
- Campo: `required_quotations`
- Puede permitir continuar antes: `can_proceed_early`

### 2. **Emails de notificación**
- Configurados en `.env`
- Múltiples destinatarios
- Templates personalizables

### 3. **Centros de costo**
- Configurados en base de datos
- Validación de presupuesto
- Control por sección

### 4. **Impuestos**
- IVA: 19% (por defecto)
- Configurable por item
- Otros impuestos adicionales opcionales

---

## 🐛 CARACTERÍSTICAS DE DEBUGGING

### 1. **Logs**
```php
Log::info('Creando orden de compra', ['purchase_request_id' => $id]);
Log::error('Error al generar PDF', ['error' => $e->getMessage()]);
```

### 2. **Historial de acciones**
Cada cambio se registra en `request_histories`

### 3. **Soft Deletes**
Todos los registros eliminados se pueden recuperar

### 4. **Timestamps**
Rastrea cuándo se creó/actualizó cada registro

---

## 📝 VALIDACIONES IMPORTANTES

### 1. **Al crear solicitud:**
- Usuario debe estar autenticado
- Campos requeridos según tipo
- Presupuesto válido para centro de costo
- Fechas válidas

### 2. **Al agregar cotización:**
- Solicitud debe estar en estado apropiado
- PDF es obligatorio
- Items deben coincidir con solicitud
- Proveedor debe tener datos completos

### 3. **Al crear orden:**
- Solicitud debe estar aprobada
- No debe existir orden previa
- Debe tener cotización seleccionada O ser sin cotización autorizada
- Datos de proveedor completos

### 4. **Al aprobar:**
- Usuario debe tener permisos
- Estado debe permitir aprobación
- Validaciones de presupuesto

---

## 🚀 PRÓXIMAS MEJORAS / ROADMAP

- [ ] Integración con sistema contable
- [ ] Firma digital electrónica
- [ ] Portal de proveedores
- [ ] Dashboard de analíticas
- [ ] API REST
- [ ] App móvil
- [ ] OCR para facturas
- [ ] Integración con bancos

---

**Documento generado:** 18 de octubre de 2025  
**Versión del sistema:** Laravel 10+  
**Autor:** GitHub Copilot
