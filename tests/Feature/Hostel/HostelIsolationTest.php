<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelAllocation;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Transport\Services\DemoTransportHostelProvisioner;

it('scopes hostels and allocations to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'hs-a', 'admin_email' => 'hs-a@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'hs-b', 'admin_email' => 'hs-b@test.test', 'plan_code' => 'professional']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoFeeProvisioner::class)->provision($registered['tenant']);
        app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(Hostel::query()->count())->toBeGreaterThan(0);
    expect(HostelAllocation::query()->count())->toBeGreaterThan(0);

    $foreignHostel = Hostel::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignAllocation = HostelAllocation::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(Hostel::query()->find($foreignHostel->id))->toBeNull();
    expect(HostelAllocation::query()->find($foreignAllocation->id))->toBeNull();
});
