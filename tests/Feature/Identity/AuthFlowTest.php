<?php

use App\Models\User;
use Modules\Identity\Models\LoginHistory;
use Modules\Identity\Models\TenantMembership;
use Spatie\Permission\Models\Role;

it('logs successful tenant login and records history', function () {
    $registered = $this->registerTenant([
        'slug' => 'login-ok',
        'admin_email' => 'login-ok@example.test',
        'admin_password' => 'password123',
    ]);

    $this->post(route('login.store'), [
        'email' => 'login-ok@example.test',
        'password' => 'password123',
    ])->assertRedirect(route('tenant.dashboard'));

    $this->assertAuthenticatedAs($registered['user']);

    expect(LoginHistory::queryWithoutTenantScope()
        ->where('email', 'login-ok@example.test')
        ->where('event', LoginHistory::EVENT_SUCCESS)
        ->exists())->toBeTrue();
});

it('records failed login attempts', function () {
    $this->post(route('login.store'), [
        'email' => 'nobody@example.test',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    expect(LoginHistory::queryWithoutTenantScope()
        ->where('email', 'nobody@example.test')
        ->where('event', LoginHistory::EVENT_FAILED)
        ->exists())->toBeTrue();
});

it('rejects inactive users at login', function () {
    $registered = $this->registerTenant([
        'slug' => 'inactive-user',
        'admin_email' => 'inactive@example.test',
        'admin_password' => 'password123',
    ]);

    $registered['user']->forceFill(['is_active' => false])->save();

    $this->post(route('login.store'), [
        'email' => 'inactive@example.test',
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('provisions institution admin role and membership on register', function () {
    $registered = $this->registerTenant([
        'slug' => 'rbac-seed',
        'admin_email' => 'rbac@example.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);

    expect($registered['user']->hasRole('Institution Admin'))->toBeTrue();

    expect(TenantMembership::queryWithoutTenantScope()
        ->where('tenant_id', $registered['tenant']->id)
        ->where('user_id', $registered['user']->id)
        ->where('status', TenantMembership::STATUS_ACTIVE)
        ->exists())->toBeTrue();

    expect(Role::query()
        ->where('tenant_id', $registered['tenant']->id)
        ->where('name', 'Teacher')
        ->exists())->toBeTrue();
});

it('shows forgot password form', function () {
    $this->get(route('password.request'))->assertOk()->assertSee('Forgot password', false);
});
