<?php

namespace Modules\Reports\Services;

use App\Core\Tenancy\CurrentTenant;
use Modules\Hr\Models\Staff;

class StaffQrIdCardService
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected QrCredentialService $qr,
    ) {}

    public function payload(Staff $staff): string
    {
        $slug = $this->currentTenant->get()?->slug ?? 'tenant';

        return 'sme://'.$slug.'/staff/'.$staff->employee_code;
    }

    public function verifyStubUrl(Staff $staff): string
    {
        $slug = $this->currentTenant->get()?->slug ?? 'tenant';

        return url('/verify/'.$slug.'/staff/'.$staff->employee_code);
    }

    public function dataUri(Staff $staff): string
    {
        return $this->qr->dataUri($this->payload($staff));
    }
}
