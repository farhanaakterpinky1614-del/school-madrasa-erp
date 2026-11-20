@extends('layouts.app')

@section('title', __('ui.new_user'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_platform_user') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.platform_users_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.form-section :title="__('ui.admin_account')">
        <form method="POST" action="{{ route('platform.users.store') }}" class="max-w-xl space-y-4">
            @csrf
            <x-ui.input name="name" :label="__('ui.field_name')" :value="old('name')" required />
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" required />
            <x-ui.input name="password" type="password" :label="__('ui.field_password')" required />
            <x-ui.input name="password_confirmation" type="password" :label="__('ui.confirm_password')" required />
            <x-ui.select name="role" :label="__('ui.role')" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </x-ui.select>
            <div class="flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('platform.users.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </x-ui.form-section>
@endsection
