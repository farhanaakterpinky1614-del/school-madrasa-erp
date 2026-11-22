<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Services\DemoAttendanceProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Reports\Models\CertificateIssue;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Reports\Models\IdCardTemplate;
use Modules\Reports\Models\ReportExport;
use Modules\Reports\Services\DemoReportsProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes certificate templates, issues, and exports to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'rep-a', 'admin_email' => 'rep-a@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'rep-b', 'admin_email' => 'rep-b@test.test', 'plan_code' => 'professional']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoAttendanceProvisioner::class)->provision($registered['tenant']);
        app(DemoFeeProvisioner::class)->provision($registered['tenant']);
        app(DemoReportsProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(CertificateTemplate::query()->count())->toBeGreaterThan(0);
    expect(CertificateIssue::query()->count())->toBeGreaterThan(0);
    expect(IdCardTemplate::query()->count())->toBeGreaterThan(0);
    expect(ReportExport::query()->count())->toBeGreaterThan(0);

    $foreignTemplate = CertificateTemplate::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignExport = ReportExport::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(CertificateTemplate::query()->find($foreignTemplate->id))->toBeNull();
    expect(ReportExport::query()->find($foreignExport->id))->toBeNull();
});

it('forces tenant_id when creating a certificate template and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-rep-a', 'admin_email' => 'fra@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'forge-rep-b', 'admin_email' => 'frb@test.test', 'plan_code' => 'professional']);

    $this->bindTenant($a['tenant']);

    $row = CertificateTemplate::query()->create([
        'tenant_id' => $b['tenant']->id,
        'name' => 'Forged certificate',
        'code' => 'FORGED',
        'certificate_type' => CertificateTemplate::TYPE_CHARACTER,
        'title' => 'Forged',
        'body' => 'Should stay on tenant A',
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(CertificateTemplate::query()->where('code', 'FORGED')->exists())->toBeFalse();
});
