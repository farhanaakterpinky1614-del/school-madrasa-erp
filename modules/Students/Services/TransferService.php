<?php

namespace Modules\Students\Services;

use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;
use Modules\Students\Models\StudentTransfer;

class TransferService
{
    public function __construct(protected EnrollmentService $enrollments) {}

    /**
     * @param  array{academic_year_id:int,to_class_id:int,to_section_id?:?int,shift_id?:?int,roll_number?:?string,transferred_on?:?string,reason?:?string,notes?:?string,created_by?:?int}  $data
     */
    public function transfer(Student $student, array $data): StudentTransfer
    {
        return DB::transaction(function () use ($student, $data) {
            $current = $student->enrollments()->where('is_current', true)->first();

            if ($current) {
                $current->update([
                    'is_current' => false,
                    'status' => StudentEnrollment::STATUS_TRANSFERRED,
                    'ended_on' => $data['transferred_on'] ?? now()->toDateString(),
                ]);
            }

            $newEnrollment = $this->enrollments->enroll($student, [
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $data['to_class_id'],
                'section_id' => $data['to_section_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? $current?->shift_id,
                'roll_number' => $data['roll_number'] ?? null,
                'enrolled_on' => $data['transferred_on'] ?? now()->toDateString(),
            ]);

            return StudentTransfer::query()->create([
                'student_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'from_enrollment_id' => $current?->id,
                'to_enrollment_id' => $newEnrollment->id,
                'from_class_id' => $current?->school_class_id,
                'from_section_id' => $current?->section_id,
                'to_class_id' => $data['to_class_id'],
                'to_section_id' => $data['to_section_id'] ?? null,
                'transferred_on' => $data['transferred_on'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);
        });
    }
}
