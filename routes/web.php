<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DebugPhotocopiesController;
use App\Http\Controllers\TestPhotocopiesDashboardController;

// Enfermería
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ThresholdController;
use App\Http\Controllers\MotivosEnfermeriaController;

// Compras
use App\Http\Controllers\KpiComprasController;
use App\Http\Controllers\ThresholdComprasController;

// Recursos Humanos
use App\Http\Controllers\RecursosHumanosKpiController;
use App\Http\Controllers\RecursosHumanosThresholdController;

// Sistemas
use App\Http\Controllers\SistemasKpiController;
use App\Http\Controllers\SistemasThresholdController;

// contabilidad
use App\Http\Controllers\BudgetExecutionController; // Add this line
use App\Http\Controllers\ParametrizacionController;

// Documentos
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\UserController;

// Reportes
use App\Http\Controllers\KPIReportController;
use App\Http\Controllers\AttendanceController; // Agregar esta línea

use App\Http\Controllers\EventController;  // Agregar esta línea
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\MaintenanceRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\EvaluacionProveedorController;
use App\Http\Controllers\SatisfactionSurveyController; // Add this line
use App\Http\Controllers\Surveys\ComplementaryServices\TransportController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\WeeklyBiometricController;
use App\Http\Controllers\PorteriaDashboardController;
use App\Http\Controllers\SalidaPedagogicaController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\QuotationApprovalController;
use App\Http\Controllers\QuotationItemSelectionController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\InventoryController; // Añadido para resolver el error Target Class
use App\Http\Controllers\SpaceBlockExceptionController; // Añadido para resolver el error con SpaceBlockExceptionController
use App\Http\Controllers\CopiesRequestController;
use App\Http\Controllers\PhotocopiesDashboardController;
use App\Http\Controllers\HelpVideoController;
use App\Http\Controllers\PrevisitaConsolidadoController;

Route::get('/', function () {
    return redirect('/login');
});

// Ruta de prueba para jQuery
Route::get('/test-jquery', function () {
    return view('test-jquery');
})->middleware('auth');

Route::get('/dashboard', function () {
    return redirect('/home');
})->middleware(['auth', 'verified'])->name('dashboard');

// API para búsqueda de estudiantes (sin middleware para AJAX)
Route::get('api/estudiantes/buscar', [App\Http\Controllers\EstudiantesController::class, 'buscarEstudiantes'])
    ->name('api.estudiantes.buscar');

