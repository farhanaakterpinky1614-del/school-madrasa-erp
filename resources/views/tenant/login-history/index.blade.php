@extends('layouts.app')

@section('title', __('ui.nav.login_history'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.login_history') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.login_history_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.when'), __('ui.field_email'), __('ui.event'), __('ui.ip'), __('ui.user_agent')]">
        @forelse ($histories as $row)
            <tr>
                <td class="px-4 py-3 text-muted whitespace-nowrap">{{ $row->created_at?->toDateTimeString() }}</td>
                <td class="px-4 py-3">{{ $row->email }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$row->event === 'success' ? 'success' : ($row->event === 'failed' ? 'danger' : 'slate')">
                        {{ $row->event }}
                    </x-ui.badge>
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $row->ip_address }}</td>
                <td class="px-4 py-3 max-w-xs truncate text-xs text-muted" title="{{ $row->user_agent }}">{{ $row->user_agent }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_login_history') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $histories->links() }}</div>
@endsection
