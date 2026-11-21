<?php

namespace Modules\Students\Services;

use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;
use Modules\Students\Models\StudentPromotion;

class PromotionService
{
    public function __construct(protected EnrollmentService $enrollments) {}

    /**
     * Foundation: promote one student into a new year/class/section.
     *
     * @param  array{to_year_id:int,to_class_id:int,to_section_id?:?int,shift_id?:?int,roll_number?:?string,promoted_on?:?string,notes?:?string,created_by?:?int}  $data
     */
    public function promote(Student $student, array $data): StudentPromotion
    {
        return DB::transaction(function () use ($student, $data) {
            $current = $student->enrollments()->where('is_current', true)->first();

            if ($current) {
                $current->update([
                    'is_current' => false,
                    'status' => StudentEnrollment::STATUS_COMPLETED,
                    'ended_on' => $data['promoted_on'] ?? now()->toDateString(),
                ]);
            }

            $newEnrollment = $this->enrollments->enroll($student, [
                'academic_year_id' => $data['to_year_id'],
                'school_class_id' => $data['to_class_id'],
                'section_id' => $data['to_section_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? $current?->shift_id,
                'roll_number' => $data['roll_number'] ?? null,
                'enrolled_on' => $data['promoted_on'] ?? now()->toDateString(),
            ]);

            return StudentPromotion::query()->create([
                'student_id' => $student->id,
                'from_enrollment_id' => $current?->id,
                'to_enrollment_id' => $newEnrollment->id,
                'from_year_id' => $current?->academic_year_id ?? $data['to_year_id'],
                'to_year_id' => $data['to_year_id'],
                'from_class_id' => $current?->school_class_id,
                'to_class_id' => $data['to_class_id'],
                'to_section_id' => $data['to_section_id'] ?? null,
                'status' => 'completed',
                'promoted_on' => $data['promoted_on'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);
        });
    }
}
