@extends('layouts.app')

@section('title', __('ui.nav.branches'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.branches') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.branches_help') }}</p>
    </div>
    <a href="{{ route('tenant.branches.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.city'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($branches as $branch)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $branch->name }} @if($branch->is_primary)<x-ui.badge tone="info">{{ __('ui.primary') }}</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ $branch->code ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $branch->city ?: '—' }}</td>
                <td class="px-4 py-3"><x-ui.badge :tone="$branch->is_active ? 'success' : 'warning'">{{ $branch->is_active ? __('ui.status_active') : __('ui.inactive') }}</x-ui.badge></td>
                <td class="px-4 py-3"><a href="{{ route('tenant.branches.edit', $branch) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $branches->links() }}</div>
@endsection
