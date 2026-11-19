@extends('layouts.app')

@section('title', $tenant->name)

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $tenant->name }}</h1>
        <p class="mt-1 text-sm text-muted">{{ $tenant->slug }}.{{ config('sme.tenancy.base_domain') }}</p>
    </div>
    <x-ui.badge :tone="$tenant->status === 'active' ? 'success' : 'warning'">{{ $tenant->status }}</x-ui.badge>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.form-section :title="__('ui.tenant_details')">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-muted">{{ __('ui.institution_type') }}</dt><dd class="font-medium">{{ config('sme.institution_types.'.$tenant->institution_type) }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.plan') }}</dt><dd class="font-medium">{{ $tenant->plan?->name }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.trial_ends') }}</dt><dd class="font-medium">{{ $tenant->trial_ends_at?->toDayDateTimeString() ?? '—' }}</dd></div>
                    <div><dt class="text-muted">{{ __('ui.institution_name') }}</dt><dd class="font-medium">{{ $tenant->institution?->name }}</dd></div>
                </dl>
            </x-ui.form-section>

            <x-ui.form-section :title="__('ui.nav.domains')">
                <ul class="space-y-2 text-sm">
                    @foreach ($tenant->domains as $domain)
                        <li class="flex items-center justify-between rounded-xl border border-sme px-3 py-2">
                            <span>{{ $domain->host }} <span class="text-muted">({{ $domain->type }})</span></span>
                            <x-ui.badge :tone="$domain->isVerified() ? 'success' : 'warning'">{{ $domain->verification_status }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            </x-ui.form-section>

            <x-ui.form-section :title="__('ui.nav.usage')">
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ($tenant->usageMetrics as $metric)
                        <div class="rounded-xl border border-sme px-3 py-3">
                            <p class="text-xs uppercase tracking-wide text-muted">{{ $metric->metric_key }}</p>
                            <p class="mt-1 text-lg font-semibold">{{ number_format($metric->current_value) }}</p>
                        </div>
                    @endforeach
                </div>
            </x-ui.form-section>
        </div>

        <aside class="space-y-6">
            <x-ui.form-section :title="__('ui.update_status')">
                <form method="POST" action="{{ route('platform.tenants.status', $tenant) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <x-ui.select name="status" :label="__('ui.col_status')">
                        @foreach (['pending','active','suspended','cancelled'] as $status)
                            <option value="{{ $status }}" @selected($tenant->status === $status)>{{ $status }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.button type="submit" size="sm">{{ __('ui.save') }}</x-ui.button>
                </form>
            </x-ui.form-section>

            <x-ui.form-section :title="__('ui.admin_account')">
                <ul class="space-y-2 text-sm">
                    @foreach ($tenant->users as $user)
                        <li>
                            <p class="font-medium">{{ $user->name }}</p>
                            <p class="text-muted">{{ $user->email }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-ui.form-section>
        </aside>
    </div>
@endsection
