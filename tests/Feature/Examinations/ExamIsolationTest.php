<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Services\DemoAttendanceProvisioner;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Services\DemoExamProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes exams to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'exam-a', 'admin_email' => 'exam-a@test.test']);
    $b = $this->registerTenant(['slug' => 'exam-b', 'admin_email' => 'exam-b@test.test']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoAttendanceProvisioner::class)->provision($registered['tenant']);
        app(DemoExamProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(Exam::query()->count())->toBeGreaterThan(0);

    $foreign = Exam::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(Exam::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating an exam and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-exam-a', 'admin_email' => 'fea@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-exam-b', 'admin_email' => 'feb@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoStudentProvisioner::class)->provision($a['tenant']);
    app(DemoHrProvisioner::class)->provision($a['tenant']);
    app(DemoAcademicProvisioner::class)->provision($a['tenant']);
    app(DemoExamProvisioner::class)->provision($a['tenant']);

    $this->bindTenant($a['tenant']);

    $class = \Modules\Academics\Models\SchoolClass::query()->firstOrFail();
    $scheme = \Modules\Examinations\Models\GradingScheme::query()->firstOrFail();

    $row = Exam::query()->create([
        'tenant_id' => $b['tenant']->id,
        'name' => 'Forged Exam',
        'school_class_id' => $class->id,
        'grading_scheme_id' => $scheme->id,
        'status' => Exam::STATUS_DRAFT,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Exam::query()->where('name', 'Forged Exam')->exists())->toBeFalse();
});
