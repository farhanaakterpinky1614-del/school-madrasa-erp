@extends('layouts.app')

@section('title', __('ui.nav.attendance_reports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 08</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.attendance_reports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.attendance_reports_help') }}</p>
    </div>
@endsection

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.classes') }}</label>
            <select name="class_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.select_class') }}</option>
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
        <x-ui.input name="summary_date" type="date" :label="__('ui.summary_date')" :value="$summaryDate" />
        <x-ui.input name="from" type="date" :label="__('ui.date_from')" :value="$from" />
        <x-ui.input name="to" type="date" :label="__('ui.date_to')" :value="$to" />
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>

    @if (! $classId)
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.select_class_for_reports') }}</p>
    @else
        <section class="mb-8">
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.class_daily_summary') }}</h2>
            @if ($dailySummary)
                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-xl border border-slate-200 bg-white/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.total_marked') }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ $dailySummary['total'] }}</p>
                        <p class="text-xs text-muted">{{ $dailySummary['date'] }}</p>
                    </div>
                    @foreach ($dailySummary['counts'] as $status => $count)
                        <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.att_'.$status) }}</p>
                            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $count }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mb-8">
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.student_attendance_pct') }}</h2>
            <p class="mb-3 text-sm text-muted">{{ $from }} → {{ $to }}</p>
            <x-ui.table :headers="[__('ui.col_name'), __('ui.marked_days'), __('ui.att_present'), __('ui.att_late'), __('ui.att_absent'), __('ui.percentage')]">
                @forelse ($percentageRows as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row->student->full_name }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $row->marked_days }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $row->present_days }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $row->late_days }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $row->absent_days }}</td>
                        <td class="px-4 py-3 font-semibold tabular-nums">{{ $row->percentage !== null ? $row->percentage.'%' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold tracking-tight">{{ __('ui.absentee_list') }}</h2>
            <x-ui.table :headers="[__('ui.attendance_date'), __('ui.col_name'), __('ui.notes')]">
                @forelse ($absentees as $row)
                    <tr>
                        <td class="px-4 py-3 text-muted">{{ optional($row->attendance_date)->toDateString() }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->student?->full_name }}</td>
                        <td class="px-4 py-3">{{ $row->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-muted">{{ __('ui.no_absentees') }}</td></tr>
                @endforelse
            </x-ui.table>
        </section>
    @endif
@endsection
