<?php

namespace Modules\Students\Services;

use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class EnrollmentService
{
    /**
     * @param  array{academic_year_id:int,school_class_id:int,section_id?:?int,shift_id?:?int,roll_number?:?string,enrolled_on?:?string}  $data
     */
    public function enroll(Student $student, array $data): StudentEnrollment
    {
        return DB::transaction(function () use ($student, $data) {
            StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'status' => StudentEnrollment::STATUS_COMPLETED,
                    'ended_on' => $data['enrolled_on'] ?? now()->toDateString(),
                ]);

            $enrollment = StudentEnrollment::query()->create([
                'student_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $data['school_class_id'],
                'section_id' => $data['section_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
                'roll_number' => $data['roll_number'] ?? null,
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'is_current' => true,
                'enrolled_on' => $data['enrolled_on'] ?? now()->toDateString(),
            ]);

            if ($student->status !== Student::STATUS_ACTIVE) {
                $student->update([
                    'status' => Student::STATUS_ACTIVE,
                    'enrolled_on' => $student->enrolled_on ?: ($data['enrolled_on'] ?? now()->toDateString()),
                    'withdrawn_on' => null,
                    'withdrawal_reason' => null,
                ]);
            } elseif (! $student->enrolled_on) {
                $student->update(['enrolled_on' => $data['enrolled_on'] ?? now()->toDateString()]);
            }

            return $enrollment;
        });
    }
}
