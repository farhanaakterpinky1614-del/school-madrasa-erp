<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hr\Models\SalaryStructure;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\StaffSalary;

class SalaryStructureController extends Controller
{
    public function index(): View
    {
        $structures = SalaryStructure::query()->orderBy('name')->paginate(20);

        return view('tenant.hr.salary.index', compact('structures'));
    }

    public function create(): View
    {
        return view('tenant.hr.salary.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        SalaryStructure::query()->create($data);

        return redirect()->route('tenant.salary.index')->with('status', __('ui.salary_structure_saved'));
    }

    public function edit(SalaryStructure $salary): View
    {
        $this->assertTenantOwned($salary);

        return view('tenant.hr.salary.edit', ['structure' => $salary]);
    }

    public function update(Request $request, SalaryStructure $salary): RedirectResponse
    {
        $this->assertTenantOwned($salary);
        $salary->update($this->validated($request));

        return redirect()->route('tenant.salary.index')->with('status', __('ui.salary_structure_saved'));
    }

    public function assignForm(): View
    {
        $staff = Staff::query()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get();
        $structures = SalaryStructure::query()->where('is_active', true)->orderBy('name')->get();

        return view('tenant.hr.salary.assign', compact('staff', 'structures'));
    }

    public function assign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')],
            'salary_structure_id' => ['required', 'integer', Rule::exists('salary_structures', 'id')],
            'effective_from' => ['required', 'date'],
            'basic_override' => ['nullable', 'numeric', 'min:0'],
        ]);

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $this->assertTenantOwned($staff);
        $structure = SalaryStructure::query()->findOrFail($data['salary_structure_id']);
        $this->assertTenantOwned($structure);

        StaffSalary::query()->where('staff_id', $staff->id)->update(['is_current' => false]);
        StaffSalary::query()->create([
            'staff_id' => $staff->id,
            'salary_structure_id' => $structure->id,
            'effective_from' => $data['effective_from'],
            'basic_override' => $data['basic_override'] ?? null,
            'is_current' => true,
        ]);

        return redirect()->route('tenant.staff.show', $staff)->with('status', __('ui.salary_assigned'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_rent' => ['nullable', 'numeric', 'min:0'],
            'medical' => ['nullable', 'numeric', 'min:0'],
            'conveyance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['code'] = $data['code'] ?: null;
        $data['house_rent'] = $data['house_rent'] ?? 0;
        $data['medical'] = $data['medical'] ?? 0;
        $data['conveyance'] = $data['conveyance'] ?? 0;
        $data['other_allowance'] = $data['other_allowance'] ?? 0;
        $data['deductions'] = $data['deductions'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
