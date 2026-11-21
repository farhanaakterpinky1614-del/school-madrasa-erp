<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Spatie\Permission\Models\Role;

it('allows institution admin to open academic management screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-acad',
        'admin_email' => 'admin-acad@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.rooms.index'))->assertOk()
        ->assertSee('Room 101', false);

    $this->actingAs($registered['user'])
        ->get(route('tenant.routines.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.syllabi.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.homework.index'))->assertOk()
        ->assertSee('Homework', false);

    $this->actingAs($registered['user'])
        ->get(route('tenant.classwork.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.academic-reports.index'))->assertOk();
});

it('denies teacher from managing rooms but allows homework', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-acad',
        'admin_email' => 'teacher-acad-admin@test.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Acad',
        'email' => 'teacher-acad@test.test',
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
        ->get(route('tenant.rooms.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.rooms.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.academic-reports.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.homework.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.lesson-plans.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.routines.index'))
        ->assertOk();
});
