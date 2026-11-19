@extends('layouts.app')

@section('title', __('ui.nav.institution'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.institution') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.edit_institution_help') }}</p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.institution.update') }}" class="max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        {{-- Intentionally no tenant_id field — isolation enforced server-side --}}
        <x-ui.form-section :title="__('ui.institution_profile')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.institution_name')" :value="old('name', $institution->name)" required />
                <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn', $institution->name_bn)" />
                <x-ui.input name="name_ar" :label="__('ui.name_ar')" :value="old('name_ar', $institution->name_ar)" />
                <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email', $institution->email)" />
                <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone', $institution->phone)" />
                <x-ui.input name="address" :label="__('ui.address')" :value="old('address', $institution->address)" />
                <x-ui.input name="city" :label="__('ui.city')" :value="old('city', $institution->city)" />
                <x-ui.input name="division" :label="__('ui.division')" :value="old('division', $institution->division)" />
            </div>
            <div class="mt-6">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </x-ui.form-section>
    </form>
@endsection
