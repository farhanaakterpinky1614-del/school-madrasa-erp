<?php

use Modules\Tenancy\Models\TenantDomain;
use Modules\Tenancy\Services\DomainVerificationService;

it('verifies custom domain via dns txt override', function () {
    $registered = $this->registerTenant([
        'slug' => 'dom-ent',
        'plan_code' => 'enterprise',
        'admin_email' => 'doment@test.test',
    ]);
    $this->bindTenant($registered['tenant']);

    $domain = TenantDomain::query()->create([
        'host' => 'school.example.edu.bd',
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verification_status' => TenantDomain::STATUS_PENDING,
    ]);
    $domain->beginVerification();

    $service = app(DomainVerificationService::class);
    config([
        'sme.domain_verification.driver' => 'dns',
        'sme.domain_verification.txt_overrides' => [
            $service->txtName($domain) => $service->expectedTxtValue($domain),
        ],
    ]);

    $result = $service->verify($domain->fresh());

    expect($result['verified'])->toBeTrue()
        ->and($result['method'])->toBe('dns_txt')
        ->and($domain->fresh()->isVerified())->toBeTrue();
});

it('fails verification when txt missing', function () {
    $registered = $this->registerTenant([
        'slug' => 'dom-fail',
        'plan_code' => 'enterprise',
        'admin_email' => 'domfail@test.test',
    ]);
    $this->bindTenant($registered['tenant']);

    $domain = TenantDomain::query()->create([
        'host' => 'missing.example.edu.bd',
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verification_status' => TenantDomain::STATUS_PENDING,
    ]);
    $domain->beginVerification();

    config([
        'sme.domain_verification.driver' => 'dns',
        'sme.domain_verification.txt_overrides' => [],
        'sme.domain_verification.allow_live_http' => false,
    ]);

    $result = app(DomainVerificationService::class)->verify($domain->fresh());

    expect($result['verified'])->toBeFalse()
        ->and($domain->fresh()->verification_status)->toBe(TenantDomain::STATUS_FAILED);
});

it('enterprise admin can verify via http route with always_pass driver', function () {
    config(['sme.domain_verification.driver' => 'always_pass']);

    $registered = $this->registerTenant([
        'slug' => 'dom-ui',
        'plan_code' => 'enterprise',
        'admin_email' => 'domui@test.test',
    ]);

    $this->bindTenant($registered['tenant']);
    $domain = TenantDomain::query()->create([
        'host' => 'ui.example.edu.bd',
        'type' => TenantDomain::TYPE_CUSTOM,
        'is_primary' => false,
        'verification_status' => TenantDomain::STATUS_PENDING,
    ]);
    $domain->beginVerification();

    $this->actingAs($registered['user'])
        ->withTenantHost('dom-ui')
        ->post(route('tenant.domains.verify', $domain))
        ->assertRedirect();

    expect($domain->fresh()->isVerified())->toBeTrue();
});
