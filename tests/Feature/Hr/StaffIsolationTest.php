<?php

use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Hr\Services\StaffIdGenerator;

it('scopes staff to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'hr-a', 'admin_email' => 'hr-a@test.test']);
    $b = $this->registerTenant(['slug' => 'hr-b', 'admin_email' => 'hr-b@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoHrProvisioner::class)->provision($a['tenant']);
    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoHrProvisioner::class)->provision($b['tenant']);

    $this->bindTenant($a['tenant']);
    expect(Staff::query()->count())->toBeGreaterThan(0);

    $foreign = Staff::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();
    expect(Staff::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating staff and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-hr-a', 'admin_email' => 'fha@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-hr-b', 'admin_email' => 'fhb@test.test']);

    $this->bindTenant($a['tenant']);
    $ids = app(StaffIdGenerator::class);

    $staff = Staff::query()->create([
        'tenant_id' => $b['tenant']->id,
        'employee_code' => $ids->nextEmployeeCode(),
        'first_name' => 'Forged',
        'staff_type' => Staff::TYPE_EMPLOYEE,
        'status' => Staff::STATUS_ACTIVE,
    ]);

    expect($staff->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Staff::query()->where('first_name', 'Forged')->exists())->toBeFalse();
});

it('blocks cross-tenant staff show by id', function () {
    $a = $this->registerTenant(['slug' => 'xhr-a', 'admin_email' => 'xha@test.test']);
    $b = $this->registerTenant(['slug' => 'xhr-b', 'admin_email' => 'xhb@test.test']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoHrProvisioner::class)->provision($b['tenant']);

    $foreign = Staff::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();

    $this->actingAs($a['user'])
        ->get(route('tenant.staff.show', $foreign))
        ->assertNotFound();
});
