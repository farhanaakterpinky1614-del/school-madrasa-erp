<?php

namespace App\Http\Controllers\Tenant\Fees;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Fees\Concerns\LoadsFeeFormOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Services\InvoiceLedger;
use Modules\Tenancy\Models\Institution;

class FeePaymentController extends Controller
{
    use LoadsFeeFormOptions;

    public function index(Request $request): View
    {
        $query = FeePayment::query()
            ->with(['invoice.student'])
            ->orderByDesc('paid_on')
            ->orderByDesc('id');

        if ($request->filled('method')) {
            $query->where('method', $request->string('method'));
        }

        return view('tenant.fees.payments.index', array_merge($this->feeFormOptions(), [
            'payments' => $query->paginate(20)->withQueryString(),
            'filterMethod' => $request->string('method')->toString(),
            'openInvoices' => FeeInvoice::query()
                ->with('student')
                ->whereIn('status', [FeeInvoice::STATUS_ISSUED, FeeInvoice::STATUS_PARTIAL])
                ->where('balance', '>', 0)
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
        ]));
    }

    public function store(Request $request, InvoiceLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'fee_invoice_id' => ['required', 'integer', Rule::exists('fee_invoices', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(FeePayment::METHODS)],
            'paid_on' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = FeeInvoice::query()->findOrFail($data['fee_invoice_id']);
        $this->assertTenantOwned($invoice);

        $payment = $ledger->recordPayment($invoice, $data, $request->user()?->id);

        return redirect()
            ->route('tenant.fees.invoices.show', $invoice)
            ->with('status', __('ui.fee_payment_saved', ['number' => $payment->payment_number]));
    }

    public function receipt(FeePayment $payment): Response
    {
        $this->assertTenantOwned($payment);
        $payment->load(['invoice.student', 'receivedBy']);
        $institution = Institution::query()->first();

        $pdf = Pdf::loadView('tenant.fees.pdf.receipt', [
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'institution' => $institution,
        ])->setPaper('a4');

        return $pdf->download('receipt-'.$payment->payment_number.'.pdf');
    }
}
