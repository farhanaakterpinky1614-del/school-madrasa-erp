<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;
use Modules\Transport\Models\TransportAssignment;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Services\DemoTransportHostelProvisioner;

it('assigns and deactivates student transport via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-tr-assign',
        'admin_email' => 'wf-tr-assign@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $route = TransportRoute::query()->where('is_active', true)->firstOrFail();

    // Pick a student without an active assignment
    $busyIds = TransportAssignment::query()
        ->where('status', TransportAssignment::STATUS_ACTIVE)
        ->pluck('student_id');
    $student = Student::query()
        ->where('status', Student::STATUS_ACTIVE)
        ->whereNotIn('id', $busyIds)
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.transport.assignments.store'), [
            'student_id' => $student->id,
            'transport_route_id' => $route->id,
            'pickup_stop' => 'Banani',
            'assigned_on' => now('Asia/Dhaka')->toDateString(),
            'fees_note' => 'Optional TRANSPORT fee link',
        ])
        ->assertRedirect();

    $assignment = TransportAssignment::query()
        ->where('student_id', $student->id)
        ->where('status', TransportAssignment::STATUS_ACTIVE)
        ->firstOrFail();

    expect($assignment->pickup_stop)->toBe('Banani');

    $this->actingAs($registered['user'])
        ->post(route('tenant.transport.assignments.deactivate', $assignment))
        ->assertRedirect();

    $assignment->refresh();
    expect($assignment->status)->toBe(TransportAssignment::STATUS_INACTIVE);
    expect($assignment->ended_on)->not->toBeNull();
});

it('rejects a second active transport assignment for the same student', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-tr-dup',
        'admin_email' => 'wf-tr-dup@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);
    app(DemoTransportHostelProvisioner::class)->provision($registered['tenant']);

    $existing = TransportAssignment::query()
        ->where('status', TransportAssignment::STATUS_ACTIVE)
        ->firstOrFail();
    $route = TransportRoute::query()->where('is_active', true)->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.transport.assignments.store'), [
            'student_id' => $existing->student_id,
            'transport_route_id' => $route->id,
            'pickup_stop' => 'Airport',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('student_id');
});
