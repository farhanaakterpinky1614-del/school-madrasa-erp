<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Tenancy\Models\Tenant;

class UsageController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['usageMetrics', 'plan'])
            ->orderBy('name')
            ->paginate(20);

        return view('platform.usage.index', compact('tenants'));
    }
}
