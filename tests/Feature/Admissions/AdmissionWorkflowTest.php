<?php

use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Admissions\Models\AdmissionApplication;
use Modules\Admissions\Services\AdmissionWorkflowService;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Students\Services\EnrollmentService;
use Modules\Students\Services\TransferService;
use Modules\Students\Services\WithdrawalService;

it('runs admission approve and enroll workflow', function () {
    $registered = $this->registerTenant([
        'slug' => 'adm-flow',
        'admin_email' => 'adm-flow@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);

    $this->bindTenant($registered['tenant']);

    $application = AdmissionApplication::query()
        ->where('application_number', 'APP-DEMO-0002')
        ->firstOrFail();

    expect($application->status)->toBe(AdmissionApplication::STATUS_APPROVED);

    $year = AcademicYear::query()->where('is_current', true)->firstOrFail();
    $class = SchoolClass::query()->firstOrFail();
    $section = Section::query()->where('school_class_id', $class->id)->first();

    $workflow = app(AdmissionWorkflowService::class);
    $enrolled = $workflow->enroll($application, [
        'academic_year_id' => $year->id,
        'school_class_id' => $class->id,
        'section_id' => $section?->id,
        'roll_number' => '99',
    ], $registered['user']->id);

    expect($enrolled->status)->toBe(AdmissionApplication::STATUS_ENROLLED)
        ->and($enrolled->student_id)->not->toBeNull();

    $student = Student::query()->findOrFail($enrolled->student_id);
    expect($student->currentEnrollment)->not->toBeNull()
        ->and($student->student_code)->toStartWith('STU-')
        ->and($student->qr_payload)->not->toBeEmpty();
});

it('transfers a student between classes or sections', function () {
    $registered = $this->registerTenant([
        'slug' => 'xfer-flow',
        'admin_email' => 'xfer-flow@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $student = Student::query()->where('first_name', 'Ayesha')->firstOrFail();
    $year = AcademicYear::query()->where('is_current', true)->firstOrFail();
    $classes = SchoolClass::query()->orderBy('numeric_level')->get();
    expect($classes->count())->toBeGreaterThan(1);

    $toClass = $classes->firstWhere('id', '!=', $student->currentEnrollment?->school_class_id) ?? $classes->last();
    $toSection = Section::query()->where('school_class_id', $toClass->id)->first();

    $transfer = app(TransferService::class)->transfer($student, [
        'academic_year_id' => $year->id,
        'to_class_id' => $toClass->id,
        'to_section_id' => $toSection?->id,
        'roll_number' => '55',
        'reason' => 'Section change',
        'created_by' => $registered['user']->id,
    ]);

    expect($transfer->to_class_id)->toBe($toClass->id);
    $student->refresh();
    expect($student->currentEnrollment?->school_class_id)->toBe($toClass->id);
});

it('withdraws a student and can mark alumni', function () {
    $registered = $this->registerTenant([
        'slug' => 'wd-flow',
        'admin_email' => 'wd-flow@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $student = Student::query()->where('first_name', 'Rahim')->firstOrFail();
    $updated = app(WithdrawalService::class)->withdraw($student, [
        'reason' => 'Relocated',
        'mark_alumni' => true,
    ]);

    expect($updated->status)->toBe(Student::STATUS_ALUMNI)
        ->and($updated->withdrawn_on)->not->toBeNull()
        ->and($updated->alumni_on)->not->toBeNull();
});

it('accepts a rate-limited public online admission submission', function () {
    $registered = $this->registerTenant([
        'slug' => 'online-adm',
        'admin_email' => 'online-adm@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);

    $this->post(route('public.admissions.store', ['online-adm', 'demo-2025-intake']), [
        'first_name' => 'Online',
        'last_name' => 'Applicant',
        'guardian_name' => 'Parent Online',
        'guardian_phone' => '01711112222',
        'custom' => ['previous_school' => 'ABC', 'blood_group' => 'A+'],
    ])->assertRedirect();

    $this->bindTenant($registered['tenant']);
    expect(AdmissionApplication::query()->where('first_name', 'Online')->where('channel', 'online')->exists())->toBeTrue();
});

it('enrolls a registered student into class section year', function () {
    $registered = $this->registerTenant([
        'slug' => 'enroll-flow',
        'admin_email' => 'enroll-flow@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $student = Student::query()->create([
        'student_code' => 'STU-2026-9999',
        'first_name' => 'Fresh',
        'last_name' => 'Enrollee',
        'status' => Student::STATUS_ACTIVE,
    ]);

    $year = AcademicYear::query()->firstOrFail();
    $class = SchoolClass::query()->firstOrFail();
    $section = Section::query()->where('school_class_id', $class->id)->first();

    $enrollment = app(EnrollmentService::class)->enroll($student, [
        'academic_year_id' => $year->id,
        'school_class_id' => $class->id,
        'section_id' => $section?->id,
        'roll_number' => '1',
    ]);

    expect($enrollment->is_current)->toBeTrue()
        ->and($student->fresh()->currentEnrollment?->id)->toBe($enrollment->id);
});
