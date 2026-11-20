<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\Department;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()->with('branch')->orderBy('name')->paginate(20);

        return view('tenant.setup.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $branches = Branch::query()->orderBy('name')->get();

        return view('tenant.setup.departments.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'kind' => ['required', Rule::in(['academic', 'administrative'])],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Department::query()->create($data);

        return redirect()->route('tenant.departments.index')->with('status', __('ui.department_saved'));
    }

    public function edit(Department $department): View
    {
        $this->assertTenantOwned($department);
        $branches = Branch::query()->orderBy('name')->get();

        return view('tenant.setup.departments.edit', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->assertTenantOwned($department);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'kind' => ['required', Rule::in(['academic', 'administrative'])],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $department->update($data);

        return redirect()->route('tenant.departments.index')->with('status', __('ui.department_saved'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->assertTenantOwned($department);
        $department->delete();

        return redirect()->route('tenant.departments.index')->with('status', __('ui.department_deleted'));
    }
}
