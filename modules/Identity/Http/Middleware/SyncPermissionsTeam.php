<?php

namespace Modules\Identity\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bind Spatie Permission team id to the resolved tenant for the request.
 */
class SyncPermissionsTeam
{
    public function __construct(
        protected CurrentTenant $currentTenant,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->currentTenant->check()) {
            setPermissionsTeamId($this->currentTenant->id());
        } else {
            // Spatie teams pivots require non-null team FK; 0 = platform/global context.
            setPermissionsTeamId(0);
        }

        return $next($request);
    }
}
