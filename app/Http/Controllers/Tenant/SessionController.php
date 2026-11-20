<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Identity\Services\AuditLogger;
use Modules\Identity\Services\SessionManager;

class SessionController extends Controller
{
    public function index(Request $request, SessionManager $sessions): View
    {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.sessions.view'),
            403
        );

        // Own sessions always; admins can pass ?user_id= for another tenant user later.
        $userId = $request->user()->id;
        $rows = $sessions->listForUser($userId);
        $currentId = $request->session()->getId();

        return view('tenant.sessions.index', [
            'sessions' => $rows,
            'currentId' => $currentId,
        ]);
    }

    public function destroy(
        Request $request,
        string $session,
        SessionManager $sessions,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.sessions.revoke'),
            403
        );

        if ($session === $request->session()->getId()) {
            return back()->withErrors(['session' => __('ui.cannot_revoke_current')]);
        }

        $sessions->revoke($session, $request->user()->id);
        $audit->log('sessions.revoked', null, null, ['session_id' => $session], 'Session revoked');

        return back()->with('status', __('ui.session_revoked'));
    }

    public function destroyOthers(
        Request $request,
        SessionManager $sessions,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.sessions.revoke'),
            403
        );

        $count = $sessions->revokeOthers($request->user()->id, $request->session()->getId());
        $audit->log('sessions.revoked_others', null, null, ['count' => $count]);

        return back()->with('status', __('ui.sessions_revoked', ['count' => $count]));
    }
}
