<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Examinations\Services\DemoExamProvisioner;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Services\DemoHifzProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes hifz profiles to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'hifz-a', 'admin_email' => 'hifz-a@test.test']);
    $b = $this->registerTenant(['slug' => 'hifz-b', 'admin_email' => 'hifz-b@test.test']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoExamProvisioner::class)->provision($registered['tenant']);
        app(DemoHifzProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(HifzProfile::query()->count())->toBeGreaterThan(0);

    $foreign = HifzProfile::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(HifzProfile::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating a hifz profile and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-hifz-a', 'admin_email' => 'fha@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-hifz-b', 'admin_email' => 'fhb@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoStudentProvisioner::class)->provision($a['tenant']);
    app(DemoHrProvisioner::class)->provision($a['tenant']);
    app(DemoAcademicProvisioner::class)->provision($a['tenant']);
    app(DemoHifzProvisioner::class)->provision($a['tenant']);

    $this->bindTenant($a['tenant']);

    $student = Student::query()->create([
        'student_code' => 'HFZ-FORGE-'.uniqid(),
        'first_name' => 'Forge',
        'last_name' => 'Student',
        'status' => Student::STATUS_ACTIVE,
        'gender' => 'male',
    ]);

    $row = HifzProfile::query()->create([
        'tenant_id' => $b['tenant']->id,
        'student_id' => $student->id,
        'status' => HifzProfile::STATUS_ACTIVE,
        'current_juz' => 2,
        'current_ayah' => 1,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(HifzProfile::query()->where('student_id', $student->id)->exists())->toBeFalse();
});
