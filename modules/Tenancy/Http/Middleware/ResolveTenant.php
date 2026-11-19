<?php

namespace Modules\Tenancy\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Modules\Tenancy\Services\TenantResolver;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        protected TenantResolver $resolver,
        protected CurrentTenant $currentTenant,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->currentTenant->clear();

        $tenant = $this->resolver->resolveFromRequest($request);

        if ($tenant) {
            $this->currentTenant->set($tenant);

            if ($tenant->timezone) {
                config(['app.timezone' => $tenant->timezone]);
                date_default_timezone_set($tenant->timezone);
            }
        }

        return $next($request);
    }
}
