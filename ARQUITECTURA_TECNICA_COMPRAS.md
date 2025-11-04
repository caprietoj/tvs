# 🏗️ ARQUITECTURA TÉCNICA - MÓDULO DE COMPRAS

## 📐 ARQUITECTURA DEL SISTEMA

### Patrón de Diseño: MVC (Model-View-Controller)

```
┌─────────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA GENERAL                          │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐     HTTP      ┌──────────────┐
│   Browser    │ ◄────────────► │   Routes     │
│  (Cliente)   │    Request     │   (web.php)  │
└──────────────┘                └──────┬───────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │  Controllers    │
                              │  - Purchase     │
                              │  - Quotation    │
                              │  - Order        │
                              └────────┬────────┘
                                       │
                    ┌──────────────────┼──────────────────┐
                    ▼                  ▼                  ▼
            ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
            │   Models     │  │  Services    │  │  Observers   │
            │  - Request   │  │  - Email     │  │  - History   │
            │  - Quotation │  │  - PDF       │  │  - Budget    │
            │  - Order     │  │  - Classifier│  │              │
            └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
                   │                 │                 │
                   └─────────────────┼─────────────────┘
                                     ▼
                            ┌─────────────────┐
                            │    Database     │
                            │    (MySQL)      │
                            └─────────────────┘
```

---

## 🗄️ ESQUEMA DE BASE DE DATOS DETALLADO

### Diagrama ER (Entity-Relationship)

```
┌─────────────────────────────────────────────────────────────────┐
│                  DIAGRAMA ENTIDAD-RELACIÓN                       │
└─────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │    users     │
    │──────────────│
    │ id (PK)      │
    │ name         │
    │ email        │
    │ password     │
    └──────┬───────┘
           │
           │ 1:N (crea)
           │
           ▼
    ┌──────────────────────┐
    │ purchase_requests    │ 1:N (tiene)
    │──────────────────────├──────────────┐
    │ id (PK)              │              │
    │ request_number       │              ▼
    │ user_id (FK)         │      ┌──────────────────┐
    │ type                 │      │   quotations     │
    │ status               │      │──────────────────│
    │ purchase_items       │      │ id (PK)          │
    │ service_items        │      │ purchase_req(FK) │
    │ material_items       │      │ quotation_number │
    │ copy_items           │      │ provider_name    │
    │ selected_quotation_id│◄─────│ provider_nit     │
    │ budget               │      │ subtotal         │
    │ approval_date        │      │ tax_amount       │
    │ delivery_status      │      │ total_amount     │
    └───────┬──────────────┘      │ items (JSON)     │
            │                     │ file_path        │
            │                     └──────────────────┘
            │
            │ 1:N (genera)
            │
            ▼
    ┌──────────────────────┐
    │  purchase_orders     │
    │──────────────────────│
    │ id (PK)              │
    │ order_number         │
    │ purchase_req_id (FK) │
    │ provider_id (FK)     │──┐
    │ subtotal             │  │
    │ tax_amount           │  │ N:1
    │ total_amount         │  │
    │ items (JSON)         │  │
    │ status               │  │
    │ file_path            │  │
    │ approved_at          │  │
    │ payment_date         │  │
    └──────────────────────┘  │
                              │
                              ▼
                       ┌──────────────┐
                       │  proveedors  │
                       │──────────────│
                       │ id (PK)      │
                       │ nit (UNIQUE) │
                       │ nombre       │
                       │ direccion    │
                       │ telefono     │
                       │ email        │
                       │ activo       │
                       └──────────────┘

    ┌──────────────────────────────────┐
    │ quotation_item_selections        │
    │──────────────────────────────────│
    │ id (PK)                          │
    │ purchase_request_id (FK)         │
    │ quotation_id (FK)                │
    │ item_index                       │
    │ item_description                 │
    │ quantity                         │
    │ unit_price                       │
    │ total_price                      │
    │ selected_by (FK → users)         │
    └──────────────────────────────────┘

    ┌──────────────────────────────────┐
    │      request_histories           │
    │──────────────────────────────────│
    │ id (PK)                          │
    │ purchase_request_id (FK)         │
    │ user_id (FK)                     │
    │ action                           │
    │ notes                            │
    │ created_at                       │
    └──────────────────────────────────┘
```

---

## 🔄 FLUJO DE DATOS DETALLADO

### 1. Flujo de Creación de Solicitud

