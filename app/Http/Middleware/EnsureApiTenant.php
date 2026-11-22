<?php

namespace App\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Modules\Platform\Services\FeatureAccess;
use Modules\Tenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bind CurrentTenant from the Sanctum-authenticated user and gate api_access.
 */
class EnsureApiTenant
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected FeatureAccess $features,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isPlatformAdmin()) {
            return response()->json(['message' => 'Unauthenticated tenant API user required.'], 401);
        }

        if (! $user->tenant_id) {
            return response()->json(['message' => 'User has no tenant.'], 403);
        }

        $tenant = Tenant::query()->find($user->tenant_id);
        if (! $tenant || $tenant->status === 'suspended') {
            return response()->json(['message' => 'Tenant unavailable.'], 403);
        }

        if (! $this->features->enabled($tenant, 'api_access')) {
            return response()->json(['message' => 'API access requires a plan with api_access enabled.'], 403);
        }

        $this->currentTenant->set($tenant);
        setPermissionsTeamId($tenant->id);

        if ($tenant->timezone) {
            config(['app.timezone' => $tenant->timezone]);
            date_default_timezone_set($tenant->timezone);
        }

        return $next($request);
    }
}
