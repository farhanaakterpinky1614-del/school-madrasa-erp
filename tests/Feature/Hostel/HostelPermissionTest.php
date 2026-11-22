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

it('allows institution admin to open hostel screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-hs',
        'admin_email' => 'admin-hs@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.hostel.hostels.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hostel.rooms.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hostel.allocations.index'))
        ->assertOk();
});

it('allows hostel warden but denies teacher hostel screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'role-hs',
        'admin_email' => 'role-hs-admin@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $wardenRole = Role::findByName('Hostel Warden', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');

    $warden = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Hostel Warden',
        'email' => 'hostel-warden@test.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $warden->assignRole($wardenRole);
    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $warden->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher HS',
        'email' => 'teacher-hs@test.test',
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

    $this->actingAs($warden)
        ->get(route('tenant.hostel.hostels.index'))
        ->assertOk();

    $this->actingAs($warden)
        ->get(route('tenant.hostel.allocations.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.hostel.hostels.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.hostel.allocations.index'))
        ->assertForbidden();
});
