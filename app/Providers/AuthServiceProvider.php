<?php

namespace App\Providers;

use App\Models\PurchaseRequest;
use App\Models\HelpVideo;
use App\Models\Quotation;
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

        // Gate para verificar acceso al módulo de presupuesto
        Gate::define('presupuesto.access', function ($user) {
            // Administradores tienen acceso completo
            if ($user->hasRole('admin')) {
                return true;
            }
            
            // Usuarios específicos con acceso a secciones del presupuesto
            $allowedUsers = [
                'Ana Maria Grisales',
                'GINA LORENA HURTADO GÓMEZ',
                'Maria Constanza Bernal Baracaldo',
                'Andrea Carolina Florez Varon',
                'HELENA ORTIZ',
                'Laura Rodriguez Laverde',
                'Johanna Gavidia Barbosa'
            ];
            
            return in_array($user->name, $allowedUsers);
        });

        // Gate para verificar acceso al módulo de enfermería
        Gate::define('view.enfermeria', function ($user) {
            // Administradores tienen acceso completo
            if ($user->hasRole('admin')) {
                return true;
            }
            
            // También usuarios con el permiso específico de enfermería
            return $user->can('enfermeria.access');
        });

        // Gate para verificar acceso específico a ingreso de estudiantes
        Gate::define('enfermeria.ingreso_estudiantes', function ($user) {
            // Administradores tienen acceso completo
            if ($user->hasRole('admin')) {
                return true;
            }
            
            // También usuarios con el permiso específico
            return $user->can('enfermeria.ingreso_estudiantes.access');
        });

        // ... existing code ...
    }
}
