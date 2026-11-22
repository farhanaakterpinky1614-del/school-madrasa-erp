@extends('layouts.app')

@section('title', __('ui.nav.fee_invoices'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.fee_invoices') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.fee_invoices_help') }}</p>
    </div>
    @can('fees.invoice.manage')
        <a href="{{ route('tenant.fees.invoices.create') }}"><x-ui.button type="button">{{ __('ui.new_invoice') }}</x-ui.button></a>
    @endcan
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($invoiceStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.invoice_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_student') }}</label>
            <select name="student_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_students') }}</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((int) $filterStudentId === (int) $student->id)>{{ $student->full_name }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <x-ui.table :headers="[__('ui.invoice_number'), __('ui.col_student'), __('ui.title'), __('ui.col_status'), __('ui.total_bdt'), __('ui.balance_bdt'), __('ui.due_date'), __('ui.col_action')]">
        @forelse ($invoices as $invoice)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $invoice->invoice_number }}</td>
                <td class="px-4 py-3">{{ $invoice->student?->full_name }}</td>
                <td class="px-4 py-3 text-muted">{{ $invoice->title }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.invoice_status_'.$invoice->status) }}
                    </span>
                </td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($invoice->total) }}</td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($invoice->balance) }}</td>
                <td class="px-4 py-3 text-muted">{{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.fees.invoices.show', $invoice) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
