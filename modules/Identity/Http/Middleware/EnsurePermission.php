<?php

namespace Modules\Identity\Http\Middleware;

use App\Core\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side permission gate (never rely on frontend alone).
 * Sets Spatie team id from CurrentTenant before checking.
 */
class EnsurePermission
{
    public function __construct(
        protected CurrentTenant $currentTenant,
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->is_platform_admin) {
            return $next($request);
        }

        if ($this->currentTenant->check()) {
            setPermissionsTeamId($this->currentTenant->id());
        } elseif ($user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }

        // Institution owners / tenant_admin retain full back-office access.
        if ($user->isTenantAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (! $user->can($permission)) {
                abort(403, 'Missing permission: '.$permission);
            }
        }

        return $next($request);
    }
}
