<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;
use Modules\Students\Models\Student;
use Modules\Students\Services\EnrollmentService;

class EnrollmentController extends Controller
{
    public function create(Student $student): View
    {
        $this->assertTenantOwned($student);

        return view('tenant.students.enrollments.create', [
            'student' => $student,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Student $student, EnrollmentService $enrollments): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'school_class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'shift_id' => ['nullable', 'integer'],
            'roll_number' => ['nullable', 'string', 'max:32'],
            'enrolled_on' => ['nullable', 'date'],
        ]);

        $enrollments->enroll($student, $data);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.enrollment_saved'));
    }
}
