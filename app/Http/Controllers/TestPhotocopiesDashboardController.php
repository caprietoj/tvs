<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use Carbon\Carbon;

class TestPhotocopiesDashboardController extends Controller
{
    public function test()
    {
        echo "<h1>Test Dashboard Fotocopias</h1>";
        
        // 1. Verificar datos base
        echo "<h2>1. Datos Base</h2>";
        $total = PurchaseRequest::count();
        echo "Total registros: $total<br>";
        
        $photocopyRequests = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->get();
            
        echo "Registros de fotocopias: " . $photocopyRequests->count() . "<br>";
        
        // 2. Mostrar primer registro
        if ($photocopyRequests->count() > 0) {
            $first = $photocopyRequests->first();
            echo "<h2>2. Primer Registro</h2>";
            echo "ID: " . $first->id . "<br>";
            echo "Requester: " . $first->requester . "<br>";
            echo "Grade: " . $first->grade . "<br>";
            echo "Section: " . $first->section . "<br>";
            echo "Created: " . $first->created_at . "<br>";
            echo "Copy Items: <pre>" . json_encode($first->copy_items, JSON_PRETTY_PRINT) . "</pre>";
        }
        
        // 3. Filtro por fechas
        echo "<h2>3. Filtro por Fechas</h2>";
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        echo "Rango: $startDate a $endDate<br>";
        
        $filteredRequests = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->get();
            
        echo "Registros en rango: " . $filteredRequests->count() . "<br>";
        
        // 4. Calcular estadísticas manualmente
        echo "<h2>4. Estadísticas</h2>";
        $totalSolicitudes = $filteredRequests->count();
        $totalBlancoNegro = 0;
        $totalColor = 0;
        $totalDobleCarta = 0;
        
        foreach ($filteredRequests as $request) {
            if (is_array($request->copy_items)) {
                foreach ($request->copy_items as $item) {
                    $totalBlancoNegro += (int)($item['black_white'] ?? 0);
                    $totalColor += (int)($item['color'] ?? 0);
                    $totalDobleCarta += (int)($item['double_letter_color'] ?? 0);
                }
            }
        }
        
        echo "Total Solicitudes: $totalSolicitudes<br>";
        echo "Total Blanco y Negro: $totalBlancoNegro<br>";
        echo "Total Color: $totalColor<br>";
        echo "Total Doble Carta: $totalDobleCarta<br>";
        echo "Total Impresiones: " . ($totalBlancoNegro + $totalColor + $totalDobleCarta) . "<br>";
        
        // 5. Probar el controlador real
        echo "<h2>5. Controlador Real</h2>";
        try {
            $controller = new PhotocopiesDashboardController();
            $request = new Request([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            // Simular la lógica del controlador
            $query = PurchaseRequest::where('type', 'materials')
                ->whereNotNull('copy_items')
                ->whereNull('material_items')
                ->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
                
            $copiesRequests = $query->orderBy('created_at', 'desc')->get();
            echo "Controlador encontró: " . $copiesRequests->count() . " registros<br>";
            
            // Usar reflection para acceder al método privado
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('calculateStatistics');
            $method->setAccessible(true);
            $statistics = $method->invoke($controller, $copiesRequests);
            
            echo "Estadísticas calculadas:<br>";
            echo "<pre>" . json_encode($statistics, JSON_PRETTY_PRINT) . "</pre>";
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br>";
        }
    }
}
