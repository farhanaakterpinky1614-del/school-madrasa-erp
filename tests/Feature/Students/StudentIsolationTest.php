<?php

use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Students\Services\StudentIdGenerator;

it('scopes students to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'stu-a', 'admin_email' => 'stu-a@test.test']);
    $b = $this->registerTenant(['slug' => 'stu-b', 'admin_email' => 'stu-b@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoStudentProvisioner::class)->provision($a['tenant']);
    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoStudentProvisioner::class)->provision($b['tenant']);

    $this->bindTenant($a['tenant']);
    expect(Student::query()->count())->toBeGreaterThan(0);

    $foreign = Student::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();
    expect(Student::query()->find($foreign->id))->toBeNull();
});

it('forces tenant_id when creating a student and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-stu-a', 'admin_email' => 'fsa@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-stu-b', 'admin_email' => 'fsb@test.test']);

    $this->bindTenant($a['tenant']);
    $ids = app(StudentIdGenerator::class);

    $student = Student::query()->create([
        'tenant_id' => $b['tenant']->id,
        'student_code' => $ids->nextStudentCode(),
        'first_name' => 'Forged',
        'status' => Student::STATUS_ACTIVE,
    ]);

    expect($student->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Student::query()->where('first_name', 'Forged')->exists())->toBeFalse();
});

it('blocks cross-tenant student show by id', function () {
    $a = $this->registerTenant(['slug' => 'xstu-a', 'admin_email' => 'xsa@test.test']);
    $b = $this->registerTenant(['slug' => 'xstu-b', 'admin_email' => 'xsb@test.test']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoStudentProvisioner::class)->provision($b['tenant']);

    $foreign = Student::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();

    $this->actingAs($a['user'])
        ->get(route('tenant.students.show', $foreign))
        ->assertNotFound();
});
