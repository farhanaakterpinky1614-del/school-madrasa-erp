<?php

use Modules\Platform\Services\ThemePack;

it('sends security headers on web responses', function () {
    $registered = $this->registerTenant(['slug' => 'sec-hdr', 'plan_code' => 'professional', 'admin_email' => 'sech@test.test']);

    $this->actingAs($registered['user'])
        ->withTenantHost('sec-hdr')
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('gates premium theme packs by plan', function () {
    $starter = $this->registerTenant(['slug' => 'theme-st', 'plan_code' => 'starter']);
    $pro = $this->registerTenant(['slug' => 'theme-pro', 'plan_code' => 'professional', 'admin_email' => 'themepro@test.test']);

    $packs = app(ThemePack::class);

    expect($packs->canUsePremium($starter['tenant']))->toBeFalse()
        ->and($packs->canUsePremium($pro['tenant']))->toBeTrue();

    $packs->setPack($starter['tenant'], $starter['institution'], 'navy_gold');
    expect($packs->activePack($starter['tenant'], $starter['institution']->fresh()))->toBe('indigo_slate');

    $packs->setPack($pro['tenant'], $pro['institution'], 'emerald_slate');
    expect($packs->activePack($pro['tenant'], $pro['institution']->fresh()))->toBe('emerald_slate');
});

it('institution admin can open appearance and api token pages', function () {
    $registered = $this->registerTenant(['slug' => 'theme-ui', 'plan_code' => 'professional', 'admin_email' => 'themeui@test.test']);

    $this->actingAs($registered['user'])
        ->withTenantHost('theme-ui')
        ->get(route('tenant.appearance.edit'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->withTenantHost('theme-ui')
        ->get(route('tenant.api.tokens.index'))
        ->assertOk();
});
