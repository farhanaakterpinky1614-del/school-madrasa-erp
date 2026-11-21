@extends('layouts.app')

@section('title', __('ui.nav.fee_payments'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.fee_payments') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.fee_payments_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @can('fees.payment.create')
        <form method="POST" action="{{ route('tenant.fees.payments.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.invoice') }}</label>
                <select name="fee_invoice_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    <option value="">{{ __('ui.select_invoice') }}</option>
                    @foreach ($openInvoices as $inv)
                        <option value="{{ $inv->id }}">
                            {{ $inv->invoice_number }} — {{ $inv->student?->full_name }} ({{ \Modules\Fees\Support\Money::formatBdt($inv->balance) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.amount_bdt') }}</label>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.payment_method') }}</label>
                <select name="method" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}">{{ __('ui.pay_method_'.$method) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.paid_on') }}</label>
                <input type="date" name="paid_on" required value="{{ now('Asia/Dhaka')->toDateString() }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.reference') }}</label>
                <input name="reference" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div class="md:col-span-3">
                <x-ui.button type="submit">{{ __('ui.record_payment') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.payment_method') }}</label>
            <select name="method" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method }}" @selected($filterMethod === $method)>{{ __('ui.pay_method_'.$method) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <x-ui.table :headers="[__('ui.receipt_number'), __('ui.invoice_number'), __('ui.col_student'), __('ui.amount_bdt'), __('ui.payment_method'), __('ui.paid_on'), __('ui.col_action')]">
        @forelse ($payments as $payment)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $payment->payment_number }}</td>
                <td class="px-4 py-3 text-muted">{{ $payment->invoice?->invoice_number }}</td>
                <td class="px-4 py-3">{{ $payment->invoice?->student?->full_name }}</td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($payment->amount) }}</td>
                <td class="px-4 py-3">{{ __('ui.pay_method_'.$payment->method) }}</td>
                <td class="px-4 py-3 text-muted">{{ $payment->paid_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3 space-x-3">
                    @if ($payment->invoice)
                        <a href="{{ route('tenant.fees.invoices.show', $payment->invoice) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a>
                    @endif
                    @can('fees.receipt.export')
                        <a href="{{ route('tenant.fees.payments.receipt', $payment) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.receipt_pdf') }}</a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
