<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Examinations\Services\DemoExamProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open examination screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-exam',
        'admin_email' => 'admin-exam@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoExamProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.exams.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.marks.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.results.publish.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.results.pdf.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.grading-schemes.index'))
        ->assertOk();
});

it('allows teacher marks entry but denies publish', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-exam',
        'admin_email' => 'teacher-exam-admin@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoExamProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Exam',
        'email' => 'teacher-exam@test.test',
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

    $staff = Staff::query()->teachers()->orderBy('id')->first();
    $staff?->update(['user_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->get(route('tenant.exams.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.marks.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.results.publish.index'))
        ->assertForbidden();
});
