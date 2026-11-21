@extends('layouts.app')

@section('title', __('ui.nav.daily_attendance'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 08</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.daily_attendance') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.daily_attendance_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @if ($scoped ?? false)
        <p class="mb-4 rounded-xl border border-slate-200 bg-gradient-to-r from-indigo-50/80 to-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900 dark:text-slate-200">
            {{ __('ui.attendance_teacher_scope_hint') }}
        </p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <x-ui.input name="date" type="date" :label="__('ui.attendance_date')" :value="$date" />
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.classes') }}</label>
            <select name="class_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected((int) $classId === (int) $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.sections') }}</label>
            <select name="section_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_sections') }}</option>
                @foreach ($sections as $section)
                    @if (! $classId || (int) $section->school_class_id === (int) $classId)
                        <option value="{{ $section->id }}" @selected((int) $sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.load_register') }}</x-ui.button></div>
    </form>

    @if ($students->isEmpty())
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.no_enrolled_students') }}</p>
    @else
        <form
            method="POST"
            action="{{ route('tenant.attendance.daily.store') }}"
            class="space-y-4"
            x-data="attendanceRegister()"
            @keydown.window="onHotkey($event)"
        >
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}" />
            <input type="hidden" name="school_class_id" value="{{ $classId }}" />
            <input type="hidden" name="section_id" value="{{ $sectionId }}" />

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
                            <tr
                                class="bg-white/80 hover:bg-indigo-50/40 dark:bg-slate-950/40 dark:hover:bg-slate-900"
                                :class="{ 'ring-2 ring-inset ring-primary-400/50': focusIndex === {{ $i }} }"
                                @click="focusIndex = {{ $i }}"
                            >
                                <td class="px-4 py-3 text-muted">{{ $student->roll_number ?? ($i + 1) }}</td>
                                <td class="px-4 py-3 font-medium">
                                    {{ $student->full_name }}
                                    <span class="ms-2 text-xs text-muted">{{ $student->student_code }}</span>
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
                                                <input
                                                    type="radio"
                                                    class="peer sr-only"
                                                    name="entries[{{ $i }}][status]"
                                                    value="{{ $st }}"
                                                    x-ref="status_{{ $i }}_{{ $st }}"
                                                    @checked($current === $st)
                                                />
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-800 dark:border-slate-600 dark:text-slate-300 dark:peer-checked:border-primary-400 dark:peer-checked:bg-primary-950/40 dark:peer-checked:text-primary-200"
                                                >
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
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.focusIndex = Math.min(this.focusIndex + 1, this.studentCount - 1);
                        }
                        if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.focusIndex = Math.max(this.focusIndex - 1, 0);
                        }
                    },
                };
            }
        </script>
    @endif
@endsection
