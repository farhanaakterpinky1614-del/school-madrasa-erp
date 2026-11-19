@extends('layouts.app')

@section('title', __('ui.nav.usage'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.usage_overview') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.usage_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.col_name'), __('ui.usage_students'), __('ui.usage_staff'), __('ui.usage_storage'), __('ui.plan')]">
        @foreach ($tenants as $tenant)
            @php
                $metrics = $tenant->usageMetrics->keyBy('metric_key');
            @endphp
            <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                <td class="px-4 py-3.5 font-medium">{{ $tenant->name }}</td>
                <td class="px-4 py-3.5">{{ number_format($metrics->get('students')?->current_value ?? 0) }}</td>
                <td class="px-4 py-3.5">{{ number_format($metrics->get('staff')?->current_value ?? 0) }}</td>
                <td class="px-4 py-3.5">{{ number_format($metrics->get('storage_mb')?->current_value ?? 0) }} MB</td>
                <td class="px-4 py-3.5 text-muted">{{ $tenant->plan?->name }}</td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $tenants->links() }}</div>
@endsection
