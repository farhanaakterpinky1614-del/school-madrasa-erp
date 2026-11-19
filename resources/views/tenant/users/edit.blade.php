@extends('layouts.app')

@section('title', __('ui.edit_user'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit_user') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $user->email }}</p>
    </div>
@endsection

@section('content')
    <x-ui.form-section :title="__('ui.admin_account')">
        <form method="POST" action="{{ route('tenant.users.update', $user) }}" class="max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <x-ui.input name="name" :label="__('ui.field_name')" :value="old('name', $user->name)" required />
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email', $user->email)" required />
            <x-ui.input name="password" type="password" :label="__('ui.field_password')" />
            <x-ui.input name="password_confirmation" type="password" :label="__('ui.confirm_password')" />

            <x-ui.select name="user_type" :label="__('ui.user_type')">
                <option value="staff" @selected(old('user_type', $user->user_type) === 'staff')>{{ __('ui.staff') }}</option>
                <option value="tenant_admin" @selected(old('user_type', $user->user_type) === 'tenant_admin')>{{ __('ui.tenant_admin') }}</option>
                <option value="owner" @selected(old('user_type', $user->user_type) === 'owner')>{{ __('ui.owner') }}</option>
            </x-ui.select>

            <x-ui.select name="role" :label="__('ui.role')">
                <option value="">{{ __('ui.select_option') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $user->roles->first()?->name) === $role->name)>{{ $role->name }}</option>
                @endforeach
            </x-ui.select>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-sme text-primary-600">
                {{ __('ui.status_active') }}
            </label>
            <div class="flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.users.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </x-ui.form-section>

    @can('delete', $user)
        <form method="POST" action="{{ route('tenant.users.destroy', $user) }}" class="mt-6" onsubmit="return confirm(@json(__('ui.confirm_delete_user')))">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" class="!bg-rose-600 hover:!bg-rose-700">{{ __('ui.delete') }}</x-ui.button>
        </form>
    @endcan
@endsection
