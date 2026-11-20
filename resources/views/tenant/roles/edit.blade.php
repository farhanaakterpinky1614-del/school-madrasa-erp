@extends('layouts.app')

@section('title', __('ui.edit_role'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit_role') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $role->name }}</p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.role_details')">
            <div class="max-w-xl">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $role->name)" :disabled="$role->name === 'Institution Admin'" required />
                @if ($role->name === 'Institution Admin')
                    <p class="mt-2 text-xs text-muted">{{ __('ui.institution_admin_locked') }}</p>
                    <input type="hidden" name="name" value="Institution Admin">
                @endif
            </div>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.permission_catalog')">
            <div class="space-y-6">
                @foreach ($permissions as $module => $items)
                    <div>
                        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-muted">{{ $module }}</h3>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $permission)
                                <label class="flex items-start gap-2 rounded-xl border border-sme px-3 py-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(collect(old('permissions', $assigned))->contains($permission)) class="mt-0.5 rounded border-sme text-primary-600">
                                    <span class="font-mono text-xs">{{ $permission }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.form-section>

        <div class="flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.roles.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
        </div>
    </form>

    @can('delete', $role)
        <form method="POST" action="{{ route('tenant.roles.destroy', $role) }}" class="mt-6" onsubmit="return confirm(@json(__('ui.confirm_delete_role')))">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" class="!bg-rose-600 hover:!bg-rose-700">{{ __('ui.delete') }}</x-ui.button>
        </form>
    @endcan
@endsection