// API para búsqueda de empleados (sin middleware para AJAX)
Route::get('api/empleados/buscar', [App\Http\Controllers\EmpleadosController::class, 'buscarEmpleados'])
    ->name('api.empleados.buscar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('enfermeria')->group(function () {
        // Rutas para KPIs de Enfermería
        Route::get('kpis/create', [KpiController::class, 'createEnfermeria'])->name('kpis.enfermeria.create');
        Route::post('kpis', [KpiController::class, 'storeEnfermeria'])->name('kpis.enfermeria.store');
        Route::get('kpis', [KpiController::class, 'indexEnfermeria'])->name('kpis.enfermeria.index');
        Route::get('kpis/{id}/edit', [KpiController::class, 'editEnfermeria'])->name('kpis.enfermeria.edit');
        Route::put('kpis/{id}', [KpiController::class, 'updateEnfermeria'])->name('kpis.enfermeria.update');
        Route::get('kpis/{id}', [KpiController::class, 'showEnfermeria'])->name('kpis.enfermeria.show');
        Route::delete('kpis/{id}', [KpiController::class, 'destroyEnfermeria'])->name('kpis.enfermeria.destroy');
    
        // Rutas para la Configuración del Umbral en Enfermería
        Route::get('umbral', [ThresholdController::class, 'indexEnfermeria'])->name('umbral.enfermeria.index');
        Route::put('umbral/{id}', [ThresholdController::class, 'updateEnfermeria'])->name('umbral.enfermeria.update');
        Route::post('umbral', [ThresholdController::class, 'storeEnfermeria'])->name('umbral.enfermeria.store');
        
        // (Opcional) Ruta para visualizar el threshold en modo "show"
        Route::get('umbral/show', [ThresholdController::class, 'showEnfermeria'])->name('umbral.enfermeria.show');
        
        // Nueva ruta para crear umbral en Enfermería
        Route::get('umbral/create', [ThresholdController::class, 'createEnfermeria'])->name('umbral.enfermeria.create');

        // Nueva ruta para editar el umbral en Enfermería.
        Route::get('umbral/{id}/edit', [ThresholdController::class, 'editEnfermeria'])->name('umbral.enfermeria.edit');

        // Rutas para Ingreso de Estudiantes
        Route::get('ingreso-estudiantes', [App\Http\Controllers\EnfermeriaController::class, 'ingresoEstudiantes'])
            ->name('enfermeria.ingreso_estudiantes.index')
            ->middleware('can:enfermeria.ingreso_estudiantes');
        Route::get('ingreso-estudiantes/create', [App\Http\Controllers\EnfermeriaController::class, 'createIngresoEstudiante'])
            ->name('enfermeria.ingreso_estudiantes.create')
            ->middleware('can:enfermeria.ingreso_estudiantes');
        Route::post('ingreso-estudiantes', [App\Http\Controllers\EnfermeriaController::class, 'storeIngresoEstudiante'])
            ->name('enfermeria.ingreso_estudiantes.store')
            ->middleware('can:enfermeria.ingreso_estudiantes');
            
        // Rutas para Ingreso de Colaboradores
        Route::get('ingreso-colaboradores', [App\Http\Controllers\EnfermeriaController::class, 'ingresoColaboradores'])
            ->name('enfermeria.ingreso_colaboradores.index')
            ->middleware('can:enfermeria.ingreso_estudiantes'); // Usa el mismo permiso de enfermería
        Route::get('ingreso-colaboradores/create', [App\Http\Controllers\EnfermeriaController::class, 'createIngresoColaborador'])
            ->name('enfermeria.ingreso_colaboradores.create')
            ->middleware('can:enfermeria.ingreso_estudiantes');
        Route::post('ingreso-colaboradores', [App\Http\Controllers\EnfermeriaController::class, 'storeIngresoColaborador'])
            ->name('enfermeria.ingreso_colaboradores.store')
            ->middleware('can:enfermeria.ingreso_estudiantes');
    });

    // Parametrización de Enfermería
    Route::group(['prefix' => 'parametrizacion', 'middleware' => ['auth', 'can:view.enfermeria']], function () {
        Route::resource('motivos-enfermeria', App\Http\Controllers\MotivosEnfermeriaController::class)
            ->parameters(['motivos-enfermeria' => 'motivoEnfermeria']);
        Route::patch('motivos-enfermeria/{motivoEnfermeria}/toggle-active', [App\Http\Controllers\MotivosEnfermeriaController::class, 'toggleActive'])
            ->name('motivos-enfermeria.toggle-active');
        Route::post('motivos-enfermeria/import', [App\Http\Controllers\MotivosEnfermeriaController::class, 'import'])
            ->name('motivos-enfermeria.import');
            
        // Gestión de Estudiantes
        Route::resource('estudiantes', App\Http\Controllers\EstudiantesController::class);
        Route::patch('estudiantes/{estudiante}/toggle-active', [App\Http\Controllers\EstudiantesController::class, 'toggleActive'])
            ->name('estudiantes.toggle-active');
        Route::post('estudiantes/import', [App\Http\Controllers\EstudiantesController::class, 'import'])
            ->name('estudiantes.import');
            
        // Gestión de Empleados
        Route::resource('empleados', App\Http\Controllers\EmpleadosController::class);
        Route::post('empleados/import', [App\Http\Controllers\EmpleadosController::class, 'storeMultiple'])
            ->name('empleados.import');
    });
    
    // Enfermería Document Management
    Route::group(['prefix' => 'enfermeria', 'middleware' => ['auth']], function () {
        Route::get('/documents', [App\Http\Controllers\EnfermeriaDocumentController::class, 'index'])
            ->name('enfermeria.documents.index');
        Route::get('/documents/create', [App\Http\Controllers\EnfermeriaDocumentController::class, 'create'])
            ->name('enfermeria.documents.create');
        Route::post('/documents', [App\Http\Controllers\EnfermeriaDocumentController::class, 'store'])
            ->name('enfermeria.documents.store');
        Route::get('/documents/{document}/structure', [App\Http\Controllers\EnfermeriaDocumentController::class, 'showStructure'])
            ->name('enfermeria.documents.structure');
        Route::get('/documents/{document}/download', [App\Http\Controllers\EnfermeriaDocumentController::class, 'download'])
            ->name('enfermeria.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\EnfermeriaDocumentController::class, 'destroy'])
            ->name('enfermeria.documents.destroy');
    });

    Route::prefix('compras')->group(function () {
        // Rutas de KPIs para Compras
        Route::get('kpis', [KpiComprasController::class, 'indexCompras'])->name('kpis.compras.index');
        Route::get('kpis/create', [KpiComprasController::class, 'createCompras'])->name('kpis.compras.create');
        Route::post('kpis', [KpiComprasController::class, 'storeCompras'])->name('kpis.compras.store');
        Route::get('kpis/{id}', [KpiComprasController::class, 'showCompras'])->name('kpis.compras.show');
        Route::get('kpis/{id}/edit', [KpiComprasController::class, 'editCompras'])->name('kpis.compras.edit');
        Route::put('kpis/{id}', [KpiComprasController::class, 'updateCompras'])->name('kpis.compras.update');
        Route::delete('kpis/{id}', [KpiComprasController::class, 'destroyCompras'])->name('kpis.compras.destroy');

        // Rutas de Threshold para Compras
        Route::get('umbral/create', [ThresholdComprasController::class, 'createCompras'])->name('umbral.compras.create');
        Route::post('umbral', [ThresholdComprasController::class, 'storeCompras'])->name('umbral.compras.store');
        Route::get('umbral/{id}/edit', [ThresholdComprasController::class, 'editCompras'])->name('umbral.compras.edit');
        Route::put('umbral/{id}', [ThresholdComprasController::class, 'updateCompras'])->name('umbral.compras.update');
        Route::get('umbral/show', [ThresholdComprasController::class, 'showCompras'])->name('umbral.compras.show');
        Route::delete('umbral/{id}', [ThresholdComprasController::class, 'destroyCompras'])->name('umbral.compras.destroy');
        
        Route::post('satisfaction/process', [SatisfactionSurveyController::class, 'processExcel'])
            ->name('satisfaction.process');
    });

    // Compras Document Management
    Route::group(['prefix' => 'compras', 'middleware' => ['auth']], function () {
        Route::get('/documents', [App\Http\Controllers\ComprasDocumentController::class, 'index'])
            ->name('compras.documents.index');
        Route::get('/documents/create', [App\Http\Controllers\ComprasDocumentController::class, 'create'])
            ->name('compras.documents.create');
        Route::post('/documents', [App\Http\Controllers\ComprasDocumentController::class, 'store'])
            ->name('compras.documents.store');
        Route::get('/documents/{document}/structure', [App\Http\Controllers\ComprasDocumentController::class, 'showStructure'])
            ->name('compras.documents.structure');
        Route::get('/documents/{document}/download', [App\Http\Controllers\ComprasDocumentController::class, 'download'])
            ->name('compras.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\ComprasDocumentController::class, 'destroy'])
            ->name('compras.documents.destroy');
    });

    Route::prefix('rrhh')->group(function () {
        // Rutas de KPIs para RRHH
        Route::get('kpis', [RecursosHumanosKpiController::class, 'indexRecursosHumanos'])->name('kpis.rrhh.index');
        Route::get('kpis/create', [RecursosHumanosKpiController::class, 'createRecursosHumanos'])->name('kpis.rrhh.create');
        Route::post('kpis', [RecursosHumanosKpiController::class, 'storeRecursosHumanos'])->name('kpis.rrhh.store');
        Route::get('kpis/{id}', [RecursosHumanosKpiController::class, 'showRecursosHumanos'])->name('kpis.rrhh.show');
        Route::get('kpis/{id}/edit', [RecursosHumanosKpiController::class, 'editRecursosHumanos'])->name('kpis.rrhh.edit');
        Route::put('kpis/{id}', [RecursosHumanosKpiController::class, 'updateRecursosHumanos'])->name('kpis.rrhh.update');
        Route::delete('kpis/{id}', [RecursosHumanosKpiController::class, 'destroyRecursosHumanos'])->name('kpis.rrhh.destroy');

        // Rutas de Threshold para RRHH
        Route::get('umbral/create', [RecursosHumanosThresholdController::class, 'createRecursosHumanos'])->name('umbral.rrhh.create');
        Route::post('umbral', [RecursosHumanosThresholdController::class, 'storeRecursosHumanos'])->name('umbral.rrhh.store');
        Route::get('umbral/{id}/edit', [RecursosHumanosThresholdController::class, 'editRecursosHumanos'])->name('umbral.rrhh.edit');
        Route::put('umbral/{id}', [RecursosHumanosThresholdController::class, 'updateRecursosHumanos'])->name('umbral.rrhh.update');
        Route::get('umbral/show', [RecursosHumanosThresholdController::class, 'showRecursosHumanos'])->name('umbral.rrhh.show');
        Route::delete('umbral/{id}', [RecursosHumanosThresholdController::class, 'destroyRecursosHumanos'])->name('umbral.rrhh.destroy');

        // RRHH Document Management
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/documents', [App\Http\Controllers\RrhhDocumentController::class, 'index'])
                ->name('rrhh.documents.index');
            Route::get('/documents/create', [App\Http\Controllers\RrhhDocumentController::class, 'create'])
                ->name('rrhh.documents.create');
            Route::post('/documents', [App\Http\Controllers\RrhhDocumentController::class, 'store'])
                ->name('rrhh.documents.store');
            Route::get('/documents/{document}/download', [App\Http\Controllers\RrhhDocumentController::class, 'download'])
                ->name('rrhh.documents.download');
            Route::get('/documents/{document}/structure', [App\Http\Controllers\RrhhDocumentController::class, 'showStructure'])
                ->name('rrhh.documents.structure');
            Route::delete('/documents/{document}', [App\Http\Controllers\RrhhDocumentController::class, 'destroy'])
                ->name('rrhh.documents.destroy');
        });
    });

    Route::prefix('contabilidad')->group(function () {
        // Budget routes
        Route::get('/budget', [BudgetExecutionController::class, 'index'])->name('budget.index');
        Route::get('/budget/create', [BudgetExecutionController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetExecutionController::class, 'store'])->name('budget.store');
        
        // Presupuesto auto-login route
        Route::get('/presupuesto/autologin', function () {
            return view('presupuesto.autologin');
        })->name('presupuesto.autologin')->middleware('can:presupuesto.access');
        
        // Parametrización routes
        Route::get('/parametrizacion', [App\Http\Controllers\ParametrizacionController::class, 'index'])->name('parametrizacion.index');
        Route::post('/parametrizacion', [App\Http\Controllers\ParametrizacionController::class, 'store'])->name('parametrizacion.store');
        Route::post('/parametrizacion/reset', [App\Http\Controllers\ParametrizacionController::class, 'resetearSistema'])->name('parametrizacion.reset');
    
        // Contabilidad Document Management
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/documents', [App\Http\Controllers\ContabilidadDocumentController::class, 'index'])
                ->name('contabilidad.documents.index');
            Route::get('/documents/create', [App\Http\Controllers\ContabilidadDocumentController::class, 'create'])
                ->name('contabilidad.documents.create');
            Route::post('/documents', [App\Http\Controllers\ContabilidadDocumentController::class, 'store'])
                ->name('contabilidad.documents.store');
            Route::get('/documents/{document}/download', [App\Http\Controllers\ContabilidadDocumentController::class, 'download'])
                ->name('contabilidad.documents.download');
            Route::get('/documents/{document}/structure', [App\Http\Controllers\ContabilidadDocumentController::class, 'showStructure'])
                ->name('contabilidad.documents.structure');
            Route::delete('/documents/{document}', [App\Http\Controllers\ContabilidadDocumentController::class, 'destroy'])
                ->name('contabilidad.documents.destroy');
        });

        // Add these new routes
        Route::get('/cartera', [App\Http\Controllers\CarteraRecaudoController::class, 'index'])
            ->name('contabilidad.cartera.index');
        Route::get('/cartera/create', [App\Http\Controllers\CarteraRecaudoController::class, 'create'])
            ->name('contabilidad.cartera.create');
        Route::post('/cartera', [App\Http\Controllers\CarteraRecaudoController::class, 'store'])
            ->name('contabilidad.cartera.store');
    });

    Route::prefix('sistemas')->group(function () {
        // Rutas de KPIs para Sistemas
        Route::get('kpis', [SistemasKpiController::class, 'indexSistemas'])->name('kpis.sistemas.index');
        Route::get('kpis/create', [SistemasKpiController::class, 'createSistemas'])->name('kpis.sistemas.create');
        Route::post('kpis', [SistemasKpiController::class, 'storeSistemas'])->name('kpis.sistemas.store');
        Route::get('kpis/{id}', [SistemasKpiController::class, 'showSistemas'])->name('kpis.sistemas.show');
        Route::get('kpis/{id}/edit', [SistemasKpiController::class, 'editSistemas'])->name('kpis.sistemas.edit');
        Route::put('kpis/{id}', [SistemasKpiController::class, 'updateSistemas'])->name('kpis.sistemas.update');
        Route::delete('kpis/{id}', [SistemasKpiController::class, 'destroySistemas'])->name('kpis.sistemas.destroy');

        // Rutas de Threshold para Sistemas
        Route::get('umbral/create', [SistemasThresholdController::class, 'createSistemas'])->name('umbral.sistemas.create');
        Route::post('umbral', [SistemasThresholdController::class, 'storeSistemas'])->name('umbral.sistemas.store');
        Route::get('umbral/{id}/edit', [SistemasThresholdController::class, 'editSistemas'])->name('umbral.sistemas.edit');
        Route::put('umbral/{id}', [SistemasThresholdController::class, 'updateSistemas'])->name('umbral.sistemas.update');
        Route::get('umbral/index', [SistemasThresholdController::class, 'indexSistemas'])->name('umbral.sistemas.index');
        Route::delete('umbral/{id}', [SistemasThresholdController::class, 'destroySistemas'])->name('umbral.sistemas.destroy');

        // Sistemas Document Management
        Route::group(['middleware' => ['auth']], function () {
            Route::get('/documents', [App\Http\Controllers\SistemasDocumentController::class, 'index'])
                ->name('sistemas.documents.index');
            Route::get('/documents/create', [App\Http\Controllers\SistemasDocumentController::class, 'create'])
                ->name('sistemas.documents.create');
            Route::post('/documents', [App\Http\Controllers\SistemasDocumentController::class, 'store'])
                ->name('sistemas.documents.store');
            Route::get('/documents/{document}/structure', [App\Http\Controllers\SistemasDocumentController::class, 'showStructure'])
                ->name('sistemas.documents.structure');
            Route::get('/documents/{document}/download', [App\Http\Controllers\SistemasDocumentController::class, 'download'])
                ->name('sistemas.documents.download');
            Route::delete('/documents/{document}', [App\Http\Controllers\SistemasDocumentController::class, 'destroy'])
                ->name('sistemas.documents.destroy');
        });
    });

    // Institucional Document Management
    Route::group(['prefix' => 'institucional', 'middleware' => ['auth']], function () {
        Route::get('/documents', [App\Http\Controllers\InstitucionalDocumentController::class, 'index'])
            ->name('institucional.documents.index');
        Route::get('/documents/create', [App\Http\Controllers\InstitucionalDocumentController::class, 'create'])
            ->name('institucional.documents.create');
        Route::post('/documents', [App\Http\Controllers\InstitucionalDocumentController::class, 'store'])
            ->name('institucional.documents.store');
        Route::get('/documents/{document}/structure', [App\Http\Controllers\InstitucionalDocumentController::class, 'showStructure'])
            ->name('institucional.documents.structure');
        Route::get('/documents/{document}/download', [App\Http\Controllers\InstitucionalDocumentController::class, 'download'])
            ->name('institucional.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\InstitucionalDocumentController::class, 'destroy'])
            ->name('institucional.documents.destroy');
    });

    Route::get('tickets/dashboard', [HomeController::class, 'dashboard'])->name('tickets.dashboard');
    Route::resource('tickets', TicketController::class);

    // ruta para documentos y documentos request
    Route::resource('documents', DocumentController::class);
    Route::resource('document-requests', DocumentRequestController::class);

    // Group admin routes together
    Route::prefix('admin')->group(function () {
        Route::get('/settings', [App\Http\Controllers\AdminSettingsController::class, 'index'])->name('admin.settings');

        Route::resource('roles', App\Http\Controllers\RolesController::class)->names([
            'index'   => 'roles.index',
            'create'  => 'roles.create',
            'store'   => 'roles.store',
            'edit'    => 'roles.edit',
            'update'  => 'roles.update',
            'destroy' => 'roles.destroy',
        ]);

        // Add these new routes for bulk user import
        Route::get('users/bulk-import', [App\Http\Controllers\UserController::class, 'showBulkImport'])->name('users.bulk.import');
        Route::post('users/bulk-import', [App\Http\Controllers\UserController::class, 'bulkImport'])->name('users.bulk.import.process');
        Route::get('users/template/download', [UserController::class, 'downloadTemplate'])
            ->name('users.template.download');
        
        Route::resource('users', UserController::class);
    });

    // Rutas para el reporte de KPIs
    Route::group(['prefix' => 'admin'], function () {
    Route::get('kpis/report', [KPIReportController::class, 'index'])->name('kpi-report.index');
    });

    // Rutas para el controlador de asistencias
    Route::prefix('attendance')->group(function () {
        Route::get('upload', [AttendanceController::class, 'showUploadForm'])->name('attendance.upload');
        Route::post('import', [AttendanceController::class, 'importData'])->name('attendance.import');
        Route::get('dashboard/{mes?}', [AttendanceController::class, 'dashboard'])
            ->name('attendance.dashboard')
            ->where('mes', 'actual|Enero|Febrero|Marzo|Abril|Mayo|Junio|Julio|Agosto|Septiembre|Octubre|Noviembre|Diciembre');
        Route::get('export/excel/{mes?}', [AttendanceController::class, 'exportToExcel'])
            ->name('attendance.export.excel')
            ->where('mes', 'actual|Enero|Febrero|Marzo|Abril|Mayo|Junio|Julio|Agosto|Septiembre|Octubre|Noviembre|Diciembre');
        Route::get('export/html/{mes?}', [AttendanceController::class, 'exportHtml'])
            ->name('attendance.export.html')
            ->where('mes', 'actual|Enero|Febrero|Marzo|Abril|Mayo|Junio|Julio|Agosto|Septiembre|Octubre|Noviembre|Diciembre');
    });

    Route::get('/ausentismos/upload', [App\Http\Controllers\AusentismoController::class, 'showUploadForm'])->name('ausentismos.upload');
    Route::post('/ausentismos/store', [App\Http\Controllers\AusentismoController::class, 'store'])->name('ausentismos.store');
    Route::get('/ausentismos/dashboard', [App\Http\Controllers\AusentismoController::class, 'dashboard'])->name('ausentismos.dashboard');
    Route::get('/ausentismos/data', [App\Http\Controllers\AusentismoController::class, 'getData'])->name('ausentismos.data');

    // Rutas de eventos - deben ir antes de otras rutas que puedan interferir
    Route::get('events/calendar', [EventController::class, 'calendar'])->name('events.calendar');
    Route::get('events/dashboard', [EventController::class, 'dashboard'])->name('events.dashboard');
    Route::get('events/export', [EventController::class, 'export'])->name('events.export');
    Route::resource('events', EventController::class);
    Route::post('events/{event}/confirm', [EventController::class, 'confirm'])->name('events.confirm');
    Route::get('events/{event}/confirm/{token}', [EventController::class, 'confirm'])->name('events.confirm.token');
    Route::post('/events/{event}/confirm-service', [App\Http\Controllers\EventController::class, 'confirmService'])->name('events.confirm-service');
    
    // Rutas para las novedades de eventos
    Route::group(['prefix' => 'events/{event}/novelties', 'as' => 'event.novelties.'], function () {
        Route::get('/', [App\Http\Controllers\EventNoveltyController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\EventNoveltyController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\EventNoveltyController::class, 'store'])->name('store');
        Route::get('/{novelty}', [App\Http\Controllers\EventNoveltyController::class, 'show'])->name('show');
        Route::get('/{novelty}/edit', [App\Http\Controllers\EventNoveltyController::class, 'edit'])->name('edit');
        Route::put('/{novelty}', [App\Http\Controllers\EventNoveltyController::class, 'update'])->name('update');
        Route::delete('/{novelty}', [App\Http\Controllers\EventNoveltyController::class, 'destroy'])->name('destroy');
    });

    // Rutas para el módulo de Reserva de Equipos
    Route::prefix('equipment')->group(function () {
        // Asegúrate de que esta ruta esté antes de otras rutas más genéricas
        Route::post('/reset', [EquipmentController::class, 'resetInventory'])
            ->name('equipment.reset')
            ->middleware('auth', 'can:equipment.manage');
            
        Route::get('/', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::post('/store', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::get('/request', [EquipmentController::class, 'showRequestForm'])->name('equipment.request');
        Route::post('/request', [EquipmentController::class, 'requestLoan'])->name('equipment.request.submit');
        Route::get('/loans', [EquipmentController::class, 'showLoans'])->name('equipment.loans');
        Route::get('/loans/export', [EquipmentController::class, 'exportLoans'])->name('equipment.loans.export');
        Route::get('/inventory', [EquipmentController::class, 'inventory'])->name('equipment.inventory');
        Route::post('/reset', [EquipmentController::class, 'resetInventory'])->name('equipment.reset')->middleware('can:equipment.manage');
        Route::get('/dashboard', [EquipmentController::class, 'dashboard'])->name('equipment.dashboard');
        Route::get('/loans/data', [EquipmentController::class, 'getLoansData'])->name('equipment.loans.data');
        Route::get('/types/{section}', [EquipmentController::class, 'getEquipmentTypes'])->name('equipment.types');
        
        // Ruta para verificar disponibilidad
        Route::match(['get', 'post'], '/check-availability', [EquipmentController::class, 'checkAvailability'])
            ->name('equipment.check-availability');
            
        // Ruta adicional para compatibilidad con código existente
        Route::match(['get', 'post'], '/loans/check-availability', [EquipmentController::class, 'checkAvailability'])
            ->name('equipment.loans.check-availability');

        // Todas las rutas relacionadas con préstamos agrupadas
        Route::prefix('loans')->group(function () {
            Route::post('/{loan}/deliver', [EquipmentController::class, 'deliverEquipment'])
                ->name('equipment.loans.deliver')
                ->middleware('can:equipment.loans.manage');
            Route::post('/{loan}/return', [EquipmentController::class, 'returnEquipment'])
                ->name('equipment.loans.return');
            Route::put('/{id}', [EquipmentController::class, 'updateLoan'])
                ->name('equipment.loans.update')
                ->middleware('can:equipment.loans.manage');
            Route::post('/{id}/edit', [EquipmentController::class, 'editLoan'])
                ->name('equipment.loans.edit')
                ->middleware('can:equipment.loans.manage');
            Route::delete('/{id}', [EquipmentController::class, 'deleteLoan'])
                ->name('equipment.loans.delete')
                ->middleware('can:equipment.loans.manage');
            Route::get('/{id}/details', [EquipmentController::class, 'getLoanDetails'])
                ->name('equipment.loans.details');
            Route::post('/{id}/toggle-auto-return', [EquipmentController::class, 'toggleAutoReturn'])
                ->name('equipment.loans.toggle-auto-return');
        });

        // Rutas para horarios y procesamiento automático
        Route::get('/class-schedule', [EquipmentController::class, 'getClassSchedule'])
            ->name('equipment.class-schedule');
        Route::get('/process-auto-returns', [EquipmentController::class, 'processAutoReturns'])
            ->name('equipment.process-auto-returns')
            ->withoutMiddleware(['auth']);

        // Rutas para bloqueos de equipos
        Route::prefix('blocks')->name('equipment.blocks.')->group(function () {
            Route::get('/', [App\Http\Controllers\EquipmentBlockController::class, 'index'])
                ->name('index')
                ->middleware('can:equipment.blocks.manage');
            Route::get('/create', [App\Http\Controllers\EquipmentBlockController::class, 'create'])
                ->name('create')
                ->middleware('can:equipment.blocks.manage');
            Route::post('/', [App\Http\Controllers\EquipmentBlockController::class, 'store'])
                ->name('store')
                ->middleware('can:equipment.blocks.manage');
            Route::post('/weekly', [App\Http\Controllers\EquipmentBlockController::class, 'storeWeekly'])
                ->name('store-weekly')
                ->middleware('can:equipment.blocks.manage');
            Route::get('/{equipmentBlock}', [App\Http\Controllers\EquipmentBlockController::class, 'show'])
                ->name('show')
                ->middleware('can:equipment.blocks.manage');
            Route::get('/{equipmentBlock}/edit', [App\Http\Controllers\EquipmentBlockController::class, 'edit'])
                ->name('edit')
                ->middleware('can:equipment.blocks.manage');
            Route::put('/{equipmentBlock}', [App\Http\Controllers\EquipmentBlockController::class, 'update'])
                ->name('update')
                ->middleware('can:equipment.blocks.manage');
            Route::delete('/{equipmentBlock}', [App\Http\Controllers\EquipmentBlockController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:equipment.blocks.manage');
            Route::get('/blocked-units/check', [App\Http\Controllers\EquipmentBlockController::class, 'getBlockedUnits'])
                ->name('blocked-units');
            Route::get('/cycle-days/get', [App\Http\Controllers\EquipmentBlockController::class, 'getCycleDays'])
                ->name('cycle-days')
                ->middleware('can:equipment.blocks.manage');
        });
    });

    // Rutas para el módulo de Inventario
    Route::get('check-low-stock', [App\Http\Controllers\InventoryController::class, 'checkLowStockAlerts'])->name('inventory.check-low-stock');

    Route::group(['prefix' => 'inventory', 'as' => 'inventory.'], function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create')->middleware('can:inventario.create');
        Route::post('/', [InventoryController::class, 'store'])->name('store')->middleware('can:inventario.create');
        Route::get('/import', [InventoryController::class, 'importForm'])->name('import')->middleware('can:inventario.import');
        Route::post('/import', [InventoryController::class, 'processImport'])->name('process-import')->middleware('can:inventario.import');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit')->middleware('can:inventario.edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update')->middleware('can:inventario.edit');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy')->middleware('can:inventario.delete');
        Route::post('/{inventory}/return', [InventoryController::class, 'returnToInventory'])->name('return')->middleware('can:inventario.edit');
    });

    Route::get('maintenance/dashboard', [MaintenanceRequestController::class, 'dashboard'])
        ->name('maintenance.dashboard');
    Route::resource('maintenance', MaintenanceRequestController::class);
    Route::patch('maintenance/{maintenance}/status', [MaintenanceRequestController::class, 'updateStatus'])
        ->name('maintenance.status');
    Route::patch('maintenance/{maintenance}/assign-technician', [MaintenanceRequestController::class, 'assignTechnician'])->name('maintenance.assign-technician');

    // Rutas de proveedores
    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        
        // Rutas de exportación e importación (DEBEN IR ANTES de las rutas con parámetros)
        Route::get('/export', [ProveedorController::class, 'export'])->name('proveedores.export');
        Route::get('/import/show', [ProveedorController::class, 'showImport'])->name('proveedores.import');
        Route::get('/import/template', [ProveedorController::class, 'downloadTemplate'])->name('proveedores.download-template');
        Route::post('/import/process', [ProveedorController::class, 'processImport'])->name('proveedores.process-import');
        
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    });

    Route::resource('evaluaciones', EvaluacionProveedorController::class);

    // Add API endpoint for supplier evaluations summary
    Route::get('/api/evaluaciones/resumen', [EvaluacionProveedorController::class, 'apiResumen'])
        ->name('api.evaluaciones.resumen');

    // Add new API route for evaluations data
    Route::get('/api/evaluaciones/data', [EvaluacionProveedorController::class, 'getEvaluacionesData'])
        ->name('api.evaluaciones.data');

    // Weekly Biometric Routes
    Route::get('weekly-biometric', [WeeklyBiometricController::class, 'index'])
        ->name('weekly-biometric.index');
    Route::post('weekly-biometric/process', [WeeklyBiometricController::class, 'processData'])
        ->name('weekly-biometric.process');
    Route::get('weekly-biometric/dashboard', [WeeklyBiometricController::class, 'dashboard'])
        ->name('weekly-biometric.dashboard');
    Route::get('weekly-biometric/late-details/{department}', [WeeklyBiometricController::class, 'lateDetails'])
        ->name('weekly-biometric.late-details');

    // Salidas Pedagógicas Routes
    Route::resource('salidas', SalidaPedagogicaController::class);
    Route::get('salidas/confirmar/{id}/{area}/{token}', [SalidaPedagogicaController::class, 'confirmarArea'])
        ->name('salidas.confirmar-area');
    
    // Rutas para confirmaciones de servicios de salidas pedagógicas
    Route::post('salidas/{salida}/confirmar-transporte', [SalidaPedagogicaController::class, 'confirmarTransporte'])
        ->name('salidas.confirmar-transporte');
    Route::post('salidas/{salida}/confirmar-alimentacion', [SalidaPedagogicaController::class, 'confirmarAlimentacion'])
        ->name('salidas.confirmar-alimentacion');
    Route::post('salidas/{salida}/confirmar-enfermeria', [SalidaPedagogicaController::class, 'confirmarEnfermeria'])
        ->name('salidas.confirmar-enfermeria');
    Route::post('salidas/{salida}/confirmar-accesos', [SalidaPedagogicaController::class, 'confirmarAccesos'])
        ->name('salidas.confirmar-accesos');
    Route::post('salidas/{salida}/confirmar-comunicaciones', [SalidaPedagogicaController::class, 'confirmarComunicaciones'])
        ->name('salidas.confirmar-comunicaciones');
    Route::post('salidas/{salida}/confirmar-arl', [SalidaPedagogicaController::class, 'confirmarArl'])
        ->name('salidas.confirmar-arl');

    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        
        // Rutas de exportación e importación (DEBEN IR ANTES de las rutas con parámetros)
        Route::get('/export', [ProveedorController::class, 'export'])->name('proveedores.export');
        Route::get('/import/show', [ProveedorController::class, 'showImport'])->name('proveedores.import');
        Route::post('/import/process', [ProveedorController::class, 'processImport'])->name('proveedores.process-import');
        
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('requests', [PurchaseRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [PurchaseRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [PurchaseRequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{request}', [PurchaseRequestController::class, 'show'])->name('requests.show');
        Route::post('requests/{request}/approve', [PurchaseRequestController::class, 'approve'])->name('requests.approve');
        Route::post('requests/{id}/reject', [PurchaseRequestController::class, 'reject'])->name('requests.reject');
    
        Route::get('orders', [PurchaseOrdersController::class, 'index'])->name('orders.index');
        Route::get('orders/create/{request}', [PurchaseOrdersController::class, 'create'])->name('orders.create');
        Route::post('orders', [PurchaseOrdersController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [PurchaseOrdersController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/pdf', [PurchaseOrdersController::class, 'generatePdf'])->name('orders.pdf');
        Route::post('orders/{order}/mark-as-paid', [PurchaseOrdersController::class, 'markAsPaid'])
            ->name('orders.mark-as-paid');
        Route::post('orders/{order}/repair', [PurchaseOrdersController::class, 'repairOrderData'])
            ->name('orders.repair');
        Route::post('orders/repair-all', [PurchaseOrdersController::class, 'repairAllOrders'])
            ->name('orders.repair-all');
    });

    // Add Loan Request routes
    Route::resource('loan-requests', App\Http\Controllers\LoanRequestController::class);
    
    // Ruta para generar PDF de solicitud de préstamo
    Route::get('loan-requests/{loanRequest}/generate-pdf', [App\Http\Controllers\LoanRequestController::class, 'generatePdf'])
        ->name('loan-requests.generate-pdf');
        
    // Ruta para generar tabla de amortización
    Route::get('loan-requests/{loanRequest}/amortization', [App\Http\Controllers\LoanRequestController::class, 'amortization'])
        ->name('loan-requests.amortization');

    // Fix the middleware issue for loan request approvals
    Route::post('loan-requests/{loanRequest}/approve', [App\Http\Controllers\LoanRequestController::class, 'approve'])
        ->name('loan-requests.approve')
        ->middleware('can:approve-loan-requests');
        
    Route::post('loan-requests/{loanRequest}/reject', [App\Http\Controllers\LoanRequestController::class, 'reject'])
        ->name('loan-requests.reject')
        ->middleware('can:approve-loan-requests');
        
    // Ruta para obtener información de autorización (solo para admins)
    Route::get('loan-requests/{loanRequest}/authorization-info', [App\Http\Controllers\LoanRequestController::class, 'getAuthorizationInfo'])
        ->name('loan-requests.authorization-info')
        ->middleware('auth');
        
    // Rutas de diagnóstico (solo para administradores)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('diagnostics/routes', [App\Http\Controllers\DiagnosticController::class, 'diagnoseRoutes'])
            ->name('diagnostics.routes');
        Route::get('diagnostics/fix-routes', [App\Http\Controllers\DiagnosticController::class, 'fixRoutes'])
            ->name('diagnostics.fix-routes');
        
        // Diagnóstico de formularios grandes para producción
        Route::get('diagnostics/forms', function () {
            return view('admin.diagnose-forms');
        })->name('diagnostics.forms');
    });
});

// Rutas para el módulo de Compras y Órdenes de Compra
Route::middleware(['auth'])->group(function () {
    // Dashboard del módulo
    // Route::get('purchases/dashboard', [PurchaseDashboardController::class, 'index'])->name('purchases.dashboard');
    
    // Ruta para búsqueda AJAX en tiempo real (debe ir ANTES del resource)
    Route::get('purchase-requests/search', [PurchaseRequestController::class, 'searchAjax'])
        ->name('purchase-requests.search');
    
    // Solicitudes de compra
    Route::resource('purchase-requests', PurchaseRequestController::class);
    
    // Rutas específicas para formularios de solicitud de compra
    Route::get('purchase-requests/create/purchase', [PurchaseRequestController::class, 'createPurchaseForm'])
        ->name('purchase-requests.create-purchase');
    Route::get('purchase-requests/create/services', [PurchaseRequestController::class, 'createServicesForm'])
        ->name('purchase-requests.create-services');
    Route::get('purchase-requests/create/materials', [PurchaseRequestController::class, 'createMaterialsForm'])
        ->name('purchase-requests.create-materials');
    Route::get('purchase-requests/create/copies', [PurchaseRequestController::class, 'createCopiesForm'])
        ->name('purchase-requests.create-copies');
    
    // Rutas para aprobar/rechazar solicitudes de compra
    Route::post('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
        ->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
        ->name('purchase-requests.reject');
    
    // Ruta para aprobación masiva de fotocopias por sección
    Route::post('purchase-requests/bulk-approve-copies', [PurchaseRequestController::class, 'bulkApproveCopies'])
        ->name('purchase-requests.bulk-approve-copies');
    
    // Ruta para configurar cotizaciones requeridas
    Route::post('purchase-requests/{purchaseRequest}/configure-quotations', [PurchaseRequestController::class, 'configureQuotations'])
        ->name('purchase-requests.configure-quotations');
    
    // Rutas para manejo del estado de entrega de fotocopias
    Route::post('purchase-requests/{purchaseRequest}/mark-delivery-status', [PurchaseRequestController::class, 'markDeliveryStatus'])
        ->name('purchase-requests.mark-delivery-status')
        ->middleware('admin');
    
    // Rutas para PDF de solicitudes de compra
    Route::get('purchase-requests/{id}/pdf/download', [PurchaseRequestController::class, 'generatePdf'])
        ->name('purchase-requests.pdf.download');
    Route::get('purchase-requests/{id}/pdf/view', [PurchaseRequestController::class, 'viewPdf'])
        ->name('purchase-requests.pdf.view');
    
    // Ruta temporal de prueba AJAX
    Route::get('test-ajax', function() {
        return view('test_ajax');
    });
    
    // Ruta para descargar archivo original de fotocopias
    Route::get('purchase-requests/{purchaseRequest}/original/download', [PurchaseRequestController::class, 'downloadOriginal'])
        ->name('purchase-requests.download-original');
    
    // Ruta para descargar archivos adjuntos múltiples
    Route::get('purchase-requests/{id}/attached-files/{fileIndex}/download', [PurchaseRequestController::class, 'downloadAttachedFile'])
        ->name('purchase-requests.download-attached-file');
    
    // Cotizaciones
    Route::get('purchase-requests/{purchaseRequest}/quotations/create', [QuotationController::class, 'create'])
        ->name('quotations.create');
    Route::post('purchase-requests/{purchaseRequest}/quotations', [QuotationController::class, 'store'])
        ->name('quotations.store');
    Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])
        ->name('quotations.edit');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])
        ->name('quotations.update');
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])
        ->name('quotations.destroy');
    Route::get('quotations/{quotation}/download', [QuotationController::class, 'download'])
        ->name('quotations.download');
    
    // Corregir la ruta para seleccionar cotización - Cambio de POST a GET para pruebas
    Route::get('quotations/select/{quotation}', [QuotationController::class, 'select'])
        ->name('quotations.select');
    
    // También mantenemos la ruta POST
    Route::post('quotations/select/{quotation}', [QuotationController::class, 'select']);
    
    // Nuevas rutas para la gestión de cotizaciones
    Route::get('quotations', [QuotationController::class, 'index'])
        ->name('quotations.index');
    Route::get('quotations/ask-for-more/{purchaseRequest}', [QuotationController::class, 'askForMore'])
        ->name('quotations.ask-for-more');
    Route::post('quotations/process-more/{purchaseRequest}', [QuotationController::class, 'processMoreQuotations'])
        ->name('quotations.process-more');
    // Ruta para ver detalles de una cotización (debe ir después de las rutas más específicas)
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])
        ->name('quotations.show');
    
    // Ruta para envío de email de pre-aprobación
    Route::post('quotations/send-preapproval/{purchaseRequest}', [QuotationController::class, 'sendPreApprovalEmail'])
        ->name('quotations.send-preapproval-email');
    
    // Ruta para marcar como completado y enviar a preaprobación
    Route::get('quotations/mark-completed/{purchaseRequest}', [QuotationController::class, 'markCompleted'])
        ->name('quotations.mark-completed');
    
    // Ruta para anulación por falta de descripción
    Route::post('quotations/cancel-description/{purchaseRequest}', [QuotationController::class, 'cancelForDescription'])
        ->name('quotations.cancel-description');
    
    // Órdenes de compra
    Route::get('purchase-orders', [PurchaseOrdersController::class, 'index'])
        ->name('purchase-orders.index');
    Route::get('purchase-requests/{purchaseRequest}/orders/create', [PurchaseOrdersController::class, 'create'])
        ->name('purchase-orders.create');
    Route::post('purchase-requests/{purchaseRequest}/orders', [PurchaseOrdersController::class, 'store'])
        ->name('purchase-orders.store');
    
    // Rutas para creación manual de órdenes
    Route::post('purchase-requests/{purchaseRequest}/orders/create-for-provider', [PurchaseOrdersController::class, 'createForProvider'])
        ->name('purchase-orders.create-for-provider');
    Route::post('purchase-requests/{purchaseRequest}/orders/create-from-quotation', [PurchaseOrdersController::class, 'createFromQuotation'])
        ->name('purchase-orders.create-from-quotation');
    Route::post('purchase-requests/{purchaseRequest}/orders/create-no-quotation', [PurchaseOrdersController::class, 'createNoQuotation'])
        ->name('purchase-orders.create-no-quotation');
    
    // Ruta AJAX para obtener datos del proveedor
    Route::get('proveedores/{proveedor}/datos', [PurchaseOrdersController::class, 'getProviderData'])
        ->name('purchase-orders.get-provider-data');
    Route::get('purchase-requests/{purchaseRequest}/orders/create-no-quotation-purchase', [PurchaseOrdersController::class, 'showCreateNoQuotationPurchase'])
        ->name('purchase-orders.show-create-no-quotation-purchase');
    Route::post('purchase-requests/{purchaseRequest}/orders/create-no-quotation-purchase', [PurchaseOrdersController::class, 'createNoQuotationPurchase'])
        ->name('purchase-orders.create-no-quotation-purchase');
    
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'show'])
        ->name('purchase-orders.show');
    Route::get('purchase-orders/{purchaseOrder}/pdf', [PurchaseOrdersController::class, 'generatePdf'])
        ->name('purchase-orders.pdf');
    Route::get('purchase-orders/{purchaseOrder}/edit', [PurchaseOrdersController::class, 'edit'])
        ->name('purchase-orders.edit');
    Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'update'])
        ->name('purchase-orders.update');
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'destroy'])
        ->name('purchase-orders.destroy');
    Route::post('purchase-orders/{purchaseOrder}/toggle-viewed', [PurchaseOrdersController::class, 'toggleViewed'])
        ->name('purchase-orders.toggle-viewed');
    
    // Rutas específicas para administradores y personal de compras - Edición de PDF
    Route::middleware(['auth', 'admin_or_compras'])->group(function () {
        Route::get('purchase-orders/{purchaseOrder}/edit-pdf', [PurchaseOrdersController::class, 'editPdf'])
            ->name('purchase-orders.edit-pdf');
        Route::get('purchase-orders/{purchaseOrder}/edit-pdf-new', [PurchaseOrdersController::class, 'editPdfNew'])
            ->name('purchase-orders.edit-pdf-new');
        Route::put('purchase-orders/{purchaseOrder}/update-pdf', [PurchaseOrdersController::class, 'updatePdf'])
            ->name('purchase-orders.update-pdf');
        Route::post('purchase-orders/{purchaseOrder}/regenerate-pdf', [PurchaseOrdersController::class, 'regeneratePdf'])
            ->name('purchase-orders.regenerate-pdf');
        Route::post('purchase-orders/{purchaseOrder}/separate-mixed-order', [PurchaseOrdersController::class, 'separateMixedOrder'])
            ->name('purchase-orders.separate-mixed-order');
        Route::post('purchase-orders/{purchaseOrder}/remove-provider-items', [PurchaseOrdersController::class, 'removeProviderItems'])
            ->name('purchase-orders.remove-provider-items');
        Route::post('purchase-orders/{purchaseOrder}/create-alternative-order', [PurchaseOrdersController::class, 'createAlternativeOrder'])
            ->name('purchase-orders.create-alternative-order');
        Route::post('purchase-orders/{purchaseOrder}/revert-to-mixed-selection', [PurchaseOrdersController::class, 'revertToMixedSelection'])
            ->name('purchase-orders.revert-to-mixed-selection');
    });
    
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'destroy'])
        ->name('purchase-orders.destroy');
    Route::get('purchase-orders/{purchaseOrder}/download', [PurchaseOrdersController::class, 'download'])
        ->name('purchase-orders.download');
    Route::get('purchase-orders/{purchaseOrder}/view', [PurchaseOrdersController::class, 'view'])
        ->name('purchase-orders.view');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrdersController::class, 'approve'])
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/reject', [PurchaseOrdersController::class, 'reject'])
        ->name('purchase-orders.reject');
    Route::post('purchase-orders/{purchaseOrder}/payment', [PurchaseOrdersController::class, 'registerPayment'])
        ->name('purchase-orders.payment');
    Route::post('purchase-orders/{purchaseOrder}/send-to-accounting', [PurchaseOrdersController::class, 'sendToAccounting'])
        ->name('purchase-orders.send-to-accounting');
    Route::post('purchase-orders/{purchaseOrder}/send-to-compras', [PurchaseOrdersController::class, 'sendToCompras'])
        ->name('purchase-orders.send-to-compras');
    Route::post('purchase-orders/{purchaseOrder}/send-to-contabilidad', [PurchaseOrdersController::class, 'sendToContabilidad'])
        ->name('purchase-orders.send-to-contabilidad');
    Route::post('purchase-orders/{purchaseOrder}/send-to-tesoreria', [PurchaseOrdersController::class, 'sendToTesoreria'])
        ->name('purchase-orders.send-to-tesoreria');
    Route::post('purchase-orders/{purchaseOrder}/mark-as-paid', [PurchaseOrdersController::class, 'markAsPaid'])
        ->name('purchase-orders.mark-as-paid');
    Route::get('purchase-orders/{purchaseOrder}/payment-receipt', [PurchaseOrdersController::class, 'downloadPaymentReceipt'])
        ->name('purchase-orders.download-payment-receipt');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrdersController::class, 'cancel'])
        ->name('purchase-orders.cancel');
    
    // Ruta para regenerar PDF (solo administradores)
    Route::post('purchase-orders/{purchaseOrder}/regenerate-pdf', [PurchaseOrdersController::class, 'regeneratePdf'])
        ->name('purchase-orders.regenerate-pdf')
        ->middleware('auth');
});

