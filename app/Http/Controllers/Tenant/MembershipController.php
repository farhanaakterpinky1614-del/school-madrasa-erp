<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Identity\Models\TenantMembership;
use Modules\Identity\Services\AuditLogger;

class MembershipController extends Controller
{
    public function index(Request $request, CurrentTenant $currentTenant): View
    {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.memberships.view'),
            403
        );

        $memberships = TenantMembership::query()
            ->where('tenant_id', $currentTenant->id())
            ->with(['user', 'inviter'])
            ->latest()
            ->paginate(20);

        return view('tenant.memberships.index', compact('memberships'));
    }

    public function update(
        Request $request,
        TenantMembership $membership,
        CurrentTenant $currentTenant,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.memberships.update'),
            403
        );
        abort_unless((int) $membership->tenant_id === (int) $currentTenant->id(), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                TenantMembership::STATUS_ACTIVE,
                TenantMembership::STATUS_SUSPENDED,
                TenantMembership::STATUS_INVITED,
            ])],
        ]);

        $old = ['status' => $membership->status];

        $membership->status = $data['status'];
        $membership->suspended_at = $data['status'] === TenantMembership::STATUS_SUSPENDED ? now() : null;
        if ($data['status'] === TenantMembership::STATUS_ACTIVE && ! $membership->joined_at) {
            $membership->joined_at = now();
        }
        $membership->save();

        if ($membership->user && $data['status'] === TenantMembership::STATUS_SUSPENDED) {
            $membership->user->forceFill(['is_active' => false])->save();
        }

        if ($membership->user && $data['status'] === TenantMembership::STATUS_ACTIVE) {
            $membership->user->forceFill(['is_active' => true])->save();
        }

        $audit->log('memberships.updated', $membership, $old, ['status' => $membership->status]);

        return back()->with('status', __('ui.membership_updated'));
    }

    public function destroy(
        Request $request,
        TenantMembership $membership,
        CurrentTenant $currentTenant,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.memberships.delete'),
            403
        );
        abort_unless((int) $membership->tenant_id === (int) $currentTenant->id(), 404);

        if ((int) $membership->user_id === (int) $request->user()->id) {
            return back()->withErrors(['membership' => __('ui.cannot_remove_self')]);
        }

        $audit->log('memberships.deleted', $membership, [
            'user_id' => $membership->user_id,
            'status' => $membership->status,
        ], null);

        $membership->delete();

        return back()->with('status', __('ui.membership_removed'));
    }
}