```
┌─────────────────────────────────────────────────────────────────┐
│           FLUJO: CREACIÓN DE SOLICITUD DE COMPRA                 │
└─────────────────────────────────────────────────────────────────┘

Usuario                Controller              Model              Database
  │                         │                    │                    │
  │  1. GET /purchase-      │                    │                    │
  │     requests/create     │                    │                    │
  ├────────────────────────►│                    │                    │
  │                         │                    │                    │
  │  2. Renderiza formulario│                    │                    │
  │◄────────────────────────┤                    │                    │
  │                         │                    │                    │
  │  3. POST /purchase-     │                    │                    │
  │     requests (datos)    │                    │                    │
  ├────────────────────────►│                    │                    │
  │                         │                    │                    │
  │                         │ 4. Validar datos   │                    │
  │                         ├───────────────────►│                    │
  │                         │                    │                    │
  │                         │ 5. Generar request_│                    │
  │                         │    number (SC-XXXX)│                    │
  │                         │◄───────────────────┤                    │
  │                         │                    │                    │
  │                         │                    │ 6. INSERT INTO    │
  │                         │                    │    purchase_requests│
  │                         │                    ├───────────────────►│
  │                         │                    │                    │
  │                         │                    │ 7. created_at =   │
  │                         │                    │    NOW()          │
  │                         │                    │◄───────────────────┤
  │                         │                    │                    │
  │                         │ 8. Crear historial │                    │
  │                         │    "Solicitud      │                    │
  │                         │    creada"         │                    │
  │                         │                    ├───────────────────►│
  │                         │                    │                    │
  │                         │ 9. Enviar email a  │                    │
  │                         │    Compras         │                    │
  │                         │    (Queue job)     │                    │
  │                         ├────────────────────┘                    │
  │                         │                                         │
  │  10. Redirect a show    │                                         │
  │◄────────────────────────┤                                         │
  │                         │                                         │
  │  11. Flash message:     │                                         │
  │      "Solicitud creada" │                                         │
  │◄────────────────────────┘                                         │
```

### 2. Flujo de Cotización

```
┌─────────────────────────────────────────────────────────────────┐
│              FLUJO: AGREGAR COTIZACIÓN                           │
└─────────────────────────────────────────────────────────────────┘

Compras              Controller            Model              Database
  │                      │                   │                    │
  │  1. GET /quotations/ │                   │                    │
  │     create/{id}      │                   │                    │
  ├─────────────────────►│                   │                    │
  │                      │                   │                    │
  │                      │ 2. Verificar que  │                    │
  │                      │    request esté en│                    │
  │                      │    estado válido  │                    │
  │                      ├──────────────────►│                    │
  │                      │                   │                    │
  │  3. Formulario de    │                   │                    │
  │     cotización       │                   │                    │
  │◄─────────────────────┤                   │                    │
  │                      │                   │                    │
  │  4. POST /quotations │                   │                    │
  │     (datos + PDF)    │                   │                    │
  ├─────────────────────►│                   │                    │
  │                      │                   │                    │
  │                      │ 5. Validar:       │                    │
  │                      │    - Datos completos│                  │
  │                      │    - PDF presente │                    │
  │                      │    - Items válidos│                    │
  │                      ├──────────────────►│                    │
  │                      │                   │                    │
  │                      │ 6. Guardar PDF en │                    │
  │                      │    storage/       │                    │
  │                      │    quotations/    │                    │
  │                      │                   │                    │
  │                      │                   │ 7. INSERT quotation│
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │                      │ 8. Actualizar     │                    │
  │                      │    request.status │                    │
  │                      │    = "En Cotización"│                  │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │                      │ 9. Historial:     │                    │
  │                      │    "Cotización    │                    │
  │                      │    #X agregada"   │                    │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │                      │10. Verificar si   │                    │
  │                      │   tiene 3 cotiza- │                    │
  │                      │   ciones → enviar │                    │
  │                      │   email pre-aprob.│                    │
  │                      │                   │                    │
  │  11. Redirect +      │                   │                    │
  │      mensaje éxito   │                   │                    │
  │◄─────────────────────┘                   │                    │
```

### 3. Flujo de Pre-aprobación (Selección de Cotización)

