@extends('layouts.app')

@section('title', __('ui.nav.library_fines'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_fines') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_fines_help') }}</p>
    </div>
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
                @foreach ($fineStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.fine_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <x-ui.table :headers="[__('ui.book'), __('ui.borrower'), __('ui.days_overdue'), __('ui.amount_bdt'), __('ui.col_status'), __('ui.fees_note'), __('ui.col_action')]">
        @forelse ($fines as $fine)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $fine->issue?->copy?->book?->title }}</td>
                <td class="px-4 py-3">{{ $fine->issue?->borrowerName() }}</td>
                <td class="px-4 py-3">{{ $fine->days_overdue }}</td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($fine->amount) }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.fine_status_'.$fine->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-muted text-xs">{{ $fine->fees_note ?: '—' }}</td>
                <td class="px-4 py-3 space-y-1">
                    @can('library.fine.manage')
                        @if ($fine->status === 'pending')
                            <form method="POST" action="{{ route('tenant.library.fines.paid', $fine) }}" class="flex flex-col gap-1">
                                @csrf
                                <input name="fees_note" placeholder="{{ __('ui.fees_note_placeholder') }}" class="rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900" value="{{ $fine->fees_note }}">
                                <button type="submit" class="text-left text-sm font-semibold text-emerald-700">{{ __('ui.mark_paid') }}</button>
                            </form>
                            <form method="POST" action="{{ route('tenant.library.fines.waived', $fine) }}">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-amber-700">{{ __('ui.mark_waived') }}</button>
                            </form>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $fines->links() }}</div>
@endsection
