@extends('layouts.app')

@section('title', __('ui.new_tenant'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_tenant') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.register_help') }}</p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('platform.tenants.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.institution_name')" :description="__('ui.tenants_help')">
            <div class="space-y-4">
                <x-ui.input name="institution_name" :label="__('ui.institution_name')" :value="old('institution_name')" required :error="$errors->first('institution_name')" />
                <x-ui.input name="slug" :label="__('ui.subdomain_slug')" :value="old('slug')" required :hint="'*.'.config('sme.tenancy.base_domain')" :error="$errors->first('slug')" />
                <x-ui.select name="institution_type" :label="__('ui.institution_type')" required :error="$errors->first('institution_type')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($institutionTypes as $code => $label)
                        <option value="{{ $code }}" @selected(old('institution_type') === $code)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="plan_code" :label="__('ui.plan')" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->code }}" @selected(old('plan_code', 'starter') === $plan->code)>{{ $plan->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="admin_name" :label="__('ui.field_name')" :value="old('admin_name')" required />
                <x-ui.input name="admin_email" type="email" :label="__('ui.field_email')" :value="old('admin_email')" required :error="$errors->first('admin_email')" />
                <x-ui.input name="admin_password" type="password" :label="__('ui.field_password')" required />
                <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
            </div>
            <div class="mt-6 flex gap-2">
                <x-ui.button type="submit">{{ __('ui.create_tenant') }}</x-ui.button>
                <a href="{{ route('platform.tenants.index') }}"><x-ui.button type="button" variant="outline">{{ __('ui.cancel') }}</x-ui.button></a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