```
┌─────────────────────────────────────────────────────────────────┐
│         FLUJO: PRE-APROBACIÓN Y SELECCIÓN DE COTIZACIÓN          │
└─────────────────────────────────────────────────────────────────┘

Supervisor           Controller            Model              Database
  │                      │                   │                    │
  │  1. GET /purchase-   │                   │                    │
  │     requests/{id}    │                   │                    │
  │     (ver cotizaciones)│                  │                    │
  ├─────────────────────►│                   │                    │
  │                      │                   │                    │
  │                      │ 2. Cargar request │                    │
  │                      │    con quotations │                    │
  │                      ├──────────────────►│                    │
  │                      │                   │ 3. SELECT * FROM  │
  │                      │                   │    quotations     │
  │                      │                   │    WHERE request=id│
  │                      │                   ├───────────────────►│
  │                      │                   │◄───────────────────┤
  │                      │                   │                    │
  │  4. Vista con tabla  │                   │                    │
  │     comparativa de   │                   │                    │
  │     cotizaciones     │                   │                    │
  │◄─────────────────────┤                   │                    │
  │                      │                   │                    │
  │  OPCIÓN A: Selección Simple              │                    │
  │  ─────────────────────────────────────────                    │
  │  5. POST /quotations/│                   │                    │
  │     select/{quot_id} │                   │                    │
  ├─────────────────────►│                   │                    │
  │                      │                   │                    │
  │                      │ 6. UPDATE request │                    │
  │                      │    SET selected_  │                    │
  │                      │    quotation_id   │                    │
  │                      │    = {id}         │                    │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │  OPCIÓN B: Selección Mixta               │                    │
  │  ─────────────────────────────────────────                    │
  │  7. POST /quotation- │                   │                    │
  │     item-selections/ │                   │                    │
  │     save             │                   │                    │
  │     (items array)    │                   │                    │
  ├─────────────────────►│                   │                    │
  │                      │                   │                    │
  │                      │ 8. Para cada item:│                    │
  │                      │    INSERT INTO    │                    │
  │                      │    quotation_item_│                    │
  │                      │    selections     │                    │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │  COMÚN PARA AMBAS OPCIONES               │                    │
  │  ─────────────────────────────────────────                    │
  │                      │ 9. UPDATE request │                    │
  │                      │    SET status =   │                    │
  │                      │    "En pre-aprob."│                    │
  │                      │    pre_approved_by│                    │
  │                      │    pre_approved_at│                    │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │                      │10. Historial:     │                    │
  │                      │   "Pre-aprobada"  │                    │
  │                      │                   ├───────────────────►│
  │                      │                   │                    │
  │                      │11. Email a Admin  │                    │
  │                      │   para aprobación │                    │
  │                      │   final           │                    │
  │                      │                   │                    │
  │  12. Redirect + msg  │                   │                    │
  │◄─────────────────────┘                   │                    │
```

### 4. Flujo de Creación de Orden de Compra

```
┌─────────────────────────────────────────────────────────────────┐
│           FLUJO: CREACIÓN DE ORDEN DE COMPRA                     │
└─────────────────────────────────────────────────────────────────┘

Admin                Controller            Service            Database
  │                      │                     │                  │
  │  1. POST /purchase-  │                     │                  │
  │     requests/{id}/   │                     │                  │
  │     approve          │                     │                  │
  ├─────────────────────►│                     │                  │
  │                      │                     │                  │
  │                      │ 2. UPDATE request   │                  │
  │                      │    SET status =     │                  │
  │                      │    "approved"       │                  │
  │                      │    approval_date    │                  │
  │                      │    approved_by      │                  │
  │                      ├────────────────────────────────────────►│
  │                      │                     │                  │
  │                      │ 3. Verificar si hay │                  │
  │                      │    cotización       │                  │
  │                      │    seleccionada     │                  │
  │                      ├────────────────────►│                  │
  │                      │                     │                  │
  │                      │ CASO A: CON COTIZACIÓN SIMPLE           │
  │                      │ ─────────────────────────────────────   │
  │                      │ 4. Obtener datos de │                  │
  │                      │    cotización       │                  │
  │                      │    seleccionada     │                  │
  │                      │                     ├─────────────────►│
  │                      │                     │◄─────────────────┤
  │                      │                     │                  │
  │                      │ 5. Crear 1 orden    │                  │
  │                      │    con datos de     │                  │
  │                      │    la cotización    │                  │
  │                      │                     ├─────────────────►│
  │                      │                     │                  │
  │                      │ CASO B: CON SELECCIÓN MIXTA            │
  │                      │ ─────────────────────────────────────  │
  │                      │ 6. Obtener items    │                  │
  │                      │    seleccionados    │                  │
  │                      │    agrupados por    │                  │
  │                      │    proveedor        │                  │
  │                      │                     ├─────────────────►│
  │                      │                     │◄─────────────────┤
  │                      │                     │                  │
  │                      │ 7. Para cada        │                  │
  │                      │    proveedor:       │                  │
  │                      │    crear orden      │                  │
  │                      │                     ├─────────────────►│
  │                      │                     │ INSERT orden 1   │
  │                      │                     ├─────────────────►│
  │                      │                     │ INSERT orden 2   │
  │                      │                     ├─────────────────►│
  │                      │                     │ INSERT orden N   │
  │                      │                     │                  │
  │                      │ COMÚN PARA TODOS LOS CASOS             │
  │                      │ ─────────────────────────────────────  │
  │                      │ 8. Generar número   │                  │
  │                      │    de orden         │                  │
  │                      │    OC-XXXX          │                  │
  │                      │                     │                  │
  │                      │ 9. Generar PDF      │                  │
  │                      │    (PdfService)     │                  │
  │                      ├────────────────────►│                  │
  │                      │                     │                  │
  │                      │10. Guardar PDF en   │                  │
  │                      │   storage/orders/   │                  │
  │                      │◄────────────────────┤                  │
  │                      │                     │                  │
  │                      │11. UPDATE order     │                  │
  │                      │    SET file_path    │                  │
  │                      ├────────────────────────────────────────►│
  │                      │                     │                  │
  │                      │12. Historial:       │                  │
  │                      │   "Orden OC-XXX     │                  │
  │                      │    creada"          │                  │
  │                      ├────────────────────────────────────────►│
  │                      │                     │                  │
  │                      │13. Email a Admin    │                  │
  │                      │   con PDF para      │                  │
  │                      │   revisión          │                  │
  │                      │                     │                  │
  │  14. Redirect a      │                     │                  │
  │      order/show      │                     │                  │
  │◄─────────────────────┘                     │                  │
```

