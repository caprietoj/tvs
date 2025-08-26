<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

echo "=== ANÁLISIS DEL PROBLEMA DE PLANTILLAS PDF ===\n\n";

try {
    // 1. Autenticar como usuario de compras
    $user = User::where('email', 'compras@tvs.edu.co')->first();
    if (!$user) {
        echo "ERROR: Usuario de compras no encontrado\n";
        exit(1);
    }
    Auth::login($user);
    echo "✓ Autenticado como: {$user->email}\n";

    // 2. Cargar la orden más reciente con selecciones mixtas
    $order = PurchaseOrder::where('order_number', 'ORD-0629')
        ->with(['provider', 'purchaseRequest.quotationItemSelections.quotation'])
        ->first();
    
    if (!$order) {
        echo "ERROR: Orden ORD-0629 no encontrada\n";
        exit(1);
    }
    
    echo "✓ Orden cargada: {$order->order_number}\n";
    echo "  Proveedor: {$order->provider->nombre}\n";
    echo "  Estado: {$order->status}\n\n";

    // 3. Analizar las selecciones
    $allSelections = $order->purchaseRequest->quotationItemSelections;
    echo "📊 ANÁLISIS DE SELECCIONES:\n";
    echo "  Total selecciones: {$allSelections->count()}\n";
    
    // Mostrar proveedores únicos en las selecciones
    $providers = $allSelections->pluck('quotation.provider_name')->unique();
    echo "  Proveedores en selecciones: " . $providers->implode(', ') . "\n";
    echo "  Proveedor de la orden: {$order->provider->nombre}\n";
    
    // 4. Filtrar selecciones por proveedor (como hace el controlador)
    $providerSelections = $allSelections->filter(function ($selection) use ($order) {
        return $selection->quotation->provider_name === $order->provider->nombre;
    });
    echo "  Selecciones filtradas para {$order->provider->nombre}: {$providerSelections->count()}\n\n";

    // 5. Determinar qué plantilla se debería usar
    echo "🎨 ANÁLISIS DE PLANTILLAS:\n";
    
    // Lógica del PurchaseOrderPdfService
    $hasQuotationItemSelections = $order->purchaseRequest->quotationItemSelections()->exists();
    $isService = $order->purchaseRequest->quotation === null;
    
    echo "  ¿Tiene selecciones de cotización? " . ($hasQuotationItemSelections ? 'SÍ' : 'NO') . "\n";
    echo "  ¿Es servicio sin cotización? " . ($isService ? 'SÍ' : 'NO') . "\n";
    
    if ($isService) {
        $expectedTemplate = 'pdf-template-service';
    } elseif ($hasQuotationItemSelections) {
        $expectedTemplate = 'pdf-template-mixed';
    } else {
        $expectedTemplate = 'pdf-template-new';
    }
    
    echo "  Plantilla esperada: {$expectedTemplate}\n\n";

    // 6. Simular generación como en creación (sin providerSelections)
    echo "📄 SIMULANDO CREACIÓN (sin providerSelections):\n";
    $pdfService = app(PurchaseOrderPdfService::class);
    
    // Limpiar logs anteriores
    Log::info('=== INICIO SIMULACIÓN CREACIÓN ===');
    
    // Determinar plantilla que se usaría
    $isService = $order->purchaseRequest->quotation === null;
    $hasQuotationItemSelections = $order->purchaseRequest->quotationItemSelections()->exists();
    
    if ($isService) {
        $templateToUse = 'pdf-template-service';
    } elseif ($hasQuotationItemSelections) {
        $templateToUse = 'pdf-template-mixed';
    } else {
        $templateToUse = 'pdf-template-new';
    }
    
    Log::info('Plantilla que se usará en CREACIÓN', ['template' => $templateToUse, 'isService' => $isService, 'hasSelections' => $hasQuotationItemSelections]);
    echo "  Plantilla esperada: {$templateToUse}\n";
    
    $pdfPath1 = $pdfService->generatePdf($order, null);
    $size1 = Storage::disk('public')->size($pdfPath1);
    echo "  PDF generado: {$pdfPath1}\n";
    echo "  Tamaño: {$size1} bytes\n\n";

    // 7. Simular generación como en edición (con providerSelections)
    echo "📝 SIMULANDO EDICIÓN (con providerSelections):\n";
    
    Log::info('=== INICIO SIMULACIÓN EDICIÓN ===');
    
    // Determinar plantilla que se usaría con providerSelections
    if ($isService) {
        $templateToUseEdit = 'pdf-template-service';
    } elseif ($providerSelections && $providerSelections->count() > 0) {
        $templateToUseEdit = 'pdf-template-mixed';
    } else {
        $templateToUseEdit = 'pdf-template-new';
    }
    
    Log::info('Plantilla que se usará en EDICIÓN', ['template' => $templateToUseEdit, 'providerSelections' => $providerSelections->count()]);
    echo "  Plantilla esperada: {$templateToUseEdit}\n";
    
    $pdfPath2 = $pdfService->generatePdf($order, $providerSelections);
    $size2 = Storage::disk('public')->size($pdfPath2);
    echo "  PDF generado: {$pdfPath2}\n";
    echo "  Tamaño: {$size2} bytes\n\n";

    // 8. Comparar resultados
    echo "🔍 COMPARACIÓN:\n";
    $diff = abs($size1 - $size2);
    $percentDiff = ($diff / max($size1, $size2)) * 100;
    
    echo "  Diferencia de tamaño: {$diff} bytes\n";
    echo "  Diferencia porcentual: " . number_format($percentDiff, 2) . "%\n";
    
    if ($percentDiff > 5) {
        echo "  ⚠️  PROBLEMA DETECTADO: Diferencia significativa (>5%)\n";
        echo "  Esto indica que se están usando plantillas diferentes\n";
    } else {
        echo "  ✓ Diferencia aceptable (<5%)\n";
    }
    
    echo "\n📋 RECOMENDACIONES:\n";
    echo "1. Revisar logs en storage/logs/laravel.log\n";
    echo "2. Buscar líneas que contengan 'SIMULACIÓN CREACIÓN' y 'SIMULACIÓN EDICIÓN'\n";
    echo "3. Verificar qué plantilla se usa en cada caso\n";
    echo "4. Comparar manualmente los PDFs generados\n";
    
    // 9. Mostrar rutas de los PDFs para verificación manual
    echo "\n📁 ARCHIVOS GENERADOS:\n";
    echo "  Creación: storage/app/public/{$pdfPath1}\n";
    echo "  Edición: storage/app/public/{$pdfPath2}\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";