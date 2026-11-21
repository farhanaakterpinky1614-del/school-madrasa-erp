<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Library\Services\DemoLibraryProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open library screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-lib',
        'admin_email' => 'admin-lib@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoLibraryProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.books.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.copies.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.issues.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.fines.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.reports.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.library.settings.edit'))
        ->assertOk();
});

it('allows librarian library access but denies teacher circulation screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'role-lib',
        'admin_email' => 'role-lib-admin@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoLibraryProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $librarianRole = Role::findByName('Librarian', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');

    $librarian = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Librarian',
        'email' => 'librarian-lib@test.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $librarian->assignRole($librarianRole);
    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $librarian->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Lib',
        'email' => 'teacher-lib@test.test',
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

    $this->actingAs($librarian)
        ->get(route('tenant.library.books.index'))
        ->assertOk();

    $this->actingAs($librarian)
        ->get(route('tenant.library.issues.index'))
        ->assertOk();

    $this->actingAs($librarian)
        ->get(route('tenant.library.fines.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.library.books.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.library.issues.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.library.fines.index'))
        ->assertForbidden();
});