// Rutas para las preaprobaciones de solicitudes de compra
Route::middleware(['auth'])->group(function () {
    Route::get('quotation-approvals/search', [QuotationApprovalController::class, 'searchAjax'])
        ->name('quotation-approvals.search');
    Route::get('quotation-approvals', [QuotationApprovalController::class, 'index'])
        ->name('quotation-approvals.index');
    Route::get('quotation-approvals/{id}', [QuotationApprovalController::class, 'show'])
        ->name('quotation-approvals.show');
    Route::get('quotation-approvals/{id}/comparison', [QuotationApprovalController::class, 'compareQuotations'])
        ->name('quotation-approvals.compare');
    Route::post('quotation-approvals/{id}/pre-approve', [QuotationApprovalController::class, 'preApprove'])
        ->name('quotation-approvals.pre-approve');
    Route::post('quotation-approvals/{id}/pre-approve-without-quotation', [QuotationApprovalController::class, 'preApproveWithoutQuotation'])
        ->name('quotation-approvals.pre-approve-without-quotation');
    Route::post('quotation-approvals/{id}/pre-approve-mixed-selection', [QuotationApprovalController::class, 'preApproveMixedSelection'])
        ->name('quotation-approvals.pre-approve-mixed-selection');
    Route::post('quotation-approvals/{id}/reject', [QuotationApprovalController::class, 'reject'])
        ->name('quotation-approvals.reject');
    Route::post('quotation-approvals/{id}/resend', [QuotationApprovalController::class, 'resendRequest'])
        ->name('quotation-approvals.resend');
});

