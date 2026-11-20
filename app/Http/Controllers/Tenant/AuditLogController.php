<?php

namespace App\Http\Controllers\Tenant;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Identity\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request, CurrentTenant $currentTenant): View
    {
        abort_unless(
            $request->user()->isTenantAdmin() || $request->user()->can('settings.audit.view'),
            403
        );

        $logs = AuditLog::query()
            ->where('tenant_id', $currentTenant->id())
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('tenant.audit.index', compact('logs'));
    }
}
