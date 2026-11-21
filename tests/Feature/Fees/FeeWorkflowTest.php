<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeeStructure;
use Modules\Fees\Services\DemoFeeProvisioner;
use Modules\Fees\Services\InvoiceLedger;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;

it('creates issues and partially pays an invoice via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-fee-pay',
        'admin_email' => 'wf-fee-pay@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);

    $student = Student::query()->where('status', Student::STATUS_ACTIVE)->firstOrFail();
    $structure = FeeStructure::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.fees.invoices.store'), [
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'title' => 'Workflow tuition',
            'subtotal' => 1000,
            'due_date' => now('Asia/Dhaka')->addDays(7)->toDateString(),
            'issue_now' => 1,
        ])
        ->assertRedirect();

    $invoice = FeeInvoice::query()->where('title', 'Workflow tuition')->firstOrFail();
    expect($invoice->status)->toBe(FeeInvoice::STATUS_ISSUED);
    expect((float) $invoice->balance)->toBe(1000.0);

    $this->actingAs($registered['user'])
        ->post(route('tenant.fees.payments.store'), [
            'fee_invoice_id' => $invoice->id,
            'amount' => 400,
            'method' => FeePayment::METHOD_CASH,
            'paid_on' => now('Asia/Dhaka')->toDateString(),
            'reference' => 'WF-CASH-1',
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(FeeInvoice::STATUS_PARTIAL);
    expect((float) $invoice->paid_amount)->toBe(400.0);
    expect((float) $invoice->balance)->toBe(600.0);

    $this->actingAs($registered['user'])
        ->post(route('tenant.fees.payments.store'), [
            'fee_invoice_id' => $invoice->id,
            'amount' => 600,
            'method' => FeePayment::METHOD_BKASH,
            'paid_on' => now('Asia/Dhaka')->toDateString(),
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(FeeInvoice::STATUS_PAID);
    expect((float) $invoice->balance)->toBe(0.0);
});

it('applies percent discount before payment via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-fee-disc',
        'admin_email' => 'wf-fee-disc@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);

    $student = Student::query()->where('status', Student::STATUS_ACTIVE)->firstOrFail();

    $invoice = app(InvoiceLedger::class)->createManual($student, [
        'title' => 'Discountable exam fee',
        'subtotal' => 2000,
        'due_date' => now('Asia/Dhaka')->addDays(10)->toDateString(),
    ], $registered['user']->id);
    app(InvoiceLedger::class)->issue($invoice);
    $invoice = $invoice->fresh();

    $this->actingAs($registered['user'])
        ->post(route('tenant.fees.discounts.store', $invoice), [
            'type' => FeeDiscount::TYPE_PERCENT,
            'label' => 'Sibling discount 25%',
            'percent' => 25,
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect((float) $invoice->discount_total)->toBe(500.0);
    expect((float) $invoice->total)->toBe(1500.0);
    expect((float) $invoice->balance)->toBe(1500.0);
    expect($invoice->status)->toBe(FeeInvoice::STATUS_ISSUED);
});

it('downloads a payment receipt pdf', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-fee-pdf',
        'admin_email' => 'wf-fee-pdf@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoFeeProvisioner::class)->provision($registered['tenant']);

    $payment = FeePayment::query()->orderBy('id')->firstOrFail();

    $this->actingAs($registered['user'])
        ->get(route('tenant.fees.payments.receipt', $payment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
