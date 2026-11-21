@extends('layouts.app')

@section('title', __('ui.pdf_exports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.pdf_exports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.pdf_exports_help') }}</p>
    </div>
@endsection

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.exams') }}</label>
            <select name="exam_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($exams as $ex)
                    <option value="{{ $ex->id }}" @selected((int) $examId === (int) $ex->id)>{{ $ex->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.load') }}</x-ui.button></div>
    </form>

    @if (! $exam)
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.no_records') }}</p>
    @else
        <div class="mb-6">
            <a href="{{ route('tenant.results.pdf.class', $exam) }}">
                <x-ui.button type="button">{{ __('ui.download_class_sheet') }}</x-ui.button>
            </a>
            <p class="mt-2 text-xs text-muted">{{ __('ui.pdf_bangla_font_note') }}</p>
        </div>

        <x-ui.table :headers="['#', __('ui.col_name'), __('ui.col_action')]">
            @forelse ($students as $enrollment)
                <tr>
                    <td class="px-4 py-3 text-muted">{{ $enrollment->roll_number }}</td>
                    <td class="px-4 py-3 font-medium">{{ $enrollment->student?->full_name }}</td>
                    <td class="px-4 py-3">
                        @if ($enrollment->student)
                            <a href="{{ route('tenant.results.pdf.marksheet', [$exam, $enrollment->student]) }}" class="text-sm font-semibold text-primary-600">
                                {{ __('ui.download_marksheet') }}
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-muted">{{ __('ui.no_enrolled_students') }}</td></tr>
            @endforelse
        </x-ui.table>
    @endif
@endsection
