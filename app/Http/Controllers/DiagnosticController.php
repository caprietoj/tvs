<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class DiagnosticController extends Controller
{
    public function diagnoseRoutes()
    {
        // Verificar si está autenticado y es administrador
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'No autorizado');
        }
        
        $diagnoseResults = [];
        
        // Verificar rutas de solicitudes de préstamo
        $routes = Route::getRoutes();
        $loanRoutes = [];
        
        foreach ($routes as $route) {
            if (strpos($route->uri, 'loan-requests') !== false) {
                $loanRoutes[] = [
                    'uri' => $route->uri,
                    'methods' => implode('|', $route->methods()),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                ];
            }
        }
        
        $diagnoseResults['routes'] = $loanRoutes;
        
        // Verificar tabla en la base de datos
        $diagnoseResults['database'] = [
            'table_exists' => Schema::hasTable('loan_requests'),
            'columns' => Schema::hasTable('loan_requests') ? Schema::getColumnListing('loan_requests') : [],
        ];
        
        // Verificar controlador
        try {
            $reflector = new \ReflectionClass('App\Http\Controllers\LoanRequestController');
            $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            $controllerMethods = [];
            foreach ($methods as $method) {
                if ($method->class === 'App\Http\Controllers\LoanRequestController') {
                    $controllerMethods[] = $method->name;
                }
            }
            
            $diagnoseResults['controller'] = [
                'exists' => true,
                'methods' => $controllerMethods
            ];
        } catch (\Exception $e) {
            $diagnoseResults['controller'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Verificar acceso a la ruta loan-requests.store
        $diagnoseResults['route_test'] = [
            'store_route_exists' => Route::has('loan-requests.store'),
            'store_route_url' => Route::has('loan-requests.store') ? route('loan-requests.store') : null,
        ];
        
        return view('diagnostics.routes', compact('diagnoseResults'));
    }
    
    public function fixRoutes()
    {
        // Verificar si está autenticado y es administrador
        if (!auth()->check() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'No autorizado');
        }
        
        // Limpiar caché de rutas
        Artisan::call('route:clear');
        
        // Limpiar caché de configuración
        Artisan::call('config:clear');
        
        // Limpiar caché de vistas
        Artisan::call('view:clear');
        
        // Limpiar caché general
        Artisan::call('cache:clear');
        
        // Reconstruir caché de rutas
        Artisan::call('route:cache');
        
        return back()->with('success', 'Se ha limpiado la caché de rutas y configuración. Intente enviar el formulario nuevamente.');
    }
}
