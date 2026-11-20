<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Tenant admin access required.');
        }

        if ($user->is_platform_admin) {
            return redirect()->route('platform.dashboard');
        }

        if (! in_array($user->user_type, ['tenant_admin', 'owner'], true)) {
            abort(403, 'Tenant admin access required.');
        }

        return $next($request);
    }
}
