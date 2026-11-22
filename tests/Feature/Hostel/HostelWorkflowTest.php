<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hostel\Models\HostelAllocation;
use Modules\Hostel\Models\HostelBed;
use Modules\Hostel\Services\HostelAllocationService;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Transport\Services\DemoTransportHostelProvisioner;

it('allocates and checks out a hostel bed via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hs-alloc',
        'admin_email' => 'wf-hs-alloc@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $busyStudentIds = HostelAllocation::query()
        ->where('status', HostelAllocation::STATUS_ACTIVE)
        ->pluck('student_id');
    $student = Student::query()
        ->where('status', Student::STATUS_ACTIVE)
        ->whereNotIn('id', $busyStudentIds)
        ->firstOrFail();
    $bed = HostelBed::query()->where('status', HostelBed::STATUS_AVAILABLE)->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.hostel.allocations.store'), [
            'student_id' => $student->id,
            'hostel_bed_id' => $bed->id,
            'check_in_on' => now('Asia/Dhaka')->toDateString(),
            'fees_note' => 'Optional HOSTEL fee link',
        ])
        ->assertRedirect();

    $bed->refresh();
    expect($bed->status)->toBe(HostelBed::STATUS_OCCUPIED);

    $allocation = HostelAllocation::query()
        ->where('hostel_bed_id', $bed->id)
        ->where('status', HostelAllocation::STATUS_ACTIVE)
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.hostel.allocations.checkout', $allocation))
        ->assertRedirect();

    $allocation->refresh();
    $bed->refresh();
    expect($allocation->status)->toBe(HostelAllocation::STATUS_CHECKED_OUT);
    expect($bed->status)->toBe(HostelBed::STATUS_AVAILABLE);
});

it('rejects allocating an already occupied bed', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-hs-conflict',
        'admin_email' => 'wf-hs-conflict@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $occupied = HostelAllocation::query()
        ->where('status', HostelAllocation::STATUS_ACTIVE)
        ->firstOrFail();

    $busyStudentIds = HostelAllocation::query()
        ->where('status', HostelAllocation::STATUS_ACTIVE)
        ->pluck('student_id');
    $student = Student::query()
        ->where('status', Student::STATUS_ACTIVE)
        ->whereNotIn('id', $busyStudentIds)
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.hostel.allocations.store'), [
            'student_id' => $student->id,
            'hostel_bed_id' => $occupied->hostel_bed_id,
            'check_in_on' => now('Asia/Dhaka')->toDateString(),
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('hostel_bed_id');

    // Service-level capacity conflict as well
    $service = app(HostelAllocationService::class);
    expect(fn () => $service->allocate([
        'student_id' => $student->id,
        'hostel_bed_id' => $occupied->hostel_bed_id,
    ]))->toThrow(RuntimeException::class);
});
