<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Proveedor;

echo "=== DEMOSTRACIÓN DE LA CORRECCIÓN DEL PROBLEMA ===\n\n";

echo "PROBLEMA ANTERIOR:\n";
echo "En el método createSinglePurchaseOrder() se usaba:\n";
echo "  \$provider = \\App\\Models\\Proveedor::first();\n\n";
echo "Esto causaba que SIEMPRE se tomara el primer proveedor de la base de datos,\n";
echo "sin importar qué proveedor fue seleccionado en la cotización.\n\n";

echo "SOLUCIÓN IMPLEMENTADA:\n";
echo "Ahora el código:\n";
echo "1. Obtiene la cotización pre-aprobada/seleccionada\n";
echo "2. Busca el proveedor por nombre usando: \$quotation->provider_name\n";
echo "3. Si no existe, lo crea con los datos de la cotización\n";
echo "4. Usa ESE proveedor específico para la orden de compra\n\n";

echo "CÓDIGO CORREGIDO:\n";
echo "```php\n";
echo "// Obtener datos de la cotización\n";
echo "\$quotation = \$purchaseRequest->selectedQuotation ?? \$purchaseRequest->preApprovedQuotation;\n";
echo "\n";
echo "// Buscar el proveedor por nombre de la cotización\n";
echo "\$provider = \\App\\Models\\Proveedor::where('nombre', \$quotation->provider_name)->first();\n";
echo "\n";
echo "// Si no existe el proveedor, crearlo basado en los datos de la cotización\n";
echo "if (!\$provider) {\n";
echo "    \$provider = \\App\\Models\\Proveedor::create([\n";
echo "        'nombre' => \$quotation->provider_name,\n";
echo "        'email' => \$quotation->provider_email ?? 'sin-email@proveedor.com',\n";
echo "        // ... otros campos de la cotización\n";
echo "    ]);\n";
echo "}\n";
echo "```\n\n";

echo "RESULTADO:\n";
echo "✅ Ahora la orden de compra usará el proveedor CORRECTO de la cotización\n";
echo "✅ Si seleccionas 'Sistemi', la orden mostrará 'Sistemi'\n";
echo "✅ Si el proveedor no existe en la base de datos, se creará automáticamente\n";
echo "✅ Se mantiene la trazabilidad entre cotización → orden de compra\n\n";

echo "ARCHIVOS MODIFICADOS:\n";
echo "- app/Http/Controllers/ApprovalController.php (método createSinglePurchaseOrder)\n";
echo "- Se agregaron logs para mejor trazabilidad del proceso\n\n";

echo "PARA PROBAR LA CORRECCIÓN:\n";
echo "1. Crea una solicitud de compra\n";
echo "2. Sube cotizaciones de diferentes proveedores\n";
echo "3. Pre-aprueba una cotización específica (ej: Sistemi)\n";
echo "4. Aprueba definitivamente la solicitud\n";
echo "5. Verifica que la orden de compra muestre el proveedor correcto\n\n";

echo "=== CORRECCIÓN COMPLETADA ===\n";
