<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hifz\Services\DemoHifzProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open hifz screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-hifz',
        'admin_email' => 'admin-hifz@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoHifzProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.hifz.profiles.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hifz.progress.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hifz.revisions.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hifz.meta.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.hifz.reports.index'))
        ->assertOk();
});

it('allows teacher progress access but denies muhtamim reports and islamic meta', function () {
    $registered = $this->registerTenant([
        'slug' => 'teacher-hifz',
        'admin_email' => 'teacher-hifz-admin@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoHifzProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Hifz',
        'email' => 'teacher-hifz@test.test',
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
        ->get(route('tenant.hifz.profiles.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.hifz.progress.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.hifz.revisions.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.hifz.reports.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.hifz.meta.index'))
        ->assertForbidden();
});
