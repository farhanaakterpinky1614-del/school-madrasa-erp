<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Models\PeriodAttendance;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\TeacherAssignment;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Models\StudentEnrollment;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('marks daily student attendance via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-daily-att',
        'admin_email' => 'wf-daily-att@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $class = SchoolClass::query()->orderBy('numeric_level')->firstOrFail();
    $section = Section::query()->where('school_class_id', $class->id)->first();
    $enrollments = StudentEnrollment::query()
        ->where('school_class_id', $class->id)
        ->where('is_current', true)
        ->limit(3)
        ->get();

    expect($enrollments)->not->toBeEmpty();

    $date = now('Asia/Dhaka')->toDateString();
    $entries = [];
    foreach ($enrollments as $i => $enrollment) {
        $entries[$i] = [
            'student_id' => $enrollment->student_id,
            'status' => $i === 0 ? StudentAttendance::STATUS_ABSENT : StudentAttendance::STATUS_PRESENT,
        ];
    }

    $this->actingAs($registered['user'])
        ->post(route('tenant.attendance.daily.store'), [
            'attendance_date' => $date,
            'school_class_id' => $class->id,
            'section_id' => $section?->id,
            'entries' => $entries,
        ])
        ->assertRedirect();

    expect(StudentAttendance::query()
        ->whereDate('attendance_date', $date)
        ->where('status', StudentAttendance::STATUS_ABSENT)
        ->count())->toBeGreaterThan(0);

    expect(StudentAttendance::query()
        ->whereDate('attendance_date', $date)
        ->count())->toBe(count($entries));
});

it('updates daily attendance on second save without unique violation', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-daily-att-resave',
        'admin_email' => 'wf-daily-att-resave@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $class = SchoolClass::query()->orderBy('numeric_level')->firstOrFail();
    $section = Section::query()->where('school_class_id', $class->id)->first();
    $enrollment = StudentEnrollment::query()
        ->where('school_class_id', $class->id)
        ->where('is_current', true)
        ->firstOrFail();

    $date = now('Asia/Dhaka')->toDateString();
    $payload = [
        'attendance_date' => $date,
        'school_class_id' => $class->id,
        'section_id' => $section?->id,
        'entries' => [
            [
                'student_id' => $enrollment->student_id,
                'status' => StudentAttendance::STATUS_PRESENT,
            ],
        ],
    ];

    $this->actingAs($registered['user'])
        ->post(route('tenant.attendance.daily.store'), $payload)
        ->assertRedirect();

    $first = StudentAttendance::query()
        ->where('student_id', $enrollment->student_id)
        ->whereDate('attendance_date', $date)
        ->firstOrFail();

    $payload['entries'][0]['status'] = StudentAttendance::STATUS_ABSENT;
    $payload['entries'][0]['notes'] = 'Updated on resave';

    $this->actingAs($registered['user'])
        ->post(route('tenant.attendance.daily.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $rows = StudentAttendance::query()
        ->where('student_id', $enrollment->student_id)
        ->whereDate('attendance_date', $date)
        ->get();

    expect($rows)->toHaveCount(1);
    expect($rows->first()->id)->toBe($first->id);
    expect($rows->first()->status)->toBe(StudentAttendance::STATUS_ABSENT);
    expect($rows->first()->notes)->toBe('Updated on resave');
});

it('marks period attendance for a routine slot via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-period-att',
        'admin_email' => 'wf-period-att@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $routine = ClassRoutine::query()->where('is_active', true)->firstOrFail();
    $enrollments = StudentEnrollment::query()
        ->where('school_class_id', $routine->school_class_id)
        ->when($routine->section_id, fn ($q) => $q->where('section_id', $routine->section_id))
        ->where('is_current', true)
        ->limit(2)
        ->get();

    expect($enrollments)->not->toBeEmpty();

    $date = now('Asia/Dhaka')->toDateString();
    $entries = [];
    foreach ($enrollments as $i => $enrollment) {
        $entries[$i] = [
            'student_id' => $enrollment->student_id,
            'status' => PeriodAttendance::STATUS_PRESENT,
        ];
    }

    $this->actingAs($registered['user'])
        ->post(route('tenant.attendance.period.store'), [
            'attendance_date' => $date,
            'class_routine_id' => $routine->id,
            'entries' => $entries,
        ])
        ->assertRedirect(route('tenant.attendance.period.index', [
            'date' => $date,
            'routine_id' => $routine->id,
        ]));

    expect(PeriodAttendance::query()
        ->where('class_routine_id', $routine->id)
        ->whereDate('attendance_date', $date)
        ->count())->toBe(count($entries));
});

it('blocks teacher from marking a class outside own assignments', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-scope-att',
        'admin_email' => 'wf-scope-att@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    setPermissionsTeamId($registered['tenant']->id);
    $teacherRole = Role::findByName('Teacher', 'web');

    $teacher = User::query()->create([
        'tenant_id' => $registered['tenant']->id,
        'name' => 'Scoped Teacher',
        'email' => 'scoped-teacher@test.test',
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

    // Ensure only first class is assigned; pick another class if available, else create orphan class id mismatch
    $assignedClassId = TeacherAssignment::query()->where('staff_id', $staff->id)->value('school_class_id');
    $otherClass = SchoolClass::query()->where('id', '!=', $assignedClassId)->first();

    if (! $otherClass) {
        $otherClass = SchoolClass::query()->create([
            'name' => 'Unassigned Class',
            'code' => 'UA1',
            'numeric_level' => 99,
            'is_active' => true,
        ]);
    }

    $enrollment = StudentEnrollment::query()->where('is_current', true)->firstOrFail();

    $this->actingAs($teacher)
        ->post(route('tenant.attendance.daily.store'), [
            'attendance_date' => now('Asia/Dhaka')->toDateString(),
            'school_class_id' => $otherClass->id,
            'section_id' => null,
            'entries' => [
                ['student_id' => $enrollment->student_id, 'status' => StudentAttendance::STATUS_PRESENT],
            ],
        ])
        ->assertForbidden();
});
