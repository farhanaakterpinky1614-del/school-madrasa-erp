<?php

namespace Modules\Attendance\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Attendance\Models\AttendanceQrToken;
use Modules\Attendance\Models\PeriodAttendance;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo daily + period attendance and a sample QR token.
 */
class DemoAttendanceProvisioner
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->first();
            $class = SchoolClass::query()->orderBy('numeric_level')->first();
            $section = $class
                ? Section::query()->where('school_class_id', $class->id)->orderBy('name')->first()
                : null;

            if (! $class || ! $year) {
                return;
            }

            $enrollments = StudentEnrollment::query()
                ->where('school_class_id', $class->id)
                ->when($section, fn ($q) => $q->where('section_id', $section->id))
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->with('student')
                ->orderBy('roll_number')
                ->get();

            if ($enrollments->isEmpty()) {
                $students = Student::query()->where('status', Student::STATUS_ACTIVE)->orderBy('id')->limit(5)->get();
            } else {
                $students = $enrollments->pluck('student')->filter();
            }

            $today = now('Asia/Dhaka')->toDateString();
            $yesterday = now('Asia/Dhaka')->subDay()->toDateString();

            foreach ([$yesterday, $today] as $dateIndex => $date) {
                foreach ($students as $i => $student) {
                    if (! $student) {
                        continue;
                    }
                    $status = match (true) {
                        $i === 1 && $dateIndex === 0 => StudentAttendance::STATUS_ABSENT,
                        $i === 2 => StudentAttendance::STATUS_LATE,
                        $i === 3 && $dateIndex === 1 => StudentAttendance::STATUS_EXCUSED,
                        default => StudentAttendance::STATUS_PRESENT,
                    };

                    StudentAttendance::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'student_id' => $student->id,
                            'attendance_date' => $date,
                        ],
                        [
                            'academic_year_id' => $year->id,
                            'school_class_id' => $class->id,
                            'section_id' => $section?->id,
                            'status' => $status,
                            'notes' => $status === StudentAttendance::STATUS_ABSENT ? 'Demo absent' : null,
                        ]
                    );
                }
            }

            $routine = ClassRoutine::query()
                ->where('school_class_id', $class->id)
                ->where('is_active', true)
                ->orderBy('day_of_week')
                ->first();

            if ($routine && $students->isNotEmpty()) {
                foreach ($students->take(4) as $i => $student) {
                    PeriodAttendance::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'student_id' => $student->id,
                            'class_routine_id' => $routine->id,
                            'attendance_date' => $today,
                        ],
                        [
                            'period_id' => $routine->period_id,
                            'school_class_id' => $class->id,
                            'section_id' => $section?->id ?? $routine->section_id,
                            'subject_id' => $routine->subject_id,
                            'staff_id' => $routine->staff_id,
                            'status' => $i === 1 ? PeriodAttendance::STATUS_ABSENT : PeriodAttendance::STATUS_PRESENT,
                        ]
                    );
                }

                AttendanceQrToken::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'class_routine_id' => $routine->id,
                        'valid_on' => $today,
                    ],
                    [
                        'token' => Str::lower(Str::random(32)),
                        'expires_at' => now('Asia/Dhaka')->endOfDay(),
                    ]
                );
            }
        });
    }
}
