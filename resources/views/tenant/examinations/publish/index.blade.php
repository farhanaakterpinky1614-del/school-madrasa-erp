@extends('layouts.app')

@section('title', __('ui.nav.publish_results'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.publish_results') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.publish_results_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

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
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-muted">{{ __('ui.marks_total') }}</p>
                <p class="mt-1 text-2xl font-semibold">{{ $markStats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-muted">{{ __('ui.marks_draft') }}</p>
                <p class="mt-1 text-2xl font-semibold text-amber-700">{{ $markStats['draft'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-muted">{{ __('ui.marks_submitted') }}</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ $markStats['submitted'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mb-4 rounded-xl border px-3 py-2 text-sm {{ $isPublished ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
            @if ($isPublished)
                {{ __('ui.exam_currently_published') }}
            @else
                {{ __('ui.exam_currently_unpublished') }}
            @endif
        </div>

        <form method="POST" action="{{ route('tenant.results.publish.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-5 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}" />
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.sections') }} ({{ __('ui.optional_whole_exam') }})</label>
                <select name="section_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.whole_exam') }}</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.textarea name="notes" :label="__('ui.notes')" />
            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit" name="action" value="publish" variant="success">{{ __('ui.publish_results') }}</x-ui.button>
                <x-ui.button type="submit" name="action" value="unpublish" variant="secondary">{{ __('ui.unpublish_results') }}</x-ui.button>
            </div>
            <p class="text-xs text-muted">{{ __('ui.publish_parent_note') }}</p>
        </form>

        @if ($exam->publications->isNotEmpty())
            <h2 class="mt-8 mb-3 text-lg font-semibold">{{ __('ui.publication_history') }}</h2>
            <x-ui.table :headers="[__('ui.nav.sections'), __('ui.published_at'), __('ui.notes')]">
                @foreach ($exam->publications as $pub)
                    <tr>
                        <td class="px-4 py-3">{{ $pub->section?->name ?? __('ui.whole_exam') }}</td>
                        <td class="px-4 py-3">{{ $pub->published_at?->timezone('Asia/Dhaka')->format('d M Y H:i') ?? __('ui.unpublished') }}</td>
                        <td class="px-4 py-3 text-muted">{{ $pub->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    @endif
@endsection
