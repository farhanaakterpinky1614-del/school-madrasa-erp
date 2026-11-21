<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Spatie\Permission\Models\Role;

it('allows institution admin to open staff and payroll screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-hr',
        'admin_email' => 'admin-hr@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.staff.index'))->assertOk()
        ->assertSee('Fatima', false);

    $this->actingAs($registered['user'])
        ->get(route('tenant.teachers.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.payroll.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.leave-requests.index'))->assertOk();
});

it('denies teacher from creating staff and viewing payroll', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-hr',
        'admin_email' => 'teacher-hr-admin@test.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Only',
        'email' => 'teacher-hr@test.test',
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
        ->get(route('tenant.staff.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.staff.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.payroll.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.leave-requests.index'))
        ->assertOk();
});
