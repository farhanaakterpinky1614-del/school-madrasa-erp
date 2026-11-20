<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\Branch;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()->orderByDesc('is_primary')->orderBy('name')->paginate(20);

        return view('tenant.setup.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('tenant.setup.branches.create');
    }

    public function store(Request $request, CurrentTenant $currentTenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }


        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_primary']) {
            Branch::query()->where('tenant_id', $currentTenant->id())->update(['is_primary' => false]);
        }

        Branch::query()->create($data);

        return redirect()->route('tenant.branches.index')->with('status', __('ui.branch_saved'));
    }

    public function edit(Branch $branch): View
    {
        $this->assertTenantOwned($branch);
        return view('tenant.setup.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch, CurrentTenant $currentTenant): RedirectResponse
    {
        $this->assertTenantOwned($branch);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }


        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['is_primary']) {
            Branch::query()->where('tenant_id', $currentTenant->id())->whereKeyNot($branch->id)->update(['is_primary' => false]);
        }

        $branch->update($data);

        return redirect()->route('tenant.branches.index')->with('status', __('ui.branch_saved'));
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->assertTenantOwned($branch);
        $branch->delete();

        return redirect()->route('tenant.branches.index')->with('status', __('ui.branch_deleted'));
    }
}
