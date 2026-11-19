<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Services\FeatureAccess;
use Modules\Platform\Services\UsageTracker;

class DashboardController extends Controller
{
    public function __invoke(CurrentTenant $currentTenant, FeatureAccess $features, UsageTracker $usage): View
    {
        $tenant = $currentTenant->require();
        $tenant->load(['institution', 'plan', 'primaryDomain', 'currentSubscription.plan']);

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'modules' => $features->modulesFor($tenant),
            'usage' => $usage->summary($tenant, $features),
        ]);
    }
}
