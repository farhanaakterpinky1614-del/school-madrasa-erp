<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Department;
use Modules\Academics\Models\SchoolClass;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::query()->with('department')->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('tenant.setup.classes.index', compact('classes'));
    }

    public function create(): View
    {
        $departments = Department::query()->orderBy('name')->get();

        return view('tenant.setup.classes.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'numeric_level' => ['nullable', 'integer', 'min:0', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        SchoolClass::query()->create($data);

        return redirect()->route('tenant.classes.index')->with('status', __('ui.class_saved'));
    }

    public function edit(SchoolClass $class): View
    {
        $this->assertTenantOwned($class);
        $departments = Department::query()->orderBy('name')->get();

        return view('tenant.setup.classes.edit', ['class' => $class, 'departments' => $departments]);
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $this->assertTenantOwned($class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'numeric_level' => ['nullable', 'integer', 'min:0', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $class->update($data);

        return redirect()->route('tenant.classes.index')->with('status', __('ui.class_saved'));
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $this->assertTenantOwned($class);
        $class->delete();

        return redirect()->route('tenant.classes.index')->with('status', __('ui.class_deleted'));
    }
}
