<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hr\Models\Designation;

class DesignationController extends Controller
{
    public function index(): View
    {
        $designations = Designation::query()->orderBy('name')->paginate(20);

        return view('tenant.hr.designations.index', compact('designations'));
    }

    public function create(): View
    {
        return view('tenant.hr.designations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = $data['code'] ?: null;
        $data['is_active'] = $request->boolean('is_active', true);

        Designation::query()->create($data);

        return redirect()->route('tenant.designations.index')->with('status', __('ui.designation_saved'));
    }

    public function edit(Designation $designation): View
    {
        $this->assertTenantOwned($designation);

        return view('tenant.hr.designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        $this->assertTenantOwned($designation);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = $data['code'] ?: null;
        $data['is_active'] = $request->boolean('is_active', true);
        $designation->update($data);

        return redirect()->route('tenant.designations.index')->with('status', __('ui.designation_saved'));
    }
}
