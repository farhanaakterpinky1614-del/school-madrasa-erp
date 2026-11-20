@extends('layouts.app')

@section('title', __('ui.nav.branches'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.branches') }}</h1>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.branches.store') }}" class="max-w-2xl space-y-4">
        @csrf
        
        <x-ui.form-section :title="__('ui.nav.branches')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
                <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" />
                <x-ui.input name="address" :label="__('ui.address')" :value="old('address')" />
                <x-ui.input name="city" :label="__('ui.city')" :value="old('city')" />
                <x-ui.input name="division" :label="__('ui.division')" :value="old('division')" />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary'))> {{ __('ui.primary') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.branches.index') }}" class="inline-flex items-center text-sm font-medium text-muted hover:text-primary-600">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
