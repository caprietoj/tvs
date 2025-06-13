<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DebugPhotocopiesController;
use App\Http\Controllers\TestPhotocopiesDashboardController;

// Enfermería
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ThresholdController;

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
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\WeeklyBiometricController;
use App\Http\Controllers\SalidaPedagogicaController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\QuotationApprovalController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\InventoryController; // Añadido para resolver el error Target Class
use App\Http\Controllers\SpaceBlockExceptionController; // Añadido para resolver el error con SpaceBlockExceptionController
use App\Http\Controllers\CopiesRequestController;
use App\Http\Controllers\PhotocopiesDashboardController;
use App\Http\Controllers\HelpVideoController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return redirect('/home');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    });

    // Enfermería Document Management
    Route::group(['prefix' => 'enfermeria', 'middleware' => ['auth']], function () {
        Route::get('/documents', [App\Http\Controllers\EnfermeriaDocumentController::class, 'index'])
            ->name('enfermeria.documents.index');
        Route::get('/documents/create', [App\Http\Controllers\EnfermeriaDocumentController::class, 'create'])
            ->name('enfermeria.documents.create');
        Route::post('/documents', [App\Http\Controllers\EnfermeriaDocumentController::class, 'store'])
            ->name('enfermeria.documents.store');
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
            Route::delete('/documents/{document}', [App\Http\Controllers\RrhhDocumentController::class, 'destroy'])
                ->name('rrhh.documents.destroy');
        });
    });

    Route::prefix('contabilidad')->group(function () {
        // Budget routes
        Route::get('/budget', [BudgetExecutionController::class, 'index'])->name('budget.index');
        Route::get('/budget/create', [BudgetExecutionController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetExecutionController::class, 'store'])->name('budget.store');
    
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
            Route::get('/documents/{document}/download', [App\Http\Controllers\SistemasDocumentController::class, 'download'])
                ->name('sistemas.documents.download');
            Route::delete('/documents/{document}', [App\Http\Controllers\SistemasDocumentController::class, 'destroy'])
                ->name('sistemas.documents.destroy');
        });
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
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        
        // Rutas de exportación e importación
        Route::get('/export', [ProveedorController::class, 'export'])->name('proveedores.export');
        Route::get('/import/show', [ProveedorController::class, 'showImport'])->name('proveedores.import');
        Route::post('/import/process', [ProveedorController::class, 'processImport'])->name('proveedores.process-import');
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

    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        
        // Rutas de exportación e importación
        Route::get('/export', [ProveedorController::class, 'export'])->name('proveedores.export');
        Route::get('/import/show', [ProveedorController::class, 'showImport'])->name('proveedores.import');
        Route::post('/import/process', [ProveedorController::class, 'processImport'])->name('proveedores.process-import');
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
        
    // Rutas de diagnóstico (solo para administradores)
    Route::middleware(['auth', 'role:Admin'])->group(function () {
        Route::get('diagnostics/routes', [App\Http\Controllers\DiagnosticController::class, 'diagnoseRoutes'])
            ->name('diagnostics.routes');
        Route::get('diagnostics/fix-routes', [App\Http\Controllers\DiagnosticController::class, 'fixRoutes'])
            ->name('diagnostics.fix-routes');
    });
});

// Rutas para el módulo de Compras y Órdenes de Compra
Route::middleware(['auth'])->group(function () {
    // Dashboard del módulo
    // Route::get('purchases/dashboard', [PurchaseDashboardController::class, 'index'])->name('purchases.dashboard');
    
    // Solicitudes de compra
    Route::resource('purchase-requests', PurchaseRequestController::class);
    
    // Rutas específicas para formularios de solicitud de compra
    Route::get('purchase-requests/create/purchase', [PurchaseRequestController::class, 'createPurchaseForm'])
        ->name('purchase-requests.create-purchase');
    Route::get('purchase-requests/create/materials', [PurchaseRequestController::class, 'createMaterialsForm'])
        ->name('purchase-requests.create-materials');
    Route::get('purchase-requests/create/copies', [PurchaseRequestController::class, 'createCopiesForm'])
        ->name('purchase-requests.create-copies');
    
    // Rutas para aprobar/rechazar solicitudes de compra
    Route::post('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
        ->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
        ->name('purchase-requests.reject');
    
    // Ruta para configurar cotizaciones requeridas
    Route::post('purchase-requests/{purchaseRequest}/configure-quotations', [PurchaseRequestController::class, 'configureQuotations'])
        ->name('purchase-requests.configure-quotations');
    
    // Rutas para manejo del estado de entrega de fotocopias
    Route::post('purchase-requests/{purchaseRequest}/mark-delivery-status', [PurchaseRequestController::class, 'markDeliveryStatus'])
        ->name('purchase-requests.mark-delivery-status')
        ->middleware('role:compras|admin|almacen');
    
    // Rutas para PDF de solicitudes de compra
    Route::get('purchase-requests/{id}/pdf/download', [PurchaseRequestController::class, 'generatePdf'])
        ->name('purchase-requests.pdf.download');
    Route::get('purchase-requests/{id}/pdf/view', [PurchaseRequestController::class, 'viewPdf'])
        ->name('purchase-requests.pdf.view');
    
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
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'show'])
        ->name('purchase-orders.show');
    Route::get('purchase-orders/{purchaseOrder}/edit', [PurchaseOrdersController::class, 'edit'])
        ->name('purchase-orders.edit');
    Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'update'])
        ->name('purchase-orders.update');
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
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrdersController::class, 'cancel'])
        ->name('purchase-orders.cancel');
});

// Rutas para las preaprobaciones de solicitudes de compra
Route::middleware(['auth'])->group(function () {
    Route::get('quotation-approvals', [QuotationApprovalController::class, 'index'])
        ->name('quotation-approvals.index');
    Route::get('quotation-approvals/{id}', [QuotationApprovalController::class, 'show'])
        ->name('quotation-approvals.show');
    Route::get('quotation-approvals/{id}/comparison', [QuotationApprovalController::class, 'compareQuotations'])
        ->name('quotation-approvals.compare');
    Route::post('quotation-approvals/{id}/pre-approve', [QuotationApprovalController::class, 'preApprove'])
        ->name('quotation-approvals.pre-approve');
});

// Rutas para las aprobaciones finales de solicitudes de compra
Route::middleware(['auth'])->group(function () {
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{id}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('approvals/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('approvals/{id}/update-budget', [ApprovalController::class, 'updateBudget'])->name('approvals.update-budget');
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
Route::post('/debug-photocopies/process', [DebugPhotocopiesController::class, 'process'])->name('debug.photocopies.process');

// Rutas para Videos de Ayuda
Route::middleware('auth')->group(function () {
    Route::resource('help-videos', HelpVideoController::class);
});