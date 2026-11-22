@extends('layouts.app')

@section('title', __('ui.nav.certificate_issues'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.certificate_issues') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.certificate_issues_help') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.reports.certificates.templates.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.certificate_templates') }}</x-ui.button></a>
        <a href="{{ route('tenant.reports.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.reports_hub') }}</x-ui.button></a>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @can('reports.certificates.issue')
        <form method="POST" action="{{ route('tenant.reports.certificates.issues.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.template') }}</label>
                <select name="certificate_template_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }} ({{ __('ui.certificate_type_'.$template->certificate_type) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.student') }}</label>
                <select name="student_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->student_code }} — {{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.notes') }}</label>
                <input name="notes" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('notes') }}">
            </div>
            <div class="md:col-span-2">
                <x-ui.button type="submit">{{ __('ui.issue_certificate') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.issue_number') }}</th>
                    <th class="px-4 py-3">{{ __('ui.student') }}</th>
                    <th class="px-4 py-3">{{ __('ui.template') }}</th>
                    <th class="px-4 py-3">{{ __('ui.issued_at') }}</th>
                    <th class="px-4 py-3">{{ __('ui.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @forelse ($issues as $issue)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $issue->issue_number }}</td>
                        <td class="px-4 py-3">{{ $issue->student?->full_name }} <span class="text-xs text-muted">({{ $issue->student?->student_code }})</span></td>
                        <td class="px-4 py-3">{{ $issue->template?->name }}</td>
                        <td class="px-4 py-3">{{ optional($issue->issued_at)?->timezone('Asia/Dhaka')->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ __('ui.certificate_status_'.$issue->status) }}</td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('tenant.reports.certificates.issues.pdf', $issue) }}" class="text-primary-600 hover:underline">{{ __('ui.download_pdf') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $issues->links() }}</div>
@endsection
