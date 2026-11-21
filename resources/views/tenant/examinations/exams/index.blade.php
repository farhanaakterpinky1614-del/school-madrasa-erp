@extends('layouts.app')

@section('title', __('ui.nav.exams'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.exams') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.exams_help') }}</p>
    </div>
    @can('exams.exam.manage')
        <a href="{{ route('tenant.exams.create') }}"><x-ui.button type="button">{{ __('ui.new_exam') }}</x-ui.button></a>
    @endcan
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @if ($scoped ?? false)
        <p class="mb-4 rounded-xl border border-slate-200 bg-gradient-to-r from-indigo-50/80 to-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900 dark:text-slate-200">
            {{ __('ui.exam_teacher_scope_hint') }}
        </p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.exam_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.classes') }}</label>
            <select name="class_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_classes') }}</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected((int) $filterClassId === (int) $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>

    <x-ui.table :headers="[__('ui.col_name'), __('ui.nav.classes'), __('ui.col_status'), __('ui.exam_dates'), __('ui.papers'), __('ui.col_action')]">
        @forelse ($exams as $exam)
            <tr>
                <td class="px-4 py-3 font-medium">
                    {{ $exam->name }}
                    @if ($exam->code)<span class="ms-2 text-xs text-muted">{{ $exam->code }}</span>@endif
                    @if ($exam->board_name)
                        <span class="ms-2 inline-flex rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">{{ $exam->board_name }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-muted">{{ $exam->schoolClass?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.exam_status_'.$exam->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-muted text-sm">
                    {{ $exam->starts_on?->format('d M Y') ?? '—' }}
                    →
                    {{ $exam->ends_on?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3">{{ $exam->subjects_count }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.exams.show', $exam) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $exams->links() }}</div>
@endsection
