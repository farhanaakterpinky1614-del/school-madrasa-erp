<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Platform\Models\Plan;
use Modules\Platform\Models\TenantUsageMetric;
use Modules\Tenancy\Models\Institution;
use Modules\Tenancy\Models\Tenant;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('platform.dashboard', [
            'tenantCount' => Tenant::query()->count(),
            'activeTenants' => Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count(),
            'institutionCount' => Institution::queryWithoutTenantScope()->count(),
            'planCount' => Plan::query()->where('is_active', true)->count(),
            'recentTenants' => Tenant::query()->latest()->limit(5)->with('plan')->get(),
            'usageTotals' => [
                'students' => (int) TenantUsageMetric::query()->where('metric_key', TenantUsageMetric::STUDENTS)->sum('current_value'),
                'staff' => (int) TenantUsageMetric::query()->where('metric_key', TenantUsageMetric::STAFF)->sum('current_value'),
                'storage_mb' => (int) TenantUsageMetric::query()->where('metric_key', TenantUsageMetric::STORAGE_MB)->sum('current_value'),
            ],
        ]);
    }
}
