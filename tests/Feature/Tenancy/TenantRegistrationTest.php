<?php

use App\Models\User;
use Modules\Platform\Models\TenantSubscription;
use Modules\Tenancy\Models\Tenant;
use Modules\Tenancy\Models\TenantDomain;

it('registers a tenant with institution, domain, trial subscription, and admin', function () {
    $this->seedPlansAndModules();

    $response = $this->post(route('register.store'), [
        'institution_name' => 'Nurani Madrasa',
        'slug' => 'nurani',
        'institution_type' => 'alia',
        'plan_code' => 'starter',
        'admin_name' => 'Rahim Uddin',
        'admin_email' => 'rahim@nurani.test',
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
        'phone' => '01700000000',
    ]);

    $response->assertRedirect(route('tenant.dashboard'));

    $tenant = Tenant::query()->where('slug', 'nurani')->first();
    expect($tenant)->not->toBeNull()
        ->and($tenant->status)->toBe(Tenant::STATUS_ACTIVE)
        ->and($tenant->institution->name)->toBe('Nurani Madrasa');

    expect(TenantDomain::queryWithoutTenantScope()->where('host', 'nurani.'.config('sme.tenancy.base_domain'))->exists())->toBeTrue();
    expect(TenantSubscription::query()->where('tenant_id', $tenant->id)->where('status', 'trialing')->exists())->toBeTrue();
    expect(User::query()->where('email', 'rahim@nurani.test')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('rejects reserved slugs', function () {
    $this->seedPlansAndModules();

    $this->from(route('register'))
        ->post(route('register.store'), [
            'institution_name' => 'Bad',
            'slug' => 'admin',
            'institution_type' => 'school',
            'plan_code' => 'starter',
            'admin_name' => 'A',
            'admin_email' => 'a@example.test',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('slug');
});