// Rutas para las aprobaciones finales de solicitudes de compra
Route::middleware(['auth'])->group(function () {
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{id}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('approvals/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('approvals/{id}/resend', [ApprovalController::class, 'resendRequest'])->name('approvals.resend');
    Route::post('approvals/{id}/update-budget', [ApprovalController::class, 'updateBudget'])->name('approvals.update-budget');
    Route::post('approvals/{id}/update-shared-budget', [ApprovalController::class, 'updateSharedBudget'])->name('approvals.update-shared-budget');
    Route::post('approvals/{id}/update-third-budget', [ApprovalController::class, 'updateThirdBudget'])->name('approvals.update-third-budget');
    Route::post('approvals/{id}/update-quotation-amount', [ApprovalController::class, 'updateQuotationAmount'])->name('approvals.update-quotation-amount');
    
    // Ruta temporal para pruebas de aprobación
    Route::get('approvals/test-approval', function () {
        return view('approvals.test-approval');
    })->name('approvals.test');
    
    // Ruta para debug de modales
    Route::get('approvals/debug-show', function () {
        return view('approvals.debug-show');
    })->name('approvals.debug');
});

// Add impersonation routes
Route::middleware(['auth'])->group(function () {
    Route::get('/impersonate/{id}', [ImpersonateController::class, 'impersonate'])
        ->name('impersonate')
        ->middleware('can:impersonate');
    Route::get('/impersonate-stop', [ImpersonateController::class, 'stopImpersonating'])
        ->name('impersonate.stop');
});

// Announcement routes
Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);

Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
    Route::post('/configuration/emails', [ConfigurationController::class, 'updateEmails'])->name('configuration.update-emails');
});

