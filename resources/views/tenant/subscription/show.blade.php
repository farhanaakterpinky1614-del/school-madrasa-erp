@extends('layouts.app')

@section('title', __('ui.nav.subscription'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.subscription') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.subscription_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="mb-6 rounded-2xl border border-sme sme-surface-solid p-5 shadow-sme-soft">
        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.current_plan') }}</p>
        <p class="mt-1 text-2xl font-semibold">{{ $tenant->plan?->name }}</p>
        <p class="mt-1 text-sm text-muted">
            {{ __('ui.subscription_status') }}: {{ $tenant->currentSubscription?->status }}
            · {{ __('ui.trial_ends') }}: {{ $tenant->trial_ends_at?->toFormattedDateString() ?? '—' }}
        </p>
        <p class="mt-3 text-xs text-muted">{{ __('ui.no_payment_gateway') }}</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($plans as $plan)
            <div @class([
                'rounded-2xl border p-5 shadow-sme-soft',
                'border-primary-400/60 bg-gradient-to-br from-primary-50/80 to-secondary-50/40 dark:from-primary-950/40 dark:to-secondary-950/20' => $plan->id === $tenant->plan_id,
                'border-sme sme-surface-solid' => $plan->id !== $tenant->plan_id,
            ])>
                <h2 class="text-lg font-semibold">{{ $plan->name }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $plan->description }}</p>
                <p class="mt-3 text-xl font-semibold">৳{{ number_format($plan->price_monthly_bdt) }}</p>
                @if ($plan->id === $tenant->plan_id)
                    <x-ui.badge tone="success" class="mt-3">{{ __('ui.current') }}</x-ui.badge>
                @endif
            </div>
        @endforeach
    </div>
@endsection
