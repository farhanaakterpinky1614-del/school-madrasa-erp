<?php

use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Models\LeaveRequest;
use Modules\Hr\Models\LeaveType;
use Modules\Hr\Models\PayrollRun;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Hr\Services\LeaveWorkflowService;
use Modules\Hr\Services\PayrollService;

it('approves a leave request through the workflow service', function () {
    $registered = $this->registerTenant([
        'slug' => 'leave-wf',
        'admin_email' => 'leave-wf@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $staff = Staff::query()->firstOrFail();
    $type = LeaveType::query()->firstOrFail();
    $workflow = app(LeaveWorkflowService::class);

    $request = $workflow->submit($staff, [
        'leave_type_id' => $type->id,
        'starts_on' => now()->addDays(2)->toDateString(),
        'ends_on' => now()->addDays(3)->toDateString(),
        'reason' => 'Family event',
    ]);

    expect($request->status)->toBe(LeaveRequest::STATUS_PENDING)
        ->and((float) $request->days)->toBe(2.0);

    $approved = $workflow->approve($request, $registered['user'], 'OK');
    expect($approved->status)->toBe(LeaveRequest::STATUS_APPROVED)
        ->and($approved->reviewed_by)->toBe($registered['user']->id);
});

it('processes basic payroll and generates payslips', function () {
    $registered = $this->registerTenant([
        'slug' => 'pay-wf',
        'admin_email' => 'pay-wf@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $payroll = app(PayrollService::class);
    $run = $payroll->createRun((int) now()->year, (int) now()->month);
    expect($run->status)->toBe(PayrollRun::STATUS_DRAFT);

    $processed = $payroll->process($run, $registered['user']);
    expect($processed->status)->toBe(PayrollRun::STATUS_PROCESSED)
        ->and($processed->payslips()->count())->toBeGreaterThan(0);

    $paid = $payroll->markPaid($processed);
    expect($paid->status)->toBe(PayrollRun::STATUS_PAID);
});

it('allows admin to submit leave via HTTP and approve it', function () {
    $registered = $this->registerTenant([
        'slug' => 'leave-http',
        'admin_email' => 'leave-http@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);

    $staff = Staff::queryWithoutTenantScope()->where('tenant_id', $registered['tenant']->id)->firstOrFail();
    $type = LeaveType::queryWithoutTenantScope()->where('tenant_id', $registered['tenant']->id)->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.leave-requests.store'), [
            'staff_id' => $staff->id,
            'leave_type_id' => $type->id,
            'starts_on' => now()->addWeek()->toDateString(),
            'ends_on' => now()->addWeek()->addDay()->toDateString(),
            'reason' => 'HTTP leave',
        ])
        ->assertRedirect(route('tenant.leave-requests.index'));

    $leave = LeaveRequest::queryWithoutTenantScope()
        ->where('tenant_id', $registered['tenant']->id)
        ->where('reason', 'HTTP leave')
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.leave-requests.approve', $leave), ['review_notes' => 'Approved'])
        ->assertRedirect();

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
});
