@extends('layouts.app')

@section('title', __('ui.nav.expense_reports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.expense_reports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.expense_reports_help') }}</p>
    </div>
@endsection

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.date_from') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.date_to') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <p class="mb-4 text-sm text-slate-700 dark:text-slate-200">
        {{ __('ui.grand_total') }}: <strong>{{ \Modules\Fees\Support\Money::formatBdt($grandTotal) }}</strong>
    </p>

    <x-ui.table :headers="[__('ui.expense_category'), __('ui.entries'), __('ui.total_bdt')]">
        @forelse ($byCategory as $row)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $row->category?->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $row->entries }}</td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($row->total) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
@endsection
