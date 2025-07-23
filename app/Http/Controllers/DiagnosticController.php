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

    public function diagnoseGrades()
    {
        // Agregar imports necesarios
        $parentStudentSurvey = new \App\Models\ParentStudentSurvey();
        
        // Obtener una muestra de grados únicos
        $grades = \Illuminate\Support\Facades\DB::table('parent_student_surveys')
            ->select('student_grade')
            ->distinct()
            ->whereNotNull('student_grade')
            ->orderBy('student_grade')
            ->limit(20)
            ->get();

        $result = [
            'total_unique_grades' => $grades->count(),
            'sample_grades' => $grades->pluck('student_grade')->toArray(),
            'grade_filter_test' => []
        ];

        // Probar cada tipo de filtro
        $filters = ['Preescolar', 'Primaria', 'Secundaria', 'Bachillerato'];
        
        foreach ($filters as $filter) {
            $count = \App\Models\ParentStudentSurvey::where(function($query) use ($filter) {
                switch ($filter) {
                    case 'Preescolar':
                        $query->where(function($q) {
                            $q->where('student_grade', 'like', '%preescolar%')
                              ->orWhere('student_grade', 'like', '%prejardín%')
                              ->orWhere('student_grade', 'like', '%jardín%')
                              ->orWhere('student_grade', 'like', '%transición%')
                              ->orWhere('student_grade', 'like', '%kinder%')
                              ->orWhere('student_grade', 'like', '%pre-jardín%')
                              ->orWhere('student_grade', 'like', '%pre jardín%');
                        });
                        break;
                        
                    case 'Primaria':
                        $query->where(function($q) {
                            $q->where('student_grade', 'like', '%primaria%')
                              ->orWhere('student_grade', 'like', '%1°%')
                              ->orWhere('student_grade', 'like', '%2°%')
                              ->orWhere('student_grade', 'like', '%3°%')
                              ->orWhere('student_grade', 'like', '%4°%')
                              ->orWhere('student_grade', 'like', '%5°%')
                              ->orWhere('student_grade', 'like', '%primero%')
                              ->orWhere('student_grade', 'like', '%segundo%')
                              ->orWhere('student_grade', 'like', '%tercero%')
                              ->orWhere('student_grade', 'like', '%cuarto%')
                              ->orWhere('student_grade', 'like', '%quinto%');
                        });
                        break;
                        
                    case 'Secundaria':
                        $query->where(function($q) {
                            $q->where('student_grade', 'like', '%secundaria%')
                              ->orWhere('student_grade', 'like', '%6°%')
                              ->orWhere('student_grade', 'like', '%7°%')
                              ->orWhere('student_grade', 'like', '%8°%')
                              ->orWhere('student_grade', 'like', '%9°%')
                              ->orWhere('student_grade', 'like', '%sexto%')
                              ->orWhere('student_grade', 'like', '%séptimo%')
                              ->orWhere('student_grade', 'like', '%septimo%')
                              ->orWhere('student_grade', 'like', '%octavo%')
                              ->orWhere('student_grade', 'like', '%noveno%');
                        });
                        break;
                        
                    case 'Bachillerato':
                        $query->where(function($q) {
                            $q->where('student_grade', 'like', '%bachillerato%')
                              ->orWhere('student_grade', 'like', '%10°%')
                              ->orWhere('student_grade', 'like', '%11°%')
                              ->orWhere('student_grade', 'like', '%décimo%')
                              ->orWhere('student_grade', 'like', '%decimo%')
                              ->orWhere('student_grade', 'like', '%undécimo%')
                              ->orWhere('student_grade', 'like', '%undecimo%')
                              ->orWhere('student_grade', 'like', '%once%');
                        });
                        break;
                }
            })->count();
            
            $result['grade_filter_test'][$filter] = $count;
        }

        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
    }
}