---

## 🔧 LÓGICA DE NEGOCIO DETALLADA

### 1. Generación de Números de Solicitud

```php
/**
 * Algoritmo de generación de número de solicitud
 * Formato: {PREFIJO}-{NÚMERO}
 * Ejemplos: SC-0001, SS-0023, SM-0156
 */

function generateRequestNumber($type) {
    // 1. Determinar prefijo según tipo
    $prefix = match($type) {
        'purchase' => 'SC',  // Solicitud de Compra
        'services' => 'SS',  // Solicitud de Servicio
        'materials' => 'SM', // Solicitud de Material
        default => 'SM'
    };
    
    // 2. Buscar el último número usado para este tipo
    $lastRequest = DB::table('purchase_requests')
        ->where('type', $type)
        ->where('request_number', 'LIKE', $prefix . '-%')
        ->orderBy('request_number', 'desc')
        ->first();
    
    // 3. Extraer el número y sumar 1
    if ($lastRequest) {
        $lastNumber = intval(substr($lastRequest->request_number, 3));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    
    // 4. Formatear con padding de 4 dígitos
    $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // 5. Retornar número completo
    return $prefix . '-' . $formattedNumber;
    
    // Resultado: SC-0001, SC-0002, ..., SC-9999
}
```

### 2. Cálculo de Totales con Impuestos

```php
/**
 * Algoritmo de cálculo de totales
 * Soporta múltiples impuestos por item
 */

function calculateOrderTotals($items, $globalTaxes = []) {
    $subtotal = 0;
    $taxAmounts = [
        'iva_19' => 0,
        'iva_5' => 0,
        'consumo_8' => 0,
        'consumo_4' => 0
    ];
    
    foreach ($items as $item) {
        // 1. Calcular subtotal del item
        $itemSubtotal = $item['quantity'] * $item['unit_price'];
        $subtotal += $itemSubtotal;
        
        // 2. Determinar impuestos aplicables
        $appliedTaxes = $item['applied_taxes'] ?? $globalTaxes;
        
        // 3. Calcular cada tipo de impuesto
        foreach ($appliedTaxes as $tax) {
            $taxRate = match($tax) {
                'iva_19' => 0.19,
                'iva_5' => 0.05,
                'consumo_8' => 0.08,
                'consumo_4' => 0.04,
                default => 0
            };
            
            $taxAmount = $itemSubtotal * $taxRate;
            $taxAmounts[$tax] += $taxAmount;
        }
    }
    
    // 4. Calcular total de impuestos
    $totalTax = array_sum($taxAmounts);
    
    // 5. Calcular total general
    $total = $subtotal + $totalTax;
    
    return [
        'subtotal' => $subtotal,
        'tax_amounts' => $taxAmounts,
        'total_tax' => $totalTax,
        'total' => $total,
        'breakdown' => [
            'subtotal' => number_format($subtotal, 2),
            'iva_19' => number_format($taxAmounts['iva_19'], 2),
            'iva_5' => number_format($taxAmounts['iva_5'], 2),
            'consumo_8' => number_format($taxAmounts['consumo_8'], 2),
            'consumo_4' => number_format($taxAmounts['consumo_4'], 2),
            'total' => number_format($total, 2)
        ]
    ];
}

/**
 * Ejemplo de uso:
 */
$items = [
    [
        'description' => 'Laptop',
        'quantity' => 2,
        'unit_price' => 1500000,
        'applied_taxes' => ['iva_19']
    ],
    [
        'description' => 'Comida',
        'quantity' => 100,
        'unit_price' => 5000,
        'applied_taxes' => ['iva_5', 'consumo_8']
    ]
];

$result = calculateOrderTotals($items);
/*
Resultado:
[
    'subtotal' => 3500000,
    'tax_amounts' => [
        'iva_19' => 570000,    // 19% de 3,000,000
        'iva_5' => 25000,      // 5% de 500,000
        'consumo_8' => 40000,  // 8% de 500,000
        'consumo_4' => 0
    ],
    'total_tax' => 635000,
    'total' => 4135000
]
*/
```

