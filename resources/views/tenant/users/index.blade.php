@extends('layouts.app')

@section('title', __('ui.nav.users'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.users') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.users_help') }}</p>
    </div>
    @can('create', App\Models\User::class)
        <a href="{{ route('tenant.users.create') }}">
            <x-ui.button type="button">{{ __('ui.new_user') }}</x-ui.button>
        </a>
    @endcan
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</p>
    @endif

    <x-ui.table :headers="[__('ui.col_name'), __('ui.field_email'), __('ui.role'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($users as $user)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $user->email }}</td>
                <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$user->is_active ? 'success' : 'warning'">
                        {{ $user->is_active ? __('ui.status_active') : __('ui.inactive') }}
                    </x-ui.badge>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.users.edit', $user) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.edit') }}</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_users') }}</td>
            </tr>
        @endforelse
    </x-ui.table>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
