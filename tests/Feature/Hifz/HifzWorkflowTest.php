<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzProgressLog;
use Modules\Hifz\Models\HifzRevision;
use Modules\Hifz\Services\DemoHifzProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('logs hifz progress via HTTP and updates profile position', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hifz-progress',
        'admin_email' => 'wf-hifz-progress@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoHifzProvisioner::class)->provision($registered['tenant']);

    $profile = HifzProfile::query()->where('status', HifzProfile::STATUS_ACTIVE)->firstOrFail();
    $toJuz = min(30, (int) $profile->current_juz + 1);

    $this->actingAs($registered['user'])
        ->post(route('tenant.hifz.progress.store'), [
            'hifz_profile_id' => $profile->id,
            'logged_on' => now('Asia/Dhaka')->toDateString(),
            'from_juz' => $profile->current_juz,
            'from_ayah' => 1,
            'to_juz' => $toJuz,
            'to_ayah' => 5,
            'quality' => HifzProgressLog::QUALITY_GOOD,
            'teacher_notes' => 'Workflow sabak note',
            'update_profile_position' => 1,
        ])
        ->assertRedirect();

    expect(HifzProgressLog::query()
        ->where('hifz_profile_id', $profile->id)
        ->where('teacher_notes', 'Workflow sabak note')
        ->exists())->toBeTrue();

    $profile->refresh();
    expect($profile->current_juz)->toBe($toJuz);
    expect($profile->current_ayah)->toBe(5);
});

it('creates a dawr revision cycle via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hifz-dawr',
        'admin_email' => 'wf-hifz-dawr@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoHifzProvisioner::class)->provision($registered['tenant']);

    $profile = HifzProfile::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.hifz.revisions.store'), [
            'hifz_profile_id' => $profile->id,
            'label' => 'Test Dawr',
            'from_juz' => 1,
            'to_juz' => 3,
            'scheduled_on' => now('Asia/Dhaka')->toDateString(),
            'status' => HifzRevision::STATUS_SCHEDULED,
            'notes' => 'Workflow dawr',
        ])
        ->assertRedirect();

    expect(HifzRevision::query()
        ->where('hifz_profile_id', $profile->id)
        ->where('label', 'Test Dawr')
        ->exists())->toBeTrue();
});

it('blocks teacher from updating an unassigned hifz profile', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hifz-scope',
        'admin_email' => 'wf-hifz-scope@test.test',
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
        'name' => 'Scoped Hifz Teacher',
        'email' => 'scoped-hifz-teacher@test.test',
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

    $staff = Staff::query()->teachers()->orderBy('id')->firstOrFail();
    $staff->update(['user_id' => $teacher->id]);

    // Ensure at least one profile is NOT assigned to this teacher
    $foreign = HifzProfile::query()
        ->where(function ($q) use ($staff) {
            $q->whereNull('assigned_teacher_id')
                ->orWhere('assigned_teacher_id', '!=', $staff->id);
        })
        ->first();

    if (! $foreign) {
        $student = Student::query()->create([
            'student_code' => 'HFZ-SCOPE-'.uniqid(),
            'first_name' => 'Other',
            'last_name' => 'Student',
            'status' => Student::STATUS_ACTIVE,
            'gender' => 'male',
        ]);
        $otherTeacher = Staff::query()
            ->teachers()
            ->where('id', '!=', $staff->id)
            ->orderBy('id')
            ->first();

        $foreign = HifzProfile::query()->create([
            'student_id' => $student->id,
            'status' => HifzProfile::STATUS_ACTIVE,
            'current_juz' => 4,
            'current_ayah' => 1,
            'assigned_teacher_id' => $otherTeacher?->id,
        ]);
    }

    $this->actingAs($teacher)
        ->post(route('tenant.hifz.progress.store'), [
            'hifz_profile_id' => $foreign->id,
            'logged_on' => now('Asia/Dhaka')->toDateString(),
            'from_juz' => 1,
            'from_ayah' => 1,
            'to_juz' => 1,
            'to_ayah' => 2,
            'quality' => HifzProgressLog::QUALITY_AVERAGE,
        ])
        ->assertForbidden();
});

it('creates a hifz profile via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hifz-create',
        'admin_email' => 'wf-hifz-create@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoHifzProvisioner::class)->provision($registered['tenant']);

    $linkedIds = HifzProfile::query()->pluck('student_id')->all();
    $student = Student::query()
        ->whereNotIn('id', $linkedIds ?: [0])
        ->first();

    if (! $student) {
        $student = Student::query()->create([
            'student_code' => 'HFZ-NEW-'.uniqid(),
            'first_name' => 'New',
            'last_name' => 'Hifz',
            'status' => Student::STATUS_ACTIVE,
            'gender' => 'male',
        ]);
    }

    $teacher = Staff::query()->teachers()->orderBy('id')->first();

    $this->actingAs($registered['user'])
        ->post(route('tenant.hifz.profiles.store'), [
            'student_id' => $student->id,
            'status' => HifzProfile::STATUS_ACTIVE,
            'current_juz' => 1,
            'current_ayah' => 1,
            'assigned_teacher_id' => $teacher?->id,
            'started_on' => now('Asia/Dhaka')->toDateString(),
            'notes' => 'Created in test',
        ])
        ->assertRedirect();

    expect(HifzProfile::query()->where('student_id', $student->id)->exists())->toBeTrue();
});
