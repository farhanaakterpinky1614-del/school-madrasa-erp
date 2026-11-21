@extends('layouts.app')

@section('title', __('ui.nav.period_attendance'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 08</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.period_attendance') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.period_attendance_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <x-ui.input name="date" type="date" :label="__('ui.attendance_date')" :value="$date" />
        <div class="min-w-[16rem] flex-1">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.routine_slot') }}</label>
            <select name="routine_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @forelse ($routinesForDay as $routineSlot)
                    <option value="{{ $routineSlot->id }}" @selected((int) $routineId === (int) $routineSlot->id)>
                        {{ $days[$routineSlot->day_of_week] ?? $routineSlot->day_of_week }}
                        · {{ $routineSlot->period?->name }}
                        · {{ $routineSlot->schoolClass?->name }}{{ $routineSlot->section ? ' / '.$routineSlot->section->name : '' }}
                        · {{ $routineSlot->subject?->name ?? '—' }}
                    </option>
                @empty
                    <option value="">{{ __('ui.no_routines_for_day') }}</option>
                @endforelse
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.load_register') }}</x-ui.button></div>
    </form>

    @if (! $routine)
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.no_routines_for_day') }}</p>
    @elseif ($students->isEmpty())
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.no_enrolled_students') }}</p>
    @else
        <div class="mb-4 rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-900/50">
            <span class="font-semibold">{{ $routine->period?->name }}</span>
            <span class="text-muted"> · {{ $routine->subject?->name ?? '—' }} · {{ $routine->staff?->full_name ?? '—' }}</span>
        </div>

        <form method="POST" action="{{ route('tenant.attendance.period.store') }}" class="space-y-4" x-data="attendanceRegister()" @keydown.window="onHotkey($event)">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}" />
            <input type="hidden" name="class_routine_id" value="{{ $routine->id }}" />

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="markAll('present')" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                    {{ __('ui.mark_all_present') }}
                </button>
                <button type="button" @click="markAll('absent')" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                    {{ __('ui.mark_all_absent') }}
                </button>
                <p class="text-xs text-muted">{{ __('ui.attendance_keyboard_hint') }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/80 dark:bg-slate-900/60">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold">#</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.col_name') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.col_status') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($students as $i => $student)
                            @php $row = $existing->get($student->id); $current = $row?->status ?? 'present'; @endphp
                            <tr class="bg-white/80 dark:bg-slate-950/40" :class="{ 'ring-2 ring-inset ring-primary-400/50': focusIndex === {{ $i }} }" @click="focusIndex = {{ $i }}">
                                <td class="px-4 py-3 text-muted">{{ $student->roll_number ?? ($i + 1) }}</td>
                                <td class="px-4 py-3 font-medium">
                                    {{ $student->full_name }}
                                    <input type="hidden" name="entries[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1" role="group" aria-label="{{ __('ui.col_status') }}">
                                        @foreach ($statuses as $st)
                                            @php
                                                $abbr = match ($st) {
                                                    'present' => 'P',
                                                    'absent' => 'A',
                                                    'late' => 'L',
                                                    'excused' => 'E',
                                                    'leave' => 'V',
                                                    default => strtoupper(substr($st, 0, 1)),
                                                };
                                            @endphp
                                            <label class="cursor-pointer">
                                                <input type="radio" class="peer sr-only" name="entries[{{ $i }}][status]" value="{{ $st }}" x-ref="status_{{ $i }}_{{ $st }}" @checked($current === $st) />
                                                <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-800 dark:border-slate-600 dark:text-slate-300 dark:peer-checked:border-primary-400 dark:peer-checked:bg-primary-950/40 dark:peer-checked:text-primary-200">
                                                    <span aria-hidden="true">{{ $abbr }}</span>
                                                    <span>{{ __('ui.att_'.$st) }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="entries[{{ $i }}][notes]" value="{{ $row?->notes ?? '' }}" class="w-full min-w-[8rem] rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.button type="submit">{{ __('ui.mark_attendance') }}</x-ui.button>
        </form>

        <script>
            function attendanceRegister() {
                return {
                    focusIndex: 0,
                    studentCount: {{ $students->count() }},
                    markAll(status) {
                        for (let i = 0; i < this.studentCount; i++) {
                            const el = this.$refs['status_' + i + '_' + status];
                            if (el) el.checked = true;
                        }
                    },
                    onHotkey(e) {
                        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && e.target.type !== 'radio') return;
                        const map = { p: 'present', a: 'absent', l: 'late', e: 'excused', v: 'leave' };
                        const key = e.key.toLowerCase();
                        if (map[key]) {
                            e.preventDefault();
                            const el = this.$refs['status_' + this.focusIndex + '_' + map[key]];
                            if (el) el.checked = true;
                            this.focusIndex = Math.min(this.focusIndex + 1, this.studentCount - 1);
                        }
                        if (e.key === 'ArrowDown') { e.preventDefault(); this.focusIndex = Math.min(this.focusIndex + 1, this.studentCount - 1); }
                        if (e.key === 'ArrowUp') { e.preventDefault(); this.focusIndex = Math.max(this.focusIndex - 1, 0); }
                    },
                };
            }
        </script>
    @endif
@endsection
