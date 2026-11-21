<?php

namespace App\Http\Controllers\Tenant\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Attendance\Concerns\LoadsAttendanceFormOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class AttendanceReportController extends Controller
{
    use LoadsAttendanceFormOptions;

    public function index(Request $request): View
    {
        $options = $this->attendanceFormOptions($request->user());
        $from = $request->string('from')->toString() ?: now('Asia/Dhaka')->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now('Asia/Dhaka')->toDateString();
        $classId = $request->integer('class_id') ?: null;
        $sectionId = $request->filled('section_id') && $request->input('section_id') !== ''
            ? $request->integer('section_id')
            : null;
        $date = $request->string('summary_date')->toString() ?: now('Asia/Dhaka')->toDateString();

        $dailySummary = null;
        if ($classId) {
            $rows = StudentAttendance::query()
                ->where('school_class_id', $classId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->whereDate('attendance_date', $date)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $dailySummary = [
                'date' => $date,
                'counts' => collect(StudentAttendance::STATUSES)->mapWithKeys(
                    fn ($s) => [$s => (int) ($rows[$s] ?? 0)]
                ),
                'total' => (int) $rows->sum(),
            ];
        }

        $percentageRows = collect();
        $absentees = collect();

        if ($classId) {
            $studentIds = StudentEnrollment::query()
                ->where('school_class_id', $classId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('is_current', true)
                ->pluck('student_id');

            $stats = StudentAttendance::query()
                ->whereIn('student_id', $studentIds)
                ->whereBetween('attendance_date', [$from, $to])
                ->select(
                    'student_id',
                    DB::raw('count(*) as marked_days'),
                    DB::raw("sum(case when status = 'present' then 1 else 0 end) as present_days"),
                    DB::raw("sum(case when status = 'late' then 1 else 0 end) as late_days"),
                    DB::raw("sum(case when status = 'absent' then 1 else 0 end) as absent_days"),
                    DB::raw("sum(case when status in ('excused','leave') then 1 else 0 end) as excused_days")
                )
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            $students = Student::query()->whereIn('id', $studentIds)->orderBy('first_name')->get();

            $percentageRows = $students->map(function (Student $student) use ($stats) {
                $row = $stats->get($student->id);
                $marked = (int) ($row->marked_days ?? 0);
                $presentish = (int) ($row->present_days ?? 0) + (int) ($row->late_days ?? 0);
                $pct = $marked > 0 ? round(($presentish / $marked) * 100, 1) : null;

                return (object) [
                    'student' => $student,
                    'marked_days' => $marked,
                    'present_days' => (int) ($row->present_days ?? 0),
                    'late_days' => (int) ($row->late_days ?? 0),
                    'absent_days' => (int) ($row->absent_days ?? 0),
                    'excused_days' => (int) ($row->excused_days ?? 0),
                    'percentage' => $pct,
                ];
            });

            $absentees = StudentAttendance::query()
                ->with('student')
                ->where('school_class_id', $classId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->whereBetween('attendance_date', [$from, $to])
                ->where('status', StudentAttendance::STATUS_ABSENT)
                ->orderByDesc('attendance_date')
                ->limit(50)
                ->get();
        }

        return view('tenant.attendance.reports.index', array_merge($options, [
            'from' => $from,
            'to' => $to,
            'classId' => $classId,
            'sectionId' => $sectionId,
            'summaryDate' => $date,
            'dailySummary' => $dailySummary,
            'percentageRows' => $percentageRows,
            'absentees' => $absentees,
        ]));
    }
}
