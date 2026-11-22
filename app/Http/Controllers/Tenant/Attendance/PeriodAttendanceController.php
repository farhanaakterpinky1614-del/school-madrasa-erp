<?php

namespace App\Http\Controllers\Tenant\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Attendance\Concerns\LoadsAttendanceFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\ClassRoutine;
use Modules\Attendance\Models\PeriodAttendance;
use Modules\Attendance\Services\TeacherAttendanceScope;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class PeriodAttendanceController extends Controller
{
    use LoadsAttendanceFormOptions;

    public function index(Request $request): View
    {
        $options = $this->attendanceFormOptions($request->user());
        $date = $request->string('date')->toString() ?: now('Asia/Dhaka')->toDateString();
        $dayOfWeek = (int) Carbon::parse($date, 'Asia/Dhaka')->dayOfWeek; // 0=Sun

        $routinesForDay = collect($options['routines'])->filter(
            fn (ClassRoutine $r) => (int) $r->day_of_week === $dayOfWeek
        )->values();

        $routineId = $request->integer('routine_id') ?: $routinesForDay->first()?->id;
        $routine = $routineId
            ? ClassRoutine::query()->with(['schoolClass', 'section', 'period', 'subject', 'staff'])->find($routineId)
            : null;

        $students = collect();
        $existing = collect();

        if ($routine) {
            $enrollments = StudentEnrollment::query()
                ->with('student')
                ->where('school_class_id', $routine->school_class_id)
                ->when($routine->section_id, fn ($q) => $q->where('section_id', $routine->section_id))
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

            $existing = PeriodAttendance::query()
                ->where('class_routine_id', $routine->id)
                ->whereDate('attendance_date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        return view('tenant.attendance.period.index', array_merge($options, [
            'date' => $date,
            'dayOfWeek' => $dayOfWeek,
            'routinesForDay' => $routinesForDay,
            'routineId' => $routineId,
            'routine' => $routine,
            'students' => $students,
            'existing' => $existing,
        ]));
    }

    public function store(Request $request, TeacherAttendanceScope $scope): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'class_routine_id' => ['required', 'integer', Rule::exists('class_routines', 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'entries.*.status' => ['required', Rule::in(PeriodAttendance::STATUSES)],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $routine = ClassRoutine::query()->findOrFail($data['class_routine_id']);
        $this->assertTenantOwned($routine);

        abort_unless(
            $scope->canMarkRoutine($request->user(), $routine),
            403
        );

        foreach ($data['entries'] as $entry) {
            $student = Student::query()->findOrFail($entry['student_id']);
            $this->assertTenantOwned($student);

            PeriodAttendance::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'class_routine_id' => $routine->id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'period_id' => $routine->period_id,
                    'school_class_id' => $routine->school_class_id,
                    'section_id' => $routine->section_id,
                    'subject_id' => $routine->subject_id,
                    'staff_id' => $routine->staff_id,
                    'status' => $entry['status'],
                    'notes' => $entry['notes'] ?? null,
                    'marked_by' => $request->user()?->id,
                ]
            );
        }

        return redirect()
            ->route('tenant.attendance.period.index', [
                'date' => $data['attendance_date'],
                'routine_id' => $routine->id,
            ])
            ->with('status', __('ui.period_attendance_saved'));
    }
}