### 3. Validación de Presupuesto

```php
/**
 * Algoritmo de validación de presupuesto
 * Verifica que la sección tenga presupuesto disponible
 */

function validateBudget($section, $requestedAmount) {
    // 1. Obtener presupuesto total asignado
    $totalBudget = DB::table('budgets')
        ->where('section', $section)
        ->where('year', date('Y'))
        ->value('total_amount');
    
    if (!$totalBudget) {
        return [
            'valid' => false,
            'message' => 'No hay presupuesto asignado para esta sección'
        ];
    }
    
    // 2. Calcular presupuesto ya comprometido
    $committedBudget = DB::table('purchase_requests')
        ->where('section_area', $section)
        ->whereIn('status', ['approved', 'in_process'])
        ->whereYear('created_at', date('Y'))
        ->sum('budget');
    
    // 3. Calcular presupuesto disponible
    $availableBudget = $totalBudget - $committedBudget;
    
    // 4. Verificar si hay suficiente presupuesto
    if ($requestedAmount > $availableBudget) {
        return [
            'valid' => false,
            'message' => "Presupuesto insuficiente. Disponible: $" . 
                         number_format($availableBudget, 2) .
                         ", Solicitado: $" . 
                         number_format($requestedAmount, 2),
            'available' => $availableBudget,
            'requested' => $requestedAmount,
            'deficit' => $requestedAmount - $availableBudget
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Presupuesto disponible',
        'available' => $availableBudget,
        'requested' => $requestedAmount,
        'remaining' => $availableBudget - $requestedAmount
    ];
}
```

### 4. Selección Mixta de Cotizaciones

```php
/**
 * Algoritmo para agrupar items seleccionados por proveedor
 * y crear órdenes de compra separadas
 */

function processeMixedSelection($purchaseRequestId) {
    // 1. Obtener todas las selecciones de items
    $selections = DB::table('quotation_item_selections')
        ->where('purchase_request_id', $purchaseRequestId)
        ->get();
    
    // 2. Agrupar por cotización (proveedor)
    $groupedByQuotation = [];
    foreach ($selections as $selection) {
        $quotationId = $selection->quotation_id;
        
        if (!isset($groupedByQuotation[$quotationId])) {
            $groupedByQuotation[$quotationId] = [
                'quotation' => getQuotation($quotationId),
                'items' => []
            ];
        }
        
        $groupedByQuotation[$quotationId]['items'][] = [
            'description' => $selection->item_description,
            'quantity' => $selection->quantity,
            'unit_price' => $selection->unit_price,
            'total' => $selection->total_price
        ];
    }
    
    // 3. Crear una orden de compra por cada proveedor
    $createdOrders = [];
    foreach ($groupedByQuotation as $quotationId => $data) {
        $quotation = $data['quotation'];
        $items = $data['items'];
        
        // 3.1 Calcular totales
        $totals = calculateOrderTotals($items);
        
        // 3.2 Crear orden
        $order = [
            'order_number' => generateOrderNumber(),
            'purchase_request_id' => $purchaseRequestId,
            'provider_id' => $quotation->provider_id,
            'provider_name' => $quotation->provider_name,
            'provider_nit' => $quotation->provider_nit,
            'items' => json_encode($items),
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['total_tax'],
            'total_amount' => $totals['total'],
            'status' => 'pending',
            'created_at' => now()
        ];
        
        $orderId = DB::table('purchase_orders')->insertGetId($order);
        
        // 3.3 Generar PDF
        generateOrderPDF($orderId);
        
        $createdOrders[] = $orderId;
    }
    
    return [
        'success' => true,
        'orders_created' => count($createdOrders),
        'order_ids' => $createdOrders
    ];
}
```

