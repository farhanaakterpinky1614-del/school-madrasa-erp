@extends('layouts.guest')

@section('title', __('ui.register_institution'))

@section('content')
    <div class="sme-surface-solid rounded-2xl border border-sme p-6 shadow-sme sm:max-w-lg sm:p-8" style="max-width: 32rem; margin-inline: auto;">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('ui.register_institution') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.register_help') }}</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf

            <x-ui.input name="institution_name" :label="__('ui.institution_name')" :value="old('institution_name')" required />
            <x-ui.input name="slug" :label="__('ui.subdomain_slug')" :value="old('slug')" required :hint="'*.'.config('sme.tenancy.base_domain')" :error="$errors->first('slug')" />

            <x-ui.select name="institution_type" :label="__('ui.institution_type')" required :error="$errors->first('institution_type')">
                <option value="">{{ __('ui.select_option') }}</option>
                @foreach ($institutionTypes as $code => $label)
                    <option value="{{ $code }}" @selected(old('institution_type') === $code)>{{ $label }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select name="plan_code" :label="__('ui.plan')" required :error="$errors->first('plan_code')">
                @foreach ($plans as $plan)
                    <option value="{{ $plan->code }}" @selected(old('plan_code', 'starter') === $plan->code)>
                        {{ $plan->name }} — ৳{{ number_format($plan->price_monthly_bdt) }}/mo
                    </option>
                @endforeach
            </x-ui.select>

            <div class="border-t border-sme pt-4">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.admin_account') }}</p>
                <div class="space-y-4">
                    <x-ui.input name="admin_name" :label="__('ui.field_name')" :value="old('admin_name')" required />
                    <x-ui.input name="admin_email" type="email" :label="__('ui.field_email')" :value="old('admin_email')" required />
                    <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
                    <x-ui.input name="admin_password" type="password" :label="__('ui.field_password')" required />
                    <x-ui.input name="admin_password_confirmation" type="password" :label="__('ui.confirm_password')" required />
                </div>
            </div>

            <x-ui.button type="submit" class="w-full justify-center">{{ __('ui.create_tenant') }}</x-ui.button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            {{ __('ui.have_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('ui.sign_in') }}</a>
        </p>
    </div>
@endsection
