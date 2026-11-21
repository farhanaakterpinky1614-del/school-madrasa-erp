<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('allows institution admin to open fees and finance screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'admin-fees',
        'admin_email' => 'admin-fees@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.fees.categories.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.fees.structures.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.fees.invoices.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.fees.payments.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.finance.expenses.index'))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.finance.reports.index'))
        ->assertOk();
});

it('allows accountant fee access but denies teacher invoice manage screens', function () {
    $registered = $this->registerTenant([
        'slug' => 'acct-fees',
        'admin_email' => 'acct-fees-admin@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $accountantRole = Role::findByName('Accountant', 'web');
    $teacherRole = Role::findByName('Teacher', 'web');

    $accountant = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Demo Accountant',
        'email' => 'accountant-fees@test.test',
        'password' => Hash::make('password123'),
        'is_platform_admin' => false,
        'user_type' => User::TYPE_STAFF,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $accountant->assignRole($accountantRole);
    TenantMembership::queryWithoutTenantScope()->create([
        'tenant_id' => $registered['tenant']->id,
        'user_id' => $accountant->id,
        'status' => TenantMembership::STATUS_ACTIVE,
        'joined_at' => now(),
    ]);

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Teacher Fees',
        'email' => 'teacher-fees@test.test',
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

    $this->actingAs($accountant)
        ->get(route('tenant.fees.invoices.index'))
        ->assertOk();

    $this->actingAs($accountant)
        ->get(route('tenant.fees.payments.index'))
        ->assertOk();

    $this->actingAs($accountant)
        ->get(route('tenant.finance.expense-categories.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('tenant.fees.invoices.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.fees.payments.index'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('tenant.finance.expenses.index'))
        ->assertForbidden();
});
