<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Library\Models\LibraryCopy;
use Modules\Library\Models\LibraryFine;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Models\LibrarySetting;
use Modules\Library\Services\CirculationService;
use Modules\Library\Services\DemoLibraryProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;

it('issues and returns a copy via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-lib-issue',
        'admin_email' => 'wf-lib-issue@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoLibraryProvisioner::class)->provision($registered['tenant']);

    $student = Student::query()->where('status', Student::STATUS_ACTIVE)->firstOrFail();
    $copy = LibraryCopy::query()->where('status', LibraryCopy::STATUS_AVAILABLE)->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.library.issues.store'), [
            'library_copy_id' => $copy->id,
            'borrower_type' => LibraryIssue::BORROWER_STUDENT,
            'student_id' => $student->id,
            'issued_on' => now('Asia/Dhaka')->toDateString(),
            'due_on' => now('Asia/Dhaka')->addDays(7)->toDateString(),
        ])
        ->assertRedirect();

    $copy->refresh();
    expect($copy->status)->toBe(LibraryCopy::STATUS_ISSUED);

    $issue = LibraryIssue::query()
        ->where('library_copy_id', $copy->id)
        ->where('status', LibraryIssue::STATUS_ISSUED)
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.library.issues.return', $issue))
        ->assertRedirect();

    $issue->refresh();
    $copy->refresh();
    expect($issue->status)->toBe(LibraryIssue::STATUS_RETURNED);
    expect($copy->status)->toBe(LibraryCopy::STATUS_AVAILABLE);
});

it('creates overdue fine and marks paid / waived via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-lib-fine',
        'admin_email' => 'wf-lib-fine@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoLibraryProvisioner::class)->provision($registered['tenant']);

    $student = Student::query()->where('status', Student::STATUS_ACTIVE)->firstOrFail();
    $copy = LibraryCopy::query()->where('status', LibraryCopy::STATUS_AVAILABLE)->firstOrFail();
    $circulation = app(CirculationService::class);

    $settings = $circulation->settings();
    $settings->update([
        'fine_mode' => LibrarySetting::FINE_MODE_DAILY,
        'fine_daily_rate' => 10,
    ]);

    $issue = $circulation->issue([
        'library_copy_id' => $copy->id,
        'borrower_type' => LibraryIssue::BORROWER_STUDENT,
        'student_id' => $student->id,
        'issued_on' => now('Asia/Dhaka')->subDays(10)->toDateString(),
        'due_on' => now('Asia/Dhaka')->subDays(5)->toDateString(),
    ], $registered['user']->id);

    $this->actingAs($registered['user'])
        ->post(route('tenant.library.issues.return', $issue), [
            'returned_on' => now('Asia/Dhaka')->toDateString(),
        ])
        ->assertRedirect();

    $fine = LibraryFine::query()->where('library_issue_id', $issue->id)->firstOrFail();
    expect($fine->status)->toBe(LibraryFine::STATUS_PENDING);
    expect((float) $fine->amount)->toBe(50.0);
    expect($fine->days_overdue)->toBe(5);

    $this->actingAs($registered['user'])
        ->post(route('tenant.library.fines.paid', $fine), [
            'fees_note' => 'Settled at counter — Fees invoice optional',
        ])
        ->assertRedirect();

    $fine->refresh();
    expect($fine->status)->toBe(LibraryFine::STATUS_PAID);
    expect($fine->fees_note)->toContain('Fees');

    // Second overdue for waiver path
    $copy2 = LibraryCopy::query()->where('status', LibraryCopy::STATUS_AVAILABLE)->firstOrFail();
    $issue2 = $circulation->issue([
        'library_copy_id' => $copy2->id,
        'borrower_type' => LibraryIssue::BORROWER_STUDENT,
        'student_id' => $student->id,
        'issued_on' => now('Asia/Dhaka')->subDays(8)->toDateString(),
        'due_on' => now('Asia/Dhaka')->subDays(3)->toDateString(),
    ], $registered['user']->id);
    $circulation->returnCopy($issue2, $registered['user']->id, now('Asia/Dhaka')->toDateString());
    $fine2 = LibraryFine::query()->where('library_issue_id', $issue2->id)->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.library.fines.waived', $fine2), [
            'notes' => 'Waived by admin',
        ])
        ->assertRedirect();

    $fine2->refresh();
    expect($fine2->status)->toBe(LibraryFine::STATUS_WAIVED);
});