Route::middleware(['auth', 'can:manage.configuration'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
    Route::post('/configuration/emails', [ConfigurationController::class, 'updateEmails'])->name('configuration.update-emails');
});

// Ruta de prueba para verificar envío de correos
Route::get('/test-mail', function () {
    $emails = app(\App\Http\Controllers\ConfigurationController::class)
        ->getNotificationEmails('equipment_loan');
    
    dd([
        'configured_emails' => $emails,
        'mail_config' => [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from' => config('mail.from'),
        ]
    ]);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [App\Http\Controllers\AdminSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/update-profile', [App\Http\Controllers\AdminSettingsController::class, 'updateProfile'])->name('settings.update-profile');
});

// Rutas accesibles sin autenticación para bloqueos de espacios
Route::middleware(['auth'])->group(function () {
    Route::get('space-blocks/create-weekly', [App\Http\Controllers\SpaceBlockController::class, 'createWeekly'])
        ->name('space-blocks.create-weekly');
    Route::post('space-blocks/store-weekly', [App\Http\Controllers\SpaceBlockController::class, 'storeWeekly'])
        ->name('space-blocks.store-weekly');
});

// Ruta para obtener eventos del calendario - Necesita estar fuera del middleware auth para AJAX
Route::get('space-reservations/events', [App\Http\Controllers\SpaceReservationController::class, 'getEvents'])
    ->name('space-reservations.events');

// Rutas para el sistema de reservas de espacios
Route::middleware(['auth'])->group(function () {
    // Gestión de Espacios
    Route::resource('spaces', App\Http\Controllers\SpaceController::class);
    // Nueva ruta para obtener detalles de un espacio en formato JSON
    Route::get('spaces/{space}/details', [App\Http\Controllers\SpaceController::class, 'getDetails'])
        ->name('spaces.details');
    // Ruta para duplicar espacios
    Route::post('spaces/{space}/duplicate', [App\Http\Controllers\SpaceController::class, 'duplicate'])
        ->name('spaces.duplicate');
    
    // Ciclos Escolares
    Route::resource('school-cycles', App\Http\Controllers\SchoolCycleController::class);
    Route::post('school-cycles/{schoolCycle}/generate-days', [App\Http\Controllers\SchoolCycleController::class, 'generateCycleDays'])
        ->name('school-cycles.generate-days');
    
    // Días Festivos
    Route::resource('holidays', App\Http\Controllers\HolidayController::class);
    Route::get('holidays/import/form', [App\Http\Controllers\HolidayController::class, 'importForm'])
        ->name('holidays.import.form');
    Route::post('holidays/import', [App\Http\Controllers\HolidayController::class, 'import'])
        ->name('holidays.import');
    
    // Bloqueos de Espacios
    Route::get('space-blocks/create-weekly', [App\Http\Controllers\SpaceBlockController::class, 'createWeekly'])
        ->name('space-blocks.create-weekly');
    Route::post('space-blocks/store-weekly', [App\Http\Controllers\SpaceBlockController::class, 'storeWeekly'])
        ->name('space-blocks.store-weekly');
    Route::get('space-blocks/download-list', [App\Http\Controllers\SpaceBlockController::class, 'downloadList'])
        ->name('space-blocks.download-list');
    Route::resource('space-blocks', App\Http\Controllers\SpaceBlockController::class);
    Route::get('space-blocks/space/{spaceId}', [App\Http\Controllers\SpaceBlockController::class, 'getBlocksBySpace'])
        ->name('space-blocks.by-space');
    
    // Reservas de Espacios - IMPORTANTE: Orden de rutas específico
    
    // 1. Otras rutas específicas
    Route::get('space-reservations/pending', [App\Http\Controllers\SpaceReservationController::class, 'pending'])
        ->name('space-reservations.pending');
    Route::get('space-reservations/calendar/{spaceId?}', [App\Http\Controllers\SpaceReservationController::class, 'calendar'])
        ->name('space-reservations.calendar');
    Route::get('space-reservations/check-availability/{spaceId}/{date}', [App\Http\Controllers\SpaceReservationController::class, 'checkAvailability'])
        ->name('space-reservations.check-availability');
    Route::get('space-reservations/{spaceReservation}/modal', [App\Http\Controllers\SpaceReservationController::class, 'getModalContent'])
        ->name('space-reservations.modal');
    Route::post('space-reservations/{spaceReservation}/cancel', [App\Http\Controllers\SpaceReservationController::class, 'cancel'])
        ->name('space-reservations.cancel');
    Route::match(['get', 'post'], 'space-reservations/{spaceReservation}/copy', [App\Http\Controllers\SpaceReservationController::class, 'copy'])
        ->name('space-reservations.copy');
    
    Route::post('space-reservations/{spaceReservation}/approve', [App\Http\Controllers\SpaceReservationController::class, 'approve'])
        ->name('space-reservations.approve')
        ->middleware('can:approve-space-reservations');
    
    // 2. Ruta resource general (debe ir al final para no capturar las rutas específicas)
    Route::resource('space-reservations', App\Http\Controllers\SpaceReservationController::class);
    
    // Ruta de prueba para diagnóstico
    Route::get('/test-route', function() {
        return 'Esta ruta de prueba funciona correctamente';
    });
});

// Rutas para excepciones de bloqueos semanales
Route::middleware(['auth'])->group(function () {
    Route::resource('space-block-exceptions', 'App\Http\Controllers\SpaceBlockExceptionController');
    Route::post('space-block-exceptions/quick-create', [SpaceBlockExceptionController::class, 'quickCreate'])->name('space-block-exceptions.quick-create');
});

// Rutas para listado de solicitudes de fotocopias
Route::middleware(['auth'])->group(function () {
    Route::get('copies-requests', [CopiesRequestController::class, 'index'])->name('copies-requests.index');
    Route::get('copies-requests/export', [CopiesRequestController::class, 'export'])->name('copies-requests.export');
});

// Rutas para Videos de Ayuda
Route::middleware(['auth'])->group(function () {
    Route::resource('help-videos', HelpVideoController::class);
});

// Rutas para dashboard de fotocopias
Route::middleware(['auth'])->group(function () {
    Route::get('photocopies/dashboard', [PhotocopiesDashboardController::class, 'index'])->name('photocopies.dashboard');
    Route::get('photocopies/export-data', [PhotocopiesDashboardController::class, 'exportData'])->name('photocopies.export-data');
    Route::get('photocopies/debug', [DebugPhotocopiesController::class, 'debug'])->name('photocopies.debug');
    Route::get('photocopies/test', [TestPhotocopiesDashboardController::class, 'test'])->name('photocopies.test');
});

// Rutas de prueba para el rol EMC (solo para testing)
Route::middleware(['auth'])->group(function () {
    Route::get('/test/emc-role', [App\Http\Controllers\EmcTestController::class, 'testEmcRole'])->name('test.emc-role');
    Route::get('/test/emc-functionality', [App\Http\Controllers\EmcTestController::class, 'verifyEmcFunctionality'])->name('test.emc-functionality');
});

require __DIR__.'/auth.php';

// Ruta de prueba pública sin middleware de autenticación
Route::get('/test-public', function() {
    return 'Esta ruta pública funciona correctamente';
});

// Ruta alternativa para el calendario de reservas
Route::get('/calendario-espacios/{spaceId?}', [App\Http\Controllers\SpaceReservationController::class, 'calendar'])
    ->name('calendario-espacios');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rutas de depuración temporal
Route::get('/debug-photocopies', [DebugPhotocopiesController::class, 'index'])->name('debug.photocopies');

// Rutas para el sistema de evaluaciones de desempeño
Route::middleware(['auth'])->prefix('performance-evaluations')->name('performance-evaluations.')->group(function () {
    Route::get('/', [App\Http\Controllers\PerformanceEvaluationController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\PerformanceEvaluationController::class, 'export'])->name('export');
    Route::get('/create', [App\Http\Controllers\PerformanceEvaluationController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PerformanceEvaluationController::class, 'store'])->name('store');
    Route::get('/{performanceEvaluation}', [App\Http\Controllers\PerformanceEvaluationController::class, 'show'])->name('show');
    
    // Rutas para autoevaluación
    Route::get('/{performanceEvaluation}/self-evaluate', [App\Http\Controllers\PerformanceEvaluationController::class, 'selfEvaluate'])->name('self-evaluate');
    Route::post('/{performanceEvaluation}/self-evaluate', [App\Http\Controllers\PerformanceEvaluationController::class, 'storeSelfEvaluation'])->name('store-self-evaluation');
    
    // Rutas para evaluación del supervisor
    Route::get('/{performanceEvaluation}/supervisor-evaluate', [App\Http\Controllers\PerformanceEvaluationController::class, 'supervisorEvaluate'])->name('supervisor-evaluate');
    Route::post('/{performanceEvaluation}/supervisor-evaluate', [App\Http\Controllers\PerformanceEvaluationController::class, 'storeSupervisorEvaluation'])->name('store-supervisor-evaluation');
});

// Rutas para sesiones de retroalimentación
Route::middleware(['auth'])->prefix('feedback-sessions')->name('feedback-sessions.')->group(function () {
    Route::get('/evaluation/{evaluation}/create', [App\Http\Controllers\FeedbackSessionController::class, 'create'])->name('create');
    Route::get('/evaluation/{evaluation}', function($evaluation) {
        return redirect()->route('feedback-sessions.create', $evaluation);
    });
    Route::post('/evaluation/{evaluation}', [App\Http\Controllers\FeedbackSessionController::class, 'store'])->name('store');
    Route::get('/{feedbackSession}', [App\Http\Controllers\FeedbackSessionController::class, 'show'])->name('show');
    Route::get('/{feedbackSession}/edit', [App\Http\Controllers\FeedbackSessionController::class, 'edit'])->name('edit');
    Route::patch('/{feedbackSession}', [App\Http\Controllers\FeedbackSessionController::class, 'update'])->name('update');
    Route::post('/{feedbackSession}/complete', [App\Http\Controllers\FeedbackSessionController::class, 'complete'])->name('complete');
    Route::delete('/{feedbackSession}/cancel', [App\Http\Controllers\FeedbackSessionController::class, 'cancel'])->name('cancel');
});

// Rutas para selección mixta de proveedores
Route::middleware('auth')->group(function () {
    Route::get('/purchase-requests/{purchaseRequest}/quotation-selections', [QuotationItemSelectionController::class, 'show'])
        ->name('quotation-selections.show');
    Route::post('/quotation-selections/select-item', [QuotationItemSelectionController::class, 'selectItem'])
        ->name('quotation-selections.select-item');
    Route::post('/purchase-requests/{purchaseRequest}/save-selection', [QuotationItemSelectionController::class, 'saveSelection'])
        ->name('quotation-selections.save-selection');
    Route::post('/quotation-selections/remove', [QuotationItemSelectionController::class, 'removeSelection'])
        ->name('quotation-selections.remove');
    Route::post('/purchase-requests/{purchaseRequest}/finalize-selection', [QuotationItemSelectionController::class, 'finalize'])
        ->name('quotation-selections.finalize');
    Route::post('/purchase-requests/{purchaseRequest}/save-and-send-selection', [QuotationItemSelectionController::class, 'saveAndSend'])
        ->name('quotation-selections.save-and-send');
});

// Ruta temporal de debugging para verificar permisos
Route::get('/debug-permissions', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado']);
    }
    
    $data = [
        'user_info' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'roles' => $user->roles->pluck('name')->toArray(),
        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        'performance_evaluation_permissions' => [
            'hasRole_admin' => $user->hasRole('admin'),
            'hasRole_rrhh' => $user->hasRole('rrhh'),
            'can_create_performance_evaluations' => $user->can('create-performance-evaluations'),
            'can_view_all_performance_evaluations' => $user->can('view-all-performance-evaluations'),
            'can_self_evaluate' => $user->can('self-evaluate'),
            'can_evaluate_as_supervisor' => $user->can('evaluate-as-supervisor'),
        ],
        'button_visibility' => [
            'create_button_condition' => $user->hasRole('admin') || $user->can('create-performance-evaluations'),
            'should_see_create_button' => $user->hasRole('admin') || $user->can('create-performance-evaluations') ? 'YES' : 'NO',
        ],
    ];
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

// Ruta de diagnóstico para grados
Route::get('/debug-grades', [App\Http\Controllers\DiagnosticController::class, 'diagnoseGrades'])->middleware('auth');

// Ruta temporal de prueba para evaluaciones SIN autenticación
Route::get('/test-evaluations-view', function() {
    // Simular datos para la vista
    $availableDepartments = [
        'Mantenimiento',
        'Servicios Generales', 
        'Sistemas',
        'Almacen',
        'Enfermeria',
        'Docentes',
        'EMC',
        'Biblioteca',
        'Contabilidad',
        'Asistentes'
    ];
    
    $employeesByDepartment = \App\Models\User::select('id', 'name', 'email', 'department')
        ->whereIn('department', $availableDepartments)
        ->orderBy('department')
        ->orderBy('name')
        ->get()
        ->groupBy('department');
    
    $allUsers = \App\Models\User::select('id', 'name', 'email', 'department')
        ->orderBy('name')
        ->get();
    
    $supervisors = \App\Models\User::orderBy('name')->get();
    
    return view('performance-evaluations.create', compact('employeesByDepartment', 'allUsers', 'supervisors', 'availableDepartments'));
});

// ==== RUTAS PARA ENCUESTAS INSTITUCIONALES (Solo Administradores) ====
Route::middleware(['auth', 'admin'])->prefix('surveys')->name('surveys.')->group(function () {
    
    // Cliente Interno
    Route::prefix('internal-client')->name('internal-client.')->group(function () {
        // Almacén
        Route::get('/warehouse', [App\Http\Controllers\WarehouseSurveyController::class, 'index'])->name('warehouse');
        Route::get('/warehouse/upload', [App\Http\Controllers\WarehouseSurveyController::class, 'upload'])->name('warehouse.upload');
        Route::post('/warehouse/upload', [App\Http\Controllers\WarehouseSurveyController::class, 'processUpload'])->name('warehouse.process-upload');
        Route::get('/warehouse/export', [App\Http\Controllers\WarehouseSurveyController::class, 'export'])->name('warehouse.export');
        
        // Enfermería (anteriormente cafetería)
        Route::get('/cafeteria', [App\Http\Controllers\NursingSurveyController::class, 'index'])->name('cafeteria');
        Route::get('/cafeteria/upload', [App\Http\Controllers\NursingSurveyController::class, 'upload'])->name('cafeteria.upload');
        Route::post('/cafeteria/process-upload', [App\Http\Controllers\NursingSurveyController::class, 'processUpload'])->name('cafeteria.process-upload');
        Route::get('/cafeteria/export', [App\Http\Controllers\NursingSurveyController::class, 'export'])->name('cafeteria.export');
        
        // Enfermería con URL correcta
        Route::get('/enfermeria', [App\Http\Controllers\NursingSurveyController::class, 'index'])->name('enfermeria');
        Route::get('/enfermeria/upload', [App\Http\Controllers\NursingSurveyController::class, 'upload'])->name('enfermeria.upload');
        Route::post('/enfermeria/process-upload', [App\Http\Controllers\NursingSurveyController::class, 'processUpload'])->name('enfermeria.process-upload');
        Route::get('/enfermeria/results', [App\Http\Controllers\NursingSurveyController::class, 'results'])->name('enfermeria.results');
        Route::get('/enfermeria/export', [App\Http\Controllers\NursingSurveyController::class, 'export'])->name('enfermeria.export');
        
        Route::get('/systems', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'index'])->name('systems');
        
        Route::get('/systems/upload', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'upload'])->name('systems.upload');
        Route::post('/systems/process', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'processUpload'])->name('systems.process');
        Route::get('/systems/results', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'results'])->name('systems.results');
        Route::get('/systems/details/{id}', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'details'])->name('systems.details');
        Route::get('/systems/export', [App\Http\Controllers\SurveySistemas\SystemsSurveyController::class, 'export'])->name('systems.export');
    });
    
    // Servicios Complementarios
    Route::prefix('complementary-services')->name('complementary-services.')->group(function () {
        Route::prefix('transport')->name('transport.')->group(function () {
            Route::get('/', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'index'])->name('index');
            Route::get('/upload', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'upload'])->name('upload');
            Route::post('/upload', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'processUpload'])->name('process-upload');
            Route::post('/upload-multiple', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'processMultipleUpload'])->name('process-multiple-upload');
            Route::get('/comparison', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'comparison'])->name('comparison');
            Route::post('/comparison', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'generateComparison'])->name('generate-comparison');
            Route::get('/compare', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'generateComparison'])->name('compare');
            Route::get('/export', [App\Http\Controllers\Surveys\ComplementaryServices\TransportController::class, 'export'])->name('export');
        });
    });
    
    // Encuestas Padres de Familia - Cafetería y Transporte
    Route::prefix('parent-student')->name('parent-student.')->group(function () {
        Route::get('/', [App\Http\Controllers\ParentStudentSurveyController::class, 'index'])->name('index');
        Route::get('/upload', [App\Http\Controllers\ParentStudentSurveyController::class, 'upload'])->name('upload');
        Route::post('/upload', [App\Http\Controllers\ParentStudentSurveyController::class, 'processUpload'])->name('upload.process');
        Route::get('/analysis', [App\Http\Controllers\ParentStudentSurveyController::class, 'analysis'])->name('analysis');
        Route::get('/comparison', [App\Http\Controllers\ParentStudentSurveyController::class, 'comparison'])->name('comparison');
        Route::post('/comparison', [App\Http\Controllers\ParentStudentSurveyController::class, 'comparison'])->name('generate-comparison');
        Route::get('/report', [App\Http\Controllers\ParentStudentSurveyController::class, 'generateReport'])->name('report');
        Route::get('/export', [App\Http\Controllers\ParentStudentSurveyController::class, 'exportData'])->name('export');
    });
});

