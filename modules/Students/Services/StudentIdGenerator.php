<?php

namespace Modules\Students\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Str;
use Modules\Students\Models\Student;

class StudentIdGenerator
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    public function nextStudentCode(?int $year = null): string
    {
        $year ??= (int) now('Asia/Dhaka')->format('Y');
        $tenantId = $this->currentTenant->id();
        $prefix = 'STU-'.$year.'-';

        $latest = Student::queryWithoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('student_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('student_code');

        $seq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function nextAdmissionNumber(?int $year = null): string
    {
        $year ??= (int) now('Asia/Dhaka')->format('Y');
        $tenantId = $this->currentTenant->id();
        $prefix = 'ADM-'.$year.'-';

        $latest = Student::queryWithoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('admission_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('admission_number');

        $seq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function qrPayload(Student $student): string
    {
        $slug = $this->currentTenant->get()?->slug ?? 'tenant';

        return 'sme://'.$slug.'/student/'.$student->student_code;
    }

    public function nextApplicationNumber(): string
    {
        return 'APP-'.now('Asia/Dhaka')->format('Ymd').'-'.Str::upper(Str::random(4));
    }
}
