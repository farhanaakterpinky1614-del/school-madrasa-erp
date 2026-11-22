@extends('layouts.app')

@section('title', __('ui.nav.hifz_progress'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hifz_progress') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hifz_progress_help') }}</p>
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
                @foreach ($profiles as $p)
                    <option value="{{ $p->id }}" @selected((int) ($profile?->id) === (int) $p->id)>
                        {{ $p->student?->full_name }} — {{ $p->positionLabel() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.load') }}</x-ui.button></div>
    </form>

    @if ($profile)
        <div class="mb-6 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/20 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
            <p class="text-sm text-muted">{{ __('ui.current_position') }}: <strong class="text-slate-900 dark:text-slate-100">{{ $profile->positionLabel() }}</strong>
                · {{ __('ui.assigned_ustaz') }}: {{ $profile->assignedTeacher?->full_name ?? $profile->student?->full_name }}</p>
        </div>

        @can('hifz.progress.update')
            <form method="POST" action="{{ route('tenant.hifz.progress.store') }}" class="mb-8 max-w-4xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                @csrf
                <input type="hidden" name="hifz_profile_id" value="{{ $profile->id }}">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.log_progress') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-ui.input name="logged_on" type="date" :label="__('ui.date')" :value="old('logged_on', now('Asia/Dhaka')->toDateString())" required />
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.from_juz') }}</label>
                        <select name="from_juz" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                            @foreach ($juzOptions as $juz)
                                <option value="{{ $juz }}" @selected((int) old('from_juz', $profile->current_juz) === (int) $juz)>{{ $juz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.input name="from_ayah" type="number" min="1" :label="__('ui.from_ayah')" :value="old('from_ayah', 1)" required />
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.to_juz') }}</label>
                        <select name="to_juz" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                            @foreach ($juzOptions as $juz)
                                <option value="{{ $juz }}" @selected((int) old('to_juz', $profile->current_juz) === (int) $juz)>{{ $juz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.input name="to_ayah" type="number" min="1" :label="__('ui.to_ayah')" :value="old('to_ayah', $profile->current_ayah)" required />
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.quality') }}</label>
                        <select name="quality" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                            <option value="">—</option>
                            @foreach ($qualities as $q)
                                <option value="{{ $q }}" @selected(old('quality') === $q)>{{ __('ui.hifz_quality_'.$q) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-ui.textarea name="teacher_notes" :label="__('ui.teacher_notes')">{{ old('teacher_notes') }}</x-ui.textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm sm:col-span-2">
                        <input type="checkbox" name="update_profile_position" value="1" class="rounded border-slate-300" checked>
                        {{ __('ui.update_profile_position') }}
                    </label>
                </div>
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </form>
        @endcan

        <x-ui.table :headers="[__('ui.date'), __('ui.range'), __('ui.quality'), __('ui.teacher_notes'), __('ui.col_action')]">
            @forelse ($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ $log->logged_on?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ $log->rangeLabel() }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->quality ? __('ui.hifz_quality_'.$log->quality) : '—' }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ \Illuminate\Support\Str::limit($log->teacher_notes, 60) ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @can('hifz.progress.update')
                            <form method="POST" action="{{ route('tenant.hifz.progress.destroy', $log) }}" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
            @endforelse
        </x-ui.table>
        @if ($logs instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="mt-4">{{ $logs->links() }}</div>
        @endif
    @else
        <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-muted dark:border-slate-700 dark:bg-slate-900">{{ __('ui.select_hifz_student') }}</p>
    @endif
@endsection