// Rutas para Consolidado Previsitas
Route::middleware(['auth'])->prefix('previsitas')->name('previsitas.')->group(function () {
    Route::get('/', [PrevisitaConsolidadoController::class, 'index'])->name('index');
    Route::get('/dashboard', [PrevisitaConsolidadoController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [PrevisitaConsolidadoController::class, 'create'])->name('create');
    Route::post('/', [PrevisitaConsolidadoController::class, 'store'])->name('store');
    Route::get('/suggestions/lugares', [PrevisitaConsolidadoController::class, 'getLugarSuggestions'])->name('suggestions.lugares');
    Route::get('/suggestions/responsables', [PrevisitaConsolidadoController::class, 'getResponsableSuggestions'])->name('suggestions.responsables');
    Route::get('/{previsita}', [PrevisitaConsolidadoController::class, 'show'])->name('show');
    Route::get('/{previsita}/edit', [PrevisitaConsolidadoController::class, 'edit'])->name('edit');
    Route::put('/{previsita}', [PrevisitaConsolidadoController::class, 'update'])->name('update');
    Route::delete('/{previsita}', [PrevisitaConsolidadoController::class, 'destroy'])->name('destroy');
    Route::get('/{previsita}/download', [PrevisitaConsolidadoController::class, 'downloadFile'])->name('download');
    
    // Rutas para archivos múltiples
    Route::get('/archivos/{archivo}/download', [PrevisitaConsolidadoController::class, 'downloadArchivo'])->name('download-archivo');
    Route::delete('/archivos/{archivo}', [PrevisitaConsolidadoController::class, 'destroyArchivo'])->name('destroy-archivo');
});

// Ruta temporal para verificar cotizaciones con impuestos por ítem
Route::get('/check-quotations-item-taxes', function () {
    $quotations = \App\Models\Quotation::whereNotNull('original_item_taxes')
        ->orWhere('tax_application_mode', 'per_item')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    $response = [
        'total_found' => $quotations->count(),
        'quotations' => []
    ];
    
    foreach ($quotations as $quotation) {
        $quotationData = [
            'id' => $quotation->id,
            'provider_name' => $quotation->provider_name,
            'tax_application_mode' => $quotation->tax_application_mode,
            'total_amount' => $quotation->total_amount,
            'item_taxes' => [],
            'orders' => []
        ];
        
        if ($quotation->original_item_taxes) {
            $itemTaxes = is_array($quotation->original_item_taxes) 
                ? $quotation->original_item_taxes 
                : json_decode($quotation->original_item_taxes, true);
            
            if ($itemTaxes && is_array($itemTaxes)) {
                foreach ($itemTaxes as $index => $taxes) {
                    $appliedTaxes = [];
                    foreach ($taxes as $taxType => $applied) {
                        if ($applied) {
                            $appliedTaxes[] = $taxType;
                        }
                    }
                    if (!empty($appliedTaxes)) {
                        $quotationData['item_taxes'][$index] = $appliedTaxes;
                    }
                }
            }
        }
        
        // Verificar si hay órdenes asociadas
        $orders = \App\Models\PurchaseOrder::whereHas('purchaseRequest', function($query) use ($quotation) {
            $query->where('selected_quotation_id', $quotation->id);
        })->get();
        
        foreach ($orders as $order) {
            $quotationData['orders'][] = [
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount
            ];
        }
        
        $response['quotations'][] = $quotationData;
    }
    
    return response()->json($response, 200, [], JSON_PRETTY_PRINT);
});

// Ruta temporal para regenerar PDF de órdenes
Route::get('/regenerate-pdf/{orderNumber}', function ($orderNumber) {
    $order = \App\Models\PurchaseOrder::where('order_number', $orderNumber)->first();
    
    if (!$order) {
        return response()->json(['error' => "Orden {$orderNumber} no encontrada"], 404);
    }
    
    // Regenerar PDF
    $pdfService = app(\App\Services\PurchaseOrderPdfService::class);
    $pdfPath = $pdfService->generatePdf($order);
    
    // Actualizar la ruta del PDF en la orden
    $order->update(['file_path' => $pdfPath]);
    
    $response = [
        'success' => true,
        'message' => "PDF regenerado exitosamente",
        'order_number' => $order->order_number,
        'pdf_path' => $pdfPath,
    ];
    
    // Mostrar información de debug
    if ($order->pdf_custom_data) {
        $customData = json_decode($order->pdf_custom_data, true);
        $response['items_count'] = count($customData['items'] ?? []);
        
        $itemsWithTaxes = [];
        foreach ($customData['items'] ?? [] as $index => $item) {
            if (isset($item['applied_taxes']) && !empty($item['applied_taxes'])) {
                $itemsWithTaxes[] = [
                    'index' => $index + 1,
                    'description' => $item['description'],
                    'taxes' => $item['applied_taxes']
                ];
            }
        }
        
        $response['items_with_taxes'] = $itemsWithTaxes;
        $response['has_item_taxes'] = !empty($itemsWithTaxes);
    }
    
    return response()->json($response);
});

// === RUTAS DE PORTERÍA ===
Route::middleware(['auth'])->prefix('porteria')->name('porteria.')->group(function () {
    // Ruta de prueba (temporal)
    Route::get('test-verificacion', function () {
        return view('porteria.test-verificacion');
    })->name('test.verificacion');
    
    // Registro de entrada/salida
    Route::get('registro', [App\Http\Controllers\PorteriaController::class, 'index'])
        ->name('registro.index')
        ->middleware('permission:porteria.registro');
    
    Route::post('registro/verificar', [App\Http\Controllers\PorteriaController::class, 'verificar'])
        ->name('registro.verificar')
        ->middleware('permission:porteria.registro');
    
    Route::post('registro', [App\Http\Controllers\PorteriaController::class, 'store'])
        ->name('registro.store')
        ->middleware('permission:porteria.registro.create');
    
    Route::get('registro/{id}/edit', [App\Http\Controllers\PorteriaController::class, 'edit'])
        ->name('registro.edit')
        ->middleware('permission:admin.personas');
    
    Route::put('registro/{id}', [App\Http\Controllers\PorteriaController::class, 'update'])
        ->name('registro.update')
        ->middleware('permission:admin.personas');
    
    Route::delete('registro/{id}', [App\Http\Controllers\PorteriaController::class, 'destroy'])
        ->name('registro.destroy')
        ->middleware('permission:admin.personas');
    
    Route::get('registros/hoy', [App\Http\Controllers\PorteriaController::class, 'getRegistrosHoy'])
        ->name('registro.hoy')
        ->middleware('permission:porteria.registro.view');
    
    // Dashboard de portería
    Route::get('dashboard', [App\Http\Controllers\PorteriaDashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view.reports');
    
    Route::get('dashboard/export', [App\Http\Controllers\PorteriaDashboardController::class, 'export'])
        ->name('dashboard.export')
        ->middleware('permission:view.reports');
    
    Route::get('dashboard/export-html', [App\Http\Controllers\PorteriaDashboardController::class, 'exportHtml'])
        ->name('dashboard.export-html')
        ->middleware('permission:view.reports');
    
    // Gestión de personas (solo admin)
    Route::middleware('permission:admin.personas')->group(function () {
        Route::get('personas/import', [App\Http\Controllers\PersonasController::class, 'importForm'])
            ->name('personas.import');
        Route::post('personas/import', [App\Http\Controllers\PersonasController::class, 'import'])
            ->name('personas.import.process');
        Route::resource('personas', App\Http\Controllers\PersonasController::class)
            ->except(['show']);
    });
});
