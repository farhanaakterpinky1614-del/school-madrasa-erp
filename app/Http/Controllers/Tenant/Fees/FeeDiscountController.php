<?php

namespace App\Http\Controllers\Tenant\Fees;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Services\InvoiceLedger;

class FeeDiscountController extends Controller
{
    public function store(Request $request, FeeInvoice $invoice, InvoiceLedger $ledger): RedirectResponse
    {
        $this->assertTenantOwned($invoice);

        $data = $request->validate([
            'type' => ['required', Rule::in(FeeDiscount::TYPES)],
            'label' => ['required', 'string', 'max:255'],
            'percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['type'] === FeeDiscount::TYPE_PERCENT) {
            $request->validate(['percent' => ['required', 'numeric', 'min:0.01', 'max:100']]);
        } elseif (in_array($data['type'], [FeeDiscount::TYPE_FIXED], true)) {
            $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);
        }

        $ledger->applyDiscount($invoice, $data, $request->user()?->id);

        return back()->with('status', __('ui.fee_discount_saved'));
    }

    public function destroy(FeeInvoice $invoice, FeeDiscount $discount, InvoiceLedger $ledger): RedirectResponse
    {
        $this->assertTenantOwned($invoice);
        $this->assertTenantOwned($discount);
        abort_unless((int) $discount->fee_invoice_id === (int) $invoice->id, 404);
        abort_unless($invoice->acceptsDiscounts(), 422);

        $discount->delete();
        $ledger->recalculate($invoice->fresh());

        return back()->with('status', __('ui.fee_discount_deleted'));
    }
}
