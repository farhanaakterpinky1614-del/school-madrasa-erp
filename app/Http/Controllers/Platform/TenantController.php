<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Platform\Models\Plan;
use Modules\Tenancy\Models\Tenant;
use Modules\Tenancy\Services\TenantRegistrar;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['plan', 'institution', 'primaryDomain'])
            ->latest()
            ->paginate(15);

        return view('platform.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('platform.tenants.create', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'institutionTypes' => config('sme.institution_types'),
        ]);
    }

    public function store(Request $request, TenantRegistrar $registrar): RedirectResponse
    {
        $data = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'min:3', 'max:63', 'alpha_dash'],
            'institution_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('sme.institution_types')))],
            'plan_code' => ['required', 'string', 'exists:plans,code'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $result = $registrar->register($data);

        return redirect()
            ->route('platform.tenants.show', $result['tenant'])
            ->with('status', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load(['plan', 'institution', 'domains', 'subscriptions.plan', 'usageMetrics', 'users']);

        return view('platform.tenants.show', compact('tenant'));
    }

    public function updateStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,active,suspended,cancelled'],
        ]);

        $tenant->update(['status' => $data['status']]);

        return back()->with('status', 'Tenant status updated.');
    }
}
