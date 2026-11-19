@extends('layouts.app')

@section('title', __('ui.platform_console'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 02</p>
        <h1 class="text-2xl font-semibold tracking-tight sm:text-[1.7rem]">{{ __('ui.platform_console') }}</h1>
        <p class="mt-1.5 max-w-2xl text-sm text-muted">{{ __('ui.platform_dashboard_help') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('platform.tenants.create') }}">
            <x-ui.button size="sm" type="button">
                <x-lucide-plus class="h-4 w-4" />
                {{ __('ui.new_tenant') }}
            </x-ui.button>
        </a>
    </div>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="sme-kpi sme-kpi-indigo px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.nav.tenants') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $tenantCount }}</p>
            <p class="mt-1 text-xs text-muted">{{ $activeTenants }} {{ __('ui.status_active') }}</p>
        </div>
        <div class="sme-kpi sme-kpi-emerald px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.nav.institutions') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $institutionCount }}</p>
        </div>
        <div class="sme-kpi sme-kpi-amber px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.nav.plans') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $planCount }}</p>
        </div>
        <div class="sme-kpi sme-kpi-indigo px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.usage_students') }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ number_format($usageTotals['students']) }}</p>
            <p class="mt-1 text-xs text-muted">{{ __('ui.usage_staff') }}: {{ number_format($usageTotals['staff']) }}</p>
        </div>
    </div>

    <x-ui.form-section :title="__('ui.recent_tenants')" :description="__('ui.recent_tenants_help')">
        <x-ui.table :headers="[__('ui.col_name'), __('ui.col_slug'), __('ui.plan'), __('ui.col_status'), __('ui.col_action')]">
            @forelse ($recentTenants as $tenant)
                <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                    <td class="px-4 py-3.5 font-medium">{{ $tenant->name }}</td>
                    <td class="px-4 py-3.5 text-muted">{{ $tenant->slug }}</td>
                    <td class="px-4 py-3.5">{{ $tenant->plan?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5">
                        <x-ui.badge :tone="$tenant->status === 'active' ? 'success' : 'warning'">{{ $tenant->status }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3.5">
                        <a href="{{ route('platform.tenants.show', $tenant) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.view') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-muted">{{ __('ui.no_tenants_yet') }}</td>
                </tr>
            @endforelse
        </x-ui.table>
    </x-ui.form-section>
@endsection