### 5. Sistema de Notificaciones por Email

```php
/**
 * Servicio de clasificación de emails según sección
 * Determina a quién enviar notificaciones
 */

class SectionClassifierService {
    
    /**
     * Obtiene el email del director/coordinador según la sección
     */
    public function getDirectorEmail($section) {
        // Mapeo de secciones a coordinadores
        $sectionMapping = [
            // Preescolar
            'Preescolar' => 'preescolar@tvs.edu.co',
            'Prejardín' => 'preescolar@tvs.edu.co',
            'Jardín' => 'preescolar@tvs.edu.co',
            'Transición' => 'preescolar@tvs.edu.co',
            
            // Primaria
            'Primero' => 'primaria@tvs.edu.co',
            'Segundo' => 'primaria@tvs.edu.co',
            'Tercero' => 'primaria@tvs.edu.co',
            'Cuarto' => 'primaria@tvs.edu.co',
            'Quinto' => 'primaria@tvs.edu.co',
            
            // Bachillerato
            'Sexto' => 'bachillerato@tvs.edu.co',
            'Séptimo' => 'bachillerato@tvs.edu.co',
            'Octavo' => 'bachillerato@tvs.edu.co',
            'Noveno' => 'bachillerato@tvs.edu.co',
            'Décimo' => 'bachillerato@tvs.edu.co',
            'Once' => 'bachillerato@tvs.edu.co',
            
            // Administrativo
            'Rectoría' => 'rector@tvs.edu.co',
            'Administración' => 'admin@tvs.edu.co',
            'Contabilidad' => 'contabilidad@tvs.edu.co',
        ];
        
        // Buscar email correspondiente
        foreach ($sectionMapping as $key => $email) {
            if (stripos($section, $key) !== false) {
                return $email;
            }
        }
        
        // Default: rector
        return 'rector@tvs.edu.co';
    }
    
    /**
     * Determina el flujo de aprobación según monto
     */
    public function getApprovalFlow($amount) {
        if ($amount < 1000000) {
            // Menos de 1 millón: solo coordinador
            return [
                'requires_director' => true,
                'requires_admin' => false,
                'requires_rector' => false
            ];
        } elseif ($amount < 5000000) {
            // Entre 1 y 5 millones: coordinador + admin
            return [
                'requires_director' => true,
                'requires_admin' => true,
                'requires_rector' => false
            ];
        } else {
            // Más de 5 millones: coordinador + admin + rector
            return [
                'requires_director' => true,
                'requires_admin' => true,
                'requires_rector' => true
            ];
        }
    }
}
```

---

## 📨 ESTRUCTURA DE NOTIFICACIONES

### Notificación: Nueva Solicitud Creada

```php
/**
 * Email enviado a Personal de Compras cuando se crea una solicitud
 */

class NewPurchaseRequestNotification {
    
    public function toMail($purchaseRequest) {
        $type = match($purchaseRequest->type) {
            'purchase' => 'Compra',
            'services' => 'Servicio',
            'materials' => 'Material'
        };
        
        return [
            'subject' => "Nueva Solicitud de {$type} - {$purchaseRequest->request_number}",
            'greeting' => "Hola, Personal de Compras",
            'intro' => [
                "Se ha creado una nueva solicitud de {$type}.",
                "Detalles de la solicitud:"
            ],
            'lines' => [
                "**Número:** {$purchaseRequest->request_number}",
                "**Solicitante:** {$purchaseRequest->requester}",
                "**Sección:** {$purchaseRequest->section_area}",
                "**Fecha:** {$purchaseRequest->created_at->format('d/m/Y H:i')}",
                "**Presupuesto:** $" . number_format($purchaseRequest->budget, 2)
            ],
            'action' => [
                'text' => 'Ver Solicitud',
                'url' => route('purchase-requests.show', $purchaseRequest->id)
            ],
            'outro' => "Por favor, proceda a solicitar cotizaciones a los proveedores."
        ];
    }
}
```

### Notificación: Cotizaciones Completadas

```php
/**
 * Email enviado a Supervisor/Coordinador para pre-aprobar
 */

class QuotationsCompletedNotification {
    
    public function toMail($purchaseRequest) {
        $quotationsCount = $purchaseRequest->quotations->count();
        
        return [
            'subject' => "Cotizaciones Completadas - {$purchaseRequest->request_number}",
            'greeting' => "Hola, " . $this->getCoordinatorName($purchaseRequest->section_area),
            'intro' => [
                "Las cotizaciones para la solicitud {$purchaseRequest->request_number} están listas para revisión.",
                "Se han recibido {$quotationsCount} cotizaciones:"
            ],
            'table' => [
                'headers' => ['Proveedor', 'Total'],
                'rows' => $purchaseRequest->quotations->map(function($q) {
                    return [
                        $q->provider_name,
                        '$' . number_format($q->total_amount, 2)
                    ];
                })
            ],
            'action' => [
                'text' => 'Revisar y Seleccionar Cotización',
                'url' => route('purchase-requests.show', $purchaseRequest->id)
            ],
            'outro' => "Por favor, revise las cotizaciones y seleccione la más conveniente."
        ];
    }
}
```

