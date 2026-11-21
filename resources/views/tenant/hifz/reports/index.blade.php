@extends('layouts.app')

@section('title', __('ui.nav.hifz_reports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hifz_reports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hifz_reports_help') }}</p>
    </div>
    <a href="{{ route('tenant.hifz.reports.pdf') }}"><x-ui.button type="button" variant="outline">{{ __('ui.download_muhtamim_pdf') }}</x-ui.button></a>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.total_hifz_students') }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $summary['total_profiles'] }}</p>
        </div>
        @foreach ($summary['by_status'] as $status => $count)
            <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/20 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hifz_status_'.$status) }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $count }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.progress_bands') }}</h2>
            <x-ui.table :headers="[__('ui.progress_band'), __('ui.count')]">
                @foreach ($summary['by_band'] as $band)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $band['label'] }}</td>
                        <td class="px-4 py-3">{{ $band['count'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
        <div>
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.ustaz_load') }}</h2>
            <x-ui.table :headers="[__('ui.assigned_ustaz'), __('ui.count')]">
                @forelse ($summary['teacher_load'] as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                        <td class="px-4 py-3">{{ $row['count'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>

    <div>
        <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.recent_dawr') }}</h2>
        <x-ui.table :headers="[__('ui.hifz_student'), __('ui.label'), __('ui.range'), __('ui.scheduled_on'), __('ui.col_status')]">
            @forelse ($summary['recent_revisions'] as $revision)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $revision->profile?->student?->full_name }}</td>
                    <td class="px-4 py-3 text-sm">{{ $revision->label ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ $revision->rangeLabel() }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ $revision->scheduled_on?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm">{{ __('ui.hifz_revision_status_'.$revision->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
            @endforelse
        </x-ui.table>
    </div>
@endsection
