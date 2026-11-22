@extends('layouts.app')

@section('title', __('ui.nav.report_exports'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 15</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.report_exports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.report_exports_help') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.reports_hub') }}</x-ui.button></a>
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

    @can('reports.export.create')
        <form method="POST" action="{{ route('tenant.reports.exports.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.report_type') }}</label>
                <select name="report_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($reportTypes as $type)
                        <option value="{{ $type }}">{{ __('ui.report_type_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.format') }}</label>
                <select name="format" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($formats as $format)
                        <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.school_class') }} ({{ __('ui.optional') }})</label>
                <select name="school_class_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.invoice_status') }} ({{ __('ui.optional') }})</label>
                <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach ($invoiceStatuses as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.from_date') }}</label>
                <input type="date" name="from" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.to_date') }}</label>
                <input type="date" name="to" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div class="md:col-span-3">
                <x-ui.button type="submit">{{ __('ui.run_export') }}</x-ui.button>
                <span class="ms-2 text-xs text-muted">{{ __('ui.export_sync_note') }}</span>
            </div>
        </form>
    @endcan

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.report_type') }}</th>
                    <th class="px-4 py-3">{{ __('ui.format') }}</th>
                    <th class="px-4 py-3">{{ __('ui.status') }}</th>
                    <th class="px-4 py-3">{{ __('ui.rows') }}</th>
                    <th class="px-4 py-3">{{ __('ui.created_at') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @forelse ($exports as $export)
                    <tr>
                        <td class="px-4 py-3">{{ __('ui.report_type_'.$export->report_type) }}</td>
                        <td class="px-4 py-3 uppercase">{{ $export->format }}</td>
                        <td class="px-4 py-3">{{ __('ui.export_status_'.$export->status) }}</td>
                        <td class="px-4 py-3">{{ $export->row_count ?? '—' }}</td>
                        <td class="px-4 py-3">{{ optional($export->created_at)?->timezone('Asia/Dhaka')->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-end">
                            @if ($export->status === \Modules\Reports\Models\ReportExport::STATUS_COMPLETED)
                                <a href="{{ route('tenant.reports.exports.download', $export) }}" class="text-primary-600 hover:underline">{{ __('ui.download') }}</a>
                            @elseif ($export->error_message)
                                <span class="text-xs text-rose-600" title="{{ $export->error_message }}">{{ __('ui.failed') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $exports->links() }}</div>
@endsection
