@extends('layouts.app')

@section('title', __('ui.nav.library_issues'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_issues') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_issues_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <p class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">{{ $errors->first() }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($issueStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.issue_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 pb-2 text-sm">
            <input type="checkbox" name="overdue" value="1" @checked($filterOverdue) class="rounded border-slate-300">
            {{ __('ui.overdue_only') }}
        </label>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('library.issue.manage')
        <form method="POST" action="{{ route('tenant.library.issues.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3" x-data="{ borrower: '{{ old('borrower_type', 'student') }}' }">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.copy') }}</label>
                <select name="library_copy_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($availableCopies as $copy)
                        <option value="{{ $copy->id }}">{{ $copy->accession_no }} — {{ $copy->book?->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.borrower_type') }}</label>
                <select name="borrower_type" x-model="borrower" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($borrowerTypes as $type)
                        <option value="{{ $type }}">{{ __('ui.borrower_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="borrower === 'student'">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_student') }}</label>
                <select name="student_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="borrower === 'staff'" style="display:none;">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.staff') }}</label>
                <select name="staff_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($staffMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.issued_on') }}</label>
                <input type="date" name="issued_on" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('issued_on', now('Asia/Dhaka')->toDateString()) }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.due_on') }}</label>
                <input type="date" name="due_on" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('due_on', now('Asia/Dhaka')->addDays($settings->default_loan_days)->toDateString()) }}">
            </div>
            <div class="md:col-span-3 flex items-end">
                <x-ui.button type="submit">{{ __('ui.issue_book') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.copy'), __('ui.book'), __('ui.borrower'), __('ui.issued_on'), __('ui.due_on'), __('ui.col_status'), __('ui.overdue'), __('ui.col_action')]">
        @forelse ($issues as $issue)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $issue->copy?->accession_no }}</td>
                <td class="px-4 py-3">{{ $issue->copy?->book?->title }}</td>
                <td class="px-4 py-3">{{ $issue->borrowerName() }} <span class="text-xs text-muted">({{ __('ui.borrower_'.$issue->borrower_type) }})</span></td>
                <td class="px-4 py-3 text-muted">{{ $issue->issued_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-muted">{{ $issue->due_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ __('ui.issue_status_'.$issue->status) }}</td>
                <td class="px-4 py-3">
                    @if ($issue->is_overdue)
                        <span class="inline-flex rounded-lg bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ __('ui.yes') }}</span>
                    @else
                        {{ __('ui.no') }}
                    @endif
                </td>
                <td class="px-4 py-3">
                    @can('library.issue.manage')
                        @if ($issue->status === 'issued')
                            <form method="POST" action="{{ route('tenant.library.issues.return', $issue) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-primary-600">{{ __('ui.return_book') }}</button>
                            </form>
                        @endif
                    @endcan
                    @can('library.fine.manage')
                        @if ($issue->is_overdue)
                            <form method="POST" action="{{ route('tenant.library.fines.recalculate', $issue) }}" class="mt-1">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-slate-600">{{ __('ui.recalculate_fine') }}</button>
                            </form>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $issues->links() }}</div>
@endsection
