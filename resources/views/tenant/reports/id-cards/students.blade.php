@extends('layouts.app')

@section('title', __('ui.nav.student_id_cards'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.student_id_cards') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.student_id_cards_batch_help') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.reports_hub') }}</x-ui.button></a>
@endsection

@section('content')
    <form method="GET" action="{{ route('tenant.reports.id-cards.students.print') }}" target="_blank" class="grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-4">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.school_class') }}</label>
            <select name="school_class_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected((string) $filterClassId === (string) $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.section') }}</label>
            <select name="section_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) $filterSectionId === (string) $section->id)>{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.id_card_template') }}</label>
            <select name="template_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.default') }}</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" @selected((string) $filterTemplateId === (string) $template->id)>{{ $template->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <x-ui.button type="submit">{{ __('ui.print_batch') }}</x-ui.button>
        </div>
    </form>
    <p class="mt-3 text-xs text-muted">{{ __('ui.id_card_batch_hint') }}</p>
@endsection
