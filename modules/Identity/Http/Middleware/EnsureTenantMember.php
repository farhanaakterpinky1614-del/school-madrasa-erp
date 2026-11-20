<?php

namespace Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\Models\TenantMembership;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticated tenant staff (not platform admin) with an active membership when one exists.
 */
class EnsureTenantMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Tenant member access required.');
        }

        // Platform admins belong on /platform — redirect instead of a bare 403 (Try Live / bookmarks).
        if ($user->is_platform_admin) {
            return redirect()->route('platform.dashboard');
        }

        if (! $user->tenant_id) {
            abort(403, 'User is not attached to a tenant.');
        }

        if (! $user->is_active) {
            abort(403, 'User account is inactive.');
        }

        $membership = TenantMembership::queryWithoutTenantScope()
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();

        // Legacy Phase 02 users may lack a membership row — allow tenant_admin/owner.
        if ($membership && ! $membership->isActive()) {
            abort(403, 'Membership is not active.');
        }

        if (! $membership && ! $user->isTenantAdmin()) {
            abort(403, 'No active tenant membership.');
        }

        return $next($request);
    }
}
