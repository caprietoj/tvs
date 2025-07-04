<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Actualizar una solicitud existente al estado "En Cotización"
    $request = \App\Models\PurchaseRequest::where('request_number', 'SC-0003')->first();
    
    if ($request) {
        $request->update([
            'status' => 'En Cotización',
            'type' => 'purchase'
        ]);
        
        echo "✅ Solicitud {$request->request_number} actualizada:\n";
        echo "   Estado: {$request->fresh()->status}\n";
        echo "   Tipo: {$request->fresh()->type}\n";
        echo "   Cotizaciones: {$request->quotations->count()}\n";
        echo "\n📍 Esta solicitud ahora DEBE mostrar el botón 'Hecho Cumplido' en /quotations\n";
    } else {
        echo "❌ No se encontró la solicitud SC-0003\n";
    }
    
    // También crear una nueva solicitud para estar seguros
    $newRequest = \App\Models\PurchaseRequest::create([
        'request_number' => 'SC-TEST-' . date('His'),
        'requester' => 'Usuario de Prueba',
        'section_area' => 'Sistemas',
        'request_date' => now(),
        'type' => 'purchase',
        'status' => 'En Cotización',
        'description' => 'Solicitud de prueba para verificar el botón Hecho Cumplido',
        'items' => json_encode([
            ['description' => 'Producto de prueba', 'quantity' => 1, 'unit' => 'Unidad']
        ]),
        'user_id' => 1,
        'created_by' => 1
    ]);
    
    echo "\n✅ Nueva solicitud creada:\n";
    echo "   Número: {$newRequest->request_number}\n";
    echo "   Estado: {$newRequest->status}\n";
    echo "   Tipo: {$newRequest->type}\n";
    echo "   Cotizaciones: {$newRequest->quotations->count()}\n";
    echo "\n📍 Esta solicitud también DEBE mostrar el botón 'Hecho Cumplido' en /quotations\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
