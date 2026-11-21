<?php

namespace Modules\Fees\Services;

use Illuminate\Support\Facades\DB;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeeStructure;
use Modules\Fees\Support\Money;
use Modules\Students\Models\Student;

class InvoiceLedger
{
    public function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now('Asia/Dhaka')->format('Ymd').'-';
        $last = FeeInvoice::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function nextPaymentNumber(): string
    {
        $prefix = 'RCPT-'.now('Asia/Dhaka')->format('Ymd').'-';
        $last = FeePayment::query()
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    public function createManual(Student $student, array $attrs, ?int $userId = null): FeeInvoice
    {
        $subtotal = Money::round($attrs['subtotal'] ?? 0);

        $invoice = FeeInvoice::query()->create([
            'student_id' => $student->id,
            'fee_structure_id' => $attrs['fee_structure_id'] ?? null,
            'invoice_number' => $this->nextInvoiceNumber(),
            'title' => $attrs['title'],
            'status' => FeeInvoice::STATUS_DRAFT,
            'issue_date' => null,
            'due_date' => $attrs['due_date'] ?? null,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'total' => $subtotal,
            'paid_amount' => 0,
            'balance' => $subtotal,
            'currency' => 'BDT',
            'notes' => $attrs['notes'] ?? null,
            'created_by' => $userId,
        ]);

        return $this->recalculate($invoice);
    }

    public function createFromStructure(
        Student $student,
        FeeStructure $structure,
        ?string $dueDate = null,
        bool $issue = false,
        ?int $userId = null,
    ): FeeInvoice {
        $invoice = $this->createManual($student, [
            'fee_structure_id' => $structure->id,
            'title' => $structure->name,
            'subtotal' => $structure->amount,
            'due_date' => $dueDate,
            'notes' => $structure->notes,
        ], $userId);

        if ($issue) {
            return $this->issue($invoice);
        }

        return $invoice;
    }

    public function issue(FeeInvoice $invoice): FeeInvoice
    {
        abort_unless($invoice->status === FeeInvoice::STATUS_DRAFT, 422);

        $invoice->update([
            'status' => FeeInvoice::STATUS_ISSUED,
            'issue_date' => now('Asia/Dhaka')->toDateString(),
        ]);

        return $this->recalculate($invoice->fresh());
    }

    public function cancel(FeeInvoice $invoice): FeeInvoice
    {
        abort_unless(in_array($invoice->status, [
            FeeInvoice::STATUS_DRAFT,
            FeeInvoice::STATUS_ISSUED,
            FeeInvoice::STATUS_PARTIAL,
        ], true), 422);

        abort_unless((float) $invoice->paid_amount <= 0, 422);

        $invoice->update(['status' => FeeInvoice::STATUS_CANCELLED]);

        return $invoice->fresh();
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    public function applyDiscount(FeeInvoice $invoice, array $attrs, ?int $userId = null): FeeDiscount
    {
        abort_unless($invoice->acceptsDiscounts(), 422);

        $type = $attrs['type'];
        $subtotal = Money::round($invoice->subtotal);
        $amount = 0.0;
        $percent = null;

        if ($type === FeeDiscount::TYPE_PERCENT) {
            $percent = Money::round($attrs['percent'] ?? 0);
            $amount = Money::round($subtotal * ($percent / 100));
        } else {
            $amount = Money::round($attrs['amount'] ?? 0);
            if (in_array($type, [FeeDiscount::TYPE_WAIVER, FeeDiscount::TYPE_SCHOLARSHIP], true) && $amount <= 0) {
                $amount = $subtotal;
            }
        }

        $discount = FeeDiscount::query()->create([
            'fee_invoice_id' => $invoice->id,
            'type' => $type,
            'label' => $attrs['label'],
            'percent' => $percent,
            'amount' => $amount,
            'notes' => $attrs['notes'] ?? null,
            'applied_by' => $userId,
        ]);

        $this->recalculate($invoice->fresh());

        return $discount;
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    public function recordPayment(FeeInvoice $invoice, array $attrs, ?int $userId = null): FeePayment
    {
        abort_unless($invoice->acceptsPayments(), 422);

        $amount = Money::round($attrs['amount'] ?? 0);
        abort_unless($amount > 0, 422);
        abort_unless($amount <= Money::round($invoice->balance) + 0.001, 422);

        $payment = DB::transaction(function () use ($invoice, $attrs, $amount, $userId) {
            $payment = FeePayment::query()->create([
                'fee_invoice_id' => $invoice->id,
                'payment_number' => $this->nextPaymentNumber(),
                'amount' => $amount,
                'method' => $attrs['method'] ?? FeePayment::METHOD_CASH,
                'paid_on' => $attrs['paid_on'] ?? now('Asia/Dhaka')->toDateString(),
                'reference' => $attrs['reference'] ?? null,
                'notes' => $attrs['notes'] ?? null,
                'received_by' => $userId,
            ]);

            $this->recalculate($invoice->fresh());

            return $payment;
        });

        return $payment;
    }

    public function recalculate(FeeInvoice $invoice): FeeInvoice
    {
        if ($invoice->status === FeeInvoice::STATUS_CANCELLED) {
            return $invoice;
        }

        $subtotal = Money::round($invoice->subtotal);
        $discountTotal = Money::round(
            FeeDiscount::query()->where('fee_invoice_id', $invoice->id)->sum('amount')
        );
        $discountTotal = min($discountTotal, $subtotal);
        $total = Money::round(max(0, $subtotal - $discountTotal));
        $paid = Money::round(
            FeePayment::query()->where('fee_invoice_id', $invoice->id)->sum('amount')
        );
        $balance = Money::round(max(0, $total - $paid));

        $status = $invoice->status;
        if ($status !== FeeInvoice::STATUS_DRAFT) {
            if ($paid <= 0) {
                $status = FeeInvoice::STATUS_ISSUED;
            } elseif ($balance <= 0) {
                $status = FeeInvoice::STATUS_PAID;
            } else {
                $status = FeeInvoice::STATUS_PARTIAL;
            }
        }

        $invoice->update([
            'discount_total' => $discountTotal,
            'total' => $total,
            'paid_amount' => $paid,
            'balance' => $balance,
            'status' => $status,
        ]);

        return $invoice->fresh();
    }
}
