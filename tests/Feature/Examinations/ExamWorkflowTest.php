<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Subject;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;
use Modules\Examinations\Models\Mark;
use Modules\Examinations\Models\ResultPublication;
use Modules\Examinations\Services\DemoExamProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\TeacherAssignment;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Identity\Models\TenantMembership;
use Modules\Students\Models\StudentEnrollment;
use Modules\Students\Services\DemoStudentProvisioner;
use Spatie\Permission\Models\Role;

it('enters marks via HTTP for an exam paper', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-marks',
        'admin_email' => 'wf-marks@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoExamProvisioner::class)->provision($registered['tenant']);

    $exam = Exam::query()->where('code', 'HALF-2026')->firstOrFail();
    $paper = ExamSubject::query()->where('exam_id', $exam->id)->orderBy('id')->firstOrFail();
    $enrollments = StudentEnrollment::query()
        ->where('school_class_id', $exam->school_class_id)
        ->where('is_current', true)
        ->limit(2)
        ->get();

    expect($enrollments)->not->toBeEmpty();

    $entries = [];
    foreach ($enrollments as $i => $enrollment) {
        $entries[$i] = [
            'student_id' => $enrollment->student_id,
            'marks_obtained' => 80 - ($i * 5),
            'entry_status' => Mark::ENTRY_DRAFT,
        ];
    }

    $this->actingAs($registered['user'])
        ->post(route('tenant.marks.store'), [
            'exam_id' => $exam->id,
            'exam_subject_id' => $paper->id,
            'section_id' => $enrollments->first()->section_id,
            'entries' => $entries,
        ])
        ->assertRedirect();

    expect(Mark::query()
        ->where('exam_subject_id', $paper->id)
        ->whereIn('student_id', $enrollments->pluck('student_id'))
        ->count())->toBe(count($entries));

    $saved = Mark::query()
        ->where('exam_subject_id', $paper->id)
        ->where('student_id', $enrollments->first()->student_id)
        ->firstOrFail();

    expect($saved->grade)->not->toBeNull();
});

it('publishes and unpublishes exam results via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-publish',
        'admin_email' => 'wf-publish@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoExamProvisioner::class)->provision($registered['tenant']);

    $exam = Exam::query()->where('code', 'FINAL-DRAFT')->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.results.publish.store'), [
            'exam_id' => $exam->id,
            'action' => 'publish',
            'notes' => 'Published in test',
        ])
        ->assertRedirect(route('tenant.results.publish.index', ['exam_id' => $exam->id]));

    $pub = ResultPublication::query()
        ->where('exam_id', $exam->id)
        ->whereNull('section_id')
        ->firstOrFail();

    expect($pub->published_at)->not->toBeNull();

    $this->actingAs($registered['user'])
        ->post(route('tenant.results.publish.store'), [
            'exam_id' => $exam->id,
            'action' => 'unpublish',
        ])
        ->assertRedirect();

    $pub->refresh();
    expect($pub->published_at)->toBeNull();
});

it('blocks teacher from entering marks for an unassigned subject', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-scope-exam',
        'admin_email' => 'wf-scope-exam@test.test',
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
        'name' => 'Scoped Exam Teacher',
        'email' => 'scoped-exam-teacher@test.test',
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

    $assignedSubjectId = TeacherAssignment::query()->where('staff_id', $staff->id)->value('subject_id');
    $exam = Exam::query()->where('code', 'HALF-2026')->firstOrFail();
    $linkedSubjectIds = ExamSubject::query()->where('exam_id', $exam->id)->pluck('subject_id')->all();

    $otherSubject = Subject::query()->create([
        'name' => 'Unassigned Subject',
        'code' => 'UA-SUB-'.uniqid(),
        'is_active' => true,
    ]);

    // Keep teacher assigned only to a different subject (if any)
    if ($assignedSubjectId) {
        TeacherAssignment::query()
            ->where('staff_id', $staff->id)
            ->where('subject_id', $otherSubject->id)
            ->delete();
    }

    $foreignPaper = ExamSubject::query()->create([
        'exam_id' => $exam->id,
        'subject_id' => $otherSubject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
        'sort_order' => 99,
    ]);

    // Ensure teacher assignment does not include this subject
    TeacherAssignment::query()
        ->where('staff_id', $staff->id)
        ->where(function ($q) use ($otherSubject, $linkedSubjectIds) {
            $q->where('subject_id', $otherSubject->id)
                ->orWhereNull('subject_id');
        })
        ->delete();

    // If teacher has no subject-scoped assignment left, pin to first linked subject only
    if (TeacherAssignment::query()->where('staff_id', $staff->id)->where('is_active', true)->doesntExist()) {
        $pinSubjectId = $assignedSubjectId ?: ($linkedSubjectIds[0] ?? null);
        if ($pinSubjectId) {
            TeacherAssignment::query()->create([
                'staff_id' => $staff->id,
                'school_class_id' => $exam->school_class_id,
                'subject_id' => $pinSubjectId,
                'is_active' => true,
            ]);
        }
    }

    $enrollment = StudentEnrollment::query()->where('is_current', true)->firstOrFail();

    $this->actingAs($teacher)
        ->post(route('tenant.marks.store'), [
            'exam_id' => $exam->id,
            'exam_subject_id' => $foreignPaper->id,
            'entries' => [
                ['student_id' => $enrollment->student_id, 'marks_obtained' => 50],
            ],
        ])
        ->assertForbidden();
});

it('creates an exam via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-exam-create',
        'admin_email' => 'wf-exam-create@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoExamProvisioner::class)->provision($registered['tenant']);

    $class = SchoolClass::query()->orderBy('numeric_level')->firstOrFail();
    $scheme = \Modules\Examinations\Models\GradingScheme::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.exams.store'), [
            'name' => 'Unit Test Exam',
            'code' => 'UT-EXAM',
            'school_class_id' => $class->id,
            'grading_scheme_id' => $scheme->id,
            'status' => Exam::STATUS_SCHEDULED,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addDays(5)->toDateString(),
        ])
        ->assertRedirect();

    expect(Exam::query()->where('code', 'UT-EXAM')->exists())->toBeTrue();
});
