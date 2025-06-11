use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Example API route for KPIs
Route::get('/kpis', [KpiController::class, 'index']);
