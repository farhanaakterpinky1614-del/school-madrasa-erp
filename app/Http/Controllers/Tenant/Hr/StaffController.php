<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\Department;
use Modules\Hr\Models\Designation;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\StaffIdGenerator;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = Staff::query()
            ->with(['designation', 'department'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('employee_code', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('type')->toString() === 'teacher', fn ($q) => $q->teachers())
            ->when($request->string('type')->toString() === 'employee', fn ($q) => $q->where('is_teacher', false))
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hr.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('tenant.hr.staff.create', $this->formOptions());
    }

    public function store(Request $request, StaffIdGenerator $ids): RedirectResponse
    {
        $data = $this->validated($request);
        $data['employee_code'] = $ids->nextEmployeeCode();
        $data['status'] = $data['status'] ?? Staff::STATUS_ACTIVE;
        $data['joined_on'] = $data['joined_on'] ?? now()->toDateString();
        $data['is_teacher'] = in_array($data['staff_type'], [Staff::TYPE_TEACHER, Staff::TYPE_BOTH], true);

        $staff = Staff::query()->create($data);

        return redirect()->route('tenant.staff.show', $staff)->with('status', __('ui.staff_saved'));
    }

    public function show(Staff $staff): View
    {
        $this->assertTenantOwned($staff);
        $staff->load([
            'designation', 'department', 'branch', 'documents',
            'assignments.schoolClass', 'assignments.section', 'assignments.subject', 'assignments.academicYear',
            'currentSalary.salaryStructure', 'leaveRequests.leaveType',
        ]);

        return view('tenant.hr.staff.show', compact('staff'));
    }

    public function edit(Staff $staff): View
    {
        $this->assertTenantOwned($staff);

        return view('tenant.hr.staff.edit', array_merge(['staff' => $staff], $this->formOptions()));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->assertTenantOwned($staff);
        $data = $this->validated($request);
        $data['is_teacher'] = in_array($data['staff_type'], [Staff::TYPE_TEACHER, Staff::TYPE_BOTH], true);
        $staff->update($data);

        return redirect()->route('tenant.staff.show', $staff)->with('status', __('ui.staff_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'designations' => Designation::query()->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'nid_no' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
            'staff_type' => ['required', Rule::in([Staff::TYPE_EMPLOYEE, Staff::TYPE_TEACHER, Staff::TYPE_BOTH])],
            'designation_id' => ['nullable', 'integer', Rule::exists('designations', 'id')],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'joined_on' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in([Staff::STATUS_ACTIVE, Staff::STATUS_INACTIVE, Staff::STATUS_TERMINATED])],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);

        return $data;
    }
}
