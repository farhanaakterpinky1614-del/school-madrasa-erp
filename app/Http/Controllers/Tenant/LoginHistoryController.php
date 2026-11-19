<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Identity\Models\LoginHistory;

class LoginHistoryController extends Controller
{
    public function index(Request $request, CurrentTenant $currentTenant): View
    {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.login_history.view'),
            403
        );

        $histories = LoginHistory::query()
            ->where('tenant_id', $currentTenant->id())
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('tenant.login-history.index', compact('histories'));
    }
}
