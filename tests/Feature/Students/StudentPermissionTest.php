<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open student and admission screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-stu',
        'admin_email' => 'admin-stu@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.students.index'))->assertOk()
        ->assertSee('Ayesha', false);

    $this->actingAs($registered['user'])
        ->get(route('tenant.applications.index'))->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.admission-forms.index'))->assertOk();
});

it('denies teacher from creating students', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-stu',
        'admin_email' => 'teacher-stu-admin@test.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Only',
        'email' => 'teacher-stu@test.test',
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
        ->get(route('tenant.students.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.students.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.applications.index'))
        ->assertForbidden();
});
