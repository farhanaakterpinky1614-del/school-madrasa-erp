@extends('layouts.app')

@section('title', __('ui.nav.library_settings'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_settings') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_settings_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('tenant.library.settings.update') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-6 dark:border-slate-700 dark:from-slate-900">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fine_mode') }}</label>
            <select name="fine_mode" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($fineModes as $mode)
                    <option value="{{ $mode }}" @selected(old('fine_mode', $settings->fine_mode) === $mode)>{{ __('ui.fine_mode_'.$mode) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fine_daily_rate') }}</label>
            <input type="number" step="0.01" min="0" name="fine_daily_rate" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('fine_daily_rate', $settings->fine_daily_rate) }}">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fine_fixed_amount') }}</label>
            <input type="number" step="0.01" min="0" name="fine_fixed_amount" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('fine_fixed_amount', $settings->fine_fixed_amount) }}">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.default_loan_days') }}</label>
            <input type="number" min="1" max="365" name="default_loan_days" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('default_loan_days', $settings->default_loan_days) }}">
        </div>
        <x-ui.button type="submit">{{ __('ui.save_settings') }}</x-ui.button>
    </form>
@endsection
