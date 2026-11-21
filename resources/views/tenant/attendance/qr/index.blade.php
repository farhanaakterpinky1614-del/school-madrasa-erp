@extends('layouts.app')

@section('title', __('ui.nav.attendance_qr'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 08</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.attendance_qr') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.attendance_qr_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if (session('qr_token'))
        <p class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 font-mono text-sm text-indigo-900 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-100">
            {{ __('ui.attendance_qr_token_label') }}: {{ session('qr_token') }}
        </p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <x-ui.input name="date" type="date" :label="__('ui.attendance_date')" :value="$date" />
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>

    <form method="POST" action="{{ route('tenant.attendance.qr.store') }}" class="mb-8 space-y-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
        @csrf
        <h2 class="text-base font-semibold">{{ __('ui.generate_period_qr') }}</h2>
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[16rem] flex-1">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.routine_slot') }}</label>
                <select name="class_routine_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($routines as $routineSlot)
                        <option value="{{ $routineSlot->id }}">
                            {{ $days[$routineSlot->day_of_week] ?? $routineSlot->day_of_week }}
                            · {{ $routineSlot->period?->name }}
                            · {{ $routineSlot->schoolClass?->name }}
                            · {{ $routineSlot->subject?->name ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-ui.input name="valid_on" type="date" :label="__('ui.valid_on')" :value="$date" />
            <div class="flex items-end"><x-ui.button type="submit">{{ __('ui.generate_token') }}</x-ui.button></div>
        </div>
        <p class="text-xs text-muted">{{ __('ui.attendance_qr_stub_note') }}</p>
    </form>

    <x-ui.table :headers="[__('ui.routine_slot'), __('ui.valid_on'), __('ui.token'), __('ui.expires_at')]">
        @forelse ($tokens as $token)
            <tr>
                <td class="px-4 py-3">
                    {{ $token->classRoutine?->period?->name }}
                    · {{ $token->classRoutine?->schoolClass?->name }}
                    · {{ $token->classRoutine?->subject?->name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted">{{ optional($token->valid_on)->toDateString() }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $token->token }}</td>
                <td class="px-4 py-3 text-muted">{{ optional($token->expires_at)->toDateTimeString() ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
@endsection
