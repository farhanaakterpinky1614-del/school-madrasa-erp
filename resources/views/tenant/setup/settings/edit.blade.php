@extends('layouts.app')

@section('title', __('ui.nav.settings'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.settings') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.settings_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mb-6 rounded-2xl border border-sme bg-surface/70 p-4 text-sm shadow-sme-soft">
        <p class="font-semibold">{{ __('ui.type_preset') }}: {{ $preset['label'] ?? $tenant->institution_type }}</p>
        <p class="mt-1 text-muted">{{ __('ui.type_preset_hint') }}</p>
    </div>

    <form method="POST" action="{{ route('tenant.settings.update') }}" class="max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.locale_currency')">
            <div class="space-y-4">
                <x-ui.select name="locale" :label="__('ui.language')">
                    @foreach ($locales as $code => $meta)
                        <option value="{{ $code }}" @selected(old('locale', $settings['locale'] ?? 'en') === $code)>{{ $meta['native'] }} ({{ $meta['label'] }})</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="currency" :label="__('ui.currency')" :value="old('currency', $settings['currency'] ?? 'BDT')" required />
                <x-ui.input name="timezone" :label="__('ui.timezone')" :value="old('timezone', $settings['timezone'] ?? 'Asia/Dhaka')" required />
                <x-ui.input name="date_format" :label="__('ui.date_format')" :value="old('date_format', $settings['date_format'] ?? 'd/m/Y')" required />
            </div>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.working_days')">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach (['sun','mon','tue','wed','thu','fri','sat'] as $day)
                    <label class="flex items-center gap-2 rounded-xl border border-sme px-3 py-2 text-sm">
                        <input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, old('working_days', $settings['working_days'] ?? []), true))>
                        {{ __('ui.day_'.$day) }}
                    </label>
                @endforeach
            </div>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.branding_hooks')">
            <div class="space-y-4">
                <x-ui.input name="primary_color" :label="__('ui.primary_color')" :value="old('primary_color', data_get($settings, 'branding.primary_color', '#4F46E5'))" />
                <x-ui.input name="secondary_color" :label="__('ui.secondary_color')" :value="old('secondary_color', data_get($settings, 'branding.secondary_color', '#0F172A'))" />
                <p class="text-xs text-muted">{{ __('ui.branding_hooks_hint') }}</p>
            </div>
            <div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
        </x-ui.form-section>
    </form>
@endsection
