<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Models\TenantUsageMetric;
use Modules\Platform\Services\FeatureAccess;
use Modules\Platform\Services\UsageTracker;

class UsageController extends Controller
{
    public function index(CurrentTenant $currentTenant, FeatureAccess $features, UsageTracker $usage): View
    {
        $tenant = $currentTenant->require();

        return view('tenant.usage.index', [
            'tenant' => $tenant,
            'usage' => $usage->summary($tenant, $features),
        ]);
    }

    /**
     * Demo stub: allow tenant admin to set counters until real modules exist.
     */
    public function updateStub(Request $request, CurrentTenant $currentTenant, UsageTracker $usage): RedirectResponse
    {
        $tenant = $currentTenant->require();

        $data = $request->validate([
            'students' => ['required', 'integer', 'min:0'],
            'staff' => ['required', 'integer', 'min:0'],
            'storage_mb' => ['required', 'integer', 'min:0'],
        ]);

        $usage->set($tenant, TenantUsageMetric::STUDENTS, $data['students']);
        $usage->set($tenant, TenantUsageMetric::STAFF, $data['staff']);
        $usage->set($tenant, TenantUsageMetric::STORAGE_MB, $data['storage_mb']);

        return back()->with('status', 'Usage counters updated (stub).');
    }
}
