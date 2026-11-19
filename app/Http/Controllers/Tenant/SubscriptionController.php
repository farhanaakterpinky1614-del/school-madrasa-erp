<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Models\Plan;
use Modules\Platform\Services\FeatureAccess;

class SubscriptionController extends Controller
{
    public function show(CurrentTenant $currentTenant, FeatureAccess $features): View
    {
        $tenant = $currentTenant->require();
        $tenant->load(['plan.features', 'currentSubscription.plan']);

        return view('tenant.subscription.show', [
            'tenant' => $tenant,
            'plans' => Plan::query()->where('is_active', true)->with('features')->orderBy('sort_order')->get(),
            'modules' => $features->modulesFor($tenant),
        ]);
    }
}
