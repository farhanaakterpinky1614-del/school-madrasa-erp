<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Exceptions\QuotaExceededException;
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
     * Demo setter with hard-quota enforcement on write.
     */
    public function updateStub(Request $request, CurrentTenant $currentTenant, UsageTracker $usage): RedirectResponse
    {
        $tenant = $currentTenant->require();

        $data = $request->validate([
            'students' => ['required', 'integer', 'min:0'],
            'staff' => ['required', 'integer', 'min:0'],
            'storage_mb' => ['required', 'integer', 'min:0'],
        ]);

        foreach ([
            TenantUsageMetric::STUDENTS => $data['students'],
            TenantUsageMetric::STAFF => $data['staff'],
            TenantUsageMetric::STORAGE_MB => $data['storage_mb'],
        ] as $metric => $value) {
            $limit = $usage->limitFor($tenant, $metric);
            if ($limit !== null && $value > $limit) {
                throw new QuotaExceededException(
                    $metric,
                    $usage->get($tenant, $metric),
                    $limit,
                    "Cannot set {$metric} to {$value}; plan limit is {$limit}.",
                );
            }
            $usage->set($tenant, $metric, $value);
        }

        return back()->with('status', 'Usage counters updated (quota-checked).');
    }
}
