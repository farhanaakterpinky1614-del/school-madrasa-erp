<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hr\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $leaveTypes = LeaveType::query()->orderBy('name')->paginate(20);

        return view('tenant.hr.leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        return view('tenant.hr.leave-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = $data['code'] ?: null;
        $data['is_paid'] = $request->boolean('is_paid', true);
        $data['requires_approval'] = $request->boolean('requires_approval', true);
        $data['is_active'] = $request->boolean('is_active', true);

        LeaveType::query()->create($data);

        return redirect()->route('tenant.leave-types.index')->with('status', __('ui.leave_type_saved'));
    }

    public function edit(LeaveType $leaveType): View
    {
        $this->assertTenantOwned($leaveType);

        return view('tenant.hr.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->assertTenantOwned($leaveType);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = $data['code'] ?: null;
        $data['is_paid'] = $request->boolean('is_paid', true);
        $data['requires_approval'] = $request->boolean('requires_approval', true);
        $data['is_active'] = $request->boolean('is_active', true);
        $leaveType->update($data);

        return redirect()->route('tenant.leave-types.index')->with('status', __('ui.leave_type_saved'));
    }
}
