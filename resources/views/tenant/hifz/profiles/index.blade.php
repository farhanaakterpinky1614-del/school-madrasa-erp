@extends('layouts.app')

@section('title', __('ui.nav.hifz_profiles'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hifz_profiles') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hifz_profiles_help') }}</p>
    </div>
    @can('hifz.profile.manage')
        <a href="{{ route('tenant.hifz.profiles.create') }}"><x-ui.button type="button">{{ __('ui.new_hifz_profile') }}</x-ui.button></a>
    @endcan
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @if ($scoped ?? false)
        <p class="mb-4 rounded-xl border border-slate-200 bg-gradient-to-r from-indigo-50/80 to-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900 dark:text-slate-200">
            {{ __('ui.hifz_teacher_scope_hint') }}
        </p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.hifz_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.progress_band') }}</label>
            <select name="band" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_bands') }}</option>
                @foreach ($progressBands as $key => $band)
                    <option value="{{ $key }}" @selected($filterBand === $key)>{{ $band['label'] }}</option>
                @endforeach
            </select>
        </div>
        @unless ($scoped ?? false)
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.assigned_ustaz') }}</label>
                <select name="teacher_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.all_teachers') }}</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) $filterTeacherId === (int) $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
        @endunless
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>

    <x-ui.table :headers="[__('ui.col_student'), __('ui.col_status'), __('ui.current_position'), __('ui.assigned_ustaz'), __('ui.progress_logs'), __('ui.col_action')]">
        @forelse ($profiles as $profile)
            <tr>
                <td class="px-4 py-3 font-medium">
                    {{ $profile->student?->full_name }}
                    <span class="ms-2 text-xs text-muted">{{ $profile->student?->student_code }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.hifz_status_'.$profile->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-muted">{{ $profile->positionLabel() }}</td>
                <td class="px-4 py-3 text-muted">{{ $profile->assignedTeacher?->full_name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $profile->progress_logs_count }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.hifz.profiles.show', $profile) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $profiles->links() }}</div>
@endsection
