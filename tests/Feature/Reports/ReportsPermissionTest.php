<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Services\DemoAttendanceProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Reports\Services\DemoReportsProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open reports screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-rep',
        'admin_email' => 'admin-rep@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoAttendanceProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoReportsProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.id-cards.students.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.id-cards.staff.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.certificates.templates.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.certificates.issues.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.exports.index'))
        ->assertOk();
});

it('allows reports officer; denies teacher reports hub', function () {
    $registered = $this->registerTenant([
        'slug' => 'role-rep',
        'admin_email' => 'role-rep-admin@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoReportsProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $officerRole = Role::findByName('Reports Officer', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');

    $officer = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Reports Officer',
        'email' => 'reports-officer@test.test',
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
        'name' => 'Teacher Rep',
        'email' => 'teacher-rep@test.test',
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
        ->get(route('tenant.reports.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('tenant.reports.exports.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.reports.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.reports.certificates.templates.index'))
        ->assertForbidden();
});
