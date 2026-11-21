<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Attendance\Services\DemoAttendanceProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes student attendance to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'att-a', 'admin_email' => 'att-a@test.test']);
    $b = $this->registerTenant(['slug' => 'att-b', 'admin_email' => 'att-b@test.test']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoAttendanceProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(StudentAttendance::query()->count())->toBeGreaterThan(0);

    $foreign = StudentAttendance::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(StudentAttendance::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating student attendance and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-att-a', 'admin_email' => 'faa-att@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-att-b', 'admin_email' => 'fab-att@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoStudentProvisioner::class)->provision($a['tenant']);
    app(DemoHrProvisioner::class)->provision($a['tenant']);
    app(DemoAcademicProvisioner::class)->provision($a['tenant']);

    $this->bindTenant($a['tenant']);

    $student = \Modules\Students\Models\Student::query()->firstOrFail();
    $class = \Modules\Academics\Models\SchoolClass::query()->firstOrFail();

    $row = StudentAttendance::query()->create([
        'tenant_id' => $b['tenant']->id,
        'student_id' => $student->id,
        'school_class_id' => $class->id,
        'attendance_date' => now()->toDateString(),
        'status' => StudentAttendance::STATUS_PRESENT,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(StudentAttendance::query()->where('student_id', $student->id)->exists())->toBeFalse();
});
