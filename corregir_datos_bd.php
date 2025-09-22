<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRECCIÓN DE DATOS EXISTENTES EN BD ===\n\n";

// Mapeo de correcciones basado en archivo Excel
$correcciones = [
    '132001' => 'PREESCOLAR Y PRIMARIA',
    '132003' => 'PREESCOLAR Y PRIMARIA', 
    '132005' => 'PREESCOLAR Y PRIMARIA',
    '130403' => 'BIBLIOTECA',
    '130405' => 'BIBLIOTECA',
];

foreach ($correcciones as $centro => $seccionCorrecta) {
    // Buscar registros que necesitan corrección
    $registros = \App\Models\PresupuestoItem::where('centro_costo', $centro)->get();
    
    if ($registros->count() > 0) {
        $seccionActual = $registros->first()->seccion;
        
        echo "Centro de costo: $centro\n";
        echo "Registros encontrados: " . $registros->count() . "\n";
        echo "Sección actual: $seccionActual\n";
        echo "Sección correcta: $seccionCorrecta\n";
        
        if ($seccionActual !== $seccionCorrecta) {
            echo "🔄 NECESITA CORRECCIÓN\n";
            
            // Realizar la corrección
            $actualizados = \App\Models\PresupuestoItem::where('centro_costo', $centro)
                ->update(['seccion' => $seccionCorrecta]);
            
            echo "✅ $actualizados registros corregidos\n";
        } else {
            echo "✅ Ya está correcto\n";
        }
        
        echo "---\n";
    } else {
        echo "Centro de costo $centro: No se encontraron registros\n---\n";
    }
}

echo "\n=== VERIFICACIÓN POST-CORRECCIÓN ===\n";

// Verificar el registro específico mencionado inicialmente
$registroOriginal = \App\Models\PresupuestoItem::where('centro_costo', '132001')
    ->where('cuenta', '616005959601')
    ->first();

if ($registroOriginal) {
    echo "Registro original del ejemplo:\n";
    echo "Centro: " . $registroOriginal->centro_costo . "\n";
    echo "Cuenta: " . $registroOriginal->cuenta . "\n";
    echo "Sección: " . $registroOriginal->seccion . "\n";
    echo "Descripción: " . $registroOriginal->descripcion . "\n";
    echo "Valor: " . $registroOriginal->valor . "\n";
}

echo "\n=== RESUMEN FINAL ===\n";
foreach ($correcciones as $centro => $seccion) {
    $count = \App\Models\PresupuestoItem::where('centro_costo', $centro)
        ->where('seccion', $seccion)
        ->count();
    echo "$centro → $seccion: $count registros\n";
}