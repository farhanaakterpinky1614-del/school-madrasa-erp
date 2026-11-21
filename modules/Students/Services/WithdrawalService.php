<?php

namespace Modules\Students\Services;

use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class WithdrawalService
{
    /**
     * @param  array{withdrawn_on?:?string,reason?:?string,mark_alumni?:bool}  $data
     */
    public function withdraw(Student $student, array $data = []): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $asAlumni = (bool) ($data['mark_alumni'] ?? false);
            $on = $data['withdrawn_on'] ?? now()->toDateString();

            StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'status' => StudentEnrollment::STATUS_WITHDRAWN,
                    'ended_on' => $on,
                ]);

            $student->update([
                'status' => $asAlumni ? Student::STATUS_ALUMNI : Student::STATUS_WITHDRAWN,
                'withdrawn_on' => $on,
                'alumni_on' => $asAlumni ? $on : $student->alumni_on,
                'withdrawal_reason' => $data['reason'] ?? null,
            ]);

            return $student->fresh();
        });
    }

    public function markAlumni(Student $student, ?string $on = null): Student
    {
        $student->update([
            'status' => Student::STATUS_ALUMNI,
            'alumni_on' => $on ?? now()->toDateString(),
        ]);

        return $student->fresh();
    }
}
