<?php

use Illuminate\Support\Facades\Storage;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Services\DemoAttendanceProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Reports\Models\CertificateIssue;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Reports\Models\ReportExport;
use Modules\Reports\Services\DemoReportsProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;

it('opens student id card batch and staff id card routes', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-id-cards',
        'admin_email' => 'wf-id-cards@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoReportsProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.id-cards.students.print'))
        ->assertOk()
        ->assertSee('sme-id-card', false);

    $staff = Staff::query()->orderBy('id')->firstOrFail();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.id-cards.staff.show', $staff))
        ->assertOk()
        ->assertSee('sme-id-card', false)
        ->assertSee($staff->employee_code, false);
});

it('issues a certificate and downloads a pdf', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-cert-pdf',
        'admin_email' => 'wf-cert-pdf@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoReportsProvisioner::class)->provision($registered['tenant']);

    $template = CertificateTemplate::query()->where('code', 'CHARACTER')->firstOrFail();
    $student = Student::query()->orderBy('id')->firstOrFail();

    $before = CertificateIssue::query()->count();

    $this->actingAs($registered['user'])
        ->post(route('tenant.reports.certificates.issues.store'), [
            'certificate_template_id' => $template->id,
            'student_id' => $student->id,
            'notes' => 'Phase 15 pest',
        ])
        ->assertRedirect();

    expect(CertificateIssue::query()->count())->toBe($before + 1);

    $issue = CertificateIssue::query()->orderByDesc('id')->firstOrFail();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.certificates.issues.pdf', $issue))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('creates an export log and file synchronously', function () {
    Storage::fake('local');

    $registered = $this->registerTenant([
        'slug' => 'wf-export',
        'admin_email' => 'wf-export@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoAttendanceProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoReportsProvisioner::class)->provision($registered['tenant']);

    $before = ReportExport::query()->count();

    $this->actingAs($registered['user'])
        ->post(route('tenant.reports.exports.store'), [
            'report_type' => ReportExport::TYPE_STUDENTS_ROSTER,
            'format' => ReportExport::FORMAT_CSV,
        ])
        ->assertRedirect();

    expect(ReportExport::query()->count())->toBeGreaterThan($before);

    $export = ReportExport::query()->orderByDesc('id')->firstOrFail();
    expect($export->status)->toBe(ReportExport::STATUS_COMPLETED);
    expect($export->file_path)->not->toBeNull();
    expect(Storage::disk('local')->exists($export->file_path))->toBeTrue();

    $this->actingAs($registered['user'])
        ->get(route('tenant.reports.exports.download', $export))
        ->assertOk();
});
