<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Identity\Models\TenantMembership;
use Spatie\Permission\Models\Role;

it('allows institution admin to open setup screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-setup',
        'admin_email' => 'admin-setup@setup.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.branches.index'))->assertOk()
        ->assertSee('Main Campus', false);

    $this->actingAs($registered['user'])
        ->get(route('tenant.classes.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.settings.edit'))->assertOk()
        ->assertSee('BDT', false)
        ->assertSee('Asia/Dhaka', false);
});

it('denies teacher from managing branches', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-setup',
        'admin_email' => 'teacher-admin@setup.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Only',
        'email' => 'teacher-only@setup.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $teacher->assignRole($teacherRole);

    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $teacher->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->get(route('tenant.branches.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.branches.create'))
        ->assertForbidden();
});

it('lets academic officer manage classes but not institution branding settings update route without permission', function () {
    $registered = $this->registerTenant([
        'slug' => 'officer-setup',
        'admin_email' => 'officer-admin@setup.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $role = Role::findByName('Academic Officer', 'web');

    $officer = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Academic Officer',
        'email' => 'officer@setup.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $officer->assignRole($role);

    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $officer->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $this->actingAs($officer)
        ->get(route('tenant.classes.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('tenant.settings.edit'))
        ->assertForbidden();
});
