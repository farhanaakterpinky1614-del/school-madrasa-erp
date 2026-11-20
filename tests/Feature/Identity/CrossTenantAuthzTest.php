<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Services\RoleProvisioner;
use Spatie\Permission\Models\Role;

it('blocks editing another tenant user by id', function () {
    $a = $this->registerTenant(['slug' => 'authz-a', 'admin_email' => 'a@authz.test']);
    $b = $this->registerTenant(['slug' => 'authz-b', 'admin_email' => 'b@authz.test']);

    $this->actingAs($a['user'])
        ->get(route('tenant.users.edit', $b['user']))
        ->assertNotFound();
});

it('blocks assigning roles across tenants', function () {
    $a = $this->registerTenant(['slug' => 'role-a', 'admin_email' => 'ra@authz.test']);
    $b = $this->registerTenant(['slug' => 'role-b', 'admin_email' => 'rb@authz.test']);

    setPermissionsTeamId($b['tenant']->id);
    $foreignRole = Role::findByName('Teacher', 'web');

    $this->actingAs($a['user'])
        ->get(route('tenant.roles.edit', $foreignRole))
        ->assertNotFound();
});

it('isolates custom roles to the creating tenant', function () {
    $a = $this->registerTenant(['slug' => 'custom-a', 'admin_email' => 'ca@authz.test']);
    $b = $this->registerTenant(['slug' => 'custom-b', 'admin_email' => 'cb@authz.test']);

    $this->actingAs($a['user'])
        ->post(route('tenant.roles.store'), [
            'name' => 'Campus Lead',
            'permissions' => ['settings.users.view', 'branches.branch.view'],
        ])
        ->assertRedirect(route('tenant.roles.index'));

    setPermissionsTeamId($a['tenant']->id);
    expect(Role::query()->where('tenant_id', $a['tenant']->id)->where('name', 'Campus Lead')->exists())->toBeTrue();

    setPermissionsTeamId($b['tenant']->id);
    expect(Role::query()->where('tenant_id', $b['tenant']->id)->where('name', 'Campus Lead')->exists())->toBeFalse();
});

it('does not leak tenant users in another tenant users index', function () {
    $a = $this->registerTenant(['slug' => 'list-a', 'admin_email' => 'la@authz.test', 'admin_name' => 'Alpha Admin']);
    $b = $this->registerTenant(['slug' => 'list-b', 'admin_email' => 'lb@authz.test', 'admin_name' => 'Beta Admin']);

    $this->actingAs($a['user'])
        ->get(route('tenant.users.index'))
        ->assertOk()
        ->assertSee('Alpha Admin', false)
        ->assertDontSee('Beta Admin', false);
});

it('staff from tenant A cannot access tenant B host even with matching permissions', function () {
    $a = $this->registerTenant(['slug' => 'host-authz-a', 'admin_email' => 'ha@authz.test']);
    $b = $this->registerTenant(['slug' => 'host-authz-b', 'admin_email' => 'hb@authz.test']);

    setPermissionsTeamId($a['tenant']->id);
    $adminRole = Role::findByName('Institution Admin', 'web');

    $staff = User::query()->create([
        'tenant_id' => $a['tenant']->id,
        'name' => 'Cross Host',
        'email' => 'crosshost@authz.test',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $staff->assignRole($adminRole);

    app(RoleProvisioner::class); // ensure catalog exists

    $host = 'host-authz-b.'.config('sme.tenancy.base_domain');

    $this->actingAs($staff)
        ->get('http://'.$host.'/app/users')
        ->assertForbidden();
});
