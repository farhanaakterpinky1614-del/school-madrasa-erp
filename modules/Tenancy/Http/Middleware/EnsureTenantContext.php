<?php

namespace Modules\Tenancy\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Modules\Tenancy\Services\TenantResolver;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected TenantResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->currentTenant->check()) {
            $this->tryAuthenticatedFallback($request);
        }

        if (! $this->currentTenant->check()) {
            abort(404, 'No tenant resolved for this host.');
        }

        $user = $request->user();

        if ($user && ! $user->is_platform_admin && (int) $user->tenant_id !== (int) $this->currentTenant->id()) {
            abort(403, 'User does not belong to this tenant.');
        }

        return $next($request);
    }

    /**
     * Local/central convenience: bind CurrentTenant from the authenticated user.
     * Host resolution remains the primary trust boundary in production.
     */
    protected function tryAuthenticatedFallback(Request $request): void
    {
        if (! config('sme.tenancy.allow_user_tenant_fallback')) {
            return;
        }

        if (! $this->resolver->isCentralHost($request->getHost())) {
            return;
        }

        $user = $request->user();

        if (! $user || $user->is_platform_admin || ! $user->tenant_id) {
            return;
        }

        $userTenant = $user->tenant;

        if ($userTenant && $userTenant->isActive()) {
            $this->currentTenant->set($userTenant);

            if ($userTenant->timezone) {
                config(['app.timezone' => $userTenant->timezone]);
                date_default_timezone_set($userTenant->timezone);
            }
        }
    }
}
