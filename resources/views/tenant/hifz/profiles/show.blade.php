@extends('layouts.app')

@section('title', $profile->student?->full_name ?? __('ui.nav.hifz_profiles'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $profile->student?->full_name }}</h1>
        <p class="mt-1.5 text-sm text-muted">
            {{ $profile->positionLabel() }}
            · {{ __('ui.hifz_status_'.$profile->status) }}
            · {{ $profile->assignedTeacher?->full_name ?? __('ui.unassigned') }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @canany(['hifz.profile.manage', 'hifz.progress.update'])
            <a href="{{ route('tenant.hifz.profiles.edit', $profile) }}"><x-ui.button type="button" variant="secondary">{{ __('ui.edit') }}</x-ui.button></a>
        @endcanany
        @can('hifz.progress.update')
            <a href="{{ route('tenant.hifz.progress.index', ['hifz_profile_id' => $profile->id]) }}"><x-ui.button type="button">{{ __('ui.log_progress') }}</x-ui.button></a>
        @endcan
        @can('hifz.revision.manage')
            <a href="{{ route('tenant.hifz.revisions.index', ['hifz_profile_id' => $profile->id]) }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.hifz_revisions') }}</x-ui.button></a>
        @endcan
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950 lg:col-span-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hifz_profile_details') }}</h2>
            <dl class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-muted">{{ __('ui.col_student') }}</dt><dd class="font-medium">{{ $profile->student?->full_name }} ({{ $profile->student?->student_code }})</dd></div>
                <div><dt class="text-muted">{{ __('ui.assigned_ustaz') }}</dt><dd class="font-medium">{{ $profile->assignedTeacher?->full_name ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.current_juz_para') }}</dt><dd class="font-medium">{{ $profile->current_juz }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.current_ayah') }}</dt><dd class="font-medium">{{ $profile->current_ayah }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.started_on') }}</dt><dd class="font-medium">{{ $profile->started_on?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.completed_on') }}</dt><dd class="font-medium">{{ $profile->completed_on?->format('d M Y') ?? '—' }}</dd></div>
            </dl>
            @if ($profile->notes)
                <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">{{ $profile->notes }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-4 text-white">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-indigo-200">{{ __('ui.quick_links') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a class="text-indigo-100 underline-offset-2 hover:underline" href="{{ route('tenant.hifz.progress.index', ['hifz_profile_id' => $profile->id]) }}">{{ __('ui.nav.hifz_progress') }}</a></li>
                <li><a class="text-indigo-100 underline-offset-2 hover:underline" href="{{ route('tenant.hifz.revisions.index', ['hifz_profile_id' => $profile->id]) }}">{{ __('ui.nav.hifz_revisions') }}</a></li>
                @can('hifz.report.view')
                    <li><a class="text-indigo-100 underline-offset-2 hover:underline" href="{{ route('tenant.hifz.reports.index') }}">{{ __('ui.nav.hifz_reports') }}</a></li>
                @endcan
            </ul>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.recent_progress') }}</h2>
            <x-ui.table :headers="[__('ui.date'), __('ui.range'), __('ui.quality')]">
                @forelse ($profile->progressLogs as $log)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $log->logged_on?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm text-muted">{{ $log->rangeLabel() }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->quality ? __('ui.hifz_quality_'.$log->quality) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </div>
        <div>
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.recent_dawr') }}</h2>
            <x-ui.table :headers="[__('ui.label'), __('ui.range'), __('ui.col_status')]">
                @forelse ($profile->revisions as $revision)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium">{{ $revision->label ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-muted">{{ $revision->rangeLabel() }}</td>
                        <td class="px-4 py-3 text-sm">{{ __('ui.hifz_revision_status_'.$revision->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
@endsection
