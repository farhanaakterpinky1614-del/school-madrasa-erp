<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Services\InvoiceLedger;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Integrations\Services\WebhookDispatcher;

class FeeController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'fees.invoice.view');

        $invoices = FeeInvoice::query()
            ->selectRaw('status, count(*) as count, coalesce(sum(total),0) as total_amount, coalesce(sum(paid_amount),0) as paid_amount, coalesce(sum(balance),0) as balance')
            ->groupBy('status')
            ->get();

        $paymentsToday = FeePayment::query()
            ->whereDate('paid_on', now('Asia/Dhaka')->toDateString())
            ->sum('amount');

        return response()->json([
            'by_status' => $invoices,
            'payments_today' => (float) $paymentsToday,
            'currency' => 'BDT',
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'fees.invoice.view');

        $query = FeeInvoice::query()->orderByDesc('id');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($studentId = $request->integer('student_id')) {
            $query->where('student_id', $studentId);
        }

        return response()->json(
            $query->paginate(min(100, max(1, (int) $request->integer('per_page', 25))))
        );
    }

    public function recordPayment(
        Request $request,
        FeeInvoice $invoice,
        InvoiceLedger $ledger,
        CurrentTenant $currentTenant,
        WebhookDispatcher $webhooks,
    ): JsonResponse {
        $this->authorizePermission($request, 'fees.payment.create');
        $tenant = $currentTenant->require();
        abort_unless((int) $invoice->tenant_id === (int) $tenant->id, 404);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'in:'.implode(',', FeePayment::METHODS)],
            'paid_on' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $ledger->recordPayment($invoice, $data, $request->user()?->id);
        $invoice = $invoice->fresh();

        $webhooks->dispatch($tenant, WebhookSubscription::EVENT_FEE_PAID, [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'student_id' => $invoice->student_id,
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
            'invoice_status' => $invoice->status,
            'balance' => (float) $invoice->balance,
        ]);

        return response()->json([
            'data' => [
                'payment' => $payment,
                'invoice' => $invoice,
            ],
        ], 201);
    }

    protected function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->can($permission), 403, 'Missing permission: '.$permission);
    }
}
