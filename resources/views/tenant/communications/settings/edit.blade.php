@extends('layouts.app')

@section('title', __('ui.nav.communication_settings'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.communication_settings') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.communication_settings_help') }}</p>
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

    <form method="POST" action="{{ route('tenant.communications.settings.update') }}" class="grid max-w-2xl gap-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-5 dark:border-slate-700 dark:from-slate-900">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.sms_driver') }}</label>
            <select name="sms_driver" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($smsDrivers as $driver)
                    <option value="{{ $driver }}" @selected(old('sms_driver', $settings->sms_driver) === $driver)>{{ __('ui.sms_driver_'.$driver) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.email_driver') }}</label>
            <select name="email_driver" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($emailDrivers as $driver)
                    <option value="{{ $driver }}" @selected(old('email_driver', $settings->email_driver) === $driver)>{{ __('ui.email_driver_'.$driver) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap gap-6">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings->sms_enabled)) class="rounded border-slate-300">
                {{ __('ui.sms_enabled') }}
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="email_enabled" value="1" @checked(old('email_enabled', $settings->email_enabled)) class="rounded border-slate-300">
                {{ __('ui.email_enabled') }}
            </label>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.notes') }}</label>
            <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">{{ old('notes', $settings->notes) }}</textarea>
        </div>
        <div>
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        </div>
    </form>
@endsection
