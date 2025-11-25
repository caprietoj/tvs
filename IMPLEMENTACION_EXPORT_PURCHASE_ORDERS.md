# Implementación de Exportación de Órdenes de Compra a Excel

## Fecha de Implementación
22 de Octubre, 2025

## Descripción
Se agregó funcionalidad para exportar órdenes de compra a formato Excel (.xlsx) desde la vista de índice de órdenes de compra.

## Archivos Creados

### 1. `app/Exports/PurchaseOrdersExport.php`
Clase de exportación que implementa las siguientes interfaces:
- `FromQuery`: Permite exportar datos desde una consulta Eloquent
- `WithHeadings`: Define los encabezados de las columnas
- `WithMapping`: Mapea los datos del modelo a formato de fila
- `WithStyles`: Aplica estilos a las celdas
- `WithColumnWidths`: Define el ancho de las columnas
- `WithTitle`: Define el título de la hoja
- `WithEvents`: Permite personalización avanzada después de la creación

**Columnas exportadas:**
1. **Número de Orden**: Número identificador de la orden
2. **Solicitud**: Número de la solicitud de compra relacionada
3. **Proveedor**: Nombre del proveedor
4. **Monto**: Valor total de la orden (formato: $X,XXX.XX)
5. **Fecha de Entrega**: Fecha esperada de entrega (formato: dd/mm/yyyy)
6. **Creado**: Fecha y hora de creación (formato: dd/mm/yyyy HH:mm)

