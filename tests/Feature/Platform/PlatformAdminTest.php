<?php

use Modules\Platform\Models\Plan;

it('allows platform admin to open the saas console', function () {
    $this->seedPlansAndModules();
    $this->actingAsPlatformAdmin();

    $this->get(route('platform.dashboard'))
        ->assertOk()
        ->assertSee('SaaS console', false);
});

it('forbids tenant admins from platform routes', function () {
    $tenant = $this->registerTenant(['admin_email' => 'tenant-only@example.test']);

    $this->actingAs($tenant['user'])
        ->get(route('platform.dashboard'))
        ->assertForbidden();
});

it('lists plans including starter professional enterprise', function () {
    $this->seedPlansAndModules();
    $this->actingAsPlatformAdmin();

    $this->get(route('platform.plans.index'))
        ->assertOk()
        ->assertSee('Starter', false)
        ->assertSee('Professional', false)
        ->assertSee('Enterprise', false);

    expect(Plan::query()->count())->toBe(3);
});

it('creates a tenant from the platform console', function () {
    $this->seedPlansAndModules();
    $this->actingAsPlatformAdmin();

    $this->post(route('platform.tenants.store'), [
        'institution_name' => 'Platform Created School',
        'slug' => 'platform-school',
        'institution_type' => 'school',
        'plan_code' => 'professional',
        'admin_name' => 'Owner',
        'admin_email' => 'owner@platform-school.test',
        'admin_password' => 'password123',
    ])->assertRedirect();

    $this->assertDatabaseHas('tenants', [
        'slug' => 'platform-school',
        'name' => 'Platform Created School',
    ]);
});

it('lists institutions across tenants for platform admin', function () {
    $this->registerTenant(['slug' => 'list-a', 'institution_name' => 'List A']);
    $this->registerTenant(['slug' => 'list-b', 'institution_name' => 'List B']);
    $this->actingAsPlatformAdmin();

    $this->get(route('platform.institutions.index'))
        ->assertOk()
        ->assertSee('List A', false)
        ->assertSee('List B', false);
});
