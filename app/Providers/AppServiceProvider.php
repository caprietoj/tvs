<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Configurar paginación para usar Bootstrap 4
        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');
        
        // Register mail classes
        $this->app->singleton(\App\Mail\LoanRequestCreated::class);
        $this->app->singleton(\App\Mail\LoanRequestReviewed::class);
        $this->app->singleton(\App\Mail\LoanRequestFinalized::class);
    }
}
