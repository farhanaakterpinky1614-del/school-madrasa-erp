@extends('layouts.app')

@section('title', __('ui.nav.certificate_templates'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.certificate_templates') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.certificate_templates_help') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.reports.certificates.issues.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.certificate_issues') }}</x-ui.button></a>
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

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('reports.certificates.manage')
        <form method="POST" action="{{ route('tenant.reports.certificates.templates.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.name') }}</label>
                <input name="name" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.code') }}</label>
                <input name="code" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('code') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.certificate_type') }}</label>
                <select name="certificate_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($certificateTypes as $type)
                        <option value="{{ $type }}" @selected(old('certificate_type', 'completion') === $type)>{{ __('ui.certificate_type_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.title') }}</label>
                <input name="title" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('title') }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.body') }}</label>
                <textarea name="body" rows="4" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">{{ old('body') }}</textarea>
                <p class="mt-1 text-xs text-muted">@{{student_name}}, @{{class_name}}, @{{institution_name}}, @{{issue_date}}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.seal_label') }}</label>
                <input name="seal_label" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('seal_label', 'Official seal') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.signature_label') }}</label>
                <input name="signature_label" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('signature_label', 'Principal / Muhtamim') }}">
            </div>
            <div class="flex items-center gap-4 md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="show_qr" value="1" @checked(old('show_qr', true))> {{ __('ui.show_qr') }}</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.active') }}</label>
                <x-ui.button type="submit">{{ __('ui.add_template') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.code') }}</th>
                    <th class="px-4 py-3">{{ __('ui.certificate_type') }}</th>
                    <th class="px-4 py-3">{{ __('ui.title') }}</th>
                    <th class="px-4 py-3">{{ __('ui.status') }}</th>
                    @can('reports.certificates.manage')<th class="px-4 py-3"></th>@endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $template->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $template->code }}</td>
                        <td class="px-4 py-3">{{ __('ui.certificate_type_'.$template->certificate_type) }}</td>
                        <td class="px-4 py-3">{{ $template->title }}</td>
                        <td class="px-4 py-3">{{ $template->is_active ? __('ui.active') : __('ui.inactive') }}</td>
                        @can('reports.certificates.manage')
                            <td class="px-4 py-3 text-end">
                                <form method="POST" action="{{ route('tenant.reports.certificates.templates.destroy', $template) }}" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline">{{ __('ui.delete') }}</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $templates->links() }}</div>
@endsection
