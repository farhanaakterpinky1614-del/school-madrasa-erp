@extends('layouts.app')

@section('title', __('ui.nav.staff_id_cards'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.staff_id_cards') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.staff_id_cards_help') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.reports_hub') }}</x-ui.button></a>
@endsection

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.employee_code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.col_name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.role') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @forelse ($staff as $row)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->employee_code }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->full_name }}</td>
                        <td class="px-4 py-3 text-muted">{{ $row->designation?->name ?? ($row->is_teacher ? __('ui.teacher') : __('ui.staff')) }}</td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('tenant.reports.id-cards.staff.show', $row) }}" class="text-primary-600 hover:underline">{{ __('ui.id_card') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
