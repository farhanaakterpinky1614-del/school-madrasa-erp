<?php

namespace Modules\Hr\Services;

use App\Core\Tenancy\CurrentTenant;
use Modules\Hr\Models\Staff;

class StaffIdGenerator
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    public function nextEmployeeCode(?int $year = null): string
    {
        $year ??= (int) now('Asia/Dhaka')->format('Y');
        $tenantId = $this->currentTenant->id();
        $prefix = 'EMP-'.$year.'-';

        $latest = Staff::queryWithoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('employee_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('employee_code');

        $seq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
