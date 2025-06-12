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

        // ... existing code ...
    }
}