---

## 🎨 COMPONENTES FRONTEND

### Estructura de Formulario de Solicitud

```html
<!-- Formulario dinámico según tipo de solicitud -->
<form id="purchaseRequestForm" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- Sección 1: Información General -->
    <div class="card">
        <div class="card-header">Información General</div>
        <div class="card-body">
            <!-- Tipo de Solicitud -->
            <div class="form-group">
                <label>Tipo de Solicitud</label>
                <select name="type" id="requestType" required>
                    <option value="">Seleccione...</option>
                    <option value="purchase">Compra</option>
                    <option value="services">Servicio</option>
                    <option value="materials">Material</option>
                </select>
            </div>
            
            <!-- Solicitante -->
            <div class="form-group">
                <label>Solicitante</label>
                <input type="text" name="requester" value="{{ auth()->user()->name }}" readonly>
            </div>
            
            <!-- Sección/Área -->
            <div class="form-group">
                <label>Sección/Área/Departamento</label>
                <select name="section_area" required>
                    <option value="">Seleccione...</option>
                    <option value="Preescolar">Preescolar</option>
                    <option value="Primaria">Primaria</option>
                    <option value="Bachillerato">Bachillerato</option>
                    <option value="Administración">Administración</option>
                    <!-- ... más opciones -->
                </select>
            </div>
            
            <!-- Presupuesto -->
            <div class="form-group">
                <label>Presupuesto Estimado</label>
                <input type="number" name="budget" step="0.01" required>
            </div>
            
            <!-- Fecha de Entrega Requerida -->
            <div class="form-group">
                <label>Fecha de Entrega Requerida</label>
                <input type="date" name="delivery_date" required>
            </div>
        </div>
    </div>
    
    <!-- Sección 2: Items (Dinámico según tipo) -->
    <div class="card" id="itemsSection">
        <div class="card-header">
            Items Solicitados
            <button type="button" class="btn btn-sm btn-success float-right" onclick="addItem()">
                <i class="fas fa-plus"></i> Agregar Item
            </button>
        </div>
        <div class="card-body">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario Estimado</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <!-- Items se agregan dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Sección 3: Justificación -->
    <div class="card">
        <div class="card-header">Justificación</div>
        <div class="card-body">
            <textarea name="justification" rows="5" required></textarea>
        </div>
    </div>
    
    <!-- Sección 4: Archivos Adjuntos -->
    <div class="card">
        <div class="card-header">Archivos Adjuntos</div>
        <div class="card-body">
            <input type="file" name="attached_files[]" multiple>
        </div>
    </div>
    
    <!-- Botones -->
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Crear Solicitud</button>
        <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
// JavaScript para agregar items dinámicamente
let itemCount = 0;

function addItem() {
    itemCount++;
    const row = `
        <tr id="item-${itemCount}">
            <td>
                <input type="text" name="items[${itemCount}][description]" 
                       class="form-control" required>
            </td>
            <td>
                <input type="number" name="items[${itemCount}][quantity]" 
                       class="form-control item-quantity" 
                       min="1" value="1" required
                       onchange="calculateItemTotal(${itemCount})">
            </td>
            <td>
                <input type="number" name="items[${itemCount}][unit_price]" 
                       class="form-control item-price" 
                       step="0.01" min="0" required
                       onchange="calculateItemTotal(${itemCount})">
            </td>
            <td>
                <input type="number" name="items[${itemCount}][total]" 
                       class="form-control item-total" 
                       readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="removeItem(${itemCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    document.getElementById('itemsTableBody').insertAdjacentHTML('beforeend', row);
}

function removeItem(itemId) {
    document.getElementById(`item-${itemId}`).remove();
    calculateGrandTotal();
}

function calculateItemTotal(itemId) {
    const row = document.getElementById(`item-${itemId}`);
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.item-total').value = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    // Actualizar presupuesto estimado
    document.querySelector('[name="budget"]').value = grandTotal.toFixed(2);
}

