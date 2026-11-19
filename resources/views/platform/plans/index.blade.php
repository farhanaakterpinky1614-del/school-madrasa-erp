@extends('layouts.app')

@section('title', __('ui.nav.plans'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.plans') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.plans_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($plans as $plan)
            <div class="sme-surface-solid rounded-2xl border border-sme p-5 shadow-sme-soft">
                <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">{{ $plan->code }}</p>
                <h2 class="mt-1 text-xl font-semibold">{{ $plan->name }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $plan->description }}</p>
                <p class="mt-4 text-2xl font-semibold">৳{{ number_format($plan->price_monthly_bdt) }}<span class="text-sm font-normal text-muted">/mo</span></p>
                <p class="mt-1 text-xs text-muted">{{ __('ui.trial_days') }}: {{ $plan->trial_days }}</p>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach ($plan->features as $feature)
                        <li class="flex items-center justify-between gap-2">
                            <span>{{ $feature->feature_key }}</span>
                            <span class="text-muted">
                                @if (! $feature->enabled)
                                    off
                                @elseif ($feature->limit_value === null)
                                    ∞
                                @else
                                    {{ number_format($feature->limit_value) }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endsection
