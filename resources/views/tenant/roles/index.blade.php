@extends('layouts.app')

@section('title', __('ui.nav.roles'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.roles') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.roles_help') }}</p>
    </div>
    @can('create', Spatie\Permission\Models\Role::class)
        <a href="{{ route('tenant.roles.create') }}">
            <x-ui.button type="button">{{ __('ui.custom_role') }}</x-ui.button>
        </a>
    @endcan
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</p>
    @endif

    <x-ui.table :headers="[__('ui.col_name'), __('ui.permissions_count'), __('ui.col_action')]">
        @foreach ($roles as $role)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $role->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $role->permissions_count }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.roles.edit', $role) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.edit') }}</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endsection
