<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Transport\Services\DemoTransportHostelProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open transport screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-tr',
        'admin_email' => 'admin-tr@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.transport.vehicles.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.transport.routes.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.transport.assignments.index'))
        ->assertOk();
});

it('allows transport officer but denies teacher transport screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'role-tr',
        'admin_email' => 'role-tr-admin@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $officerRole = Role::findByName('Transport Officer', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');

    $officer = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Transport Officer',
        'email' => 'transport-officer@test.test',
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
        'name' => 'Teacher TR',
        'email' => 'teacher-tr@test.test',
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

    $this->actingAs($officer)
        ->get(route('tenant.transport.vehicles.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('tenant.transport.assignments.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.transport.vehicles.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.transport.assignments.index'))
        ->assertForbidden();
});
