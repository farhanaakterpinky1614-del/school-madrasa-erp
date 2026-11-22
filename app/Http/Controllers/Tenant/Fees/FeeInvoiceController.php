<?php

namespace App\Http\Controllers\Tenant\Fees;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Fees\Concerns\LoadsFeeFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeeStructure;
use Modules\Fees\Services\InvoiceLedger;
use Modules\Students\Models\Student;

class FeeInvoiceController extends Controller
{
    use LoadsFeeFormOptions;

    public function index(Request $request): View
    {
        $query = FeeInvoice::query()
            ->with(['student', 'structure.category'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->integer('student_id'));
        }

        return view('tenant.fees.invoices.index', array_merge($this->feeFormOptions(), [
            'invoices' => $query->paginate(20)->withQueryString(),
            'filterStatus' => $request->string('status')->toString(),
            'filterStudentId' => $request->integer('student_id') ?: null,
        ]));
    }

    public function create(): View
    {
        return view('tenant.fees.invoices.create', $this->feeFormOptions());
    }

    public function store(Request $request, InvoiceLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'fee_structure_id' => ['nullable', 'integer', Rule::exists('fee_structures', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'issue_now' => ['sometimes', 'boolean'],
        ]);

        $student = Student::query()->findOrFail($data['student_id']);

        if (! empty($data['fee_structure_id']) && ! $request->filled('title')) {
            $structure = FeeStructure::query()->findOrFail($data['fee_structure_id']);
            $data['title'] = $structure->name;
            $data['subtotal'] = $structure->amount;
        }

        $invoice = $ledger->createManual($student, $data, $request->user()?->id);

        if ($request->boolean('issue_now')) {
            $ledger->issue($invoice);
        }

        return redirect()
            ->route('tenant.fees.invoices.show', $invoice)
            ->with('status', __('ui.fee_invoice_saved'));
    }

    public function show(FeeInvoice $invoice): View
    {
        $this->assertTenantOwned($invoice);
        $invoice->load(['student', 'structure.category', 'discounts', 'payments.receivedBy']);

        return view('tenant.fees.invoices.show', array_merge($this->feeFormOptions(), [
            'invoice' => $invoice,
        ]));
    }

    public function issue(FeeInvoice $invoice, InvoiceLedger $ledger): RedirectResponse
    {
        $this->assertTenantOwned($invoice);
        $ledger->issue($invoice);

        return back()->with('status', __('ui.fee_invoice_issued'));
    }

    public function cancel(FeeInvoice $invoice, InvoiceLedger $ledger): RedirectResponse
    {
        $this->assertTenantOwned($invoice);
        $ledger->cancel($invoice);

        return back()->with('status', __('ui.fee_invoice_cancelled'));
    }
}