**Características de la exportación:**
- Filtros aplicables (número de orden, solicitud, proveedor, rango de fechas)
- Estilos profesionales con encabezado en azul (#364E76)
- Bordes en todas las celdas
- Filas alternadas con fondo gris claro (#F8F9FA)
- Primera fila congelada para facilitar la navegación
- Alineación centrada para la mayoría de las columnas
- Alineación izquierda para nombres de proveedores

## Archivos Modificados

### 2. `app/Http/Controllers/PurchaseOrdersController.php`
Se agregó el método `exportExcel()`:
```php
public function exportExcel(Request $request)
{
    // Obtener filtros de la request
    $filters = [
        'order_number' => $request->get('order_number'),
        'request_number' => $request->get('request_number'),
        'provider_name' => $request->get('provider_name'),
        'date_from' => $request->get('date_from'),
        'date_to' => $request->get('date_to'),
    ];

    $filename = 'ordenes-compra-' . date('Y-m-d-His') . '.xlsx';
    
    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\PurchaseOrdersExport($filters),
        $filename
    );
}
```

### 3. `routes/web.php`
Se agregó la ruta para la exportación:
```php
Route::get('purchase-orders/export', [PurchaseOrdersController::class, 'exportExcel'])
    ->name('purchase-orders.export');
```

**Ubicación**: Dentro del grupo de middleware `auth` y `compras`, después de la ruta `purchase-orders.index`.

### 4. `resources/views/purchase-orders/index.blade.php`
Se agregó el botón de exportación en el encabezado de la tarjeta:
```blade
<a href="{{ route('purchase-orders.export') }}" class="btn btn-sm btn-success" title="Descargar informe en Excel">
    <i class="fas fa-file-excel"></i> Exportar a Excel
</a>
```

**Ubicación**: En el `card-header` de la sección "Lista de Órdenes de Compra", dentro de `card-tools`.

## Uso

### Exportación Simple
1. Navegar a la vista de órdenes de compra: `http://localhost/purchase-orders`
2. Hacer clic en el botón verde "Exportar a Excel"
3. Se descargará un archivo con nombre `ordenes-compra-YYYY-MM-DD-HHmmss.xlsx`

### Exportación con Filtros (Futuro)
Los filtros aplicados en la tabla DataTables pueden ser integrados con la exportación para exportar solo los registros filtrados. Para implementar esto:

1. Capturar los valores de los filtros en JavaScript
2. Enviar los filtros como parámetros GET en la URL de exportación
3. El método `exportExcel()` ya está preparado para recibir estos filtros

**Ejemplo de URL con filtros:**
```
http://localhost/purchase-orders/export?order_number=OC-001&provider_name=Proveedor&date_from=2025-01-01&date_to=2025-12-31
```

## Dependencias
- **Maatwebsite/Laravel-Excel**: Ya instalado en el proyecto
- **PhpSpreadsheet**: Dependencia de Laravel-Excel

## Formato del Archivo Excel

### Estructura
- **Nombre del archivo**: `ordenes-compra-YYYY-MM-DD-HHmmss.xlsx`
- **Nombre de la hoja**: "Órdenes de Compra"
- **Formato**: Excel 2007+ (.xlsx)

### Estilos
- **Encabezado**: 
  - Fondo azul (#364E76)
  - Texto blanco y en negrita
  - Tamaño de fuente: 12pt
  - Alineación centrada
  - Bordes negros

- **Filas de datos**:
  - Filas pares: Fondo gris claro (#F8F9FA)
  - Filas impares: Fondo blanco
  - Bordes negros en todas las celdas
  - Alineación centrada (excepto proveedor que está alineado a la izquierda)

- **Anchos de columna**:
  - Número de Orden: 20 caracteres
  - Solicitud: 18 caracteres
  - Proveedor: 35 caracteres
  - Monto: 18 caracteres
  - Fecha de Entrega: 18 caracteres
  - Creado: 20 caracteres

## Permisos
- Disponible para todos los usuarios autenticados que tengan acceso al módulo de compras
- La exportación respeta los filtros y permisos del usuario actual

## Mejoras Futuras Sugeridas

1. **Integración con Filtros de DataTables**: 
   - Capturar filtros activos y aplicarlos a la exportación
   - Agregar JavaScript para pasar parámetros de filtros a la URL de exportación

2. **Columnas Adicionales Opcionales**:
   - Estado de la orden
   - Usuario que creó la orden
   - Notas o comentarios
   - Ítems incluidos en la orden

3. **Formatos Alternativos**:
   - Exportación a PDF
   - Exportación a CSV para análisis de datos

4. **Filtros Avanzados**:
   - Exportar por rango de montos
   - Exportar por estado de orden
   - Exportar por departamento solicitante

5. **Estadísticas en la Exportación**:
   - Totales al final del documento
   - Resumen de órdenes por proveedor
   - Resumen de montos por mes

## Testing

### Pruebas Manuales Recomendadas
1. ✅ Exportar sin órdenes de compra (debería generar archivo con solo encabezados)
2. ✅ Exportar con 1 orden de compra
3. ✅ Exportar con múltiples órdenes (10+)
4. ✅ Verificar formato de montos ($X,XXX.XX)
5. ✅ Verificar formato de fechas (dd/mm/yyyy)
6. ✅ Verificar que las relaciones (solicitud, proveedor) se carguen correctamente
7. ✅ Verificar estilos y formato del Excel generado
8. ✅ Probar con órdenes sin proveedor o sin solicitud (debería mostrar "N/A")

### Comandos de Prueba
```bash
# Navegar al proyecto
cd c:/xampp/htdocs/tvs-final/tvs

# Verificar que no hay errores de sintaxis
php artisan route:list | grep "purchase-orders.export"

# Limpiar cache
php artisan optimize:clear
```

## Notas Adicionales
- El archivo se descarga directamente sin almacenarse en el servidor
- El nombre del archivo incluye fecha y hora para evitar sobrescrituras
- El formato de montos usa el estándar colombiano (punto para miles, coma para decimales)
- Las fechas usan formato día/mes/año para mayor claridad

## Autor
Implementación realizada el 22 de Octubre, 2025

## Referencias
- [Laravel Excel Documentation](https://docs.laravel-excel.com)
- [PhpSpreadsheet Documentation](https://phpspreadsheet.readthedocs.io)
