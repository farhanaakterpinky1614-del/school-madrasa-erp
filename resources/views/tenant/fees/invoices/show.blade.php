@extends('layouts.app')

@section('title', $invoice->invoice_number)

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $invoice->invoice_number }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $invoice->title }} · {{ $invoice->student?->full_name }}</p>
    </div>
    <a href="{{ route('tenant.fees.invoices.index') }}" class="text-sm font-semibold text-primary-600">{{ __('ui.back') }}</a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mb-6 grid gap-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-5 dark:border-slate-700 dark:from-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</p>
            <p class="mt-1 font-semibold">{{ __('ui.invoice_status_'.$invoice->status) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.subtotal_bdt') }}</p>
            <p class="mt-1 font-semibold">{{ \Modules\Fees\Support\Money::formatBdt($invoice->subtotal) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.discount_bdt') }}</p>
            <p class="mt-1 font-semibold">{{ \Modules\Fees\Support\Money::formatBdt($invoice->discount_total) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.balance_bdt') }}</p>
            <p class="mt-1 font-semibold text-primary-700">{{ \Modules\Fees\Support\Money::formatBdt($invoice->balance) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.total_bdt') }}</p>
            <p class="mt-1">{{ \Modules\Fees\Support\Money::formatBdt($invoice->total) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.paid_bdt') }}</p>
            <p class="mt-1">{{ \Modules\Fees\Support\Money::formatBdt($invoice->paid_amount) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.issue_date') }}</p>
            <p class="mt-1">{{ $invoice->issue_date?->format('Y-m-d') ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.due_date') }}</p>
            <p class="mt-1">{{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</p>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @can('fees.invoice.manage')
            @if ($invoice->status === 'draft')
                <form method="POST" action="{{ route('tenant.fees.invoices.issue', $invoice) }}">
                    @csrf
                    <x-ui.button type="submit">{{ __('ui.issue_invoice') }}</x-ui.button>
                </form>
            @endif
            @if (in_array($invoice->status, ['draft', 'issued', 'partial'], true) && (float) $invoice->paid_amount <= 0)
                <form method="POST" action="{{ route('tenant.fees.invoices.cancel', $invoice) }}" onsubmit="return confirm(@json(__('ui.confirm_cancel_invoice')))">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">{{ __('ui.cancel_invoice') }}</x-ui.button>
                </form>
            @endif
        @endcan
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <section>
            <h2 class="mb-3 text-lg font-semibold">{{ __('ui.discounts_waivers') }}</h2>
            <x-ui.table :headers="[__('ui.label'), __('ui.type'), __('ui.amount_bdt'), __('ui.col_action')]">
                @forelse ($invoice->discounts as $discount)
                    <tr>
                        <td class="px-4 py-3">{{ $discount->label }}</td>
                        <td class="px-4 py-3 text-muted">{{ __('ui.discount_type_'.$discount->type) }}@if($discount->percent) ({{ $discount->percent }}%)@endif</td>
                        <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($discount->amount) }}</td>
                        <td class="px-4 py-3">
                            @can('fees.discount.manage')
                                @if ($invoice->acceptsDiscounts())
                                    <form method="POST" action="{{ route('tenant.fees.discounts.destroy', [$invoice, $discount]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>

            @can('fees.discount.manage')
                @if ($invoice->acceptsDiscounts())
                    <form method="POST" action="{{ route('tenant.fees.discounts.store', $invoice) }}" class="mt-4 space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.type') }}</label>
                                <select name="type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                                    @foreach ($discountTypes as $type)
                                        <option value="{{ $type }}">{{ __('ui.discount_type_'.$type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.label') }}</label>
                                <input name="label" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.percent') }}</label>
                                <input type="number" step="0.01" name="percent" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.amount_bdt') }}</label>
                                <input type="number" step="0.01" name="amount" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                        </div>
                        <x-ui.button type="submit">{{ __('ui.apply_discount') }}</x-ui.button>
                    </form>
                @endif
            @endcan
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">{{ __('ui.payments') }}</h2>
            <x-ui.table :headers="[__('ui.receipt_number'), __('ui.amount_bdt'), __('ui.payment_method'), __('ui.paid_on'), __('ui.col_action')]">
                @forelse ($invoice->payments as $payment)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $payment->payment_number }}</td>
                        <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($payment->amount) }}</td>
                        <td class="px-4 py-3">{{ __('ui.pay_method_'.$payment->method) }}</td>
                        <td class="px-4 py-3 text-muted">{{ $payment->paid_on?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @can('fees.receipt.export')
                                <a href="{{ route('tenant.fees.payments.receipt', $payment) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.receipt_pdf') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>

            @can('fees.payment.create')
                @if ($invoice->acceptsPayments())
                    <form method="POST" action="{{ route('tenant.fees.payments.store') }}" class="mt-4 space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        @csrf
                        <input type="hidden" name="fee_invoice_id" value="{{ $invoice->id }}">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.amount_bdt') }}</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance }}" name="amount" required value="{{ $invoice->balance }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.payment_method') }}</label>
                                <select name="method" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}">{{ __('ui.pay_method_'.$method) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.paid_on') }}</label>
                                <input type="date" name="paid_on" required value="{{ now('Asia/Dhaka')->toDateString() }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">{{ __('ui.reference') }}</label>
                                <input name="reference" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                            </div>
                        </div>
                        <x-ui.button type="submit">{{ __('ui.record_payment') }}</x-ui.button>
                    </form>
                @endif
            @endcan
        </section>
    </div>
@endsection
