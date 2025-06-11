<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use Carbon\Carbon;

class DebugPhotocopiesController extends Controller
{
    public function debug()
    {
        $debug = [];
        
        // 1. Verificar total de registros
        $debug['total_records'] = PurchaseRequest::count();
        
        // 2. Verificar registros con copy_items
        $debug['with_copy_items'] = PurchaseRequest::whereNotNull('copy_items')->count();
        
        // 3. Verificar tipo materials
        $debug['type_materials'] = PurchaseRequest::where('type', 'materials')->count();
        
        // 4. Verificar filtro completo
        $debug['photocopy_requests'] = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->count();
        
        // 5. Mostrar el primer registro
        $first = PurchaseRequest::first();
        if ($first) {
            $debug['first_record'] = [
                'id' => $first->id,
                'type' => $first->type,
                'requester' => $first->requester,
                'grade' => $first->grade,
                'created_at' => $first->created_at->format('Y-m-d H:i:s'),
                'copy_items' => $first->copy_items,
                'material_items' => $first->material_items,
            ];
        }
        
        // 6. Verificar rango de fechas por defecto
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $debug['date_range'] = [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ];
        
        $debug['in_date_range'] = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay()
            ])
            ->count();
        
        // 7. Obtener todos los registros que cumplan el filtro base
        $allPhotocopyRequests = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->get(['id', 'requester', 'grade', 'created_at', 'copy_items']);
        
        $debug['all_photocopy_requests'] = $allPhotocopyRequests->toArray();
        
        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    }
}
