@extends('layouts.app')

@section('title', __('ui.nav.library_reports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_reports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_reports_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.copy_status_available') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $counts['available'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.copy_status_issued') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $counts['issued'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-rose-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.overdue') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $counts['overdue'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-amber-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.pending_fines') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ \Modules\Fees\Support\Money::formatBdt($counts['pending_fines_bdt']) }}</p>
        </div>
    </div>

    <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.circulation_list') }}</h2>
    <x-ui.table :headers="[__('ui.copy'), __('ui.book'), __('ui.borrower'), __('ui.due_on'), __('ui.overdue')]">
        @forelse ($openIssues as $issue)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $issue->copy?->accession_no }}</td>
                <td class="px-4 py-3">{{ $issue->copy?->book?->title }}</td>
                <td class="px-4 py-3">{{ $issue->borrowerName() }}</td>
                <td class="px-4 py-3 text-muted">{{ $issue->due_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ $issue->is_overdue ? __('ui.yes') : __('ui.no') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>

    <h2 class="mb-3 mt-8 text-lg font-semibold tracking-tight">{{ __('ui.overdue_report') }}</h2>
    <x-ui.table :headers="[__('ui.copy'), __('ui.book'), __('ui.borrower'), __('ui.due_on'), __('ui.days_overdue')]">
        @forelse ($overdueIssues as $issue)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $issue->copy?->accession_no }}</td>
                <td class="px-4 py-3">{{ $issue->copy?->book?->title }}</td>
                <td class="px-4 py-3">{{ $issue->borrowerName() }}</td>
                <td class="px-4 py-3 text-muted">{{ $issue->due_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ max(0, $issue->due_on->diffInDays(now('Asia/Dhaka')->startOfDay())) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
@endsection
