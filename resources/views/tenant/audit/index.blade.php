@extends('layouts.app')

@section('title', __('ui.nav.audit'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.audit') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.audit_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.when'), __('ui.actor'), __('ui.action'), __('ui.description')]">
        @forelse ($logs as $log)
            <tr>
                <td class="px-4 py-3 text-muted whitespace-nowrap">{{ $log->created_at?->toDateTimeString() }}</td>
                <td class="px-4 py-3">{{ $log->user?->email ?? '—' }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                <td class="px-4 py-3 text-sm text-muted">{{ $log->description ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_audit_logs') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
