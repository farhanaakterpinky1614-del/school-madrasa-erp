<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Transport\Models\TransportAssignment;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;
use Modules\Transport\Services\DemoTransportHostelProvisioner;

it('scopes transport vehicles and routes to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'tr-a', 'admin_email' => 'tr-a@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'tr-b', 'admin_email' => 'tr-b@test.test', 'plan_code' => 'professional']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoFeeProvisioner::class)->provision($registered['tenant']);
        app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(Vehicle::query()->count())->toBeGreaterThan(0);
    expect(TransportRoute::query()->count())->toBeGreaterThan(0);
    expect(TransportAssignment::query()->count())->toBeGreaterThan(0);

    $foreignVehicle = Vehicle::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignRoute = TransportRoute::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(Vehicle::query()->find($foreignVehicle->id))->toBeNull();
    expect(TransportRoute::query()->find($foreignRoute->id))->toBeNull();
});

it('forces tenant_id when creating a vehicle and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-tr-a', 'admin_email' => 'fta@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'forge-tr-b', 'admin_email' => 'ftb@test.test', 'plan_code' => 'professional']);

    $this->bindTenant($a['tenant']);

    $row = Vehicle::query()->create([
        'tenant_id' => $b['tenant']->id,
        'plate_number' => 'FORGE-PLATE-1',
        'type' => Vehicle::TYPE_BUS,
        'capacity' => 20,
        'status' => Vehicle::STATUS_ACTIVE,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Vehicle::query()->where('plate_number', 'FORGE-PLATE-1')->exists())->toBeFalse();
});
