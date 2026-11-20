<?php

use Modules\Academics\Models\Branch;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Services\DemoStructureProvisioner;

it('scopes academic setup records to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'setup-a', 'institution_name' => 'Setup A', 'admin_email' => 'a@setup.test']);
    $b = $this->registerTenant(['slug' => 'setup-b', 'institution_name' => 'Setup B', 'admin_email' => 'b@setup.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoStructureProvisioner::class)->provision($b['tenant']);

    $this->bindTenant($a['tenant']);

    $visibleBranches = Branch::query()->pluck('name')->all();
    expect($visibleBranches)->toContain('Main Campus')
        ->and(Branch::query()->count())->toBe(1);

    $this->bindTenant($b['tenant']);
    $foreign = Branch::queryWithoutTenantScope()->where('tenant_id', $a['tenant']->id)->first();
    expect(Branch::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating a class and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-class-a', 'admin_email' => 'fca@setup.test']);
    $b = $this->registerTenant(['slug' => 'forge-class-b', 'admin_email' => 'fcb@setup.test']);

    $this->bindTenant($a['tenant']);

    $class = SchoolClass::query()->create([
        'tenant_id' => $b['tenant']->id,
        'name' => 'Forged Class',
        'code' => 'FX',
        'is_active' => true,
    ]);

    expect($class->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(SchoolClass::query()->where('code', 'FX')->exists())->toBeFalse();
});

it('blocks cross-tenant branch edit by id', function () {
    $a = $this->registerTenant(['slug' => 'branch-a', 'admin_email' => 'ba@setup.test']);
    $b = $this->registerTenant(['slug' => 'branch-b', 'admin_email' => 'bb@setup.test']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);

    $foreignBranch = Branch::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();

    $this->actingAs($a['user'])
        ->get(route('tenant.branches.edit', $foreignBranch))
        ->assertNotFound();
});
