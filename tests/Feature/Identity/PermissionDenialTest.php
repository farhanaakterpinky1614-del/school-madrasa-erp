<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Models\TenantMembership;
use Spatie\Permission\Models\Role;

it('denies staff without settings.users.view from users index', function () {
    $registered = $this->registerTenant([
        'slug' => 'deny-users',
        'admin_email' => 'admin-deny@example.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);

    $teacherRole = Role::findByName('Teacher', 'web');

    $staff = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher User',
        'email' => 'teacher-deny@example.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $staff->assignRole($teacherRole);

    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $staff->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get(route('tenant.users.index'))
        ->assertForbidden();
});

it('allows institution admin to open users and roles', function () {
    $registered = $this->registerTenant([
        'slug' => 'allow-admin',
        'admin_email' => 'admin-allow@example.test',
    ]);

    $this->actingAs($registered['user'])
        ->get(route('tenant.users.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.roles.index'))
        ->assertOk();
});

it('denies creating roles without settings.roles.create', function () {
    $registered = $this->registerTenant([
        'slug' => 'deny-roles',
        'admin_email' => 'admin-roles@example.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $staff = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'No Role Builder',
        'email' => 'norole@example.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $staff->assignRole($teacherRole);

    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $staff->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $this->actingAs($staff)
        ->post(route('tenant.roles.store'), [
            'name' => 'Hacker Role',
            'permissions' => ['settings.users.view'],
        ])
        ->assertForbidden();
});
