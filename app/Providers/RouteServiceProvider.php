<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home'; // Asegurarse que es '/home'

    public function boot(): void
    {
        // ...existing code...

        Route::model('request', PurchaseRequest::class);
        Route::model('order', PurchaseOrder::class);

        // ...existing code...
    }
}
