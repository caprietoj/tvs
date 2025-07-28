<?php

namespace App\Providers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\HelpVideo;
use App\Models\Quotation;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\HelpVideoPolicy;
use App\Policies\QuotationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // ... existing policies ...
        PurchaseRequest::class => PurchaseRequestPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        HelpVideo::class => HelpVideoPolicy::class,
        Quotation::class => QuotationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Gate para verificar si el usuario es admin
        Gate::define('admin', function ($user = null) {
            // Si no hay usuario, denegar acceso
            if (!$user) {
                return false;
            }
            
            // Verificar que el usuario tiene el trait HasRoles
            if (!method_exists($user, 'hasRole')) {
                return false;
            }
            
            try {
                // Verificar el rol admin usando Spatie Permission
                return $user->hasRole('admin');
            } catch (\Exception $e) {
                // En caso de error, denegar acceso
                return false;
            }
        });

        // Gate para verificar acceso a documentos institucionales
        Gate::define('institucional-access', function ($user) {
            return $user->hasRole('admin') || $user->email === 'asistentegeneral@tvs.edu.co';
        });

        // Gate para verificar acceso a gestión documental (incluyendo acceso especial para asistentegeneral)
        Gate::define('documents-general-access', function ($user) {
            return $user->hasRole('admin') || $user->email === 'asistentegeneral@tvs.edu.co';
        });

        // ... existing code ...
    }
}
