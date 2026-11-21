<?php

namespace App\Http\Controllers\Tenant\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Attendance\Concerns\LoadsAttendanceFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Attendance\Services\TeacherAttendanceScope;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class DailyAttendanceController extends Controller
{
    use LoadsAttendanceFormOptions;

    public function index(Request $request): View
    {
        $options = $this->attendanceFormOptions($request->user());
        $date = $request->string('date')->toString() ?: now('Asia/Dhaka')->toDateString();
        $classId = $request->integer('class_id') ?: ($options['classes']->first()?->id);
        $sectionId = $request->filled('section_id') && $request->input('section_id') !== ''
            ? $request->integer('section_id')
            : ($options['sections']->firstWhere('school_class_id', $classId)?->id);

        $students = collect();
        $existing = collect();

        if ($classId) {
            $enrollments = StudentEnrollment::query()
                ->with('student')
                ->where('school_class_id', $classId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->orderBy('roll_number')
                ->get();

            $students = $enrollments->map(function (StudentEnrollment $e) {
                $student = $e->student;
                if ($student) {
                    $student->setAttribute('roll_number', $e->roll_number);
                }

                return $student;
            })->filter()->values();

            $existing = StudentAttendance::query()
                ->whereDate('attendance_date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        return view('tenant.attendance.daily.index', array_merge($options, [
            'date' => $date,
            'classId' => $classId,
            'sectionId' => $sectionId,
            'students' => $students,
            'existing' => $existing,
        ]));
    }

    public function store(Request $request, TeacherAttendanceScope $scope): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'entries.*.status' => ['required', Rule::in(StudentAttendance::STATUSES)],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $classId = (int) $data['school_class_id'];
        $sectionId = isset($data['section_id']) ? (int) $data['section_id'] : null;

        abort_unless(
            $scope->canMarkClassSection($request->user(), $classId, $sectionId),
            403
        );

        $yearId = $data['academic_year_id']
            ?? AcademicYear::query()->where('is_current', true)->value('id');

        foreach ($data['entries'] as $entry) {
            $student = Student::query()->findOrFail($entry['student_id']);
            $this->assertTenantOwned($student);

            StudentAttendance::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'academic_year_id' => $yearId,
                    'school_class_id' => $classId,
                    'section_id' => $sectionId,
                    'status' => $entry['status'],
                    'notes' => $entry['notes'] ?? null,
                    'marked_by' => $request->user()?->id,
                ]
            );
        }

        return redirect()
            ->route('tenant.attendance.daily.index', [
                'date' => $data['attendance_date'],
                'class_id' => $classId,
                'section_id' => $sectionId,
            ])
            ->with('status', __('ui.student_attendance_saved'));
    }
}
