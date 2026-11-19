@extends('layouts.app')

@section('title', __('ui.nav.tenants'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.tenants') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.tenants_help') }}</p>
    </div>
    <a href="{{ route('platform.tenants.create') }}">
        <x-ui.button size="sm"><x-lucide-plus class="h-4 w-4" /> {{ __('ui.new_tenant') }}</x-ui.button>
    </a>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.col_name'), __('ui.col_slug'), __('ui.institution_type'), __('ui.plan'), __('ui.col_status'), __('ui.col_action')]">
        @foreach ($tenants as $tenant)
            <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                <td class="px-4 py-3.5 font-medium">{{ $tenant->name }}</td>
                <td class="px-4 py-3.5 text-muted">{{ $tenant->slug }}</td>
                <td class="px-4 py-3.5">{{ config('sme.institution_types.'.$tenant->institution_type, $tenant->institution_type) }}</td>
                <td class="px-4 py-3.5">{{ $tenant->plan?->name ?? '—' }}</td>
                <td class="px-4 py-3.5"><x-ui.badge :tone="$tenant->status === 'active' ? 'success' : 'warning'">{{ $tenant->status }}</x-ui.badge></td>
                <td class="px-4 py-3.5">
                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $tenants->links() }}</div>
@endsection
