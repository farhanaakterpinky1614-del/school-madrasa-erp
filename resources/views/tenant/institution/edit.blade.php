@extends('layouts.app')

@section('title', __('ui.nav.institution'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.institution') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.edit_institution_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mb-6 rounded-2xl border border-sme bg-surface/70 p-4 text-sm shadow-sme-soft">
        <p class="font-semibold">{{ $institutionTypes[$institution->institution_type] ?? $institution->institution_type }}</p>
        <p class="mt-1 text-muted">{{ __('ui.preset_features') }}:
            @foreach (($preset['features'] ?? []) as $feature => $enabled)
                <span class="me-2 inline-flex items-center gap-1">
                    <x-ui.badge :tone="$enabled ? 'success' : 'slate'">{{ $feature }}</x-ui.badge>
                </span>
            @endforeach
        </p>
    </div>

    <form method="POST" action="{{ route('tenant.institution.update') }}" class="max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        {{-- Intentionally no tenant_id field — isolation enforced server-side --}}
        <x-ui.form-section :title="__('ui.institution_profile')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.institution_name')" :value="old('name', $institution->name)" required />
                <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn', $institution->name_bn)" />
                <x-ui.input name="name_ar" :label="__('ui.name_ar')" :value="old('name_ar', $institution->name_ar)" />
                <x-ui.select name="institution_type" :label="__('ui.institution_type')" required>
                    @foreach ($institutionTypes as $code => $label)
                        <option value="{{ $code }}" @selected(old('institution_type', $institution->institution_type) === $code)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="eiin" :label="__('ui.eiin')" :value="old('eiin', $institution->eiin)" />
                <x-ui.input name="board" :label="__('ui.board')" :value="old('board', $institution->board)" />
                <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email', $institution->email)" />
                <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone', $institution->phone)" />
                <x-ui.input name="website" :label="__('ui.website')" :value="old('website', $institution->website)" />
                <x-ui.input name="motto" :label="__('ui.motto')" :value="old('motto', $institution->motto)" />
                <x-ui.input name="founded_year" type="number" :label="__('ui.founded_year')" :value="old('founded_year', $institution->founded_year)" />
                <x-ui.input name="primary_color" :label="__('ui.primary_color')" :value="old('primary_color', $institution->primary_color)" />
                <x-ui.input name="secondary_color" :label="__('ui.secondary_color')" :value="old('secondary_color', $institution->secondary_color)" />
                <x-ui.input name="address" :label="__('ui.address')" :value="old('address', $institution->address)" />
                <x-ui.input name="city" :label="__('ui.city')" :value="old('city', $institution->city)" />
                <x-ui.input name="division" :label="__('ui.division')" :value="old('division', $institution->division)" />
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.settings.edit') }}" class="inline-flex items-center text-sm font-semibold text-primary-600">{{ __('ui.nav.settings') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
