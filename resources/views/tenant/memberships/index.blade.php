@extends('layouts.app')

@section('title', __('ui.nav.memberships'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.memberships') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.memberships_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</p>
    @endif

    <x-ui.table :headers="[__('ui.col_name'), __('ui.field_email'), __('ui.col_status'), __('ui.joined'), __('ui.col_action')]">
        @forelse ($memberships as $membership)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $membership->user?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $membership->user?->email }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$membership->status === 'active' ? 'success' : 'warning'">{{ $membership->status }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-muted">{{ $membership->joined_at?->toDateString() ?? '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.memberships.update', $membership) }}" class="inline-flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="rounded-lg border border-sme bg-surface px-2 py-1 text-xs">
                            <option value="active" @selected($membership->status === 'active')>active</option>
                            <option value="suspended" @selected($membership->status === 'suspended')>suspended</option>
                            <option value="invited" @selected($membership->status === 'invited')>invited</option>
                        </select>
                        <button type="submit" class="text-xs font-semibold text-primary-600">{{ __('ui.update_status') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_memberships') }}</td></tr>
        @endforelse
    </x-ui.table>

    <div class="mt-4">{{ $memberships->links() }}</div>
@endsection
