<?php

use Modules\Platform\Services\FeatureAccess;

it('gates custom domain and library by plan', function () {
    $starter = $this->registerTenant(['slug' => 'feat-starter', 'plan_code' => 'starter']);
    $enterprise = $this->registerTenant(['slug' => 'feat-ent', 'plan_code' => 'enterprise', 'admin_email' => 'ent@example.test']);

    $access = app(FeatureAccess::class);

    expect($access->enabled($starter['tenant'], 'custom_domain'))->toBeFalse()
        ->and($access->enabled($starter['tenant'], 'module_library'))->toBeFalse()
        ->and($access->enabled($enterprise['tenant'], 'custom_domain'))->toBeTrue()
        ->and($access->enabled($enterprise['tenant'], 'module_library'))->toBeTrue();

    $modules = collect($access->modulesFor($starter['tenant']))->keyBy('key');
    expect($modules['dashboard']['accessible'])->toBeTrue()
        ->and($modules['library']['accessible'])->toBeFalse();
});

it('exposes usage limits from plan features', function () {
    $tenant = $this->registerTenant(['slug' => 'limits-a', 'plan_code' => 'starter']);
    $access = app(FeatureAccess::class);

    expect($access->limit($tenant['tenant'], 'max_students'))->toBe(200)
        ->and($access->limit($tenant['tenant'], 'max_staff'))->toBe(20);
});
