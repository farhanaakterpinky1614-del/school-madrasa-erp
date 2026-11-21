<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Subject;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\TeacherAssignment;

class TeacherAssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = TeacherAssignment::query()
            ->with(['staff', 'schoolClass', 'section', 'subject', 'academicYear'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.hr.assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        return view('tenant.hr.assignments.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'is_class_teacher' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_class_teacher'] = $request->boolean('is_class_teacher');
        $data['is_active'] = $request->boolean('is_active', true);

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $this->assertTenantOwned($staff);
        abort_unless($staff->is_teacher || in_array($staff->staff_type, [Staff::TYPE_TEACHER, Staff::TYPE_BOTH], true), 422);

        TeacherAssignment::query()->create($data);

        return redirect()->route('tenant.assignments.index')->with('status', __('ui.assignment_saved'));
    }

    public function destroy(TeacherAssignment $assignment): RedirectResponse
    {
        $this->assertTenantOwned($assignment);
        $assignment->delete();

        return redirect()->route('tenant.assignments.index')->with('status', __('ui.assignment_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'teachers' => Staff::query()->teachers()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get(),
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ];
    }
}
