<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Communications\Services\DemoCommunicationsProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open communications screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-comm',
        'admin_email' => 'admin-comm@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoCommunicationsProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.announcements.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.templates.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.send.create'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.logs.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.settings.edit'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.inbox.index'))
        ->assertOk();
});

it('allows communications officer and teacher inbox; denies teacher manage screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'role-comm',
        'admin_email' => 'role-comm-admin@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoCommunicationsProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $officerRole = Role::findByName('Communications Officer', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');
    $parentRole = Role::findByName('Parent', 'web');

    $officer = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Communications Officer',
        'email' => 'comms-officer@test.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $officer->assignRole($officerRole);
    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $officer->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Comm',
        'email' => 'teacher-comm@test.test',
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

    $parent = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Parent Comm',
        'email' => 'parent-comm@test.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $parent->assignRole($parentRole);
    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $parent->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $this->actingAs($officer)
        ->get(route('tenant.communications.announcements.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('tenant.communications.send.create'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.communications.inbox.index'))
        ->assertOk();

    $this->actingAs($parent)
        ->get(route('tenant.communications.inbox.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.communications.announcements.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.communications.send.create'))
        ->assertForbidden();

    $this->actingAs($parent)
        ->get(route('tenant.communications.templates.index'))
        ->assertForbidden();
});
