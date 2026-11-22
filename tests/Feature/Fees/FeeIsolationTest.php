<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes fee categories and invoices to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'fee-a', 'admin_email' => 'fee-a@test.test']);
    $b = $this->registerTenant(['slug' => 'fee-b', 'admin_email' => 'fee-b@test.test']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(FeeCategory::query()->count())->toBeGreaterThan(0);
    expect(FeeInvoice::query()->count())->toBeGreaterThan(0);

    $foreignCat = FeeCategory::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignInv = FeeInvoice::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(FeeCategory::query()->find($foreignCat->id))->toBeNull();
    expect(FeeInvoice::query()->find($foreignInv->id))->toBeNull();
});

it('forces tenant_id when creating a fee category and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-fee-a', 'admin_email' => 'ffa@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-fee-b', 'admin_email' => 'ffb@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    $this->bindTenant($a['tenant']);

    $row = FeeCategory::query()->create([
        'tenant_id' => $b['tenant']->id,
        'name' => 'Forged Tuition',
        'code' => 'FORGE-TUI',
        'kind' => FeeCategory::KIND_TUITION,
        'is_active' => true,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(FeeCategory::query()->where('code', 'FORGE-TUI')->exists())->toBeFalse();
});
