<?php

namespace Modules\Admissions\Services;

use Illuminate\Support\Facades\DB;
use Modules\Admissions\Models\AdmissionApplication;
use Modules\Students\Models\Guardian;
use Modules\Students\Models\Student;
use Modules\Students\Services\EnrollmentService;
use Modules\Students\Services\StudentIdGenerator;

class AdmissionWorkflowService
{
    public function __construct(
        protected StudentIdGenerator $ids,
        protected EnrollmentService $enrollments,
    ) {}

    public function markUnderReview(AdmissionApplication $application, ?int $userId = null): AdmissionApplication
    {
        $this->assertStatus($application, [AdmissionApplication::STATUS_SUBMITTED, AdmissionApplication::STATUS_UNDER_REVIEW]);

        $application->update([
            'status' => AdmissionApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => $userId ?? $application->reviewed_by,
        ]);

        return $application->fresh();
    }

    public function approve(AdmissionApplication $application, ?int $userId = null, ?string $notes = null): AdmissionApplication
    {
        $this->assertStatus($application, [
            AdmissionApplication::STATUS_SUBMITTED,
            AdmissionApplication::STATUS_UNDER_REVIEW,
        ]);

        $application->update([
            'status' => AdmissionApplication::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
            'decision_notes' => $notes,
        ]);

        return $application->fresh();
    }

    public function reject(AdmissionApplication $application, ?int $userId = null, ?string $notes = null): AdmissionApplication
    {
        $this->assertStatus($application, [
            AdmissionApplication::STATUS_SUBMITTED,
            AdmissionApplication::STATUS_UNDER_REVIEW,
            AdmissionApplication::STATUS_APPROVED,
        ]);

        $application->update([
            'status' => AdmissionApplication::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
            'decision_notes' => $notes,
        ]);

        return $application->fresh();
    }

    /**
     * Convert approved application → student + guardian + enrollment.
     *
     * @param  array{academic_year_id:int,school_class_id:int,section_id?:?int,shift_id?:?int,roll_number?:?string}  $enrollment
     */
    public function enroll(AdmissionApplication $application, array $enrollment, ?int $userId = null): AdmissionApplication
    {
        $this->assertStatus($application, [AdmissionApplication::STATUS_APPROVED]);

        return DB::transaction(function () use ($application, $enrollment, $userId) {
            $student = Student::query()->create([
                'student_code' => $this->ids->nextStudentCode(),
                'admission_number' => $this->ids->nextAdmissionNumber(),
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'name_bn' => $application->name_bn,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'phone' => $application->phone,
                'email' => $application->email,
                'address' => $application->address,
                'status' => Student::STATUS_ACTIVE,
                'enrolled_on' => now()->toDateString(),
                'custom_fields' => $application->form_payload,
            ]);
            $student->update(['qr_payload' => $this->ids->qrPayload($student)]);

            if ($application->guardian_name) {
                $guardian = Guardian::query()->create([
                    'name' => $application->guardian_name,
                    'phone' => $application->guardian_phone,
                ]);
                $student->guardians()->attach($guardian->id, [
                    'tenant_id' => $student->tenant_id,
                    'relationship' => $application->guardian_relation ?: 'guardian',
                    'is_primary' => true,
                    'is_emergency' => true,
                    'can_pickup' => true,
                ]);
            }

            $this->enrollments->enroll($student, [
                'academic_year_id' => $enrollment['academic_year_id'],
                'school_class_id' => $enrollment['school_class_id'],
                'section_id' => $enrollment['section_id'] ?? null,
                'shift_id' => $enrollment['shift_id'] ?? null,
                'roll_number' => $enrollment['roll_number'] ?? null,
            ]);

            $application->update([
                'status' => AdmissionApplication::STATUS_ENROLLED,
                'student_id' => $student->id,
                'reviewed_by' => $userId ?? $application->reviewed_by,
                'reviewed_at' => $application->reviewed_at ?? now(),
            ]);

            return $application->fresh(['student']);
        });
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function assertStatus(AdmissionApplication $application, array $allowed): void
    {
        abort_unless(in_array($application->status, $allowed, true), 422, 'Invalid application status transition.');
    }
}
