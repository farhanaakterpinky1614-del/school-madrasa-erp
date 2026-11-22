@extends('layouts.app')

@section('title', __('ui.nav.reports_hub'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.reports_hub') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.reports_hub_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('tenant.reports.id-cards.students.index') }}" class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-5 transition hover:border-indigo-300 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.student_id_cards') }}</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.batch_print_id_cards') }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('ui.student_id_cards_hub_help') }}</p>
        </a>
        <a href="{{ route('tenant.reports.id-cards.staff.index') }}" class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-5 transition hover:border-indigo-300 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.staff_id_cards') }}</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ __('ui.staff_credentials') }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('ui.staff_id_cards_hub_help') }}</p>
        </a>
        <a href="{{ route('tenant.reports.certificates.templates.index') }}" class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-5 transition hover:border-indigo-300 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.certificates') }}</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $templateCount }} {{ __('ui.templates') }} · {{ $issueCount }} {{ __('ui.issued') }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('ui.certificates_hub_help') }}</p>
        </a>
        <a href="{{ route('tenant.reports.exports.index') }}" class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/50 to-slate-50 p-5 transition hover:border-indigo-300 dark:border-slate-700 dark:from-slate-900">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.exports') }}</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $completedExports }} / {{ $exportCount }} {{ __('ui.completed') }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('ui.exports_hub_help') }}</p>
        </a>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-slate-50 to-indigo-50/40 p-5 dark:border-slate-700 dark:from-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('ui.quick_links') }}</h2>
        <ul class="mt-3 flex flex-wrap gap-3 text-sm">
            <li><a class="text-primary-600 hover:underline" href="{{ route('tenant.reports.certificates.issues.index') }}">{{ __('ui.nav.certificate_issues') }}</a></li>
            <li><a class="text-primary-600 hover:underline" href="{{ route('tenant.reports.id-cards.students.index') }}">{{ __('ui.nav.student_id_cards') }}</a></li>
            <li><a class="text-primary-600 hover:underline" href="{{ route('tenant.reports.exports.index') }}">{{ __('ui.nav.report_exports') }}</a></li>
        </ul>
        <p class="mt-4 text-xs text-muted">{{ __('ui.reports_branding_note') }}</p>
    </div>
@endsection