// Inicializar con un item
document.addEventListener('DOMContentLoaded', function() {
    addItem();
});
</script>
```

---

## 🔐 SEGURIDAD Y VALIDACIONES

### Middleware de Autorización

```php
/**
 * Middleware para verificar permisos de acceso
 */

class CheckPurchasePermission {
    
    public function handle($request, Closure $next, $permission) {
        $user = auth()->user();
        
        // Verificar si el usuario tiene el rol requerido
        if (!$user->hasRole($permission) && !$user->hasRole('admin')) {
            abort(403, 'No tiene permisos para esta acción');
        }
        
        // Verificar si está accediendo a una solicitud propia
        if ($request->route('purchaseRequest')) {
            $purchaseRequest = $request->route('purchaseRequest');
            
            // El usuario solo puede ver sus propias solicitudes
            // (a menos que tenga rol admin o compras)
            if ($purchaseRequest->user_id !== $user->id &&
                !$user->hasRole('admin') &&
                !$user->hasRole('compras')) {
                abort(403, 'No puede acceder a esta solicitud');
            }
        }
        
        return $next($request);
    }
}
```

### Validaciones de Datos

```php
/**
 * Form Request para validar solicitud de compra
 */

class StorePurchaseRequestRequest extends FormRequest {
    
    public function authorize() {
        return auth()->check();
    }
    
    public function rules() {
        $rules = [
            'type' => 'required|in:purchase,services,materials',
            'requester' => 'required|string|max:255',
            'section_area' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'delivery_date' => 'required|date|after:today',
            'attached_files.*' => 'file|max:10240' // 10MB max
        ];
        
        // Validaciones específicas según tipo
        if ($this->type === 'purchase') {
            $rules['purchase_items'] = 'required|array|min:1';
            $rules['purchase_items.*.description'] = 'required|string';
            $rules['purchase_items.*.quantity'] = 'required|numeric|min:1';
            $rules['purchase_items.*.unit_price'] = 'required|numeric|min:0';
            $rules['purchase_justification'] = 'required|string';
        }
        
        if ($this->type === 'services') {
            $rules['service_items'] = 'required|array|min:1';
            $rules['service_justification'] = 'required|string';
        }
        
        if ($this->type === 'materials') {
            $rules['material_items'] = 'required_without:copy_items|array';
            $rules['copy_items'] = 'required_without:material_items|array';
        }
        
        return $rules;
    }
    
    public function messages() {
        return [
            'type.required' => 'Debe seleccionar un tipo de solicitud',
            'budget.required' => 'El presupuesto es obligatorio',
            'budget.min' => 'El presupuesto debe ser mayor a cero',
            'delivery_date.after' => 'La fecha de entrega debe ser posterior a hoy',
            'purchase_items.required' => 'Debe agregar al menos un item',
            'purchase_items.*.description.required' => 'Cada item debe tener una descripción',
            // ... más mensajes
        ];
    }
}
```

---

## 📊 REPORTES Y ESTADÍSTICAS

### Query para Reporte de Gastos por Sección

```sql
-- Reporte de gastos por sección en un período
SELECT 
    pr.section_area AS seccion,
    COUNT(pr.id) AS total_solicitudes,
    COUNT(CASE WHEN pr.status = 'approved' THEN 1 END) AS aprobadas,
    COUNT(CASE WHEN pr.status = 'rejected' THEN 1 END) AS rechazadas,
    SUM(pr.budget) AS presupuesto_solicitado,
    SUM(CASE WHEN pr.status = 'approved' THEN po.total_amount ELSE 0 END) AS monto_aprobado,
    AVG(DATEDIFF(pr.approval_date, pr.created_at)) AS dias_promedio_aprobacion
FROM 
    purchase_requests pr
LEFT JOIN 
    purchase_orders po ON pr.id = po.purchase_request_id
WHERE 
    pr.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY 
    pr.section_area
ORDER BY 
    monto_aprobado DESC;
```

### Query para Top Proveedores

```sql
-- Proveedores más utilizados
SELECT 
    p.nombre AS proveedor,
    p.nit,
    COUNT(po.id) AS ordenes_generadas,
    SUM(po.total_amount) AS monto_total,
    AVG(po.total_amount) AS monto_promedio,
    MAX(po.created_at) AS ultima_orden
FROM 
    proveedors p
INNER JOIN 
    purchase_orders po ON p.id = po.provider_id
WHERE 
    po.status IN ('approved', 'Pagado')
    AND po.created_at BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY 
    p.id, p.nombre, p.nit
HAVING 
    ordenes_generadas >= 3
ORDER BY 
    monto_total DESC
LIMIT 10;
```

---

**Continuará en la siguiente parte...**

