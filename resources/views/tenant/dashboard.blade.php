@extends('layouts.app')

@section('title', __('ui.nav.dashboard'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 02</p>
        <h1 class="text-2xl font-semibold tracking-tight sm:text-[1.7rem]">{{ $tenant->name }}</h1>
        <p class="mt-1.5 max-w-2xl text-sm text-muted">{{ __('ui.tenant_dashboard_help') }}</p>
    </div>
    <x-ui.badge tone="success">{{ $tenant->plan?->name ?? '—' }}</x-ui.badge>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ($usage as $key => $row)
            <div class="sme-kpi sme-kpi-indigo px-4 py-4 ps-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.usage_'.$key) }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight">{{ number_format($row['current']) }}</p>
                <p class="mt-1 text-xs text-muted">
                    {{ __('ui.limit') }}:
                    {{ $row['limit'] === null ? '∞' : number_format($row['limit']) }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.form-section :title="__('ui.institution_name')" :description="$tenant->institution?->name">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-muted">{{ __('ui.institution_type') }}</dt><dd class="font-medium">{{ config('sme.institution_types.'.$tenant->institution_type) }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.col_slug') }}</dt><dd class="font-medium">{{ $tenant->slug }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.primary_domain') }}</dt><dd class="font-medium">{{ $tenant->primaryDomain?->host }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.subscription_status') }}</dt><dd class="font-medium">{{ $tenant->currentSubscription?->status ?? '—' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <a href="{{ route('tenant.institution.edit') }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.edit_institution') }}</a>
                </div>
            </x-ui.form-section>

            <x-ui.form-section :title="__('ui.module_access')">
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($modules as $module)
                        <div class="flex items-center justify-between rounded-xl border border-sme px-3 py-2.5 text-sm">
                            <span>{{ $module['name'] }}</span>
                            <x-ui.badge :tone="$module['accessible'] ? 'success' : 'warning'">
                                {{ $module['accessible'] ? __('ui.enabled') : __('ui.locked') }}
                            </x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </x-ui.form-section>
        </div>

        <aside>
            <x-ui.form-section :title="__('ui.quick_links')">
                <ul class="space-y-2 text-sm">
                    <li><a class="font-semibold text-primary-600 dark:text-primary-400" href="{{ route('tenant.domains.index') }}">{{ __('ui.nav.domains') }}</a></li>
                    <li><a class="font-semibold text-primary-600 dark:text-primary-400" href="{{ route('tenant.subscription.show') }}">{{ __('ui.nav.subscription') }}</a></li>
                    <li><a class="font-semibold text-primary-600 dark:text-primary-400" href="{{ route('tenant.usage.index') }}">{{ __('ui.nav.usage') }}</a></li>
                </ul>
            </x-ui.form-section>
        </aside>
    </div>
@endsection
