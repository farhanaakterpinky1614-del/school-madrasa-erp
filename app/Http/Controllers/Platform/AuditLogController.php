<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Identity\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::queryWithoutTenantScope()
            ->with('user')
            ->latest()
            ->paginate(40);

        return view('platform.audit.index', compact('logs'));
    }
}
