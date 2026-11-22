<?php

namespace App\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Modules\Platform\Services\FeatureAccess;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected FeatureAccess $features,
    ) {}

    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $tenant = $this->currentTenant->require();

        if (! $this->features->enabled($tenant, $featureKey)) {
            abort(403, "Feature [{$featureKey}] is not available on your plan.");
        }

        return $next($request);
    }
}
