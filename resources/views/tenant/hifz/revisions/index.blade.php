@extends('layouts.app')

@section('title', __('ui.nav.hifz_revisions'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hifz_revisions') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hifz_revisions_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hifz_student') }}</label>
            <select name="hifz_profile_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_students') }}</option>
                @foreach ($profiles as $p)
                    <option value="{{ $p->id }}" @selected((int) $filterProfileId === (int) $p->id)>{{ $p->student?->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($revisionStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.hifz_revision_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>

    @can('hifz.revision.manage')
        <form method="POST" action="{{ route('tenant.hifz.revisions.store') }}" class="mb-8 max-w-4xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            @csrf
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.new_dawr_cycle') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hifz_student') }}</label>
                    <select name="hifz_profile_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($profiles as $p)
                            <option value="{{ $p->id }}" @selected((int) old('hifz_profile_id', $filterProfileId) === (int) $p->id)>{{ $p->student?->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.input name="label" :label="__('ui.label')" :value="old('label')" />
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.from_juz') }}</label>
                    <select name="from_juz" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($juzOptions as $juz)
                            <option value="{{ $juz }}" @selected((int) old('from_juz', 1) === (int) $juz)>{{ $juz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.to_juz') }}</label>
                    <select name="to_juz" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($juzOptions as $juz)
                            <option value="{{ $juz }}" @selected((int) old('to_juz', 1) === (int) $juz)>{{ $juz }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.input name="scheduled_on" type="date" :label="__('ui.scheduled_on')" :value="old('scheduled_on', now('Asia/Dhaka')->toDateString())" />
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
                    <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($revisionStatuses as $st)
                            <option value="{{ $st }}" @selected(old('status', 'scheduled') === $st)>{{ __('ui.hifz_revision_status_'.$st) }}</option>
                        @endforeach
                    </select>
                </div>
                @unless ($scoped ?? false)
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.assigned_ustaz') }}</label>
                        <select name="assigned_teacher_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                            <option value="">—</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((int) old('assigned_teacher_id') === (int) $teacher->id)>{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-ui.textarea name="notes" :label="__('ui.notes')">{{ old('notes') }}</x-ui.textarea>
                </div>
            </div>
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.hifz_student'), __('ui.label'), __('ui.range'), __('ui.scheduled_on'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($revisions as $revision)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $revision->profile?->student?->full_name }}</td>
                <td class="px-4 py-3 text-sm">{{ $revision->label ?: '—' }}</td>
                <td class="px-4 py-3 text-sm text-muted">{{ $revision->rangeLabel() }}</td>
                <td class="px-4 py-3 text-sm text-muted">{{ $revision->scheduled_on?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.hifz_revision_status_'.$revision->status) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @can('hifz.revision.manage')
                        @if ($revision->status !== 'completed')
                            <form method="POST" action="{{ route('tenant.hifz.revisions.update', $revision) }}" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="from_juz" value="{{ $revision->from_juz }}">
                                <input type="hidden" name="to_juz" value="{{ $revision->to_juz }}">
                                <input type="hidden" name="status" value="completed">
                                <input type="hidden" name="label" value="{{ $revision->label }}">
                                <button type="submit" class="text-sm font-semibold text-emerald-700">{{ __('ui.mark_completed') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('tenant.hifz.revisions.destroy', $revision) }}" class="inline ms-3" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $revisions->links() }}</div>
@endsection
